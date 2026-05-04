@extends('layouts.app')
@section('title', 'Edit Motor')
@section('page-title', 'Edit Motor')
@section('content')
<div class="card">
    <div class="card-header"><h3><i class="fas fa-edit"></i> Edit Motor</h3></div>
    <div class="card-body">
        <form action="{{ route('motors.update', $motor) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group"><label>Nama Motor</label><input type="text" name="nama_motor" class="form-control" value="{{ old('nama_motor', $motor->nama_motor) }}" required></div>
                <div class="form-group"><label>Jenis Motor</label><select name="id_jenis" class="form-control" required><option value="">Pilih</option>@foreach($jenisMotor as $jm)<option value="{{ $jm->id }}" {{ $motor->id_jenis == $jm->id ? 'selected' : '' }}>{{ $jm->merk }} - {{ $jm->jenis }}</option>@endforeach</select></div>
                <div class="form-group"><label>Harga Jual (Rp)</label><input type="number" name="harga_jual" class="form-control" value="{{ old('harga_jual', $motor->harga_jual) }}" required></div>
                <div class="form-group"><label>Berat Pengiriman (gram)</label><input type="number" name="berat_gram" class="form-control" min="10000" step="1000" value="{{ old('berat_gram', $motor->shipping_weight_grams) }}" required><div class="field-hint">Dipakai otomatis untuk hitung ongkir RajaOngkir pada pengajuan motor ini.</div></div>
                <div class="form-group"><label>Warna</label><input type="text" name="warna" class="form-control" value="{{ old('warna', $motor->warna) }}"></div>
                <div class="form-group"><label>Kapasitas Mesin</label><input type="text" name="kapasitas_mesin" class="form-control" value="{{ old('kapasitas_mesin', $motor->kapasitas_mesin) }}"></div>
                <div class="form-group"><label>Tahun</label><input type="number" name="tahun_produksi" class="form-control" value="{{ old('tahun_produksi', $motor->tahun_produksi) }}"></div>
                <div class="form-group"><label>Stok</label><input type="number" name="stok" class="form-control" value="{{ old('stok', $motor->stok) }}" required></div>
            </div>
            <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi_motor" class="form-control">{{ old('deskripsi_motor', $motor->deskripsi_motor) }}</textarea></div>
            <div class="form-grid">
                <div class="form-group"><label>Foto 1 (opsional)</label><input type="file" name="foto1" class="form-control" accept="image/*"></div>
                <div class="form-group"><label>Foto 2 (opsional)</label><input type="file" name="foto2" class="form-control" accept="image/*"></div>
                <div class="form-group"><label>Foto 3 (opsional)</label><input type="file" name="foto3" class="form-control" accept="image/*"></div>
            </div>
            <div class="btn-group"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button><a href="{{ route('motors.index') }}" class="btn btn-outline">Batal</a></div>
        </form>
    </div>
</div>
@endsection
