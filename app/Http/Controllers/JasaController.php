<?php

namespace App\Http\Controllers;

use App\Http\Requests\JasaRequest;
use App\Models\Jasa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JasaController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SORT_OPTIONS = [
        'nama_jasa',
        'satuan',
        'harga',
        'total_order',
        'created_at',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola biaya jasa.');
        }

        $perPage = $this->resolvePerPage($request);
        $sort = $this->resolveSort($request->string('sort')->toString(), 'created_at');
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $search = trim($request->string('search')->toString());
        $kategori = $request->string('kategori')->toString();

        $query = Jasa::query()
            ->where('toko_id', $toko->id)
            ->withCount(['laundries as total_order']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('nama_jasa', 'like', "%{$search}%")
                    ->orWhere('satuan', 'like', "%{$search}%");
            });
        }

        if ($kategori === 'kiloan') {
            $query->where('satuan', 'like', '%kg%');
        } elseif ($kategori === 'per_unit') {
            $query->where('satuan', 'not like', '%kg%');
        }

        $summaryQuery = Jasa::query()->where('toko_id', $toko->id);

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'kiloan' => (clone $summaryQuery)->where('satuan', 'like', '%kg%')->count(),
            'per_unit' => (clone $summaryQuery)->where('satuan', 'not like', '%kg%')->count(),
        ];

        $this->applySorting($query, $sort, $direction);

        return view('modules.biaya-jasa', [
            'data' => $query->paginate($perPage)->withQueryString(),
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'kategori' => $kategori,
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
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

    private function resolvePerPage(Request $request): int
    {
        return in_array($request->integer('per_page', 10), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page', 10)
            : 10;
    }

    private function resolveSort(string $sort, string $default): string
    {
        return in_array($sort, self::SORT_OPTIONS, true) ? $sort : $default;
    }

    private function applySorting($query, string $sort, string $direction): void
    {
        $column = match ($sort) {
            'nama_jasa' => 'nama_jasa',
            'satuan' => 'satuan',
            'harga' => 'harga',
            'total_order' => 'total_order',
            default => 'created_at',
        };

        $query->orderBy($column, $direction)->orderBy('id');
    }
}
