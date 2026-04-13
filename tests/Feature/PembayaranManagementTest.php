<?php

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function createPembayaranOwner(): User
{
    $user = User::factory()->create();

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Nyuci Payment Test',
        'alamat' => 'Jl. Test No. 2',
        'no_hp' => '081234567891',
    ]);

    return $user->fresh();
}

function createPaymentMasterData(Toko $toko, array $klienOverrides = [], array $jasaOverrides = []): array
{
    $klien = Klien::create(array_merge([
        'toko_id' => $toko->id,
        'nama_klien' => 'Klien Payment',
        'alamat_klien' => 'Jl. Payment',
        'no_hp_klien' => '081111111111',
    ], $klienOverrides));

    $jasa = Jasa::create(array_merge([
        'toko_id' => $toko->id,
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 9000,
    ], $jasaOverrides));

    return [$klien, $jasa];
}

function createPaymentLaundry(Toko $toko, Klien $klien, Jasa $jasa, array $overrides = []): Laundry
{
    $qty = (float) ($overrides['qty'] ?? 3);
    $status = $overrides['status'] ?? 'belum_selesai';
    $tanggalDimulai = $overrides['tanggal_dimulai'] ?? '2026-04-07';
    $etsSelesai = $overrides['ets_selesai'] ?? '2026-04-08';

    return Laundry::create(array_merge([
        'toko_id' => $toko->id,
        'klien_id' => $klien->id,
        'jasa_id' => $jasa->id,
        'qty' => $qty,
        'status' => $status,
        'tanggal_dimulai' => $tanggalDimulai,
        'ets_selesai' => $etsSelesai,
        'nama' => $klien->nama_klien,
        'no_hp' => $klien->no_hp_klien,
        'berat' => str_contains($jasa->satuan, 'kg') ? $qty : 0,
        'satuan' => rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.').' '.$jasa->satuan,
        'tanggal' => $tanggalDimulai,
        'layanan' => $jasa->nama_jasa,
        'jenis_jasa' => $jasa->nama_jasa,
        'estimasi_selesai' => $etsSelesai,
        'tgl_selesai' => $status === 'selesai' ? ($overrides['tgl_selesai'] ?? $etsSelesai) : ($overrides['tgl_selesai'] ?? null),
        'is_taken' => $status === 'selesai',
    ], $overrides));
}

test('pembayaran schema includes phase three columns', function () {
    expect(Schema::hasColumns('pembayarans', ['metode_pembayaran', 'tgl_pembayaran', 'catatan']))->toBeTrue();
});

test('pembayaran index supports search, filters, and pagination', function () {
    $user = createPembayaranOwner();
    $toko = $user->toko;

    [$targetKlien, $targetJasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Budi Santoso',
        'no_hp_klien' => '081111111111',
    ], [
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 8333,
    ]);

    $targetLaundry = createPaymentLaundry($toko, $targetKlien, $targetJasa, [
        'qty' => 3,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-08',
    ]);

    Pembayaran::create([
        'klien_id' => $targetKlien->id,
        'laundry_id' => $targetLaundry->id,
        'total' => 25000,
        'total_biaya' => 25000,
        'metode_pembayaran' => 'qris',
        'tgl_pembayaran' => '2026-04-07',
        'catatan' => 'Lunas via QRIS',
        'status' => 'belum_bayar',
    ]);

    [$secondaryKlien, $secondaryJasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Siti Aminah',
        'no_hp_klien' => '082222222222',
    ], [
        'nama_jasa' => 'setrika',
        'satuan' => 'pcs',
        'harga' => 7500,
    ]);

    createPaymentLaundry($toko, $secondaryKlien, $secondaryJasa, [
        'qty' => 2,
        'status' => 'selesai',
        'tanggal_dimulai' => '2026-04-06',
        'ets_selesai' => '2026-04-09',
        'tgl_selesai' => '2026-04-09',
    ]);

    foreach (range(1, 10) as $index) {
        [$loopKlien, $loopJasa] = createPaymentMasterData($toko, [
            'nama_klien' => "Laundry {$index}",
            'no_hp_klien' => '08333333333'.$index,
        ], [
            'nama_jasa' => 'cuci-'.$index,
            'satuan' => 'kg',
            'harga' => 15000,
        ]);

        $laundry = createPaymentLaundry($toko, $loopKlien, $loopJasa, [
            'qty' => 1,
            'tanggal_dimulai' => '2026-04-05',
            'ets_selesai' => '2026-04-10',
        ]);

        Pembayaran::create([
            'klien_id' => $loopKlien->id,
            'laundry_id' => $laundry->id,
            'total' => 15000,
            'total_biaya' => 15000,
            'metode_pembayaran' => 'cash',
            'tgl_pembayaran' => '2026-04-07',
            'catatan' => null,
            'status' => 'sudah_bayar',
        ]);
    }

    $response = $this
        ->actingAs($user)
        ->get(route('pembayaran.index', [
            'search' => 'Budi',
            'status' => 'belum_bayar',
            'metode_pembayaran' => 'qris',
            'sort' => 'nama_klien',
            'direction' => 'asc',
            'per_page' => 10,
        ]));

    $response
        ->assertOk()
        ->assertSee('Budi Santoso')
        ->assertDontSee('Siti Aminah')
        ->assertSee('Showing 1 to 1 of 1 results')
        ->assertDontSee('Showing 1-1 of 1 entries')
        ->assertDontSee('Transfer')
        ->assertDontSee('E-Wallet');
});

