<?php

namespace App\Http\Controllers;

use App\Http\Requests\KlienRequest;
use App\Models\Klien;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KlienController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SORT_OPTIONS = [
        'nama_klien',
        'no_hp_klien',
        'total_order',
        'belum_bayar',
        'terakhir_order',
        'created_at',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola pelanggan.');
        }

        $perPage = $this->resolvePerPage($request);
        $sort = $this->resolveSort($request->string('sort')->toString(), 'terakhir_order');
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $activeSince = now()->subDays(30)->toDateString();

        $query = $this->buildIndexQuery($toko->id);

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('nama_klien', 'like', "%{$search}%")
                    ->orWhere('no_hp_klien', 'like', "%{$search}%");
            });
        }

        if ($status === 'aktif') {
            $query->where('terakhir_order', '>=', $activeSince);
        } elseif ($status === 'perlu_follow_up') {
            $query->where('belum_bayar', '>', 0);
        } elseif ($status === 'arsip') {
            $query->where(function (Builder $builder) use ($activeSince): void {
                $builder
                    ->whereNull('terakhir_order')
                    ->orWhere('terakhir_order', '<', $activeSince);
            })->where('belum_bayar', 0);
        }

        $summaryQuery = $this->buildIndexQuery($toko->id);

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'aktif' => (clone $summaryQuery)->where('terakhir_order', '>=', $activeSince)->count(),
            'perlu_follow_up' => (clone $summaryQuery)->where('belum_bayar', '>', 0)->count(),
        ];

        $this->applySorting($query, $sort, $direction);

        return view('modules.pelanggan', [
            'data' => $query->paginate($perPage)->withQueryString(),
            'summary' => $summary,
            'activeSinceLabel' => now()->subDays(30)->translatedFormat('d M Y'),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
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

    private function buildIndexQuery(int $tokoId): Builder
    {
        return Klien::query()
            ->where('toko_id', $tokoId)
            ->withCount([
                'laundries as total_order',
                'pembayarans as belum_bayar' => fn (Builder $builder) => $builder->where('status', 'belum_bayar'),
            ])
            ->withMax('laundries as terakhir_order', 'tanggal_dimulai');
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

    private function applySorting(Builder $query, string $sort, string $direction): void
    {
        $column = match ($sort) {
            'nama_klien' => 'nama_klien',
            'no_hp_klien' => 'no_hp_klien',
            'total_order' => 'total_order',
            'belum_bayar' => 'belum_bayar',
            'created_at' => 'created_at',
            default => 'terakhir_order',
        };

        $query->orderBy($column, $direction)->orderBy('nama_klien');
    }
}
