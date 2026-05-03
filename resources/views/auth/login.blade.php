<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Login - Kremo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-container { width: 100%; max-width: 420px; padding: 20px; }
        .auth-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .auth-brand { text-align: center; margin-bottom: 32px; }
        .auth-brand h1 { font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, #2563eb, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .auth-brand p { color: #64748b; font-size: 0.9rem; margin-top: 4px; }
        .form-group { margin-bottom: 20px; position: relative; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; color: #334155; font-size: 0.9rem; font-family: inherit; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); background: #ffffff; }
        .btn-login { width: 100%; padding: 14px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 10px; font-size: 0.95rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.3s; }
        .btn-login:hover { box-shadow: 0 4px 20px rgba(59,130,246,0.3); transform: translateY(-1px); }
        .auth-link { text-align: center; margin-top: 20px; font-size: 0.85rem; color: #64748b; }
        .auth-link a { color: #3b82f6; text-decoration: none; font-weight: 600; }
        .auth-link a:hover { color: #2563eb; }
        .error { color: #ef4444; font-size: 0.8rem; margin-top: 4px; }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; padding: 12px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px; }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none; }
        .password-field { position: relative; }
        .password-field .form-control { padding-right: 44px; }
        .toggle-password { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; background: none; border: none; color: #94a3b8; cursor: pointer; border-radius: 8px; }
        .toggle-password:hover, .toggle-password:focus { color: #475569; background: #eef2f7; outline: none; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-brand">
                <h1><i class="fas fa-motorcycle"></i> Kremo</h1>
                <p>Masuk ke akun Anda</p>
            </div>

            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div style="text-align: right; margin-top: -12px; margin-bottom: 20px;">
                    <a href="{{ route('password.forgot') }}" style="color: #3b82f6; text-decoration: none; font-size: 0.8rem; font-weight: 500;">Lupa Password?</a>
                </div>
                <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Masuk</button>
            </form>
            <div class="auth-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
            </div>
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
