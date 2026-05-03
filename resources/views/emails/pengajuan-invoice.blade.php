@extends('emails.layout')

@section('content')
@php
    $tenor = $pengajuan->jenisCicilan?->lama_cicilan ?? 0;
    $pokokKredit = max(($pengajuan->harga_cash ?? 0) - ($pengajuan->dp ?? 0), 0);
    $totalAsuransi = ($pengajuan->biaya_asuransi_perbulan ?? 0) * $tenor;
@endphp

<h2>Pengajuan kredit berhasil diterima</h2>
<p class="lead">Halo {{ $pengajuan->pelanggan?->nama_pelanggan ?? 'Pelanggan' }}, pengajuan kredit Anda sudah masuk ke sistem Kremo. Detail di bawah ini dapat digunakan sebagai ringkasan pengajuan.</p>

<div class="summary-grid">
    <div class="summary-cell"><span>No. Pengajuan</span><strong>#{{ $pengajuan->id }}</strong></div>
    <div class="summary-cell"><span>Status</span><strong>{{ $pengajuan->status_pengajuan }}</strong></div>
    <div class="summary-cell"><span>Cicilan</span><strong>Rp {{ number_format($pengajuan->cicilan_perbulan, 0, ',', '.') }}</strong></div>
</div>

<div class="section-title">Data Pengajuan</div>
<div class="info-box">
    <div class="info-row"><span class="info-label">Tanggal Pengajuan</span><span class="info-value">{{ $pengajuan->tgl_pengajuan_kredit?->format('d/m/Y') ?? now()->format('d/m/Y') }}</span></div>
    <div class="info-row"><span class="info-label">Pelanggan</span><span class="info-value">{{ $pengajuan->pelanggan?->nama_pelanggan ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Motor</span><span class="info-value">{{ $pengajuan->motor?->nama_motor ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Tenor</span><span class="info-value">{{ $tenor }} bulan</span></div>
    <div class="info-row"><span class="info-label">Metode Bayar</span><span class="info-value">{{ $pengajuan->metodeBayar?->metode_pembayaran ?? '-' }}{{ $pengajuan->metodeBayar?->tempat_bayar ? ' - ' . $pengajuan->metodeBayar->tempat_bayar : '' }}</span></div>
    <div class="info-row"><span class="info-label">Asuransi</span><span class="info-value">{{ $pengajuan->asuransi?->nama_asuransi ?? 'Tanpa asuransi' }}</span></div>
</div>

<div class="section-title">Rincian Biaya</div>
<div class="info-box">
    <div class="info-row"><span class="info-label">Harga Cash</span><span class="info-value">Rp {{ number_format($pengajuan->harga_cash, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">DP / Uang Muka</span><span class="info-value">Rp {{ number_format($pengajuan->dp, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">Pokok Kredit</span><span class="info-value">Rp {{ number_format($pokokKredit, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">Estimasi Total Asuransi</span><span class="info-value">Rp {{ number_format($totalAsuransi, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">Total Kredit</span><span class="info-value">Rp {{ number_format($pengajuan->harga_kredit, 0, ',', '.') }}</span></div>
    <div class="info-row"><span class="info-label">Cicilan per Bulan</span><span class="info-value">Rp {{ number_format($pengajuan->cicilan_perbulan, 0, ',', '.') }}</span></div>
</div>

<div class="note">Tahap berikutnya adalah proses survey. Anda akan menerima email lagi setiap ada perubahan penting pada pengajuan.</div>
<p style="margin-top: 22px;"><a href="{{ route('pengajuan.show', $pengajuan) }}" class="btn">Lihat Detail Pengajuan</a></p>
@endsection
