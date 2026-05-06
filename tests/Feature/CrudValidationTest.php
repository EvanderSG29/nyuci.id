<?php

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCrudValidationOwner(string $storeName = 'Nyuci Validasi'): User
{
    $user = User::factory()->create();

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => $storeName,
        'alamat' => 'Jl. Validasi No. 1',
        'no_hp' => '081234567890',
    ]);

    return $user->fresh();
}

function createCrudValidationMasterData(Toko $toko, array $klienOverrides = [], array $jasaOverrides = []): array
{
    $klien = Klien::create(array_merge([
        'toko_id' => $toko->id,
        'nama_klien' => 'Pelanggan Validasi',
        'email_klien' => null,
        'alamat_klien' => 'Jl. Pelanggan No. 1',
        'no_hp_klien' => '081111111111',
    ], $klienOverrides));

    $jasa = Jasa::create(array_merge([
        'toko_id' => $toko->id,
        'nama_jasa' => 'Cuci Reguler',
        'satuan' => 'kg',
        'harga' => 7000,
    ], $jasaOverrides));

    return [$klien, $jasa];
}

function createCrudValidationLaundry(Toko $toko, Klien $klien, Jasa $jasa, array $overrides = []): Laundry
{
    $qty = (float) ($overrides['qty'] ?? 2);
    $tanggalDimulai = $overrides['tanggal_dimulai'] ?? '2026-04-01';
    $etsSelesai = $overrides['ets_selesai'] ?? '2026-04-02';

    return Laundry::create(array_merge([
        'toko_id' => $toko->id,
        'klien_id' => $klien->id,
        'jasa_id' => $jasa->id,
        'qty' => $qty,
        'status' => 'belum_selesai',
        'tanggal_dimulai' => $tanggalDimulai,
        'ets_selesai' => $etsSelesai,
        'nama' => $klien->nama_klien,
        'no_hp' => $klien->no_hp_klien,
        'berat' => $qty,
        'satuan' => $qty.' '.$jasa->satuan,
        'tanggal' => $tanggalDimulai,
        'layanan' => $jasa->nama_jasa,
        'jenis_jasa' => $jasa->nama_jasa,
        'estimasi_selesai' => $etsSelesai,
        'is_taken' => false,
    ], $overrides));
}

test('pelanggan validation normalizes input and rejects duplicate phone numbers per store', function () {
    $user = createCrudValidationOwner();
    $toko = $user->toko;

    Klien::create([
        'toko_id' => $toko->id,
        'nama_klien' => 'Nomor Sudah Ada',
        'email_klien' => 'lama@example.test',
        'alamat_klien' => 'Jl. Lama',
        'no_hp_klien' => '081222333444',
    ]);

    $this->actingAs($user)
        ->post(route('pelanggan.store'), [
            'nama_klien' => '  Budi   Santoso  ',
            'email_klien' => '  BUDI@EXAMPLE.TEST  ',
            'alamat_klien' => '  Jl.  Baru   No. 2  ',
            'no_hp_klien' => '0812 5555-6666',
        ])
        ->assertRedirect(route('pelanggan.index'));

    $this->assertDatabaseHas('kliens', [
        'toko_id' => $toko->id,
        'nama_klien' => 'Budi Santoso',
        'email_klien' => 'budi@example.test',
        'alamat_klien' => 'Jl. Baru No. 2',
        'no_hp_klien' => '081255556666',
    ]);

    $this->actingAs($user)
        ->post(route('pelanggan.store'), [
            'nama_klien' => 'Nomor Duplikat',
            'email_klien' => 'duplikat@example.test',
            'alamat_klien' => 'Jl. Duplikat',
            'no_hp_klien' => '081222333444',
        ])
        ->assertSessionHasErrors('no_hp_klien');
});

test('biaya jasa validation rejects duplicate service units and non-positive prices', function () {
    $user = createCrudValidationOwner();
    $toko = $user->toko;

    Jasa::create([
        'toko_id' => $toko->id,
        'nama_jasa' => 'Cuci Kilat',
        'satuan' => 'kg',
        'harga' => 9000,
    ]);

    $this->actingAs($user)
        ->post(route('biaya-jasa.store'), [
            'nama_jasa' => 'Cuci Kilat',
            'satuan' => 'KG',
            'harga' => 0,
        ])
        ->assertSessionHasErrors(['nama_jasa', 'harga']);
});