test('kelola belum bayar lists unpaid laundries and action buttons', function () {
    $user = createPembayaranOwner();
    $toko = $user->toko;

    [$needsPaymentKlien, $needsPaymentJasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Rina',
        'no_hp_klien' => '081999999999',
    ], [
        'nama_jasa' => 'keduanya',
        'satuan' => 'kg',
        'harga' => 12000,
    ]);

    $needsPayment = createPaymentLaundry($toko, $needsPaymentKlien, $needsPaymentJasa, [
        'qty' => 4,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-09',
    ]);

    [$withUnpaidKlien, $withUnpaidJasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Dina',
        'no_hp_klien' => '082888888888',
    ], [
        'nama_jasa' => 'cuci-unit',
        'satuan' => 'kg',
        'harga' => 15000,
    ]);

    $withUnpaidPayment = createPaymentLaundry($toko, $withUnpaidKlien, $withUnpaidJasa, [
        'qty' => 2,
        'status' => 'selesai',
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-09',
    ]);

    Pembayaran::create([
        'klien_id' => $withUnpaidKlien->id,
        'laundry_id' => $withUnpaidPayment->id,
        'total' => 30000,
        'total_biaya' => 30000,
        'metode_pembayaran' => 'transfer',
        'tgl_pembayaran' => '2026-04-07',
        'catatan' => null,
        'status' => 'belum_bayar',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('pembayaran.unpaid'));

    $response
        ->assertOk()
        ->assertSee('Kelola Belum Bayar')
        ->assertSee('Bayar Sekarang')
        ->assertSee('Selesaikan Pembayaran')
        ->assertSee($needsPayment->nama)
        ->assertSee($withUnpaidPayment->nama)
        ->assertSee('Showing 1 to 2 of 2 results')
        ->assertDontSee('Showing 1-2 of 2 entries');
});

test('pembayaran store saves phase three fields and rejects foreign laundries', function () {
    $user = createPembayaranOwner();
    $toko = $user->toko;
    $otherUser = User::factory()->create();
    $otherToko = Toko::create([
        'user_id' => $otherUser->id,
        'nama_toko' => 'Other Toko',
        'alamat' => 'Jl. Lain',
        'no_hp' => '089999999999',
    ]);

    [$klien, $jasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Andi',
        'no_hp_klien' => '081700000000',
    ], [
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 9000,
    ]);

    $laundry = createPaymentLaundry($toko, $klien, $jasa, [
        'qty' => 5,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-10',
    ]);

    [$foreignKlien, $foreignJasa] = createPaymentMasterData($otherToko, [
        'nama_klien' => 'Luar Toko',
        'no_hp_klien' => '081800000000',
    ], [
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 10000,
    ]);

    $foreignLaundry = createPaymentLaundry($otherToko, $foreignKlien, $foreignJasa, [
        'qty' => 1,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-10',
    ]);

    $this
        ->actingAs($user)
        ->from(route('pembayaran.create'))
        ->post(route('pembayaran.store'), [
            'laundry_id' => $laundry->id,
            'metode_pembayaran' => 'cash',
            'tgl_pembayaran' => '2026-04-07',
            'catatan' => 'Bayar tunai',
            'status' => 'belum_bayar',
        ])
        ->assertRedirect(route('pembayaran.index'))
        ->assertSessionHas('success', 'Pembayaran berhasil disimpan.');

    expect(Pembayaran::query()->where('laundry_id', $laundry->id)->exists())->toBeTrue();

    $this
        ->actingAs($user)
        ->from(route('pembayaran.create'))
        ->post(route('pembayaran.store'), [
            'laundry_id' => $foreignLaundry->id,
            'metode_pembayaran' => 'cash',
            'tgl_pembayaran' => '2026-04-07',
            'catatan' => null,
            'status' => 'belum_bayar',
        ])
        ->assertSessionHasErrors('laundry_id');
});

test('pembayaran mark as paid updates status and date', function () {
    $user = createPembayaranOwner();
    $toko = $user->toko;
    [$klien, $jasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Sari',
        'no_hp_klien' => '081900000000',
    ], [
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 18333,
    ]);

    $laundry = createPaymentLaundry($toko, $klien, $jasa, [
        'qty' => 3,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-10',
    ]);

    $payment = Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 55000,
        'total_biaya' => 55000,
        'metode_pembayaran' => 'transfer',
        'tgl_pembayaran' => null,
        'catatan' => null,
        'status' => 'belum_bayar',
    ]);

    $this
        ->actingAs($user)
        ->from(route('pembayaran.index'))
        ->get(route('pembayaran.paid', $payment))
        ->assertRedirect(route('pembayaran.index'));

    $payment->refresh();

    expect($payment->status)->toBe('sudah_bayar');
    expect($payment->tgl_pembayaran?->format('Y-m-d'))->not()->toBeEmpty();
});
