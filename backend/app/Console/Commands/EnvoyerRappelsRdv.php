<?php

namespace App\Console\Commands;

use App\Jobs\EnvoyerRappelRdv;
use App\Models\RendezVous;
use Illuminate\Console\Command;

class EnvoyerRappelsRdv extends Command
{
    protected $signature = 'rendez-vous:envoyer-rappels';

    protected $description = 'Dispatche un job de rappel pour chaque rendez-vous prevu dans 24h';

    public function handle(): int
    {
        $debut = now()->addHours(23);
        $fin = now()->addHours(25);

        $rendezVous = RendezVous::whereIn('statut', ['confirme', 'paye'])
            ->whereHas('creneau', function ($query) use ($debut, $fin) {
                $query->whereBetween('date_debut', [$debut, $fin]);
            })
            ->with('creneau')
            ->get();

        foreach ($rendezVous as $rdv) {
            EnvoyerRappelRdv::dispatch($rdv);
        }

        $this->info("Rappels dispatches pour {$rendezVous->count()} rendez-vous.");

        return self::SUCCESS;
    }
}