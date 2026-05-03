<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Lupa Password - Kremo</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0 }
        body { font-family:'Inter',sans-serif; background:#f8fafc; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px }
        .card { background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; width:100%; max-width:440px; overflow:hidden; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05) }
        .card-header { padding:30px; border-bottom:1px solid #e2e8f0; text-align:center }
        .card-header h1 { font-size:1.5rem; background:linear-gradient(135deg,#2563eb,#3b82f6); -webkit-background-clip:text; -webkit-text-fill-color:transparent }
        .card-body { padding:30px }
        .form-group { margin-bottom:20px }
        .form-group label { display:block; font-size:0.8rem; font-weight:600; color:#475569; margin-bottom:8px }
        .form-control { width:100%; padding:10px 14px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; color:#334155; font-size:0.875rem; font-family:inherit; transition:border-color 0.2s }
        .form-control:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.15); background:#ffffff }
        .form-error { color:#ef4444; font-size:0.8rem; margin-top:4px }
        .alert { padding:14px; border-radius:8px; font-size:0.85rem; margin-bottom:20px }
        .alert-success { background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#10b981 }
        .alert-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444 }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 20px; border-radius:8px; font-size:0.875rem; font-weight:600; border:none; cursor:pointer; width:100%; color:white; background:linear-gradient(135deg,#3b82f6,#2563eb) }
        .btn:hover { box-shadow:0 4px 20px rgba(59,130,246,0.3); transform:translateY(-1px) }
        a { color:#3b82f6; text-decoration:none }
        a:hover { color:#2563eb }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-motorcycle"></i> Kremo</h1>
            <p style="color:#64748b;font-size:0.85rem;margin-top:8px">Lupa Password</p>
        </div>
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
            <p style="color:#475569;margin-bottom:20px;font-size:0.9rem;text-align:center">Masukkan email Anda untuk menerima kode reset password.</p>
            <form method="POST" action="{{ route('password.forgot.send') }}">
                @csrf
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Kirim Kode OTP</button>
            </form>
            <p style="text-align:center;margin-top:20px"><a href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Kembali ke Login</a></p>
        </div>
    </div>
</body>
</html>
