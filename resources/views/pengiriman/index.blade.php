@extends('layouts.app')
@section('title', 'Pengiriman')
@section('page-title', 'Kelola Pengiriman')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-truck"></i> Daftar Pengiriman</h3>
        <a href="{{ route('pengiriman.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Pengiriman</a>
    </div>
    <div class="card-body table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Pelanggan</th>
                    <th>Motor</th>
                    <th>Kurir</th>
                    <th>Status</th>
                    <th>Biaya</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengiriman as $p)
                    <tr>
                        <td>
                            <div style="font-weight:700;color:var(--text-heading);">{{ $p->no_invoice }}</div>
                            <div style="font-size:0.78rem;color:var(--text-muted);">{{ $p->tgl_kirim ? $p->tgl_kirim->format('d/m/Y H:i') : 'Belum dikirim' }}</div>
                        </td>
                        <td>{{ $p->pengajuanKredit->pelanggan->nama_pelanggan }}</td>
                        <td>{{ $p->pengajuanKredit->motor->nama_motor ?? '-' }}</td>
                        <td>
                            <div style="font-weight:600;">{{ strtoupper($p->courier_code ?? '-') }} {{ $p->courier_service }}</div>
                            <div style="font-size:0.78rem;color:var(--text-muted);">{{ $p->shipping_etd ?: $p->nama_kurir }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $p->delivery_verification_badge_class }}">
                                {{ $p->delivery_verification_status ?: \App\Models\Pengiriman::VERIFICATION_UNCONFIRMED }}
                            </span>
                            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;">{{ $p->status_kirim }}</div>
                        </td>
                        <td>{{ $p->shipping_cost ? 'Rp ' . number_format($p->shipping_cost, 0, ',', '.') : '-' }}</td>
                        <td>
                            <a href="{{ route('pengiriman.show', $p) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">Belum ada data pengiriman</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $pengiriman->links('pagination.simple') }}</div>
    </div>
</div>
@endsection
