<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryRequest;
use App\Http\Requests\LaundryStatusUpdateRequest;
use App\Models\Laundry;
use App\Models\Toko;
use App\Notifications\LaundryFinishedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class LaundryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola laundry.');
        }

        return view('laundry.index');
    }

    public function create(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum menambah laundry.');
        }

        if (! $toko->kliens()->exists()) {
            return redirect()->route('pelanggan.create')->with('warning', 'Tambahkan pelanggan terlebih dahulu sebelum membuat order laundry.');
        }

        if (! $toko->jasas()->exists()) {
            return redirect()->route('biaya-jasa.create')->with('warning', 'Tambahkan biaya jasa terlebih dahulu sebelum membuat order laundry.');
        }

        return view('laundry.create', [
            'kliens' => $toko->kliens()->orderBy('nama_klien')->get(),
            'jasas' => $toko->jasas()->orderBy('nama_jasa')->get(),
        ]);
    }

    public function store(LaundryRequest $request): RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum menyimpan laundry.');
        }

        $laundry = Laundry::create([
            ...$this->validatedLaundryData($request, $toko),
            'toko_id' => $toko->id,
        ]);

        $this->syncExistingPayment($laundry->load('jasa', 'pembayaran'));

        return redirect()->route('laundry.index')->with('success', 'Data laundry berhasil ditambahkan.');
    }

    public function edit(Laundry $laundry): View
    {
        $this->authorize('update', $laundry);

        return view('laundry.edit', [
            'laundry' => $laundry->load('klien', 'jasa'),
            'kliens' => auth()->user()->toko->kliens()->orderBy('nama_klien')->get(),
            'jasas' => auth()->user()->toko->jasas()->orderBy('nama_jasa')->get(),
        ]);
    }

    public function update(LaundryRequest $request, Laundry $laundry): RedirectResponse
    {
        $this->authorize('update', $laundry);

        $laundry->update($this->validatedLaundryData($request, auth()->user()->toko, $laundry));
        $laundry->refresh()->load('jasa', 'pembayaran');
        $this->syncExistingPayment($laundry);

        return redirect()->route('laundry.index')->with('success', 'Data laundry berhasil diperbarui.');
    }

    public function destroy(Laundry $laundry): RedirectResponse
    {
        $this->authorize('delete', $laundry);
        $laundry->delete();

        return redirect()->route('laundry.index')->with('success', 'Data laundry berhasil dihapus.');
    }

    public function updateStatus(LaundryStatusUpdateRequest $request, Laundry $laundry): RedirectResponse
    {
        $this->authorize('update', $laundry);

        if ((int) $request->validated('status_laundry_id') !== $laundry->id) {
            return back()
                ->withErrors(['status' => 'Data laundry yang dipilih tidak cocok.'])
                ->withInput();
        }

        $validated = $request->validated();
        $wasFinished = $laundry->status === 'selesai';
        $isFinished = $validated['status'] === 'selesai';
        $finishedDate = $isFinished ? $validated['tgl_selesai'] : null;

        if ($finishedDate !== null && $laundry->tanggal_dimulai && $finishedDate < $laundry->tanggal_dimulai->format('Y-m-d')) {
            return back()
                ->withErrors(['tgl_selesai' => 'Tanggal selesai tidak boleh lebih awal dari tanggal masuk.'])
                ->withInput();
        }

        DB::transaction(function () use ($laundry, $validated, $isFinished, $finishedDate): void {
            $laundry->update([
                'status' => $validated['status'],
                'is_taken' => $isFinished,
                'tgl_selesai' => $finishedDate,
            ]);
        });

        $warning = null;

        if (! $wasFinished && $isFinished) {
            $warning = $this->dispatchFinishedNotifications($request, $laundry->fresh());
        }

        $redirect = back()->with('success', 'Status laundry berhasil diperbarui.');

        if ($warning !== null) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    private function validatedLaundryData(LaundryRequest $request, Toko $toko, ?Laundry $laundry = null): array
    {
        $validated = $request->validated();
        $klien = $toko->kliens()->find($validated['klien_id']);
        $jasa = $toko->jasas()->find($validated['jasa_id']);

        if (! $klien) {
            throw ValidationException::withMessages([
                'klien_id' => 'Pelanggan tidak valid untuk toko ini.',
            ]);
        }

        if (! $jasa) {
            throw ValidationException::withMessages([
                'jasa_id' => 'Biaya jasa tidak valid untuk toko ini.',
            ]);
        }

        $qty = (float) $validated['qty'];
        $isFinished = $validated['status'] === 'selesai';

        return [
            'klien_id' => $klien->id,
            'jasa_id' => $jasa->id,
            'qty' => $qty,
            'status' => $validated['status'],
            'tanggal_dimulai' => $validated['tanggal_dimulai'],
            'ets_selesai' => $validated['ets_selesai'],
            'nama' => $klien->nama_klien,
            'no_hp' => $klien->no_hp_klien,
            'jenis_jasa' => $jasa->nama_jasa,
            'layanan' => $jasa->nama_jasa,
            'satuan' => trim($this->formatQty($qty).' '.$jasa->satuan),
            'berat' => $this->resolveLegacyWeight($qty, $jasa->satuan),
            'tanggal' => $validated['tanggal_dimulai'],
            'estimasi_selesai' => $validated['ets_selesai'],
            'is_taken' => $isFinished,
            'tgl_selesai' => $isFinished ? ($laundry?->tgl_selesai?->format('Y-m-d') ?? now()->toDateString()) : null,
        ];
    }

    private function formatQty(float $qty): string
    {
        return rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
    }

    private function resolveLegacyWeight(float $qty, string $unit): float
    {
        return str_contains(strtolower($unit), 'kg') ? $qty : 0.0;
    }

    private function syncExistingPayment(Laundry $laundry): void
    {
        if (! $laundry->pembayaran || ! $laundry->jasa) {
            return;
        }

        $totalBiaya = (int) round(($laundry->qty ?? 0) * $laundry->jasa->harga);

        $laundry->pembayaran->update([
            'klien_id' => $laundry->klien_id,
            'total' => $totalBiaya,
            'total_biaya' => $totalBiaya,
        ]);

        if ($laundry->pembayaran->status !== 'sudah_bayar' && $laundry->pembayaran->gatewayHasSession()) {
            $laundry->pembayaran->clearGatewaySession();
        }
    }

    private function dispatchFinishedNotifications(Request $request, Laundry $laundry): ?string
    {
        $laundry->loadMissing('klien', 'jasa', 'toko');

        try {
            $request->user()?->notify(new LaundryFinishedNotification($laundry));
        } catch (Throwable $exception) {
            report($exception);

            return 'Status laundry tersimpan, tetapi notifikasi dashboard gagal dijadwalkan.';
        }

        if (! filled($laundry->klien?->email_klien)) {
            return 'Status laundry tersimpan. Email pelanggan belum dikirim karena alamat email pelanggan belum diisi.';
        }

        try {
            $laundry->klien->notify(new LaundryFinishedNotification($laundry));
        } catch (Throwable $exception) {
            report($exception);

            return 'Status laundry tersimpan, tetapi email notifikasi pelanggan gagal dijadwalkan.';
        }

        return null;
    }
}
