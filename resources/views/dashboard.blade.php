@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
    $currentUser = auth()->user();
    $role = $currentUser->role;
    $firstName = preg_split('/\s+/', trim($currentUser->name))[0] ?? $currentUser->name;
    $roleLabels = [
        'admin' => 'Administrator',
        'marketing' => 'Marketing',
        'surveyor' => 'Surveyor',
        'approver' => 'Approver',
        'customer' => 'Customer',
    ];
    $dashboardIntro = match ($role) {
        'admin' => 'Pantau operasional, data master, pengajuan, dan kredit aktif dari satu tempat.',
        'marketing' => 'Kelola pelanggan, pengajuan baru, dan katalog motor dengan alur kerja yang ringkas.',
        'surveyor' => 'Lihat tugas survey yang perlu ditindaklanjuti dan buka detailnya dengan cepat.',
        'approver' => 'Review pengajuan yang sudah selesai survey sebelum masuk tahap keputusan.',
        'customer' => 'Pantau pengajuan kredit dan tagihan angsuran yang perlu dibayar.',
        default => 'Ringkasan aktivitas akun Anda.',
    };
    $pengajuanSayaCollection = collect($pengajuanSaya ?? []);
    $angsuranSayaCollection = collect($angsuranSaya ?? []);
    $angsuranSayaPreview = $angsuranSayaCollection->take(5);
    $customerProfile = $customerProfile ?? null;
    $pengirimanSayaCollection = collect($pengirimanSaya ?? []);
    $pengirimanSayaPreview = $pengirimanSayaCollection->take(3);
    $pengirimanAktifSaya = $pengirimanSayaCollection->where('status_kirim', 'Sedang Dikirim')->count();
    $customerProfileComplete = filled($customerProfile?->nama_pelanggan)
        && filled($customerProfile?->no_ktp)
        && filled($customerProfile?->no_telp)
        && filled($customerProfile?->alamat);
    $kreditAktifSaya = $pengajuanSayaCollection
        ->filter(fn ($item) => $item->kredit && $item->kredit->status_kredit === 'Dicicil')
        ->count();
    $pengajuanDisetujuiSaya = $pengajuanSayaCollection
        ->filter(fn ($item) => in_array($item->status_pengajuan, ['Disetujui', 'Diterima'], true))
        ->count();
    $userPerformanceRows = collect($userPerformance['data'] ?? []);
    $userPerformanceTopPreview = $userPerformanceRows->sortByDesc('total_respons')->take(5)->values();
    $userPerformanceRoleChart = $userPerformance['charts']['roles'] ?? ($userPerformance['roles'] ?? []);
    $userPerformanceTopChart = $userPerformance['charts']['top_users'] ?? $userPerformanceTopPreview
        ->map(fn ($row) => [
            'nama' => $row['nama'],
            'role' => $row['role'],
            'total_respons' => $row['total_respons'],
        ])
        ->values()
        ->all();
@endphp

