@extends('layouts.app')
@section('title', 'Pelanggan')
@section('page-title', 'Kelola Pelanggan')
@section('content')
<div class="card">
    <div class="card-header"><h3><i class="fas fa-users"></i> Daftar Pelanggan</h3><a href="{{ route('pelanggan.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah</a></div>
    <div class="card-body table-wrapper">
        <table>
            <thead><tr><th>Nama</th><th>No KTP</th><th>Telepon</th><th>Kota</th><th>Aksi</th></tr></thead>
            <tbody>@forelse($pelanggan as $p)
                <tr><td style="font-weight:600;">{{ $p->nama_pelanggan }}</td><td>{{ $p->no_ktp }}</td><td>{{ $p->no_telp }}</td><td>{{ $p->kota1 ?? '-' }}</td>
                <td><div class="btn-group"><a href="{{ route('pelanggan.show', $p) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a><a href="{{ route('pelanggan.edit', $p) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a><form action="{{ route('pelanggan.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></div></td></tr>
            @empty<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Belum ada data</td></tr>@endforelse</tbody>
        </table>
        <div class="pagination">{{ $pelanggan->links('pagination.simple') }}</div>
    </div>
</div>
@endsection
