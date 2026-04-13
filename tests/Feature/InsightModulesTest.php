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

function insightDataTableColumn(string $data, string $name, bool $orderable = true, bool $searchable = true): array
{
    return [
        'data' => $data,
        'name' => $name,
        'orderable' => $orderable,
        'searchable' => $searchable,
        'search' => [
            'value' => '',
            'regex' => 'false',
        ],
    ];
}

function insightDataTablePayload(array $columns, array $overrides = []): array
{
    return array_replace_recursive([
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'search' => [
            'value' => '',
            'regex' => 'false',
        ],
        'order' => [
            ['column' => 0, 'dir' => 'asc'],
        ],
        'columns' => $columns,
    ], $overrides);
}

function insightTableText(?string $value): string
{
    return trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')) ?? '');
}

test('biaya jasa page renders datatable shell and toolbar buttons', function () {
    $user = createInsightOwner();
    $toko = $user->toko;

    createInsightMasterData($toko, [], [
        'nama_jasa' => 'Cuci',
        'satuan' => 'kg',
        'harga' => 8000,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('biaya-jasa.index'));

    $response
        ->assertOk()
        ->assertSee('Biaya Jasa')
        ->assertSee('Manage Jasa')
        ->assertSee('New')
        ->assertSee('data-dt-flyout-body', false)
        ->assertSee('data-dt-action="copy"', false)
        ->assertSee('data-dt-action="reload"', false)
        ->assertSee('jasa-table');
});

test('biaya jasa datatable filters services by unit and scopes toko', function () {
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

    createInsightLaundry($toko, $kiloKlien, $kiloJasa, [
        'qty' => 3,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-08',
    ]);

    [$unitKlien, $unitJasa] = createInsightMasterData($toko, [
        'nama_klien' => 'Beni',
        'no_hp_klien' => '082222222222',
    ], [
        'nama_jasa' => 'Setrika',
        'satuan' => 'pcs',
        'harga' => 9000,
    ]);

    createInsightLaundry($toko, $unitKlien, $unitJasa, [
        'qty' => 2,
        'tanggal_dimulai' => '2026-04-06',
        'ets_selesai' => '2026-04-09',
    ]);

    $foreignUser = User::factory()->create();
    $foreignToko = Toko::create([
        'user_id' => $foreignUser->id,
        'nama_toko' => 'Foreign Insight',
        'alamat' => 'Jl. Luar',
        'no_hp' => '081255566677',
    ]);

    createInsightMasterData($foreignToko, [], [
        'nama_jasa' => 'Setrika Luar',
        'satuan' => 'pcs',
        'harga' => 9500,
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson(route('biaya-jasa.data', insightDataTablePayload([
            insightDataTableColumn('DT_RowIndex', 'DT_RowIndex', false, false),
            insightDataTableColumn('service_name', 'nama_jasa'),
            insightDataTableColumn('unit_badge', 'satuan'),
            insightDataTableColumn('price_display', 'harga'),
            insightDataTableColumn('total_order_display', 'total_order'),
            insightDataTableColumn('actions', 'actions', false, false),
        ], [
            'search' => ['value' => 'pcs'],
            'order' => [['column' => 2, 'dir' => 'asc']],
            'satuan' => 'pcs',
        ])));

    $response
        ->assertOk()
        ->assertJsonPath('recordsTotal', 2)
        ->assertJsonPath('recordsFiltered', 1);

    $row = $response->json('data.0');

    expect(insightTableText($row['service_name']))->toContain('Setrika');
    expect(insightTableText($row['service_name']))->not->toContain('Luar');
    expect(insightTableText($row['unit_badge']))->toContain('pcs');
    expect(insightTableText($row['actions']))->toContain('Detail');
    expect($row['actions'])->toContain('data-detail-url');
});

test('biaya jasa preview is scoped to toko and renders summary details', function () {
    $user = createInsightOwner();
    $toko = $user->toko;

    [$klien, $jasa] = createInsightMasterData($toko, [], [
        'nama_jasa' => 'Cuci Premium',
        'satuan' => 'kg',
        'harga' => 12000,
    ]);

    createInsightLaundry($toko, $klien, $jasa, [
        'qty' => 4,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-08',
    ]);

    $foreignUser = User::factory()->create();
    $foreignToko = Toko::create([
        'user_id' => $foreignUser->id,
        'nama_toko' => 'Preview Luar',
        'alamat' => 'Jl. Luar',
        'no_hp' => '081299900000',
    ]);

    [, $foreignJasa] = createInsightMasterData($foreignToko, [], [
        'nama_jasa' => 'Cuci Luar',
        'satuan' => 'pcs',
        'harga' => 7000,
    ]);

    $this
        ->actingAs($user)
        ->get(route('biaya-jasa.preview', $jasa))
        ->assertOk()
        ->assertSee('Cuci Premium')
        ->assertSee('Harga')
        ->assertSee('1 order');

    $this
        ->actingAs($user)
        ->get(route('biaya-jasa.preview', $foreignJasa))
        ->assertNotFound();
});

test('pelanggan page renders datatable shell and toolbar buttons', function () {
    $user = createInsightOwner();
    $toko = $user->toko;

    createInsightMasterData($toko, [
        'nama_klien' => 'Citra',
        'no_hp_klien' => '083333333333',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('pelanggan.index'));

    $response
        ->assertOk()
        ->assertSee('Pelanggan')
        ->assertSee('Manage Pelanggan')
        ->assertSee('New')
        ->assertSee('data-dt-flyout-body', false)
        ->assertSee('data-dt-action="csv"', false)
        ->assertSee('data-dt-action="print"', false)
        ->assertSee('klien-table');
});

test('pelanggan datatable filters follow up customers and scopes toko', function () {
    $user = createInsightOwner();
    $toko = $user->toko;

    [$activeKlien, $activeJasa] = createInsightMasterData($toko, [
        'nama_klien' => 'Citra',
        'no_hp_klien' => '083333333333',
    ], [
        'nama_jasa' => 'Cuci',
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
        'nama_jasa' => 'Setrika',
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

    $foreignUser = User::factory()->create();
    $foreignToko = Toko::create([
        'user_id' => $foreignUser->id,
        'nama_toko' => 'Outside Toko',
        'alamat' => 'Jl. Lain',
        'no_hp' => '081200000000',
    ]);

    [$foreignKlien, $foreignJasa] = createInsightMasterData($foreignToko, [
        'nama_klien' => 'Citra Luar',
        'no_hp_klien' => '085555555555',
    ], [
        'nama_jasa' => 'Dry Clean',
        'satuan' => 'pcs',
        'harga' => 30000,
    ]);

    $foreignLaundry = createInsightLaundry($foreignToko, $foreignKlien, $foreignJasa, [
        'qty' => 1,
        'tanggal_dimulai' => now()->toDateString(),
        'ets_selesai' => now()->addDay()->toDateString(),
    ]);

    Pembayaran::create([
        'klien_id' => $foreignKlien->id,
        'laundry_id' => $foreignLaundry->id,
        'total' => 30000,
        'total_biaya' => 30000,
        'metode_pembayaran' => 'qris',
        'tgl_pembayaran' => now()->toDateString(),
        'catatan' => null,
        'status' => 'belum_bayar',
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson(route('pelanggan.data', insightDataTablePayload([
            insightDataTableColumn('customer', 'nama_klien'),
            insightDataTableColumn('contact', 'no_hp_klien'),
            insightDataTableColumn('total_order_display', 'total_order'),
            insightDataTableColumn('unpaid_display', 'belum_bayar'),
            insightDataTableColumn('last_order_display', 'terakhir_order'),
            insightDataTableColumn('actions', 'actions', false, false),
        ], [
            'search' => ['value' => 'Citra'],
            'order' => [['column' => 0, 'dir' => 'asc']],
            'status' => 'perlu_follow_up',
        ])));

    $response
        ->assertOk()
        ->assertJsonPath('recordsTotal', 2)
        ->assertJsonPath('recordsFiltered', 1);

    $row = $response->json('data.0');

    expect(insightTableText($row['customer']))->toContain('Citra');
    expect(insightTableText($row['customer']))->not->toContain('Luar');
    expect(insightTableText($row['unpaid_display']))->toContain('1 tagihan');
    expect(insightTableText($row['actions']))->toContain('Detail');
    expect(insightTableText($row['actions']))->toContain('Edit');
    expect($row['actions'])->toContain('data-detail-url');
});

test('pelanggan preview is scoped to toko and shows follow up details', function () {
    $user = createInsightOwner();
    $toko = $user->toko;

    [$klien, $jasa] = createInsightMasterData($toko, [
        'nama_klien' => 'Rara',
        'email_klien' => 'rara@example.test',
        'alamat_klien' => 'Jl. Melati 12',
        'no_hp_klien' => '081234001122',
    ], [
        'nama_jasa' => 'Setrika',
        'satuan' => 'pcs',
        'harga' => 9000,
    ]);

    $laundry = createInsightLaundry($toko, $klien, $jasa, [
        'qty' => 2,
        'tanggal_dimulai' => '2026-04-09',
        'ets_selesai' => '2026-04-10',
    ]);

    Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 18000,
        'total_biaya' => 18000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => '2026-04-09',
        'catatan' => null,
        'status' => 'belum_bayar',
    ]);

    $foreignUser = User::factory()->create();
    $foreignToko = Toko::create([
        'user_id' => $foreignUser->id,
        'nama_toko' => 'Outside Preview',
        'alamat' => 'Jl. Batas',
        'no_hp' => '081266677788',
    ]);

    [$foreignKlien] = createInsightMasterData($foreignToko, [
        'nama_klien' => 'Rara Luar',
        'no_hp_klien' => '086666000111',
    ]);

    $this
        ->actingAs($user)
        ->get(route('pelanggan.preview', $klien))
        ->assertOk()
        ->assertSee('Rara')
        ->assertSee('Belum bayar')
        ->assertSee('1 tagihan');

    $this
        ->actingAs($user)
        ->get(route('pelanggan.preview', $foreignKlien))
        ->assertNotFound();
});
