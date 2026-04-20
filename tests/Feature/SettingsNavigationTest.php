<?php

use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createSettingsOwner(): User
{
    $user = User::factory()->create();

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Nyuci Settings',
        'alamat' => 'Jl. Settings No. 1',
        'no_hp' => '081200000111',
    ]);

    return $user->fresh();
}

test('all unified settings sections render from the new shell', function () {
    $user = createSettingsOwner();

    foreach ([
        'settings.profile' => 'Informasi Akun',
        'settings.toko' => 'Identitas Toko',
        'settings.personalisasi' => 'Mode Tampilan',
        'settings.payment.qris' => 'QRIS Pembayaran',
        'settings.payment.methods' => 'Metode pembayaran selain QRIS',
        'settings.dashboard' => 'Dashboard chart preset',
    ] as $route => $expectedText) {
        $this
            ->actingAs($user)
            ->get(route($route))
            ->assertOk()
            ->assertSee('Pengaturan Utama')
            ->assertSee($expectedText);
    }
});

test('legacy settings get routes redirect to unified settings sections', function () {
    $user = createSettingsOwner();

    $this
        ->actingAs($user)
        ->get(route('profile.edit'))
        ->assertRedirect(route('settings.profile'));

    $this
        ->actingAs($user)
        ->get(route('pengaturan-toko.edit'))
        ->assertRedirect(route('settings.toko'));

    $this
        ->actingAs($user)
        ->get(route('pengaturan-dashboard.edit'))
        ->assertRedirect(route('settings.dashboard'));
});

test('dashboard chrome and sidebar expose settings entry points', function () {
    $user = createSettingsOwner();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('aria-label="Buka pengaturan"', false)
        ->assertSee(route('settings.profile'), false)
        ->assertSee('Tutup sesi akun ini');
});

test('payment parent navigation stays active for qris and other payment methods subsections', function () {
    $user = createSettingsOwner();

    $this
        ->actingAs($user)
        ->get(route('settings.payment.qris'))
        ->assertOk()
        ->assertSee('class="nyuci-settings-nav-parent is-active"', false)
        ->assertSee('class="nyuci-settings-nav-link is-child is-active"', false);

    $this
        ->actingAs($user)
        ->get(route('settings.payment.methods'))
        ->assertOk()
        ->assertSee('class="nyuci-settings-nav-parent is-active"', false)
        ->assertSee('class="nyuci-settings-nav-link is-child is-active"', false);
});
