@extends('layouts.app')
@section('title', 'Edit Metode Bayar')
@section('page-title', 'Edit Metode Bayar')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-edit"></i> Edit Metode Bayar</h3></div><div class="card-body">
<form action="{{ route('metode-bayar.update', $metodeBayar) }}" method="POST">@csrf @method('PUT')
<div class="form-grid"><div class="form-group"><label>Metode Pembayaran</label><input type="text" name="metode_pembayaran" class="form-control" value="{{ $metodeBayar->metode_pembayaran }}" required></div><div class="form-group"><label>Tempat Bayar</label><input type="text" name="tempat_bayar" class="form-control" value="{{ $metodeBayar->tempat_bayar }}"></div><div class="form-group"><label>No Rekening</label><input type="text" name="no_rekening" class="form-control" value="{{ $metodeBayar->no_rekening }}"></div></div>
<div class="btn-group"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button><a href="{{ route('metode-bayar.index') }}" class="btn btn-outline">Batal</a></div>
</form></div></div>
@endsection
