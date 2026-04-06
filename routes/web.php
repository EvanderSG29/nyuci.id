<?php

use App\Http\Controllers\LaundryController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisterTokoController;
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
            'pendingPickup' => 0,
            'paidCount' => 0,
        ];

        $recentLaundries = collect();

        if ($toko) {
            $stats['totalLaundry'] = $toko->laundries()->count();
            $stats['pendingPickup'] = $toko->laundries()->where('is_taken', false)->count();
            $stats['paidCount'] = Pembayaran::query()
                ->whereHas('laundry', fn ($query) => $query->where('toko_id', $toko->id))
                ->where('status', 'sudah_bayar')
                ->count();

            $recentLaundries = $toko->laundries()->latest()->take(5)->get();
        }

        return view('dashboard', compact('toko', 'stats', 'recentLaundries'));
    })->name('dashboard');

    Route::resource('laundry', LaundryController::class);
    Route::get('/laundry/{id}/toggle', [LaundryController::class, 'updateStatus'])->name('laundry.toggle');

    Route::resource('pembayaran', PembayaranController::class);
    Route::get('/pembayaran/{id}/paid', [PembayaranController::class, 'markAsPaid'])->name('pembayaran.paid');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
