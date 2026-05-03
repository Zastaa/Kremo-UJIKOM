<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMail;
use App\Models\User;
use App\Services\MailNotificationService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private OtpService $otpService,
        private MailNotificationService $mailService,
    ) {}

    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak terdaftar.']);
        }

        if (!$this->otpService->canResend($request->email, 'forgot_password')) {
            return back()->with('error', 'Terlalu banyak permintaan. Coba lagi dalam 15 menit.');
        }

        $otp = $this->otpService->generate($request->email, 'forgot_password', $user->id);

        $this->mailService->send(
            new ForgotPasswordMail($otp),
            $request->email, 'Reset Password - Kremo', 'forgot-password',
            $user->id,
        );

        return redirect()->route('password.reset.form', ['email' => $request->email])
            ->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    public function showReset(Request $request)
    {
        return view('auth.reset-password', ['email' => $request->email]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $result = $this->otpService->verify($request->email, $request->otp, 'forgot_password');

        if ($result !== true) {
            return back()->withErrors(['otp' => $result]);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
            return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login.');
        }

        return back()->withErrors(['email' => 'User tidak ditemukan.']);
    }
}