@push('styles')
<style>
    .dashboard-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; margin-bottom: 24px; padding-bottom: 18px; border-bottom: 1px solid var(--border); }
    .dashboard-eyebrow { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 8px; color: var(--primary-dark); font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; }
    .dashboard-title { color: var(--text-heading); font-size: 1.45rem; font-weight: 800; letter-spacing: 0; line-height: 1.25; }
    .dashboard-subtitle { max-width: 720px; margin-top: 8px; color: var(--text-muted); font-size: 0.94rem; }
    .dashboard-actions { display: flex; align-items: center; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }
    .dashboard-stat-grid { margin-bottom: 24px; }
    .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(300px, 0.42fr); gap: 20px; align-items: start; }
    .dashboard-grid-balanced { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dashboard-table td strong { color: var(--text-heading); }
    .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; }
    .action-tile { display: flex; align-items: center; gap: 12px; padding: 14px; border: 1px solid var(--border); border-radius: var(--radius); color: var(--text); background: #fff; transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s; }
    .action-tile:hover { color: var(--text); border-color: var(--primary); box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08); transform: translateY(-1px); }
    .action-tile i { width: 38px; height: 38px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: #eff6ff; color: var(--primary); flex: 0 0 auto; }
    .action-tile strong { display: block; color: var(--text-heading); font-size: 0.92rem; }
    .action-tile span { display: block; color: var(--text-muted); font-size: 0.78rem; margin-top: 2px; }
    .priority-list, .payment-list { display: grid; gap: 12px; }
    .priority-item { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .priority-item:last-child { border-bottom: 0; padding-bottom: 0; }
    .priority-item span { color: var(--text-muted); font-size: 0.82rem; }
    .priority-value { color: var(--text-heading); font-size: 1.2rem; font-weight: 800; white-space: nowrap; }
    .payment-item { display: grid; gap: 10px; padding: 14px; border: 1px solid var(--border); border-radius: var(--radius); background: #fff; }
    .payment-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
    .payment-title { color: var(--text-heading); font-weight: 800; }
    .payment-meta { color: var(--text-muted); font-size: 0.8rem; margin-top: 2px; }
    .payment-amount { color: var(--primary-dark); font-size: 1.1rem; font-weight: 800; white-space: nowrap; }
    .profile-summary { display: grid; gap: 12px; }
    .profile-summary-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
    .profile-summary-row:last-child { border-bottom: 0; padding-bottom: 0; }
    .profile-summary-row span { color: var(--text-muted); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; }
    .profile-summary-row strong { color: var(--text-heading); text-align: right; overflow-wrap: anywhere; }
    .shipping-list { display: grid; gap: 12px; }
    .shipping-item { border: 1px solid var(--border); border-radius: var(--radius); padding: 14px; background: #fff; }
    .shipping-map-mini { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 12px; padding-top: 16px; }
    .shipping-map-mini::before { content: ""; position: absolute; left: 16px; right: 16px; top: 23px; height: 3px; background: #dbeafe; border-radius: 999px; }
    .shipping-point { position: relative; z-index: 1; display: grid; gap: 6px; justify-items: center; color: var(--text-muted); font-size: 0.72rem; text-align: center; }
    .shipping-point i { width: 18px; height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #e2e8f0; color: #64748b; font-size: 0.56rem; }
    .shipping-point.done i, .shipping-point.active i { background: var(--primary); color: #fff; }
    .shipping-point.done, .shipping-point.active { color: var(--text-heading); font-weight: 700; }
    .empty-panel { padding: 28px 18px; border: 1px dashed var(--border); border-radius: var(--radius); color: var(--text-muted); text-align: center; background: #f8fafc; }
    .empty-panel i { display: block; margin-bottom: 10px; color: #94a3b8; font-size: 1.4rem; }
    .performance-panel { margin-top: 22px; }
    .performance-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 18px; }
    .performance-box { border: 1px solid var(--border); border-radius: var(--radius); padding: 14px; background: #f8fafc; }
    .performance-box span { display: block; color: var(--text-muted); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; }
    .performance-box strong { display: block; margin-top: 4px; color: var(--text-heading); font-size: 1.25rem; }
    .performance-preview-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(280px, 0.62fr); gap: 20px; align-items: start; }
    .performance-chart-wrap { position: relative; min-height: 240px; }
    .performance-leader-list { display: grid; gap: 10px; }
    .performance-leader { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .performance-leader:last-child { border-bottom: 0; }
    .performance-leader strong { color: var(--text-heading); font-size: 0.9rem; }
    .performance-leader span { display: block; color: var(--text-muted); font-size: 0.78rem; margin-top: 2px; }
    .performance-leader-value { color: var(--primary-dark); font-size: 1.1rem; font-weight: 800; white-space: nowrap; }
    .export-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .export-actions form { margin: 0; }
    @media (max-width: 980px) {
        .dashboard-header { align-items: flex-start; flex-direction: column; }
        .dashboard-actions { justify-content: flex-start; }
        .dashboard-grid, .dashboard-grid-balanced, .performance-preview-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
        .dashboard-title { font-size: 1.22rem; }
        .dashboard-actions .btn { width: 100%; justify-content: center; }
        .payment-head { flex-direction: column; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-header">
    <div>
        <div class="dashboard-eyebrow">
            <i class="fas fa-circle-user"></i>
            {{ $roleLabels[$role] ?? ucfirst($role) }}
        </div>
        <div class="dashboard-title">Selamat datang, {{ $firstName }}</div>
        <p class="dashboard-subtitle">{{ $dashboardIntro }}</p>
    </div>
    <div class="dashboard-actions">
        @if($role === 'admin')
            <a href="{{ route('pengajuan.index') }}" class="btn btn-primary"><i class="fas fa-file-invoice"></i> Kelola Pengajuan</a>
            <a href="{{ route('reports.orders') }}" class="btn btn-outline"><i class="fas fa-chart-bar"></i> Laporan</a>
        @elseif($role === 'marketing')
            <a href="{{ route('pengajuan.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Pengajuan</a>
            <a href="{{ route('pelanggan.create') }}" class="btn btn-outline"><i class="fas fa-user-plus"></i> Tambah Pelanggan</a>
        @elseif($role === 'customer')
            <a href="{{ route('pengajuan.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Ajukan Kredit</a>
            <a href="{{ route('customer.profile.edit') }}" class="btn btn-outline"><i class="fas fa-id-card"></i> Data Pribadi</a>
            <a href="{{ route('angsuran.index') }}" class="btn btn-outline"><i class="fas fa-receipt"></i> Angsuran</a>
        @endif
    </div>
</div>

@if($role === 'admin')
<div class="stat-grid dashboard-stat-grid">
    <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-users"></i></div><div class="stat-info"><h4>{{ $totalUsers }}</h4><p>Total Pengguna</p></div></div>
    <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-motorcycle"></i></div><div class="stat-info"><h4>{{ $totalMotor }}</h4><p>Total Motor</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-user-friends"></i></div><div class="stat-info"><h4>{{ $totalPelanggan }}</h4><p>Total Pelanggan</p></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div><div class="stat-info"><h4>{{ $totalPengajuan }}</h4><p>Total Pengajuan</p></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="fas fa-clock"></i></div><div class="stat-info"><h4>{{ $pengajuanBaru }}</h4><p>Menunggu Konfirmasi</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-hand-holding-usd"></i></div><div class="stat-info"><h4>{{ $kreditAktif }}</h4><p>Kredit Aktif</p></div></div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clock"></i> Pengajuan Terbaru</h3>
            <a href="{{ route('pengajuan.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body table-wrapper">
            <table class="dashboard-table">
                <thead><tr><th>ID</th><th>Pelanggan</th><th>Motor</th><th>Status</th><th>Tanggal</th></tr></thead>
                <tbody>
                    @forelse($recentPengajuan as $p)
                        @php
                            $statusClass = match($p->status_pengajuan) {
                                'Disetujui', 'Diterima' => 'badge-success',
                                'Ditolak', 'Dibatalkan Pembeli', 'Dibatalkan Penjual', 'Bermasalah' => 'badge-danger',
                                'Survey' => 'badge-info',
                                default => 'badge-warning',
                            };
                        @endphp
                        <tr>
                            <td>#{{ $p->id }}</td>
                            <td><strong>{{ $p->pelanggan->nama_pelanggan }}</strong></td>
                            <td>{{ $p->motor->nama_motor }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $p->status_pengajuan }}</span></td>
                            <td>{{ $p->tgl_pengajuan_kredit->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-panel"><i class="fas fa-inbox"></i>Belum ada pengajuan</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-list-check"></i> Prioritas</h3></div>
        <div class="card-body">
            <div class="priority-list">
                <div class="priority-item"><span>Pengajuan perlu konfirmasi</span><div class="priority-value">{{ $pengajuanBaru }}</div></div>
                <div class="priority-item"><span>Kredit masih dicicil</span><div class="priority-value">{{ $kreditAktif }}</div></div>
                <div class="priority-item"><span>Data pelanggan tercatat</span><div class="priority-value">{{ $totalPelanggan }}</div></div>
            </div>
        </div>
    </div>
</div>

<div class="card performance-panel">
    <div class="card-header">
        <h3><i class="fas fa-users-gear"></i> Kinerja User Operasional</h3>
        <a href="{{ route('reports.user-performance') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-up-right-from-square"></i> Lihat Panel Detail</a>
    </div>
    <div class="card-body">
        <div class="performance-summary">
            <div class="performance-box"><span>Total User</span><strong>{{ $userPerformance['summary']['total_user'] ?? 0 }}</strong></div>
            <div class="performance-box"><span>Total Respons</span><strong>{{ $userPerformance['summary']['total_respons'] ?? 0 }}</strong></div>
            <div class="performance-box"><span>Survey Selesai</span><strong>{{ $userPerformance['summary']['total_survey_selesai'] ?? 0 }}</strong></div>
            <div class="performance-box"><span>Total Approval</span><strong>{{ $userPerformance['summary']['total_approval'] ?? 0 }}</strong></div>
        </div>
        <div class="performance-preview-grid">
            <div>
                @if(count($userPerformanceRoleChart) > 0)
                    <div class="performance-chart-wrap">
                        <canvas id="dashboardPerformanceRoleChart"></canvas>
                    </div>
                @else
                    <div class="empty-panel"><i class="fas fa-chart-pie"></i>Belum ada data chart kinerja</div>
                @endif
            </div>
            <div>
                <div class="performance-leader-list">
                    @forelse($userPerformanceTopPreview as $row)
                        <div class="performance-leader">
                            <div>
                                <strong>{{ $row['nama'] }}</strong>
                                <span>{{ ucfirst($row['role']) }} / {{ $row['last_activity'] ? $row['last_activity']->format('d/m/Y H:i') : 'Belum ada aktivitas' }}</span>
                            </div>
                            <div class="performance-leader-value">{{ $row['total_respons'] }}</div>
                        </div>
                    @empty
                        <div class="empty-panel"><i class="fas fa-inbox"></i>Belum ada data kinerja</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($role === 'marketing')
<div class="stat-grid dashboard-stat-grid">
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-users"></i></div><div class="stat-info"><h4>{{ $totalPelanggan }}</h4><p>Total Pelanggan</p></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div><div class="stat-info"><h4>{{ $totalPengajuan }}</h4><p>Total Pengajuan</p></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="fas fa-clock"></i></div><div class="stat-info"><h4>{{ $pengajuanBaru }}</h4><p>Menunggu Konfirmasi</p></div></div>
</div>

<div class="dashboard-grid-balanced">
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-bolt"></i> Aksi Cepat</h3></div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="{{ route('pengajuan.create') }}" class="action-tile"><i class="fas fa-plus"></i><span><strong>Buat Pengajuan</strong><span>Input aplikasi kredit baru</span></span></a>
                <a href="{{ route('pelanggan.create') }}" class="action-tile"><i class="fas fa-user-plus"></i><span><strong>Tambah Pelanggan</strong><span>Simpan profil pelanggan</span></span></a>
                <a href="{{ route('motors.index') }}" class="action-tile"><i class="fas fa-motorcycle"></i><span><strong>Kelola Motor</strong><span>Cek katalog dan harga</span></span></a>
                <a href="{{ route('pengiriman.index') }}" class="action-tile"><i class="fas fa-truck"></i><span><strong>Pengiriman</strong><span>Atur ongkir dan resi</span></span></a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-clock"></i> Pengajuan Terbaru</h3></div>
        <div class="card-body table-wrapper">
            <table class="dashboard-table">
                <thead><tr><th>Pelanggan</th><th>Motor</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($recentPengajuan as $p)
                        @php
                            $statusClass = match($p->status_pengajuan) {
                                'Disetujui', 'Diterima' => 'badge-success',
                                'Ditolak', 'Dibatalkan Pembeli', 'Dibatalkan Penjual', 'Bermasalah' => 'badge-danger',
                                'Survey' => 'badge-info',
                                default => 'badge-warning',
                            };
                        @endphp
                        <tr>
                            <td><strong>{{ $p->pelanggan->nama_pelanggan }}</strong></td>
                            <td>{{ $p->motor->nama_motor }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $p->status_pengajuan }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-panel"><i class="fas fa-inbox"></i>Belum ada pengajuan</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($role === 'surveyor')
<div class="stat-grid dashboard-stat-grid">
    <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-inbox"></i></div><div class="stat-info"><h4>{{ $surveyAvailable }}</h4><p>Siap Diambil</p></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-clipboard-list"></i></div><div class="stat-info"><h4>{{ $surveyAssigned }}</h4><p>Survey Ditugaskan</p></div></div>
</div>
<div class="dashboard-grid-balanced">
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-inbox"></i> Pool Pengajuan Siap Survey</h3></div>
        <div class="card-body table-wrapper">
            <table class="dashboard-table">
                <thead><tr><th>ID</th><th>Pelanggan</th><th>Motor</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($availableSurveyList as $s)
                        <tr>
                            <td>#{{ $s->id }}</td>
                            <td><strong>{{ $s->pelanggan->nama_pelanggan }}</strong></td>
                            <td>{{ $s->motor->nama_motor }}</td>
                            <td>
                                <form action="{{ route('pengajuan.claim', $s) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-hand-pointer"></i> Ambil</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-panel"><i class="fas fa-check-circle"></i>Tidak ada pengajuan baru untuk diambil</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-clipboard-list"></i> Tugas Survey Saya</h3></div>
        <div class="card-body table-wrapper">
            <table class="dashboard-table">
                <thead><tr><th>ID</th><th>Pelanggan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($surveyList as $s)
                        <tr>
                            <td>#{{ $s->id }}</td>
                            <td><strong>{{ $s->pelanggan->nama_pelanggan }}</strong></td>
                            <td><span class="badge badge-warning">{{ $s->status_pengajuan }}</span></td>
                            <td><a href="{{ route('pengajuan.show', $s) }}" class="btn btn-sm btn-primary">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-panel"><i class="fas fa-check-circle"></i>Belum ada tugas survey aktif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($role === 'approver')
<div class="stat-grid dashboard-stat-grid">
    <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-check-double"></i></div><div class="stat-info"><h4>{{ $pendingApproval }}</h4><p>Menunggu Approval</p></div></div>
</div>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-check-double"></i> Menunggu Persetujuan</h3></div>
    <div class="card-body table-wrapper">
        <table class="dashboard-table">
            <thead><tr><th>ID</th><th>Pelanggan</th><th>Motor</th><th>Kredit</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($approvalList as $a)
                    <tr>
                        <td>#{{ $a->id }}</td>
                        <td><strong>{{ $a->pelanggan->nama_pelanggan }}</strong></td>
                        <td>{{ $a->motor->nama_motor }}</td>
                        <td>Rp {{ number_format($a->harga_kredit, 0, ',', '.') }}</td>
                        <td><a href="{{ route('pengajuan.show', $a) }}" class="btn btn-sm btn-primary">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-panel"><i class="fas fa-check-circle"></i>Tidak ada pengajuan menunggu</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@if($role === 'customer')
<div class="stat-grid dashboard-stat-grid">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-file-signature"></i></div><div class="stat-info"><h4>{{ $pengajuanSayaCollection->count() }}</h4><p>Total Pengajuan</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-circle-check"></i></div><div class="stat-info"><h4>{{ $pengajuanDisetujuiSaya }}</h4><p>Disetujui / Diterima</p></div></div>
    <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-receipt"></i></div><div class="stat-info"><h4>{{ $angsuranSayaCollection->count() }}</h4><p>Angsuran Belum Bayar</p></div></div>
    <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-hand-holding-usd"></i></div><div class="stat-info"><h4>{{ $kreditAktifSaya }}</h4><p>Kredit Aktif</p></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-truck-fast"></i></div><div class="stat-info"><h4>{{ $pengirimanAktifSaya }}</h4><p>Pengiriman Aktif</p></div></div>
</div>

<div class="dashboard-grid" style="margin-bottom:22px;">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-id-card"></i> Data Pribadi</h3>
            <a href="{{ route('customer.profile.edit') }}" class="btn btn-sm btn-outline">Edit</a>
        </div>
        <div class="card-body">
            @if($customerProfile)
                <div class="profile-summary">
                    <div class="profile-summary-row"><span>Status</span><strong><span class="badge {{ $customerProfileComplete ? 'badge-success' : 'badge-warning' }}">{{ $customerProfileComplete ? 'Lengkap' : 'Perlu dilengkapi' }}</span></strong></div>
                    <div class="profile-summary-row"><span>Nama</span><strong>{{ $customerProfile->nama_pelanggan }}</strong></div>
                    <div class="profile-summary-row"><span>KTP</span><strong>{{ $customerProfile->no_ktp }}</strong></div>
                    <div class="profile-summary-row"><span>Telepon</span><strong>{{ $customerProfile->no_telp }}</strong></div>
                    <div class="profile-summary-row"><span>Alamat</span><strong>{{ $customerProfile->alamat ?: '-' }}</strong></div>
                </div>
            @else
                <div class="empty-panel"><i class="fas fa-id-card"></i>Lengkapi data pribadi sebelum membuat pengajuan kredit.</div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-truck-fast"></i> Status Pengiriman</h3>
        </div>
        <div class="card-body">
            <div class="shipping-list">
                @forelse($pengirimanSayaPreview as $ship)
                    @php
                        $shipVerificationStatus = $ship->delivery_verification_status ?: \App\Models\Pengiriman::VERIFICATION_UNCONFIRMED;
                        $shipStep = match (true) {
                            $ship->status_kirim === 'Tiba Di Tujuan' => 3,
                            $shipVerificationStatus === \App\Models\Pengiriman::VERIFICATION_PENDING => 2,
                            $ship->tgl_kirim !== null => 1,
                            default => 0,
                        };
                    @endphp
                    <div class="shipping-item">
                        <div class="payment-head">
                            <div>
                                <div class="payment-title">{{ $ship->pengajuanKredit?->motor?->nama_motor ?? 'Motor' }}</div>
                                <div class="payment-meta">{{ $ship->no_invoice ?? '-' }} / {{ $ship->shipping_etd ?: 'Estimasi belum tersedia' }}</div>
                            </div>
                            <span class="badge {{ $ship->delivery_verification_badge_class }}">{{ $shipVerificationStatus }}</span>
                        </div>
                        <div class="shipping-map-mini">
                            @foreach(['Disiapkan', 'Dikirim', 'Bukti', 'Selesai'] as $index => $label)
                                <div class="shipping-point {{ $index < $shipStep ? 'done' : ($index === $shipStep ? 'active' : '') }}">
                                    <i class="fas {{ $index <= $shipStep ? 'fa-check' : 'fa-circle' }}"></i>
                                    <span>{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="payment-meta" style="margin-top:10px;">Tujuan: {{ $ship->destination_label ?: $ship->receiver_address ?: '-' }}</div>
                        @if($ship->can_customer_confirm_received)
                            <a href="{{ route('pengajuan.show', $ship->pengajuanKredit) }}#status-pengiriman" class="btn btn-sm btn-primary" style="margin-top:10px;"><i class="fas fa-camera"></i> Kirim Bukti Terima</a>
                        @endif
                    </div>
                @empty
                    <div class="empty-panel"><i class="fas fa-truck"></i>Belum ada pengiriman untuk ditampilkan</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-file-invoice"></i> Pengajuan Kredit Saya</h3>
            <a href="{{ route('pengajuan.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Ajukan Baru</a>
        </div>
        <div class="card-body table-wrapper">
            <table class="dashboard-table">
                <thead><tr><th>Motor</th><th>Total Kredit</th><th>Cicilan/bln</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($pengajuanSayaCollection as $p)
                        @php
                            $statusClass = match($p->status_pengajuan) {
                                'Disetujui', 'Diterima' => 'badge-success',
                                'Ditolak', 'Dibatalkan Pembeli', 'Dibatalkan Penjual', 'Bermasalah' => 'badge-danger',
                                'Survey' => 'badge-info',
                                default => 'badge-warning',
                            };
                        @endphp
                        <tr>
                            <td><strong>{{ $p->motor->nama_motor }}</strong></td>
                            <td>Rp {{ number_format($p->harga_kredit, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($p->cicilan_perbulan, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $p->status_pengajuan }}</span></td>
                            <td>
                                @if($p->status_pengajuan === 'Disetujui' && ! $p->is_down_payment_paid)
                                    <a href="{{ route('pengajuan.show', $p) }}#dp-ongkir" class="btn btn-sm btn-primary"><i class="fas fa-credit-card"></i> Bayar DP</a>
                                @else
                                    <a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-outline">Detail</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-panel"><i class="fas fa-file-circle-plus"></i>Belum ada pengajuan kredit</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-receipt"></i> Tagihan Angsuran</h3>
            <a href="{{ route('angsuran.index') }}" class="btn btn-sm btn-outline">Semua</a>
        </div>
        <div class="card-body">
            <div class="payment-list">
                @forelse($angsuranSayaPreview as $a)
                    <div class="payment-item">
                        <div class="payment-head">
                            <div>
                                <div class="payment-title">Angsuran ke-{{ $a->angsuran_ke }}</div>
                                <div class="payment-meta">{{ $a->kredit?->pengajuanKredit?->motor?->nama_motor ?? 'Kredit motor' }}</div>
                            </div>
                            <div class="payment-amount">Rp {{ number_format($a->total_bayar, 0, ',', '.') }}</div>
                        </div>
                        <div class="payment-meta">Jatuh tempo: {{ optional($a->tgl_jatuh_tempo)->format('d/m/Y') ?? '-' }}</div>
                        @if($a->is_customer_payable)
                            <a href="{{ route('payment.pay', $a) }}" class="btn btn-sm btn-primary"><i class="fas fa-credit-card"></i> Bayar Sekarang</a>
                        @else
                            <button type="button" class="btn btn-sm btn-outline" disabled title="{{ $a->customer_payment_lock_reason }}">
                                <i class="fas fa-lock"></i> Belum Bisa Dibayar
                            </button>
                            <div class="payment-meta">{{ $a->customer_payment_lock_reason }}</div>
                        @endif
                    </div>
                @empty
                    <div class="empty-panel"><i class="fas fa-circle-check"></i>Tidak ada angsuran yang perlu dibayar</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@if($role === 'admin' && count($userPerformanceRoleChart) > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleChart = document.getElementById('dashboardPerformanceRoleChart');
    if (!roleChart) return;

    new Chart(roleChart, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(collect($userPerformanceRoleChart)->pluck('role')->map(fn ($role) => ucfirst($role))->values()) !!},
            datasets: [{
                data: {!! json_encode(collect($userPerformanceRoleChart)->pluck('total_respons')->values()) !!},
                backgroundColor: ['#2563eb', '#059669', '#d97706'],
                borderColor: '#ffffff',
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#64748b', padding: 14 }
                }
            },
            cutout: '64%'
        }
    });
});
</script>
@endpush
@endif
