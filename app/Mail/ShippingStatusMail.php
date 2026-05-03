<?php

namespace App\Mail;

use App\Models\Pengiriman;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShippingStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pengiriman $pengiriman,
        public string $eventLabel = 'Status pengiriman diperbarui',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address') ?: 'hello@example.com',
                config('mail.from.name') ?: 'Kremo',
            ),
            subject: "{$this->eventLabel} - {$this->pengiriman->no_invoice}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.shipping-status');
    }
}
