@extends('layouts.app')
@section('title', 'Tambah Asuransi')
@section('page-title', 'Tambah Asuransi')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-plus"></i> Asuransi Baru</h3></div><div class="card-body">
<form action="{{ route('asuransi.store') }}" method="POST">@csrf
<div class="form-grid"><div class="form-group"><label>Perusahaan</label><input type="text" name="nama_perusahaan_asuransi" class="form-control" required></div><div class="form-group"><label>Nama Asuransi</label><input type="text" name="nama_asuransi" class="form-control" required></div><div class="form-group"><label>Margin (%)</label><input type="number" step="0.01" name="margin_asuransi" class="form-control" required></div><div class="form-group"><label>No Rekening</label><input type="text" name="no_rekening" class="form-control"></div></div>
<div class="btn-group"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button><a href="{{ route('asuransi.index') }}" class="btn btn-outline">Batal</a></div>
</form></div></div>
@endsection
