<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RegisterTokoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardChartSettingsController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\JasaController;
use App\Http\Controllers\KlienController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PembayaranGatewayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:3,1')->name('register.store');

    Route::get('/register/otp', [OtpController::class, 'create'])->name('register.otp.notice');
    Route::post('/register/otp', [OtpController::class, 'store'])->middleware('throttle:5,1')->name('register.otp.verify');
    Route::post('/register/otp/resend', [OtpController::class, 'resend'])->middleware('throttle:3,1')->name('register.otp.resend');
});

Route::get('/bayar/{pembayaran}/{token}', [PembayaranGatewayController::class, 'checkout'])->name('pembayaran.gateway.checkout');
Route::post('/bayar/{pembayaran}/{token}/sync', [PembayaranGatewayController::class, 'sync'])->name('pembayaran.gateway.sync');

Route::middleware('auth')->group(function () {
    Route::get('/register/toko', [RegisterTokoController::class, 'create'])->name('register.toko.create');
    Route::post('/register/toko', [RegisterTokoController::class, 'store'])->name('register.toko.store');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search/global', GlobalSearchController::class)->name('search.global');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::get('/settings/toko', [SettingsController::class, 'store'])->name('settings.toko');
    Route::patch('/settings/toko', [SettingsController::class, 'updateStoreIdentity'])->name('settings.toko.update');
    Route::get('/settings/personalisasi', [SettingsController::class, 'personalization'])->name('settings.personalisasi');
    Route::get('/settings/pembayaran/qris', [SettingsController::class, 'paymentQris'])->name('settings.payment.qris');
    Route::patch('/settings/pembayaran/qris', [SettingsController::class, 'updatePaymentQris'])->name('settings.payment.qris.update');
    Route::get('/settings/pembayaran/metode-lainnya', [SettingsController::class, 'paymentMethods'])->name('settings.payment.methods');
    Route::get('/settings/dashboard', [SettingsController::class, 'dashboard'])->name('settings.dashboard');

    Route::get('/pengaturan-dashboard', fn () => redirect()->route('settings.dashboard'))->name('pengaturan-dashboard.edit');
    Route::patch('/pengaturan-dashboard/defaults', [DashboardChartSettingsController::class, 'updateDefaults'])->name('pengaturan-dashboard.defaults.update');
    Route::patch('/pengaturan-dashboard/overrides', [DashboardChartSettingsController::class, 'updateOverrides'])->name('pengaturan-dashboard.overrides.update');
    Route::delete('/pengaturan-dashboard/overrides/{slot}', [DashboardChartSettingsController::class, 'destroyOverride'])->name('pengaturan-dashboard.overrides.destroy');
    Route::post('/pengaturan-dashboard/reset-default/{slot}', [DashboardChartSettingsController::class, 'resetDefault'])->name('pengaturan-dashboard.defaults.reset');
    Route::patch('/notifikasi/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::get('/biaya-jasa/data', [JasaController::class, 'data'])->name('biaya-jasa.data');
    Route::get('/biaya-jasa/{jasa}/preview', [JasaController::class, 'preview'])->name('biaya-jasa.preview');
    Route::resource('biaya-jasa', JasaController::class)->parameters(['biaya-jasa' => 'jasa'])->except('show');
    Route::get('/pelanggan/data', [KlienController::class, 'data'])->name('pelanggan.data');
    Route::get('/pelanggan/{klien}/preview', [KlienController::class, 'preview'])->name('pelanggan.preview');
    Route::resource('pelanggan', KlienController::class)->parameters(['pelanggan' => 'klien'])->except('show');

    Route::get('/laundry/data', [LaundryController::class, 'data'])->name('laundry.data');
    Route::get('/laundry/{laundry}/preview', [LaundryController::class, 'preview'])->name('laundry.preview');
    Route::resource('laundry', LaundryController::class)->except('show');
    Route::patch('/laundry/{laundry}/status', [LaundryController::class, 'updateStatus'])->name('laundry.status.update');

    Route::get('/pembayaran/belum-bayar', [PembayaranController::class, 'unpaid'])->name('pembayaran.unpaid');
    Route::get('/pembayaran/belum-bayar/data', [PembayaranController::class, 'unpaidData'])->name('pembayaran.unpaid.data');
    Route::get('/pembayaran/data', [PembayaranController::class, 'data'])->name('pembayaran.data');
    Route::get('/pembayaran/{pembayaran}/preview', [PembayaranController::class, 'preview'])->name('pembayaran.preview');
    Route::resource('pembayaran', PembayaranController::class);
    Route::get('/pembayaran/{pembayaran}/paid', [PembayaranController::class, 'markAsPaid'])->name('pembayaran.paid');
    Route::post('/pembayaran/{pembayaran}/gateway', [PembayaranGatewayController::class, 'issue'])->name('pembayaran.gateway.issue');

    Route::get('/pengaturan-toko', fn () => redirect()->route('settings.toko'))->name('pengaturan-toko.edit');
    Route::patch('/pengaturan-toko', [ProfileController::class, 'updateStore'])->name('pengaturan-toko.update');

    Route::get('/profile', fn () => redirect()->route('settings.profile'))->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
