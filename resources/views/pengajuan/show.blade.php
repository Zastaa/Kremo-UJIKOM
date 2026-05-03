@extends('layouts.app')
@section('title', 'Detail Pengajuan #' . $pengajuan->id)
@section('page-title', 'Detail Pengajuan Kredit')

@php
    $statusClass = match($pengajuan->status_pengajuan) {
        'Disetujui', 'Diterima' => 'badge-success',
        'Ditolak', 'Dibatalkan Pembeli', 'Dibatalkan Penjual', 'Bermasalah' => 'badge-danger',
        'Survey' => 'badge-info',
        default => 'badge-warning',
    };
    $statusSteps = ['Menunggu Konfirmasi', 'Diproses', 'Survey', 'Disetujui', 'Diterima'];
    $currentStep = array_search($pengajuan->status_pengajuan, $statusSteps, true);
    $tenor = $pengajuan->jenisCicilan?->lama_cicilan ?? 0;
    $pokokKredit = max(($pengajuan->harga_cash ?? 0) - ($pengajuan->dp ?? 0), 0);
    $totalAsuransi = ($pengajuan->biaya_asuransi_perbulan ?? 0) * $tenor;
    $angsuran = $pengajuan->kredit?->angsuran?->sortBy('angsuran_ke') ?? collect();
    $lunasCount = $angsuran->where('status', 'Lunas')->count();
    $nextAngsuran = $angsuran->firstWhere('status', 'Belum Bayar');
    $nextPayableAngsuran = $angsuran->first(fn ($item) => $item->is_customer_payable);
    $pengiriman = $pengajuan->pengiriman;
    $dpPaid = $pengajuan->is_down_payment_paid;
    $dpPaymentStatus = $pengajuan->dp_payment_status ?: 'Belum Bayar';
    $dpStatusClass = match($dpPaymentStatus) {
        'Lunas' => 'badge-success',
        'Pending' => 'badge-info',
        'Gagal' => 'badge-danger',
        default => 'badge-warning',
    };
    $shippingCost = (int) ($pengajuan->shipping_cost ?? 0);
    $downPaymentTotal = (int) $pengajuan->dp + $shippingCost;
    $showDownPaymentPanel = $pengajuan->status_pengajuan === 'Disetujui';
    $canCustomerPayDp = auth()->user()->hasRole('customer') && $showDownPaymentPanel && ! $dpPaid;
    $shippingWeight = $pengajuan->motor?->shipping_weight_grams ?: ($defaultWeight ?? config('rajaongkir.default_weight', 125000));
    $documents = [
        ['label' => 'KK', 'path' => $pengajuan->url_kk],
        ['label' => 'KTP', 'path' => $pengajuan->url_ktp],
        ['label' => 'NPWP', 'path' => $pengajuan->url_npwp],
        ['label' => 'Slip Gaji', 'path' => $pengajuan->url_slip_gaji],
        ['label' => 'Foto', 'path' => $pengajuan->url_foto],
    ];
@endphp

