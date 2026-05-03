@extends('layouts.app')
@section('title', 'Tambah Pelanggan')
@section('page-title', 'Tambah Pelanggan')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-user-plus"></i> Pelanggan Baru</h3></div><div class="card-body">
<form action="{{ route('pelanggan.store') }}" method="POST" enctype="multipart/form-data">@csrf
    <div class="form-grid">
        <div class="form-group"><label>Nama</label><input type="text" name="nama_pelanggan" class="form-control" value="{{ old('nama_pelanggan') }}" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}"></div>
        <div class="form-group"><label>No KTP (16 digit)</label><input type="text" name="no_ktp" class="form-control" maxlength="16" value="{{ old('no_ktp') }}" required></div>
        <div class="form-group"><label>No Telepon</label><input type="text" name="no_telp" class="form-control" maxlength="12" inputmode="numeric" pattern="[0-9]*" data-phone-input value="{{ old('no_telp') }}" required><div class="field-hint">Maksimal 12 angka.</div></div>
        <div class="form-group"><label>Kota</label><input type="text" name="kota1" class="form-control" value="{{ old('kota1') }}"></div>
        <div class="form-group"><label>Propinsi</label><input type="text" name="propinsi1" class="form-control" value="{{ old('propinsi1') }}"></div>
        <div class="form-group"><label>Kodepos</label><input type="text" name="kodepos1" class="form-control" value="{{ old('kodepos1') }}"></div>
        <div class="form-group"><label>Foto</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
    </div>
    <div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control">{{ old('alamat') }}</textarea></div>
    <div class="btn-group"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button><a href="{{ route('pelanggan.index') }}" class="btn btn-outline">Batal</a></div>
</form></div></div>
@endsection
