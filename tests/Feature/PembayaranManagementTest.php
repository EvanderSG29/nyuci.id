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

function configureManagedQris(Toko $toko, array $overrides = []): void
{
    config()->set('payment_gateway.driver', 'qris_static');
    config()->set('payment_gateway.checkout_ttl_minutes', 30);
    config()->set('payment_gateway.qris_static.payload', '0002010102115802ID6304ABCD');
    config()->set('payment_gateway.qris_static.merchant_name', 'Server QRIS');

    $toko->update(array_merge([
        'payment_gateway_qris_payload' => '0002010102115802ID6304EFGH',
        'payment_gateway_qris_merchant_name' => 'Toko QRIS',
        'payment_gateway_checkout_ttl_minutes' => 45,
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
        ->assertSee('data-dt-flyout-body', false)
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
    expect(pembayaranTableText($row['actions']))->toContain('Detail');
    expect(pembayaranTableText($row['actions']))->toContain('Tandai Lunas');
    expect($row['actions'])->toContain('data-detail-url');
});

test('pembayaran preview is scoped to toko and renders summary details', function () {
    $user = createPembayaranOwner();
    $toko = $user->toko;

    [$klien, $jasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Raka',
        'no_hp_klien' => '081822233344',
    ], [
        'nama_jasa' => 'Dry Clean',
        'satuan' => 'pcs',
        'harga' => 20000,
    ]);

    $laundry = createPaymentLaundry($toko, $klien, $jasa, [
        'qty' => 2,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-09',
    ]);

    $payment = Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 40000,
        'total_biaya' => 40000,
        'metode_pembayaran' => 'transfer',
        'tgl_pembayaran' => '2026-04-08',
        'catatan' => 'Transfer bank',
        'status' => 'belum_bayar',
        'gateway_token' => 'preview-token',
        'gateway_status' => 'pending',
    ]);

    $foreignUser = User::factory()->create();
    $foreignToko = Toko::create([
        'user_id' => $foreignUser->id,
        'nama_toko' => 'Foreign Preview Payment',
        'alamat' => 'Jl. Sebelah',
        'no_hp' => '081211122233',
    ]);

    [$foreignKlien, $foreignJasa] = createPaymentMasterData($foreignToko);
    $foreignLaundry = createPaymentLaundry($foreignToko, $foreignKlien, $foreignJasa);
    $foreignPayment = Pembayaran::create([
        'klien_id' => $foreignKlien->id,
        'laundry_id' => $foreignLaundry->id,
        'total' => 27000,
        'total_biaya' => 27000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => '2026-04-07',
        'catatan' => null,
        'status' => 'sudah_bayar',
    ]);

    $this
        ->actingAs($user)
        ->get(route('pembayaran.preview', $payment))
        ->assertOk()
        ->assertSee('Pembayaran #'.$payment->id)
        ->assertSee('Status pembayaran')
        ->assertSee('Transfer')
        ->assertSee('Lihat Detail Lengkap')
        ->assertSee(route('pembayaran.show', $payment), false);

    $this
        ->actingAs($user)
        ->get(route('pembayaran.preview', $foreignPayment))
        ->assertForbidden();
});

test('pembayaran show renders full detail page with customer, invoice, and gateway summary', function () {
    $user = createPembayaranOwner();
    $toko = $user->toko;

    [$klien, $jasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Ayu Lestari',
        'no_hp_klien' => '080008107009',
    ], [
        'nama_jasa' => 'Cuci Setrika Express',
        'satuan' => 'kg',
        'harga' => 12000,
    ]);

    $laundry = createPaymentLaundry($toko, $klien, $jasa, [
        'qty' => 5,
        'tanggal_dimulai' => '2026-04-10',
        'ets_selesai' => '2026-04-11',
        'status' => 'belum_selesai',
    ]);

    $payment = Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 60000,
        'total_biaya' => 60000,
        'metode_pembayaran' => 'qris',
        'tgl_pembayaran' => null,
        'catatan' => 'Menunggu pembayaran QRIS.',
        'status' => 'belum_bayar',
        'gateway_token' => 'show-detail-token',
        'gateway_reference' => 'NYUCI-406-MHZAKAFTMR',
        'gateway_invoice_id' => 'INV-406-6T6CU6U3',
        'gateway_request_date' => '2026-04-15',
        'gateway_expires_at' => now()->addHour(),
        'gateway_status' => 'pending',
        'gateway_qr_image' => 'data:image/svg+xml;base64,'.base64_encode('<svg></svg>'),
        'gateway_payload' => [
            'merchant_name' => 'Nyuci Demo Store',
        ],
    ]);

    $this
        ->actingAs($user)
        ->get(route('pembayaran.show', $payment))
        ->assertOk()
        ->assertSee('Detail Pembayaran')
        ->assertSee('Invoice')
        ->assertSee('#'.$payment->id)
        ->assertSee('Ayu Lestari')
        ->assertSee('080008107009')
        ->assertSee('Cuci Setrika Express')
        ->assertSee('Gateway QRIS')
        ->assertSee('NYUCI-406-MHZAKAFTMR')
        ->assertSee('INV-406-6T6CU6U3')
        ->assertSee('Rp 60.000');
});

