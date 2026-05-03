<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { margin: 0; padding: 0; background: #eef2f7; color: #334155; font-family: Arial, Helvetica, sans-serif; }
.email-shell { width: 100%; padding: 28px 12px; }
.email-card { max-width: 680px; margin: 0 auto; background: #ffffff; border: 1px solid #dbe4ee; border-radius: 14px; overflow: hidden; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); }
.email-header { background: #0f172a; padding: 28px 30px; color: #ffffff; }
.brand { display: inline-block; font-size: 22px; font-weight: 800; letter-spacing: 0; }
.tagline { margin-top: 6px; color: #cbd5e1; font-size: 13px; }
.email-body { padding: 30px; }
h1, h2, h3, p { margin-top: 0; }
h2 { color: #0f172a; font-size: 21px; line-height: 1.35; margin-bottom: 10px; }
p { color: #475569; line-height: 1.68; font-size: 14px; margin-bottom: 14px; }
.lead { color: #334155; font-size: 15px; }
.section-title { color: #0f172a; font-size: 14px; font-weight: 800; margin: 24px 0 10px; text-transform: uppercase; letter-spacing: .04em; }
.info-box { background: #f8fafc; border: 1px solid #dbe4ee; border-radius: 10px; margin: 18px 0; overflow: hidden; }
.info-row { display: table; width: 100%; border-bottom: 1px solid #e2e8f0; }
.info-row:last-child { border-bottom: 0; }
.info-label, .info-value { display: table-cell; padding: 12px 14px; vertical-align: top; font-size: 14px; }
.info-label { width: 42%; color: #64748b; font-weight: 700; }
.info-value { color: #0f172a; font-weight: 700; text-align: right; }
.summary-grid { display: table; width: 100%; border-spacing: 10px; margin: 18px -10px 8px; }
.summary-cell { display: table-cell; width: 33.33%; background: #f8fafc; border: 1px solid #dbe4ee; border-radius: 10px; padding: 14px; }
.summary-cell span { display: block; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; }
.summary-cell strong { display: block; color: #0f172a; font-size: 17px; margin-top: 6px; }
.badge { display: inline-block; padding: 5px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; }
.badge-success { background: #dcfce7; color: #166534; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-info { background: #dbeafe; color: #1d4ed8; }
.btn { display: inline-block; padding: 12px 18px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 800; font-size: 14px; }
.note { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; border-radius: 10px; padding: 14px; font-size: 13px; line-height: 1.6; }
.timeline { margin: 18px 0; border-left: 3px solid #bfdbfe; padding-left: 16px; }
.timeline-item { margin-bottom: 14px; }
.timeline-item strong { display: block; color: #0f172a; font-size: 14px; }
.timeline-item span { display: block; color: #64748b; font-size: 13px; margin-top: 3px; }
.email-footer { padding: 20px 30px 28px; color: #94a3b8; font-size: 12px; line-height: 1.6; border-top: 1px solid #e2e8f0; background: #f8fafc; text-align: center; }
.otp-box { text-align: center; padding: 18px; margin: 18px 0; background: #f8fafc; border: 1px solid #dbe4ee; border-radius: 10px; }
.otp-code { font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #1d4ed8; }
@media (max-width: 560px) {
    .email-body, .email-header, .email-footer { padding-left: 20px; padding-right: 20px; }
    .info-label, .info-value { display: block; width: auto; text-align: left; padding-bottom: 4px; }
    .info-value { padding-top: 0; padding-bottom: 12px; }
    .summary-grid, .summary-cell { display: block; width: auto; }
    .summary-cell { margin-bottom: 10px; }
}
</style>
</head>
<body>
<div class="email-shell">
    <div class="email-card">
        <div class="email-header">
            <div class="brand">Kremo</div>
            <div class="tagline">Kredit Motor Online</div>
        </div>
        <div class="email-body">
            @yield('content')
        </div>
        <div class="email-footer">
            <div>&copy; {{ date('Y') }} Kremo - Kredit Motor Online.</div>
            <div>Email ini dikirim otomatis oleh sistem. Mohon jangan membalas email ini.</div>
        </div>
    </div>
</div>
</body>
</html>
