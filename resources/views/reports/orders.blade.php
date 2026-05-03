@extends('layouts.app')
@section('title', 'Laporan Order')
@section('page-title', 'Laporan Order')

@section('content')
<!-- Filters -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.orders') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; min-width: 160px;">
                <label>Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 160px;">
                <label>Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 160px;">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    @foreach(['Menunggu Konfirmasi','Diproses','Survey','Disetujui','Ditolak','Diterima','Dibatalkan Pembeli','Dibatalkan Penjual','Bermasalah'] as $s)
                        <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 160px;">
                <label>Motor</label>
                <select name="motor_id" class="form-control">
                    <option value="">Semua Motor</option>
                    @foreach($motors as $m)
                        <option value="{{ $m->id }}" {{ ($filters['motor_id'] ?? '') == $m->id ? 'selected' : '' }}>{{ $m->nama_motor }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 140px;">
                <label>Tenor</label>
                <select name="tenor_id" class="form-control">
                    <option value="">Semua Tenor</option>
                    @foreach($tenors as $t)
                        <option value="{{ $t->id }}" {{ ($filters['tenor_id'] ?? '') == $t->id ? 'selected' : '' }}>{{ $t->lama_cicilan }} Bulan</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('reports.orders') }}" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</a>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-info">
            <h4>{{ number_format($report['transaction_stats']['total_order']) }}</h4>
            <p>Total Order</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-info">
            <h4>Rp {{ number_format($report['transaction_stats']['total_transaksi'], 0, ',', '.') }}</h4>
            <p>Total Transaksi</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
            <h4>Rp {{ number_format($report['transaction_stats']['rata_rata'], 0, ',', '.') }}</h4>
            <p>Rata-rata</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-info">
            <h4>Rp {{ number_format($report['transaction_stats']['tertinggi'], 0, ',', '.') }}</h4>
            <p>Tertinggi</p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
    <!-- Top 3 Motors -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-trophy"></i> Top 3 Motor</h3></div>
        <div class="card-body">
            @if(count($report['top_motors']) > 0)
                <canvas id="topMotorsChart" height="200"></canvas>
                <div class="table-wrapper" style="margin-top: 16px;">
                    <table>
                        <thead><tr><th>Motor</th><th>Jumlah</th><th>Total Nilai</th><th>%</th></tr></thead>
                        <tbody>
                        @foreach($report['top_motors'] as $motor)
                            <tr>
                                <td>{{ $motor['nama_motor'] }}</td>
                                <td>{{ $motor['jumlah_order'] }}</td>
                                <td>Rp {{ number_format($motor['total_nilai'], 0, ',', '.') }}</td>
                                <td><span class="badge badge-purple">{{ $motor['persentase'] }}%</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state"><i class="fas fa-chart-bar"></i><p>Belum ada data</p></div>
            @endif
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-chart-pie"></i> Distribusi Status</h3></div>
        <div class="card-body">
            @if(count($report['status_distribution']) > 0)
                <canvas id="statusChart" height="200"></canvas>
                <div class="table-wrapper" style="margin-top: 16px;">
                    <table>
                        <thead><tr><th>Status</th><th>Jumlah</th><th>%</th></tr></thead>
                        <tbody>
                        @foreach($report['status_distribution'] as $s)
                            <tr>
                                <td><span class="badge badge-info">{{ $s['status'] }}</span></td>
                                <td>{{ $s['jumlah'] }}</td>
                                <td>{{ $s['persentase'] }}%</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state"><i class="fas fa-chart-pie"></i><p>Belum ada data</p></div>
            @endif
        </div>
    </div>
</div>

<!-- Export Buttons -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="fas fa-download"></i> Export:</span>
        <form method="POST" action="{{ route('reports.export') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="type" value="order">
            <input type="hidden" name="format" value="xlsx">
            <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
            <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
            <input type="hidden" name="motor_id" value="{{ $filters['motor_id'] ?? '' }}">
            <input type="hidden" name="tenor_id" value="{{ $filters['tenor_id'] ?? '' }}">
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</button>
        </form>
        <form method="POST" action="{{ route('reports.export') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="type" value="order">
            <input type="hidden" name="format" value="csv">
            <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
            <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
            <input type="hidden" name="motor_id" value="{{ $filters['motor_id'] ?? '' }}">
            <input type="hidden" name="tenor_id" value="{{ $filters['tenor_id'] ?? '' }}">
            <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-file-csv"></i> CSV</button>
        </form>
        <form method="POST" action="{{ route('reports.export') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="type" value="order">
            <input type="hidden" name="format" value="pdf">
            <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
            <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
            <input type="hidden" name="motor_id" value="{{ $filters['motor_id'] ?? '' }}">
            <input type="hidden" name="tenor_id" value="{{ $filters['tenor_id'] ?? '' }}">
            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header"><h3>Detail Order ({{ $report['total'] }} data)</h3></div>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Motor</th><th>Harga Cash</th><th>DP</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($report['data'] as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->tgl_pengajuan_kredit?->format('d/m/Y') }}</td>
                    <td>{{ $p->pelanggan?->nama_pelanggan ?? '-' }}</td>
                    <td>{{ $p->motor?->nama_motor ?? '-' }}</td>
                    <td>Rp {{ number_format($p->harga_cash, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($p->dp, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $badgeClass = match($p->status_pengajuan) {
                                'Disetujui', 'Diterima' => 'badge-success',
                                'Ditolak', 'Dibatalkan Pembeli', 'Dibatalkan Penjual' => 'badge-danger',
                                'Menunggu Konfirmasi' => 'badge-warning',
                                default => 'badge-info',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $p->status_pengajuan }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada data order</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(count($report['top_motors']) > 0)
    new Chart(document.getElementById('topMotorsChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($report['top_motors'], 'nama_motor')) !!},
            datasets: [{
                label: 'Jumlah Order',
                data: {!! json_encode(array_column($report['top_motors'], 'jumlah_order')) !!},
                backgroundColor: ['#6366f1', '#f59e0b', '#10b981'],
                borderRadius: 8,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { color: '#94a3b8' } }, x: { ticks: { color: '#94a3b8' } } } }
    });
    @endif

    @if(count($report['status_distribution']) > 0)
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_column($report['status_distribution'], 'status')) !!},
            datasets: [{
                data: {!! json_encode(array_column($report['status_distribution'], 'jumlah')) !!},
                backgroundColor: ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', padding: 12 } } } }
    });
    @endif
});
</script>
@endpush
