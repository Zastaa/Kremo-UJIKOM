@extends('layouts.app')
@section('title', 'Detail Kredit')
@section('page-title', 'Detail Kredit')
@section('content')
<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-money-bill"></i></div><div class="stat-info"><h4>Rp {{ number_format($kredit->sisa_kredit, 0, ',', '.') }}</h4><p>Sisa Kredit</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-calendar"></i></div><div class="stat-info"><h4>{{ $kredit->tgl_mulai_kredit->format('d/m/Y') }}</h4><p>Mulai Kredit</p></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-flag-checkered"></i></div><div class="stat-info"><h4>{{ $kredit->tgl_selesai_kredit ? $kredit->tgl_selesai_kredit->format('d/m/Y') : '-' }}</h4><p>Target Selesai</p></div></div>
    <div class="stat-card"><div class="stat-icon {{ $kredit->status_kredit === 'Lunas' ? 'green' : 'amber' }}"><i class="fas fa-info-circle"></i></div><div class="stat-info"><h4><span class="badge {{ $kredit->status_kredit === 'Lunas' ? 'badge-success' : 'badge-warning' }}">{{ $kredit->status_kredit }}</span></h4><p>Status</p></div></div>
</div>
<div class="card"><div class="card-header"><h3><i class="fas fa-money-bill-wave"></i> Jadwal Angsuran</h3></div>
<div class="card-body table-wrapper"><table><thead><tr><th>Ke-</th><th>Jatuh Tempo</th><th>Jumlah</th><th>Status</th><th>Tgl Bayar</th><th>Aksi</th></tr></thead>
<tbody>@foreach($kredit->angsuran->sortBy('angsuran_ke') as $a)<tr><td>{{ $a->angsuran_ke }}</td><td>{{ $a->tgl_jatuh_tempo ? $a->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td><td>Rp {{ number_format($a->total_bayar,0,',','.') }}</td><td><span class="badge {{ $a->status === 'Lunas' ? 'badge-success' : 'badge-warning' }}">{{ $a->status }}</span></td><td>{{ $a->tgl_bayar ? $a->tgl_bayar->format('d/m/Y') : '-' }}</td><td>@if($a->status === 'Belum Bayar')@if(auth()->user()->role === 'admin')<form action="{{ route('angsuran.bayar', $a) }}" method="POST">@csrf<button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Bayar</button></form>@elseif(auth()->user()->role === 'customer')@if($a->is_customer_payable)<a href="{{ route('payment.pay', $a) }}" class="btn btn-sm btn-primary"><i class="fas fa-credit-card"></i> Pay</a>@else<button type="button" class="btn btn-sm btn-outline" disabled title="{{ $a->customer_payment_lock_reason }}"><i class="fas fa-lock"></i> Terkunci</button><div style="color:var(--text-muted);font-size:0.76rem;margin-top:6px;max-width:190px;">{{ $a->customer_payment_lock_reason }}</div>@endif @endif @endif</td></tr>@endforeach</tbody></table></div></div>
@endsection
