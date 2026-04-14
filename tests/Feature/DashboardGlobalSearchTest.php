<?php

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createSearchOwner(string $name = 'Search Owner'): User
{
    $user = User::factory()->create([
        'name' => $name,
    ]);

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Nyuci Search',
        'alamat' => 'Jl. Search No. 1',
        'no_hp' => '081200000111',
    ]);

    return $user->fresh();
}

function seedSearchRecords(Toko $toko, string $customerName = 'Andi Saputra'): array
{
    $jasa = Jasa::create([
        'toko_id' => $toko->id,
        'nama_jasa' => 'cuci_express',
        'satuan' => 'kg',
        'harga' => 9000,
    ]);

    $klien = Klien::create([
        'toko_id' => $toko->id,
        'nama_klien' => $customerName,
        'alamat_klien' => 'Jl. Mawar',
        'no_hp_klien' => '081234567890',
    ]);

    $laundry = Laundry::create([
        'toko_id' => $toko->id,
        'klien_id' => $klien->id,
        'jasa_id' => $jasa->id,
        'qty' => 2,
        'status' => 'proses',
        'tanggal_dimulai' => '2026-04-09',
        'ets_selesai' => '2026-04-10',
        'nama' => $klien->nama_klien,
        'no_hp' => $klien->no_hp_klien,
        'berat' => 2,
        'satuan' => '2 kg',
        'tanggal' => '2026-04-09',
        'layanan' => $jasa->nama_jasa,
        'jenis_jasa' => $jasa->nama_jasa,
        'estimasi_selesai' => '2026-04-10',
        'is_taken' => false,
    ]);

    $pembayaran = Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 18000,
        'total_biaya' => 18000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => '2026-04-09',
        'status' => 'belum_bayar',
    ]);

    return [$jasa, $klien, $laundry, $pembayaran];
}

test('global dashboard search returns grouped results scoped to the authenticated store', function () {
    $user = createSearchOwner();
    $toko = $user->toko;

    [, $klien, $laundry, $pembayaran] = seedSearchRecords($toko, 'Andi Saputra');

    $otherUser = createSearchOwner('Other Search Owner');
    seedSearchRecords($otherUser->toko, 'Andi Toko Lain');

    $response = $this
        ->actingAs($user)
        ->getJson(route('search.global', ['query' => 'Andi']));

    $response
        ->assertOk()
        ->assertJsonPath('query', 'Andi')
        ->assertJsonPath('message', null)
        ->assertJsonPath('groups.0.key', 'laundry')
        ->assertJsonPath('groups.1.key', 'pelanggan')
        ->assertJsonPath('groups.2.key', 'pembayaran')
        ->assertJsonPath('groups.0.items.0.url', route('laundry.edit', $laundry))
        ->assertJsonPath('groups.1.items.0.url', route('pelanggan.edit', $klien))
        ->assertJsonPath('groups.2.items.0.url', route('pembayaran.show', $pembayaran))
        ->assertDontSee('Andi Toko Lain');
});

test('global dashboard search returns no results for short queries or users without a store', function () {
    $userWithoutStore = User::factory()->create();

    $this
        ->actingAs($userWithoutStore)
        ->getJson(route('search.global', ['query' => 'Andi']))
        ->assertOk()
        ->assertJson([
            'query' => 'Andi',
            'groups' => [],
            'message' => null,
        ]);

    $userWithStore = createSearchOwner('Store Ready');

    $this
        ->actingAs($userWithStore)
        ->getJson(route('search.global', ['query' => 'A']))
        ->assertOk()
        ->assertJson([
            'query' => 'A',
            'groups' => [],
            'message' => null,
        ]);
});

test('module index pages expose initial datatable search from query string', function () {
    $user = createSearchOwner('Deep Link Search');

    $this
        ->actingAs($user)
        ->get(route('laundry.index', ['search' => 'Budi']))
        ->assertOk()
        ->assertSee('"initialSearch":"Budi"', false);

    $this
        ->actingAs($user)
        ->get(route('pelanggan.index', ['search' => 'Salsa']))
        ->assertOk()
        ->assertSee('"initialSearch":"Salsa"', false);

    $this
        ->actingAs($user)
        ->get(route('pembayaran.index', ['search' => 'QRIS']))
        ->assertOk()
        ->assertSee('"initialSearch":"QRIS"', false);
});
