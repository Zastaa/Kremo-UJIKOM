@extends('layouts.app')
@section('title', 'Buat Pengiriman')
@section('page-title', 'Buat Pengiriman')

@section('content')
<style>
    .shipping-panel { display: grid; grid-template-columns: minmax(0, 1fr) minmax(320px, 420px); gap: 20px; align-items: start; }
    .soft-section { border: 1px solid var(--border); border-radius: 8px; padding: 18px; background: #fff; }
    .soft-section + .soft-section { margin-top: 16px; }
    .soft-title { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; color: var(--text-heading); font-weight: 700; }
    .destination-box { position: relative; }
    .destination-results { position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 20; background: #fff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12); max-height: 260px; overflow: auto; display: none; }
    .destination-results.open { display: block; }
    .destination-option { padding: 12px 14px; cursor: pointer; border-bottom: 1px solid var(--border); }
    .destination-option:last-child { border-bottom: 0; }
    .destination-option:hover { background: #f8fafc; }
    .destination-option strong { display: block; color: var(--text-heading); font-size: 0.9rem; }
    .destination-option span { display: block; color: var(--text-muted); font-size: 0.78rem; margin-top: 2px; }
    .rate-list { display: grid; gap: 10px; margin-top: 12px; }
    .rate-card { width: 100%; text-align: left; border: 1px solid var(--border); background: #fff; border-radius: 8px; padding: 14px; cursor: pointer; transition: 0.2s ease; }
    .rate-card:hover, .rate-card.selected { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); }
    .rate-row { display: flex; justify-content: space-between; gap: 12px; align-items: center; }
    .rate-name { color: var(--text-heading); font-weight: 700; }
    .rate-meta { color: var(--text-muted); font-size: 0.78rem; margin-top: 4px; }
    .rate-price { color: var(--primary-dark); font-weight: 800; white-space: nowrap; }
    .helper-text { color: var(--text-muted); font-size: 0.78rem; line-height: 1.5; margin-top: 6px; }
    .empty-inline { border: 1px dashed var(--border); border-radius: 8px; padding: 18px; color: var(--text-muted); text-align: center; font-size: 0.86rem; }
    @media (max-width: 1024px) { .shipping-panel { grid-template-columns: 1fr; } }
</style>

<form action="{{ route('pengiriman.store') }}" method="POST" enctype="multipart/form-data" id="shipping-form">
    @csrf
    <div class="shipping-panel">
        <div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-truck-fast"></i> Data Pengiriman</h3>
                </div>
                <div class="card-body">
                    <div class="soft-section">
                        <div class="soft-title"><i class="fas fa-file-invoice"></i> Order Disetujui</div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="id_pengajuan_kredit">Pengajuan Kredit</label>
                                <select id="id_pengajuan_kredit" name="id_pengajuan_kredit" class="form-control" required>
                                    <option value="">Pilih pengajuan</option>
                                    @foreach($pengajuanList as $pengajuan)
                                        <option
                                            value="{{ $pengajuan->id }}"
                                            data-name="{{ $pengajuan->pelanggan->nama_pelanggan }}"
                                            data-phone="{{ $pengajuan->pelanggan->no_telp }}"
                                            data-address="{{ $pengajuan->pelanggan->alamat }}"
                                            data-weight="{{ $pengajuan->motor?->shipping_weight_grams ?? $defaultWeight }}"
                                            data-motor="{{ $pengajuan->motor?->nama_motor ?? '-' }}"
                                            data-origin-id="{{ $pengajuan->shipping_origin_destination_id }}"
                                            data-origin-label="{{ $pengajuan->shipping_origin_label }}"
                                            data-destination-id="{{ $pengajuan->shipping_destination_destination_id }}"
                                            data-destination-label="{{ $pengajuan->shipping_destination_label }}"
                                            data-courier-code="{{ $pengajuan->shipping_courier_code }}"
                                            data-courier-service="{{ $pengajuan->shipping_courier_service }}"
                                            data-shipping-cost="{{ $pengajuan->shipping_cost }}"
                                            data-shipping-etd="{{ $pengajuan->shipping_etd }}"
                                            {{ old('id_pengajuan_kredit') == $pengajuan->id ? 'selected' : '' }}
                                        >
                                            #{{ $pengajuan->id }} - {{ $pengajuan->pelanggan->nama_pelanggan }} / {{ $pengajuan->motor?->nama_motor ?? '-' }} / Ongkir lunas
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="no_invoice">No Invoice</label>
                                <input type="text" id="no_invoice" name="no_invoice" class="form-control" value="{{ old('no_invoice') }}" placeholder="Otomatis bila kosong">
                            </div>
                            <div class="form-group">
                                <label for="tgl_kirim">Tanggal Kirim</label>
                                <input type="datetime-local" id="tgl_kirim" name="tgl_kirim" class="form-control" value="{{ old('tgl_kirim') }}">
                            </div>
                        </div>
                    </div>

                    <div class="soft-section">
                        <div class="soft-title"><i class="fas fa-location-dot"></i> Penerima</div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="receiver_name">Nama Penerima</label>
                                <input type="text" id="receiver_name" name="receiver_name" class="form-control" value="{{ old('receiver_name') }}">
                            </div>
                            <div class="form-group">
                                <label for="receiver_phone">No Telepon</label>
                                <input type="text" id="receiver_phone" name="receiver_phone" class="form-control" maxlength="12" inputmode="numeric" pattern="[0-9]*" data-phone-input value="{{ old('receiver_phone') }}">
                                <div class="helper-text">Maksimal 12 angka.</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="receiver_address">Alamat Lengkap</label>
                            <textarea id="receiver_address" name="receiver_address" class="form-control">{{ old('receiver_address') }}</textarea>
                        </div>
                    </div>

                    <div class="soft-section">
                        <div class="soft-title"><i class="fas fa-map-location-dot"></i> RajaOngkir</div>
                        <input type="hidden" id="origin_destination_id" name="origin_destination_id" value="{{ old('origin_destination_id', $originDestinationId) }}">
                        <input type="hidden" id="origin_label" name="origin_label" value="{{ old('origin_label', $originLabel) }}">
                        <input type="hidden" id="destination_destination_id" name="destination_destination_id" value="{{ old('destination_destination_id') }}">
                        <input type="hidden" id="destination_label" name="destination_label" value="{{ old('destination_label') }}">
                        <input type="hidden" id="courier_service" name="courier_service" value="{{ old('courier_service') }}">
                        <input type="hidden" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost') }}">
                        <input type="hidden" id="shipping_etd" name="shipping_etd" value="{{ old('shipping_etd') }}">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="origin_search">Origin</label>
                                <div class="destination-box">
                                    <input type="text" id="origin_search" class="form-control js-destination-search" data-target="origin" value="{{ old('origin_label', $originLabel) }}" placeholder="Cari kota/kecamatan origin">
                                    <div class="destination-results" id="origin-results"></div>
                                </div>
                                <div class="helper-text">Isi `RAJAONGKIR_ORIGIN_DESTINATION_ID` agar origin otomatis terpilih.</div>
                            </div>
                            <div class="form-group">
                                <label for="destination_search">Destination</label>
                                <div class="destination-box">
                                    <input type="text" id="destination_search" class="form-control js-destination-search" data-target="destination" value="{{ old('destination_label') }}" placeholder="Cari tujuan penerima">
                                    <div class="destination-results" id="destination-results"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="package_weight">Berat Paket (gram)</label>
                                <input type="number" id="package_weight" name="package_weight" class="form-control" min="1" value="{{ old('package_weight', $defaultWeight) }}" readonly>
                                <div class="helper-text">Berat otomatis mengikuti data motor pada pengajuan.</div>
                            </div>
                            <div class="form-group">
                                <label for="courier_code">Kurir</label>
                                <select id="courier_code" name="courier_code" class="form-control">
                                    <option value="">Semua kurir utama</option>
                                    @foreach($couriers as $code => $name)
                                        <option value="{{ $code }}" {{ old('courier_code') === $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="calculate-rates">
                            <i class="fas fa-calculator"></i> Hitung Ongkir
                        </button>
                        <div id="rate-list" class="rate-list">
                            <div class="empty-inline">Pilih origin, destination, berat, lalu hitung ongkir.</div>
                        </div>
                    </div>

                    <div class="soft-section">
                        <div class="soft-title"><i class="fas fa-id-card-clip"></i> Kurir & Resi</div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nama_kurir">Nama Kurir Internal</label>
                                <input type="text" id="nama_kurir" name="nama_kurir" class="form-control" value="{{ old('nama_kurir') }}">
                            </div>
                            <div class="form-group">
                                <label for="telpon_kurir">Telepon Kurir</label>
                                <input type="text" id="telpon_kurir" name="telpon_kurir" class="form-control" maxlength="12" inputmode="numeric" pattern="[0-9]*" data-phone-input value="{{ old('telpon_kurir') }}">
                            </div>
                            <div class="form-group">
                                <label for="awb_number">No Referensi Pengiriman <small>Opsional</small></label>
                                <input type="text" id="awb_number" name="awb_number" class="form-control" value="{{ old('awb_number') }}">
                            </div>
                            <div class="form-group">
                                <label for="bukti_foto">Bukti Foto</label>
                                <input type="file" id="bukti_foto" name="bukti_foto" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Catatan</label>
                            <textarea id="keterangan" name="keterangan" class="form-control">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>

                    <div class="btn-group" style="margin-top:18px;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengiriman</button>
                        <a href="{{ route('pengiriman.index') }}" class="btn btn-outline">Batal</a>
                    </div>
                </div>
            </div>
        </div>

        <aside class="card">
            <div class="card-header">
                <h3><i class="fas fa-receipt"></i> Ringkasan</h3>
            </div>
            <div class="card-body">
                <div class="soft-section">
                    <div class="helper-text" style="margin-top:0;">Tujuan</div>
                    <div id="summary-destination" style="font-weight:700;color:var(--text-heading);margin-top:4px;">-</div>
                    <div class="helper-text" style="margin-top:14px;">Layanan</div>
                    <div id="summary-service" style="font-weight:700;color:var(--text-heading);margin-top:4px;">-</div>
                    <div class="helper-text" style="margin-top:14px;">Estimasi Biaya</div>
                    <div id="summary-cost" style="font-size:1.5rem;font-weight:800;color:var(--primary-dark);margin-top:4px;">Rp 0</div>
                </div>
            </div>
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const rupiah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function debounce(callback, delay = 350) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => callback(...args), delay);
        };
    }

    function closeDestinationResults() {
        document.querySelectorAll('.destination-results').forEach((node) => node.classList.remove('open'));
    }

    function renderDestinationResults(target, results) {
        const wrapper = document.getElementById(`${target}-results`);
        wrapper.innerHTML = '';

        if (!results.length) {
            wrapper.innerHTML = '<div class="destination-option"><strong>Tidak ada hasil</strong><span>Coba kata kunci yang lebih spesifik.</span></div>';
            wrapper.classList.add('open');
            return;
        }

        results.forEach((item) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'destination-option';
            option.innerHTML = `<strong>${item.label}</strong><span>${[item.city_name, item.district_name, item.zip_code].filter(Boolean).join(' / ')}</span>`;
            option.addEventListener('click', () => {
                document.getElementById(`${target}_search`).value = item.label;
                document.getElementById(`${target}_destination_id`).value = item.id;
                document.getElementById(`${target}_label`).value = item.label;
                if (target === 'destination') {
                    document.getElementById('summary-destination').textContent = item.label;
                }
                closeDestinationResults();
            });
            wrapper.appendChild(option);
        });

        wrapper.classList.add('open');
    }

    const searchDestination = debounce(async (event) => {
        const input = event.target;
        const search = input.value.trim();
        const target = input.dataset.target;

        if (search.length < 2) {
            closeDestinationResults();
            return;
        }

        const response = await fetch(`{{ route('shipping.destinations') }}?search=${encodeURIComponent(search)}&limit=12`, {
            headers: { 'Accept': 'application/json' },
        });
        const payload = await response.json();
        renderDestinationResults(target, payload.data || []);
    });

    document.querySelectorAll('.js-destination-search').forEach((input) => {
        input.addEventListener('input', searchDestination);
    });

    document.getElementById('id_pengajuan_kredit').addEventListener('change', (event) => {
        const option = event.target.selectedOptions[0];
        if (!option) return;

        document.getElementById('receiver_name').value = option.dataset.name || '';
        document.getElementById('receiver_phone').value = option.dataset.phone || '';
        document.getElementById('receiver_address').value = option.dataset.address || '';
        document.getElementById('package_weight').value = option.dataset.weight || '{{ $defaultWeight }}';
        document.getElementById('origin_destination_id').value = option.dataset.originId || '{{ $originDestinationId }}';
        document.getElementById('origin_label').value = option.dataset.originLabel || '{{ $originLabel }}';
        document.getElementById('origin_search').value = option.dataset.originLabel || '{{ $originLabel }}';
        document.getElementById('destination_destination_id').value = option.dataset.destinationId || '';
        document.getElementById('destination_label').value = option.dataset.destinationLabel || '';
        document.getElementById('destination_search').value = option.dataset.destinationLabel || '';
        document.getElementById('courier_code').value = option.dataset.courierCode || '';
        document.getElementById('courier_service').value = option.dataset.courierService || '';
        document.getElementById('shipping_cost').value = option.dataset.shippingCost || '';
        document.getElementById('shipping_etd').value = option.dataset.shippingEtd || '';
        document.getElementById('summary-destination').textContent = option.dataset.destinationLabel || '-';
        document.getElementById('summary-service').textContent = [option.dataset.courierCode?.toUpperCase(), option.dataset.courierService].filter(Boolean).join(' ') || '-';
        document.getElementById('summary-cost').textContent = option.dataset.shippingCost ? rupiah.format(Number(option.dataset.shippingCost)) : 'Rp 0';
    });

    if (document.getElementById('id_pengajuan_kredit').value) {
        document.getElementById('id_pengajuan_kredit').dispatchEvent(new Event('change'));
    }

    document.getElementById('calculate-rates').addEventListener('click', async () => {
        const origin = document.getElementById('origin_destination_id').value;
        const destination = document.getElementById('destination_destination_id').value;
        const weight = document.getElementById('package_weight').value;
        const courier = document.getElementById('courier_code').value;
        const list = document.getElementById('rate-list');

        if (!origin || !destination || !weight) {
            list.innerHTML = '<div class="empty-inline">Origin, destination, dan berat paket harus lengkap.</div>';
            return;
        }

        list.innerHTML = '<div class="empty-inline">Mengambil ongkir...</div>';

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
            list.innerHTML = `<div class="empty-inline">${payload.message || 'Ongkir tidak tersedia untuk rute ini.'}</div>`;
            return;
        }

        list.innerHTML = '';
        payload.data.forEach((rate) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'rate-card';
            button.innerHTML = `
                <div class="rate-row">
                    <div>
                        <div class="rate-name">${rate.name} ${rate.service}</div>
                        <div class="rate-meta">${rate.description || 'Layanan pengiriman'} / ${rate.etd || '-'}</div>
                    </div>
                    <div class="rate-price">${rupiah.format(rate.cost)}</div>
                </div>
            `;
            button.addEventListener('click', () => {
                document.querySelectorAll('.rate-card').forEach((item) => item.classList.remove('selected'));
                button.classList.add('selected');
                document.getElementById('courier_code').value = rate.code;
                document.getElementById('courier_service').value = rate.service;
                document.getElementById('shipping_cost').value = rate.cost;
                document.getElementById('shipping_etd').value = rate.etd || '';
                document.getElementById('summary-service').textContent = `${rate.name} ${rate.service}`;
                document.getElementById('summary-cost').textContent = rupiah.format(rate.cost);
            });
            list.appendChild(button);
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.destination-box')) {
            closeDestinationResults();
        }
    });
</script>
@endpush
