<?php

namespace App\Jobs;

use App\Mail\RendezVousRappelMail;
use App\Models\RendezVous;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnvoyerRappelRdv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public RendezVous $rendezVous)
    {
    }

    public function handle(): void
    {
        // On revalide que le rendez-vous est toujours actif au moment de l'execution
        if (! in_array($this->rendezVous->statut, ['confirme', 'paye'])) {
            return;
        }

        Mail::to($this->rendezVous->patient->email)
            ->send(new RendezVousRappelMail($this->rendezVous));
    }
}