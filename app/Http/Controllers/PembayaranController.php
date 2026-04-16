<?php

namespace App\Http\Controllers;

use App\DataTables\PembayaranTable as PembayaranDataTable;
use App\DataTables\UnpaidLaundryTable;
use App\Http\Requests\PembayaranRequest;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Services\PaymentGateway\StaticQrisGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PembayaranController extends Controller
{
    private const CHECKOUT_WINDOW_NAME = 'nyuci-qris-checkout';

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

    public function preview(Pembayaran $pembayaran): View
    {
        $this->authorize('view', $pembayaran);

        return view('previews.pembayaran', [
            'pembayaran' => $pembayaran->load('laundry.toko', 'laundry.klien', 'laundry.jasa', 'klien'),
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
            'checkoutTabName' => $this->checkoutWindowName(),
        ]);
    }

    public function store(PembayaranRequest $request, StaticQrisGateway $gateway): RedirectResponse
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

        $pembayaran = Pembayaran::create($this->buildPaymentPayload($laundry, $validated));

        return $this->finalizePaymentFlow($pembayaran, $validated, $gateway, 'Pembayaran berhasil disimpan.');
    }

    public function show(Pembayaran $pembayaran): View
    {
        $this->authorize('view', $pembayaran);

        return view('pembayaran.show', [
            'pembayaran' => $pembayaran->load('laundry.toko', 'laundry.klien', 'laundry.jasa', 'klien'),
            'checkoutTabName' => $this->checkoutWindowName(),
        ]);
    }

    public function edit(Pembayaran $pembayaran): View
    {
        $this->authorize('update', $pembayaran);

        return view('pembayaran.edit', [
            'pembayaran' => $pembayaran->load('laundry.klien', 'laundry.jasa'),
            'paymentMethods' => self::PAYMENT_METHODS,
            'checkoutTabName' => $this->checkoutWindowName(),
        ]);
    }

    public function update(PembayaranRequest $request, Pembayaran $pembayaran, StaticQrisGateway $gateway): RedirectResponse
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

        return $this->finalizePaymentFlow($pembayaran, $validated, $gateway, 'Pembayaran berhasil diperbarui.');
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

        if ($pembayaran->metode_pembayaran === 'qris') {
            $pembayaran->forceFill([
                'gateway_status' => 'paid',
                'gateway_paid_at' => $pembayaran->gateway_paid_at ?? now(),
            ])->save();
        } else {
            $pembayaran->clearGatewaySession();
        }

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
            'tgl_pembayaran' => $validated['status'] === 'sudah_bayar'
                ? ($validated['tgl_pembayaran'] ?? now()->toDateString())
                : null,
            'catatan' => $validated['catatan'] ?: null,
            'status' => $validated['status'],
        ];
    }

    private function finalizePaymentFlow(Pembayaran $pembayaran, array $validated, StaticQrisGateway $gateway, string $successMessage): RedirectResponse
    {
        $pembayaran->refresh();

        if ($validated['metode_pembayaran'] === 'qris') {
            if ($validated['status'] === 'sudah_bayar') {
                $pembayaran->forceFill([
                    'gateway_status' => 'paid',
                    'gateway_paid_at' => $pembayaran->gateway_paid_at ?? now(),
                    'tgl_pembayaran' => $pembayaran->tgl_pembayaran ?? now()->toDateString(),
                ])->save();

                return redirect()->route('pembayaran.index')->with('success', $successMessage);
            }

            return $this->redirectToGatewayCheckout($pembayaran, $gateway);
        }

        if ($pembayaran->gateway_token || $pembayaran->gateway_status !== null || $pembayaran->gateway_paid_at !== null || $pembayaran->gateway_payload !== null) {
            $pembayaran->clearGatewaySession();
        }

        return redirect()->route('pembayaran.index')->with('success', $successMessage);
    }

    private function redirectToGatewayCheckout(Pembayaran $pembayaran, StaticQrisGateway $gateway): RedirectResponse
    {
        try {
            $session = $gateway->issue($pembayaran->fresh());
        } catch (Throwable $e) {
            return redirect()
                ->route('pembayaran.edit', $pembayaran)
                ->with('warning', $e->getMessage());
        }

        if ($session['created'] ?? false) {
            $pembayaran->setGatewaySession($session);
            $pembayaran->refresh();
        }

        $checkoutUrl = route('pembayaran.gateway.checkout', [
            'pembayaran' => $pembayaran->id,
            'token' => $pembayaran->gateway_token ?? $session['token'] ?? '',
        ]);

        return $this->redirectWithCheckoutTab(
            $pembayaran,
            $checkoutUrl,
            ($session['created'] ?? false)
                ? 'Sesi QRIS berhasil dibuat dan dibuka di tab baru.'
                : 'Sesi QRIS aktif dibuka di tab baru.'
        );
    }

    private function redirectWithCheckoutTab(Pembayaran $pembayaran, string $checkoutUrl, string $successMessage): RedirectResponse
    {
        return redirect()
            ->route('pembayaran.show', $pembayaran)
            ->with([
                'success' => $successMessage,
                'open_new_tab_url' => $checkoutUrl,
                'open_new_tab_name' => $this->checkoutWindowName(),
            ]);
    }

    private function checkoutWindowName(): string
    {
        return (string) config('payment_gateway.checkout_window_name', self::CHECKOUT_WINDOW_NAME);
    }
}
