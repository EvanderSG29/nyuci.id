<?php

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Database\Seeders\NyuciDemoSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createSeedTargetStore(string $storeName, string $email): Toko
{
    $user = User::factory()->create([
        'name' => $storeName.' Owner',
        'email' => $email,
    ]);

    return Toko::create([
        'user_id' => $user->id,
        'nama_toko' => $storeName,
        'alamat' => 'Jl. '.$storeName.' No. 1',
        'no_hp' => '0812'.str_pad((string) $user->id, 8, '0', STR_PAD_LEFT),
    ]);
}

function runNyuciDemoSeedCommand(): string
{
    $exitCode = Artisan::call('nyuci:seed-demo');

    expect($exitCode)->toBe(0);

    return Artisan::output();
}

function pembayaranCountForStore(Toko $toko): int
{
    return Pembayaran::query()
        ->whereHas('laundry', fn ($query) => $query->where('toko_id', $toko->id))
        ->count();
}

function seedLegacyGatewayConflict(Toko $toko): void
{
    $service = Jasa::create([
        'toko_id' => $toko->id,
        'nama_jasa' => 'Legacy Conflict Service',
        'satuan' => 'kg',
        'harga' => 12000,
    ]);

    $client = Klien::create([
        'toko_id' => $toko->id,
        'nama_klien' => 'Legacy Conflict Client',
        'email_klien' => 'legacy-conflict@example.test',
        'alamat_klien' => 'Jl. Legacy No. 1',
        'no_hp_klien' => '081234567890',
    ]);

    $laundry = Laundry::create([
        'toko_id' => $toko->id,
        'klien_id' => $client->id,
        'jasa_id' => $service->id,
        'qty' => 1.50,
        'status' => 'selesai',
        'tanggal_dimulai' => today()->subMonths(14)->toDateString(),
        'ets_selesai' => today()->subMonths(14)->addDays(2)->toDateString(),
        'nama' => $client->nama_klien,
        'no_hp' => $client->no_hp_klien,
        'berat' => 1.50,
        'tanggal' => today()->subMonths(14)->toDateString(),
        'layanan' => $service->nama_jasa,
        'jenis_jasa' => $service->nama_jasa,
        'satuan' => '1.5 kg',
        'estimasi_selesai' => today()->subMonths(14)->addDays(2)->toDateString(),
        'tgl_selesai' => today()->subMonths(14)->addDays(2)->toDateString(),
        'is_taken' => true,
    ]);

    Pembayaran::create([
        'laundry_id' => $laundry->id,
        'klien_id' => $client->id,
        'total' => 18000,
        'total_biaya' => 18000,
        'metode_pembayaran' => 'qris',
        'tgl_pembayaran' => today()->subMonths(14)->addDays(2)->toDateString(),
        'catatan' => 'Legacy demo record.',
        'status' => 'belum_bayar',
        'gateway_provider' => 'qris_static',
        'gateway_reference' => 'NYUCI-REF-'.$toko->id.'-02',
        'gateway_invoice_id' => 'NYUCI-INV-'.$toko->id.'-02',
        'gateway_token' => 'nyuci-demo-'.$toko->id.'-02',
        'gateway_payment_url' => 'https://nyuci.test/demo/checkout/nyuci-demo-'.$toko->id.'-02',
        'gateway_qr_image' => 'data:image/png;base64,'.base64_encode('QRIS-nyuci-demo-'.$toko->id.'-02'),
        'gateway_request_date' => today()->subMonths(14)->toDateString(),
        'gateway_expires_at' => today()->subMonths(14)->addDays(1)->startOfDay(),
        'gateway_status' => 'pending',
        'gateway_customer_name' => $client->nama_klien,
        'gateway_method_by' => 'qris',
        'gateway_payload' => [
            'source' => 'legacy_demo_seed',
            'store_id' => $toko->id,
        ],
    ]);
}

