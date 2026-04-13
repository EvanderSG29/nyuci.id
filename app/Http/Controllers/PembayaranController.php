<?php

namespace App\Http\Controllers;

use App\DataTables\PembayaranTable as PembayaranDataTable;
use App\DataTables\UnpaidLaundryTable;
use App\Http\Requests\PembayaranRequest;
use App\Models\Laundry;
use App\Models\Pembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    private const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'qris' => 'QRIS',
        'transfer' => 'Transfer',
        'ewallet' => 'E-Wallet',
    ];

    public function index(Request $request, PembayaranDataTable $table): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola pembayaran.');
        }

        return view('pembayaran.index', [
            'summary' => $table->summary($toko->id),
            'statusOptions' => $table->statusOptions($toko->id),
            'paymentMethodOptions' => $table->paymentMethodOptions($toko->id),
        ]);
    }

    public function data(Request $request, PembayaranDataTable $table): JsonResponse
    {
        abort_unless($request->user()?->toko, 403);

        return $table->data($request);
    }

    public function unpaid(Request $request, UnpaidLaundryTable $table): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola pembayaran.');
        }

        return view('pembayaran.unpaid', [
            'summary' => $table->summary($toko->id),
            'statusOptions' => $table->statusOptions($toko->id),
        ]);
    }

    public function unpaidData(Request $request, UnpaidLaundryTable $table): JsonResponse
    {
        abort_unless($request->user()?->toko, 403);

        return $table->data($request);
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

        if ($validated['status'] === 'sudah_bayar') {
            $pembayaran->forceFill([
                'gateway_status' => 'paid',
                'gateway_paid_at' => $pembayaran->gateway_paid_at ?? now(),
            ])->save();
        } elseif ($pembayaran->gatewayHasSession()) {
            $pembayaran->clearGatewaySession();
        } else {
            $pembayaran->forceFill([
                'gateway_status' => null,
                'gateway_paid_at' => null,
            ])->save();
        }

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

        $pembayaran->forceFill([
            'gateway_status' => 'paid',
            'gateway_paid_at' => $pembayaran->gateway_paid_at ?? now(),
        ])->save();

        return back()->with('success', 'Status pembayaran diperbarui.');
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
