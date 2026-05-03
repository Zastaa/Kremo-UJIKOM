@extends('emails.layout')

@section('content')
<h2>Verifikasi email Anda</h2>
<p class="lead">Gunakan kode OTP berikut untuk {{ $purpose === 'forgot_password' ? 'mereset password Anda' : 'memverifikasi email Anda' }}.</p>
<div class="otp-box">
    <span class="otp-code">{{ $otp }}</span>
</div>
<p>Kode ini berlaku selama <strong>10 menit</strong>.</p>
<div class="note">Jangan bagikan kode ini kepada siapapun, termasuk pihak yang mengaku dari Kremo.</div>
@endsection
