@extends('layouts.app')
@section('title', 'Detail Pengiriman')
@section('page-title', 'Detail Pengiriman')

@section('content')
@php
    $pengajuan = $pengiriman->pengajuanKredit;
    $customerStatus = $pengiriman->delivery_verification_status ?: \App\Models\Pengiriman::VERIFICATION_UNCONFIRMED;
    $shippingStep = match (true) {
        $pengiriman->status_kirim === 'Tiba Di Tujuan' => 3,
        $customerStatus === \App\Models\Pengiriman::VERIFICATION_PENDING => 2,
        $pengiriman->tgl_kirim !== null => 1,
        default => 0,
    };
    $proofUrl = $pengiriman->customer_received_photo
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($pengiriman->customer_received_photo)
        : null;
@endphp

<style>
    .delivery-detail-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(320px, 420px); gap: 20px; align-items: start; }
    .delivery-hero { border: 1px solid var(--border); border-radius: 8px; padding: 18px; background: #f8fafc; margin-bottom: 18px; }
    .delivery-hero span { display:block; color: var(--text-muted); font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
    .delivery-hero strong { color: var(--text-heading); font-size: 1.45rem; line-height: 1.25; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
    .info-box { border: 1px solid var(--border); border-radius: 8px; padding: 14px; background: #fff; }
    .info-box label { display:block; color: var(--text-muted); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
    .info-box p { color: var(--text-heading); font-weight: 650; line-height: 1.45; overflow-wrap: anywhere; }
    .delivery-progress { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; position: relative; margin: 6px 0 20px; padding-top: 16px; }
    .delivery-progress::before { content: ""; position: absolute; top: 27px; left: 28px; right: 28px; height: 4px; border-radius: 999px; background: #dbeafe; }
    .progress-point { position: relative; z-index: 1; display: grid; justify-items: center; gap: 8px; color: var(--text-muted); text-align: center; font-size: .76rem; }
    .progress-point i { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #e2e8f0; color: #64748b; font-size: .7rem; }
    .progress-point.done i, .progress-point.active i { background: var(--primary); color: #fff; }
    .progress-point.done, .progress-point.active { color: var(--text-heading); font-weight: 800; }
    .soft-block { border: 1px solid var(--border); border-radius: 8px; background: #fff; margin-top: 18px; overflow: hidden; }
    .soft-block-header { padding: 14px 16px; border-bottom: 1px solid var(--border); color: var(--text-heading); font-weight: 800; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .soft-block-body { padding: 16px; }
    .proof-image { width: 100%; border-radius: 8px; border: 1px solid var(--border); background: #f8fafc; object-fit: cover; max-height: 360px; }
    .muted-note { color: var(--text-muted); font-size: .82rem; line-height: 1.55; }
    .review-actions { display: grid; gap: 12px; }
    .review-actions form { margin: 0; }
    @media (max-width: 1024px) { .delivery-detail-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
        .delivery-progress { grid-template-columns: repeat(2, 1fr); row-gap: 18px; }
        .delivery-progress::before { display:none; }
    }
</style>

<div class="delivery-detail-grid">
    <main>
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-truck-fast"></i> {{ $pengiriman->no_invoice }}</h3>
                <span class="badge {{ $pengiriman->status_kirim === 'Tiba Di Tujuan' ? 'badge-success' : 'badge-info' }}">{{ $pengiriman->status_kirim }}</span>
            </div>
            <div class="card-body">
                <div class="delivery-hero">
                    <span>Status Validasi Penerimaan</span>
                    <strong><span class="badge {{ $pengiriman->delivery_verification_badge_class }}" style="font-size:1rem;">{{ $customerStatus }}</span></strong>
                    <p class="muted-note" style="margin-top:10px;">
                        Pengiriman diselesaikan setelah pelanggan mengirim bukti foto penerimaan dan admin/marketing memverifikasinya.
                    </p>
                </div>

                <div class="delivery-progress">
                    @foreach(['Pengiriman Dibuat', 'Sedang Dikirim', 'Bukti Customer', 'Selesai'] as $index => $label)
                        @php
                            $pointClass = $index < $shippingStep ? 'done' : ($index === $shippingStep ? 'active' : '');
                        @endphp
                        <div class="progress-point {{ $pointClass }}">
                            <i class="fas {{ $index <= $shippingStep ? 'fa-check' : 'fa-circle' }}"></i>
                            <span>{{ $label }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="info-grid">
                    <div class="info-box"><label>Pelanggan</label><p>{{ $pengajuan->pelanggan->nama_pelanggan }}</p></div>
                    <div class="info-box"><label>Motor</label><p>{{ $pengajuan->motor->nama_motor ?? '-' }}</p></div>
                    <div class="info-box"><label>Tanggal Kirim</label><p>{{ $pengiriman->tgl_kirim?->format('d/m/Y H:i') ?? '-' }}</p></div>
                    <div class="info-box"><label>Tanggal Selesai</label><p>{{ $pengiriman->tgl_tiba?->format('d/m/Y H:i') ?? '-' }}</p></div>
                    <div class="info-box"><label>Layanan</label><p>{{ strtoupper($pengiriman->courier_code ?? '-') }} {{ $pengiriman->courier_service }}</p></div>
                    <div class="info-box"><label>Ongkir Dibayar</label><p>{{ $pengiriman->shipping_cost ? 'Rp ' . number_format($pengiriman->shipping_cost, 0, ',', '.') : '-' }}</p></div>
                    <div class="info-box"><label>Origin</label><p>{{ $pengiriman->origin_label ?: '-' }}</p></div>
                    <div class="info-box"><label>Destination</label><p>{{ $pengiriman->destination_label ?: '-' }}</p></div>
                </div>

                <div class="soft-block">
                    <div class="soft-block-header"><span><i class="fas fa-user-check"></i> Penerima</span></div>
                    <div class="soft-block-body">
                        <div class="info-grid">
                            <div class="info-box"><label>Nama</label><p>{{ $pengiriman->receiver_name ?: '-' }}</p></div>
                            <div class="info-box"><label>Telepon</label><p>{{ $pengiriman->receiver_phone ?: '-' }}</p></div>
                            <div class="info-box" style="grid-column:1/-1;"><label>Alamat</label><p>{{ $pengiriman->receiver_address ?: '-' }}</p></div>
                        </div>
                    </div>
                </div>

                <div class="soft-block">
                    <div class="soft-block-header">
                        <span><i class="fas fa-image"></i> Bukti Penerimaan Customer</span>
                        <span class="badge {{ $pengiriman->delivery_verification_badge_class }}">{{ $customerStatus }}</span>
                    </div>
                    <div class="soft-block-body">
                        @if($proofUrl)
                            <img src="{{ $proofUrl }}" alt="Bukti penerimaan customer" class="proof-image">
                            <div class="info-grid" style="margin-top:14px;">
                                <div class="info-box"><label>Dikirim Customer</label><p>{{ $pengiriman->customer_received_at?->format('d/m/Y H:i') ?? '-' }}</p></div>
                                <div class="info-box"><label>Diverifikasi Oleh</label><p>{{ $pengiriman->deliveryVerifiedBy?->name ?? '-' }}</p></div>
                                <div class="info-box" style="grid-column:1/-1;"><label>Catatan Customer</label><p>{{ $pengiriman->customer_received_note ?: '-' }}</p></div>
                                <div class="info-box" style="grid-column:1/-1;"><label>Catatan Verifikasi</label><p>{{ $pengiriman->delivery_verification_note ?: '-' }}</p></div>
                            </div>
                        @else
                            <div class="muted-note">Pelanggan belum mengirim bukti foto penerimaan motor.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <aside>
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-clipboard-check"></i> Verifikasi Admin/Marketing</h3></div>
            <div class="card-body">
                @if($customerStatus === \App\Models\Pengiriman::VERIFICATION_PENDING)
                    <div class="review-actions">
                        <form action="{{ route('pengiriman.verifyReceived', $pengiriman) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Catatan Verifikasi <small>Opsional</small></label>
                                <textarea name="delivery_verification_note" class="form-control" placeholder="Contoh: bukti foto sesuai, motor diterima pelanggan."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;">
                                <i class="fas fa-check"></i> Selesaikan Pengiriman
                            </button>
                        </form>

                        <form action="{{ route('pengiriman.rejectReceived', $pengiriman) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Alasan Ditolak</label>
                                <textarea name="delivery_verification_note" class="form-control" required placeholder="Jelaskan bukti yang perlu dikirim ulang."></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                                <i class="fas fa-times"></i> Tolak Bukti
                            </button>
                        </form>
                    </div>
                @elseif($customerStatus === \App\Models\Pengiriman::VERIFICATION_ACCEPTED)
                    <div class="muted-note">
                        Pengiriman sudah selesai diverifikasi pada {{ $pengiriman->delivery_verified_at?->format('d/m/Y H:i') ?? '-' }} oleh {{ $pengiriman->deliveryVerifiedBy?->name ?? '-' }}.
                    </div>
                @elseif($customerStatus === \App\Models\Pengiriman::VERIFICATION_REJECTED)
                    <div class="alert alert-warning" style="margin-bottom:0;">
                        <i class="fas fa-triangle-exclamation"></i> Bukti terakhir ditolak. Menunggu pelanggan mengirim ulang bukti foto.
                    </div>
                @else
                    <div class="muted-note">Belum ada bukti penerimaan dari pelanggan. Status pengiriman tetap berjalan sampai customer melakukan konfirmasi.</div>
                @endif
            </div>
        </div>

        <div class="card" style="margin-top:18px;">
            <div class="card-header"><h3><i class="fas fa-note-sticky"></i> Catatan Operasional</h3></div>
            <div class="card-body">
                <div class="info-grid" style="grid-template-columns:1fr;">
                    <div class="info-box"><label>Kurir Internal</label><p>{{ $pengiriman->nama_kurir ?: '-' }}</p></div>
                    <div class="info-box"><label>Telepon Kurir</label><p>{{ $pengiriman->telpon_kurir ?: '-' }}</p></div>
                    <div class="info-box"><label>Catatan</label><p>{{ $pengiriman->keterangan ?: '-' }}</p></div>
                </div>
            </div>
        </div>
    </aside>
</div>
@endsection
