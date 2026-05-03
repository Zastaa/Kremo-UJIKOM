@extends('layouts.app')
@section('title', 'Tambah Motor')
@section('page-title', 'Tambah Motor')

@section('content')
<div class="card">
    <div class="card-header"><h3><i class="fas fa-plus"></i> Tambah Motor Baru</h3></div>
    <div class="card-body">
        <form action="{{ route('motors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_motor">Nama Motor</label>
                    <input type="text" id="nama_motor" name="nama_motor" class="form-control" value="{{ old('nama_motor') }}" required>
                </div>
                <div class="form-group">
                    <label for="id_jenis">Jenis Motor</label>
                    <select id="id_jenis" name="id_jenis" class="form-control" required>
                        <option value="">Pilih Jenis</option>
                        @foreach($jenisMotor as $jm)
                            <option value="{{ $jm->id }}">{{ $jm->merk }} - {{ $jm->jenis }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga_jual">Harga Jual (Rp)</label>
                    <input type="number" id="harga_jual" name="harga_jual" class="form-control" value="{{ old('harga_jual') }}" required>
                </div>
                <div class="form-group">
                    <label for="berat_gram">Berat Pengiriman (gram)</label>
                    <input type="number" id="berat_gram" name="berat_gram" class="form-control" value="{{ old('berat_gram', config('rajaongkir.default_weight')) }}" min="1">
                    <div class="field-hint">Berat ini dipakai otomatis saat membuat pengiriman dari pengajuan motor ini.</div>
                </div>
                <div class="form-group">
                    <label for="warna">Warna</label>
                    <input type="text" id="warna" name="warna" class="form-control" value="{{ old('warna') }}">
                </div>
                <div class="form-group">
                    <label for="kapasitas_mesin">Kapasitas Mesin</label>
                    <input type="text" id="kapasitas_mesin" name="kapasitas_mesin" class="form-control" value="{{ old('kapasitas_mesin') }}" placeholder="e.g. 150cc">
                </div>
                <div class="form-group">
                    <label for="tahun_produksi">Tahun Produksi</label>
                    <input type="number" id="tahun_produksi" name="tahun_produksi" class="form-control" value="{{ old('tahun_produksi', date('Y')) }}">
                </div>
                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" id="stok" name="stok" class="form-control" value="{{ old('stok', 0) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label for="deskripsi_motor">Deskripsi</label>
                <textarea id="deskripsi_motor" name="deskripsi_motor" class="form-control">{{ old('deskripsi_motor') }}</textarea>
            </div>
            <div class="form-grid">
                <div class="form-group"><label for="foto1">Foto 1</label><input type="file" id="foto1" name="foto1" class="form-control" accept="image/*"></div>
                <div class="form-group"><label for="foto2">Foto 2</label><input type="file" id="foto2" name="foto2" class="form-control" accept="image/*"></div>
                <div class="form-group"><label for="foto3">Foto 3</label><input type="file" id="foto3" name="foto3" class="form-control" accept="image/*"></div>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('motors.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
