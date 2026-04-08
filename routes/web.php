<?php

use App\Http\Controllers\Auth\RegisterTokoController;
use App\Http\Controllers\JasaController;
use App\Http\Controllers\KlienController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ProfileController;
use App\Models\Klien;
use App\Models\Pembayaran;
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

Route::middleware('auth')->group(function () {
    Route::get('/register/toko', [RegisterTokoController::class, 'create'])->name('register.toko.create');
    Route::post('/register/toko', [RegisterTokoController::class, 'store'])->name('register.toko.store');

    Route::get('/dashboard', function (Request $request) {
        $user = $request->user()->load('toko');
        $toko = $user->toko;

        $stats = [
            'totalLaundry' => 0,
            'pendingLaundry' => 0,
            'paidCount' => 0,
            'totalPelanggan' => 0,
        ];

        $recentLaundries = collect();

        if ($toko) {
            $stats['totalLaundry'] = $toko->laundries()->count();
            $stats['pendingLaundry'] = $toko->laundries()->where('status', '!=', 'selesai')->count();
            $stats['paidCount'] = Pembayaran::query()
                ->whereHas('laundry', fn ($query) => $query->where('toko_id', $toko->id))
                ->where('status', 'sudah_bayar')
                ->count();
            $stats['totalPelanggan'] = Klien::query()->where('toko_id', $toko->id)->count();

            $recentLaundries = $toko->laundries()->with(['klien', 'jasa'])->latest()->take(5)->get();
        }

        return view('dashboard', compact('toko', 'stats', 'recentLaundries'));
    })->name('dashboard');

    Route::resource('biaya-jasa', JasaController::class)->parameters(['biaya-jasa' => 'jasa'])->except('show');
    Route::resource('pelanggan', KlienController::class)->parameters(['pelanggan' => 'klien'])->except('show');

    Route::resource('laundry', LaundryController::class)->except('show');
    Route::patch('/laundry/{laundry}/status', [LaundryController::class, 'updateStatus'])->name('laundry.status.update');

    Route::get('/pembayaran/belum-bayar', [PembayaranController::class, 'unpaid'])->name('pembayaran.unpaid');
    Route::resource('pembayaran', PembayaranController::class);
    Route::get('/pembayaran/{pembayaran}/paid', [PembayaranController::class, 'markAsPaid'])->name('pembayaran.paid');

    Route::get('/pengaturan-toko', [ProfileController::class, 'editStore'])->name('pengaturan-toko.edit');
    Route::patch('/pengaturan-toko', [ProfileController::class, 'updateStore'])->name('pengaturan-toko.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
