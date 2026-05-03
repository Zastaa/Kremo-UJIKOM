<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\PengajuanKredit;
use App\Models\PaymentStatusLog;
use App\Services\ScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScannerController extends Controller
{
    public function __construct(private ScannerService $scannerService) {}

    /**
     * Scanner page with camera.
     */
    public function index()
    {
        return view('scanner.index');
    }

    /**
     * Verify a scanned token.
     */
    public function verify(Request $request)
    {
        $request->validate(['token' => 'required|string|min:10']);

        $token = $this->scannerService->resolveToken($request->token);

        if (!$token) {
            $this->scannerService->logScan($request->token, 'scan_not_found', auth()->id());
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan.',
            ], 404);
        }

        if ($token->isExpired()) {
            $this->scannerService->logScan($request->token, 'scan_expired', auth()->id(), $token->tokenable_type, $token->tokenable_id);
            return response()->json([
                'success' => false,
                'message' => 'Token sudah kadaluarsa.',
            ], 410);
        }

        // Log the scan
        $this->scannerService->logScan(
            $request->token, 'scan_verified', auth()->id(),
            $token->tokenable_type, $token->tokenable_id
        );

        // Load related data
        $entity = $token->tokenable;
        $result = ['type' => $token->tokenable_type, 'id' => $token->tokenable_id];

        if ($entity instanceof PengajuanKredit) {
            $entity->load(['pelanggan', 'motor', 'jenisCicilan', 'kredit.angsuran']);
            $result['pengajuan'] = $entity;
            $result['label'] = 'Pengajuan Kredit #' . $entity->id;
        } elseif ($entity instanceof Angsuran) {
            $entity->load(['kredit.pengajuanKredit.pelanggan', 'kredit.pengajuanKredit.motor']);
            $result['angsuran'] = $entity;
            $result['label'] = 'Angsuran Ke-' . $entity->angsuran_ke;

            // Check Midtrans status if available
            if ($entity->payment_type) {
                $result['midtrans_order_id'] = $entity->payment_type;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Generate QR token for an entity.
     */
    public function generateQr(string $type, int $id)
    {
        $typeMap = [
            'pengajuan' => PengajuanKredit::class,
            'angsuran' => Angsuran::class,
        ];

        if (!isset($typeMap[$type])) {
            return response()->json(['error' => 'Tipe tidak valid.'], 400);
        }

        $modelClass = $typeMap[$type];
        $entity = $modelClass::findOrFail($id);

        $token = $this->scannerService->generateToken($modelClass, $id);

        return response()->json([
            'success' => true,
            'token' => $token->token,
            'qr_data' => $token->token,
        ]);
    }

    /**
     * Sync Midtrans status for an angsuran.
     */
    public function syncMidtrans(Request $request)
    {
        $request->validate(['angsuran_id' => 'required|exists:angsuran,id']);

        $angsuran = Angsuran::findOrFail($request->angsuran_id);

        if (!$angsuran->payment_type) {
            return response()->json(['message' => 'Tidak ada transaksi Midtrans untuk angsuran ini.'], 400);
        }

        try {
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            $status = \Midtrans\Transaction::status($angsuran->payment_type);
            $transactionStatus = is_array($status) ? $status['transaction_status'] : $status->transaction_status;

            // Log status change
            PaymentStatusLog::create([
                'angsuran_id' => $angsuran->id,
                'order_id' => $angsuran->payment_type,
                'previous_status' => $angsuran->status,
                'new_status' => $transactionStatus,
                'midtrans_response' => is_array($status) ? $status : (array) $status,
            ]);

            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                if ($angsuran->status !== 'Lunas') {
                    $angsuran->update(['status' => 'Lunas', 'tgl_bayar' => now()]);
                    $kredit = $angsuran->kredit;
                    $kredit->sisa_kredit -= $angsuran->total_bayar;
                    if ($kredit->sisa_kredit <= 0) {
                        $kredit->status_kredit = 'Lunas';
                        $kredit->tgl_selesai_kredit = now();
                    }
                    $kredit->save();
                }
            }

            return response()->json([
                'success' => true,
                'midtrans_status' => $transactionStatus,
                'angsuran_status' => $angsuran->fresh()->status,
            ]);
        } catch (\Exception $e) {
            Log::error('ScannerController syncMidtrans: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal sinkronisasi: ' . $e->getMessage()], 500);
        }
    }
}
