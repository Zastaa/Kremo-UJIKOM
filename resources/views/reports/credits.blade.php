@extends('layouts.app')
@section('title', 'Laporan Kredit')
@section('page-title', 'Laporan Kredit')
@section('content')
<div class="card" style="margin-bottom:24px"><div class="card-body">
<form method="GET" action="{{ route('reports.credits') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
<div class="form-group" style="margin-bottom:0;min-width:160px"><label>Tanggal Mulai</label><input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}"></div>
<div class="form-group" style="margin-bottom:0;min-width:160px"><label>Tanggal Akhir</label><input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}"></div>
<div class="form-group" style="margin-bottom:0;min-width:160px"><label>Status</label><select name="status" class="form-control"><option value="">Semua</option>@foreach(['Dicicil','Macet','Lunas'] as $s)<option value="{{ $s }}" {{ ($filters['status'] ?? '')===$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
<div class="form-group" style="margin-bottom:0;min-width:140px"><label>Tenor</label><select name="tenor_id" class="form-control"><option value="">Semua</option>@foreach($tenors as $t)<option value="{{ $t->id }}" {{ ($filters['tenor_id'] ?? '')==$t->id?'selected':'' }}>{{ $t->lama_cicilan }} Bulan</option>@endforeach</select></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
<a href="{{ route('reports.credits') }}" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</a>
</form></div></div>

<div class="stat-grid">
<div class="stat-card"><div class="stat-icon purple"><i class="fas fa-handshake"></i></div><div class="stat-info"><h4>{{ $report['total'] }}</h4><p>Total Kredit</p></div></div>
@foreach($report['payment_ratio'] as $r)
<div class="stat-card"><div class="stat-icon {{ $r['kategori']==='Lunas'?'green':($r['kategori']==='Macet'?'red':'blue') }}"><i class="fas {{ $r['kategori']==='Lunas'?'fa-check-circle':($r['kategori']==='Macet'?'fa-exclamation-triangle':'fa-sync') }}"></i></div><div class="stat-info"><h4>{{ $r['jumlah'] }} <small style="font-size:0.6em;color:var(--text-muted)">({{ $r['persentase'] }}%)</small></h4><p>{{ $r['kategori'] }}</p></div></div>
@endforeach
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
<div class="card"><div class="card-header"><h3>Rasio Pembayaran</h3></div><div class="card-body">@if(count($report['payment_ratio'])>0)<canvas id="ratioChart" height="200"></canvas>@else<div class="empty-state"><p>Belum ada data</p></div>@endif</div></div>
<div class="card"><div class="card-header"><h3>Tenor Populer</h3></div><div class="card-body">@if(count($report['tenor_distribution'])>0)<canvas id="tenorChart" height="200"></canvas>@else<div class="empty-state"><p>Belum ada data</p></div>@endif</div></div>
</div>

<div class="card" style="margin-bottom:24px"><div class="card-body" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
<span style="color:var(--text-muted);font-size:0.85rem"><i class="fas fa-download"></i> Export:</span>
<form method="POST" action="{{ route('reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="kredit"><input type="hidden" name="format" value="xlsx"><input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}"><input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}"><input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}"><input type="hidden" name="tenor_id" value="{{ $filters['tenor_id'] ?? '' }}"><button type="submit" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</button></form>
<form method="POST" action="{{ route('reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="kredit"><input type="hidden" name="format" value="csv"><input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}"><input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}"><input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}"><input type="hidden" name="tenor_id" value="{{ $filters['tenor_id'] ?? '' }}"><button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-file-csv"></i> CSV</button></form>
<form method="POST" action="{{ route('reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="kredit"><input type="hidden" name="format" value="pdf"><input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}"><input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}"><input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}"><input type="hidden" name="tenor_id" value="{{ $filters['tenor_id'] ?? '' }}"><button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button></form></div></div>

<div class="card"><div class="card-header"><h3>Detail Kredit ({{ $report['total'] }})</h3></div><div class="table-wrapper"><table><thead><tr><th>No</th><th>Mulai</th><th>Pelanggan</th><th>Motor</th><th>Total Kredit</th><th>Sisa</th><th>Status</th></tr></thead><tbody>
@forelse($report['data'] as $i => $k)
<tr><td>{{ $i+1 }}</td><td>{{ $k->tgl_mulai_kredit?->format('d/m/Y') }}</td><td>{{ $k->pengajuanKredit?->pelanggan?->nama_pelanggan ?? '-' }}</td><td>{{ $k->pengajuanKredit?->motor?->nama_motor ?? '-' }}</td><td>Rp {{ number_format($k->pengajuanKredit?->harga_kredit ?? 0,0,',','.') }}</td><td>Rp {{ number_format($k->sisa_kredit,0,',','.') }}</td><td><span class="badge {{ $k->status_kredit==='Lunas'?'badge-success':($k->status_kredit==='Macet'?'badge-danger':'badge-info') }}">{{ $k->status_kredit }}</span></td></tr>
@empty<tr><td colspan="7"><div class="empty-state"><p>Tidak ada data</p></div></td></tr>@endforelse
</tbody></table></div></div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
@if(count($report['payment_ratio'])>0)
new Chart(document.getElementById('ratioChart'),{type:'doughnut',data:{labels:{!! json_encode(array_column($report['payment_ratio'],'kategori')) !!},datasets:[{data:{!! json_encode(array_column($report['payment_ratio'],'jumlah')) !!},backgroundColor:['#10b981','#3b82f6','#ef4444']}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{color:'#94a3b8'}}}}});
@endif
@if(count($report['tenor_distribution'])>0)
new Chart(document.getElementById('tenorChart'),{type:'bar',data:{labels:{!! json_encode(array_column($report['tenor_distribution'],'tenor')) !!},datasets:[{label:'Jumlah',data:{!! json_encode(array_column($report['tenor_distribution'],'jumlah')) !!},backgroundColor:'#6366f1',borderRadius:8}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{color:'#94a3b8'}},x:{ticks:{color:'#94a3b8'}}}}});
@endif
</script>
@endpush
