<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\RendezVous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaiementController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // Le patient paie son rendez-vous
    public function payer(Request $request, RendezVous $rendezVous)
    {
        if ($rendezVous->patient_id !== $request->user()->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($rendezVous->paiement) {
            return response()->json(['message' => 'Ce rendez-vous a deja ete paye.'], 409);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tarif = $rendezVous->creneau->medecinProfile->tarif_consultation;

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($tarif * 100), // Stripe travaille en centimes
                'currency' => 'eur',
                'payment_method' => $request->payment_method,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'description' => "Consultation MediConnect - RendezVous #{$rendezVous->id}",
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            return response()->json(['message' => 'Paiement refuse : '.$e->getMessage()], 402);
        }

        $statut = $paymentIntent->status === 'succeeded' ? 'reussi' : 'en_attente';

        $paiement = Paiement::create([
            'rendez_vous_id' => $rendezVous->id,
            'montant' => $tarif,
            'stripe_payment_id' => $paymentIntent->id,
            'statut' => $statut,
        ]);

        if ($statut === 'reussi') {
            $rendezVous->update(['statut' => 'paye']);
        }

        return response()->json([
            'paiement' => $paiement,
            'stripe_status' => $paymentIntent->status,
        ], 201);
    }

    // Consulter le statut d'un paiement
    public function show(Request $request, RendezVous $rendezVous)
    {
        if ($rendezVous->patient_id !== $request->user()->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if (! $rendezVous->paiement) {
            return response()->json(['message' => 'Aucun paiement trouve pour ce rendez-vous.'], 404);
        }

        return response()->json($rendezVous->paiement);
    }
}