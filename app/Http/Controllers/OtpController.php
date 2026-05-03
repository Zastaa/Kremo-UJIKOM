<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMail;
use App\Mail\OtpVerificationMail;
use App\Models\User;
use App\Services\MailNotificationService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OtpController extends Controller
{
    public function __construct(
        private OtpService $otpService,
        private MailNotificationService $mailService,
    ) {}

    public function showVerify()
    {
        // Izinkan jika ada data pendaftaran di session, ATAU jika user sudah login tapi belum verifikasi
        if (!session()->has('register_data') && !(auth()->check() && is_null(auth()->user()->email_verified_at))) {
            return auth()->check()
                ? redirect()->route('dashboard')
                : redirect()->route('register');
        }
        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        if (session()->has('register_data')) {
            // Flow untuk pengguna baru yang mendaftar via session
            $email = session('register_data')['email'];
            $result = $this->otpService->verify($email, $request->otp, 'register_verification');

            if ($result === true) {
                $user = User::create(session('register_data'));
                
                // Set secara paksa agar tidak terhalang Fillable attribute
                $user->email_verified_at = now();
                $user->save();
                
                auth()->login($user);
                session()->forget('register_data');

                return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil dan email telah diverifikasi!');
            }
        } elseif (auth()->check() && is_null(auth()->user()->email_verified_at)) {
            // Flow untuk pengguna lama yang login tapi belum terverifikasi
            $email = auth()->user()->email;
            $result = $this->otpService->verify($email, $request->otp, 'register_verification');

            if ($result === true) {
                $user = auth()->user();
                $user->email_verified_at = now();
                $user->save();

                return redirect()->route('dashboard')->with('success', 'Email berhasil diverifikasi!');
            }
        } else {
            return redirect()->route('register')->with('error', 'Sesi pendaftaran kadaluarsa. Silakan daftar ulang.');
        }

        return back()->withErrors(['otp' => $result]);
    }

    public function resend()
    {
        if (session()->has('register_data')) {
            $email = session('register_data')['email'];
            $userId = null;
        } elseif (auth()->check() && is_null(auth()->user()->email_verified_at)) {
            $email = auth()->user()->email;
            $userId = auth()->id();
        } else {
            return auth()->check()
                ? redirect()->route('dashboard')
                : redirect()->route('register');
        }

        if (!$this->otpService->canResend($email, 'register_verification')) {
            return back()->with('error', 'Terlalu banyak permintaan. Coba lagi dalam 15 menit.');
        }

        $otp = $this->otpService->generate($email, 'register_verification', $userId);

        $this->mailService->send(
            new OtpVerificationMail($otp, 'register_verification'),
            $email, 'Kode Verifikasi Email - Kremo', 'otp-verification',
            $userId,
        );

        return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda.');
    }
}
