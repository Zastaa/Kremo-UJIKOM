<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Reset Password - Kremo</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0 }
        body { font-family:'Inter',sans-serif; background:#f8fafc; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px }
        .card { background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; width:100%; max-width:440px; overflow:hidden; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05) }
        .card-header { padding:30px; border-bottom:1px solid #e2e8f0; text-align:center }
        .card-header h1 { font-size:1.5rem; background:linear-gradient(135deg,#2563eb,#3b82f6); -webkit-background-clip:text; -webkit-text-fill-color:transparent }
        .card-body { padding:30px }
        .form-group { margin-bottom:20px; position:relative; }
        .form-group label { display:block; font-size:0.8rem; font-weight:600; color:#475569; margin-bottom:8px }
        .form-control { width:100%; padding:10px 14px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; color:#334155; font-size:0.875rem; font-family:inherit }
        .form-control:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.15); background:#ffffff }
        .form-error { color:#ef4444; font-size:0.8rem; margin-top:4px }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 20px; border-radius:8px; font-size:0.875rem; font-weight:600; border:none; cursor:pointer; width:100%; color:white; background:linear-gradient(135deg,#3b82f6,#2563eb) }
        .btn:hover { box-shadow:0 4px 20px rgba(59,130,246,0.3); transform:translateY(-1px) }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display:none }
        .password-field { position:relative }
        .password-field .form-control { padding-right:44px }
        .toggle-password { position:absolute; right:8px; top:50%; transform:translateY(-50%); width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; background:none; border:none; color:#94a3b8; cursor:pointer; border-radius:8px }
        .toggle-password:hover, .toggle-password:focus { color:#475569; background:#eef2f7; outline:none }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-motorcycle"></i> Kremo</h1>
            <p style="color:#64748b;font-size:0.85rem;margin-top:8px">Reset Password</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.reset') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
                <div class="form-group">
                    <label>Kode OTP</label>
                    <input type="text" name="otp" class="form-control" maxlength="6" placeholder="000000" style="text-align:center;font-size:1.3rem;letter-spacing:6px" required autofocus>
                    @error('otp')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required>
                        <button type="button" class="toggle-password" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false"><i class="far fa-eye"></i></button>
                    </div>
                    @error('password')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <div class="password-field">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                        <button type="button" class="toggle-password" data-password-toggle="password_confirmation" aria-label="Tampilkan password" aria-pressed="false"><i class="far fa-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn"><i class="fas fa-key"></i> Reset Password</button>
            </form>
        </div>
    </div>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function(button) {
            button.addEventListener('click', function() {
                const input = document.getElementById(button.dataset.passwordToggle);
                const icon = button.querySelector('i');
                if (!input || !icon) return;

                const shouldShow = input.type === 'password';
                input.type = shouldShow ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !shouldShow);
                icon.classList.toggle('fa-eye-slash', shouldShow);
                button.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Tampilkan password');
                button.setAttribute('aria-pressed', String(shouldShow));
            });
        });
    </script>
</body>
</html>