test('pembayaran edit form renders order summary without blank state', function () {
    $user = createPembayaranOwner();
    $toko = $user->toko;

    [$klien, $jasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Nadia',
        'no_hp_klien' => '081855566677',
    ], [
        'nama_jasa' => 'cuci premium',
        'satuan' => 'kg',
        'harga' => 15000,
    ]);

    $laundry = createPaymentLaundry($toko, $klien, $jasa, [
        'qty' => 2,
        'tanggal_dimulai' => '2026-04-08',
        'ets_selesai' => '2026-04-09',
    ]);

    $payment = Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 30000,
        'total_biaya' => 30000,
        'metode_pembayaran' => 'qris',
        'tgl_pembayaran' => null,
        'catatan' => 'Menunggu QRIS',
        'status' => 'belum_bayar',
        'gateway_token' => 'edit-summary-token',
        'gateway_invoice_id' => 'INV-EDIT-SUMMARY',
        'gateway_reference' => 'REF-EDIT-SUMMARY',
        'gateway_request_date' => '2026-04-08',
        'gateway_expires_at' => now()->addMinutes(30),
        'gateway_status' => 'pending',
    ]);

    $this
        ->actingAs($user)
        ->get(route('pembayaran.edit', $payment))
        ->assertOk()
        ->assertSee('Nadia')
        ->assertSee('081855566677')
        ->assertSee('cuci premium')
        ->assertSee('Rp 30.000')
        ->assertSee('Flow QRIS')
        ->assertSee('Status QRIS');
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
        ->assertSee('data-dt-flyout-body', false)
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
    $rawActions = collect($rows)->pluck('actions')->implode(' | ');
    $actions = collect($rows)->pluck('actions')->map(fn (?string $value) => pembayaranTableText($value))->implode(' | ');
    $customers = collect($rows)->pluck('customer')->map(fn (?string $value) => pembayaranTableText($value))->implode(' | ');

    expect($customers)->toContain($needsPayment->nama);
    expect($customers)->toContain($withUnpaidPayment->nama);
    expect($customers)->not->toContain('Rina Luar');
    expect($actions)->toContain('Detail');
    expect($actions)->toContain('Bayar Sekarang');
    expect($actions)->toContain('Selesaikan Pembayaran');
    expect($rawActions)->toContain('data-detail-url');
    expect($rawActions)->toContain(route('laundry.preview', $needsPayment));
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

test('pembayaran store with qris creates checkout session and opens public checkout in a new tab', function () {
    $user = createPembayaranOwner();
    $toko = $user->toko;
    configureManagedQris($toko);

    [$klien, $jasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Bayu',
        'no_hp_klien' => '081711122233',
    ], [
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 12000,
    ]);

    $laundry = createPaymentLaundry($toko, $klien, $jasa, [
        'qty' => 2,
        'tanggal_dimulai' => '2026-04-08',
        'ets_selesai' => '2026-04-09',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('pembayaran.create'))
        ->post(route('pembayaran.store'), [
            'laundry_id' => $laundry->id,
            'metode_pembayaran' => 'qris',
            'catatan' => 'Checkout QRIS otomatis',
            'status' => 'belum_bayar',
        ]);

    $payment = Pembayaran::query()->where('laundry_id', $laundry->id)->firstOrFail();

    $response
        ->assertRedirect(route('pembayaran.show', $payment))
        ->assertSessionHas('open_new_tab_url', route('pembayaran.gateway.checkout', [
            'pembayaran' => $payment->id,
            'token' => $payment->gateway_token,
        ]))
        ->assertSessionHas('open_new_tab_name', config('payment_gateway.checkout_window_name', 'nyuci-qris-checkout'));

    $payment->refresh();

    expect($payment->tgl_pembayaran)->toBeNull();
    expect($payment->gateway_status)->toBe('pending');
    expect($payment->gateway_token)->not()->toBeNull();
    expect($payment->gateway_expires_at)->not()->toBeNull();
    expect(data_get($payment->gateway_payload, 'merchant_name'))->toBe('Toko QRIS');
});