function assertSeededStoreCounts(Toko $toko): void
{
    expect(Jasa::query()->where('toko_id', $toko->id)->count())->toBe(8);
    expect(Klien::query()->where('toko_id', $toko->id)->count())->toBe(12);
    expect(Laundry::query()->where('toko_id', $toko->id)->count())->toBe(30);
    expect(pembayaranCountForStore($toko))->toBe(30);
}

test('nyuci seed demo command creates demo account and seeds all target stores', function () {
    $alphaStore = createSeedTargetStore('Alpha Laundry', 'alpha@example.test');
    $betaStore = createSeedTargetStore('Beta Laundry', 'beta@example.test');

    $output = runNyuciDemoSeedCommand();

    expect($output)->toContain('Dummy data laundry berhasil disinkronkan.');
    expect($output)->toContain('Alpha Laundry');
    expect($output)->toContain('Beta Laundry');
    expect($output)->toContain(NyuciDemoSeeder::DEMO_STORE_NAME);
    expect($output)->toContain('Demo login: '.NyuciDemoSeeder::DEMO_EMAIL.' / '.NyuciDemoSeeder::DEMO_PASSWORD);

    $demoUser = User::query()->where('email', NyuciDemoSeeder::DEMO_EMAIL)->first();

    expect($demoUser)->not->toBeNull();
    expect(Hash::check(NyuciDemoSeeder::DEMO_PASSWORD, $demoUser->password))->toBeTrue();

    $demoStore = Toko::query()
        ->where('user_id', $demoUser->id)
        ->where('nama_toko', NyuciDemoSeeder::DEMO_STORE_NAME)
        ->first();

    expect($demoStore)->not->toBeNull();
    expect(Toko::count())->toBe(3);

    assertSeededStoreCounts($alphaStore->fresh());
    assertSeededStoreCounts($betaStore->fresh());
    assertSeededStoreCounts($demoStore);
});

test('nyuci seed demo command is idempotent when executed twice', function () {
    createSeedTargetStore('Laundry Idempotent', 'idempotent@example.test');

    runNyuciDemoSeedCommand();

    $firstCounts = [
        'users' => User::count(),
        'tokos' => Toko::count(),
        'jasas' => Jasa::count(),
        'kliens' => Klien::count(),
        'laundries' => Laundry::count(),
        'pembayarans' => Pembayaran::count(),
    ];

    runNyuciDemoSeedCommand();

    $secondCounts = [
        'users' => User::count(),
        'tokos' => Toko::count(),
        'jasas' => Jasa::count(),
        'kliens' => Klien::count(),
        'laundries' => Laundry::count(),
        'pembayarans' => Pembayaran::count(),
    ];

    expect($secondCounts)->toEqual($firstCounts);
    expect(User::query()->where('email', NyuciDemoSeeder::DEMO_EMAIL)->count())->toBe(1);

    $duplicateLaundryKeys = DB::table('laundries')
        ->selectRaw('toko_id, klien_id, jasa_id, tanggal_dimulai, qty, count(*) as aggregate')
        ->groupBy('toko_id', 'klien_id', 'jasa_id', 'tanggal_dimulai', 'qty')
        ->havingRaw('count(*) > 1')
        ->count();

    expect($duplicateLaundryKeys)->toBe(0);
});

test('nyuci seed demo command clears legacy transactional rows before reseeding', function () {
    $store = createSeedTargetStore('Laundry Legacy Cleanup', 'legacy-cleanup@example.test');
    seedLegacyGatewayConflict($store);

    $output = runNyuciDemoSeedCommand();

    expect($output)->toContain('Dummy data laundry berhasil disinkronkan.');
    expect(Laundry::query()->where('toko_id', $store->id)->count())->toBe(30);
    expect(pembayaranCountForStore($store))->toBe(30);
    expect(Pembayaran::query()->where('gateway_invoice_id', 'NYUCI-INV-'.$store->id.'-02')->count())->toBe(1);
});

