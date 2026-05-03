@extends('layouts.app')
@section('title', 'Tambah Metode Bayar')
@section('page-title', 'Tambah Metode Bayar')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-plus"></i> Metode Bayar Baru</h3></div><div class="card-body">
<form action="{{ route('metode-bayar.store') }}" method="POST">@csrf
<div class="form-grid"><div class="form-group"><label>Metode Pembayaran</label><input type="text" name="metode_pembayaran" class="form-control" required></div><div class="form-group"><label>Tempat Bayar</label><input type="text" name="tempat_bayar" class="form-control"></div><div class="form-group"><label>No Rekening</label><input type="text" name="no_rekening" class="form-control"></div></div>
<div class="btn-group"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button><a href="{{ route('metode-bayar.index') }}" class="btn btn-outline">Batal</a></div>
</form></div></div>
@endsection
