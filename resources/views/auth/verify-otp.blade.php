<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Verifikasi Email - Kremo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-container { width: 100%; max-width: 440px; padding: 20px; }
        .auth-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .auth-brand { text-align: center; margin-bottom: 24px; }
        .auth-brand h1 { font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, #2563eb, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; color: #334155; font-size: 0.9rem; font-family: inherit; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); background: #ffffff; }
        .btn-verify { width: 100%; padding: 14px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 10px; font-size: 0.95rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.3s; }
        .btn-verify:hover { box-shadow: 0 4px 20px rgba(59,130,246,0.3); transform: translateY(-1px); }
        .btn-outline { background: transparent; border: 1px solid #cbd5e1; color: #64748b; }
        .btn-outline:hover { background: rgba(241,245,249,0.8); color: #334155; }
        .error { color: #ef4444; font-size: 0.8rem; margin-top: 4px; }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; padding: 12px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px; }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; padding: 12px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-brand">
                <h1><i class="fas fa-envelope-open-text"></i> Verifikasi Email</h1>
            </div>

            @if(session('success'))
                <div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif

            <p style="color:#94a3b8;margin-bottom:20px;font-size:0.9rem;text-align:center;line-height:1.5;">
                Kami telah mengirimkan kode OTP ke email <strong>{{ session('register_data')['email'] ?? '' }}</strong>.<br>Masukkan kode 6 digit tersebut di bawah ini.
            </p>

            <form method="POST" action="{{ route('otp.verify') }}">
                @csrf
                <div class="form-group">
                    <label style="text-align:center;">Kode OTP</label>
                    <input type="text" name="otp" class="form-control" maxlength="6" placeholder="000000" style="text-align:center;font-size:1.5rem;letter-spacing:12px;font-weight:bold;" required autofocus>
                    @error('otp')<div class="error" style="text-align:center;margin-top:8px;">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn-verify"><i class="fas fa-check"></i> Verifikasi Akun</button>
            </form>

            <form method="POST" action="{{ route('otp.resend') }}" style="margin-top:16px;text-align:center">
                @csrf
                <button type="submit" class="btn-verify btn-outline" style="padding:10px; font-size:0.85rem;"><i class="fas fa-sync"></i> Kirim Ulang OTP</button>
            </form>
            
            <!-- Tombol batalkan jika salah email -->
            <div style="margin-top:16px;text-align:center">
                <a href="{{ route('register') }}" style="color:#ef4444;font-size:0.85rem;text-decoration:underline;">Batalkan / Daftar Ulang</a>
            </div>
        </div>
    </div>
</body>
</html>
