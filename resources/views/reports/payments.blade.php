@extends('layouts.app')
@section('title', 'Laporan Pembayaran')
@section('page-title', 'Laporan Pembayaran')
@section('content')
<div class="card" style="margin-bottom:24px"><div class="card-body">
<form method="GET" action="{{ route('reports.payments') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
<div class="form-group" style="margin-bottom:0;min-width:160px"><label>Tanggal Mulai</label><input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}"></div>
<div class="form-group" style="margin-bottom:0;min-width:160px"><label>Tanggal Akhir</label><input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}"></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
<a href="{{ route('reports.payments') }}" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</a>
</form></div></div>

<div class="stat-grid">
<div class="stat-card"><div class="stat-icon green"><i class="fas fa-receipt"></i></div><div class="stat-info"><h4>{{ number_format($report['summary']['total_transaksi']) }}</h4><p>Total Transaksi Lunas</p></div></div>
<div class="stat-card"><div class="stat-icon purple"><i class="fas fa-wallet"></i></div><div class="stat-info"><h4>Rp {{ number_format($report['summary']['total_pendapatan'],0,',','.') }}</h4><p>Total Pendapatan</p></div></div>
<div class="stat-card"><div class="stat-icon blue"><i class="fas fa-chart-line"></i></div><div class="stat-info"><h4>Rp {{ number_format($report['summary']['rata_rata'],0,',','.') }}</h4><p>Rata-rata</p></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
<div class="card"><div class="card-header"><h3>Metode Pembayaran</h3></div><div class="card-body">
@if(count($report['payment_methods'])>0)<canvas id="methodChart" height="200"></canvas>
<div class="table-wrapper" style="margin-top:16px"><table><thead><tr><th>Metode</th><th>Jumlah</th><th>Total</th><th>%</th></tr></thead><tbody>
@foreach($report['payment_methods'] as $m)<tr><td>{{ $m['metode'] }}</td><td>{{ $m['jumlah'] }}</td><td>Rp {{ number_format($m['total_nominal'],0,',','.') }}</td><td>{{ $m['persentase'] }}%</td></tr>@endforeach
</tbody></table></div>@else<div class="empty-state"><p>Belum ada data</p></div>@endif</div></div>

<div class="card"><div class="card-header"><h3>Tren Pendapatan</h3></div><div class="card-body">
@if(count($report['revenue_trend'])>0)<canvas id="trendChart" height="200"></canvas>@else<div class="empty-state"><p>Belum ada data</p></div>@endif
</div></div>
</div>

<div class="card" style="margin-bottom:24px"><div class="card-body" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
<span style="color:var(--text-muted);font-size:0.85rem"><i class="fas fa-download"></i> Export:</span>
<form method="POST" action="{{ route('reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="pembayaran"><input type="hidden" name="format" value="xlsx"><input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}"><input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}"><button type="submit" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</button></form>
<form method="POST" action="{{ route('reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="pembayaran"><input type="hidden" name="format" value="csv"><input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}"><input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}"><button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-file-csv"></i> CSV</button></form>
<form method="POST" action="{{ route('reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="pembayaran"><input type="hidden" name="format" value="pdf"><input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}"><input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}"><button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button></form></div></div>

<div class="card"><div class="card-header"><h3>Detail Pembayaran ({{ $report['total'] }})</h3></div><div class="table-wrapper"><table><thead><tr><th>No</th><th>Tgl Bayar</th><th>Pelanggan</th><th>Motor</th><th>Angsuran Ke</th><th>Nominal</th><th>Metode</th></tr></thead><tbody>
@forelse($report['data'] as $i => $a)
<tr><td>{{ $i+1 }}</td><td>{{ $a->tgl_bayar?->format('d/m/Y') }}</td><td>{{ $a->kredit?->pengajuanKredit?->pelanggan?->nama_pelanggan ?? '-' }}</td><td>{{ $a->kredit?->pengajuanKredit?->motor?->nama_motor ?? '-' }}</td><td>{{ $a->angsuran_ke }}</td><td>Rp {{ number_format($a->total_bayar,0,',','.') }}</td><td><span class="badge {{ $a->payment_type?'badge-info':'badge-warning' }}">{{ $a->payment_type?'Midtrans':'Manual' }}</span></td></tr>
@empty<tr><td colspan="7"><div class="empty-state"><p>Tidak ada data</p></div></td></tr>@endforelse
</tbody></table></div></div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
@if(count($report['payment_methods'])>0)
new Chart(document.getElementById('methodChart'),{type:'doughnut',data:{labels:{!! json_encode(array_column($report['payment_methods'],'metode')) !!},datasets:[{data:{!! json_encode(array_column($report['payment_methods'],'jumlah')) !!},backgroundColor:['#6366f1','#f59e0b']}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{color:'#94a3b8'}}}}});
@endif
@if(count($report['revenue_trend'])>0)
new Chart(document.getElementById('trendChart'),{type:'line',data:{labels:{!! json_encode(array_map(fn($t)=>$t['tanggal']??$t['minggu']??'', $report['revenue_trend'])) !!},datasets:[{label:'Pendapatan',data:{!! json_encode(array_column($report['revenue_trend'],'total_pendapatan')) !!},borderColor:'#10b981',backgroundColor:'rgba(16,185,129,0.1)',fill:true,tension:0.4}]},options:{responsive:true,plugins:{legend:{labels:{color:'#94a3b8'}}},scales:{y:{beginAtZero:true,ticks:{color:'#94a3b8'}},x:{ticks:{color:'#94a3b8'}}}}});
@endif
</script>
@endpush
