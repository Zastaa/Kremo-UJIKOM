<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Kremo') - Kredit Motor Online</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #2563eb; --primary-dark: #1d4ed8; --primary-light: #3b82f6;
            --accent: #d97706; --accent-dark: #b45309;
            --bg: #f6f8fb; --bg-card: #ffffff; --bg-sidebar: #ffffff;
            --text: #334155; --text-muted: #64748b; --text-heading: #0f172a;
            --border: #dbe4ee; --success: #059669; --danger: #dc2626; --warning: #d97706; --info: #2563eb;
            --radius: 8px; --shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; line-height: 1.5; }
        a { color: var(--primary-light); text-decoration: none; transition: color 0.2s; }
        a:hover { color: var(--accent); }

        .app-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--bg-sidebar); border-right: 1px solid var(--border); padding: 0; position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto; z-index: 50; transition: transform 0.3s; }
        .sidebar-brand { padding: 24px 20px; border-bottom: 1px solid var(--border); }
        .sidebar-brand h1 { font-size: 1.5rem; font-weight: 800; color: var(--text-heading); letter-spacing: 0; }
        .sidebar-brand h1 i { color: var(--primary); }
        .sidebar-brand small { color: var(--text-muted); font-size: 0.75rem; }
        .sidebar-nav { padding: 16px 12px; }
        .sidebar-nav .nav-section { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); padding: 16px 12px 8px; }
        .sidebar-nav a { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 8px; color: var(--text); font-size: 0.875rem; font-weight: 600; transition: all 0.2s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: #eff6ff; color: var(--primary-dark); }
        .sidebar-nav a i { width: 20px; text-align: center; font-size: 0.9rem; }

        .main-content { margin-left: 260px; flex: 1; min-height: 100vh; }
        .topbar { background: rgba(255,255,255,0.92); border-bottom: 1px solid var(--border); padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; backdrop-filter: blur(10px); }
        .topbar h2 { font-size: 1.125rem; font-weight: 750; color: var(--text-heading); letter-spacing: 0; }
        .topbar-actions { display: flex; align-items: center; gap: 16px; }
        .topbar-user { display: flex; align-items: center; gap: 10px; font-size: 0.875rem; }
        .topbar-user .avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; color: white; }
        .topbar-user .role-badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 20px; background: #eff6ff; color: var(--primary-dark); font-weight: 700; text-transform: uppercase; }

        .page-content { padding: 32px; max-width: 1480px; margin: 0 auto; }

        /* Cards */
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; box-shadow: 0 1px 2px rgba(15,23,42,0.04); }
        .card-header { padding: 18px 22px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; background: #fff; }
        .card-header h3 { font-size: 1rem; font-weight: 750; color: var(--text-heading); letter-spacing: 0; }
        .card-body { padding: 24px; }

        /* Stat Cards */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 22px; display: flex; align-items: center; gap: 16px; transition: transform 0.2s, box-shadow 0.2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
        .stat-icon { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .stat-icon.purple { background: rgba(59,130,246,0.15); color: var(--primary); }
        .stat-icon.amber { background: rgba(245,158,11,0.15); color: var(--accent); }
        .stat-icon.green { background: rgba(16,185,129,0.15); color: var(--success); }
        .stat-icon.blue { background: rgba(59,130,246,0.15); color: var(--info); }
        .stat-icon.red { background: rgba(239,68,68,0.15); color: var(--danger); }
        .stat-info h4 { font-size: 1.5rem; font-weight: 700; color: var(--text-heading); }
        .stat-info p { font-size: 0.8rem; color: var(--text-muted); }

        /* Tables */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px 16px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); background: #f8fafc; border-bottom: 1px solid var(--border); }
        td { padding: 14px 16px; font-size: 0.875rem; border-bottom: 1px solid var(--border); }
        tr:hover td { background: #f8fafc; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-size: 0.875rem; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-success:hover { background: #059669; color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #dc2626; color: white; }
        .btn-warning { background: var(--warning); color: #1e293b; }
        .btn-warning:hover { background: var(--accent-dark); color: white; }
        .btn-info { background: var(--info); color: white; }
        .btn-info:hover { background: #2563eb; color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .btn-sm { padding: 6px 14px; font-size: 0.8rem; }
        .btn-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn:disabled, .btn[disabled] { opacity: 0.58; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn:disabled:hover, .btn[disabled]:hover { border-color: var(--border); color: var(--text); }

        /* Forms */
        .form-group { margin-bottom: 18px; display: flex; flex-direction: column; gap: 8px; }
        .form-group label { display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-heading); margin-bottom: 0; letter-spacing: 0; }
        .form-group label small { display: inline-block; margin-left: 4px; color: var(--text-muted); font-weight: 500; line-height: 1.4; }
        .form-control { width: 100%; min-height: 42px; padding: 10px 13px; background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 0.9rem; font-family: inherit; line-height: 1.45; transition: border-color 0.2s, box-shadow 0.2s, background 0.2s; }
        .form-control:hover { border-color: #c5d1df; background-color: #fff; }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.14); background-color: #fff; }
        .form-control::placeholder { color: #94a3b8; }
        .form-control:disabled,
        .form-control[readonly] { background: #eef2f7; color: #64748b; cursor: not-allowed; }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none; }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 10px center; background-repeat: no-repeat; background-size: 20px; padding-right: 36px; }
        textarea.form-control { resize: vertical; min-height: 108px; }
        input[type="file"].form-control { padding: 8px 12px; background: #fff; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); column-gap: 18px; row-gap: 2px; align-items: start; }
        .form-error { color: var(--danger); font-size: 0.8rem; margin-top: 4px; }
        .form-section { border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; background: #fff; }
        .form-section + .form-section { margin-top: 18px; }
        .form-section-title { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; color: var(--text-heading); font-size: 0.95rem; font-weight: 800; }
        .form-section-title i { color: var(--primary); }
        .form-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); }
        .field-hint { color: var(--text-muted); font-size: 0.78rem; line-height: 1.5; margin-top: -2px; }
        .password-field { position: relative; }
        .password-field .form-control { padding-right: 44px; }
        .password-toggle { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; background: transparent; border: none; border-radius: 8px; color: #94a3b8; cursor: pointer; }
        .password-toggle:hover, .password-toggle:focus { color: var(--text); background: #eef2f7; outline: none; }

        /* Badges */
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: rgba(16,185,129,0.15); color: var(--success); }
        .badge-danger { background: rgba(239,68,68,0.15); color: var(--danger); }
        .badge-warning { background: rgba(245,158,11,0.15); color: var(--warning); }
        .badge-info { background: rgba(59,130,246,0.15); color: var(--info); }
        .badge-purple { background: rgba(59,130,246,0.15); color: var(--primary); }

        /* Alerts */
        .alert { padding: 14px 20px; border-radius: 8px; font-size: 0.875rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: var(--success); }
        .alert-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: var(--danger); }
        .alert-info { background: rgba(37,99,235,0.08); border: 1px solid rgba(37,99,235,0.2); color: var(--primary-dark); }
        .alert-warning { background: rgba(217,119,6,0.1); border: 1px solid rgba(217,119,6,0.24); color: var(--accent-dark); }

        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 4px; padding: 20px 0; }
        .pagination a, .pagination span { padding: 8px 14px; border-radius: 6px; font-size: 0.8rem; border: 1px solid var(--border); color: var(--text); }
        .pagination a:hover { background: rgba(59,130,246,0.15); border-color: var(--primary); color: var(--primary); }
        .pagination .active span { background: var(--primary); border-color: var(--primary); color: white; }

        /* Empty state */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 3rem; margin-bottom: 16px; opacity: 0.5; }
        .empty-state p { font-size: 0.9rem; }

        /* Responsive */
        .sidebar-toggle { display: none; background: none; border: none; color: var(--text); font-size: 1.2rem; cursor: pointer; padding: 8px; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: block; }
            .topbar { padding: 12px 18px; }
            .topbar-user > div:not(.avatar) { display: none; }
            .page-content { padding: 22px 16px; }
            .stat-grid { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .form-section { padding: 16px; }
            .form-actions .btn { width: 100%; justify-content: center; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <h1><i class="fas fa-motorcycle"></i> Kremo</h1>
                <small>Kredit Motor Online</small>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('landing') }}">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </a>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>

                @if(auth()->user()->hasRole(['admin']))
                <div class="nav-section">Master Data</div>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i> Pengguna
                </a>
                <a href="{{ route('metode-bayar.index') }}" class="{{ request()->routeIs('metode-bayar.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> Metode Bayar
                </a>
                @endif

                @if(auth()->user()->hasRole(['admin', 'marketing']))
                <div class="nav-section">Katalog</div>
                <a href="{{ route('motors.index') }}" class="{{ request()->routeIs('motors.*') ? 'active' : '' }}">
                    <i class="fas fa-motorcycle"></i> Motor
                </a>
                <a href="{{ route('jenis-cicilan.index') }}" class="{{ request()->routeIs('jenis-cicilan.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> Tenor
                </a>
                <a href="{{ route('asuransi.index') }}" class="{{ request()->routeIs('asuransi.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i> Asuransi
                </a>

                <div class="nav-section">Pelanggan</div>
                <a href="{{ route('pelanggan.index') }}" class="{{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Pelanggan
                </a>
                @endif

                <div class="nav-section">Kredit</div>
                <a href="{{ route('pengajuan.index') }}" class="{{ request()->routeIs('pengajuan.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i> Pengajuan
                </a>
                <a href="{{ route('kredit.index') }}" class="{{ request()->routeIs('kredit.*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-usd"></i> Kredit Aktif
                </a>
                <a href="{{ route('angsuran.index') }}" class="{{ request()->routeIs('angsuran.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> Angsuran
                </a>

                @if(auth()->user()->hasRole(['customer']))
                <div class="nav-section">Akun</div>
                <a href="{{ route('customer.profile.edit') }}" class="{{ request()->routeIs('customer.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-id-card"></i> Data Pribadi
                </a>
                @endif

                @if(auth()->user()->hasRole(['admin', 'marketing']))
                <div class="nav-section">Lainnya</div>
                <a href="{{ route('pengiriman.index') }}" class="{{ request()->routeIs('pengiriman.*') ? 'active' : '' }}">
                    <i class="fas fa-truck"></i> Pengiriman
                </a>
                @endif

                @if(auth()->user()->hasRole(['admin']))
                <div class="nav-section">Laporan</div>
                <a href="{{ route('reports.orders') }}" class="{{ request()->routeIs('reports.orders') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Laporan Order
                </a>
                <a href="{{ route('reports.credits') }}" class="{{ request()->routeIs('reports.credits') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> Laporan Kredit
                </a>
                <a href="{{ route('reports.payments') }}" class="{{ request()->routeIs('reports.payments') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Laporan Pembayaran
                </a>
                <a href="{{ route('reports.user-performance') }}" class="{{ request()->routeIs('reports.user-performance') ? 'active' : '' }}">
                    <i class="fas fa-users-gear"></i> Kinerja User
                </a>
                @endif

                @if(auth()->user()->hasRole(['admin', 'marketing']))
                <div class="nav-section">Tools</div>
                <a href="{{ route('scanner.index') }}" class="{{ request()->routeIs('scanner.*') ? 'active' : '' }}">
                    <i class="fas fa-qrcode"></i> Scanner QR
                </a>
                @endif

                @if(app()->environment(['local', 'testing']) && auth()->user()->hasRole('admin'))
                <div class="nav-section">Simulasi</div>
                <a href="{{ route('dev.time-travel.show') }}" class="{{ request()->routeIs('dev.time-travel.*') ? 'active' : '' }}">
                    <i class="fas fa-clock-rotate-left"></i> Simulasi Waktu
                </a>
                @endif

                @if(auth()->user()->hasRole(['admin']))
                <a href="{{ route('email-logs.index') }}" class="{{ request()->routeIs('email-logs.*') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i> Log Email
                </a>
                @endif
            </nav>
        </aside>

        <div class="main-content">
            <div class="topbar">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2>@yield('page-title', 'Dashboard')</h2>
                </div>
                <div class="topbar-actions">
                    <div class="topbar-user">
                        <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                        <div>
                            <div style="font-weight:600;color:var(--text-heading);">{{ auth()->user()->name }}</div>
                            <span class="role-badge">{{ auth()->user()->role }}</span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

            <div class="page-content">
                @if(app()->environment(['local', 'testing']) && \App\Http\Middleware\ApplySimulatedTime::simulatedAt())
                    <div class="alert alert-warning">
                        <i class="fas fa-clock-rotate-left"></i>
                        <span>
                            Simulasi waktu aktif: <strong>{{ now()->format('d/m/Y H:i') }}</strong>.
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('dev.time-travel.show') }}">Ubah atau reset</a>
                            @endif
                        </span>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && !e.target.closest('.sidebar') && !e.target.closest('.sidebar-toggle')) {
                document.getElementById('sidebar').classList.remove('open');
            }
        });

        // SweetAlert Notifications
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{!! session('success') !!}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{!! session('error') !!}',
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                html: `
                    <ul style="text-align:left; margin-top:10px; color:#ef4444;">
                        @foreach(collect($errors->all())->unique() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `
            });
        @endif

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

        document.querySelectorAll('[data-phone-input]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.replace(/\D/g, '').slice(0, 12);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
