<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Services\PaymentGateway\StaticQrisGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PembayaranGatewayController extends Controller
{
    private const CHECKOUT_WINDOW_NAME = 'nyuci-qris-checkout';

    public function issue(Request $request, Pembayaran $pembayaran, StaticQrisGateway $gateway): RedirectResponse
    {
        $this->authorize('update', $pembayaran);

        if ($pembayaran->status === 'sudah_bayar') {
            return back()->with('warning', 'Pembayaran ini sudah lunas.');
        }

        if ($pembayaran->metode_pembayaran !== 'qris') {
            $pembayaran->update(['metode_pembayaran' => 'qris']);
        }

        try {
            $session = $gateway->issue($pembayaran->fresh());
        } catch (Throwable $e) {
            return back()->with('warning', $e->getMessage());
        }

        if ($session['created'] ?? false) {
            $pembayaran->setGatewaySession($session);
        }

        $checkoutUrl = route('pembayaran.gateway.checkout', [
            'pembayaran' => $pembayaran->id,
            'token' => $pembayaran->gateway_token ?? $session['token'] ?? '',
        ]);

        return redirect()
            ->route('pembayaran.show', $pembayaran)
            ->with([
                'success' => ($session['created'] ?? false)
                    ? 'Sesi QRIS berhasil dibuat dan dibuka di tab baru.'
                    : 'Sesi QRIS aktif dibuka di tab baru.',
                'open_new_tab_url' => $checkoutUrl,
                'open_new_tab_name' => (string) config('payment_gateway.checkout_window_name', self::CHECKOUT_WINDOW_NAME),
            ]);
    }

    public function checkout(Pembayaran $pembayaran, string $token, StaticQrisGateway $gateway): View
    {
        $this->assertTokenMatches($pembayaran, $token);

        return view('pembayaran.gateway', [
            'pembayaran' => $pembayaran->loadMissing('laundry.toko', 'laundry.klien', 'laundry.jasa', 'klien'),
            'gateway' => $gateway->snapshot($pembayaran),
        ]);
    }

    public function sync(Request $request, Pembayaran $pembayaran, string $token, StaticQrisGateway $gateway): RedirectResponse
    {
        $this->assertTokenMatches($pembayaran, $token);

        try {
            $result = $gateway->check($pembayaran->fresh());
        } catch (Throwable $e) {
            if ($request->expectsJson()) {
                return back()->with('warning', $e->getMessage());
            }

            return back()->with('warning', $e->getMessage());
        }

        $pembayaran->syncGatewayPayment($result);

        if ($request->expectsJson()) {
            return back()->with([
                'gateway_status' => $result['status'],
                'gateway_message' => $result['message'] ?? 'Status pembayaran diperbarui.',
            ]);
        }

        return back()->with(
            $result['status'] === 'paid' ? 'success' : 'warning',
            $result['message'] ?? 'Status pembayaran diperbarui.'
        );
    }

    private function assertTokenMatches(Pembayaran $pembayaran, string $token): void
    {
        if ($pembayaran->gateway_token === null || ! hash_equals($pembayaran->gateway_token, $token)) {
            abort(404);
        }
    }
}
