<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentQrisSettingsUpdateRequest;
use App\Http\Requests\StoreIdentityUpdateRequest;
use App\Services\DashboardCharts\DashboardChartConfigResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): RedirectResponse
    {
        return Redirect::route('settings.profile');
    }

    public function profile(Request $request): View
    {
        return $this->renderPage('profile', [
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): View
    {
        return $this->renderPage('store', [
            'user' => $request->user(),
        ]);
    }

    public function updateStoreIdentity(StoreIdentityUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->toko()->updateOrCreate(
            [],
            [
                'nama_toko' => $validated['nama_toko'],
                'alamat' => $validated['alamat'] ?: null,
                'no_hp' => $validated['no_hp'] ?: null,
            ]
        );

        return Redirect::route('settings.toko')->with('status', 'store-identity-updated');
    }

    public function personalization(Request $request): View
    {
        return $this->renderPage('personalization', [
            'user' => $request->user(),
        ]);
    }

    public function paymentQris(Request $request): View
    {
        return $this->renderPage('payment-qris', [
            'user' => $request->user(),
            'store' => $request->user()->toko,
        ]);
    }

    public function updatePaymentQris(PaymentQrisSettingsUpdateRequest $request): RedirectResponse
    {
        $store = $request->user()->toko;

        abort_unless($store, 403);

        $validated = $request->validated();

        $store->update([
            'payment_gateway_qris_payload' => $validated['payment_gateway_qris_payload'] ?? null,
            'payment_gateway_qris_merchant_name' => $validated['payment_gateway_qris_merchant_name'] ?? null,
            'payment_gateway_checkout_ttl_minutes' => $validated['payment_gateway_checkout_ttl_minutes'] ?? null,
        ]);

        return Redirect::route('settings.payment.qris')->with('status', 'payment-qris-updated');
    }

    public function paymentMethods(Request $request): View
    {
        return $this->renderPage('payment-methods', [
            'user' => $request->user(),
        ]);
    }

    public function dashboard(Request $request, DashboardChartConfigResolver $resolver): View
    {
        $user = $request->user();
        $store = $user->toko;

        return $this->renderPage('dashboard', [
            'user' => $user,
            'hasStore' => $store !== null,
            'chartSettings' => $store ? $resolver->settingsPayload($store, $user) : null,
        ]);
    }

    private function renderPage(string $section, array $data = []): View
    {
        $titles = [
            'profile' => [
                'title' => 'Profil',
                'description' => 'Kelola akun, keamanan, dan akses pribadi Anda ke Nyuci.id.',
            ],
            'store' => [
                'title' => 'Toko',
                'description' => 'Atur identitas toko yang tampil di dashboard, invoice, dan modul operasional.',
            ],
            'personalization' => [
                'title' => 'Personalisasi',
                'description' => 'Sesuaikan mode tampilan aplikasi agar nyaman dipakai sepanjang hari kerja.',
            ],
            'payment-qris' => [
                'title' => 'Pembayaran QRIS',
                'description' => 'Atur payload QRIS, nama merchant, dan durasi checkout untuk pembayaran digital.',
            ],
            'payment-methods' => [
                'title' => 'Metode Lainnya',
                'description' => 'Siapkan area pengaturan untuk metode pembayaran selain QRIS.',
            ],
            'dashboard' => [
                'title' => 'Dashboard',
                'description' => 'Kontrol default toko dan preferensi pribadi untuk kartu serta chart dashboard.',
            ],
        ];

        abort_unless(isset($titles[$section]), 404);

        return view('settings.show', array_merge($data, [
            'settingsSection' => $section,
            'settingsTitle' => $titles[$section]['title'],
            'settingsDescription' => $titles[$section]['description'],
            'settingsSaveSucceeded' => in_array(session('status'), [
                'profile-updated',
                'password-updated',
                'store-settings-updated',
                'store-identity-updated',
                'payment-qris-updated',
                'dashboard-chart-defaults-updated',
                'dashboard-chart-overrides-updated',
                'dashboard-chart-default-reset',
                'dashboard-chart-override-deleted',
            ], true),
        ]));
    }
}
