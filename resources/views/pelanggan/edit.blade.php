@extends('layouts.app')
@section('title', 'Edit Pelanggan')
@section('page-title', 'Edit Pelanggan')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-edit"></i> Edit Pelanggan</h3></div><div class="card-body">
<form action="{{ route('pelanggan.update', $pelanggan) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
    <div class="form-grid">
        <div class="form-group"><label>Nama</label><input type="text" name="nama_pelanggan" class="form-control" value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan) }}" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $pelanggan->email) }}"></div>
        <div class="form-group"><label>No KTP</label><input type="text" name="no_ktp" class="form-control" maxlength="16" value="{{ old('no_ktp', $pelanggan->no_ktp) }}" required></div>
        <div class="form-group"><label>No Telepon</label><input type="text" name="no_telp" class="form-control" maxlength="12" inputmode="numeric" pattern="[0-9]*" data-phone-input value="{{ old('no_telp', $pelanggan->no_telp) }}" required><div class="field-hint">Maksimal 12 angka.</div></div>
        <div class="form-group"><label>Kota</label><input type="text" name="kota1" class="form-control" value="{{ old('kota1', $pelanggan->kota1) }}"></div>
        <div class="form-group"><label>Propinsi</label><input type="text" name="propinsi1" class="form-control" value="{{ old('propinsi1', $pelanggan->propinsi1) }}"></div>
    </div>
    <div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control">{{ old('alamat', $pelanggan->alamat) }}</textarea></div>
    <div class="btn-group"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button><a href="{{ route('pelanggan.index') }}" class="btn btn-outline">Batal</a></div>
</form></div></div>
@endsection
