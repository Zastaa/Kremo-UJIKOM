@extends('layouts.app')
@section('title', $motor->nama_motor)
@section('page-title', 'Detail Motor')
@section('content')
<div class="card">
    <div class="card-header"><h3><i class="fas fa-motorcycle"></i> {{ $motor->nama_motor }}</h3><a href="{{ route('motors.edit', $motor) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a></div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;align-items:start;">
            <img src="{{ $motor->primary_image_url }}" alt="{{ $motor->nama_motor }}" style="width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:8px;border:1px solid var(--border);background:#f8fafc;">
            <div class="form-grid">
                <div><strong style="color:var(--text-muted);font-size:0.8rem;">JENIS</strong><p>{{ $motor->jenisMotor->merk ?? '-' }} - {{ $motor->jenisMotor->jenis ?? '-' }}</p></div>
                <div><strong style="color:var(--text-muted);font-size:0.8rem;">HARGA</strong><p style="font-size:1.3rem;font-weight:700;color:var(--accent);">Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</p></div>
                <div><strong style="color:var(--text-muted);font-size:0.8rem;">WARNA</strong><p>{{ $motor->warna ?? '-' }}</p></div>
                <div><strong style="color:var(--text-muted);font-size:0.8rem;">MESIN</strong><p>{{ $motor->kapasitas_mesin ?? '-' }}</p></div>
                <div><strong style="color:var(--text-muted);font-size:0.8rem;">TAHUN</strong><p>{{ $motor->tahun_produksi ?? '-' }}</p></div>
                <div><strong style="color:var(--text-muted);font-size:0.8rem;">STOK</strong><p><span class="badge {{ $motor->stok > 0 ? 'badge-success' : 'badge-danger' }}">{{ $motor->stok }}</span></p></div>
            </div>
        </div>
        @if($motor->deskripsi_motor)
        <div style="margin-top:20px;"><strong style="color:var(--text-muted);font-size:0.8rem;">DESKRIPSI</strong><p>{{ $motor->deskripsi_motor }}</p></div>
        @endif
    </div>
</div>
@endsection
