<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kremo</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; margin: 0; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 20px 0 8px; }
        p { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 7px; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; text-align: left; }
        .muted { color: #6b7280; }
        .summary { width: 100%; margin-top: 16px; }
        .summary td { width: 25%; border: 1px solid #e5e7eb; padding: 10px; }
        .summary strong { display: block; font-size: 14px; margin-top: 4px; }
        .right { text-align: right; }
        .center { text-align: center; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; background: #eef2ff; }
    </style>
</head>
<body>
@php
    $title = match ($type) {
        'order' => 'Laporan Order',
        'kredit' => 'Laporan Kredit',
        'pembayaran' => 'Laporan Pembayaran',
        'kinerja_user' => 'Laporan Kinerja User',
        default => 'Laporan',
    };
    $items = collect($data['data'] ?? []);
@endphp

<h1>{{ $title }}</h1>
<p class="muted">Kremo - Kredit Motor Online</p>
<p class="muted">Dicetak: {{ $generatedAt->format('d/m/Y H:i') }}</p>
<p class="muted">
    Filter:
    {{ $filters['start_date'] ? 'Mulai ' . $filters['start_date'] : 'Mulai semua' }},
    {{ $filters['end_date'] ? 'Akhir ' . $filters['end_date'] : 'Akhir semua' }}
    @if($filters['status'])
        , Status {{ $filters['status'] }}
    @endif
</p>

@if($type === 'order')
    <table class="summary">
        <tr>
            <td>Total Order<strong>{{ number_format($data['transaction_stats']['total_order'] ?? 0) }}</strong></td>
            <td>Total Transaksi<strong>Rp {{ number_format($data['transaction_stats']['total_transaksi'] ?? 0, 0, ',', '.') }}</strong></td>
            <td>Rata-rata<strong>Rp {{ number_format($data['transaction_stats']['rata_rata'] ?? 0, 0, ',', '.') }}</strong></td>
            <td>Tertinggi<strong>Rp {{ number_format($data['transaction_stats']['tertinggi'] ?? 0, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <h2>Detail Order</h2>
    <table>
        <thead>
            <tr><th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Motor</th><th class="right">Harga Cash</th><th class="right">DP</th><th>Status</th></tr>
        </thead>
        <tbody>
        @forelse($items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item->tgl_pengajuan_kredit?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $item->pelanggan?->nama_pelanggan ?? '-' }}</td>
                <td>{{ $item->motor?->nama_motor ?? '-' }}</td>
                <td class="right">Rp {{ number_format($item->harga_cash, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($item->dp, 0, ',', '.') }}</td>
                <td><span class="badge">{{ $item->status_pengajuan }}</span></td>
            </tr>
        @empty
            <tr><td colspan="7" class="center">Tidak ada data</td></tr>
        @endforelse
        </tbody>
    </table>
@elseif($type === 'kredit')
    <table class="summary">
        <tr>
            <td>Total Kredit<strong>{{ number_format($data['total'] ?? 0) }}</strong></td>
            @foreach(($data['payment_ratio'] ?? []) as $ratio)
                <td>{{ $ratio['kategori'] }}<strong>{{ $ratio['jumlah'] }} ({{ $ratio['persentase'] }}%)</strong></td>
            @endforeach
        </tr>
    </table>

    <h2>Detail Kredit</h2>
    <table>
        <thead>
            <tr><th>No</th><th>Mulai</th><th>Pelanggan</th><th>Motor</th><th class="right">Total Kredit</th><th class="right">Sisa</th><th>Status</th></tr>
        </thead>
        <tbody>
        @forelse($items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item->tgl_mulai_kredit?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $item->pengajuanKredit?->pelanggan?->nama_pelanggan ?? '-' }}</td>
                <td>{{ $item->pengajuanKredit?->motor?->nama_motor ?? '-' }}</td>
                <td class="right">Rp {{ number_format($item->pengajuanKredit?->harga_kredit ?? 0, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($item->sisa_kredit, 0, ',', '.') }}</td>
                <td><span class="badge">{{ $item->status_kredit }}</span></td>
            </tr>
        @empty
            <tr><td colspan="7" class="center">Tidak ada data</td></tr>
        @endforelse
        </tbody>
    </table>
@elseif($type === 'pembayaran')
    <table class="summary">
        <tr>
            <td>Total Transaksi<strong>{{ number_format($data['summary']['total_transaksi'] ?? 0) }}</strong></td>
            <td>Total Pendapatan<strong>Rp {{ number_format($data['summary']['total_pendapatan'] ?? 0, 0, ',', '.') }}</strong></td>
            <td>Rata-rata<strong>Rp {{ number_format($data['summary']['rata_rata'] ?? 0, 0, ',', '.') }}</strong></td>
            <td>Total Data<strong>{{ number_format($data['total'] ?? 0) }}</strong></td>
        </tr>
    </table>

    <h2>Detail Pembayaran</h2>
    <table>
        <thead>
            <tr><th>No</th><th>Tgl Bayar</th><th>Pelanggan</th><th>Motor</th><th>Angsuran Ke</th><th class="right">Nominal</th><th>Metode</th></tr>
        </thead>
        <tbody>
        @forelse($items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item->tgl_bayar?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $item->kredit?->pengajuanKredit?->pelanggan?->nama_pelanggan ?? '-' }}</td>
                <td>{{ $item->kredit?->pengajuanKredit?->motor?->nama_motor ?? '-' }}</td>
                <td class="center">{{ $item->angsuran_ke }}</td>
                <td class="right">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                <td>{{ $item->payment_type ? 'Midtrans' : 'Manual' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="center">Tidak ada data</td></tr>
        @endforelse
        </tbody>
    </table>
@else
    <table class="summary">
        <tr>
            <td>Total User<strong>{{ number_format($data['summary']['total_user'] ?? 0) }}</strong></td>
            <td>Total Respons<strong>{{ number_format($data['summary']['total_respons'] ?? 0) }}</strong></td>
            <td>Survey Selesai<strong>{{ number_format($data['summary']['total_survey_selesai'] ?? 0) }}</strong></td>
            <td>Total Approval<strong>{{ number_format($data['summary']['total_approval'] ?? 0) }}</strong></td>
        </tr>
    </table>

    <h2>Kinerja User Operasional</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th class="right">Respons</th>
                <th class="right">Pengajuan</th>
                <th class="right">Pelanggan</th>
                <th class="right">Survey Diambil</th>
                <th class="right">Survey Selesai</th>
                <th class="right">Approval</th>
                <th class="right">Setuju</th>
                <th class="right">Tolak</th>
            </tr>
        </thead>
        <tbody>
        @forelse($items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item['nama'] }}</td>
                <td>{{ $item['email'] }}</td>
                <td>{{ ucfirst($item['role']) }}</td>
                <td class="right">{{ number_format($item['total_respons']) }}</td>
                <td class="right">{{ number_format($item['pengajuan_dibuat']) }}</td>
                <td class="right">{{ number_format($item['pelanggan_dibuat']) }}</td>
                <td class="right">{{ number_format($item['survey_diambil']) }}</td>
                <td class="right">{{ number_format($item['survey_selesai']) }}</td>
                <td class="right">{{ number_format($item['approval_ditangani']) }}</td>
                <td class="right">{{ number_format($item['approval_disetujui']) }}</td>
                <td class="right">{{ number_format($item['approval_ditolak']) }}</td>
            </tr>
        @empty
            <tr><td colspan="12" class="center">Tidak ada data</td></tr>
        @endforelse
        </tbody>
    </table>
@endif
</body>
</html>
