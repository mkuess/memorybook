<?php

namespace App\Mail;

use App\Models\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VisitorMemoryConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Story $story,
        public readonly string $shortCode,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bitte bestätige deine Erinnerung',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.visitor-memory-confirmation',
        );
    }
}
