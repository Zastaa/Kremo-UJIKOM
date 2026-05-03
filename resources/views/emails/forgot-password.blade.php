@extends('emails.layout')

@section('content')
<h2>Reset password</h2>
<p class="lead">Kami menerima permintaan reset password untuk akun Anda. Gunakan kode OTP berikut untuk melanjutkan.</p>
<div class="otp-box">
    <span class="otp-code">{{ $otp }}</span>
</div>
<p>Kode ini berlaku selama <strong>10 menit</strong>.</p>
<div class="note">Jika Anda tidak meminta reset password, abaikan email ini dan jangan bagikan kode OTP kepada siapapun.</div>
@endsection
