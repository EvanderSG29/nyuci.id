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

function extractSubpageNavbarMarkup(string $content): string
{
    preg_match('/<header[^>]*nyuci-subpage-navbar[^>]*>[\s\S]*?<\/header>/i', $content, $matches);

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
        $navbar = extractSubpageNavbarMarkup($content);

        expect(substr_count($content, 'data-page-breadcrumbs'))->toBe(1);
        expect($content)->toMatch('/data-flux-main[\s\S]*data-page-breadcrumbs/');
        expect($content)->toContain('class="nyuci-subpage-title text-[var(--text-strong)]"');
        expect($breadcrumb)->toContain('Beranda');
        expect($breadcrumb)->toContain($label);
        expect(substr_count($breadcrumb, $label))->toBe(1);
        expect($navbar)->not->toContain('Nyuci Breadcrumb');
    }
});

test('settings profile keeps navbar compact and excludes body intro copy', function () {
    $user = createAppUserWithStore();

    $response = $this
        ->actingAs($user)
        ->get(route('settings.profile'));

    $response
        ->assertOk()
        ->assertSee('Pengaturan utama')
        ->assertSee('Profil')
        ->assertSee('Subsection aktif');

    $navbar = extractSubpageNavbarMarkup($response->getContent());

    expect($navbar)->toContain('Pengaturan utama');
    expect($navbar)->toContain('Profil');
    expect($navbar)->not->toContain('Nyuci Breadcrumb');
    expect($navbar)->not->toContain('Subsection aktif');
    expect($navbar)->not->toContain('Susun akun, toko, pembayaran, dan dashboard dalam satu area kerja yang rapi.');
});

test('payment edit moves subtitle into page intro and keeps navbar compact', function () {
    $user = createAppUserWithStore();
    $payment = createPaymentRecordForStore($user->toko);

    $response = $this
        ->actingAs($user)
        ->get(route('pembayaran.edit', $payment));

    $response
        ->assertOk()
        ->assertSee('Perbarui metode, tanggal, catatan, atau status pembayaran untuk order yang sama.');

    $content = $response->getContent();
    $navbar = extractSubpageNavbarMarkup($content);

    expect($navbar)->toContain('Transaksi pembayaran');
    expect($navbar)->toContain('Edit Pembayaran');
    expect($navbar)->not->toContain('Perbarui metode, tanggal, catatan, atau status pembayaran untuk order yang sama.');
    expect($content)->toContain('data-page-intro');
    expect($content)->toMatch('/data-page-breadcrumbs[\s\S]*data-page-intro[\s\S]*Perbarui metode, tanggal, catatan, atau status pembayaran untuk order yang sama\./');
});

test('payment detail keeps action buttons in page intro and renders breadcrumb box once', function () {
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
    $navbar = extractSubpageNavbarMarkup($content);

    expect(substr_count($content, 'data-page-breadcrumbs'))->toBe(1);
    expect($content)->toMatch('/data-flux-main[\s\S]*data-page-breadcrumbs/');
    expect($breadcrumb)->toContain('Beranda');
    expect($breadcrumb)->toContain('Pembayaran');
    expect($breadcrumb)->toContain('Detail Pembayaran');
    expect(substr_count($breadcrumb, 'Detail Pembayaran'))->toBe(1);
    expect($navbar)->toContain('Payment detail');
    expect($navbar)->toContain('Detail Pembayaran');
    expect($navbar)->not->toContain('Cetak');
    expect($navbar)->not->toContain('Edit Pembayaran');
    expect($navbar)->not->toContain('Ringkasan transaksi untuk pelanggan dan status pembayaran terakhir.');
    expect($content)->toContain('data-page-intro');
    expect($content)->toMatch('/data-page-breadcrumbs[\s\S]*data-page-intro[\s\S]*Cetak[\s\S]*Edit Pembayaran/');
});

test('dashboard does not render the separated breadcrumb box', function () {
    $user = createAppUserWithStore();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();

    expect(substr_count($response->getContent(), 'data-page-breadcrumbs'))->toBe(0);
    expect($response->getContent())->not->toContain('nyuci-subpage-title');
});

test('guest login page does not render the separated breadcrumb box', function () {
    $response = $this->get(route('login'));

    $response->assertOk();

    expect(substr_count($response->getContent(), 'data-page-breadcrumbs'))->toBe(0);
});
