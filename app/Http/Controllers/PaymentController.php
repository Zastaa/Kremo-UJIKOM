<?php

namespace App\Http\Controllers;

use App\Mail\PaymentSuccessMail;
use App\Mail\PengajuanStatusChangedMail;
use App\Models\Angsuran;
use App\Models\PaymentStatusLog;
use App\Models\PengajuanKredit;
use App\Services\MailNotificationService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct(
        private MailNotificationService $mailNotificationService,
        private RajaOngkirService $rajaOngkir,
    ) {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function pay(Request $request, Angsuran $angsuran)
    {
        $this->abortIfCustomerDoesNotOwnAngsuran($request, $angsuran);

        // Hanya Customer yang angsurannya belum lunas yang bisa bayar
        if ($angsuran->status === 'Lunas') {
            return redirect()->route('angsuran.show', $angsuran)->with('error', 'Angsuran ini sudah lunas.');
        }

        if (! $angsuran->is_customer_payable) {
            return redirect()->route('angsuran.show', $angsuran)
                ->with('error', $angsuran->customer_payment_lock_reason ?: 'Angsuran ini belum dapat dibayar.');
        }

        $kredit = $angsuran->kredit;
        $pelanggan = $kredit->pengajuanKredit->pelanggan;
        $motor = $kredit->pengajuanKredit->motor;

        // Generate order_id unik: ANG-{id}-{timestamp}
        $orderId = 'ANG-' . $angsuran->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $angsuran->total_bayar,
            ],
            'customer_details' => [
                'first_name' => $pelanggan->nama_pelanggan,
                'email' => $pelanggan->email ?? 'noreply@kremo.test',
                'phone' => $pelanggan->no_telp,
            ],
            'item_details' => [
                [
                    'id' => 'ANG-' . $angsuran->id,
                    'price' => (int) $angsuran->total_bayar,
                    'quantity' => 1,
                    'name' => 'Angsuran Ke-' . $angsuran->angsuran_ke . ' ' . $motor->nama_motor,
                ]
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            // Simpan token dan order ID di database.
            // Kita gunakan kolom payment_type untuk menyimpan order_id agar bisa di-match saat callback webhook.
            $angsuran->update([
                'snap_token' => $snapToken,
                'payment_type' => $orderId 
            ]);

            return view('payment.checkout', [
                'angsuran' => $angsuran,
                'snapToken' => $snapToken,
                'paymentTitle' => 'Pembayaran Angsuran',
                'paymentSubtitle' => 'Selesaikan pembayaran angsuran melalui Midtrans.',
                'totalAmount' => (int) $angsuran->total_bayar,
                'summaryRows' => [
                    'Angsuran Ke' => $angsuran->angsuran_ke,
                    'Motor' => $motor->nama_motor,
                    'Jatuh Tempo' => $angsuran->tgl_jatuh_tempo?->format('d/m/Y') ?? '-',
                    'Order ID' => $orderId,
                ],
                'syncUrl' => route('payment.sync', $angsuran),
                'cancelUrl' => route('angsuran.index'),
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pembayaran. Hubungi admin.');
        }
    }

    public function payDownPayment(Request $request, PengajuanKredit $pengajuan)
    {
        $this->abortIfCustomerDoesNotOwnPengajuan($request, $pengajuan);
        $pengajuan->loadMissing(['pelanggan', 'motor', 'jenisCicilan']);

        if ($pengajuan->status_pengajuan !== 'Disetujui') {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Pembayaran DP hanya tersedia setelah pengajuan disetujui.');
        }

        if ($pengajuan->is_down_payment_paid) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('info', 'DP dan ongkir untuk pengajuan ini sudah lunas.');
        }

        $data = $request->validate([
            'origin_destination_id' => 'nullable|integer',
            'origin_label' => 'nullable|string|max:255',
            'destination_destination_id' => 'required|integer',
            'destination_label' => 'required|string|max:255',
            'package_weight' => 'nullable|integer|min:1',
            'courier_code' => ['required', 'string', Rule::in(array_keys(config('rajaongkir.couriers', [])))],
            'courier_service' => 'required|string|max:60',
            'shipping_cost' => 'required|integer|min:0',
            'shipping_etd' => 'nullable|string|max:80',
        ]);

        $origin = ($data['origin_destination_id'] ?? null) ?: config('rajaongkir.origin_destination_id');
        if (! $origin) {
            return back()->with('error', 'Origin RajaOngkir belum diatur.')->withInput();
        }

        $packageWeight = $pengajuan->motor?->shipping_weight_grams
            ?: (($data['package_weight'] ?? null) ?: (int) config('rajaongkir.default_weight', 125000));
        $rates = $this->rajaOngkir->calculateDomesticCost(
            (int) $origin,
            (int) $data['destination_destination_id'],
            (int) $packageWeight,
            $data['courier_code'],
        );
        $selectedRate = collect($rates)->first(fn (array $rate) => strtolower((string) $rate['code']) === strtolower($data['courier_code'])
            && strtoupper((string) $rate['service']) === strtoupper($data['courier_service']));

        if (! $selectedRate) {
            return back()
                ->with('error', $this->rajaOngkir->lastCostError() ?: 'Layanan ongkir yang dipilih sudah tidak tersedia. Silakan hitung ulang ongkir.')
                ->withInput();
        }

        $shippingCost = (int) $selectedRate['cost'];
        $grossAmount = (int) $pengajuan->dp + $shippingCost;

        if ($grossAmount <= 0) {
            return back()->with('error', 'Total DP dan ongkir harus lebih dari Rp 0.')->withInput();
        }

        $orderId = 'DP-' . $pengajuan->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $pengajuan->pelanggan?->nama_pelanggan,
                'email' => $pengajuan->pelanggan?->email ?? 'noreply@kremo.test',
                'phone' => $pengajuan->pelanggan?->no_telp,
            ],
            'item_details' => [
                [
                    'id' => 'DP-' . $pengajuan->id,
                    'price' => (int) $pengajuan->dp,
                    'quantity' => 1,
                    'name' => 'DP ' . ($pengajuan->motor?->nama_motor ?? 'Motor'),
                ],
                [
                    'id' => 'SHIP-' . $pengajuan->id,
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => 'Ongkir ' . ($selectedRate['name'] ?: strtoupper($data['courier_code'])) . ' ' . $selectedRate['service'],
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $pengajuan->update([
                'dp_payment_status' => 'Pending',
                'dp_payment_order_id' => $orderId,
                'dp_snap_token' => $snapToken,
                'dp_paid_amount' => $grossAmount,
                'shipping_origin_destination_id' => $origin,
                'shipping_origin_label' => ($data['origin_label'] ?? null) ?: config('rajaongkir.origin_label'),
                'shipping_destination_destination_id' => $data['destination_destination_id'],
                'shipping_destination_label' => $data['destination_label'],
                'shipping_package_weight' => $packageWeight,
                'shipping_courier_code' => $selectedRate['code'],
                'shipping_courier_service' => $selectedRate['service'],
                'shipping_cost' => $shippingCost,
                'shipping_etd' => $selectedRate['etd'] ?? ($data['shipping_etd'] ?? null),
            ]);

            return view('payment.checkout', [
                'pengajuan' => $pengajuan->fresh(['pelanggan', 'motor']),
                'snapToken' => $snapToken,
                'paymentTitle' => 'Pembayaran DP & Ongkir',
                'paymentSubtitle' => 'DP dan ongkir dibayar sekaligus sebelum pengiriman dibuat.',
                'totalAmount' => $grossAmount,
                'summaryRows' => [
                    'Motor' => $pengajuan->motor?->nama_motor ?? '-',
                    'DP' => 'Rp ' . number_format($pengajuan->dp, 0, ',', '.'),
                    'Ongkir' => 'Rp ' . number_format($shippingCost, 0, ',', '.'),
                    'Layanan' => ($selectedRate['name'] ?: strtoupper($selectedRate['code'])) . ' ' . $selectedRate['service'],
                    'Estimasi' => $selectedRate['etd'] ?: '-',
                    'Order ID' => $orderId,
                ],
                'syncUrl' => route('payment.dp.sync', $pengajuan),
                'cancelUrl' => route('pengajuan.show', $pengajuan),
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans DP Snap Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pembayaran DP dan ongkir. Hubungi admin.')->withInput();
        }
    }

    public function callback(Request $request)
    {
        $payload = $request->getContent();
        $notification = json_decode($payload);
        
        if (!$notification) {
            return response()->json(['message' => 'Invalid JSON'], 400);
        }

        $validSignatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . Config::$serverKey);
        
        if ($notification->signature_key != $validSignatureKey) {
            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;
        $fraudStatus = $notification->fraud_status ?? null;

        // Cari angsuran berdasarkan order_id yg disimpan di payment_type
        $angsuran = Angsuran::where('payment_type', $orderId)->first();

        if (! $angsuran) {
            $pengajuan = PengajuanKredit::where('dp_payment_order_id', $orderId)->first();

            if (! $pengajuan) {
                return response()->json(['message' => 'Payment target not found for this order_id'], 404);
            }

            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                if ($fraudStatus !== 'challenge') {
                    $this->markDownPaymentPaid($pengajuan, (array) $notification);
                }
            } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $pengajuan->update(['dp_payment_status' => 'Gagal']);
            } elseif ($transactionStatus == 'pending') {
                $pengajuan->update(['dp_payment_status' => 'Pending']);
            }

            return response()->json(['message' => 'DP payment callback processed']);
        }

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            if ($fraudStatus == 'challenge') {
                // Pending re-verification
            } else {
                if ($angsuran->status !== 'Lunas') {
                    $angsuran->update([
                        'status' => 'Lunas',
                        'tgl_bayar' => now(),
                    ]);

                    // Update sisa kredit
                    $kredit = $angsuran->kredit;
                    $kredit->sisa_kredit -= $angsuran->total_bayar;
                    
                    // Cek jika seluruh kredit sudah lunas
                    if ($kredit->sisa_kredit <= 0) {
                        $kredit->status_kredit = 'Lunas';
                        $kredit->tgl_selesai_kredit = now();
                    }
                    $kredit->save();

                    // Log status change ke payment_status_logs
                    PaymentStatusLog::create([
                        'angsuran_id' => $angsuran->id,
                        'order_id' => $orderId,
                        'previous_status' => 'Belum Bayar',
                        'new_status' => $transactionStatus,
                        'midtrans_response' => (array) $notification,
                    ]);

                    // Kirim email notifikasi pembayaran berhasil
                    try {
                        $pelanggan = $kredit->pengajuanKredit?->pelanggan;
                        if ($pelanggan?->email) {
                            app(MailNotificationService::class)->send(
                                new PaymentSuccessMail($angsuran->fresh()),
                                $pelanggan->email,
                                'Pembayaran Angsuran Berhasil - Kremo',
                                'payment-success',
                                null,
                                Angsuran::class,
                                $angsuran->id,
                            );
                        }
                    } catch (\Exception $e) {
                        Log::error('Payment email notification failed: ' . $e->getMessage());
                    }

                    Log::info("Angsuran ID {$angsuran->id} lunas via Midtrans.");
                }
            }
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            // Bisa ubah status jika implementasi detail, tapi biarkan 'Belum Bayar' saja.
            Log::info("Angsuran ID {$angsuran->id} payment failed/expired.");
        } else if ($transactionStatus == 'pending') {
            Log::info("Angsuran ID {$angsuran->id} payment pending.");
        }

        return response()->json(['message' => 'Callback processed']);
    }

    public function sync(Request $request, Angsuran $angsuran)
    {
        $this->abortIfCustomerDoesNotOwnAngsuran($request, $angsuran);

        if ($angsuran->status === 'Belum Bayar' && $angsuran->payment_type) {
            try {
                $status = \Midtrans\Transaction::status($angsuran->payment_type);
                $transactionStatus = is_array($status) ? $status['transaction_status'] : $status->transaction_status;
                
                if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                    $angsuran->update([
                        'status' => 'Lunas',
                        'tgl_bayar' => now(),
                    ]);

                    // Update sisa kredit
                    $kredit = $angsuran->kredit;
                    $kredit->sisa_kredit -= $angsuran->total_bayar;
                    
                    if ($kredit->sisa_kredit <= 0) {
                        $kredit->status_kredit = 'Lunas';
                        $kredit->tgl_selesai_kredit = now();
                    }
                    $kredit->save();

                    return redirect()->route('angsuran.index')->with('success', 'Pembayaran via Midtrans berhasil dikonfirmasi!');
                } else if ($transactionStatus == 'pending') {
                    return redirect()->route('angsuran.index')->with('info', 'Pembayaran Midtrans Anda masih menunggu diproses.');
                } else {
                    return redirect()->route('angsuran.index')->with('error', 'Status pembayaran Midtrans: ' . $transactionStatus);
                }
            } catch (\Exception $e) {
                Log::error('Midtrans sync error: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('angsuran.index');
    }

    public function syncDownPayment(Request $request, PengajuanKredit $pengajuan)
    {
        $this->abortIfCustomerDoesNotOwnPengajuan($request, $pengajuan);

        if ($pengajuan->is_down_payment_paid) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('success', 'Pembayaran DP dan ongkir sudah lunas.');
        }

        if (! $pengajuan->dp_payment_order_id) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Belum ada transaksi DP dan ongkir untuk disinkronkan.');
        }

        try {
            $status = \Midtrans\Transaction::status($pengajuan->dp_payment_order_id);
            $transactionStatus = is_array($status) ? $status['transaction_status'] : $status->transaction_status;
            $payload = is_array($status) ? $status : (array) $status;

            if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                $this->markDownPaymentPaid($pengajuan, $payload);

                return redirect()->route('pengajuan.show', $pengajuan)
                    ->with('success', 'Pembayaran DP dan ongkir berhasil dikonfirmasi. Admin/marketing sudah dapat membuat pengiriman.');
            }

            if ($transactionStatus == 'pending') {
                $pengajuan->update(['dp_payment_status' => 'Pending']);
                return redirect()->route('pengajuan.show', $pengajuan)
                    ->with('info', 'Pembayaran DP dan ongkir masih menunggu diproses.');
            }

            $pengajuan->update(['dp_payment_status' => 'Gagal']);
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Status pembayaran DP dan ongkir: ' . $transactionStatus);
        } catch (\Exception $e) {
            Log::error('Midtrans DP sync error: ' . $e->getMessage());
        }

        return redirect()->route('pengajuan.show', $pengajuan);
    }

    private function markDownPaymentPaid(PengajuanKredit $pengajuan, array $payload = []): void
    {
        if ($pengajuan->is_down_payment_paid) {
            return;
        }

        $pengajuan->update([
            'dp_payment_status' => 'Lunas',
            'dp_paid_at' => now(),
        ]);

        $pengajuan->refresh()->load(['pelanggan', 'motor', 'jenisCicilan', 'metodeBayar', 'asuransi', 'surveyor', 'approver']);

        if ($pengajuan->pelanggan?->email) {
            try {
                $this->mailNotificationService->send(
                    new PengajuanStatusChangedMail($pengajuan, 'DP & Ongkir Dibayar'),
                    $pengajuan->pelanggan->email,
                    "Pembayaran DP & Ongkir Berhasil #{$pengajuan->id} - Kremo",
                    'pengajuan-status-changed',
                    null,
                    PengajuanKredit::class,
                    $pengajuan->id,
                );
            } catch (\Exception $e) {
                Log::error('DP payment email notification failed: ' . $e->getMessage());
            }
        }
    }
}
