@extends('layouts.app')
@section('title', 'Edit Tenor')
@section('page-title', 'Edit Jenis Cicilan')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-edit"></i> Edit Jenis Cicilan</h3></div><div class="card-body">
<form action="{{ route('jenis-cicilan.update', $jenisCicilan) }}" method="POST">@csrf @method('PUT')
<div class="form-grid"><div class="form-group"><label>Lama Cicilan (bulan)</label><input type="number" name="lama_cicilan" class="form-control" value="{{ $jenisCicilan->lama_cicilan }}" required></div><div class="form-group"><label>Margin Kredit (%)</label><input type="number" step="0.01" name="margin_kredit" class="form-control" value="{{ $jenisCicilan->margin_kredit }}" required></div></div>
<div class="btn-group"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button><a href="{{ route('jenis-cicilan.index') }}" class="btn btn-outline">Batal</a></div>
</form></div></div>
@endsection
