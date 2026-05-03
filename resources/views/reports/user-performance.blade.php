@extends('layouts.app')
@section('title', 'Kinerja User Operasional')
@section('page-title', 'Kinerja User Operasional')

@php
    $rows = collect($report['data'] ?? []);
    $roleChart = collect($report['charts']['roles'] ?? []);
    $topUsersChart = collect($report['charts']['top_users'] ?? []);
    $activityChart = collect($report['charts']['activities'] ?? []);
    $roles = [
        'marketing' => 'Marketing',
        'surveyor' => 'Surveyor',
        'approver' => 'Approver',
    ];
    $roleBadges = [
        'marketing' => 'badge-info',
        'surveyor' => 'badge-success',
        'approver' => 'badge-warning',
    ];
@endphp

@push('styles')
<style>
    .report-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; margin-bottom: 24px; padding-bottom: 18px; border-bottom: 1px solid var(--border); }
    .report-title { color: var(--text-heading); font-size: 1.35rem; font-weight: 800; line-height: 1.25; }
    .report-subtitle { margin-top: 8px; color: var(--text-muted); max-width: 740px; font-size: 0.92rem; }
    .report-filter-form { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
    .report-filter-form .form-group { margin-bottom: 0; min-width: 170px; }
    .export-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .export-actions form { margin: 0; }
    .report-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr); gap: 20px; margin-bottom: 24px; align-items: stretch; }
    .report-grid-three { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin-bottom: 24px; }
    .chart-wrap { position: relative; min-height: 280px; }
    .compact-list { display: grid; gap: 10px; }
    .compact-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .compact-row:last-child { border-bottom: 0; }
    .compact-row strong { display: block; color: var(--text-heading); font-size: 0.9rem; }
    .compact-row span { display: block; color: var(--text-muted); font-size: 0.78rem; margin-top: 2px; }
    .compact-value { color: var(--primary-dark); font-size: 1.08rem; font-weight: 800; white-space: nowrap; }
    .metric-note { color: var(--text-muted); font-size: 0.78rem; margin-top: 2px; }
    .performance-user-cell strong { color: var(--text-heading); }
    .performance-user-cell span { color: var(--text-muted); display: block; font-size: 0.78rem; margin-top: 2px; }

    @media (max-width: 1100px) {
        .report-grid, .report-grid-three { grid-template-columns: 1fr; }
        .report-header { align-items: flex-start; flex-direction: column; }
    }

    @media (max-width: 560px) {
        .report-filter-form .form-group, .report-filter-form .btn { width: 100%; }
        .export-actions .btn { width: 100%; justify-content: center; }
    }
</style>
@endpush

@section('content')
<div class="report-header">
    <div>
        <div class="report-title">Panel Kinerja User Operasional</div>
        <p class="report-subtitle">Pantau respons marketing, surveyor, dan approver dari jumlah pekerjaan yang dibuat, diambil, diselesaikan, serta aktivitas terakhirnya.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Dashboard</a>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.user-performance') }}" class="report-filter-form">
            <div class="form-group">
                <label>Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="">Semua Role</option>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ ($filters['role'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('reports.user-performance') }}" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</a>
        </form>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users-gear"></i></div>
        <div class="stat-info"><h4>{{ number_format($report['summary']['total_user'] ?? 0) }}</h4><p>Total User</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-reply-all"></i></div>
        <div class="stat-info"><h4>{{ number_format($report['summary']['total_respons'] ?? 0) }}</h4><p>Total Respons</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-clipboard-check"></i></div>
        <div class="stat-info"><h4>{{ number_format($report['summary']['total_survey_selesai'] ?? 0) }}</h4><p>Survey Selesai</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-check-double"></i></div>
        <div class="stat-info"><h4>{{ number_format($report['summary']['total_approval'] ?? 0) }}</h4><p>Approval Ditangani</p></div>
    </div>
</div>

<div class="report-grid">
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-chart-column"></i> Respons Per Role</h3></div>
        <div class="card-body">
            @if($roleChart->isNotEmpty())
                <div class="chart-wrap"><canvas id="rolePerformanceChart"></canvas></div>
            @else
                <div class="empty-state"><i class="fas fa-chart-column"></i><p>Belum ada data role</p></div>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-ranking-star"></i> Top Respons</h3></div>
        <div class="card-body">
            @if($topUsersChart->isNotEmpty())
                <div class="chart-wrap"><canvas id="topUserChart"></canvas></div>
            @else
                <div class="empty-state"><i class="fas fa-ranking-star"></i><p>Belum ada data user</p></div>
            @endif
        </div>
    </div>
</div>

