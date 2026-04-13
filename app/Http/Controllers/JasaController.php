<?php

namespace App\Http\Controllers;

use App\DataTables\JasaTable;
use App\Http\Requests\JasaRequest;
use App\Models\Jasa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JasaController extends Controller
{
    public function index(Request $request, JasaTable $table): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola biaya jasa.');
        }

        return view('modules.biaya-jasa', [
            'summary' => $table->summary($toko->id),
            'satuanOptions' => $table->satuanOptions($toko->id),
        ]);
    }

    public function data(Request $request, JasaTable $table): JsonResponse
    {
        abort_unless($request->user()?->toko, 403);

        return $table->data($request);
    }

    public function preview(Request $request, Jasa $jasa): View
    {
        $tokoId = $request->user()?->toko?->id;

        abort_unless($tokoId, 403);

        $jasa = Jasa::query()
            ->where('toko_id', $tokoId)
            ->whereKey($jasa->id)
            ->withCount(['laundries as total_order'])
            ->firstOrFail();

        return view('previews.jasa', compact('jasa'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum menambah biaya jasa.');
        }

        return view('modules.biaya-jasa-create');
    }

    public function store(JasaRequest $request): RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum menyimpan biaya jasa.');
        }

        $validated = $request->validated();

        $exists = Jasa::query()
            ->where('toko_id', $toko->id)
            ->where('nama_jasa', $validated['nama_jasa'])
            ->where('satuan', $validated['satuan'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'nama_jasa' => 'Kombinasi nama jasa dan satuan sudah ada.',
            ]);
        }

        $toko->jasas()->create($validated);

        return redirect()->route('biaya-jasa.index')->with('success', 'Biaya jasa berhasil ditambahkan.');
    }

    public function edit(Request $request, Jasa $jasa): View
    {
        abort_unless($request->user()?->toko?->id === $jasa->toko_id, 403);

        return view('modules.biaya-jasa-edit', compact('jasa'));
    }

    public function update(JasaRequest $request, Jasa $jasa): RedirectResponse
    {
        abort_unless($request->user()?->toko?->id === $jasa->toko_id, 403);

        $validated = $request->validated();

        $exists = Jasa::query()
            ->where('toko_id', $jasa->toko_id)
            ->where('nama_jasa', $validated['nama_jasa'])
            ->where('satuan', $validated['satuan'])
            ->where('id', '!=', $jasa->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'nama_jasa' => 'Kombinasi nama jasa dan satuan sudah ada.',
            ]);
        }

        $jasa->update($validated);

        $jasa->laundries()->get()->each(function ($laundry) use ($jasa): void {
            $qtyLabel = trim($laundry->formatted_qty.' '.$jasa->satuan);
            $totalBiaya = (int) round(($laundry->qty ?? 0) * $jasa->harga);

            $laundry->update([
                'jenis_jasa' => $jasa->nama_jasa,
                'layanan' => $jasa->nama_jasa,
                'satuan' => $qtyLabel,
            ]);

            if ($laundry->pembayaran) {
                $laundry->pembayaran->update([
                    'total' => $totalBiaya,
                    'total_biaya' => $totalBiaya,
                ]);
            }
        });

        return redirect()->route('biaya-jasa.index')->with('success', 'Biaya jasa berhasil diperbarui.');
    }

    public function destroy(Request $request, Jasa $jasa): RedirectResponse
    {
        abort_unless($request->user()?->toko?->id === $jasa->toko_id, 403);

        if ($jasa->laundries()->exists()) {
            return back()->with('warning', 'Jasa tidak bisa dihapus karena sudah dipakai oleh data laundry.');
        }

        $jasa->delete();

        return redirect()->route('biaya-jasa.index')->with('success', 'Biaya jasa berhasil dihapus.');
    }
}
