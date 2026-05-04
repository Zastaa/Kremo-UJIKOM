<?php

use App\Http\Controllers\AngsuranController;
use App\Http\Controllers\AsuransiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevTimeTravelController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\JenisCicilanController;
use App\Http\Controllers\KreditController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\MetodeBayarController;
use App\Http\Controllers\MotorController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PengajuanKreditController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// === PUBLIC ROUTES ===
Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/catalog', [LandingPageController::class, 'catalog'])->name('catalog');
Route::get('/motor/{motor}', [LandingPageController::class, 'motorDetail'])->name('motor.detail');

// === AUTH ROUTES ===
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Email verification must be reachable by guests during registration and by
// authenticated users whose account is not verified yet.
Route::get('/verify-email', [OtpController::class, 'showVerify'])->name('otp.verify.form');
Route::post('/verify-email', [OtpController::class, 'verify'])->name('otp.verify');
Route::post('/verify-email/resend', [OtpController::class, 'resend'])->name('otp.resend');

// === AUTHENTICATED ROUTES ===
Route::middleware(['auth', 'otp_verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    if (app()->environment(['local', 'testing'])) {
        Route::middleware('role:admin')->group(function () {
            Route::get('/dev/time-travel', [DevTimeTravelController::class, 'show'])->name('dev.time-travel.show');
            Route::post('/dev/time-travel', [DevTimeTravelController::class, 'store'])->name('dev.time-travel.store');
            Route::post('/dev/time-travel/reset', [DevTimeTravelController::class, 'reset'])->name('dev.time-travel.reset');
        });
    }

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('metode-bayar', MetodeBayarController::class)->except(['show']);
    });

    // Admin & Marketing
    Route::middleware('role:admin,marketing')->group(function () {
        Route::resource('motors', MotorController::class);
        Route::resource('pelanggan', PelangganController::class);
        Route::resource('jenis-cicilan', JenisCicilanController::class)->except(['show']);
        Route::resource('asuransi', AsuransiController::class)->except(['show']);
    });

    // Admin, Marketing, Surveyor, Approver
    Route::middleware('role:admin,marketing,surveyor,approver,customer')->group(function () {
        Route::resource('pengajuan', PengajuanKreditController::class);
        Route::get('/kredit', [KreditController::class, 'index'])->name('kredit.index');
        Route::get('/kredit/{kredit}', [KreditController::class, 'show'])->name('kredit.show');
        Route::get('/angsuran', [AngsuranController::class, 'index'])->name('angsuran.index');
        Route::get('/angsuran/{angsuran}', [AngsuranController::class, 'show'])->name('angsuran.show');
    });

    Route::middleware('role:surveyor')->group(function () {
        Route::post('/pengajuan/{pengajuan}/claim', [PengajuanKreditController::class, 'claimSurvey'])->name('pengajuan.claim');
        Route::post('/pengajuan/{pengajuan}/survey', [PengajuanKreditController::class, 'submitSurvey'])->name('pengajuan.survey');
    });

    Route::middleware('role:approver')->group(function () {
        Route::post('/pengajuan/{pengajuan}/approve', [PengajuanKreditController::class, 'approve'])->name('pengajuan.approve');
        Route::post('/pengajuan/{pengajuan}/reject', [PengajuanKreditController::class, 'reject'])->name('pengajuan.reject');
    });

    // Angsuran payment
    Route::middleware('role:admin')->group(function () {
        Route::post('/angsuran/{angsuran}/bayar', [AngsuranController::class, 'bayar'])->name('angsuran.bayar');
    });

    // Angsuran payment via Midtrans (Customer)
    Route::middleware('role:customer')->group(function () {
        Route::get('/profile/data-pribadi', [CustomerProfileController::class, 'edit'])->name('customer.profile.edit');
        Route::put('/profile/data-pribadi', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
        Route::post('/pengajuan/{pengajuan}/dp-payment', [\App\Http\Controllers\PaymentController::class, 'payDownPayment'])->name('payment.dp');
        Route::get('/pengajuan/{pengajuan}/dp-sync', [\App\Http\Controllers\PaymentController::class, 'syncDownPayment'])->name('payment.dp.sync');
        Route::get('/angsuran/{angsuran}/pay', [\App\Http\Controllers\PaymentController::class, 'pay'])->name('payment.pay');
        Route::get('/angsuran/{angsuran}/sync', [\App\Http\Controllers\PaymentController::class, 'sync'])->name('payment.sync');
        Route::post('/pengiriman/{pengiriman}/confirm-received', [PengirimanController::class, 'confirmReceived'])->name('pengiriman.confirmReceived');
    });

    // Pengiriman
    Route::middleware('role:admin,marketing')->group(function () {
        Route::resource('pengiriman', PengirimanController::class)->except(['edit', 'update']);
        Route::post('/pengiriman/{pengiriman}/status', [PengirimanController::class, 'updateStatus'])->name('pengiriman.updateStatus');
        Route::post('/pengiriman/{pengiriman}/verify-received', [PengirimanController::class, 'verifyReceived'])->name('pengiriman.verifyReceived');
        Route::post('/pengiriman/{pengiriman}/reject-received', [PengirimanController::class, 'rejectReceived'])->name('pengiriman.rejectReceived');
    });

    // === FITUR BARU: Laporan ===
    Route::middleware('role:admin')->group(function () {
        Route::get('/reports/orders', [ReportController::class, 'orderReport'])->name('reports.orders');
        Route::get('/reports/credits', [ReportController::class, 'creditReport'])->name('reports.credits');
        Route::get('/reports/payments', [ReportController::class, 'paymentReport'])->name('reports.payments');
        Route::get('/reports/user-performance', [ReportController::class, 'userPerformanceReport'])->name('reports.user-performance');
        Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // === FITUR BARU: Scanner QR/Barcode ===
    Route::middleware('role:admin,marketing')->group(function () {
        Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner.index');
        Route::post('/scanner/verify', [ScannerController::class, 'verify'])->name('scanner.verify');
        Route::get('/scanner/qr/{type}/{id}', [ScannerController::class, 'generateQr'])->name('scanner.qr');
        Route::post('/scanner/sync-midtrans', [ScannerController::class, 'syncMidtrans'])->name('scanner.sync-midtrans');
    });

    // Routes removed from here, moved to guest group

    // === FITUR BARU: Email Logs (Admin) ===
    Route::middleware('role:admin')->group(function () {
        Route::get('/email-logs', [EmailLogController::class, 'index'])->name('email-logs.index');
    });

    // === FITUR BARU: Shipping API ===
    Route::middleware('role:admin,marketing,customer')->group(function () {
        Route::get('/shipping/destinations', [ShippingController::class, 'destinations'])->name('shipping.destinations');
        Route::post('/shipping/cost', [ShippingController::class, 'calculateCost'])->name('shipping.cost');
    });

    Route::middleware('role:admin,marketing')->group(function () {
        Route::post('/shipping/sync-regions', [ShippingController::class, 'syncRegions'])->name('shipping.sync');
    });
});

// === FITUR BARU: Forgot Password (Guest) ===
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.forgot');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.forgot.send');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showReset'])->name('password.reset.form');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.reset');

});
