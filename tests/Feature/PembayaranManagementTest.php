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

function pembayaranDataTableColumn(string $data, string $name, bool $orderable = true, bool $searchable = true): array
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

function pembayaranDataTablePayload(array $columns, array $overrides = []): array
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

function pembayaranTableText(?string $value): string
{
    return trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')) ?? '');
}

test('pembayaran schema includes phase three columns', function () {
    expect(Schema::hasColumns('pembayarans', ['metode_pembayaran', 'tgl_pembayaran', 'catatan']))->toBeTrue();
});

test('pembayaran index renders datatable shell and endpoint supports filters', function () {
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

    $foreignUser = User::factory()->create();
    $foreignToko = Toko::create([
        'user_id' => $foreignUser->id,
        'nama_toko' => 'Foreign Payment',
        'alamat' => 'Jl. Pembatas',
        'no_hp' => '081255588899',
    ]);

    [$foreignKlien, $foreignJasa] = createPaymentMasterData($foreignToko, [
        'nama_klien' => 'Budi Luar',
        'no_hp_klien' => '086666666666',
    ], [
        'nama_jasa' => 'Dry Clean',
        'satuan' => 'pcs',
        'harga' => 12000,
    ]);

    $foreignLaundry = createPaymentLaundry($foreignToko, $foreignKlien, $foreignJasa, [
        'qty' => 1,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-08',
    ]);

    Pembayaran::create([
        'klien_id' => $foreignKlien->id,
        'laundry_id' => $foreignLaundry->id,
        'total' => 12000,
        'total_biaya' => 12000,
        'metode_pembayaran' => 'qris',
        'tgl_pembayaran' => '2026-04-07',
        'catatan' => 'Luar toko',
        'status' => 'belum_bayar',
    ]);

    $page = $this
        ->actingAs($user)
        ->get(route('pembayaran.index'));

    $page
        ->assertOk()
        ->assertSee('Manage Pembayaran')
        ->assertSee('New')
        ->assertSee('Kelola Belum Bayar')
        ->assertSee('data-dt-action="copy"', false)
        ->assertSee('data-dt-action="print"', false)
        ->assertSee('pembayaran-table');

    $response = $this
        ->actingAs($user)
        ->getJson(route('pembayaran.data', pembayaranDataTablePayload([
            pembayaranDataTableColumn('customer', 'nama_klien'),
            pembayaranDataTableColumn('service', 'service'),
            pembayaranDataTableColumn('method_display', 'metode_pembayaran'),
            pembayaranDataTableColumn('date_display', 'tgl_pembayaran'),
            pembayaranDataTableColumn('status_badge', 'status'),
            pembayaranDataTableColumn('total_display', 'total'),
            pembayaranDataTableColumn('actions', 'actions', false, false),
        ], [
            'search' => ['value' => 'Budi'],
            'status' => 'belum_bayar',
            'metode_pembayaran' => 'qris',
            'order' => [['column' => 0, 'dir' => 'asc']],
        ])));

    $response
        ->assertOk()
        ->assertJsonPath('recordsTotal', 11)
        ->assertJsonPath('recordsFiltered', 1);

    $row = $response->json('data.0');

    expect(pembayaranTableText($row['customer']))->toContain('Budi Santoso');
    expect(pembayaranTableText($row['customer']))->not->toContain('Luar');
    expect(pembayaranTableText($row['method_display']))->toContain('QRIS');
    expect(pembayaranTableText($row['actions']))->toContain('Tandai Lunas');
});

test('kelola belum bayar page renders datatable shell and endpoint returns action buttons', function () {
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

    $foreignUser = User::factory()->create();
    $foreignToko = Toko::create([
        'user_id' => $foreignUser->id,
        'nama_toko' => 'Unpaid Luar',
        'alamat' => 'Jl. Seberang',
        'no_hp' => '081277788899',
    ]);

    [$foreignKlien, $foreignJasa] = createPaymentMasterData($foreignToko, [
        'nama_klien' => 'Rina Luar',
        'no_hp_klien' => '087777777777',
    ], [
        'nama_jasa' => 'Cuci Luar',
        'satuan' => 'kg',
        'harga' => 17000,
    ]);

    createPaymentLaundry($foreignToko, $foreignKlien, $foreignJasa, [
        'qty' => 1,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-08',
    ]);

    $page = $this
        ->actingAs($user)
        ->get(route('pembayaran.unpaid'));

    $page
        ->assertOk()
        ->assertSee('Kelola Belum Bayar')
        ->assertSee('Manage Belum Bayar')
        ->assertSee('New')
        ->assertSee('data-dt-action="csv"', false)
        ->assertSee('data-dt-action="reload"', false)
        ->assertSee('unpaid-laundry-table');

    $response = $this
        ->actingAs($user)
        ->getJson(route('pembayaran.unpaid.data', pembayaranDataTablePayload([
            pembayaranDataTableColumn('customer', 'nama'),
            pembayaranDataTableColumn('service', 'jenis_jasa'),
            pembayaranDataTableColumn('received_at', 'tanggal'),
            pembayaranDataTableColumn('due_at', 'estimasi_selesai'),
            pembayaranDataTableColumn('status_badge', 'status'),
            pembayaranDataTableColumn('total_display', 'total'),
            pembayaranDataTableColumn('actions', 'actions', false, false),
        ], [
            'order' => [['column' => 2, 'dir' => 'desc']],
        ])));

    $response
        ->assertOk()
        ->assertJsonPath('recordsTotal', 2)
        ->assertJsonPath('recordsFiltered', 2);

    $rows = $response->json('data');
    $actions = collect($rows)->pluck('actions')->map(fn (?string $value) => pembayaranTableText($value))->implode(' | ');
    $customers = collect($rows)->pluck('customer')->map(fn (?string $value) => pembayaranTableText($value))->implode(' | ');

    expect($customers)->toContain($needsPayment->nama);
    expect($customers)->toContain($withUnpaidPayment->nama);
    expect($customers)->not->toContain('Rina Luar');
    expect($actions)->toContain('Bayar Sekarang');
    expect($actions)->toContain('Selesaikan Pembayaran');
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
