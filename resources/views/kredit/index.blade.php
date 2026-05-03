@extends('layouts.app')
@section('title', 'Kredit Aktif')
@section('page-title', 'Kredit Aktif')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-hand-holding-usd"></i> Daftar Kredit</h3></div>
<div class="card-body table-wrapper"><table><thead><tr><th>ID</th><th>Pelanggan</th><th>Motor</th><th>Sisa Kredit</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>@forelse($kredits as $k)<tr><td>#{{ $k->id }}</td><td>{{ $k->pengajuanKredit->pelanggan->nama_pelanggan }}</td><td>{{ $k->pengajuanKredit->motor->nama_motor }}</td><td>Rp {{ number_format($k->sisa_kredit, 0, ',', '.') }}</td><td><span class="badge {{ $k->status_kredit === 'Lunas' ? 'badge-success' : ($k->status_kredit === 'Macet' ? 'badge-danger' : 'badge-warning') }}">{{ $k->status_kredit }}</span></td><td><a href="{{ route('kredit.show', $k) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td></tr>@empty<tr><td colspan="6" style="text-align:center;color:var(--text-muted);">Belum ada data</td></tr>@endforelse</tbody></table>
<div class="pagination">{{ $kredits->links('pagination.simple') }}</div></div></div>
@endsection