@push('styles')
<style>
    .application-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; margin-bottom: 24px; padding-bottom: 18px; border-bottom: 1px solid var(--border); }
    .application-title { color: var(--text-heading); font-size: 1.35rem; font-weight: 800; line-height: 1.25; }
    .application-meta { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; color: var(--text-muted); font-size: 0.86rem; }
    .application-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .detail-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 22px; align-items: start; }
    .detail-stack { display: grid; gap: 22px; }
    .info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .info-item { border-bottom: 1px solid var(--border); padding-bottom: 12px; min-width: 0; }
    .info-label { color: var(--text-muted); font-size: 0.76rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; }
    .info-value { margin-top: 4px; color: var(--text-heading); font-weight: 700; overflow-wrap: anywhere; }
    .cost-list { display: grid; gap: 10px; }
    .cost-row { display: flex; justify-content: space-between; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .cost-row:last-child { border-bottom: 0; }
    .cost-label { color: var(--text-muted); }
    .cost-value { color: var(--text-heading); font-weight: 800; text-align: right; }
    .cost-row.total { padding-top: 16px; margin-top: 2px; border-top: 2px solid var(--border); border-bottom: 0; }
    .cost-row.total .cost-value { color: var(--primary-dark); font-size: 1.18rem; }
    .doc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
    .doc-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; border: 1px solid var(--border); border-radius: var(--radius); padding: 12px; background: #fff; }
    .doc-item strong { color: var(--text-heading); font-size: 0.9rem; }
    .timeline { display: grid; gap: 12px; }
    .timeline-step { display: grid; grid-template-columns: 28px 1fr; gap: 10px; align-items: start; color: var(--text-muted); }
    .timeline-dot { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--border); background: #fff; color: #94a3b8; font-size: 0.75rem; }
    .timeline-step.done .timeline-dot { border-color: rgba(5,150,105,0.25); background: rgba(5,150,105,0.12); color: var(--success); }
    .timeline-step.active .timeline-dot { border-color: rgba(37,99,235,0.3); background: rgba(37,99,235,0.12); color: var(--primary); }
    .timeline-step strong { color: var(--text-heading); font-size: 0.9rem; }
    .timeline-step span { display: block; margin-top: 2px; font-size: 0.78rem; }
    .note-box { border: 1px solid rgba(37,99,235,0.2); background: rgba(37,99,235,0.06); color: var(--text); border-radius: var(--radius); padding: 14px; }
    .payment-summary { display: grid; gap: 12px; }
    .payment-highlight { border: 1px solid var(--border); border-radius: var(--radius); padding: 14px; background: #f8fafc; }
    .payment-highlight strong { display: block; color: var(--text-heading); font-size: 1.2rem; margin-top: 4px; }
    .shipping-map-sim { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding: 22px 0 6px; margin: 12px 0; }
    .shipping-map-sim::before { content: ""; position: absolute; top: 32px; left: 20px; right: 20px; height: 4px; background: #dbeafe; border-radius: 999px; }
    .shipping-map-point { position: relative; z-index: 1; display: grid; justify-items: center; gap: 8px; color: var(--text-muted); text-align: center; font-size: .78rem; }
    .shipping-map-point i { width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #e2e8f0; color: #64748b; font-size: .7rem; }
    .shipping-map-point.done i, .shipping-map-point.active i { background: var(--primary); color: white; }
    .shipping-map-point.done, .shipping-map-point.active { color: var(--text-heading); font-weight: 800; }
    .shipping-payment-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(280px, 340px); gap: 18px; align-items: start; }
    .destination-box { position: relative; }
    .destination-results { position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 20; background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12); max-height: 250px; overflow: auto; display: none; }
    .destination-results.open { display: block; }
    .destination-option { width: 100%; text-align: left; padding: 12px 14px; border: 0; border-bottom: 1px solid var(--border); background: #fff; cursor: pointer; }
    .destination-option:last-child { border-bottom: 0; }
    .destination-option:hover { background: #f8fafc; }
    .destination-option strong { display: block; color: var(--text-heading); font-size: 0.9rem; }
    .destination-option span { display: block; color: var(--text-muted); font-size: 0.78rem; margin-top: 2px; }
    .rate-list { display: grid; gap: 10px; margin-top: 12px; }
    .rate-card { width: 100%; text-align: left; border: 1px solid var(--border); background: #fff; border-radius: var(--radius); padding: 14px; cursor: pointer; transition: 0.2s ease; }
    .rate-card:hover, .rate-card.selected { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); }
    .rate-row { display: flex; justify-content: space-between; gap: 12px; align-items: center; }
    .rate-name { color: var(--text-heading); font-weight: 800; }
    .rate-meta { color: var(--text-muted); font-size: 0.78rem; margin-top: 4px; }
    .rate-price { color: var(--primary-dark); font-weight: 800; white-space: nowrap; }
    .empty-inline { border: 1px dashed var(--border); border-radius: var(--radius); padding: 16px; color: var(--text-muted); text-align: center; font-size: 0.86rem; }
    .helper-text { color: var(--text-muted); font-size: 0.78rem; line-height: 1.5; margin-top: 6px; }
    .payment-total-box { border: 1px solid var(--border); border-radius: var(--radius); padding: 14px; background: #f8fafc; }
    .payment-total-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); }
    .payment-total-row:last-child { border-bottom: 0; }
    .payment-total-row span { color: var(--text-muted); }
    .payment-total-row strong { color: var(--text-heading); text-align: right; }
    .payment-total-row.total strong { color: var(--primary-dark); font-size: 1.28rem; }
    .btn[disabled] { opacity: .58; cursor: not-allowed; transform: none !important; }
    @media (max-width: 1040px) {
        .detail-layout { grid-template-columns: 1fr; }
        .application-header { flex-direction: column; }
        .application-actions { justify-content: flex-start; }
        .shipping-payment-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .info-grid { grid-template-columns: 1fr; }
        .application-actions .btn { width: 100%; justify-content: center; }
    }
</style>
@endpush

@section('content')
<div class="application-header">
    <div>
        <div class="application-title">Pengajuan Kredit #{{ $pengajuan->id }}</div>
        <div class="application-meta">
            <span><i class="fas fa-calendar"></i> {{ $pengajuan->tgl_pengajuan_kredit?->format('d/m/Y') ?? '-' }}</span>
            <span><i class="fas fa-motorcycle"></i> {{ $pengajuan->motor?->nama_motor ?? '-' }}</span>
            <span><span class="badge {{ $statusClass }}">{{ $pengajuan->status_pengajuan }}</span></span>
        </div>
    </div>
    <div class="application-actions">
        <a href="{{ route('pengajuan.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Kembali</a>
        @if($canCustomerPayDp)
            <a href="#dp-ongkir" class="btn btn-primary"><i class="fas fa-credit-card"></i> Bayar DP & Ongkir</a>
        @elseif(auth()->user()->hasRole('customer') && $nextPayableAngsuran)
            <a href="{{ route('payment.pay', $nextPayableAngsuran) }}" class="btn btn-primary"><i class="fas fa-credit-card"></i> Bayar Angsuran</a>
        @endif
    </div>
</div>

<div class="stat-grid" style="margin-bottom:24px;">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-info-circle"></i></div><div class="stat-info"><h4 style="font-size:1rem;"><span class="badge {{ $statusClass }}" style="font-size:0.9rem;">{{ $pengajuan->status_pengajuan }}</span></h4><p>Status Saat Ini</p></div></div>
    <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-money-bill"></i></div><div class="stat-info"><h4>Rp {{ number_format($pengajuan->harga_kredit, 0, ',', '.') }}</h4><p>Total Kredit</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-calendar"></i></div><div class="stat-info"><h4>Rp {{ number_format($pengajuan->cicilan_perbulan, 0, ',', '.') }}</h4><p>Cicilan / Bulan</p></div></div>
    <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-hand-holding-usd"></i></div><div class="stat-info"><h4>Rp {{ number_format($pengajuan->dp, 0, ',', '.') }}</h4><p>DP / <span class="badge {{ $dpStatusClass }}">{{ $dpPaymentStatus }}</span></p></div></div>
</div>

<div class="detail-layout">
    <main class="detail-stack">
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-receipt"></i> Ringkasan Pengajuan</h3></div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item"><div class="info-label">Pemohon</div><div class="info-value">{{ $pengajuan->pelanggan?->nama_pelanggan ?? '-' }}</div></div>
                    <div class="info-item"><div class="info-label">Motor</div><div class="info-value">{{ $pengajuan->motor?->nama_motor ?? '-' }}</div></div>
                    <div class="info-item"><div class="info-label">Jenis Motor</div><div class="info-value">{{ $pengajuan->motor?->jenisMotor?->jenis ?? '-' }}</div></div>
                    <div class="info-item"><div class="info-label">Tenor</div><div class="info-value">{{ $tenor ? $tenor . ' bulan' : '-' }}</div></div>
                    <div class="info-item"><div class="info-label">Metode Bayar</div><div class="info-value">{{ $pengajuan->metodeBayar?->metode_pembayaran ?? '-' }}{{ $pengajuan->metodeBayar?->tempat_bayar ? ' - ' . $pengajuan->metodeBayar->tempat_bayar : '' }}</div></div>
                    <div class="info-item"><div class="info-label">Asuransi</div><div class="info-value">{{ $pengajuan->asuransi?->nama_asuransi ?? 'Tanpa asuransi' }}</div></div>
                    <div class="info-item"><div class="info-label">Status</div><div class="info-value"><span class="badge {{ $statusClass }}">{{ $pengajuan->status_pengajuan }}</span></div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3><i class="fas fa-calculator"></i> Rincian Biaya</h3></div>
            <div class="card-body">
                <div class="cost-list">
                    <div class="cost-row"><div class="cost-label">Harga cash</div><div class="cost-value">Rp {{ number_format($pengajuan->harga_cash, 0, ',', '.') }}</div></div>
                    <div class="cost-row"><div class="cost-label">DP / uang muka</div><div class="cost-value">Rp {{ number_format($pengajuan->dp, 0, ',', '.') }}</div></div>
                    <div class="cost-row"><div class="cost-label">Ongkir pilihan pelanggan</div><div class="cost-value">{{ $shippingCost ? 'Rp ' . number_format($shippingCost, 0, ',', '.') : '-' }}</div></div>
                    <div class="cost-row"><div class="cost-label">Status DP & Ongkir</div><div class="cost-value"><span class="badge {{ $dpStatusClass }}">{{ $dpPaymentStatus }}</span></div></div>
                    <div class="cost-row"><div class="cost-label">Pokok kredit</div><div class="cost-value">Rp {{ number_format($pokokKredit, 0, ',', '.') }}</div></div>
                    <div class="cost-row"><div class="cost-label">Estimasi total asuransi</div><div class="cost-value">Rp {{ number_format($totalAsuransi, 0, ',', '.') }}</div></div>
                    <div class="cost-row total"><div class="cost-label">Total kredit</div><div class="cost-value">Rp {{ number_format($pengajuan->harga_kredit, 0, ',', '.') }}</div></div>
                    <div class="cost-row"><div class="cost-label">Cicilan per bulan</div><div class="cost-value">Rp {{ number_format($pengajuan->cicilan_perbulan, 0, ',', '.') }}</div></div>
                    <div class="cost-row"><div class="cost-label">Metode bayar pilihan</div><div class="cost-value">{{ $pengajuan->metodeBayar?->metode_pembayaran ?? '-' }}</div></div>
                </div>
            </div>
        </div>

        @if($showDownPaymentPanel)
        <div class="card" id="dp-ongkir">
            <div class="card-header">
                <h3><i class="fas fa-credit-card"></i> Pembayaran DP & Ongkir</h3>
                <span class="badge {{ $dpStatusClass }}">{{ $dpPaymentStatus }}</span>
            </div>
            <div class="card-body">
                @if($dpPaid)
                    <div class="note-box">
                        <strong><i class="fas fa-check-circle"></i> DP dan ongkir sudah lunas</strong>
                        <p style="margin:8px 0 0;">Pelanggan sudah membayar Rp {{ number_format($pengajuan->dp_paid_amount ?: $downPaymentTotal, 0, ',', '.') }} pada {{ $pengajuan->dp_paid_at?->format('d/m/Y H:i') ?? '-' }}. Admin/marketing sudah dapat membuat pengiriman.</p>
                    </div>
                    <div class="info-grid" style="margin-top:16px;">
                        <div class="info-item"><div class="info-label">Layanan</div><div class="info-value">{{ $pengajuan->selected_shipping_service }}</div></div>
                        <div class="info-item"><div class="info-label">Estimasi</div><div class="info-value">{{ $pengajuan->shipping_etd ?: '-' }}</div></div>
                        <div class="info-item"><div class="info-label">Ongkir</div><div class="info-value">Rp {{ number_format($shippingCost, 0, ',', '.') }}</div></div>
                        <div class="info-item"><div class="info-label">Tujuan</div><div class="info-value">{{ $pengajuan->shipping_destination_label ?: '-' }}</div></div>
                    </div>
                @elseif(auth()->user()->hasRole('customer'))
                    <form action="{{ route('payment.dp', $pengajuan) }}" method="POST" id="dp-shipping-form">
                        @csrf
                        <input type="hidden" id="origin_destination_id" name="origin_destination_id" value="{{ old('origin_destination_id', $pengajuan->shipping_origin_destination_id ?: $originDestinationId) }}">
                        <input type="hidden" id="origin_label" name="origin_label" value="{{ old('origin_label', $pengajuan->shipping_origin_label ?: $originLabel) }}">
                        <input type="hidden" id="destination_destination_id" name="destination_destination_id" value="{{ old('destination_destination_id', $pengajuan->shipping_destination_destination_id) }}">
                        <input type="hidden" id="destination_label" name="destination_label" value="{{ old('destination_label', $pengajuan->shipping_destination_label) }}">
                        <input type="hidden" id="courier_service" name="courier_service" value="{{ old('courier_service', $pengajuan->shipping_courier_service) }}">
                        <input type="hidden" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', $pengajuan->shipping_cost) }}">
                        <input type="hidden" id="shipping_etd" name="shipping_etd" value="{{ old('shipping_etd', $pengajuan->shipping_etd) }}">

                        <div class="shipping-payment-grid">
                            <div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Tujuan Pengiriman</label>
                                        <div class="destination-box">
                                            <input type="text" id="destination_search" class="form-control js-destination-search" data-target="destination" value="{{ old('destination_label', $pengajuan->shipping_destination_label) }}" placeholder="Cari kota/kecamatan tujuan">
                                            <div class="destination-results" id="destination-results"></div>
                                        </div>
                                        <div class="helper-text">Pilih destination RajaOngkir agar sistem bisa menghitung ongkir.</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Berat Paket (gram)</label>
                                        <input type="number" id="package_weight" name="package_weight" class="form-control" value="{{ old('package_weight', $shippingWeight) }}" readonly>
                                        <div class="helper-text">Berat mengikuti data motor.</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Kurir</label>
                                        <select id="courier_code" name="courier_code" class="form-control">
                                            <option value="">Semua kurir utama</option>
                                            @foreach($couriers as $code => $name)
                                                <option value="{{ $code }}" {{ old('courier_code', $pengajuan->shipping_courier_code) === $code ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline" id="calculate-rates">
                                    <i class="fas fa-calculator"></i> Hitung Ongkir
                                </button>
                                <div id="rate-list" class="rate-list">
                                    <div class="empty-inline">Cari tujuan, lalu hitung ongkir untuk memilih layanan.</div>
                                </div>
                            </div>

                            <aside class="payment-total-box">
                                <div class="payment-total-row"><span>DP</span><strong>Rp {{ number_format($pengajuan->dp, 0, ',', '.') }}</strong></div>
                                <div class="payment-total-row"><span>Ongkir</span><strong id="summary-shipping-cost">{{ $shippingCost ? 'Rp ' . number_format($shippingCost, 0, ',', '.') : 'Rp 0' }}</strong></div>
                                <div class="payment-total-row"><span>Layanan</span><strong id="summary-shipping-service">{{ $pengajuan->selected_shipping_service }}</strong></div>
                                <div class="payment-total-row total"><span>Total Bayar</span><strong id="summary-total">Rp {{ number_format($downPaymentTotal, 0, ',', '.') }}</strong></div>
                                <button type="submit" class="btn btn-primary" id="pay-dp-button" style="width:100%;margin-top:14px;" {{ $shippingCost ? '' : 'disabled' }}>
                                    <i class="fas fa-shield-alt"></i> Bayar DP & Ongkir
                                </button>
                                <div class="helper-text">Pembayaran dilakukan melalui Midtrans. Setelah lunas, pengiriman dapat dibuat oleh admin/marketing.</div>
                            </aside>
                        </div>
                    </form>
                @else
                    <div class="note-box">
                        <strong><i class="fas fa-clock"></i> Menunggu pembayaran pelanggan</strong>
                        <p style="margin:8px 0 0;">Pengiriman belum bisa dibuat karena pelanggan belum membayar DP dan memilih ongkir.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><h3><i class="fas fa-folder-open"></i> Dokumen Pengajuan</h3></div>
            <div class="card-body">
                <div class="doc-grid">
                    @foreach($documents as $document)
                        <div class="doc-item">
                            <strong>{{ $document['label'] }}</strong>
                            @if($document['path'])
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($document['path']) }}" target="_blank" class="btn btn-sm btn-outline">Lihat</a>
                            @else
                                <span class="badge badge-warning">Belum ada</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if(auth()->user()->hasRole(['admin','marketing','surveyor','approver']))
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-tasks"></i> Aksi Workflow</h3></div>
            <div class="card-body">
                @if(auth()->user()->hasRole(['admin','marketing']) && $pengajuan->status_pengajuan === 'Menunggu Konfirmasi')
                    <div class="note-box">
                        <strong><i class="fas fa-clock"></i> Menunggu surveyor</strong>
                        <p style="margin:8px 0 0;">Pengajuan ini belum diambil. Surveyor akan memilih sendiri dari pool pengajuan siap survey.</p>
                    </div>
                @endif

                @if(auth()->user()->hasRole(['admin','marketing']) && in_array($pengajuan->status_pengajuan, ['Diproses', 'Survey'], true))
                    <div class="note-box">
                        <strong><i class="fas fa-user-check"></i> Ditangani surveyor</strong>
                        <p style="margin:8px 0 0;">{{ $pengajuan->surveyor?->name ?? 'Surveyor' }} sedang menangani pengajuan ini.</p>
                    </div>
                @endif

                @if(auth()->user()->hasRole('surveyor') && $pengajuan->status_pengajuan === 'Menunggu Konfirmasi' && $pengajuan->surveyor_id === null)
                    <form action="{{ route('pengajuan.claim', $pengajuan) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary"><i class="fas fa-hand-pointer"></i> Ambil Tugas Survey</button>
                    </form>
                @endif

                @if(auth()->user()->hasRole('surveyor') && $pengajuan->status_pengajuan === 'Diproses' && $pengajuan->surveyor_id === auth()->id())
                    <form action="{{ route('pengajuan.survey', $pengajuan) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Catatan Survey</label>
                            <textarea name="catatan_survey" class="form-control" placeholder="Hasil survey lapangan..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Submit Hasil Survey</button>
                    </form>
                @endif

                @if(auth()->user()->hasRole('surveyor') && $pengajuan->status_pengajuan === 'Survey' && $pengajuan->surveyor_id === auth()->id())
                    <span class="badge badge-info" style="font-size:0.95rem;padding:8px 14px;">Hasil survey sudah dikirim</span>
                @endif

                @if(auth()->user()->hasRole('approver') && $pengajuan->status_pengajuan === 'Survey')
                    <div class="form-grid">
                        <form action="{{ route('pengajuan.approve', $pengajuan) }}" method="POST">
                            @csrf
                            <div class="note-box" style="margin-bottom:14px;">
                                <strong><i class="fas fa-user-check"></i> Survey dilakukan oleh</strong>
                                <p style="margin:8px 0 0;">{{ $pengajuan->surveyor?->name ?? 'Belum tercatat' }}{{ $pengajuan->surveyor?->email ? ' (' . $pengajuan->surveyor->email . ')' : '' }}</p>
                            </div>
                            <div class="note-box" style="margin-bottom:14px;">
                                <strong><i class="fas fa-credit-card"></i> Metode bayar pilihan customer</strong>
                                <p style="margin:8px 0 0;">{{ $pengajuan->metodeBayar?->metode_pembayaran ?? '-' }}{{ $pengajuan->metodeBayar?->tempat_bayar ? ' - ' . $pengajuan->metodeBayar->tempat_bayar : '' }}</p>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-check-double"></i> Setujui</button>
                        </form>
                        <form action="{{ route('pengajuan.reject', $pengajuan) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Alasan Penolakan</label>
                                <input type="text" name="keterangan" class="form-control" placeholder="Alasan...">
                            </div>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Tolak</button>
                        </form>
                    </div>
                @endif

                @if(! in_array($pengajuan->status_pengajuan, ['Menunggu Konfirmasi', 'Diproses', 'Survey'], true))
                    <span class="badge {{ $statusClass }}" style="font-size:0.95rem;padding:8px 14px;">{{ $pengajuan->status_pengajuan }}</span>
                @endif
            </div>
        </div>
        @endif

        @if($pengajuan->catatan_survey)
            <div class="note-box">
                <strong><i class="fas fa-clipboard"></i> Catatan Survey</strong>
                <p style="margin:8px 0 0;">{{ $pengajuan->catatan_survey }}</p>
            </div>
        @endif

        @if($pengiriman)
            @php
                $deliveryVerificationStatus = $pengiriman->delivery_verification_status ?: \App\Models\Pengiriman::VERIFICATION_UNCONFIRMED;
                $shippingStep = match (true) {
                    $pengiriman->status_kirim === 'Tiba Di Tujuan' => 3,
                    $deliveryVerificationStatus === \App\Models\Pengiriman::VERIFICATION_PENDING => 2,
                    $pengiriman->tgl_kirim !== null => 1,
                    default => 0,
                };
                $shippingDestination = $pengiriman->destination_label ?: ($pengiriman->receiver_address ?: '-');
                $customerProofUrl = $pengiriman->customer_received_photo
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($pengiriman->customer_received_photo)
                    : null;
            @endphp
            <div class="card" id="status-pengiriman">
                <div class="card-header">
                    <h3><i class="fas fa-truck-fast"></i> Status Pengiriman</h3>
                    <span class="badge {{ $pengiriman->delivery_verification_badge_class }}">{{ $deliveryVerificationStatus }}</span>
                </div>
                <div class="card-body">
                    <div class="shipping-map-sim">
                        @foreach(['Disiapkan', 'Sedang Dikirim', 'Bukti Dikirim', 'Selesai'] as $index => $label)
                            @php
                                $shippingPointClass = $index < $shippingStep ? 'done' : ($index === $shippingStep ? 'active' : '');
                                $shippingPointIcon = $index <= $shippingStep ? 'fa-check' : 'fa-circle';
                            @endphp
                            <div class="shipping-map-point {{ $shippingPointClass }}">
                                <i class="fas {{ $shippingPointIcon }}"></i>
                                <span>{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="info-grid">
                        <div class="info-item"><div class="info-label">Invoice</div><div class="info-value">{{ $pengiriman->no_invoice ?: '-' }}</div></div>
                        <div class="info-item"><div class="info-label">Status Kirim</div><div class="info-value">{{ $pengiriman->status_kirim }}</div></div>
                        <div class="info-item"><div class="info-label">Kurir</div><div class="info-value">{{ strtoupper($pengiriman->courier_code ?? '-') }} {{ $pengiriman->courier_service }}</div></div>
                        <div class="info-item"><div class="info-label">Estimasi</div><div class="info-value">{{ $pengiriman->shipping_etd ?: '-' }}</div></div>
                        <div class="info-item"><div class="info-label">Origin</div><div class="info-value">{{ $pengiriman->origin_label ?: '-' }}</div></div>
                        <div class="info-item"><div class="info-label">Destination</div><div class="info-value">{{ $shippingDestination }}</div></div>
                        <div class="info-item"><div class="info-label">Bukti Customer</div><div class="info-value">{{ $pengiriman->customer_received_at?->format('d/m/Y H:i') ?? '-' }}</div></div>
                        <div class="info-item"><div class="info-label">Verifikasi Admin</div><div class="info-value">{{ $pengiriman->delivery_verified_at?->format('d/m/Y H:i') ?? '-' }}</div></div>
                    </div>

                    @if(auth()->user()->hasRole('customer') && $pengiriman->can_customer_confirm_received)
                        <div class="note-box" style="margin-top:18px;">
                            <strong><i class="fas fa-camera"></i> Konfirmasi motor sudah diterima</strong>
                            <p style="margin:8px 0 14px;">Unggah bukti foto motor saat sudah sampai. Admin/marketing akan mengecek bukti ini sebelum pengiriman diselesaikan.</p>
                            @if($deliveryVerificationStatus === \App\Models\Pengiriman::VERIFICATION_REJECTED && $pengiriman->delivery_verification_note)
                                <div class="alert alert-warning" style="margin-bottom:14px;">
                                    <i class="fas fa-triangle-exclamation"></i> {{ $pengiriman->delivery_verification_note }}
                                </div>
                            @endif
                            <form action="{{ route('pengiriman.confirmReceived', $pengiriman) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label>Bukti Foto Penerimaan</label>
                                    <input type="file" name="customer_received_photo" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                                    <div class="helper-text">Format JPG, PNG, atau WebP. Maksimal 2 MB.</div>
                                </div>
                                <div class="form-group">
                                    <label>Catatan <small>Opsional</small></label>
                                    <textarea name="customer_received_note" class="form-control" placeholder="Contoh: motor sudah diterima dalam kondisi baik."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Kirim Bukti Penerimaan</button>
                            </form>
                        </div>
                    @elseif($customerProofUrl)
                        <div class="note-box" style="margin-top:18px;">
                            <strong><i class="fas fa-image"></i> Bukti penerimaan sudah dikirim</strong>
                            <p style="margin:8px 0 12px;">Status: <span class="badge {{ $pengiriman->delivery_verification_badge_class }}">{{ $deliveryVerificationStatus }}</span></p>
                            <a href="{{ $customerProofUrl }}" target="_blank" class="btn btn-sm btn-outline">Lihat Bukti Foto</a>
                            @if($pengiriman->delivery_verification_note)
                                <p style="margin:12px 0 0;color:var(--text-muted);">{{ $pengiriman->delivery_verification_note }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($pengajuan->kredit)
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-money-bill-wave"></i> Jadwal Angsuran</h3></div>
            <div class="card-body table-wrapper">
                <table>
                    <thead><tr><th>Ke-</th><th>Jatuh Tempo</th><th>Jumlah</th><th>Status</th><th>Tanggal Bayar</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach($angsuran as $a)
                            <tr>
                                <td>{{ $a->angsuran_ke }}</td>
                                <td>{{ $a->tgl_jatuh_tempo ? $a->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
                                <td>Rp {{ number_format($a->total_bayar, 0, ',', '.') }}</td>
                                <td><span class="badge {{ $a->status === 'Lunas' ? 'badge-success' : 'badge-warning' }}">{{ $a->status }}</span></td>
                                <td>{{ $a->tgl_bayar ? $a->tgl_bayar->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if($a->status === 'Belum Bayar' && auth()->user()->hasRole('customer'))
                                        @if($a->is_customer_payable)
                                            <a href="{{ route('payment.pay', $a) }}" class="btn btn-sm btn-primary">Bayar</a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline" disabled title="{{ $a->customer_payment_lock_reason }}">
                                                <i class="fas fa-lock"></i> Terkunci
                                            </button>
                                            <div style="color:var(--text-muted);font-size:0.76rem;margin-top:6px;max-width:180px;">{{ $a->customer_payment_lock_reason }}</div>
                                        @endif
                                    @elseif($a->status === 'Belum Bayar' && auth()->user()->hasRole('admin'))
                                        <form action="{{ route('angsuran.bayar', $a) }}" method="POST">@csrf
                                            <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Bayar</button>
                                        </form>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </main>

    <aside class="detail-stack">
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-route"></i> Status Pengajuan</h3></div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($statusSteps as $index => $step)
                        @php
                            $stepClass = $currentStep === false ? '' : ($index < $currentStep ? 'done' : ($index === $currentStep ? 'active' : ''));
                        @endphp
                        <div class="timeline-step {{ $stepClass }}">
                            <div class="timeline-dot"><i class="fas {{ $index < ($currentStep === false ? -1 : $currentStep) ? 'fa-check' : 'fa-circle' }}"></i></div>
                            <div><strong>{{ $step }}</strong><span>{{ $index === $currentStep ? 'Status saat ini' : 'Tahap pengajuan' }}</span></div>
                        </div>
                    @endforeach
                    @if($currentStep === false)
                        <div class="timeline-step active">
                            <div class="timeline-dot"><i class="fas fa-circle"></i></div>
                            <div><strong>{{ $pengajuan->status_pengajuan }}</strong><span>{{ $pengajuan->keterangan_status_pengajuan ?? 'Status akhir pengajuan' }}</span></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3><i class="fas fa-user-check"></i> Penugasan</h3></div>
            <div class="card-body">
                <div class="info-grid" style="grid-template-columns:1fr;">
                    <div class="info-item">
                        <div class="info-label">Surveyor</div>
                        <div class="info-value">
                            @if($pengajuan->surveyor)
                                {{ $pengajuan->surveyor->name }}
                            @else
                                <span class="badge badge-warning">Belum diambil</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Approver</div>
                        <div class="info-value">{{ $pengajuan->approver?->name ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Catatan Survey</div>
                        <div class="info-value">{{ $pengajuan->catatan_survey ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3><i class="fas fa-user"></i> Data Pemohon</h3></div>
            <div class="card-body">
                <div class="info-grid" style="grid-template-columns:1fr;">
                    <div class="info-item"><div class="info-label">Nama</div><div class="info-value">{{ $pengajuan->pelanggan?->nama_pelanggan ?? '-' }}</div></div>
                    <div class="info-item"><div class="info-label">KTP</div><div class="info-value">{{ $pengajuan->pelanggan?->no_ktp ?? '-' }}</div></div>
                    <div class="info-item"><div class="info-label">Telepon</div><div class="info-value">{{ $pengajuan->pelanggan?->no_telp ?? '-' }}</div></div>
                    <div class="info-item"><div class="info-label">Email</div><div class="info-value">{{ $pengajuan->pelanggan?->email ?? '-' }}</div></div>
                    <div class="info-item"><div class="info-label">Alamat</div><div class="info-value">{{ $pengajuan->pelanggan?->alamat ?? '-' }}</div></div>
                </div>
            </div>
        </div>

        @if($pengajuan->kredit)
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-chart-simple"></i> Ringkasan Angsuran</h3></div>
            <div class="card-body">
                <div class="payment-summary">
                    <div class="payment-highlight">
                        <span class="info-label">Angsuran Lunas</span>
                        <strong>{{ $lunasCount }} / {{ $angsuran->count() }}</strong>
                    </div>
                    <div class="payment-highlight">
                        <span class="info-label">Tagihan Berikutnya</span>
                        <strong>{{ $nextAngsuran ? 'Ke-' . $nextAngsuran->angsuran_ke : '-' }}</strong>
                        <div style="color:var(--text-muted);font-size:0.86rem;margin-top:4px;">
                            {{ $nextAngsuran?->tgl_jatuh_tempo?->format('d/m/Y') ?? 'Tidak ada tagihan aktif' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </aside>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('dp-shipping-form');
    if (!form) return;

    const rupiah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const dpAmount = Number(@json((int) $pengajuan->dp));

    const destinationSearch = document.getElementById('destination_search');
    const destinationResults = document.getElementById('destination-results');
    const destinationId = document.getElementById('destination_destination_id');
    const destinationLabel = document.getElementById('destination_label');
    const originId = document.getElementById('origin_destination_id');
    const packageWeight = document.getElementById('package_weight');
    const courierCode = document.getElementById('courier_code');
    const courierService = document.getElementById('courier_service');
    const shippingCost = document.getElementById('shipping_cost');
    const shippingEtd = document.getElementById('shipping_etd');
    const rateList = document.getElementById('rate-list');
    const payButton = document.getElementById('pay-dp-button');
    const summaryCost = document.getElementById('summary-shipping-cost');
    const summaryService = document.getElementById('summary-shipping-service');
    const summaryTotal = document.getElementById('summary-total');

    function debounce(callback, delay = 350) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => callback(...args), delay);
        };
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    function closeDestinationResults() {
        destinationResults.classList.remove('open');
    }

    function resetSelectedRate() {
        courierService.value = '';
        shippingCost.value = '';
        shippingEtd.value = '';
        summaryCost.textContent = rupiah.format(0);
        summaryService.textContent = '-';
        summaryTotal.textContent = rupiah.format(dpAmount);
        payButton.disabled = true;
    }

    function updateSummary(rate) {
        const cost = Number(rate.cost || 0);
        courierCode.value = rate.code;
        courierService.value = rate.service;
        shippingCost.value = cost;
        shippingEtd.value = rate.etd || '';
        summaryCost.textContent = rupiah.format(cost);
        summaryService.textContent = `${rate.name} ${rate.service}`;
        summaryTotal.textContent = rupiah.format(dpAmount + cost);
        payButton.disabled = cost <= 0;
    }

    function renderDestinationResults(results) {
        destinationResults.innerHTML = '';

        if (!results.length) {
            destinationResults.innerHTML = '<div class="destination-option"><strong>Tidak ada hasil</strong><span>Coba kata kunci yang lebih spesifik.</span></div>';
            destinationResults.classList.add('open');
            return;
        }

        results.forEach((item) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'destination-option';
            option.innerHTML = `<strong>${escapeHtml(item.label)}</strong><span>${escapeHtml([item.city_name, item.district_name, item.zip_code].filter(Boolean).join(' / '))}</span>`;
            option.addEventListener('click', () => {
                destinationSearch.value = item.label;
                destinationId.value = item.id;
                destinationLabel.value = item.label;
                resetSelectedRate();
                rateList.innerHTML = '<div class="empty-inline">Tujuan berubah. Hitung ulang ongkir untuk memilih layanan.</div>';
                closeDestinationResults();
            });
            destinationResults.appendChild(option);
        });

        destinationResults.classList.add('open');
    }

    const searchDestination = debounce(async () => {
        const search = destinationSearch.value.trim();
        if (search.length < 2) {
            closeDestinationResults();
            return;
        }

        try {
            const response = await fetch(`{{ route('shipping.destinations') }}?search=${encodeURIComponent(search)}&limit=12`, {
                headers: { 'Accept': 'application/json' },
            });
            const payload = await response.json();
            renderDestinationResults(payload.data || []);
        } catch (error) {
            destinationResults.innerHTML = '<div class="destination-option"><strong>Gagal mencari tujuan</strong><span>Periksa koneksi atau coba beberapa saat lagi.</span></div>';
            destinationResults.classList.add('open');
        }
    });

    destinationSearch.addEventListener('input', searchDestination);
    destinationSearch.addEventListener('input', () => {
        destinationId.value = '';
        destinationLabel.value = '';
        resetSelectedRate();
    });

    document.getElementById('calculate-rates').addEventListener('click', async () => {
        const origin = originId.value;
        const destination = destinationId.value;
        const weight = packageWeight.value;
        const courier = courierCode.value;

        if (!origin || !destination || !weight) {
            rateList.innerHTML = '<div class="empty-inline">Tujuan dan berat paket harus lengkap sebelum hitung ongkir.</div>';
            return;
        }

        rateList.innerHTML = '<div class="empty-inline">Mengambil pilihan ongkir...</div>';
        payButton.disabled = true;

        try {
            const response = await fetch(`{{ route('shipping.cost') }}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ origin, destination, weight, courier }),
            });
            const payload = await response.json();

            if (!payload.success || !payload.data.length) {
                rateList.innerHTML = `<div class="empty-inline">${escapeHtml(payload.message || 'Ongkir tidak tersedia untuk rute ini.')}</div>`;
                return;
            }

            rateList.innerHTML = '';
            payload.data.forEach((rate) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'rate-card';
                button.innerHTML = `
                    <div class="rate-row">
                        <div>
                            <div class="rate-name">${escapeHtml(rate.name)} ${escapeHtml(rate.service)}</div>
                            <div class="rate-meta">${escapeHtml(rate.description || 'Layanan pengiriman')} / ${escapeHtml(rate.etd || '-')}</div>
                        </div>
                        <div class="rate-price">${rupiah.format(Number(rate.cost || 0))}</div>
                    </div>
                `;
                button.addEventListener('click', () => {
                    document.querySelectorAll('#rate-list .rate-card').forEach((item) => item.classList.remove('selected'));
                    button.classList.add('selected');
                    updateSummary(rate);
                });
                rateList.appendChild(button);
            });
        } catch (error) {
            rateList.innerHTML = '<div class="empty-inline">Gagal menghitung ongkir. Coba lagi beberapa saat lagi.</div>';
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.destination-box')) {
            closeDestinationResults();
        }
    });

    if (Number(shippingCost.value || 0) > 0) {
        summaryTotal.textContent = rupiah.format(dpAmount + Number(shippingCost.value || 0));
        payButton.disabled = false;
    }
});
</script>
@endpush
