@extends('emails.layout')

@section('content')
@php
    $badgeClass = in_array($statusBaru, ['Disetujui', 'Diterima', 'DP & Ongkir Dibayar'], true)
        ? 'badge-success'
        : (in_array($statusBaru, ['Ditolak', 'Dibatalkan Penjual', 'Dibatalkan Pembeli', 'Bermasalah'], true) ? 'badge-danger' : 'badge-info');
    $statusMessage = match ($statusBaru) {
        'Diproses' => 'Pengajuan Anda sudah diambil oleh surveyor dan sedang masuk tahap pengecekan.',
        'Survey' => 'Survey sudah disubmit. Pengajuan Anda sekarang menunggu keputusan approver.',
        'Disetujui' => 'Pengajuan kredit Anda disetujui. Silakan pilih layanan pengiriman dan bayar DP beserta ongkir sebelum motor dikirim.',
        'DP & Ongkir Dibayar' => 'Pembayaran DP dan ongkir sudah dikonfirmasi. Tim Kremo akan menyiapkan pengiriman motor Anda.',
        'Ditolak' => 'Pengajuan kredit Anda belum dapat disetujui saat ini.',
        'Diterima' => 'Motor sudah diterima dan proses pengiriman selesai.',
        default => 'Status pengajuan kredit Anda telah diperbarui.',
    };
@endphp

<h2>Status pengajuan diperbarui</h2>
<p class="lead">Halo {{ $pengajuan->pelanggan?->nama_pelanggan ?? 'Pelanggan' }}, {{ $statusMessage }}</p>

<div class="summary-grid">
    <div class="summary-cell"><span>No. Pengajuan</span><strong>#{{ $pengajuan->id }}</strong></div>
    <div class="summary-cell"><span>Status Baru</span><strong><span class="badge {{ $badgeClass }}">{{ $statusBaru }}</span></strong></div>
    <div class="summary-cell"><span>Motor</span><strong>{{ $pengajuan->motor?->nama_motor ?? '-' }}</strong></div>
</div>

<div class="section-title">Detail Proses</div>
<div class="info-box">
    <div class="info-row"><span class="info-label">Pelanggan</span><span class="info-value">{{ $pengajuan->pelanggan?->nama_pelanggan ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Surveyor</span><span class="info-value">{{ $pengajuan->surveyor?->name ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Approver</span><span class="info-value">{{ $pengajuan->approver?->name ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Catatan Survey</span><span class="info-value">{{ $pengajuan->catatan_survey ?: '-' }}</span></div>
    <div class="info-row"><span class="info-label">Keterangan Status</span><span class="info-value">{{ $pengajuan->keterangan_status_pengajuan ?: '-' }}</span></div>
</div>

<div class="section-title">Rincian Kredit</div>
<div class="info-box">
    <div class="info-row"><span class="info-label">Harga Cash</span><span class="info-value">Rp {{ number_format($pengajuan->harga_cash, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">DP / Uang Muka</span><span class="info-value">Rp {{ number_format($pengajuan->dp, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">Ongkir Pilihan</span><span class="info-value">Rp {{ number_format($pengajuan->shipping_cost ?? 0, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">Status DP</span><span class="info-value">{{ $pengajuan->dp_payment_status ?? 'Belum Bayar' }}</span></div>
    <div class="info-row"><span class="info-label">Total Kredit</span><span class="info-value">Rp {{ number_format($pengajuan->harga_kredit, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">Cicilan per Bulan</span><span class="info-value">Rp {{ number_format($pengajuan->cicilan_perbulan, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">Metode Bayar</span><span class="info-value">{{ $pengajuan->metodeBayar?->metode_pembayaran ?? '-' }}</span></div>
</div>

<p style="margin-top: 22px;"><a href="{{ route('pengajuan.show', $pengajuan) }}" class="btn">Buka Detail Pengajuan</a></p>
@endsection
