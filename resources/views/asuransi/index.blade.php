@extends('layouts.app')
@section('title', 'Asuransi')
@section('page-title', 'Kelola Asuransi')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-shield-alt"></i> Asuransi</h3><a href="{{ route('asuransi.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah</a></div>
<div class="card-body table-wrapper"><table><thead><tr><th>Perusahaan</th><th>Nama Asuransi</th><th>Margin</th><th>Aksi</th></tr></thead>
<tbody>@forelse($asuransi as $a)<tr><td>{{ $a->nama_perusahaan_asuransi }}</td><td style="font-weight:600;">{{ $a->nama_asuransi }}</td><td>{{ $a->margin_asuransi }}%</td><td><div class="btn-group"><a href="{{ route('asuransi.edit', $a) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a><form action="{{ route('asuransi.destroy', $a) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></div></td></tr>@empty<tr><td colspan="4" style="text-align:center;">Belum ada data</td></tr>@endforelse</tbody></table>
<div class="pagination">{{ $asuransi->links('pagination.simple') }}</div></div></div>
@endsection
