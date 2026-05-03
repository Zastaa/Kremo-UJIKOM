@extends('layouts.app')
@section('title', 'Angsuran')
@section('page-title', 'Daftar Angsuran')
@section('content')
<div class="card">
    <div class="card-header"><h3><i class="fas fa-money-bill-wave"></i> Angsuran</h3></div>
    <div class="card-body table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID Kredit</th>
                    <th>Pelanggan</th>
                    <th>Ke-</th>
                    <th>Jatuh Tempo</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Tgl Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($angsuran as $a)
                    <tr>
                        <td>#{{ $a->id_kredit }}</td>
                        <td>{{ $a->kredit->pengajuanKredit->pelanggan->nama_pelanggan ?? '-' }}</td>
                        <td>{{ $a->angsuran_ke }}</td>
                        <td>{{ $a->tgl_jatuh_tempo ? $a->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
                        <td>Rp {{ number_format($a->total_bayar,0,',','.') }}</td>
                        <td><span class="badge {{ $a->status === 'Lunas' ? 'badge-success' : 'badge-warning' }}">{{ $a->status }}</span></td>
                        <td>{{ $a->tgl_bayar ? $a->tgl_bayar->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($a->status === 'Belum Bayar')
                                @if(auth()->user()->hasRole('admin'))
                                    <form action="{{ route('angsuran.bayar', $a) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Manual</button>
                                    </form>
                                @elseif(auth()->user()->hasRole('customer'))
                                    @if($a->is_customer_payable)
                                        <a href="{{ route('payment.pay', $a) }}" class="btn btn-sm btn-primary"><i class="fas fa-credit-card"></i> Pay</a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline" disabled title="{{ $a->customer_payment_lock_reason }}">
                                            <i class="fas fa-lock"></i> Terkunci
                                        </button>
                                        <div style="color:var(--text-muted);font-size:0.76rem;margin-top:6px;max-width:190px;">{{ $a->customer_payment_lock_reason }}</div>
                                    @endif
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;color:var(--text-muted);">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $angsuran->links('pagination.simple') }}</div>
    </div>
</div>
@endsection
