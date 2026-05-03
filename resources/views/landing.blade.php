<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Kremo - Kredit Motor Online</title>
    <meta name="description" content="Kredit motor online dengan cicilan jelas, pengajuan mudah, dan status yang bisa dipantau langsung dari dashboard.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --c-ink:     #0d1117;
            --c-text:    #3d4a5c;
            --c-muted:   #6b7a90;
            --c-subtle:  #9aa5b4;
            --c-line:    #e8ecf2;
            --c-bg:      #ffffff;
            --c-surface: #f6f8fb;
            --c-blue:    #1c5de8;
            --c-blue-dk: #1448c0;
            --c-blue-lt: #edf2fd;
            --c-blue-md: #dce8fb;
            --font:      'Plus Jakarta Sans', system-ui, sans-serif;
            --sh-sm: 0 1px 3px rgba(13,17,23,.06), 0 1px 2px rgba(13,17,23,.04);
            --sh-md: 0 4px 16px rgba(13,17,23,.08), 0 2px 6px rgba(13,17,23,.04);
            --sh-lg: 0 12px 40px rgba(13,17,23,.10), 0 4px 12px rgba(13,17,23,.04);
            --sh-bl: 0 8px 28px rgba(28,93,232,.20);
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--c-bg);
            color: var(--c-text);
            font-family: var(--font);
            font-size: 15px;
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }

        .container { width: min(100%, 1160px); margin: 0 auto; padding: 0 20px; }

        /* ── Eyebrow ── */
        .eyebrow {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 100px;
            background: var(--c-blue-lt); color: var(--c-blue);
            font-size: 12px; font-weight: 700;
            letter-spacing: .04em; text-transform: uppercase;
            margin-bottom: 16px;
        }
        .eyebrow i { font-size: 10px; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 46px; padding: 0 22px; border: 1.5px solid transparent;
            border-radius: 8px; font-family: var(--font); font-size: 14px;
            font-weight: 700; line-height: 1; cursor: pointer; white-space: nowrap;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary  { background: var(--c-blue); color: #fff; box-shadow: var(--sh-bl); }
        .btn-primary:hover { background: var(--c-blue-dk); box-shadow: 0 12px 32px rgba(28,93,232,.28); }
        .btn-ghost    { background: transparent; border-color: var(--c-line); color: var(--c-ink); }
        .btn-ghost:hover { background: var(--c-surface); border-color: #d0d7e2; }
        .btn-soft-blue { background: var(--c-blue-lt); border-color: var(--c-blue-md); color: var(--c-blue); }
        .btn-soft-blue:hover { background: var(--c-blue-md); }

        /* ── Header ── */
        .site-header {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255,255,255,.92); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--c-line);
        }
        .nav {
            display: flex; align-items: center; justify-content: space-between;
            gap: 20px; height: 68px;
        }
        .brand {
            display: flex; align-items: center; gap: 10px;
            color: var(--c-ink); font-size: 17px; font-weight: 800;
            letter-spacing: -.02em; flex-shrink: 0;
        }
        .brand-icon {
            width: 36px; height: 36px; display: grid; place-items: center;
            border-radius: 8px; background: var(--c-blue); color: #fff; font-size: 15px;
        }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-link {
            display: inline-flex; align-items: center; gap: 6px; padding: 7px 11px;
            border-radius: 8px; color: var(--c-muted); font-size: 14px; font-weight: 600;
            transition: background .15s ease, color .15s ease;
        }
        .nav-link:hover { background: var(--c-surface); color: var(--c-ink); }
        .nav-sep { width: 1px; height: 22px; background: var(--c-line); margin: 0 6px; }
        .nav-cta { display: flex; align-items: center; gap: 8px; }
        .nav-toggle {
            display: none; width: 42px; height: 42px; border: 1px solid var(--c-line);
            border-radius: 8px; background: #fff; color: var(--c-ink); cursor: pointer;
            place-items: center; transition: background .18s ease, border-color .18s ease;
        }
        .nav-toggle:hover { background: var(--c-surface); border-color: #d0d7e2; }
        .nav-toggle-lines { display: grid; gap: 5px; width: 18px; }
        .nav-toggle-lines span {
            display: block; height: 2px; border-radius: 999px; background: currentColor;
            transition: transform .18s ease, opacity .18s ease;
        }
        .nav-toggle[aria-expanded="true"] .nav-toggle-lines span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .nav-toggle[aria-expanded="true"] .nav-toggle-lines span:nth-child(2) { opacity: 0; }
        .nav-toggle[aria-expanded="true"] .nav-toggle-lines span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* ── Hero ── */
        .hero { padding: 64px 0 48px; position: relative; overflow: hidden; }
        .hero::before {
            content: ''; position: absolute; top: -100px; right: -180px;
            width: 560px; height: 560px; border-radius: 50%; pointer-events: none;
            background: radial-gradient(circle, rgba(28,93,232,.07) 0%, transparent 70%);
        }
        .hero-grid { display: grid; gap: 40px; align-items: center; }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px;
            border-radius: 100px; background: var(--c-blue-lt); border: 1px solid var(--c-blue-md);
            color: var(--c-blue); font-size: 12.5px; font-weight: 700; margin-bottom: 20px;
        }
        .hero-badge .dot {
            width: 6px; height: 6px; border-radius: 50%; background: var(--c-blue);
            animation: blink 2s infinite;
        }
        @keyframes blink {
            0%,100% { opacity:1; transform:scale(1); }
            50% { opacity:.4; transform:scale(.75); }
        }

        .hero h1 {
            color: var(--c-ink); font-size: 2.55rem; font-weight: 800;
            line-height: 1.1; letter-spacing: -.03em; max-width: 580px;
        }
        .hero h1 .hl { color: var(--c-blue); }

        .hero-desc {
            max-width: 500px; margin-top: 18px; color: var(--c-muted);
            font-size: 15.5px; line-height: 1.7;
        }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 28px; }

        .hero-trust { display: flex; flex-direction: column; gap: 8px; margin-top: 24px; }
        .trust-item { display: flex; align-items: center; gap: 8px; color: var(--c-muted); font-size: 13.5px; font-weight: 500; }
        .trust-icon {
            width: 20px; height: 20px; display: grid; place-items: center;
            border-radius: 50%; background: #e6f4ee; color: #1e9159;
            font-size: 9px; flex-shrink: 0;
        }

        /* ── Motor Card (Hero) ── */
        .hero-card-wrap { position: relative; }
        .hero-card-wrap::before {
            content: ''; position: absolute; inset: -14px; border-radius: 24px; z-index: -1;
            background: linear-gradient(135deg, var(--c-blue-lt) 0%, transparent 55%);
        }
        .hero-carousel-track { display: grid; }
        .hero-slide {
            grid-area: 1 / 1; opacity: 0; pointer-events: none;
            transform: translateX(14px) scale(.985);
            transition: opacity .36s ease, transform .36s ease;
        }
        .hero-slide.is-active { opacity: 1; pointer-events: auto; transform: translateX(0) scale(1); }
        .hero-carousel-ui {
            display: flex; align-items: center; justify-content: space-between;
            gap: 14px; margin-top: 14px;
        }
        .hero-carousel-actions { display: flex; gap: 8px; }
        .hero-carousel-btn {
            width: 38px; height: 38px; display: grid; place-items: center;
            border: 1px solid var(--c-line); border-radius: 999px; background: #fff;
            color: var(--c-ink); box-shadow: var(--sh-sm); cursor: pointer;
            transition: transform .18s ease, border-color .18s ease, background .18s ease;
        }
        .hero-carousel-btn:hover { transform: translateY(-1px); border-color: #cdd5e0; background: var(--c-surface); }
        .hero-carousel-dots { display: flex; align-items: center; gap: 7px; }
        .hero-carousel-dot {
            width: 8px; height: 8px; border: 0; border-radius: 999px;
            background: #cfd6e2; cursor: pointer; transition: width .18s ease, background .18s ease;
        }
        .hero-carousel-dot.is-active { width: 24px; background: var(--c-blue); }
        .motor-card {
            background: var(--c-bg); border: 1px solid var(--c-line);
            border-radius: 16px; box-shadow: var(--sh-lg); overflow: hidden;
            transition: transform .25s ease, box-shadow .25s ease; display: block;
        }
        .motor-card:hover { transform: translateY(-4px); box-shadow: 0 20px 56px rgba(13,17,23,.12); }
        .card-img { aspect-ratio: 16/10; overflow: hidden; background: var(--c-surface); }
        .card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
        .motor-card:hover .card-img img { transform: scale(1.04); }
        .card-body { padding: 20px; }
        .card-meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
        .card-tag { font-size: 12px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: .04em; }
        .card-stock {
            display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px;
            border-radius: 100px; background: #e6f4ee; color: #187c47; font-size: 11.5px; font-weight: 700;
        }
        .card-stock::before { content:''; width:5px; height:5px; border-radius:50%; background:currentColor; }
        .card-name { color: var(--c-ink); font-size: 17px; font-weight: 800; line-height: 1.3; letter-spacing: -.02em; }
        .card-prices {
            display: flex; align-items: flex-end; justify-content: space-between; gap: 12px;
            margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--c-line);
        }
        .card-cash { color: var(--c-muted); font-size: 12.5px; font-weight: 600; }
        .card-cash strong { display: block; color: var(--c-text); font-size: 14px; margin-top: 2px; }
        .card-monthly { text-align: right; }
        .card-monthly-lbl { font-size: 11.5px; font-weight: 600; color: var(--c-muted); }
        .card-monthly-val { color: var(--c-blue); font-size: 19px; font-weight: 800; letter-spacing: -.02em; line-height: 1.1; }
        .card-monthly-val small { font-size: 12px; font-weight: 600; color: var(--c-muted); }

        /* ── Stats Strip ── */
        .stats-strip {
            display: grid; grid-template-columns: repeat(4, 1fr);
            margin-top: 44px; border: 1px solid var(--c-line);
            border-radius: 12px; background: var(--c-bg); overflow: hidden;
        }
        .stat-item { padding: 18px 20px; border-right: 1px solid var(--c-line); }
        .stat-item:last-child { border-right: none; }
        .stat-val { color: var(--c-ink); font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; line-height: 1; }
        .stat-lbl { margin-top: 4px; color: var(--c-muted); font-size: 12.5px; font-weight: 500; }

        /* ── Section Layout ── */
        .section    { padding: 88px 0; }
        .section-sm { padding: 56px 0; }
        .band { background: var(--c-surface); border-top: 1px solid var(--c-line); border-bottom: 1px solid var(--c-line); }

        .section-hd {
            display: flex; align-items: flex-end; justify-content: space-between;
            gap: 24px; margin-bottom: 32px;
        }
        .section-hd-text h2 {
            color: var(--c-ink); font-size: 1.6rem; font-weight: 800;
            letter-spacing: -.025em; line-height: 1.2; max-width: 460px;
        }
        .section-hd-text p { margin-top: 8px; color: var(--c-muted); font-size: 14.5px; max-width: 440px; }
        .section-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: var(--c-blue); font-size: 14px; font-weight: 700;
            flex-shrink: 0; transition: gap .15s ease;
        }
        .section-link:hover { gap: 10px; }

        /* ── Product Rows ── */
        .product-list { display: grid; gap: 10px; }
        .product-row {
            display: grid; grid-template-columns: 88px 1fr;
            gap: 16px; align-items: center; padding: 14px 16px;
            background: var(--c-bg); border: 1px solid var(--c-line);
            border-radius: 12px; box-shadow: var(--sh-sm);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .product-row:hover { transform: translateY(-2px); border-color: #cdd5e0; box-shadow: var(--sh-md); }
        .product-thumb { width: 88px; height: 88px; border-radius: 8px; overflow: hidden; background: var(--c-surface); flex-shrink: 0; }
        .product-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .25s ease; }
        .product-row:hover .product-thumb img { transform: scale(1.06); }
        .product-name { color: var(--c-ink); font-size: 14.5px; font-weight: 700; letter-spacing: -.01em; line-height: 1.3; }
        .product-spec { margin-top: 4px; color: var(--c-subtle); font-size: 12.5px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-prices { margin-top: 10px; }
        .price-cash  { color: var(--c-muted); font-size: 12.5px; font-weight: 500; }
        .price-monthly { color: var(--c-blue); font-size: 15.5px; font-weight: 800; letter-spacing: -.015em; line-height: 1.1; }
        .price-monthly small { font-size: 12px; font-weight: 600; color: var(--c-muted); }
        .product-btn-cell { display: none; }

        .empty-state {
            padding: 40px; text-align: center; color: var(--c-muted);
            background: var(--c-bg); border: 1.5px dashed var(--c-line); border-radius: 12px; font-size: 14px;
        }

        /* ── Cicilan ── */
        .cicilan-section { padding: 88px 0; }
        .cicilan-card {
            background: var(--c-bg); border: 1px solid var(--c-line);
            border-radius: 20px; padding: 32px; box-shadow: var(--sh-md);
        }
        .cicilan-top { margin-bottom: 28px; }
        .cicilan-top h2 { color: var(--c-ink); font-size: 1.5rem; font-weight: 800; letter-spacing: -.025em; }
        .cicilan-top p { margin-top: 8px; color: var(--c-muted); font-size: 14.5px; max-width: 520px; }
        .breakdown-row { display: grid; gap: 10px; }
        .bd-item {
            padding: 18px 20px; border: 1px solid var(--c-line);
            border-radius: 10px; background: var(--c-surface);
        }
        .bd-item.is-accent { background: var(--c-blue-lt); border-color: var(--c-blue-md); }
        .bd-lbl { color: var(--c-muted); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
        .bd-val { color: var(--c-ink); font-size: 1.2rem; font-weight: 800; letter-spacing: -.02em; }
        .bd-item.is-accent .bd-val { color: var(--c-blue); font-size: 1.4rem; }
        .cicilan-note {
            margin-top: 20px; padding: 14px 16px; background: var(--c-surface);
            border-radius: 8px; color: var(--c-muted); font-size: 13px; line-height: 1.65;
        }
        .cicilan-note strong { color: var(--c-text); }

        /* ── Proses ── */
        .proses-section { padding: 88px 0; }
        .proses-grid { display: grid; gap: 10px; }
        .proses-item {
            display: flex; align-items: flex-start; gap: 16px; padding: 20px;
            background: var(--c-bg); border: 1px solid var(--c-line);
            border-radius: 12px; transition: border-color .18s ease, box-shadow .18s ease;
        }
        .proses-item:hover { border-color: var(--c-blue-md); box-shadow: var(--sh-sm); }
        .proses-num {
            width: 36px; height: 36px; border-radius: 8px;
            background: var(--c-blue-lt); color: var(--c-blue);
            font-size: 14px; font-weight: 800; display: grid; place-items: center; flex-shrink: 0;
        }
        .proses-txt strong { display: block; color: var(--c-ink); font-size: 14.5px; font-weight: 700; margin-bottom: 3px; }
        .proses-txt span  { color: var(--c-muted); font-size: 13.5px; }

        .proof-block {
            margin-top: 32px; padding: 24px;
            background: var(--c-blue-lt); border: 1px solid var(--c-blue-md); border-radius: 14px;
        }
        .proof-quote { color: var(--c-text); font-size: 15px; line-height: 1.7; font-style: italic; }
        .proof-author { display: flex; align-items: center; gap: 10px; margin-top: 14px; }
        .proof-avatar {
            width: 32px; height: 32px; border-radius: 50%; background: var(--c-blue);
            color: #fff; font-size: 12px; font-weight: 700; display: grid; place-items: center;
        }
        .proof-name { color: var(--c-ink); font-size: 13.5px; font-weight: 700; }

        /* ── CTA ── */
        .cta-section { padding: 0 0 88px; }
        .cta-box {
            position: relative; overflow: hidden; padding: 52px 40px;
            background: var(--c-ink); border-radius: 24px; text-align: center;
        }
        .cta-box::before {
            content: ''; position: absolute; top: -70px; left: 50%; transform: translateX(-50%);
            width: 420px; height: 320px; border-radius: 50%; pointer-events: none;
            background: radial-gradient(circle, rgba(28,93,232,.38) 0%, transparent 65%);
        }
        .cta-box h2 { position: relative; color: #fff; font-size: 1.8rem; font-weight: 800; letter-spacing: -.03em; max-width: 500px; margin: 0 auto; }
        .cta-box p  { position: relative; margin: 12px auto 0; max-width: 420px; color: rgba(255,255,255,.55); font-size: 15px; }
        .cta-btn {
            position: relative; display: inline-flex; align-items: center; gap: 8px;
            height: 50px; padding: 0 28px; margin-top: 28px; border-radius: 8px;
            background: var(--c-blue); color: #fff; font-family: var(--font);
            font-size: 15px; font-weight: 700; box-shadow: var(--sh-bl);
            transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
        }
        .cta-btn:hover { background: var(--c-blue-dk); transform: translateY(-2px); box-shadow: 0 14px 36px rgba(28,93,232,.35); }

        /* ── Footer ── */
        .footer { background: var(--c-bg); border-top: 1px solid var(--c-line); }
        .footer-main { display: grid; gap: 36px; padding: 48px 0 36px; }
        .footer-brand p { margin-top: 10px; color: var(--c-muted); font-size: 13.5px; line-height: 1.7; max-width: 260px; }
        .footer-col-title { color: var(--c-ink); font-size: 13px; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; margin-bottom: 14px; }
        .footer-links { display: flex; flex-direction: column; gap: 9px; }
        .footer-links a, .footer-links span { color: var(--c-muted); font-size: 13.5px; transition: color .15s ease; }
        .footer-links a:hover { color: var(--c-blue); }
        .footer-bottom {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 10px; padding: 16px 0; border-top: 1px solid var(--c-line);
            color: var(--c-subtle); font-size: 12.5px;
        }

        /* ── Responsive ── */
        @media (min-width: 640px) {
            .hero h1 { font-size: 3rem; }
            .hero-trust { flex-direction: row; flex-wrap: wrap; gap: 12px 28px; }
            .breakdown-row { grid-template-columns: repeat(3, 1fr); }
            .proses-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (min-width: 960px) {
            .hero { padding: 80px 0 64px; }
            .hero-grid { grid-template-columns: 1fr 440px; gap: 64px; }
            .hero h1 { font-size: 3.4rem; }
            .product-row { grid-template-columns: 100px 1fr 200px auto; gap: 20px; padding: 16px 20px; }
            .product-prices { margin-top: 0; }
            .product-btn-cell { display: flex; }
            .proses-grid { grid-template-columns: repeat(3, 1fr); }
            .footer-main { grid-template-columns: 1.4fr 1fr 1fr 1fr; }
            .section-hd-text h2 { font-size: 1.75rem; }
            .cta-box h2 { font-size: 2rem; }
        }
        @media (max-width: 500px) {
            .nav-link, .nav-sep { display: none; }
            .hero h1 { font-size: 2.15rem; }
            .stats-strip { grid-template-columns: 1fr 1fr; }
            .stat-item:nth-child(2) { border-right: none; }
            .stat-item:nth-child(1), .stat-item:nth-child(2) { border-bottom: 1px solid var(--c-line); }
            .cicilan-card, .cta-box { padding: 22px 18px; }
            .section-hd { flex-direction: column; align-items: flex-start; }
        }
        @media (max-width: 760px) {
            .nav { height: auto; min-height: 68px; flex-wrap: wrap; }
            .nav-toggle { display: grid; margin-left: auto; }
            .nav-links {
                display: none; width: 100%; flex-direction: column; align-items: stretch; gap: 8px;
                padding: 10px; border: 1px solid var(--c-line); border-radius: 12px;
                background: #fff; box-shadow: var(--sh-md);
            }
            .nav-links.is-open { display: flex; }
            .nav-link {
                display: flex; width: 100%; justify-content: space-between;
                padding: 11px 12px; font-size: 14px;
            }
            .nav-sep { display: none; }
            .nav-cta { display: grid; width: 100%; gap: 8px; }
            .nav-cta .btn { width: 100%; }
            .hero-carousel-ui { margin-top: 12px; }
        }
    </style>
</head>
<body>

@php
    $featuredMotor   = $motors->first();
    $heroMotors      = $motors->take(4);
    $previewMotors   = $motors->take(4);
    $featuredPrice   = $featuredMotor ? $featuredMotor->harga_jual : 24000000;
    $featuredMonthly = ceil(($featuredPrice * 1.12 / 36) / 1000) * 1000;
    $exampleDp       = ceil(($featuredPrice * 0.2) / 1000) * 1000;
    $exampleLoan     = max($featuredPrice - $exampleDp, 0);
    $exampleMonthly  = ceil(($exampleLoan * 1.12 / 36) / 1000) * 1000;
@endphp

{{-- ═══════════════════════════════ HEADER ═══════════════════════════════ --}}
<header class="site-header">
    <div class="container">
        <nav class="nav" aria-label="Navigasi utama">
            <a href="{{ route('landing') }}" class="brand">
                <span class="brand-icon" aria-hidden="true"><i class="fas fa-motorcycle"></i></span>
                Kremo
            </a>
            <button class="nav-toggle" type="button" data-nav-toggle aria-controls="landing-nav" aria-expanded="false" aria-label="Buka menu navigasi">
                <span class="nav-toggle-lines" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
            <div class="nav-links" id="landing-nav" data-nav-menu>
                <a href="#motor"   class="nav-link">Motor</a>
                <a href="#cicilan" class="nav-link">Cicilan</a>
                <a href="#proses"  class="nav-link">Proses</a>
                <a href="{{ route('catalog') }}" class="nav-link">Katalog</a>
                <div class="nav-sep" aria-hidden="true"></div>
                <div class="nav-cta">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-gauge-high"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"    class="btn btn-ghost">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </nav>
    </div>
</header>

<main>
    {{-- ═══════════════════════════════ HERO ══════════════════════════════ --}}
    <section class="hero" aria-labelledby="hero-heading">
        <div class="container">
            <div class="hero-grid">

                {{-- Copy --}}
                <div>
                    <div class="hero-badge">
                        <span class="dot" aria-hidden="true"></span> Kredit motor online
                    </div>
                    <h1 id="hero-heading">Motor baru, cicilan <span class="hl">jelas sejak awal.</span></h1>
                    <p class="hero-desc">
                        Pilih unit, lihat estimasi cicilan, lalu ajukan kredit. Status survey, approval,
                        pembayaran, dan pengiriman bisa dipantau langsung dari dashboard.
                    </p>
                    <div class="hero-actions">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Ajukan Kredit
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Ajukan Kredit
                            </a>
                        @endauth
                        <a href="{{ route('catalog') }}" class="btn btn-ghost">Lihat Motor</a>
                    </div>
                    <div class="hero-trust" aria-label="Keunggulan layanan">
                        <span class="trust-item">
                            <span class="trust-icon" aria-hidden="true"><i class="fas fa-check"></i></span>
                            Estimasi cicilan terlihat dulu
                        </span>
                        <span class="trust-item">
                            <span class="trust-icon" aria-hidden="true"><i class="fas fa-check"></i></span>
                            Approval mengikuti hasil survey
                        </span>
                        <span class="trust-item">
                            <span class="trust-icon" aria-hidden="true"><i class="fas fa-check"></i></span>
                            Update dikirim lewat email
                        </span>
                    </div>
                </div>

                {{-- Featured Motor Carousel --}}
                <div class="hero-card-wrap" data-hero-carousel>
                    <div class="hero-carousel-track">
                        @forelse($heroMotors as $heroMotor)
                            @php($heroMonthly = ceil(($heroMotor->harga_jual * 1.12 / 36) / 1000) * 1000)
                            <a href="{{ route('motor.detail', $heroMotor) }}"
                               class="motor-card hero-slide {{ $loop->first ? 'is-active' : '' }}"
                               data-hero-slide
                               aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                               tabindex="{{ $loop->first ? '0' : '-1' }}"
                               aria-label="Lihat detail {{ $heroMotor->nama_motor }}">
                                <div class="card-img">
                                    <img src="{{ $heroMotor->primary_image_url }}" alt="{{ $heroMotor->nama_motor }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                                </div>
                                <div class="card-body">
                                    <div class="card-meta">
                                        <span class="card-tag">{{ $heroMotor->jenisMotor->merk ?? 'Motor pilihan' }}</span>
                                        <span class="card-stock">{{ $heroMotor->stok }} unit</span>
                                    </div>
                                    <h2 class="card-name">{{ $heroMotor->nama_motor }}</h2>
                                    <div class="card-prices">
                                        <div class="card-cash">
                                            Harga cash
                                            <strong>Rp {{ number_format($heroMotor->harga_jual, 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="card-monthly">
                                            <div class="card-monthly-lbl">Est. cicilan</div>
                                            <div class="card-monthly-val">
                                                Rp {{ number_format($heroMonthly, 0, ',', '.') }}<small>/bln</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="motor-card hero-slide is-active" data-hero-slide aria-hidden="false">
                                <div class="card-img">
                                    <img src="{{ asset('images/motors/yamaha-nmax-155.svg') }}" alt="Katalog motor Kremo">
                                </div>
                                <div class="card-body">
                                    <div class="card-meta">
                                        <span class="card-tag">Kremo Motor</span>
                                        <span class="card-stock">Online</span>
                                    </div>
                                    <h2 class="card-name">Motor pilihan siap diajukan</h2>
                                    <div class="card-prices">
                                        <div class="card-cash">Katalog tampil setelah data motor ditambahkan.</div>
                                        <div class="card-monthly">
                                            <div class="card-monthly-lbl">Est. cicilan</div>
                                            <div class="card-monthly-val">Tersedia</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if($heroMotors->count() > 1)
                        <div class="hero-carousel-ui" aria-label="Navigasi carousel motor">
                            <div class="hero-carousel-dots">
                                @foreach($heroMotors as $heroMotor)
                                    <button class="hero-carousel-dot {{ $loop->first ? 'is-active' : '' }}"
                                            type="button"
                                            data-hero-target="{{ $loop->index }}"
                                            aria-label="Tampilkan {{ $heroMotor->nama_motor }}"></button>
                                @endforeach
                            </div>
                            <div class="hero-carousel-actions">
                                <button class="hero-carousel-btn" type="button" data-hero-prev aria-label="Motor sebelumnya">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="hero-carousel-btn" type="button" data-hero-next aria-label="Motor berikutnya">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stats Strip --}}
            <div class="stats-strip" aria-label="Ringkasan layanan">
                <div class="stat-item">
                    <div class="stat-val">{{ $motors->count() }}+</div>
                    <div class="stat-lbl">Unit tersedia</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">{{ $jenisMotor->count() }}</div>
                    <div class="stat-lbl">Kategori motor</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">36x</div>
                    <div class="stat-lbl">Tenor populer</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">AWB</div>
                    <div class="stat-lbl">Status pengiriman</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════ MOTOR LIST ═════════════════════════════ --}}
    <section class="band section" id="motor" aria-labelledby="motor-heading">
        <div class="container">
            <div class="section-hd">
                <div class="section-hd-text">
                    <div class="eyebrow"><i class="fas fa-list"></i> Pilihan motor</div>
                    <h2 id="motor-heading">Pilih unit, lihat cicilan, lalu ajukan</h2>
                    <p>Harga cash dan estimasi cicilan ditampilkan langsung untuk memudahkan perbandingan.</p>
                </div>
                <a href="{{ route('catalog') }}" class="section-link">
                    Lihat semua <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="product-list" role="list">
                @forelse($previewMotors as $motor)
                    @php($monthly = ceil(($motor->harga_jual * 1.12 / 36) / 1000) * 1000)
                    <article class="product-row" role="listitem">
                        <a href="{{ route('motor.detail', $motor) }}" class="product-thumb" aria-label="{{ $motor->nama_motor }}">
                            <img src="{{ $motor->primary_image_url }}" alt="{{ $motor->nama_motor }}" loading="lazy">
                        </a>
                        <div class="product-info">
                            <div class="product-name">{{ $motor->nama_motor }}</div>
                            <div class="product-spec">{{ $motor->jenisMotor->merk ?? '-' }} - {{ $motor->kapasitas_mesin ?? '-' }} - {{ $motor->warna ?? '-' }}</div>
                            <div class="product-prices">
                                <div class="price-cash">Harga cash Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</div>
                                <div class="price-monthly">Rp {{ number_format($monthly, 0, ',', '.') }} <small>/bulan</small></div>
                            </div>
                        </div>
                        <div class="product-prices" aria-hidden="true" style="display:none">
                            <div class="price-cash">Harga cash Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</div>
                            <div class="price-monthly">Rp {{ number_format($monthly, 0, ',', '.') }} <small>/bulan</small></div>
                        </div>
                        <div class="product-btn-cell">
                            <a href="{{ route('motor.detail', $motor) }}" class="btn btn-soft-blue">Detail</a>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-motorcycle" style="font-size:24px;margin-bottom:10px;display:block;color:var(--c-subtle)"></i>
                        Belum ada motor tersedia.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ══════════════════════════ CICILAN ════════════════════════════════ --}}
    <section class="cicilan-section" id="cicilan" aria-labelledby="cicilan-heading">
        <div class="container">
            <div class="cicilan-card">
                <div class="cicilan-top">
                    <div class="eyebrow"><i class="fas fa-calculator"></i> Contoh cicilan</div>
                    <h2 id="cicilan-heading">Tiga angka yang perlu diketahui sebelum mengajukan</h2>
                    <p>DP, tenor, dan estimasi cicilan ditampilkan transparan agar calon pembeli dapat menilai kemampuan bayar sebelum mengajukan kredit.</p>
                </div>
                <div class="breakdown-row">
                    <div class="bd-item">
                        <div class="bd-lbl">DP / Uang muka</div>
                        <div class="bd-val">Rp {{ number_format($exampleDp, 0, ',', '.') }}</div>
                    </div>
                    <div class="bd-item">
                        <div class="bd-lbl">Tenor</div>
                        <div class="bd-val">36 bulan</div>
                    </div>
                    <div class="bd-item is-accent">
                        <div class="bd-lbl">Estimasi cicilan</div>
                        <div class="bd-val">Rp {{ number_format($exampleMonthly, 0, ',', '.') }}/bln</div>
                    </div>
                </div>
                <p class="cicilan-note">
                    <strong>Contoh berdasarkan {{ $featuredMotor->nama_motor ?? 'motor pilihan' }}.</strong>
                    Angka final ditentukan setelah pengajuan masuk, hasil survey, dan keputusan approval dari tim Kremo.
                </p>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════ ALUR PROSES ════════════════════════════ --}}
    <section class="band proses-section" id="proses" aria-labelledby="proses-heading">
        <div class="container">
            <div class="section-hd">
                <div class="section-hd-text">
                    <div class="eyebrow"><i class="fas fa-route"></i> Alur proses</div>
                    <h2 id="proses-heading">Dari pengajuan sampai motor tiba, semua tercatat</h2>
                    <p>Proses kredit berjalan dalam satu alur yang bisa dipantau tanpa perlu menebak-nebak tahap berikutnya.</p>
                </div>
            </div>

            <div class="proses-grid">
                <div class="proses-item">
                    <div class="proses-num" aria-hidden="true">1</div>
                    <div class="proses-txt">
                        <strong>Daftar &amp; Pilih Motor</strong>
                        <span>Buat akun, jelajahi katalog, dan pilih motor yang sesuai anggaran.</span>
                    </div>
                </div>
                <div class="proses-item">
                    <div class="proses-num" aria-hidden="true">2</div>
                    <div class="proses-txt">
                        <strong>Isi Pengajuan Kredit</strong>
                        <span>Lengkapi data diri dan informasi kredit untuk diproses tim Kremo.</span>
                    </div>
                </div>
                <div class="proses-item">
                    <div class="proses-num" aria-hidden="true">3</div>
                    <div class="proses-txt">
                        <strong>Survey &amp; Approval</strong>
                        <span>Tim melakukan survey dan memberikan keputusan approval kredit.</span>
                    </div>
                </div>
                <div class="proses-item">
                    <div class="proses-num" aria-hidden="true">4</div>
                    <div class="proses-txt">
                        <strong>Pembayaran DP</strong>
                        <span>Setelah disetujui, lakukan pembayaran uang muka sesuai ketentuan.</span>
                    </div>
                </div>
                <div class="proses-item">
                    <div class="proses-num" aria-hidden="true">5</div>
                    <div class="proses-txt">
                        <strong>Pengiriman Motor</strong>
                        <span>Motor dikirim dengan nomor AWB yang bisa dilacak secara real-time.</span>
                    </div>
                </div>
                <div class="proses-item">
                    <div class="proses-num" aria-hidden="true">6</div>
                    <div class="proses-txt">
                        <strong>Cicilan Berjalan</strong>
                        <span>Bayar cicilan tepat waktu dan pantau riwayat pembayaran dari dashboard.</span>
                    </div>
                </div>
            </div>

            <div class="proof-block">
                <p class="proof-quote">"Yang paling membantu adalah statusnya jelas. Setelah pengajuan masuk, pelanggan tahu tahap berikutnya tanpa harus menebak-nebak."</p>
                <div class="proof-author">
                    <div class="proof-avatar" aria-hidden="true">K</div>
                    <span class="proof-name">Kremo Credit Flow</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════ CTA AKHIR ═════════════════════════════ --}}
    <section class="cta-section" aria-labelledby="cta-heading">
        <div class="container">
            <div class="cta-box">
                <h2 id="cta-heading">Ajukan kredit motor dengan cicilan yang jelas.</h2>
                <p>Buat akun, pilih motor, lalu isi data pengajuan. Setiap tahap proses dapat dipantau langsung dari dashboard.</p>
                @auth
                    <a href="{{ route('dashboard') }}" class="cta-btn">
                        <i class="fas fa-gauge-high"></i> Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="cta-btn">
                        <i class="fas fa-paper-plane"></i> Ajukan Kredit Sekarang
                    </a>
                @endauth
            </div>
        </div>
    </section>
