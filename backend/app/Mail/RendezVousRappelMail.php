<?php

namespace App\Mail;

use App\Models\RendezVous;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RendezVousRappelMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public RendezVous $rendezVous)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel : votre rendez-vous MediConnect est demain',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rendez-vous-rappel',
        );
    }
}