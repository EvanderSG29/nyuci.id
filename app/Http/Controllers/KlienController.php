<?php

namespace App\Http\Controllers;

use App\Http\Requests\KlienRequest;
use App\Models\Klien;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KlienController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola pelanggan.');
        }

        return view('modules.pelanggan');
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum menambah pelanggan.');
        }

        return view('modules.pelanggan-create');
    }

    public function store(KlienRequest $request): RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum menyimpan pelanggan.');
        }

        $validated = $request->validated();

        $exists = Klien::query()
            ->where('toko_id', $toko->id)
            ->where('no_hp_klien', $validated['no_hp_klien'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'no_hp_klien' => 'Nomor HP pelanggan sudah terdaftar.',
            ]);
        }

        $toko->kliens()->create($validated);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Request $request, Klien $klien): View
    {
        abort_unless($request->user()?->toko?->id === $klien->toko_id, 403);

        return view('modules.pelanggan-edit', compact('klien'));
    }

    public function update(KlienRequest $request, Klien $klien): RedirectResponse
    {
        abort_unless($request->user()?->toko?->id === $klien->toko_id, 403);

        $validated = $request->validated();

        $exists = Klien::query()
            ->where('toko_id', $klien->toko_id)
            ->where('no_hp_klien', $validated['no_hp_klien'])
            ->where('id', '!=', $klien->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'no_hp_klien' => 'Nomor HP pelanggan sudah terdaftar.',
            ]);
        }

        $klien->update($validated);

        $klien->laundries()->update([
            'nama' => $validated['nama_klien'],
            'no_hp' => $validated['no_hp_klien'],
        ]);

        $klien->pembayarans()->update([
            'klien_id' => $klien->id,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Request $request, Klien $klien): RedirectResponse
    {
        abort_unless($request->user()?->toko?->id === $klien->toko_id, 403);

        if ($klien->laundries()->exists()) {
            return back()->with('warning', 'Pelanggan tidak bisa dihapus karena sudah dipakai oleh data laundry.');
        }

        $klien->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
    }

}
