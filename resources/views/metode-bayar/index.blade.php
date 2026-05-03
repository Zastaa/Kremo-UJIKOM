@extends('layouts.app')
@section('title', 'Metode Bayar')
@section('page-title', 'Kelola Metode Bayar')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-credit-card"></i> Metode Bayar</h3><a href="{{ route('metode-bayar.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah</a></div>
<div class="card-body table-wrapper"><table><thead><tr><th>Metode</th><th>Tempat</th><th>No Rekening</th><th>Aksi</th></tr></thead>
<tbody>@forelse($metodeBayar as $mb)<tr><td>{{ $mb->metode_pembayaran }}</td><td>{{ $mb->tempat_bayar }}</td><td>{{ $mb->no_rekening ?? '-' }}</td><td><div class="btn-group"><a href="{{ route('metode-bayar.edit', $mb) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a><form action="{{ route('metode-bayar.destroy', $mb) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></div></td></tr>@empty<tr><td colspan="4" style="text-align:center;">Belum ada data</td></tr>@endforelse</tbody></table>
<div class="pagination">{{ $metodeBayar->links('pagination.simple') }}</div></div></div>
@endsection
