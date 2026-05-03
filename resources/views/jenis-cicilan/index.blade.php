@extends('layouts.app')
@section('title', 'Tenor')
@section('page-title', 'Kelola Tenor/Jenis Cicilan')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-calendar-alt"></i> Jenis Cicilan</h3><a href="{{ route('jenis-cicilan.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah</a></div>
<div class="card-body table-wrapper"><table><thead><tr><th>Lama Cicilan</th><th>Margin Kredit</th><th>Aksi</th></tr></thead>
<tbody>@forelse($jenisCicilan as $jc)<tr><td>{{ $jc->lama_cicilan }} bulan</td><td>{{ $jc->margin_kredit }}%</td><td><div class="btn-group"><a href="{{ route('jenis-cicilan.edit', $jc) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a><form action="{{ route('jenis-cicilan.destroy', $jc) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></div></td></tr>@empty<tr><td colspan="3" style="text-align:center;">Belum ada data</td></tr>@endforelse</tbody></table>
<div class="pagination">{{ $jenisCicilan->links('pagination.simple') }}</div></div></div>
@endsection
