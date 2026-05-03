@extends('emails.layout')

@section('content')
@php
    $pelanggan = $angsuran->kredit?->pengajuanKredit?->pelanggan;
    $motor = $angsuran->kredit?->pengajuanKredit?->motor;
    $isOverdue = str_starts_with($reminderType, 'H+');
@endphp

<h2>{{ $isOverdue ? 'Angsuran melewati jatuh tempo' : 'Pengingat jatuh tempo angsuran' }}</h2>
<p class="lead">Halo {{ $pelanggan?->nama_pelanggan ?? 'Pelanggan' }}, {{ $isOverdue ? 'angsuran Anda sudah melewati tanggal jatuh tempo.' : 'angsuran Anda akan segera jatuh tempo.' }}</p>

<div class="summary-grid">
    <div class="summary-cell"><span>Angsuran</span><strong>Ke-{{ $angsuran->angsuran_ke }}</strong></div>
    <div class="summary-cell"><span>Status</span><strong><span class="badge {{ $isOverdue ? 'badge-danger' : 'badge-warning' }}">{{ $isOverdue ? 'Terlambat' : 'Belum Bayar' }}</span></strong></div>
    <div class="summary-cell"><span>Nominal</span><strong>Rp {{ number_format($angsuran->total_bayar, 0, ',', '.') }}</strong></div>
</div>

<div class="section-title">Detail Tagihan</div>
<div class="info-box">
    <div class="info-row"><span class="info-label">Motor</span><span class="info-value">{{ $motor?->nama_motor ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Jatuh Tempo</span><span class="info-value">{{ $angsuran->tgl_jatuh_tempo?->format('d/m/Y') ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Tipe Pengingat</span><span class="info-value">{{ $reminderType }}</span></div>
</div>

<div class="note">{{ $isOverdue ? 'Mohon segera lakukan pembayaran agar status kredit tetap aman.' : 'Silakan lakukan pembayaran sebelum tanggal jatuh tempo.' }}</div>
<p style="margin-top: 22px;"><a href="{{ url('/dashboard') }}" class="btn">Buka Dashboard</a></p>
@endsection