</main>

{{-- ═══════════════════════════════ FOOTER ════════════════════════════════ --}}
<footer class="footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-brand">
                <div class="brand">
                    <span class="brand-icon" aria-hidden="true"><i class="fas fa-motorcycle"></i></span>
                    Kremo
                </div>
                <p>Kredit motor online dengan cicilan transparan, proses pengajuan terpantau, dan update status real-time.</p>
            </div>

            <div>
                <div class="footer-col-title">Produk</div>
                <div class="footer-links">
                    <a href="{{ route('catalog') }}">Katalog Motor</a>
                    <a href="#motor">Motor Tersedia</a>
                    <a href="#cicilan">Contoh Cicilan</a>
                    <a href="#proses">Alur Proses</a>
                </div>
            </div>

            <div>
                <div class="footer-col-title">Akun</div>
                <div class="footer-links">
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Daftar</a>
                    <a href="{{ route('catalog') }}">Lihat Katalog</a>
                </div>
            </div>

            <div>
                <div class="footer-col-title">Kontak</div>
                <div class="footer-links">
                    <span><i class="fas fa-location-dot" style="width:14px;color:var(--c-subtle)"></i> Jakarta, Indonesia</span>
                    <span><i class="fas fa-envelope"     style="width:14px;color:var(--c-subtle)"></i> support@kremo.test</span>
                    <span><i class="fas fa-clock"        style="width:14px;color:var(--c-subtle)"></i> 09.00 - 17.00 WIB</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} Kremo. Kredit motor online.</span>
            <span>Pengajuan / Survey / Approval / Pengiriman</span>
        </div>
    </div>