test('laundry validation only accepts pelanggan and jasa from the current store', function () {
    $user = createCrudValidationOwner();
    [$klien, $jasa] = createCrudValidationMasterData($user->toko);

    $foreignUser = createCrudValidationOwner('Toko Lain');
    [$foreignKlien, $foreignJasa] = createCrudValidationMasterData($foreignUser->toko, [
        'no_hp_klien' => '089999999999',
    ], [
        'nama_jasa' => 'Setrika Luar',
    ]);

    $this->actingAs($user)
        ->post(route('laundry.store'), [
            'klien_id' => $foreignKlien->id,
            'jasa_id' => $foreignJasa->id,
            'qty' => 0,
            'status' => 'selesai_dikirim',
            'tanggal_dimulai' => '2026-04-05',
            'ets_selesai' => '2026-04-04',
        ])
        ->assertSessionHasErrors(['klien_id', 'jasa_id', 'qty', 'status', 'ets_selesai']);

    $this->actingAs($user)
        ->post(route('laundry.store'), [
            'klien_id' => $klien->id,
            'jasa_id' => $jasa->id,
            'qty' => '1,5',
            'status' => 'proses',
            'tanggal_dimulai' => '2026-04-05',
            'ets_selesai' => '2026-04-06',
        ])
        ->assertRedirect(route('laundry.index'));

    $this->assertDatabaseHas('laundries', [
        'toko_id' => $user->toko->id,
        'klien_id' => $klien->id,
        'jasa_id' => $jasa->id,
        'qty' => 1.5,
        'status' => 'proses',
    ]);
});

test('pembayaran validation rejects duplicate laundry payments and inconsistent dates', function () {
    $user = createCrudValidationOwner();
    [$klien, $jasa] = createCrudValidationMasterData($user->toko);
    $laundry = createCrudValidationLaundry($user->toko, $klien, $jasa);

    Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 14000,
        'total_biaya' => 14000,
        'metode_pembayaran' => 'cash',
        'status' => 'belum_bayar',
    ]);

    $this->actingAs($user)
        ->post(route('pembayaran.store'), [
            'laundry_id' => $laundry->id,
            'metode_pembayaran' => 'cash',
            'tgl_pembayaran' => '2026-04-01',
            'status' => 'belum_bayar',
        ])
        ->assertSessionHasErrors(['laundry_id', 'tgl_pembayaran']);

    $unpaidLaundry = createCrudValidationLaundry($user->toko, $klien, $jasa, [
        'tanggal_dimulai' => '2026-04-03',
        'ets_selesai' => '2026-04-04',
    ]);

    $this->actingAs($user)
        ->post(route('pembayaran.store'), [
            'laundry_id' => $unpaidLaundry->id,
            'metode_pembayaran' => 'cash',
            'tgl_pembayaran' => '2099-01-01',
            'status' => 'sudah_bayar',
        ])
        ->assertSessionHasErrors('tgl_pembayaran');
});

test('laundry status validation requires a valid finished date for completed orders', function () {
    $user = createCrudValidationOwner();
    [$klien, $jasa] = createCrudValidationMasterData($user->toko);
    $laundry = createCrudValidationLaundry($user->toko, $klien, $jasa, [
        'tanggal_dimulai' => '2026-04-10',
        'ets_selesai' => '2026-04-11',
    ]);

    $this->actingAs($user)
        ->patch(route('laundry.status.update', $laundry), [
            'status_laundry_id' => $laundry->id,
            'status' => 'selesai',
            'tgl_selesai' => '2026-04-09',
        ])
        ->assertSessionHasErrors('tgl_selesai');

    $this->actingAs($user)
        ->patch(route('laundry.status.update', $laundry), [
            'status_laundry_id' => $laundry->id,
            'status' => 'selesai',
            'tgl_selesai' => '2026-04-12',
        ])
        ->assertRedirect();

    expect($laundry->fresh()->status)->toBe('selesai')
        ->and($laundry->fresh()->tgl_selesai->format('Y-m-d'))->toBe('2026-04-12');
});
