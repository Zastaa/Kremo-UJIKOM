@extends('layouts.app')
@section('title', $pelanggan->nama_pelanggan)
@section('page-title', 'Detail Pelanggan')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-user"></i> {{ $pelanggan->nama_pelanggan }}</h3><a href="{{ route('pelanggan.edit', $pelanggan) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a></div>
<div class="card-body"><div class="form-grid"><div><strong style="color:var(--text-muted);font-size:0.8rem;">KTP</strong><p>{{ $pelanggan->no_ktp }}</p></div><div><strong style="color:var(--text-muted);font-size:0.8rem;">TELEPON</strong><p>{{ $pelanggan->no_telp }}</p></div><div><strong style="color:var(--text-muted);font-size:0.8rem;">EMAIL</strong><p>{{ $pelanggan->email ?? '-' }}</p></div><div><strong style="color:var(--text-muted);font-size:0.8rem;">KOTA</strong><p>{{ $pelanggan->kota1 ?? '-' }}</p></div></div>
@if($pelanggan->alamat)<div style="margin-top:16px;"><strong style="color:var(--text-muted);font-size:0.8rem;">ALAMAT</strong><p>{{ $pelanggan->alamat }}</p></div>@endif
</div></div>
@if($pelanggan->pengajuanKredit->count())
<div class="card" style="margin-top:24px;"><div class="card-header"><h3><i class="fas fa-file-invoice"></i> Riwayat Pengajuan</h3></div><div class="card-body table-wrapper"><table><thead><tr><th>Motor</th><th>Kredit</th><th>Status</th><th>Tanggal</th></tr></thead><tbody>@foreach($pelanggan->pengajuanKredit as $pk)<tr><td>{{ $pk->motor->nama_motor }}</td><td>Rp {{ number_format($pk->harga_kredit,0,',','.') }}</td><td><span class="badge {{ $pk->status_pengajuan === 'Disetujui' ? 'badge-success' : 'badge-warning' }}">{{ $pk->status_pengajuan }}</span></td><td>{{ $pk->tgl_pengajuan_kredit->format('d/m/Y') }}</td></tr>@endforeach</tbody></table></div></div>
@endif
@endsection
