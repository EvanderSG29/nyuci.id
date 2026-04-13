<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RegisterTokoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JasaController;
use App\Http\Controllers\KlienController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PembayaranGatewayController;
use App\Http\Controllers\ProfileController;
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
    Route::patch('/notifikasi/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::get('/biaya-jasa/data', [JasaController::class, 'data'])->name('biaya-jasa.data');
    Route::resource('biaya-jasa', JasaController::class)->parameters(['biaya-jasa' => 'jasa'])->except('show');
    Route::get('/pelanggan/data', [KlienController::class, 'data'])->name('pelanggan.data');
    Route::resource('pelanggan', KlienController::class)->parameters(['pelanggan' => 'klien'])->except('show');

    Route::get('/laundry/data', [LaundryController::class, 'data'])->name('laundry.data');
    Route::resource('laundry', LaundryController::class)->except('show');
    Route::patch('/laundry/{laundry}/status', [LaundryController::class, 'updateStatus'])->name('laundry.status.update');

    Route::get('/pembayaran/belum-bayar', [PembayaranController::class, 'unpaid'])->name('pembayaran.unpaid');
    Route::get('/pembayaran/belum-bayar/data', [PembayaranController::class, 'unpaidData'])->name('pembayaran.unpaid.data');
    Route::get('/pembayaran/data', [PembayaranController::class, 'data'])->name('pembayaran.data');
    Route::resource('pembayaran', PembayaranController::class);
    Route::get('/pembayaran/{pembayaran}/paid', [PembayaranController::class, 'markAsPaid'])->name('pembayaran.paid');
    Route::post('/pembayaran/{pembayaran}/gateway', [PembayaranGatewayController::class, 'issue'])->name('pembayaran.gateway.issue');

    Route::get('/pengaturan-toko', [ProfileController::class, 'editStore'])->name('pengaturan-toko.edit');
    Route::patch('/pengaturan-toko', [ProfileController::class, 'updateStore'])->name('pengaturan-toko.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
