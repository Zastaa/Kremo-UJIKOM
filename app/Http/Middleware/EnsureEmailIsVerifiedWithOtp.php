<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerifiedWithOtp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() &&
            is_null($request->user()->email_verified_at) &&
            !$request->is('verify-email*') &&
            !$request->is('logout')) {
            
            return redirect()->route('otp.verify.form')
                ->with('error', 'Anda harus memverifikasi email terlebih dahulu sebelum melanjutkan.');
        }

        return $next($request);
    }
}
