<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Neue Bestellung #' . $this->order->id . ' – ' . ($this->order->memoryPage->person_name ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-notification',
        );
    }
}
