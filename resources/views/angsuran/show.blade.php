@extends('layouts.app')
@section('title', 'Detail Angsuran')
@section('page-title', 'Detail Angsuran')
@section('content')
<div class="card"><div class="card-header"><h3><i class="fas fa-money-bill-wave"></i> Angsuran Ke-{{ $angsuran->angsuran_ke }}</h3></div>
<div class="card-body"><div class="form-grid"><div><strong style="color:var(--text-muted);font-size:0.8rem;">PELANGGAN</strong><p>{{ $angsuran->kredit->pengajuanKredit->pelanggan->nama_pelanggan }}</p></div><div><strong style="color:var(--text-muted);font-size:0.8rem;">MOTOR</strong><p>{{ $angsuran->kredit->pengajuanKredit->motor->nama_motor }}</p></div><div><strong style="color:var(--text-muted);font-size:0.8rem;">JATUH TEMPO</strong><p>{{ $angsuran->tgl_jatuh_tempo?->format('d/m/Y') ?? '-' }}</p></div><div><strong style="color:var(--text-muted);font-size:0.8rem;">BISA DIBAYAR MULAI</strong><p>{{ $angsuran->payable_from?->format('d/m/Y') ?? '-' }}</p></div><div><strong style="color:var(--text-muted);font-size:0.8rem;">JUMLAH</strong><p style="font-size:1.3rem;font-weight:700;color:var(--accent);">Rp {{ number_format($angsuran->total_bayar,0,',','.') }}</p></div><div><strong style="color:var(--text-muted);font-size:0.8rem;">STATUS</strong><p><span class="badge {{ $angsuran->status === 'Lunas' ? 'badge-success' : 'badge-warning' }}">{{ $angsuran->status }}</span></p></div></div>
@if($angsuran->status === 'Belum Bayar' && auth()->user()->hasRole('customer'))
<div style="margin-top:20px;">
    @if($angsuran->is_customer_payable)
        <a href="{{ route('payment.pay', $angsuran) }}" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;"><i class="fas fa-credit-card"></i> Bayar via Midtrans</a>
    @else
        <div class="alert alert-info" style="margin-bottom:0;">
            <i class="fas fa-lock"></i> {{ $angsuran->customer_payment_lock_reason }}
        </div>
    @endif
</div>
@endif
</div></div>
@endsection
