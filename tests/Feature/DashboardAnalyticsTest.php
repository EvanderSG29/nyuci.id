<?php

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

function createDashboardOwner(): User
{
    $user = User::factory()->create();

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Nyuci Analytics',
        'alamat' => 'Jl. Dashboard No. 1',
        'no_hp' => '081200000001',
    ]);

    return $user->fresh();
}

function createDashboardLaundry(Toko $toko, Klien $klien, Jasa $jasa, array $overrides = []): Laundry
{
    $qty = (float) ($overrides['qty'] ?? 1);
    $status = $overrides['status'] ?? 'belum_selesai';
    $tanggalDimulai = $overrides['tanggal_dimulai'] ?? Carbon::now()->toDateString();
    $etsSelesai = $overrides['ets_selesai'] ?? Carbon::now()->addDay()->toDateString();

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
        'tgl_selesai' => $status === 'selesai' ? ($overrides['tgl_selesai'] ?? $etsSelesai) : null,
        'is_taken' => $status === 'selesai',
    ], $overrides));
}

test('dashboard displays analytics sections and computed values', function () {
    Carbon::setTestNow('2026-04-09 09:00:00');

    $user = createDashboardOwner();
    $toko = $user->toko;

    $cuci = Jasa::create([
        'toko_id' => $toko->id,
        'nama_jasa' => 'cuci_express',
        'satuan' => 'kg',
        'harga' => 8000,
    ]);

    $setrika = Jasa::create([
        'toko_id' => $toko->id,
        'nama_jasa' => 'setrika_premium',
        'satuan' => 'pcs',
        'harga' => 5500,
    ]);

    $andi = Klien::create([
        'toko_id' => $toko->id,
        'nama_klien' => 'Andi Saputra',
        'alamat_klien' => 'Jl. Mawar',
        'no_hp_klien' => '081100000001',
    ]);

    $sinta = Klien::create([
        'toko_id' => $toko->id,
        'nama_klien' => 'Sinta Dewi',
        'alamat_klien' => 'Jl. Melati',
        'no_hp_klien' => '081100000002',
    ]);

    $pendingLaundry = createDashboardLaundry($toko, $andi, $cuci, [
        'qty' => 3,
        'tanggal_dimulai' => '2026-04-08',
        'ets_selesai' => '2026-04-09',
    ]);

    $finishedLaundry = createDashboardLaundry($toko, $sinta, $setrika, [
        'qty' => 2,
        'status' => 'selesai',
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-08',
        'tgl_selesai' => '2026-04-09',
    ]);

    createDashboardLaundry($toko, $andi, $cuci, [
        'qty' => 1,
        'status' => 'proses',
        'tanggal_dimulai' => '2026-04-09',
        'ets_selesai' => '2026-04-10',
    ]);

    Pembayaran::create([
        'klien_id' => $andi->id,
        'laundry_id' => $pendingLaundry->id,
        'total' => 24000,
        'total_biaya' => 24000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => '2026-04-09',
        'status' => 'belum_bayar',
    ]);

    Pembayaran::create([
        'klien_id' => $sinta->id,
        'laundry_id' => $finishedLaundry->id,
        'total' => 16500,
        'total_biaya' => 16500,
        'metode_pembayaran' => 'qris',
        'tgl_pembayaran' => '2026-04-09',
        'status' => 'sudah_bayar',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('Pergerakan order 14 hari terakhir')
        ->assertSee('Pendapatan bulan ini')
        ->assertSee('Komposisi kanal pembayaran')
        ->assertSee('Cuci Express')
        ->assertSee('Setrika Premium')
        ->assertSee('Rp 16.500')
        ->assertSee('Rp 32.000')
        ->assertSee('2 order masih aktif dan 2 tagihan butuh tindakan hari ini.');
});
