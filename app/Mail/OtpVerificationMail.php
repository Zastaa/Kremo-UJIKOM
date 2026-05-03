<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $purpose = 'register_verification',
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'register_verification' => 'Kode Verifikasi Email - Kremo',
            'forgot_password' => 'Kode Reset Password - Kremo',
            'email_change' => 'Kode Verifikasi Perubahan Email - Kremo',
        ];
        return new Envelope(subject: $subjects[$this->purpose] ?? 'Kode OTP - Kremo');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp-verification');
    }
}
