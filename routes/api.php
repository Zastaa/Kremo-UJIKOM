<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MotorController;
use App\Http\Controllers\PengajuanKreditController;
use App\Models\Angsuran;
use App\Models\Kredit;
use App\Models\Motor;
use App\Models\Pelanggan;
use App\Models\PengajuanKredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// === PUBLIC API ===
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/midtrans/callback', [\App\Http\Controllers\PaymentController::class, 'callback']);

Route::get('/motors', function () {
    return Motor::with('jenisMotor')->where('stok', '>', 0)->paginate(15);
});

Route::get('/motors/{motor}', function (Motor $motor) {
    return $motor->load('jenisMotor');
});

// === AUTHENTICATED API ===
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'apiLogout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Pengajuan
    Route::get('/pengajuan', function (Request $request) {
        $query = PengajuanKredit::with(['pelanggan', 'motor', 'jenisCicilan', 'asuransi']);
        if ($request->filled('status')) {
            $query->where('status_pengajuan', $request->status);
        }
        return $query->latest()->paginate(15);
    });

    Route::get('/pengajuan/{pengajuan}', function (PengajuanKredit $pengajuan) {
        return $pengajuan->load(['pelanggan', 'motor', 'jenisCicilan', 'asuransi', 'kredit.angsuran', 'pengiriman']);
    });

    // Kredit
    Route::get('/kredit', function () {
        return Kredit::with(['pengajuanKredit.pelanggan', 'pengajuanKredit.motor', 'metodeBayar'])->paginate(15);
    });

    // Angsuran
    Route::get('/angsuran', function (Request $request) {
        $query = Angsuran::with('kredit.pengajuanKredit.pelanggan');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        return $query->paginate(15);
    });

    // Pelanggan
    Route::get('/pelanggan', function () {
        return Pelanggan::paginate(15);
    });

    // === FITUR BARU: Shipping API (RajaOngkir) ===
    Route::get('/shipping/destinations', [\App\Http\Controllers\ShippingController::class, 'destinations']);
    Route::get('/shipping/provinces', [\App\Http\Controllers\ShippingController::class, 'provinces']);
    Route::get('/shipping/cities/{provinceId}', [\App\Http\Controllers\ShippingController::class, 'cities']);
    Route::post('/shipping/cost', [\App\Http\Controllers\ShippingController::class, 'calculateCost']);
});
