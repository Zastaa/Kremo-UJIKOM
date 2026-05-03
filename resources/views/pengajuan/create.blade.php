@extends('layouts.app')
@section('title', 'Buat Pengajuan Kredit')
@section('page-title', 'Buat Pengajuan Kredit')

@php
    $documentMaxKb = \App\Http\Requests\StorePengajuanKreditRequest::DOCUMENT_MAX_KB;
    $documentMaxMb = $documentMaxKb / 1024;
    $documentAccept = '.jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf';
    $selectedMotorId = old('id_motor', request('motor_id'));
@endphp

@push('styles')
<style>
    .motor-choice-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
    .motor-choice { position: relative; display: grid; gap: 12px; border: 1px solid var(--border); border-radius: var(--radius); background: #fff; overflow: hidden; cursor: pointer; transition: border-color .2s, box-shadow .2s, transform .2s; }
    .motor-choice:hover { transform: translateY(-1px); border-color: var(--primary); box-shadow: 0 14px 30px rgba(15, 23, 42, .08); }
    .motor-choice.selected { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .motor-choice input { position: absolute; opacity: 0; pointer-events: none; }
    .motor-choice-image { aspect-ratio: 16 / 10; background: #eef2f7; }
    .motor-choice-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .motor-choice-body { padding: 0 14px 14px; }
    .motor-choice-title { color: var(--text-heading); font-weight: 800; line-height: 1.3; }
    .motor-choice-meta { color: var(--text-muted); font-size: .78rem; margin-top: 4px; }
    .motor-choice-price { color: var(--accent-dark); font-weight: 800; margin-top: 10px; }
    .currency-display { color: var(--text-heading); font-weight: 800; background: #f8fafc; }
    .profile-notice { display: flex; justify-content: space-between; gap: 14px; align-items: center; flex-wrap: wrap; }
    @media (max-width: 560px) {
        .profile-notice .btn { width: 100%; justify-content: center; }
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> Pengajuan Kredit Baru</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data" id="pengajuan-form">
            @csrf

            @if(auth()->user()->role === 'customer')
                <input type="hidden" name="id_pelanggan" value="{{ $myPelanggan?->id }}">
                <div class="alert alert-info profile-notice">
                    <div>
                        <strong>Data pribadi dipakai dari profil.</strong>
                        {{ $myPelanggan?->nama_pelanggan }} | Telp: {{ $myPelanggan?->no_telp }} | KTP: {{ $myPelanggan?->no_ktp }}
                    </div>
                    <a href="{{ route('customer.profile.edit') }}" class="btn btn-sm btn-outline"><i class="fas fa-id-card"></i> Edit Data Pribadi</a>
                </div>
            @else
                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-user"></i> Data Pemohon</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Pelanggan</label>
                            <select name="id_pelanggan" class="form-control" required>
                                <option value="">Pilih Pelanggan</option>
                                @foreach($pelanggan as $p)
                                    <option value="{{ $p->id }}" {{ (string) old('id_pelanggan') === (string) $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_pelanggan }} ({{ $p->no_ktp ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-motorcycle"></i> Pilih Motor</div>
                <input type="hidden" name="id_motor" id="selectedMotor" value="{{ $selectedMotorId }}">
                <div class="motor-choice-grid">
                    @foreach($motors as $motor)
                        <label class="motor-choice {{ (string) $selectedMotorId === (string) $motor->id ? 'selected' : '' }}" data-motor-card>
                            <input type="radio" value="{{ $motor->id }}" data-harga="{{ $motor->harga_jual }}" {{ (string) $selectedMotorId === (string) $motor->id ? 'checked' : '' }}>
                            <div class="motor-choice-image"><img src="{{ $motor->primary_image_url }}" alt="{{ $motor->nama_motor }}"></div>
                            <div class="motor-choice-body">
                                <div class="motor-choice-title">{{ $motor->nama_motor }}</div>
                                <div class="motor-choice-meta">{{ $motor->jenisMotor?->merk ?? '-' }} / {{ $motor->jenisMotor?->jenis ?? '-' }} / Stok {{ $motor->stok }}</div>
                                <div class="motor-choice-price">Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-calculator"></i> Skema Kredit</div>
                <input type="hidden" name="harga_cash" id="hargaCash" value="{{ old('harga_cash') }}">
                <input type="hidden" name="dp" id="dpField" value="{{ old('dp') }}">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Harga Cash</label>
                        <input type="text" id="hargaCashDisplay" class="form-control currency-display" value="" readonly required>
                    </div>
                    <div class="form-group">
                        <label>DP / Uang Muka <small>Ditetapkan otomatis 20%</small></label>
                        <input type="text" id="dpDisplay" class="form-control currency-display" value="" readonly required>
                    </div>
                    <div class="form-group">
                        <label>Tenor Cicilan</label>
                        <select name="id_jenis_cicilan" class="form-control" required>
                            @foreach($jenisCicilan as $jc)
                                <option value="{{ $jc->id }}" {{ (string) old('id_jenis_cicilan') === (string) $jc->id ? 'selected' : '' }}>
                                    {{ $jc->lama_cicilan }} bulan (Margin {{ $jc->margin_kredit }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Metode Bayar</label>
                        <select name="id_metode_bayar" class="form-control" required>
                            <option value="">Pilih metode bayar</option>
                            @foreach($metodeBayar as $mb)
                                <option value="{{ $mb->id }}" {{ (string) old('id_metode_bayar') === (string) $mb->id ? 'selected' : '' }}>
                                    {{ $mb->metode_pembayaran }}{{ $mb->tempat_bayar ? ' - ' . $mb->tempat_bayar : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Asuransi Pelindung <small>Premi menambah total kredit</small></label>
                        <select name="id_asuransi" class="form-control">
                            <option value="">Tanpa Asuransi</option>
                            @foreach($asuransi as $a)
                                <option value="{{ $a->id }}" {{ (string) old('id_asuransi') === (string) $a->id ? 'selected' : '' }}>
                                    {{ $a->nama_asuransi }} (Premi {{ $a->margin_asuransi }}% dari harga)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-upload"></i> Upload Dokumen</div>
                <div class="field-hint" style="margin-bottom:16px;">Format yang diterima: JPG, PNG, atau PDF. Maksimal {{ $documentMaxMb }} MB per file.</div>
                <div class="form-grid">
                    <div class="form-group"><label>KK</label><input type="file" name="url_kk" class="form-control" accept="{{ $documentAccept }}" data-document-input data-label="File KK" data-max-kb="{{ $documentMaxKb }}"></div>
                    <div class="form-group"><label>KTP</label><input type="file" name="url_ktp" class="form-control" accept="{{ $documentAccept }}" data-document-input data-label="File KTP" data-max-kb="{{ $documentMaxKb }}"></div>
                    <div class="form-group"><label>NPWP</label><input type="file" name="url_npwp" class="form-control" accept="{{ $documentAccept }}" data-document-input data-label="File NPWP" data-max-kb="{{ $documentMaxKb }}"></div>
                    <div class="form-group"><label>Slip Gaji</label><input type="file" name="url_slip_gaji" class="form-control" accept="{{ $documentAccept }}" data-document-input data-label="File slip gaji" data-max-kb="{{ $documentMaxKb }}"></div>
                    <div class="form-group"><label>Foto</label><input type="file" name="url_foto" class="form-control" accept="{{ $documentAccept }}" data-document-input data-label="File foto" data-max-kb="{{ $documentMaxKb }}"></div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submit-pengajuan"><i class="fas fa-paper-plane"></i> Ajukan Kredit</button>
                <a href="{{ route('pengajuan.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rupiahFormatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
    const pengajuanForm = document.getElementById('pengajuan-form');
    const submitPengajuan = document.getElementById('submit-pengajuan');
    const selectedMotor = document.getElementById('selectedMotor');
    const hargaCash = document.getElementById('hargaCash');
    const dpField = document.getElementById('dpField');
    const hargaCashDisplay = document.getElementById('hargaCashDisplay');
    const dpDisplay = document.getElementById('dpDisplay');
    const documentInputs = document.querySelectorAll('[data-document-input]');
    const allowedDocumentExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

    function setSelectedMotor(card) {
        document.querySelectorAll('[data-motor-card]').forEach((item) => item.classList.remove('selected'));
        card.classList.add('selected');

        const input = card.querySelector('input[type="radio"]');
        input.checked = true;

        const harga = parseInt(input.dataset.harga, 10) || 0;
        const dp = harga ? Math.round(harga * 0.20) : 0;

        selectedMotor.value = input.value;
        hargaCash.value = harga || '';
        dpField.value = dp || '';
        hargaCashDisplay.value = harga ? rupiahFormatter.format(harga) : '';
        dpDisplay.value = dp ? rupiahFormatter.format(dp) : '';
    }

    document.querySelectorAll('[data-motor-card]').forEach((card) => {
        card.addEventListener('click', () => setSelectedMotor(card));
        if (card.querySelector('input[type="radio"]').checked) {
            setSelectedMotor(card);
        }
    });

    function showDocumentError(message) {
        if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Dokumen belum sesuai', text: message });
            return;
        }

        alert(message);
    }

    function formatFileSize(bytes) {
        if (bytes >= 1024 * 1024) {
            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        }

        return `${Math.ceil(bytes / 1024)} KB`;
    }

    function validateDocumentInput(input) {
        const file = input.files[0];

        if (!file) return null;

        const label = input.dataset.label || 'File dokumen';
        const maxBytes = Number(input.dataset.maxKb || 5120) * 1024;
        const extension = file.name.split('.').pop().toLowerCase();

        if (!allowedDocumentExtensions.includes(extension)) {
            return `${label} harus berformat JPG, PNG, atau PDF.`;
        }

        if (file.size > maxBytes) {
            return `${label} terlalu besar (${formatFileSize(file.size)}). Maksimal ${formatFileSize(maxBytes)}.`;
        }

        return null;
    }

    documentInputs.forEach((input) => {
        input.addEventListener('change', function() {
            const message = validateDocumentInput(input);

            if (message) {
                input.value = '';
                showDocumentError(message);
            }
        });
    });

    pengajuanForm.addEventListener('submit', function(event) {
        if (!selectedMotor.value) {
            event.preventDefault();
            showDocumentError('Pilih motor terlebih dahulu.');
            return;
        }

        for (const input of documentInputs) {
            const message = validateDocumentInput(input);

            if (message) {
                event.preventDefault();
                showDocumentError(message);
                return;
            }
        }

        submitPengajuan.disabled = true;
        submitPengajuan.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    });
</script>
@endpush
