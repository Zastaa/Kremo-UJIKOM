<?php

namespace App\Services;

use App\Models\Angsuran;
use App\Models\Kredit;
use App\Models\Pelanggan;
use App\Models\PengajuanKredit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Apply date range filter to a query.
     */
    public function applyDateFilter($query, ?string $startDate, ?string $endDate, string $dateColumn = 'created_at')
    {
        if ($startDate) {
            $query->whereDate($dateColumn, '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate($dateColumn, '<=', $endDate);
        }
        return $query;
    }

    // ==========================================
    // A. LAPORAN ORDER (Pengajuan Kredit)
    // ==========================================

    /**
     * Get order report data.
     */
    public function getOrderReport(array $filters): array
    {
        $query = PengajuanKredit::with(['pelanggan', 'motor', 'jenisCicilan']);

        // Date filter
        $this->applyDateFilter($query, $filters['start_date'] ?? null, $filters['end_date'] ?? null, 'tgl_pengajuan_kredit');

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status_pengajuan', $filters['status']);
        }

        // Motor filter
        if (!empty($filters['motor_id'])) {
            $query->where('id_motor', $filters['motor_id']);
        }

        // Tenor filter
        if (!empty($filters['tenor_id'])) {
            $query->where('id_jenis_cicilan', $filters['tenor_id']);
        }

        $pengajuan = $query->latest('tgl_pengajuan_kredit')->get();

        return [
            'top_motors' => $this->getTopMotors($pengajuan),
            'status_distribution' => $this->getStatusDistribution($pengajuan),
            'transaction_stats' => $this->getTransactionStats($pengajuan),
            'data' => $pengajuan,
            'total' => $pengajuan->count(),
        ];
    }

    /**
     * Top 3 motors by order count.
     */
    private function getTopMotors($pengajuan): array
    {
        $totalOrders = $pengajuan->count();
        if ($totalOrders === 0) return [];

        return $pengajuan->groupBy('id_motor')
            ->map(function ($group) use ($totalOrders) {
                $motor = $group->first()->motor;
                return [
                    'nama_motor' => $motor?->nama_motor ?? 'Tidak Diketahui',
                    'jumlah_order' => $group->count(),
                    'total_nilai' => $group->sum('harga_cash'),
                    'persentase' => round(($group->count() / $totalOrders) * 100, 1),
                ];
            })
            ->sortByDesc('jumlah_order')
            ->take(3)
            ->values()
            ->toArray();
    }

    /**
     * Distribution of order statuses.
     */
    private function getStatusDistribution($pengajuan): array
    {
        $total = $pengajuan->count();
        if ($total === 0) return [];

        return $pengajuan->groupBy('status_pengajuan')
            ->map(function ($group) use ($total) {
                return [
                    'status' => $group->first()->status_pengajuan,
                    'jumlah' => $group->count(),
                    'persentase' => round(($group->count() / $total) * 100, 1),
                ];
            })
            ->sortByDesc('jumlah')
            ->values()
            ->toArray();
    }

    /**
     * Transaction statistics (avg, min, max, total).
     */
    private function getTransactionStats($pengajuan): array
    {
        if ($pengajuan->isEmpty()) {
            return ['total_order' => 0, 'total_transaksi' => 0, 'rata_rata' => 0, 'tertinggi' => 0, 'terendah' => 0];
        }

        return [
            'total_order' => $pengajuan->count(),
            'total_transaksi' => $pengajuan->sum('harga_cash'),
            'rata_rata' => round($pengajuan->avg('harga_cash')),
            'tertinggi' => $pengajuan->max('harga_cash'),
            'terendah' => $pengajuan->min('harga_cash'),
        ];
    }

    // ==========================================
    // B. LAPORAN KREDIT
    // ==========================================

    public function getCreditReport(array $filters): array
    {
        $kreditQuery = Kredit::with(['pengajuanKredit.pelanggan', 'pengajuanKredit.motor', 'pengajuanKredit.jenisCicilan', 'angsuran']);

        $this->applyDateFilter($kreditQuery, $filters['start_date'] ?? null, $filters['end_date'] ?? null, 'tgl_mulai_kredit');

        if (!empty($filters['status'])) {
            $kreditQuery->where('status_kredit', $filters['status']);
        }
        if (!empty($filters['tenor_id'])) {
            $kreditQuery->whereHas('pengajuanKredit', fn($q) => $q->where('id_jenis_cicilan', $filters['tenor_id']));
        }

        $kredits = $kreditQuery->latest('tgl_mulai_kredit')->get();

        return [
            'payment_ratio' => $this->getPaymentRatio($kredits),
            'tenor_distribution' => $this->getTenorDistribution($kredits),
            'data' => $kredits,
            'total' => $kredits->count(),
        ];
    }

    /**
     * Payment ratio: Lancar, Terlambat, Macet, Lunas.
     */
    private function getPaymentRatio($kredits): array
    {
        $total = $kredits->count();
        if ($total === 0) return [];

        $categories = [
            'Lunas' => $kredits->where('status_kredit', 'Lunas')->count(),
            'Lancar (Dicicil)' => $kredits->where('status_kredit', 'Dicicil')->count(),
            'Macet' => $kredits->where('status_kredit', 'Macet')->count(),
        ];

        return collect($categories)->map(function ($count, $label) use ($total) {
            return [
                'kategori' => $label,
                'jumlah' => $count,
                'persentase' => round(($count / $total) * 100, 1),
            ];
        })->values()->toArray();
    }

    /**
     * Tenor distribution.
     */
    private function getTenorDistribution($kredits): array
    {
        $total = $kredits->count();
        if ($total === 0) return [];

        return $kredits->groupBy(function ($kredit) {
            return $kredit->pengajuanKredit?->jenisCicilan?->lama_cicilan ?? 0;
        })->map(function ($group, $tenor) use ($total) {
            return [
                'tenor' => $tenor . ' Bulan',
                'lama_cicilan' => $tenor,
                'jumlah' => $group->count(),
                'total_kredit' => $group->sum(fn($k) => $k->pengajuanKredit?->harga_kredit ?? 0),
                'persentase' => round(($group->count() / $total) * 100, 1),
            ];
        })->sortByDesc('jumlah')->values()->toArray();
    }

    // ==========================================
    // C. LAPORAN PEMBAYARAN (Angsuran)
    // ==========================================

    public function getPaymentReport(array $filters): array
    {
        $query = Angsuran::with(['kredit.pengajuanKredit.pelanggan', 'kredit.pengajuanKredit.motor'])
            ->where('status', 'Lunas');

        $this->applyDateFilter($query, $filters['start_date'] ?? null, $filters['end_date'] ?? null, 'tgl_bayar');

        $angsuran = $query->latest('tgl_bayar')->get();

        return [
            'payment_methods' => $this->getPaymentMethods($angsuran),
            'revenue_trend' => $this->getRevenueTrend($angsuran, $filters),
            'summary' => [
                'total_transaksi' => $angsuran->count(),
                'total_pendapatan' => $angsuran->sum('total_bayar'),
                'rata_rata' => $angsuran->count() > 0 ? round($angsuran->avg('total_bayar')) : 0,
            ],
            'data' => $angsuran,
            'total' => $angsuran->count(),
        ];
    }

    /**
     * Payment method distribution (Manual vs Midtrans).
     */
    private function getPaymentMethods($angsuran): array
    {
        $total = $angsuran->count();
        if ($total === 0) return [];

        $methods = [
            'Midtrans' => $angsuran->whereNotNull('payment_type')->where('payment_type', '!=', '')->count(),
            'Manual' => $angsuran->filter(fn($a) => empty($a->payment_type))->count(),
        ];

        return collect($methods)->map(function ($count, $method) use ($total, $angsuran) {
            $subset = $method === 'Midtrans'
                ? $angsuran->whereNotNull('payment_type')->where('payment_type', '!=', '')
                : $angsuran->filter(fn($a) => empty($a->payment_type));

            return [
                'metode' => $method,
                'jumlah' => $count,
                'total_nominal' => $subset->sum('total_bayar'),
                'persentase' => round(($count / $total) * 100, 1),
            ];
        })->values()->toArray();
    }

    /**
     * Revenue trend — daily for short range, weekly for long range.
     */
    private function getRevenueTrend($angsuran, array $filters): array
    {
        if ($angsuran->isEmpty()) return [];

        $startDate = Carbon::parse($filters['start_date'] ?? $angsuran->min('tgl_bayar'));
        $endDate = Carbon::parse($filters['end_date'] ?? $angsuran->max('tgl_bayar'));
        $diffDays = $startDate->diffInDays($endDate);

        // Daily for <=31 days, weekly otherwise
        $groupFormat = $diffDays <= 31 ? 'Y-m-d' : 'Y-\WW';
        $labelKey = $diffDays <= 31 ? 'tanggal' : 'minggu';

        return $angsuran->groupBy(fn($a) => Carbon::parse($a->tgl_bayar)->format($groupFormat))
            ->map(function ($group, $period) use ($labelKey) {
                return [
                    $labelKey => $period,
                    'jumlah_transaksi' => $group->count(),
                    'total_pendapatan' => $group->sum('total_bayar'),
                    'rata_rata' => round($group->avg('total_bayar')),
                ];
            })
            ->sortKeys()
            ->values()
            ->toArray();
    }

    // ==========================================
    // D. LAPORAN KINERJA USER OPERASIONAL
    // ==========================================

    public function getUserPerformanceReport(array $filters): array
    {
        $roles = ['marketing', 'surveyor', 'approver'];
        $usersQuery = User::whereIn('role', $roles);

        if (!empty($filters['role']) && in_array($filters['role'], $roles, true)) {
            $usersQuery->where('role', $filters['role']);
        }

        $users = $usersQuery
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $rows = $users->map(function (User $user) use ($filters) {
            $pengajuanCreatedQuery = PengajuanKredit::where('created_by', $user->id);
            $pelangganCreatedQuery = Pelanggan::where('created_by', $user->id);
            $surveyQuery = PengajuanKredit::where('surveyor_id', $user->id);
            $approvalQuery = PengajuanKredit::where('approver_id', $user->id);

            $this->applyDateFilter($pengajuanCreatedQuery, $filters['start_date'] ?? null, $filters['end_date'] ?? null, 'tgl_pengajuan_kredit');
            $this->applyDateFilter($pelangganCreatedQuery, $filters['start_date'] ?? null, $filters['end_date'] ?? null, 'created_at');
            $this->applyDateFilter($surveyQuery, $filters['start_date'] ?? null, $filters['end_date'] ?? null, 'updated_at');
            $this->applyDateFilter($approvalQuery, $filters['start_date'] ?? null, $filters['end_date'] ?? null, 'updated_at');

            $pengajuanDibuat = (clone $pengajuanCreatedQuery)->count();
            $pelangganDibuat = (clone $pelangganCreatedQuery)->count();
            $surveyDiambil = (clone $surveyQuery)->count();
            $surveySelesai = (clone $surveyQuery)
                ->whereIn('status_pengajuan', ['Survey', 'Disetujui', 'Diterima', 'Ditolak'])
                ->count();
            $surveyAktif = (clone $surveyQuery)->where('status_pengajuan', 'Diproses')->count();
            $approvalDitangani = (clone $approvalQuery)->count();
            $approvalDisetujui = (clone $approvalQuery)->whereIn('status_pengajuan', ['Disetujui', 'Diterima'])->count();
            $approvalDitolak = (clone $approvalQuery)->where('status_pengajuan', 'Ditolak')->count();

            $totalRespons = match ($user->role) {
                'marketing' => $pengajuanDibuat + $pelangganDibuat,
                'surveyor' => $surveyDiambil,
                'approver' => $approvalDitangani,
                default => $pengajuanDibuat + $pelangganDibuat + $surveyDiambil + $approvalDitangani,
            };

            $lastActivity = collect([
                (clone $pengajuanCreatedQuery)->max('created_at'),
                (clone $pelangganCreatedQuery)->max('created_at'),
                (clone $surveyQuery)->max('updated_at'),
                (clone $approvalQuery)->max('updated_at'),
            ])->filter()->max();

            return [
                'user_id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'pengajuan_dibuat' => $pengajuanDibuat,
                'pelanggan_dibuat' => $pelangganDibuat,
                'survey_diambil' => $surveyDiambil,
                'survey_selesai' => $surveySelesai,
                'survey_aktif' => $surveyAktif,
                'approval_ditangani' => $approvalDitangani,
                'approval_disetujui' => $approvalDisetujui,
                'approval_ditolak' => $approvalDitolak,
                'total_respons' => $totalRespons,
                'last_activity' => $lastActivity ? Carbon::parse($lastActivity) : null,
            ];
        })->values();

        $roleStats = $rows->groupBy('role')->map(fn ($items, $role) => [
            'role' => $role,
            'jumlah_user' => $items->count(),
            'total_respons' => $items->sum('total_respons'),
        ])->values()->all();

        $topUsers = $rows
            ->sortByDesc('total_respons')
            ->take(6)
            ->map(fn ($row) => [
                'nama' => $row['nama'],
                'role' => $row['role'],
                'total_respons' => $row['total_respons'],
            ])
            ->values()
            ->all();

        $activityStats = [
            ['label' => 'Pengajuan', 'jumlah' => $rows->sum('pengajuan_dibuat')],
            ['label' => 'Pelanggan', 'jumlah' => $rows->sum('pelanggan_dibuat')],
            ['label' => 'Survey', 'jumlah' => $rows->sum('survey_diambil')],
            ['label' => 'Approval', 'jumlah' => $rows->sum('approval_ditangani')],
        ];

        return [
            'summary' => [
                'total_user' => $rows->count(),
                'total_respons' => $rows->sum('total_respons'),
                'total_pengajuan_dibuat' => $rows->sum('pengajuan_dibuat'),
                'total_pelanggan_dibuat' => $rows->sum('pelanggan_dibuat'),
                'total_survey_diambil' => $rows->sum('survey_diambil'),
                'total_survey_selesai' => $rows->sum('survey_selesai'),
                'total_survey_aktif' => $rows->sum('survey_aktif'),
                'total_approval' => $rows->sum('approval_ditangani'),
                'total_approval_disetujui' => $rows->sum('approval_disetujui'),
                'total_approval_ditolak' => $rows->sum('approval_ditolak'),
            ],
            'roles' => $roleStats,
            'charts' => [
                'roles' => $roleStats,
                'top_users' => $topUsers,
                'activities' => $activityStats,
            ],
            'data' => $rows,
            'total' => $rows->count(),
        ];
    }
}
