<?php

namespace App\Services;

use App\Models\EmailOtp;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    /**
     * Generate and store a hashed OTP.
     * Returns the plain OTP for sending via email.
     */
    public function generate(string $email, string $purpose, ?int $userId = null): string
    {
        // Invalidate previous OTPs for same email + purpose
        EmailOtp::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtp::create([
            'user_id' => $userId,
            'email' => $email,
            'otp_hash' => Hash::make($otp),
            'purpose' => $purpose,
            'expired_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        return $otp;
    }

    /**
     * Verify an OTP.
     */
    public function verify(string $email, string $otp, string $purpose): bool|string
    {
        $record = EmailOtp::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$record) {
            return 'OTP tidak ditemukan. Silakan kirim ulang.';
        }

        if ($record->isExpired()) {
            return 'OTP sudah kadaluarsa. Silakan kirim ulang.';
        }

        if ($record->attempts >= 5) {
            return 'Terlalu banyak percobaan. Silakan kirim ulang OTP.';
        }

        $record->increment('attempts');

        if (!Hash::check($otp, $record->otp_hash)) {
            return 'Kode OTP salah. Sisa percobaan: ' . (5 - $record->attempts);
        }

        $record->update(['verified_at' => now()]);
        return true;
    }

    /**
     * Check rate limit for OTP resend (max 3 per 15 minutes).
     */
    public function canResend(string $email, string $purpose): bool
    {
        $count = EmailOtp::where('email', $email)
            ->where('purpose', $purpose)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        return $count < 3;
    }
}
