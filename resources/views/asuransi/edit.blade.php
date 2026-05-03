@extends('layouts.app')
@section('title', 'Edit Asuransi')
@section('page-title', 'Edit Asuransi')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-edit"></i> Edit Asuransi</h3></div><div class="card-body">
<form action="{{ route('asuransi.update', $asuransi) }}" method="POST">@csrf @method('PUT')
<div class="form-grid"><div class="form-group"><label>Perusahaan</label><input type="text" name="nama_perusahaan_asuransi" class="form-control" value="{{ $asuransi->nama_perusahaan_asuransi }}" required></div><div class="form-group"><label>Nama Asuransi</label><input type="text" name="nama_asuransi" class="form-control" value="{{ $asuransi->nama_asuransi }}" required></div><div class="form-group"><label>Margin (%)</label><input type="number" step="0.01" name="margin_asuransi" class="form-control" value="{{ $asuransi->margin_asuransi }}" required></div><div class="form-group"><label>No Rekening</label><input type="text" name="no_rekening" class="form-control" value="{{ $asuransi->no_rekening }}"></div></div>
<div class="btn-group"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button><a href="{{ route('asuransi.index') }}" class="btn btn-outline">Batal</a></div>
</form></div></div>
@endsection