</footer>

<script>
    (() => {
        const navToggle = document.querySelector('[data-nav-toggle]');
        const navMenu = document.querySelector('[data-nav-menu]');

        if (navToggle && navMenu) {
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
        }

        const carousel = document.querySelector('[data-hero-carousel]');

        if (!carousel) {
            return;
        }

        const slides = [...carousel.querySelectorAll('[data-hero-slide]')];
        const dots = [...carousel.querySelectorAll('[data-hero-target]')];
        const prev = carousel.querySelector('[data-hero-prev]');
        const next = carousel.querySelector('[data-hero-next]');

        if (slides.length <= 1) {
            return;
        }

        let activeIndex = 0;
        let timerId = null;

        const showSlide = (index) => {
            activeIndex = (index + slides.length) % slides.length;

            slides.forEach((slide, slideIndex) => {
                const active = slideIndex === activeIndex;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
                slide.setAttribute('tabindex', active ? '0' : '-1');
            });

            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('is-active', dotIndex === activeIndex);
                dot.setAttribute('aria-current', dotIndex === activeIndex ? 'true' : 'false');
            });
        };

        const stop = () => {
            if (timerId) {
                window.clearInterval(timerId);
                timerId = null;
            }
        };

        const start = () => {
            stop();
            timerId = window.setInterval(() => showSlide(activeIndex + 1), 5200);
        };

        prev?.addEventListener('click', () => {
            showSlide(activeIndex - 1);
            start();
        });

        next?.addEventListener('click', () => {
            showSlide(activeIndex + 1);
            start();
        });

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                showSlide(Number(dot.dataset.heroTarget));
                start();
            });
        });

        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', start);
        document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
        start();
    })();
</script>

</body>
</html>
