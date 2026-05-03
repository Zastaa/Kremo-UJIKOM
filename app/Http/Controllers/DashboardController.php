<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\Kredit;
use App\Models\Motor;
use App\Models\Pelanggan;
use App\Models\Pengiriman;
use App\Models\PengajuanKredit;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $data = ['user' => $user];

        switch ($user->role) {
            case 'admin':
                $data['totalUsers'] = User::count();
                $data['totalMotor'] = Motor::count();
                $data['totalPelanggan'] = Pelanggan::count();
                $data['totalPengajuan'] = PengajuanKredit::count();
                $data['pengajuanBaru'] = PengajuanKredit::where('status_pengajuan', 'Menunggu Konfirmasi')->count();
                $data['kreditAktif'] = Kredit::where('status_kredit', 'Dicicil')->count();
                $data['recentPengajuan'] = PengajuanKredit::with(['pelanggan', 'motor'])->latest()->take(5)->get();
                $data['userPerformance'] = $this->reportService->getUserPerformanceReport([]);
                break;

            case 'marketing':
                $data['totalPelanggan'] = Pelanggan::count();
                $data['totalPengajuan'] = PengajuanKredit::count();
                $data['pengajuanBaru'] = PengajuanKredit::where('status_pengajuan', 'Menunggu Konfirmasi')->count();
                $data['recentPengajuan'] = PengajuanKredit::with(['pelanggan', 'motor'])->latest()->take(5)->get();
                break;

            case 'surveyor':
                $data['surveyAvailable'] = PengajuanKredit::whereNull('surveyor_id')
                    ->where('status_pengajuan', 'Menunggu Konfirmasi')
                    ->count();
                $data['surveyAssigned'] = PengajuanKredit::where('surveyor_id', $user->id)
                    ->whereIn('status_pengajuan', ['Diproses', 'Survey'])->count();
                $data['availableSurveyList'] = PengajuanKredit::with(['pelanggan', 'motor'])
                    ->whereNull('surveyor_id')
                    ->where('status_pengajuan', 'Menunggu Konfirmasi')
                    ->latest()
                    ->get();
                $data['surveyList'] = PengajuanKredit::with(['pelanggan', 'motor'])
                    ->where('surveyor_id', $user->id)
                    ->whereIn('status_pengajuan', ['Diproses', 'Survey'])
                    ->latest()->get();
                break;

            case 'approver':
                $data['pendingApproval'] = PengajuanKredit::where('status_pengajuan', 'Survey')->count();
                $data['approvalList'] = PengajuanKredit::with(['pelanggan', 'motor'])
                    ->where('status_pengajuan', 'Survey')
                    ->latest()->get();
                break;

            case 'customer':
                $data['customerProfile'] = Pelanggan::where('email', $user->email)->first();
                $data['pengajuanSaya'] = PengajuanKredit::with(['motor', 'kredit'])
                    ->whereHas('pelanggan', function ($q) use ($user) {
                        $q->where('email', $user->email);
                    })->latest()->get();
                $data['angsuranSaya'] = Angsuran::with('kredit.pengajuanKredit.motor')
                    ->whereHas('kredit.pengajuanKredit.pelanggan', function ($q) use ($user) {
                        $q->where('email', $user->email);
                    })
                    ->where('status', 'Belum Bayar')
                    ->orderBy('tgl_jatuh_tempo')
                    ->get();
                $data['pengirimanSaya'] = Pengiriman::with(['pengajuanKredit.motor', 'pengajuanKredit.pelanggan'])
                    ->whereHas('pengajuanKredit.pelanggan', function ($q) use ($user) {
                        $q->where('email', $user->email);
                    })
                    ->latest()
                    ->get();
                break;
        }

        return view('dashboard', $data);
    }
}