test('nyuci seed demo command creates the expected dataset distribution and totals', function () {
    $targetStore = createSeedTargetStore('Laundry Distribusi', 'distribusi@example.test');

    runNyuciDemoSeedCommand();

    $stores = Toko::query()->orderBy('id')->get();

    foreach ($stores as $store) {
        assertSeededStoreCounts($store);

        $laundries = Laundry::query()->where('toko_id', $store->id);
        $payments = Pembayaran::query()->whereHas('laundry', fn ($query) => $query->where('toko_id', $store->id));
        $monthCoverage = (clone $laundries)
            ->get(['tanggal_dimulai'])
            ->map(fn (Laundry $laundry) => Carbon::parse($laundry->tanggal_dimulai)->format('Y-m'))
            ->unique()
            ->values();

        expect($monthCoverage)->toHaveCount(12);
        expect((clone $laundries)->where('status', 'belum_selesai')->count())->toBeGreaterThan(0);
        expect((clone $laundries)->where('status', 'proses')->count())->toBeGreaterThan(0);
        expect((clone $laundries)->where('status', 'selesai')->count())->toBeGreaterThan(0);
        expect((clone $laundries)->whereBetween('tanggal_dimulai', [today()->subDays(29)->toDateString(), today()->toDateString()])->count())->toBeGreaterThanOrEqual(5);
        expect((clone $laundries)->distinct()->count('jasa_id'))->toBe(8);

        expect((clone $payments)->where('status', 'belum_bayar')->count())->toBeGreaterThan(0);
        expect((clone $payments)->where('status', 'sudah_bayar')->count())->toBeGreaterThan(0);

        $methods = (clone $payments)
            ->pluck('metode_pembayaran')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        expect($methods)->toEqual(['cash', 'ewallet', 'qris', 'transfer']);

        $storePayments = Pembayaran::query()
            ->whereHas('laundry', fn ($query) => $query->where('toko_id', $store->id))
            ->with('laundry.jasa')
            ->get();

        foreach ($storePayments as $payment) {
            $expectedTotal = (int) round(($payment->laundry?->qty ?? 0) * ($payment->laundry?->jasa?->harga ?? 0));

            expect($payment->total_biaya)->toBe($expectedTotal);
            expect($payment->total)->toBe($expectedTotal);
        }
    }

    assertSeededStoreCounts($targetStore->fresh());
});

test('nyuci seed demo command creates pending and paid qris samples per store', function () {
    createSeedTargetStore('Laundry QRIS', 'qris@example.test');

    runNyuciDemoSeedCommand();

    foreach (Toko::query()->orderBy('id')->get() as $store) {
        $pendingQris = Pembayaran::query()
            ->whereHas('laundry', fn ($query) => $query->where('toko_id', $store->id))
            ->where('metode_pembayaran', 'qris')
            ->where('status', 'belum_bayar')
            ->orderBy('id')
            ->get();

        $paidQris = Pembayaran::query()
            ->whereHas('laundry', fn ($query) => $query->where('toko_id', $store->id))
            ->where('metode_pembayaran', 'qris')
            ->where('status', 'sudah_bayar')
            ->orderBy('id')
            ->get();

        expect($pendingQris->count())->toBeGreaterThanOrEqual(2);
        expect($paidQris->count())->toBeGreaterThanOrEqual(2);

        foreach ($pendingQris as $payment) {
            expect($payment->gateway_provider)->toBe('qris_static');
            expect($payment->gateway_reference)->not->toBeNull();
            expect($payment->gateway_invoice_id)->not->toBeNull();
            expect($payment->gateway_token)->not->toBeNull();
            expect($payment->gateway_request_date)->not->toBeNull();
            expect($payment->gateway_expires_at)->not->toBeNull();
            expect($payment->gateway_status)->toBe('pending');
            expect($payment->gatewayHasSession())->toBeTrue();
            expect($payment->gatewaySessionIsActive())->toBeTrue();
        }

        foreach ($paidQris as $payment) {
            expect($payment->gateway_provider)->toBe('qris_static');
            expect($payment->gateway_status)->toBe('paid');
            expect($payment->gateway_paid_at)->not->toBeNull();
            expect($payment->gateway_token)->not->toBeNull();
            expect($payment->gateway_reference)->not->toBeNull();
            expect($payment->gateway_invoice_id)->not->toBeNull();
        }
    }
});
