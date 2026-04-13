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

test('laundry schema includes phase two columns', function () {
    expect(Schema::hasColumns('laundries', ['jenis_jasa', 'satuan', 'tgl_selesai']))->toBeTrue();
});

test('laundry index supports search, filters, and pagination', function () {
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

    $response = $this
        ->actingAs($user)
        ->get(route('laundry.index', [
            'search' => 'Budi',
            'status' => 'belum_selesai',
            'dibayar' => 'belum_bayar',
            'jasa_id' => $cuciJasa->id,
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
        ->assertDontSee('cuci-1');
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
