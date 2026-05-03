<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Katalog Motor - Kremo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        :root { --ink:#0f172a; --muted:#64748b; --line:#dbe4ee; --bg:#f8fafc; --blue:#2563eb; --amber:#d97706; --green:#059669; --c-ink:#0d1117; --c-muted:#6b7a90; --c-line:#e8ecf2; --c-bg:#ffffff; --c-surface:#f6f8fb; --c-blue:#1c5de8; --c-blue-dk:#1448c0; --c-blue-lt:#edf2fd; --font:'Plus Jakarta Sans', system-ui, sans-serif; --sh-sm:0 1px 3px rgba(13,17,23,.06), 0 1px 2px rgba(13,17,23,.04); --sh-md:0 4px 16px rgba(13,17,23,.08), 0 2px 6px rgba(13,17,23,.04); --sh-bl:0 8px 28px rgba(28,93,232,.20); }
        body { font-family:var(--font); background:var(--bg); color:#334155; font-size:15px; line-height:1.65; -webkit-font-smoothing:antialiased; }
        a { text-decoration:none; color:inherit; }
        .container { max-width:1180px; margin:0 auto; padding:0 22px; }
        .site-header { position:sticky; top:0; z-index:100; background:rgba(255,255,255,.92); backdrop-filter:blur(12px); border-bottom:1px solid var(--c-line); }
        .nav { display:flex; align-items:center; justify-content:space-between; gap:20px; height:68px; }
        .brand { display:flex; align-items:center; gap:10px; color:var(--c-ink); font-size:17px; font-weight:800; letter-spacing:0; flex-shrink:0; }
        .brand-icon { width:36px; height:36px; display:grid; place-items:center; border-radius:8px; background:var(--c-blue); color:#fff; font-size:15px; }
        .nav-links { display:flex; align-items:center; gap:4px; }
        .nav-link { display:inline-flex; align-items:center; gap:6px; padding:7px 11px; border-radius:8px; color:var(--c-muted); font-size:14px; font-weight:600; transition:background .15s ease, color .15s ease; }
        .nav-link:hover { background:var(--c-surface); color:var(--c-ink); }
        .nav-sep { width:1px; height:22px; background:var(--c-line); margin:0 6px; }
        .nav-cta { display:flex; align-items:center; gap:8px; }
        .nav-toggle { display:none; width:42px; height:42px; border:1px solid var(--c-line); border-radius:8px; background:#fff; color:var(--c-ink); cursor:pointer; place-items:center; transition:background .18s ease, border-color .18s ease; }
        .nav-toggle:hover { background:var(--c-surface); border-color:#d0d7e2; }
        .nav-toggle-lines { display:grid; gap:5px; width:18px; }
        .nav-toggle-lines span { display:block; height:2px; border-radius:999px; background:currentColor; transition:transform .18s ease, opacity .18s ease; }
        .nav-toggle[aria-expanded="true"] .nav-toggle-lines span:nth-child(1) { transform:translateY(7px) rotate(45deg); }
        .nav-toggle[aria-expanded="true"] .nav-toggle-lines span:nth-child(2) { opacity:0; }
        .nav-toggle[aria-expanded="true"] .nav-toggle-lines span:nth-child(3) { transform:translateY(-7px) rotate(-45deg); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; height:46px; padding:0 22px; border:1.5px solid transparent; border-radius:8px; font-family:var(--font); font-size:14px; font-weight:700; line-height:1; cursor:pointer; white-space:nowrap; transition:transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease; }
        .btn:hover { transform:translateY(-1px); }
        .btn-primary { background:var(--c-blue); color:white; box-shadow:var(--sh-bl); }
        .btn-primary:hover { background:var(--c-blue-dk); box-shadow:0 12px 32px rgba(28,93,232,.28); }
        .btn-outline { border-color:var(--line); background:white; color:var(--ink); }
        .btn-ghost { background:transparent; border-color:var(--c-line); color:var(--c-ink); }
        .btn-ghost:hover { background:var(--c-surface); border-color:#d0d7e2; }
        .header { padding:34px 0 22px; display:flex; align-items:end; justify-content:space-between; gap:20px; }
        .header h1 { color:var(--ink); font-size:2.15rem; letter-spacing:0; }
        .header p { color:var(--muted); margin-top:8px; }
        .filters { background:white; border:1px solid var(--line); border-radius:8px; padding:14px; display:grid; grid-template-columns:2fr 1fr 1fr auto; gap:12px; margin-bottom:22px; }
        .control { width:100%; height:42px; border:1px solid var(--line); border-radius:8px; padding:0 12px; font:inherit; background:#fff; color:#334155; }
        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:18px; padding-bottom:30px; }
        .card { background:white; border:1px solid var(--line); border-radius:8px; overflow:hidden; transition:.2s ease; }
        .card:hover { transform:translateY(-3px); box-shadow:0 18px 36px rgba(15, 23, 42, .10); }
        .image { display:block; aspect-ratio:16/10; background:#eef2f7; }
        .image img { width:100%; height:100%; object-fit:cover; }
        .body { padding:16px; }
        .body h2 { color:var(--ink); font-size:1rem; margin-bottom:10px; }
        .chips { display:flex; flex-wrap:wrap; gap:7px; margin-bottom:14px; }
        .chip { border:1px solid var(--line); border-radius:999px; padding:5px 9px; font-size:.75rem; color:var(--muted); background:#fff; }
        .price { color:var(--amber); font-weight:800; font-size:1.15rem; }
        .installment { color:var(--muted); font-size:.8rem; margin-top:4px; }
        .footer { border-top:1px solid var(--line); padding:13px 16px; display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .stock { color:var(--green); font-size:.78rem; font-weight:800; }
        .actions { display:flex; gap:12px; align-items:center; }
        .link { color:var(--blue); font-weight:800; font-size:.85rem; }
        .apply { color:var(--green); font-weight:800; font-size:.85rem; }
        .empty { border:1px dashed var(--line); border-radius:8px; padding:46px; text-align:center; color:var(--muted); grid-column:1/-1; background:white; }
        .pagination { display:flex; justify-content:center; gap:4px; padding:16px 0 50px; }
        .pagination a, .pagination span { padding:8px 13px; border-radius:8px; font-size:.8rem; border:1px solid var(--line); color:#334155; }
        .pagination .active span { background:var(--blue); border-color:var(--blue); color:white; }
        .site-footer { border-top:1px solid var(--line); padding:30px 0; color:var(--muted); font-size:.85rem; }
        @media(max-width:820px) { .filters { grid-template-columns:1fr; } .header { display:block; } }
        @media(max-width:760px) {
            .nav { height:auto; min-height:68px; flex-wrap:wrap; }
            .nav-toggle { display:grid; margin-left:auto; }
            .nav-links { display:none; width:100%; flex-direction:column; align-items:stretch; gap:8px; padding:10px; border:1px solid var(--c-line); border-radius:12px; background:#fff; box-shadow:var(--sh-md); }
            .nav-links.is-open { display:flex; }
            .nav-link { display:flex; width:100%; justify-content:space-between; padding:11px 12px; font-size:14px; }
            .nav-sep { display:none; }
            .nav-cta { display:grid; width:100%; gap:8px; }
            .nav-cta .btn { width:100%; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <nav class="nav" aria-label="Navigasi utama">
                <a href="{{ route('landing') }}" class="brand">
                    <span class="brand-icon" aria-hidden="true"><i class="fas fa-motorcycle"></i></span>
                    Kremo
                </a>
                <button class="nav-toggle" type="button" data-nav-toggle aria-controls="catalog-nav" aria-expanded="false" aria-label="Buka menu navigasi">
                    <span class="nav-toggle-lines" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <div class="nav-links" id="catalog-nav" data-nav-menu>
                    <a href="{{ route('landing') }}#motor" class="nav-link">Motor</a>
                    <a href="{{ route('landing') }}#cicilan" class="nav-link">Cicilan</a>
                    <a href="{{ route('landing') }}#proses" class="nav-link">Proses</a>
                    <a href="{{ route('catalog') }}" class="nav-link">Katalog</a>
                    <div class="nav-sep" aria-hidden="true"></div>
                    <div class="nav-cta">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-gauge-high"></i> Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-ghost">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Daftar
                            </a>
                        @endauth
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <header class="header">
            <div>
                <h1>Katalog Motor</h1>
                <p>{{ $motors->total() }} motor tersedia untuk pengajuan kredit.</p>
            </div>
            <a href="{{ route('landing') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Beranda</a>
        </header>

        <form method="GET" action="{{ route('catalog') }}" class="filters">
            <input class="control" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama motor, warna, atau cc">
            <select class="control" name="jenis">
                <option value="">Semua kategori</option>
                @foreach($jenisMotor as $jenis)
                    <option value="{{ $jenis->id }}" {{ (string) request('jenis') === (string) $jenis->id ? 'selected' : '' }}>{{ $jenis->merk }} - {{ $jenis->jenis }}</option>
                @endforeach
            </select>
            <select class="control" name="sort">
                <option value="">Terbaru</option>
                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga termurah</option>
                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga tertinggi</option>
            </select>
            <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Terapkan</button>
        </form>

        <main class="grid">
            @forelse($motors as $motor)
                @php($monthly = ceil(($motor->harga_jual * 1.12 / 36) / 1000) * 1000)
                <article class="card">
                    <a href="{{ route('motor.detail', $motor) }}" class="image"><img src="{{ $motor->primary_image_url }}" alt="{{ $motor->nama_motor }}"></a>
                    <div class="body">
                        <h2>{{ $motor->nama_motor }}</h2>
                        <div class="chips">
                            <span class="chip">{{ $motor->jenisMotor->merk ?? '-' }}</span>
                            <span class="chip">{{ $motor->kapasitas_mesin ?? '-' }}</span>
                            <span class="chip">{{ $motor->warna ?? '-' }}</span>
                            <span class="chip">{{ $motor->tahun_produksi ?? '-' }}</span>
                        </div>
                        <div class="price">Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</div>
                        <div class="installment">Estimasi Rp {{ number_format($monthly, 0, ',', '.') }}/bulan</div>
                    </div>
                    <div class="footer">
                        <span class="stock">{{ $motor->stok }} unit</span>
                        <div class="actions">
                            <a href="{{ route('motor.detail', $motor) }}" class="link">Detail</a>
                            <a href="{{ route('pengajuan.create', ['motor_id' => $motor->id]) }}" class="apply">Ajukan</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty"><i class="fas fa-motorcycle" style="font-size:2rem;margin-bottom:10px;"></i><p>Motor tidak ditemukan.</p></div>
            @endforelse
        </main>

        <div class="pagination">{{ $motors->links('pagination.simple') }}</div>
    </div>

    <footer class="site-footer"><div class="container">&copy; {{ date('Y') }} Kremo. Kredit motor online.</div></footer>
    <script>
        (() => {
            const navToggle = document.querySelector('[data-nav-toggle]');
            const navMenu = document.querySelector('[data-nav-menu]');

            if (!navToggle || !navMenu) {
                return;
            }

            const closeMenu = () => {
                navMenu.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Buka menu navigasi');
            };

            navToggle.addEventListener('click', () => {
                const isOpen = navMenu.classList.toggle('is-open');
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                navToggle.setAttribute('aria-label', isOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi');
            });

            navMenu.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', closeMenu);
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 760) {
                    closeMenu();
                }
            });
        })();
    </script>
</body>
</html>
