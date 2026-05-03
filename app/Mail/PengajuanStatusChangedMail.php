<?php

namespace App\Mail;

use App\Models\PengajuanKredit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PengajuanKredit $pengajuan,
        public string $statusBaru,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address') ?: 'hello@example.com',
                config('mail.from.name') ?: 'Kremo',
            ),
            subject: "Update Status Pengajuan Kredit #{$this->pengajuan->id} - Kremo",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pengajuan-status-changed');
    }
}
