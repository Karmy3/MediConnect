<?php

namespace App\Mail;

use App\Models\RendezVous;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RendezVousConfirmeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RendezVous $rendezVous)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre rendez-vous MediConnect est confirme',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rendez-vous-confirme',
        );
    }
}