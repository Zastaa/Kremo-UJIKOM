@extends('layouts.app')
@section('title', 'Motor')
@section('page-title', 'Kelola Motor')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-motorcycle"></i> Daftar Motor</h3>
        <a href="{{ route('motors.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Motor</a>
    </div>
    <div class="card-body table-wrapper">
        <table>
            <thead><tr><th>Motor</th><th>Jenis</th><th>Harga</th><th>Spesifikasi</th><th>Stok</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($motors as $motor)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img src="{{ $motor->primary_image_url }}" alt="{{ $motor->nama_motor }}" style="width:74px;height:50px;object-fit:cover;border-radius:8px;border:1px solid var(--border);background:#f8fafc;">
                            <div>
                                <div style="font-weight:700;color:var(--text-heading);">{{ $motor->nama_motor }}</div>
                                <div style="font-size:0.78rem;color:var(--text-muted);">{{ $motor->tahun_produksi ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-purple">{{ $motor->jenisMotor->jenis ?? '-' }}</span></td>
                    <td>Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</td>
                    <td>{{ $motor->warna ?: '-' }} / {{ $motor->kapasitas_mesin ?: '-' }}<br><span style="font-size:0.78rem;color:var(--text-muted);">{{ number_format($motor->shipping_weight_grams) }} gram</span></td>
                    <td><span class="badge {{ $motor->stok > 0 ? 'badge-success' : 'badge-danger' }}">{{ $motor->stok }}</span></td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('motors.show', $motor) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('motors.edit', $motor) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('motors.destroy', $motor) }}" method="POST" onsubmit="return confirm('Hapus motor ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty-state">Belum ada data motor</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $motors->links('pagination.simple') }}</div>
    </div>
</div>
@endsection
