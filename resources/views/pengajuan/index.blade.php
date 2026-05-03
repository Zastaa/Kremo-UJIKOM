@extends('layouts.app')
@section('title', 'Pengajuan Kredit')
@section('page-title', 'Pengajuan Kredit')
@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-file-invoice"></i> Daftar Pengajuan Kredit</h3>
        @if(auth()->user()->hasRole(['admin','marketing']))
        <a href="{{ route('pengajuan.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Pengajuan</a>
        @endif
    </div>
    <div class="card-body table-wrapper">
        <table>
            <thead><tr><th>ID</th><th>Pelanggan</th><th>Motor</th><th>Total Kredit</th><th>Cicilan/bln</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($pengajuan as $p)
                <tr>
                    <td>#{{ $p->id }}</td>
                    <td style="font-weight:600;">{{ $p->pelanggan->nama_pelanggan }}</td>
                    <td>{{ $p->motor->nama_motor }}</td>
                    <td>Rp {{ number_format($p->harga_kredit, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($p->cicilan_perbulan, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $statusClass = match($p->status_pengajuan) {
                                'Disetujui', 'Diterima' => 'badge-success',
                                'Ditolak', 'Dibatalkan Pembeli', 'Dibatalkan Penjual' => 'badge-danger',
                                'Survey' => 'badge-info',
                                'Bermasalah' => 'badge-danger',
                                default => 'badge-warning',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $p->status_pengajuan }}</span>
                    </td>
                    <td>{{ $p->tgl_pengajuan_kredit->format('d/m/Y') }}</td>
                    <td>
                        @if(auth()->user()->hasRole('surveyor') && $p->surveyor_id === null && $p->status_pengajuan === 'Menunggu Konfirmasi')
                            <form action="{{ route('pengajuan.claim', $p) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-primary"><i class="fas fa-hand-pointer"></i> Ambil</button>
                            </form>
                        @else
                            <a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:var(--text-muted);">Belum ada pengajuan</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $pengajuan->links('pagination.simple') }}</div>
    </div>
</div>
@endsection