<div class="report-grid">
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-chart-pie"></i> Komposisi Aktivitas</h3></div>
        <div class="card-body">
            @if($activityChart->sum('jumlah') > 0)
                <div class="chart-wrap"><canvas id="activityChart"></canvas></div>
            @else
                <div class="empty-state"><i class="fas fa-chart-pie"></i><p>Belum ada aktivitas tercatat</p></div>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-list-check"></i> Ringkasan Operasional</h3></div>
        <div class="card-body">
            <div class="compact-list">
                <div class="compact-row">
                    <div><strong>Pengajuan dibuat</strong><span>Input aplikasi kredit oleh marketing/customer</span></div>
                    <div class="compact-value">{{ number_format($report['summary']['total_pengajuan_dibuat'] ?? 0) }}</div>
                </div>
                <div class="compact-row">
                    <div><strong>Pelanggan dibuat</strong><span>Data pelanggan yang ditambahkan user operasional</span></div>
                    <div class="compact-value">{{ number_format($report['summary']['total_pelanggan_dibuat'] ?? 0) }}</div>
                </div>
                <div class="compact-row">
                    <div><strong>Survey diambil</strong><span>Pengajuan yang diklaim surveyor</span></div>
                    <div class="compact-value">{{ number_format($report['summary']['total_survey_diambil'] ?? 0) }}</div>
                </div>
                <div class="compact-row">
                    <div><strong>Approval selesai</strong><span>{{ number_format($report['summary']['total_approval_disetujui'] ?? 0) }} setuju · {{ number_format($report['summary']['total_approval_ditolak'] ?? 0) }} tolak</span></div>
                    <div class="compact-value">{{ number_format($report['summary']['total_approval'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-body export-actions">
        <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="fas fa-download"></i> Export:</span>
        @foreach(['xlsx' => ['Excel', 'btn-success', 'fa-file-excel'], 'csv' => ['CSV', 'btn-outline', 'fa-file-csv'], 'pdf' => ['PDF', 'btn-danger', 'fa-file-pdf']] as $format => [$label, $class, $icon])
            <form method="POST" action="{{ route('reports.export') }}">
                @csrf
                <input type="hidden" name="type" value="kinerja_user">
                <input type="hidden" name="format" value="{{ $format }}">
                <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                <input type="hidden" name="role" value="{{ $filters['role'] ?? '' }}">
                <button type="submit" class="btn {{ $class }} btn-sm"><i class="fas {{ $icon }}"></i> {{ $label }}</button>
            </form>
        @endforeach
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-table"></i> Detail User ({{ number_format($report['total'] ?? 0) }})</h3>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Total Respons</th>
                    <th>Pengajuan</th>
                    <th>Pelanggan</th>
                    <th>Survey</th>
                    <th>Approval</th>
                    <th>Aktivitas Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="performance-user-cell"><strong>{{ $row['nama'] }}</strong><span>{{ $row['email'] }}</span></td>
                        <td><span class="badge {{ $roleBadges[$row['role']] ?? 'badge-info' }}">{{ $roles[$row['role']] ?? ucfirst($row['role']) }}</span></td>
                        <td><strong>{{ number_format($row['total_respons']) }}</strong></td>
                        <td>{{ number_format($row['pengajuan_dibuat']) }}</td>
                        <td>{{ number_format($row['pelanggan_dibuat']) }}</td>
                        <td>
                            {{ number_format($row['survey_selesai']) }} selesai
                            <div class="metric-note">{{ number_format($row['survey_aktif']) }} aktif dari {{ number_format($row['survey_diambil']) }} diambil</div>
                        </td>
                        <td>
                            {{ number_format($row['approval_ditangani']) }} ditangani
                            <div class="metric-note">{{ number_format($row['approval_disetujui']) }} setuju · {{ number_format($row['approval_ditolak']) }} tolak</div>
                        </td>
                        <td>{{ $row['last_activity'] ? $row['last_activity']->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada data kinerja user</p></div></td></tr>
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
    const chartColors = ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0891b2'];

    @if($roleChart->isNotEmpty())
    new Chart(document.getElementById('rolePerformanceChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($roleChart->pluck('role')->map(fn ($role) => $roles[$role] ?? ucfirst($role))->values()) !!},
            datasets: [{
                label: 'Total Respons',
                data: {!! json_encode($roleChart->pluck('total_respons')->values()) !!},
                backgroundColor: chartColors,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { color: '#64748b', precision: 0 } },
                x: { ticks: { color: '#64748b' } }
            }
        }
    });
    @endif

    @if($topUsersChart->isNotEmpty())
    new Chart(document.getElementById('topUserChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($topUsersChart->pluck('nama')->values()) !!},
            datasets: [{
                label: 'Respons',
                data: {!! json_encode($topUsersChart->pluck('total_respons')->values()) !!},
                backgroundColor: '#2563eb',
                borderRadius: 8,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { color: '#64748b', precision: 0 } },
                y: { ticks: { color: '#64748b' } }
            }
        }
    });
    @endif

    @if($activityChart->sum('jumlah') > 0)
    new Chart(document.getElementById('activityChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($activityChart->pluck('label')->values()) !!},
            datasets: [{
                data: {!! json_encode($activityChart->pluck('jumlah')->values()) !!},
                backgroundColor: chartColors,
                borderColor: '#ffffff',
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#64748b', padding: 14 } }
            },
            cutout: '62%'
        }
    });
    @endif
});
</script>
@endpush
