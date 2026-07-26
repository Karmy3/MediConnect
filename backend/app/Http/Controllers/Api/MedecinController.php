<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedecinProfile;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    // Liste publique des medecins, avec recherche par nom ou specialite
    public function index(Request $request)
    {
        $recherche = $request->query('recherche');

        $medecins = MedecinProfile::with('user')
            ->when($recherche, function ($query) use ($recherche) {
                $query->whereHas('user', function ($q) use ($recherche) {
                    $q->where('name', 'ilike', "%{$recherche}%");
                })->orWhere('specialite', 'ilike', "%{$recherche}%");
            })
            ->get()
            ->map(function ($medecin) {
                return [
                    'id' => $medecin->id,
                    'name' => $medecin->user->name,
                    'specialite' => $medecin->specialite,
                    'tarif_consultation' => $medecin->tarif_consultation,
                ];
            });

        return response()->json($medecins);
    }
}