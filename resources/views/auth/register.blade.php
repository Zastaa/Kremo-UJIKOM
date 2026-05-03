<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Daftar - Kremo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-container { width: 100%; max-width: 540px; padding: 20px; }
        .auth-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01); }
        .auth-brand { text-align: center; margin-bottom: 32px; }
        .auth-brand h1 { font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, #2563eb, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .auth-brand p { color: #64748b; font-size: 0.9rem; margin-top: 4px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .form-group { position: relative; margin-bottom: 16px; }
        .form-group.full-width { grid-column: 1 / -1; margin-bottom: 0; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; color: #334155; font-size: 0.875rem; font-family: inherit; transition: all 0.2s; }
        .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); background: #ffffff; }
        .btn-register { width: 100%; padding: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.3s; margin-top: 16px; }
        .btn-register:hover { box-shadow: 0 4px 15px rgba(59,130,246,0.3); transform: translateY(-1px); }
        .auth-link { text-align: center; margin-top: 24px; font-size: 0.85rem; color: #64748b; }
        .auth-link a { color: #3b82f6; text-decoration: none; font-weight: 600; transition: color 0.2s; }
        .auth-link a:hover { color: #2563eb; text-decoration: underline; }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; padding: 12px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px; }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none; }
        .password-field { position: relative; }
        .password-field .form-control { padding-right: 42px; }
        .toggle-password { position: absolute; right: 7px; top: 50%; transform: translateY(-50%); width: 32px; height: 32px; background: none; border: none; color: #94a3b8; cursor: pointer; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .toggle-password:hover, .toggle-password:focus { color: #475569; background: #eef2f7; outline: none; }
        @media (max-width: 480px) {
            .form-grid { grid-template-columns: 1fr; gap: 0; }
            .form-group { margin-bottom: 16px; }
            .form-group.full-width { margin-bottom: 16px; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-brand">
                <h1><i class="fas fa-motorcycle"></i> Kremo</h1>
                <p>Buat akun baru</p>
            </div>

            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nama Anda" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="no_telp">No. Telepon</label>
                        <input type="text" id="no_telp" name="no_telp" class="form-control" maxlength="12" inputmode="numeric" pattern="[0-9]*" value="{{ old('no_telp') }}" placeholder="08xxxxxxxx">
                    </div>
                    <div class="form-group full-width">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="nama@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Min. 8 karakter" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <div class="password-field">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" data-password-toggle="password_confirmation" aria-label="Tampilkan password" aria-pressed="false">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-register"><i class="fas fa-user-plus"></i> Daftar Sekarang</button>
            </form>
            <div class="auth-link">
                Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
            </div>
        </div>
    </div>
    <script>
        const phoneInput = document.getElementById('no_telp');
        phoneInput?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 12);
        });

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
