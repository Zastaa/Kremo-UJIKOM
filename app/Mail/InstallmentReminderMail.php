<?php

namespace App\Mail;

use App\Models\Angsuran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Angsuran $angsuran,
        public string $reminderType,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Reminder Angsuran Ke-{$this->angsuran->angsuran_ke} - Kremo");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.installment-reminder');
    }
}