test('pembayaran update with qris reuses active session then regenerates after expiry', function () {
    $user = createPembayaranOwner();
    $toko = $user->toko;
    configureManagedQris($toko);

    [$klien, $jasa] = createPaymentMasterData($toko, [
        'nama_klien' => 'Fikri',
        'no_hp_klien' => '081744455566',
    ], [
        'nama_jasa' => 'express',
        'satuan' => 'kg',
        'harga' => 18000,
    ]);

    $laundry = createPaymentLaundry($toko, $klien, $jasa, [
        'qty' => 1,
        'tanggal_dimulai' => '2026-04-08',
        'ets_selesai' => '2026-04-08',
    ]);

    $payment = Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 18000,
        'total_biaya' => 18000,
        'metode_pembayaran' => 'transfer',
        'tgl_pembayaran' => null,
        'catatan' => null,
        'status' => 'belum_bayar',
    ]);

    $firstResponse = $this
        ->actingAs($user)
        ->from(route('pembayaran.edit', $payment))
        ->put(route('pembayaran.update', $payment), [
            'laundry_id' => $laundry->id,
            'metode_pembayaran' => 'qris',
            'catatan' => 'Alih ke QRIS',
            'status' => 'belum_bayar',
        ]);

    $payment->refresh();
    $firstToken = $payment->gateway_token;

    $firstResponse
        ->assertRedirect(route('pembayaran.show', $payment))
        ->assertSessionHas('open_new_tab_url', route('pembayaran.gateway.checkout', [
            'pembayaran' => $payment->id,
            'token' => $firstToken,
        ]))
        ->assertSessionHas('open_new_tab_name', config('payment_gateway.checkout_window_name', 'nyuci-qris-checkout'));

    $secondResponse = $this
        ->actingAs($user)
        ->from(route('pembayaran.edit', $payment))
        ->put(route('pembayaran.update', $payment), [
            'laundry_id' => $laundry->id,
            'metode_pembayaran' => 'qris',
            'catatan' => 'Pakai sesi aktif',
            'status' => 'belum_bayar',
        ]);

    $payment->refresh();

    expect($payment->gateway_token)->toBe($firstToken);

    $secondResponse
        ->assertRedirect(route('pembayaran.show', $payment))
        ->assertSessionHas('open_new_tab_url', route('pembayaran.gateway.checkout', [
            'pembayaran' => $payment->id,
            'token' => $firstToken,
        ]))
        ->assertSessionHas('open_new_tab_name', config('payment_gateway.checkout_window_name', 'nyuci-qris-checkout'));

    $payment->forceFill([
        'gateway_status' => 'pending',
        'gateway_expires_at' => now()->subMinute(),
    ])->save();

    $thirdResponse = $this
        ->actingAs($user)
        ->from(route('pembayaran.edit', $payment))
        ->put(route('pembayaran.update', $payment), [
            'laundry_id' => $laundry->id,
            'metode_pembayaran' => 'qris',
            'catatan' => 'Generate sesi baru',
            'status' => 'belum_bayar',
        ]);

    $payment->refresh();

    expect($payment->gateway_token)->not()->toBe($firstToken);

    $thirdResponse
        ->assertRedirect(route('pembayaran.show', $payment))
        ->assertSessionHas('open_new_tab_url', route('pembayaran.gateway.checkout', [
            'pembayaran' => $payment->id,
            'token' => $payment->gateway_token,
        ]))
        ->assertSessionHas('open_new_tab_name', config('payment_gateway.checkout_window_name', 'nyuci-qris-checkout'));
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
    expect($payment->gateway_status)->toBeNull();
    expect($payment->gateway_paid_at)->toBeNull();
});
