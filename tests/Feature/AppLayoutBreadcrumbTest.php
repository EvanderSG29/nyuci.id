<?php

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function extractBreadcrumbMarkup(string $content): string
{
    preg_match('/data-page-breadcrumbs[\s\S]*?<nav class="nyuci-breadcrumbs" aria-label="Breadcrumb">([\s\S]*?)<\/nav>/i', $content, $matches);

    return $matches[0] ?? '';
}

function createAppUserWithStore(): User
{
    $user = User::factory()->create();

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Nyuci Breadcrumb',
        'alamat' => 'Jl. Breadcrumb No. 1',
        'no_hp' => '081200000123',
    ]);

    return $user->fresh();
}

function createPaymentRecordForStore(Toko $toko): Pembayaran
{
    $jasa = Jasa::create([
        'toko_id' => $toko->id,
        'nama_jasa' => 'cuci_komplit',
        'satuan' => 'kg',
        'harga' => 9000,
    ]);

    $klien = Klien::create([
        'toko_id' => $toko->id,
        'nama_klien' => 'Pelanggan Breadcrumb',
        'alamat_klien' => 'Jl. Klien',
        'no_hp_klien' => '081100000456',
    ]);

    $laundry = Laundry::create([
        'toko_id' => $toko->id,
        'klien_id' => $klien->id,
        'jasa_id' => $jasa->id,
        'qty' => 2,
        'status' => 'proses',
        'tanggal_dimulai' => '2026-04-16',
        'ets_selesai' => '2026-04-17',
        'nama' => $klien->nama_klien,
        'no_hp' => $klien->no_hp_klien,
        'berat' => 2,
        'satuan' => '2 kg',
        'tanggal' => '2026-04-16',
        'layanan' => $jasa->nama_jasa,
        'jenis_jasa' => $jasa->nama_jasa,
        'estimasi_selesai' => '2026-04-17',
        'is_taken' => false,
    ]);

    return Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 18000,
        'total_biaya' => 18000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => '2026-04-16',
        'status' => 'sudah_bayar',
    ]);
}

test('non-dashboard index pages render one breadcrumb strip inside main and do not duplicate the active label', function () {
    $user = createAppUserWithStore();

    foreach ([
        'biaya-jasa.index' => 'Biaya Jasa',
        'pelanggan.index' => 'Pelanggan',
        'laundry.index' => 'Laundry',
        'pembayaran.index' => 'Pembayaran',
    ] as $route => $label) {
        $response = $this
            ->actingAs($user)
            ->get(route($route));

        $response
            ->assertOk()
            ->assertSee('Cari laundry, pelanggan, pembayaran...');

        $content = $response->getContent();
        $breadcrumb = extractBreadcrumbMarkup($content);

        expect(substr_count($content, 'data-page-breadcrumbs'))->toBe(1);
        expect($content)->toMatch('/data-flux-main[\s\S]*data-page-breadcrumbs/');
        expect($breadcrumb)->toContain('Beranda');
        expect($breadcrumb)->toContain($label);
        expect(substr_count($breadcrumb, $label))->toBe(1);
    }
});

test('payment detail keeps action buttons and renders breadcrumb box once', function () {
    $user = createAppUserWithStore();
    $payment = createPaymentRecordForStore($user->toko);

    $response = $this
        ->actingAs($user)
        ->get(route('pembayaran.show', $payment));

    $response
        ->assertOk()
        ->assertSee('Cari laundry, pelanggan, pembayaran...')
        ->assertSee('Cetak')
        ->assertSee('Edit Pembayaran');

    $content = $response->getContent();
    $breadcrumb = extractBreadcrumbMarkup($content);

    expect(substr_count($content, 'data-page-breadcrumbs'))->toBe(1);
    expect($content)->toMatch('/data-flux-main[\s\S]*data-page-breadcrumbs/');
    expect($breadcrumb)->toContain('Beranda');
    expect($breadcrumb)->toContain('Pembayaran');
    expect($breadcrumb)->toContain('Detail Pembayaran');
    expect(substr_count($breadcrumb, 'Detail Pembayaran'))->toBe(1);
});

test('dashboard does not render the separated breadcrumb box', function () {
    $user = createAppUserWithStore();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();

    expect(substr_count($response->getContent(), 'data-page-breadcrumbs'))->toBe(0);
});

test('guest login page does not render the separated breadcrumb box', function () {
    $response = $this->get(route('login'));

    $response->assertOk();

    expect(substr_count($response->getContent(), 'data-page-breadcrumbs'))->toBe(0);
});
