@extends('emails.layout')

@section('content')
@php
    $pengajuan = $pengiriman->pengajuanKredit;
    $pelanggan = $pengajuan?->pelanggan;
    $motor = $pengajuan?->motor;
    $delivered = $pengiriman->status_kirim === 'Tiba Di Tujuan';
    $verificationStatus = $pengiriman->delivery_verification_status ?? 'Belum Dikonfirmasi';
@endphp

<h2>{{ $eventLabel }}</h2>
<p class="lead">Halo {{ $pelanggan?->nama_pelanggan ?? 'Pelanggan' }}, berikut update pengiriman motor Anda.</p>

<div class="summary-grid">
    <div class="summary-cell"><span>Invoice</span><strong>{{ $pengiriman->no_invoice ?? '-' }}</strong></div>
    <div class="summary-cell"><span>Status</span><strong><span class="badge {{ $delivered ? 'badge-success' : 'badge-info' }}">{{ $pengiriman->status_kirim }}</span></strong></div>
    <div class="summary-cell"><span>Validasi</span><strong>{{ $verificationStatus }}</strong></div>
</div>

<div class="section-title">Detail Pengiriman</div>
<div class="info-box">
    <div class="info-row"><span class="info-label">Motor</span><span class="info-value">{{ $motor?->nama_motor ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Penerima</span><span class="info-value">{{ $pengiriman->receiver_name ?: ($pelanggan?->nama_pelanggan ?? '-') }}</span></div>
    <div class="info-row"><span class="info-label">Telepon</span><span class="info-value">{{ $pengiriman->receiver_phone ?: ($pelanggan?->no_telp ?? '-') }}</span></div>
    <div class="info-row"><span class="info-label">Alamat</span><span class="info-value">{{ $pengiriman->receiver_address ?: ($pelanggan?->alamat ?? '-') }}</span></div>
    <div class="info-row"><span class="info-label">Origin</span><span class="info-value">{{ $pengiriman->origin_label ?: '-' }}</span></div>
    <div class="info-row"><span class="info-label">Destination</span><span class="info-value">{{ $pengiriman->destination_label ?: '-' }}</span></div>
    <div class="info-row"><span class="info-label">Kurir</span><span class="info-value">{{ strtoupper($pengiriman->courier_code ?? '-') }} {{ $pengiriman->courier_service }}</span></div>
    <div class="info-row"><span class="info-label">Ongkir</span><span class="info-value">{{ $pengiriman->shipping_cost ? 'Rp ' . number_format($pengiriman->shipping_cost, 0, ',', '.') : '-' }}</span></div>
    <div class="info-row"><span class="info-label">Estimasi</span><span class="info-value">{{ $pengiriman->shipping_etd ?: '-' }}</span></div>
    <div class="info-row"><span class="info-label">Bukti Customer</span><span class="info-value">{{ $pengiriman->customer_received_at?->format('d/m/Y H:i') ?? 'Belum dikirim' }}</span></div>
    <div class="info-row"><span class="info-label">Verifikasi Admin</span><span class="info-value">{{ $pengiriman->delivery_verified_at?->format('d/m/Y H:i') ?? '-' }}</span></div>
    @if($pengiriman->delivery_verification_note)
        <div class="info-row"><span class="info-label">Catatan</span><span class="info-value">{{ $pengiriman->delivery_verification_note }}</span></div>
    @endif
</div>

<div class="timeline">
    <div class="timeline-item"><strong>Pengiriman dibuat</strong><span>{{ $pengiriman->created_at?->format('d/m/Y H:i') ?? '-' }}</span></div>
    <div class="timeline-item"><strong>Sedang dikirim</strong><span>{{ $pengiriman->tgl_kirim?->format('d/m/Y H:i') ?? 'Menunggu jadwal kirim' }}</span></div>
    <div class="timeline-item"><strong>Bukti penerimaan customer</strong><span>{{ $pengiriman->customer_received_at?->format('d/m/Y H:i') ?? 'Belum dikirim' }}</span></div>
    <div class="timeline-item"><strong>Pengiriman selesai</strong><span>{{ $pengiriman->tgl_tiba?->format('d/m/Y H:i') ?? 'Menunggu verifikasi' }}</span></div>
</div>

<p style="margin-top: 22px;"><a href="{{ route('pengajuan.show', $pengajuan) }}" class="btn">Pantau dari Dashboard</a></p>
@endsection
