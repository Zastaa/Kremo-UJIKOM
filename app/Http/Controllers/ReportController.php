<?php

namespace App\Http\Controllers;

use App\Models\JenisCicilan;
use App\Models\Motor;
use App\Models\ReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    /**
     * Laporan Order (Pengajuan Kredit).
     */
    public function orderReport(Request $request)
    {
        $filters = $this->getFilters($request);
        $report = $this->reportService->getOrderReport($filters);
        $motors = Motor::all();
        $tenors = JenisCicilan::all();

        return view('reports.orders', compact('report', 'filters', 'motors', 'tenors'));
    }

    /**
     * Laporan Kredit.
     */
    public function creditReport(Request $request)
    {
        $filters = $this->getFilters($request);
        $report = $this->reportService->getCreditReport($filters);
        $tenors = JenisCicilan::all();

        return view('reports.credits', compact('report', 'filters', 'tenors'));
    }

    /**
     * Laporan Pembayaran.
     */
    public function paymentReport(Request $request)
    {
        $filters = $this->getFilters($request);
        $report = $this->reportService->getPaymentReport($filters);

        return view('reports.payments', compact('report', 'filters'));
    }

    /**
     * Laporan kinerja user operasional.
     */
    public function userPerformanceReport(Request $request)
    {
        $filters = $this->getFilters($request);
        $report = $this->reportService->getUserPerformanceReport($filters);

        return view('reports.user-performance', compact('report', 'filters'));
    }

    /**
     * Export report (XLSX/CSV/PDF).
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:order,kredit,pembayaran,kinerja_user',
            'format' => 'required|in:xlsx,csv,pdf',
        ]);

        $filters = $this->getFilters($request);
        $type = $request->type;
        $format = $request->format;

        $data = match ($type) {
            'order' => $this->reportService->getOrderReport($filters),
            'kredit' => $this->reportService->getCreditReport($filters),
            'pembayaran' => $this->reportService->getPaymentReport($filters),
            'kinerja_user' => $this->reportService->getUserPerformanceReport($filters),
        };

        $fileName = "laporan-{$type}-" . now()->format('Y-m-d') . ".{$format}";

        // Log export
        ReportExport::create([
            'user_id' => auth()->id(),
            'report_type' => $type,
            'file_name' => $fileName,
            'file_path' => 'exports/' . $fileName,
            'format' => $format,
            'filters' => $filters,
        ]);

        if ($format === 'xlsx') {
            if ($type === 'order') return Excel::download(new \App\Exports\OrderExport($data['data']), $fileName);
            if ($type === 'kredit') return Excel::download(new \App\Exports\CreditExport($data['data']), $fileName);
            if ($type === 'pembayaran') return Excel::download(new \App\Exports\PaymentExport($data['data']), $fileName);
            if ($type === 'kinerja_user') return Excel::download(new \App\Exports\UserPerformanceExport($data['data']), $fileName);
        }

        if ($format === 'pdf') {
            return $this->exportPdf($data, $type, $fileName, $filters);
        }

        return $this->exportCsv($data, $type, $fileName);
    }

    private function exportPdf(array $data, string $type, string $fileName, array $filters)
    {
        $pdf = Pdf::loadView('reports.pdf', [
            'data' => $data,
            'type' => $type,
            'filters' => $filters,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    /**
     * CSV export helper.
     */
    private function exportCsv(array $data, string $type, string $fileName)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($data, $type) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($file, ['Laporan Kremo - ' . ucfirst($type)]);
            fputcsv($file, ['Tanggal Export: ' . now()->format('d/m/Y H:i')]);
            fputcsv($file, []);

            $items = collect($data['data'] ?? []);

            if ($type === 'order') {
                fputcsv($file, ['No', 'Tanggal', 'Pelanggan', 'Motor', 'Harga Cash', 'DP', 'Status']);
                foreach ($items as $i => $item) {
                    fputcsv($file, [
                        $i + 1,
                        $item->tgl_pengajuan_kredit?->format('d/m/Y') ?? '-',
                        $item->pelanggan?->nama_pelanggan ?? '-',
                        $item->motor?->nama_motor ?? '-',
                        $item->harga_cash,
                        $item->dp,
                        $item->status_pengajuan,
                    ]);
                }
            } elseif ($type === 'kredit') {
                fputcsv($file, ['No', 'Mulai', 'Pelanggan', 'Motor', 'Sisa Kredit', 'Status']);
                foreach ($items as $i => $item) {
                    fputcsv($file, [
                        $i + 1,
                        $item->tgl_mulai_kredit?->format('d/m/Y') ?? '-',
                        $item->pengajuanKredit?->pelanggan?->nama_pelanggan ?? '-',
                        $item->pengajuanKredit?->motor?->nama_motor ?? '-',
                        $item->sisa_kredit,
                        $item->status_kredit,
                    ]);
                }
            } elseif ($type === 'pembayaran') {
                fputcsv($file, ['No', 'Tgl Bayar', 'Pelanggan', 'Angsuran Ke', 'Nominal', 'Metode']);
                foreach ($items as $i => $item) {
                    fputcsv($file, [
                        $i + 1,
                        $item->tgl_bayar?->format('d/m/Y') ?? '-',
                        $item->kredit?->pengajuanKredit?->pelanggan?->nama_pelanggan ?? '-',
                        $item->angsuran_ke,
                        $item->total_bayar,
                        $item->payment_type ? 'Midtrans' : 'Manual',
                    ]);
                }
            } else {
                fputcsv($file, ['No', 'Nama', 'Email', 'Role', 'Total Respons', 'Pengajuan Dibuat', 'Pelanggan Dibuat', 'Survey Diambil', 'Survey Selesai', 'Survey Aktif', 'Approval Ditangani', 'Disetujui', 'Ditolak', 'Aktivitas Terakhir']);
                foreach ($items as $i => $item) {
                    fputcsv($file, [
                        $i + 1,
                        $item['nama'],
                        $item['email'],
                        ucfirst($item['role']),
                        $item['total_respons'],
                        $item['pengajuan_dibuat'],
                        $item['pelanggan_dibuat'],
                        $item['survey_diambil'],
                        $item['survey_selesai'],
                        $item['survey_aktif'],
                        $item['approval_ditangani'],
                        $item['approval_disetujui'],
                        $item['approval_ditolak'],
                        $item['last_activity'] ? $item['last_activity']->format('d/m/Y H:i') : '-',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Extract filters from request.
     */
    private function getFilters(Request $request): array
    {
        return [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'motor_id' => $request->motor_id,
            'tenor_id' => $request->tenor_id,
            'role' => $request->role,
            'preset' => $request->preset,
        ];
    }
}
