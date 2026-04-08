<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembayaranRequest;
use App\Models\Laundry;
use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SORT_OPTIONS = [
        'nama_klien',
        'no_hp_klien',
        'total',
        'metode_pembayaran',
        'tgl_pembayaran',
        'status',
        'created_at',
    ];

    private const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'qris' => 'QRIS',
        'transfer' => 'Transfer',
        'ewallet' => 'E-Wallet',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola pembayaran.');
        }

        $perPage = in_array($request->integer('per_page', 10), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page', 10)
            : 10;

        $sort = in_array($request->string('sort')->toString(), self::SORT_OPTIONS, true)
            ? $request->string('sort')->toString()
            : 'tgl_pembayaran';

        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $search = trim($request->string('search')->toString());
        $statusFilter = $request->string('status')->toString();
        $methodFilter = $request->string('metode_pembayaran')->toString();

        $summary = [
            'total' => Pembayaran::query()
                ->whereHas('laundry', fn (Builder $builder) => $builder->where('toko_id', $toko->id))
                ->count(),
            'sudah_bayar' => Pembayaran::query()
                ->whereHas('laundry', fn (Builder $builder) => $builder->where('toko_id', $toko->id))
                ->where('status', 'sudah_bayar')
                ->count(),
            'belum_bayar' => $this->unpaidLaundryQuery($toko->id)->count(),
        ];

        $query = Pembayaran::query()
            ->select('pembayarans.*')
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->where('laundries.toko_id', $toko->id)
            ->with(['laundry.klien', 'laundry.jasa', 'klien']);

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('laundries.nama', 'like', "%{$search}%")
                    ->orWhere('laundries.no_hp', 'like', "%{$search}%")
                    ->orWhere('pembayarans.metode_pembayaran', 'like', "%{$search}%")
                    ->orWhere('pembayarans.catatan', 'like', "%{$search}%")
                    ->orWhere('pembayarans.total_biaya', 'like', "%{$search}%")
                    ->orWhere('pembayarans.total', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'belum_bayar' || $statusFilter === 'sudah_bayar') {
            $query->where('pembayarans.status', $statusFilter);
        }

        if ($methodFilter !== '') {
            $query->where('pembayarans.metode_pembayaran', $methodFilter);
        }

        $this->applyPaymentSorting($query, $sort, $direction);

        return view('pembayaran.index', [
            'data' => $query->paginate($perPage)->withQueryString(),
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'metode_pembayaran' => $methodFilter,
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    public function unpaid(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola pembayaran.');
        }

        $perPage = in_array($request->integer('per_page', 10), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page', 10)
            : 10;

        $sort = in_array($request->string('sort')->toString(), ['nama', 'no_hp', 'jenis_jasa', 'satuan', 'tanggal', 'estimasi_selesai'], true)
            ? $request->string('sort')->toString()
            : 'tanggal';

        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $search = trim($request->string('search')->toString());
        $statusFilter = $request->string('status')->toString();

        $query = $this->unpaidLaundryQuery($toko->id)
            ->with(['pembayaran', 'klien', 'jasa']);

        $summary = [
            'total' => (clone $query)->count(),
            'belum_selesai' => (clone $query)->where('laundries.status', 'belum_selesai')->count(),
            'selesai' => (clone $query)->where('laundries.status', 'selesai')->count(),
        ];

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('laundries.nama', 'like', "%{$search}%")
                    ->orWhere('laundries.no_hp', 'like', "%{$search}%")
                    ->orWhere('laundries.jenis_jasa', 'like', "%{$search}%")
                    ->orWhere('laundries.satuan', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'belum_selesai') {
            $query->where('laundries.status', 'belum_selesai');
        } elseif ($statusFilter === 'selesai') {
            $query->where('laundries.status', 'selesai');
        }

        $this->applyUnpaidSorting($query, $sort, $direction);

        return view('pembayaran.unpaid', [
            'data' => $query->paginate($perPage)->withQueryString(),
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum membuat pembayaran.');
        }

        $laundries = Laundry::query()
            ->where('toko_id', $toko->id)
            ->doesntHave('pembayaran')
            ->with(['klien', 'jasa'])
            ->orderByDesc('tanggal_dimulai')
            ->get();

        $selectedLaundryId = $request->integer('laundry_id');
        $selectedLaundry = $selectedLaundryId
            ? $laundries->firstWhere('id', $selectedLaundryId)
            : null;

        return view('pembayaran.create', [
            'laundries' => $laundries,
            'selectedLaundry' => $selectedLaundry,
            'selectedLaundryId' => $selectedLaundry?->id,
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    public function store(PembayaranRequest $request): RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum menyimpan pembayaran.');
        }

        $validated = $request->validated();
        $laundry = Laundry::query()
            ->where('toko_id', $toko->id)
            ->whereKey($validated['laundry_id'])
            ->doesntHave('pembayaran')
            ->with(['klien', 'jasa'])
            ->first();

        if (! $laundry) {
            return back()->withErrors(['laundry_id' => 'Laundry yang dipilih tidak valid.'])->withInput();
        }

        if ($laundry->pembayaran()->exists()) {
            return back()->withErrors(['laundry_id' => 'Laundry ini sudah memiliki data pembayaran.'])->withInput();
        }

        Pembayaran::create($this->buildPaymentPayload($laundry, $validated));

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil disimpan.');
    }

    public function show(Pembayaran $pembayaran): View
    {
        $this->authorize('view', $pembayaran);

        return view('pembayaran.show', ['pembayaran' => $pembayaran->load('laundry.toko', 'laundry.klien', 'laundry.jasa', 'klien')]);
    }

    public function edit(Pembayaran $pembayaran): View
    {
        $this->authorize('update', $pembayaran);

        return view('pembayaran.edit', [
            'pembayaran' => $pembayaran->load('laundry.klien', 'laundry.jasa'),
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    public function update(PembayaranRequest $request, Pembayaran $pembayaran): RedirectResponse
    {
        $this->authorize('update', $pembayaran);

        $validated = $request->validated();
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum memperbarui pembayaran.');
        }

        $laundry = Laundry::query()
            ->where('toko_id', $toko->id)
            ->whereKey($validated['laundry_id'])
            ->with(['klien', 'jasa'])
            ->first();

        if (! $laundry) {
            return back()->withErrors(['laundry_id' => 'Laundry yang dipilih tidak valid.'])->withInput();
        }

        if ($laundry->id !== $pembayaran->laundry_id && $laundry->pembayaran()->exists()) {
            return back()->withErrors(['laundry_id' => 'Laundry ini sudah memiliki data pembayaran.'])->withInput();
        }

        $pembayaran->update($this->buildPaymentPayload($laundry, $validated));

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(Pembayaran $pembayaran): RedirectResponse
    {
        $this->authorize('delete', $pembayaran);
        $pembayaran->delete();

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function markAsPaid(Pembayaran $pembayaran): RedirectResponse
    {
        $this->authorize('update', $pembayaran);

        $pembayaran->update([
            'status' => 'sudah_bayar',
            'tgl_pembayaran' => $pembayaran->tgl_pembayaran ?? now()->toDateString(),
        ]);

        return back()->with('success', 'Status pembayaran diperbarui.');
    }

    private function unpaidLaundryQuery(int $tokoId): Builder
    {
        return Laundry::query()
            ->where('toko_id', $tokoId)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereDoesntHave('pembayaran')
                    ->orWhereHas('pembayaran', fn (Builder $relation) => $relation->where('status', 'belum_bayar'));
            });
    }

    private function applyPaymentSorting(Builder $query, string $sort, string $direction): void
    {
        $column = match ($sort) {
            'nama_klien' => 'laundries.nama',
            'no_hp_klien' => 'laundries.no_hp',
            'total' => 'pembayarans.total_biaya',
            'metode_pembayaran' => 'pembayarans.metode_pembayaran',
            'status' => 'pembayarans.status',
            'created_at' => 'pembayarans.created_at',
            default => 'pembayarans.tgl_pembayaran',
        };

        $query
            ->orderBy($column, $direction)
            ->orderBy('pembayarans.id', 'desc');
    }

    private function applyUnpaidSorting(Builder $query, string $sort, string $direction): void
    {
        $column = match ($sort) {
            'nama' => 'laundries.nama',
            'no_hp' => 'laundries.no_hp',
            'jenis_jasa' => 'laundries.jenis_jasa',
            'satuan' => 'laundries.satuan',
            'estimasi_selesai' => 'laundries.ets_selesai',
            default => 'laundries.tanggal_dimulai',
        };

        $query
            ->orderBy($column, $direction)
            ->orderBy('laundries.id', 'desc');
    }

    private function buildPaymentPayload(Laundry $laundry, array $validated): array
    {
        if (! $laundry->klien || ! $laundry->jasa) {
            throw ValidationException::withMessages([
                'laundry_id' => 'Laundry belum memiliki pelanggan atau jasa yang valid.',
            ]);
        }

        $totalBiaya = (int) round(($laundry->qty ?? 0) * $laundry->jasa->harga);

        return [
            'klien_id' => $laundry->klien_id,
            'laundry_id' => $laundry->id,
            'total' => $totalBiaya,
            'total_biaya' => $totalBiaya,
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'tgl_pembayaran' => $validated['tgl_pembayaran'],
            'catatan' => $validated['catatan'] ?: null,
            'status' => $validated['status'],
        ];
    }
}
