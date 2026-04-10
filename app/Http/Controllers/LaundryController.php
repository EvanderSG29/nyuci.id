<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryRequest;
use App\Http\Requests\LaundryStatusUpdateRequest;
use App\Models\Laundry;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LaundryController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SORT_OPTIONS = [
        'nama_klien',
        'no_hp_klien',
        'jasa',
        'qty',
        'tanggal_dimulai',
        'ets_selesai',
        'tgl_selesai',
        'status',
        'dibayar',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola laundry.');
        }

        $perPage = in_array($request->integer('per_page', 10), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page', 10)
            : 10;

        $sort = in_array($request->string('sort')->toString(), self::SORT_OPTIONS, true)
            ? $request->string('sort')->toString()
            : 'tanggal_dimulai';

        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $search = trim($request->string('search')->toString());
        $statusFilter = $request->string('status')->toString();
        $paymentFilter = $request->string('dibayar')->toString();
        $jasaFilter = $request->integer('jasa_id');

        $summaryQuery = Laundry::query()->where('toko_id', $toko->id);

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'belum_selesai' => (clone $summaryQuery)->where('status', 'belum_selesai')->count(),
            'proses' => (clone $summaryQuery)->where('status', 'proses')->count(),
            'selesai' => (clone $summaryQuery)->where('status', 'selesai')->count(),
        ];

        $query = Laundry::query()
            ->where('laundries.toko_id', $toko->id)
            ->with(['klien', 'jasa', 'pembayaran']);

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('laundries.nama', 'like', "%{$search}%")
                    ->orWhere('laundries.no_hp', 'like', "%{$search}%")
                    ->orWhere('laundries.jenis_jasa', 'like', "%{$search}%")
                    ->orWhere('laundries.satuan', 'like', "%{$search}%");
            });
        }

        if (in_array($statusFilter, ['belum_selesai', 'proses', 'selesai'], true)) {
            $query->where('laundries.status', $statusFilter);
        }

        if ($paymentFilter === 'sudah_bayar') {
            $query->whereHas('pembayaran', fn (Builder $builder) => $builder->where('status', 'sudah_bayar'));
        } elseif ($paymentFilter === 'belum_bayar') {
            $query->where(function (Builder $builder): void {
                $builder
                    ->whereDoesntHave('pembayaran')
                    ->orWhereHas('pembayaran', fn (Builder $relation) => $relation->where('status', 'belum_bayar'));
            });
        }

        if ($jasaFilter > 0) {
            $query->where('laundries.jasa_id', $jasaFilter);
        }

        $this->applySorting($query, $sort, $direction);

        $laundries = $query->paginate($perPage)->withQueryString();

        $statusModalLaundry = null;

        if (old('status_laundry_id')) {
            $statusModalLaundry = $laundries->getCollection()->firstWhere('id', (int) old('status_laundry_id'));
        }

        return view('laundry.index', [
            'data' => $laundries,
            'summary' => $summary,
            'jasaOptions' => $toko->jasas()->orderBy('nama_jasa')->get(),
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'dibayar' => $paymentFilter,
                'jasa_id' => $jasaFilter,
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'statusModalLaundry' => $statusModalLaundry,
        ]);
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
        $isFinished = $validated['status'] === 'selesai';
        $finishedDate = $isFinished ? $validated['tgl_selesai'] : null;

        if ($finishedDate !== null && $laundry->tanggal_dimulai && $finishedDate < $laundry->tanggal_dimulai->format('Y-m-d')) {
            return back()
                ->withErrors(['tgl_selesai' => 'Tanggal selesai tidak boleh lebih awal dari tanggal masuk.'])
                ->withInput();
        }

        $laundry->update([
            'status' => $validated['status'],
            'is_taken' => $isFinished,
            'tgl_selesai' => $finishedDate,
        ]);

        return back()->with('success', 'Status laundry berhasil diperbarui.');
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

    private function applySorting(Builder $query, string $sort, string $direction): void
    {
        if ($sort === 'dibayar') {
            $query
                ->leftJoin('pembayarans as pembayaran_sort', 'pembayaran_sort.laundry_id', '=', 'laundries.id')
                ->select('laundries.*')
                ->orderByRaw(
                    "case when pembayaran_sort.status = 'sudah_bayar' then 1 when pembayaran_sort.status = 'belum_bayar' then 0 else 0 end {$direction}"
                )
                ->orderBy('laundries.tanggal_dimulai', 'desc');

            return;
        }

        $column = match ($sort) {
            'nama_klien' => 'laundries.nama',
            'no_hp_klien' => 'laundries.no_hp',
            'jasa' => 'laundries.jenis_jasa',
            'qty' => 'laundries.qty',
            'ets_selesai' => 'laundries.ets_selesai',
            'tgl_selesai' => 'laundries.tgl_selesai',
            'status' => 'laundries.status',
            default => 'laundries.tanggal_dimulai',
        };

        $query
            ->orderBy($column, $direction)
            ->orderBy('laundries.id', 'desc');
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
}
