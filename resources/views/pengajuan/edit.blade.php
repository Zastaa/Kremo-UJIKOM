@extends('layouts.app')
@section('title', 'Edit Pengajuan')
@section('page-title', 'Edit Pengajuan Kredit')
@section('content')
<div class="card">
    <div class="card-header"><h3><i class="fas fa-edit"></i> Edit Pengajuan #{{ $pengajuan->id }}</h3></div>
    <div class="card-body">
        <form action="{{ route('pengajuan.update', $pengajuan) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group"><label>Pelanggan</label><select name="id_pelanggan" class="form-control" required>@foreach($pelanggan as $p)<option value="{{ $p->id }}" {{ $pengajuan->id_pelanggan == $p->id ? 'selected' : '' }}>{{ $p->nama_pelanggan }}</option>@endforeach</select></div>
                <div class="form-group"><label>Motor</label><select name="id_motor" class="form-control" required>@foreach($motors as $m)<option value="{{ $m->id }}" {{ $pengajuan->id_motor == $m->id ? 'selected' : '' }}>{{ $m->nama_motor }}</option>@endforeach</select></div>
                <div class="form-group"><label>Harga Cash</label><input type="number" name="harga_cash" class="form-control" value="{{ $pengajuan->harga_cash }}" required></div>
                <div class="form-group"><label>DP</label><input type="number" name="dp" class="form-control" value="{{ $pengajuan->dp }}" required></div>
                <div class="form-group"><label>Tenor</label><select name="id_jenis_cicilan" class="form-control" required>@foreach($jenisCicilan as $jc)<option value="{{ $jc->id }}" {{ $pengajuan->id_jenis_cicilan == $jc->id ? 'selected' : '' }}>{{ $jc->lama_cicilan }} bulan</option>@endforeach</select></div>
                <div class="form-group"><label>Metode Bayar</label><select name="id_metode_bayar" class="form-control" required><option value="">Pilih metode bayar</option>@foreach($metodeBayar as $mb)<option value="{{ $mb->id }}" {{ $pengajuan->id_metode_bayar == $mb->id ? 'selected' : '' }}>{{ $mb->metode_pembayaran }}{{ $mb->tempat_bayar ? ' - ' . $mb->tempat_bayar : '' }}</option>@endforeach</select></div>
                <div class="form-group"><label>Asuransi</label><select name="id_asuransi" class="form-control"><option value="">Tanpa</option>@foreach($asuransi as $a)<option value="{{ $a->id }}" {{ $pengajuan->id_asuransi == $a->id ? 'selected' : '' }}>{{ $a->nama_asuransi }}</option>@endforeach</select></div>
            </div>
            <div class="btn-group"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button><a href="{{ route('pengajuan.show', $pengajuan) }}" class="btn btn-outline">Batal</a></div>
        </form>
    </div>
</div>
@endsection
