<?php

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createInsightOwner(): User
{
    $user = User::factory()->create();

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Nyuci Insight Test',
        'alamat' => 'Jl. Insight No. 5',
        'no_hp' => '081233344455',
    ]);

    return $user->fresh();
}

function createInsightMasterData(Toko $toko, array $klienOverrides = [], array $jasaOverrides = []): array
{
    $klien = Klien::create(array_merge([
        'toko_id' => $toko->id,
        'nama_klien' => 'Klien Insight',
        'alamat_klien' => 'Jl. Insight',
        'no_hp_klien' => '081111111111',
    ], $klienOverrides));

    $jasa = Jasa::create(array_merge([
        'toko_id' => $toko->id,
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 8000,
    ], $jasaOverrides));

    return [$klien, $jasa];
}

function createInsightLaundry(Toko $toko, Klien $klien, Jasa $jasa, array $overrides = []): Laundry
{
    $qty = (float) ($overrides['qty'] ?? 3);
    $status = $overrides['status'] ?? 'belum_selesai';
    $tanggalDimulai = $overrides['tanggal_dimulai'] ?? now()->toDateString();
    $etsSelesai = $overrides['ets_selesai'] ?? now()->addDay()->toDateString();

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

test('biaya jasa page groups services into kiloan and per unit references', function () {
    $user = createInsightOwner();
    $toko = $user->toko;

    [$kiloKlien, $kiloJasa] = createInsightMasterData($toko, [
        'nama_klien' => 'Ari',
        'no_hp_klien' => '081111111111',
    ], [
        'nama_jasa' => 'Cuci',
        'satuan' => 'kg',
        'harga' => 8000,
    ]);

    $kiloLaundry = createInsightLaundry($toko, $kiloKlien, $kiloJasa, [
        'qty' => 3,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-08',
    ]);

    Pembayaran::create([
        'klien_id' => $kiloKlien->id,
        'laundry_id' => $kiloLaundry->id,
        'total' => 24000,
        'total_biaya' => 24000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => '2026-04-07',
        'catatan' => null,
        'status' => 'sudah_bayar',
    ]);

    [$unitKlien, $unitJasa] = createInsightMasterData($toko, [
        'nama_klien' => 'Beni',
        'no_hp_klien' => '082222222222',
    ], [
        'nama_jasa' => 'Setrika',
        'satuan' => 'pcs',
        'harga' => 9000,
    ]);

    $unitLaundry = createInsightLaundry($toko, $unitKlien, $unitJasa, [
        'qty' => 2,
        'tanggal_dimulai' => '2026-04-06',
        'ets_selesai' => '2026-04-09',
    ]);

    Pembayaran::create([
        'klien_id' => $unitKlien->id,
        'laundry_id' => $unitLaundry->id,
        'total' => 18000,
        'total_biaya' => 18000,
        'metode_pembayaran' => 'transfer',
        'tgl_pembayaran' => '2026-04-07',
        'catatan' => null,
        'status' => 'sudah_bayar',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('biaya-jasa.index', [
            'search' => 'pcs',
            'satuan' => 'pcs',
            'sort' => 'satuan',
            'direction' => 'asc',
            'per_page' => 10,
        ]));

    $response
        ->assertOk()
        ->assertSee('Biaya Jasa')
        ->assertSee('Per Unit')
        ->assertSee('Setrika')
        ->assertSee('pcs')
        ->assertDontSee('Cuci')
        ->assertSee('Showing 1 to 1 of 1 results')
        ->assertDontSee('Showing 1-1 of 1 entries');
});

test('pelanggan page filters follow up customers and shows overview cards', function () {
    $user = createInsightOwner();
    $toko = $user->toko;

    [$activeKlien, $activeJasa] = createInsightMasterData($toko, [
        'nama_klien' => 'Citra',
        'no_hp_klien' => '083333333333',
    ], [
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 10000,
    ]);

    $activeLaundry = createInsightLaundry($toko, $activeKlien, $activeJasa, [
        'qty' => 2,
        'tanggal_dimulai' => now()->toDateString(),
        'ets_selesai' => now()->addDay()->toDateString(),
    ]);

    Pembayaran::create([
        'klien_id' => $activeKlien->id,
        'laundry_id' => $activeLaundry->id,
        'total' => 20000,
        'total_biaya' => 20000,
        'metode_pembayaran' => 'qris',
        'tgl_pembayaran' => now()->toDateString(),
        'catatan' => null,
        'status' => 'belum_bayar',
    ]);

    [$archivedKlien, $archivedJasa] = createInsightMasterData($toko, [
        'nama_klien' => 'Dina',
        'no_hp_klien' => '084444444444',
    ], [
        'nama_jasa' => 'keduanya',
        'satuan' => 'kg',
        'harga' => 12500,
    ]);

    $archivedLaundry = createInsightLaundry($toko, $archivedKlien, $archivedJasa, [
        'qty' => 4,
        'status' => 'selesai',
        'tanggal_dimulai' => now()->subDays(45)->toDateString(),
        'ets_selesai' => now()->subDays(43)->toDateString(),
        'tgl_selesai' => now()->subDays(43)->toDateString(),
    ]);

    Pembayaran::create([
        'klien_id' => $archivedKlien->id,
        'laundry_id' => $archivedLaundry->id,
        'total' => 50000,
        'total_biaya' => 50000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => now()->subDays(45)->toDateString(),
        'catatan' => null,
        'status' => 'sudah_bayar',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('pelanggan.index', [
            'status' => 'perlu_follow_up',
            'search' => 'Citra',
            'sort' => 'nama_klien',
            'direction' => 'asc',
            'per_page' => 10,
        ]));

    $response
        ->assertOk()
        ->assertSee('Pelanggan')
        ->assertSee('Total Pelanggan')
        ->assertSee('Perlu Follow Up')
        ->assertSee('Citra')
        ->assertDontSee('Dina')
        ->assertSee('Showing 1 to 1 of 1 results')
        ->assertDontSee('Showing 1-1 of 1 entries');
});
