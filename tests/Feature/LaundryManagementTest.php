<?php

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use App\Notifications\LaundryFinishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function createLaundryOwner(): User
{
    $user = User::factory()->create();

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Nyuci Test',
        'alamat' => 'Jl. Test No. 1',
        'no_hp' => '081234567890',
    ]);

    return $user->fresh();
}

function createLaundryMasterData(Toko $toko, array $klienOverrides = [], array $jasaOverrides = []): array
{
    $klien = Klien::create(array_merge([
        'toko_id' => $toko->id,
        'nama_klien' => 'Klien Test',
        'email_klien' => null,
        'alamat_klien' => 'Jl. Klien Test',
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

function createLaundryRecord(Toko $toko, Klien $klien, Jasa $jasa, array $overrides = []): Laundry
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

function laundryDataTableColumn(string $data, string $name, bool $orderable = true, bool $searchable = true): array
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

function laundryDataTablePayload(array $columns, array $overrides = []): array
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

function laundryTableText(?string $value): string
{
    return trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')) ?? '');
}

test('laundry schema includes phase two columns', function () {
    expect(Schema::hasColumns('laundries', ['jenis_jasa', 'satuan', 'tgl_selesai']))->toBeTrue();
});

test('laundry index renders datatable shell and endpoint supports filters', function () {
    $user = createLaundryOwner();
    $toko = $user->toko;

    [$targetKlien, $cuciJasa] = createLaundryMasterData($toko, [
        'nama_klien' => 'Budi Santoso',
        'no_hp_klien' => '081111111111',
    ], [
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 8000,
    ]);

    $targetLaundry = createLaundryRecord($toko, $targetKlien, $cuciJasa, [
        'qty' => 3,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-08',
    ]);

    Pembayaran::create([
        'klien_id' => $targetKlien->id,
        'laundry_id' => $targetLaundry->id,
        'total' => 25000,
        'total_biaya' => 25000,
        'status' => 'belum_bayar',
    ]);

    [$secondaryKlien, $setrikaJasa] = createLaundryMasterData($toko, [
        'nama_klien' => 'Siti Aminah',
        'no_hp_klien' => '082222222222',
    ], [
        'nama_jasa' => 'setrika',
        'satuan' => 'pcs',
        'harga' => 5000,
    ]);

    createLaundryRecord($toko, $secondaryKlien, $setrikaJasa, [
        'qty' => 2,
        'status' => 'selesai',
        'tanggal_dimulai' => '2026-04-06',
        'ets_selesai' => '2026-04-09',
        'tgl_selesai' => '2026-04-09',
    ]);

    foreach (range(1, 10) as $index) {
        [$loopKlien] = createLaundryMasterData($toko, [
            'nama_klien' => "Laundry {$index}",
            'no_hp_klien' => '08333333333'.$index,
        ], [
            'nama_jasa' => 'cuci-'.$index,
            'satuan' => 'kg',
            'harga' => 7000,
        ]);

        createLaundryRecord($toko, $loopKlien, $cuciJasa, [
            'qty' => 1,
            'tanggal_dimulai' => '2026-04-05',
            'ets_selesai' => '2026-04-10',
        ]);
    }

    $foreignUser = User::factory()->create();
    $foreignToko = Toko::create([
        'user_id' => $foreignUser->id,
        'nama_toko' => 'Foreign Laundry',
        'alamat' => 'Jl. Luar',
        'no_hp' => '081255500000',
    ]);

    [$foreignKlien, $foreignJasa] = createLaundryMasterData($foreignToko, [
        'nama_klien' => 'Budi Luar',
        'no_hp_klien' => '086666666666',
    ], [
        'nama_jasa' => 'cuci luar',
        'satuan' => 'kg',
        'harga' => 9000,
    ]);

    createLaundryRecord($foreignToko, $foreignKlien, $foreignJasa, [
        'qty' => 2,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-08',
    ]);

    $page = $this
        ->actingAs($user)
        ->get(route('laundry.index'));

    $page
        ->assertOk()
        ->assertSee('Manage Laundry')
        ->assertSee('New')
        ->assertSee('Kelola Belum Bayar')
        ->assertSee('data-dt-action="pdf"', false)
        ->assertSee('data-dt-action="reload"', false)
        ->assertSee('laundry-table');

    $response = $this
        ->actingAs($user)
        ->getJson(route('laundry.data', laundryDataTablePayload([
            laundryDataTableColumn('customer', 'nama_klien'),
            laundryDataTableColumn('service', 'jasa'),
            laundryDataTableColumn('qty_display', 'qty'),
            laundryDataTableColumn('received_at', 'tanggal_dimulai'),
            laundryDataTableColumn('due_at', 'ets_selesai'),
            laundryDataTableColumn('status_badge', 'status'),
            laundryDataTableColumn('payment_badge', 'dibayar'),
            laundryDataTableColumn('actions', 'actions', false, false),
        ], [
            'search' => ['value' => 'Budi'],
            'status' => 'belum_selesai',
            'dibayar' => 'belum_bayar',
            'jasa_id' => $cuciJasa->id,
            'order' => [['column' => 0, 'dir' => 'asc']],
        ])));

    $response
        ->assertOk()
        ->assertJsonPath('recordsTotal', 12)
        ->assertJsonPath('recordsFiltered', 1);

    $row = $response->json('data.0');

    expect(laundryTableText($row['customer']))->toContain('Budi Santoso');
    expect(laundryTableText($row['customer']))->not->toContain('Budi Luar');
    expect(laundryTableText($row['payment_badge']))->toContain('Belum Bayar');
    expect(laundryTableText($row['actions']))->toContain('Selesaikan Pembayaran');
});

test('laundry status update route stores the finished date', function () {
    $user = createLaundryOwner();
    $toko = $user->toko;
    [$klien, $jasa] = createLaundryMasterData($toko, [
        'nama_klien' => 'Rina',
        'no_hp_klien' => '081999999999',
    ], [
        'nama_jasa' => 'keduanya',
        'satuan' => 'kg',
        'harga' => 10000,
    ]);

    $laundry = createLaundryRecord($toko, $klien, $jasa, [
        'qty' => 4,
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-09',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('laundry.index'))
        ->patch(route('laundry.status.update', $laundry), [
            'status_laundry_id' => $laundry->id,
            'status' => 'selesai',
            'tgl_selesai' => '2026-04-08',
        ]);

    $response
        ->assertRedirect(route('laundry.index'))
        ->assertSessionHas('success', 'Status laundry berhasil diperbarui.');

    $laundry->refresh();

    expect($laundry->is_taken)->toBeTrue();
    expect($laundry->status)->toBe('selesai');
    expect($laundry->tgl_selesai?->format('Y-m-d'))->toBe('2026-04-08');
});

test('laundry status update queues dashboard and customer notifications', function () {
    Notification::fake();

    $user = createLaundryOwner();
    $toko = $user->toko;
    [$klien, $jasa] = createLaundryMasterData($toko, [
        'nama_klien' => 'Bima',
        'email_klien' => 'bima@example.test',
        'no_hp_klien' => '081912345678',
    ], [
        'nama_jasa' => 'cuci express',
        'satuan' => 'kg',
        'harga' => 12000,
    ]);

    $laundry = createLaundryRecord($toko, $klien, $jasa, [
        'status' => 'proses',
        'tanggal_dimulai' => '2026-04-07',
        'ets_selesai' => '2026-04-09',
    ]);

    $this
        ->actingAs($user)
        ->from(route('laundry.index'))
        ->patch(route('laundry.status.update', $laundry), [
            'status_laundry_id' => $laundry->id,
            'status' => 'selesai',
            'tgl_selesai' => '2026-04-09',
        ])
        ->assertRedirect(route('laundry.index'))
        ->assertSessionHas('success', 'Status laundry berhasil diperbarui.');

    Notification::assertSentTo(
        $user,
        LaundryFinishedNotification::class,
        fn (LaundryFinishedNotification $notification, array $channels) => $channels === ['database']
    );

    Notification::assertSentTo(
        $klien,
        LaundryFinishedNotification::class,
        fn (LaundryFinishedNotification $notification, array $channels) => $channels === ['mail']
    );
});

test('laundry status update does not resend notification when order was already finished', function () {
    Notification::fake();

    $user = createLaundryOwner();
    $toko = $user->toko;
    [$klien, $jasa] = createLaundryMasterData($toko, [
        'nama_klien' => 'Nadia',
        'email_klien' => 'nadia@example.test',
        'no_hp_klien' => '081923456789',
    ]);

    $laundry = createLaundryRecord($toko, $klien, $jasa, [
        'status' => 'selesai',
        'tgl_selesai' => '2026-04-08',
    ]);

    $this
        ->actingAs($user)
        ->from(route('laundry.index'))
        ->patch(route('laundry.status.update', $laundry), [
            'status_laundry_id' => $laundry->id,
            'status' => 'selesai',
            'tgl_selesai' => '2026-04-08',
        ])
        ->assertRedirect(route('laundry.index'));

    Notification::assertNothingSent();
});

test('dashboard dropdown can mark notification as read', function () {
    $user = createLaundryOwner();
    $toko = $user->toko;
    [$klien, $jasa] = createLaundryMasterData($toko, [
        'nama_klien' => 'Salsa',
        'email_klien' => 'salsa@example.test',
        'no_hp_klien' => '081934567890',
    ]);

    $laundry = createLaundryRecord($toko, $klien, $jasa, [
        'status' => 'selesai',
        'tgl_selesai' => '2026-04-08',
    ]);

    $user->notifyNow(new LaundryFinishedNotification($laundry));

    $notification = $user->unreadNotifications()->firstOrFail();

    $this
        ->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Laundry selesai diproses')
        ->assertSee('Tandai dibaca');

    $this
        ->actingAs($user)
        ->from(route('dashboard'))
        ->patch(route('notifications.read', $notification))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Notifikasi ditandai sudah dibaca.');

    expect($notification->fresh()->read_at)->not()->toBeNull();
    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});
