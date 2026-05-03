@extends('layouts.app')
@section('title', 'Data Pribadi')
@section('page-title', 'Data Pribadi')

@push('styles')
<style>
    .profile-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 22px; align-items: start; }
    .profile-preview { display: grid; gap: 14px; }
    .profile-avatar { width: 86px; height: 86px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #eff6ff; color: var(--primary-dark); font-size: 1.8rem; font-weight: 800; overflow: hidden; }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-row { border-bottom: 1px solid var(--border); padding-bottom: 12px; }
    .profile-row:last-child { border-bottom: 0; }
    .profile-row span { display: block; color: var(--text-muted); font-size: 0.76rem; font-weight: 800; text-transform: uppercase; }
    .profile-row strong { display: block; margin-top: 4px; color: var(--text-heading); overflow-wrap: anywhere; }
    @media (max-width: 980px) { .profile-layout { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="profile-layout">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-id-card"></i> Form Data Pribadi</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-user"></i> Identitas</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_pelanggan" class="form-control" value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan ?? auth()->user()->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" value="{{ auth()->user()->email }}" readonly>
                            <div class="field-hint">Email mengikuti akun login Anda.</div>
                        </div>
                        <div class="form-group">
                            <label>Nomor KTP</label>
                            <input type="text" name="no_ktp" class="form-control" maxlength="16" inputmode="numeric" value="{{ old('no_ktp', $pelanggan->no_ktp ?? '') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="no_telp" class="form-control" maxlength="12" inputmode="numeric" pattern="[0-9]*" data-phone-input value="{{ old('no_telp', $pelanggan->no_telp ?? auth()->user()->no_telp) }}" required>
                            <div class="field-hint">Maksimal 12 angka.</div>
                        </div>
                        <div class="form-group">
                            <label>Kota</label>
                            <input type="text" name="kota1" class="form-control" value="{{ old('kota1', $pelanggan->kota1 ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>Provinsi</label>
                            <input type="text" name="propinsi1" class="form-control" value="{{ old('propinsi1', $pelanggan->propinsi1 ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>Kode Pos</label>
                            <input type="text" name="kodepos1" class="form-control" value="{{ old('kodepos1', $pelanggan->kodepos1 ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>Foto</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" required>{{ old('alamat', $pelanggan->alamat ?? auth()->user()->alamat) }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Data</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <aside class="card">
        <div class="card-header"><h3><i class="fas fa-circle-user"></i> Ringkasan</h3></div>
        <div class="card-body profile-preview">
            <div class="profile-avatar">
                @if($pelanggan?->foto)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($pelanggan->foto) }}" alt="{{ $pelanggan->nama_pelanggan }}">
                @else
                    {{ strtoupper(substr($pelanggan->nama_pelanggan ?? auth()->user()->name, 0, 2)) }}
                @endif
            </div>
            <div class="profile-row"><span>Nama</span><strong>{{ $pelanggan->nama_pelanggan ?? auth()->user()->name }}</strong></div>
            <div class="profile-row"><span>Email</span><strong>{{ auth()->user()->email }}</strong></div>
            <div class="profile-row"><span>Telepon</span><strong>{{ $pelanggan->no_telp ?? auth()->user()->no_telp ?? '-' }}</strong></div>
            <div class="profile-row"><span>Status</span><strong>{{ $pelanggan ? 'Profil sudah tersimpan' : 'Profil belum lengkap' }}</strong></div>
        </div>
    </aside>
</div>
@endsection
