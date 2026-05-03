@extends('emails.layout')

@section('content')
@php
    $pengajuan = $angsuran->kredit?->pengajuanKredit;
@endphp

<h2>Pembayaran angsuran berhasil</h2>
<p class="lead">Halo {{ $pengajuan?->pelanggan?->nama_pelanggan ?? 'Pelanggan' }}, pembayaran angsuran Anda sudah dikonfirmasi.</p>

<div class="summary-grid">
    <div class="summary-cell"><span>Angsuran</span><strong>Ke-{{ $angsuran->angsuran_ke }}</strong></div>
    <div class="summary-cell"><span>Status</span><strong><span class="badge badge-success">Lunas</span></strong></div>
    <div class="summary-cell"><span>Nominal</span><strong>Rp {{ number_format($angsuran->total_bayar, 0, ',', '.') }}</strong></div>
</div>

<div class="section-title">Detail Pembayaran</div>
<div class="info-box">
    <div class="info-row"><span class="info-label">Motor</span><span class="info-value">{{ $pengajuan?->motor?->nama_motor ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Tanggal Bayar</span><span class="info-value">{{ $angsuran->tgl_bayar?->format('d/m/Y H:i') ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Jatuh Tempo</span><span class="info-value">{{ $angsuran->tgl_jatuh_tempo?->format('d/m/Y') ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Sisa Kredit</span><span class="info-value">Rp {{ number_format($angsuran->kredit?->sisa_kredit ?? 0, 0, ',', '.') }}</span></div>
</div>

<p>Terima kasih. Simpan email ini sebagai arsip pembayaran Anda.</p>
@endsection
