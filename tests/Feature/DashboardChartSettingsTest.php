<?php

use App\Models\DashboardChartPreset;
use App\Models\DashboardChartUserOverride;
use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use App\Services\DashboardCharts\DashboardChartConfigResolver;
use App\Services\DashboardCharts\DashboardChartDatasetBuilder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

function createDashboardChartSettingsOwner(): User
{
    $user = User::factory()->create();

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Nyuci Dashboard',
        'alamat' => 'Jl. Dashboard No. 1',
        'no_hp' => '081200000222',
    ]);

    return $user->fresh();
}

function dashboardChartSlotPayload(string $slotKey, array $overrides = []): array
{
    $definition = DashboardChartConfigResolver::slotDefinitions()[$slotKey];

    return array_merge([
        'title' => $definition['title'],
        'subtitle' => $definition['subtitle'],
        'chart_type' => $definition['chart_type'],
        'period_granularity' => $definition['period_granularity'],
        'period_length' => $definition['period_length'],
        'primary_metric' => $definition['primary_metric'],
        'secondary_metric' => $definition['secondary_metric'],
        'accent_color' => $definition['accent_color'],
        'show_points' => $definition['show_points'],
        'show_previous_comparison' => $definition['show_previous_comparison'] ?? true,
    ], $overrides);
}

function dashboardChartDefaultsPayload(array $overrides = []): array
{
    $charts = [];

    foreach (DashboardChartConfigResolver::slotKeys() as $slotKey) {
        $charts[$slotKey] = dashboardChartSlotPayload($slotKey, $overrides[$slotKey] ?? []);
    }

    return ['charts' => $charts];
}

function dashboardChartOverridesPayload(array $overrides = []): array
{
    $payload = [];

    foreach (DashboardChartConfigResolver::slotKeys() as $slotKey) {
        $payload[$slotKey] = array_merge([
            'use_custom' => false,
        ], $overrides[$slotKey] ?? []);
    }

    return ['overrides' => $payload];
}

function seedDashboardChartRecords(Toko $toko): void
{
    $jasa = Jasa::create([
        'toko_id' => $toko->id,
        'nama_jasa' => 'cuci_express',
        'satuan' => 'kg',
        'harga' => 10000,
    ]);

    $klien = Klien::create([
        'toko_id' => $toko->id,
        'nama_klien' => 'Andi Dashboard',
        'alamat_klien' => 'Jl. Melati 1',
        'no_hp_klien' => '081111111111',
    ]);

    $laundryMarch4 = Laundry::create([
        'toko_id' => $toko->id,
        'klien_id' => $klien->id,
        'jasa_id' => $jasa->id,
        'qty' => 2,
        'status' => 'proses',
        'tanggal_dimulai' => '2026-03-04',
        'ets_selesai' => '2026-03-06',
        'nama' => $klien->nama_klien,
        'no_hp' => $klien->no_hp_klien,
        'berat' => 2,
        'satuan' => '2 kg',
        'tanggal' => '2026-03-04',
        'layanan' => $jasa->nama_jasa,
        'jenis_jasa' => $jasa->nama_jasa,
        'estimasi_selesai' => '2026-03-06',
        'is_taken' => false,
    ]);

    $laundryMarch5 = Laundry::create([
        'toko_id' => $toko->id,
        'klien_id' => $klien->id,
        'jasa_id' => $jasa->id,
        'qty' => 3,
        'status' => 'selesai',
        'tanggal_dimulai' => '2026-03-05',
        'ets_selesai' => '2026-03-06',
        'nama' => $klien->nama_klien,
        'no_hp' => $klien->no_hp_klien,
        'berat' => 3,
        'satuan' => '3 kg',
        'tanggal' => '2026-03-05',
        'layanan' => $jasa->nama_jasa,
        'jenis_jasa' => $jasa->nama_jasa,
        'estimasi_selesai' => '2026-03-06',
        'tgl_selesai' => '2026-03-05',
        'is_taken' => true,
    ]);

    $laundryApril2 = Laundry::create([
        'toko_id' => $toko->id,
        'klien_id' => $klien->id,
        'jasa_id' => $jasa->id,
        'qty' => 1,
        'status' => 'selesai',
        'tanggal_dimulai' => '2026-04-02',
        'ets_selesai' => '2026-04-03',
        'nama' => $klien->nama_klien,
        'no_hp' => $klien->no_hp_klien,
        'berat' => 1,
        'satuan' => '1 kg',
        'tanggal' => '2026-04-02',
        'layanan' => $jasa->nama_jasa,
        'jenis_jasa' => $jasa->nama_jasa,
        'estimasi_selesai' => '2026-04-03',
        'tgl_selesai' => '2026-04-02',
        'is_taken' => true,
    ]);

    Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundryMarch5->id,
        'total' => 30000,
        'total_biaya' => 30000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => '2026-03-05',
        'status' => 'sudah_bayar',
    ]);

    Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundryApril2->id,
        'total' => 10000,
        'total_biaya' => 10000,
        'metode_pembayaran' => 'qris',
        'tgl_pembayaran' => '2026-04-02',
        'status' => 'sudah_bayar',
    ]);
}

test('dashboard chart settings page seeds defaults and chart payloads remain structured', function () {
    Carbon::setTestNow('2026-04-02 09:00:00');

    $user = createDashboardChartSettingsOwner();
    $toko = $user->toko;

    $this
        ->actingAs($user)
        ->get(route('settings.dashboard'))
        ->assertOk()
        ->assertSee('Default Toko')
        ->assertSee('Preferensi Saya')
        ->assertSee('4 slot tetap: hero, card_1, card_2, card_3');

    expect(DashboardChartPreset::query()->where('toko_id', $toko->id)->count())->toBe(4);
    foreach (DashboardChartConfigResolver::slotKeys() as $slotKey) {
        expect(
            DashboardChartPreset::query()
                ->where('toko_id', $toko->id)
                ->where('slot_key', $slotKey)
                ->exists()
        )->toBeTrue();
    }

    seedDashboardChartRecords($toko);

    $payload = app(DashboardChartConfigResolver::class)->dashboardPayload(
        $toko,
        $user,
        app(DashboardChartDatasetBuilder::class),
    );

    expect($payload['heroChart']['chart']['data']['labels'])->toHaveCount(12);
    expect($payload['heroChart']['chart']['data']['datasets'])->toHaveCount(2);
    expect($payload['heroChart']['show_previous_comparison'])->toBeTrue();
    expect($payload['heroChart']['axes'])->toHaveKey('y1');
    expect($payload['heroChart']['summary_items'][0]['trend'])->not->toBeNull();
    expect($payload['cardCharts']['card_1']['chart']['data']['labels'])->toHaveCount(30);
    expect($payload['cardCharts']['card_1']['chart']['data']['datasets'][0]['meta'][0]['deltaText'])->toBeNull();
    expect($payload['cardCharts']['card_1']['chart']['data']['datasets'][0]['meta'][1]['deltaText'])->not->toBeNull();
    expect(collect($payload['cardCharts']['card_1']['chart']['data']['datasets'][0]['data'])->contains(fn ($value) => $value > 0))->toBeTrue();
    expect(collect($payload['cardCharts']['card_2']['chart']['data']['datasets'][0]['data'])->contains(fn ($value) => $value > 0))->toBeTrue();
    expect(collect($payload['cardCharts']['card_3']['chart']['data']['datasets'][0]['data'])->contains(fn ($value) => $value > 0))->toBeTrue();
});

test('dashboard chart comparison visibility is validated as boolean', function () {
    Carbon::setTestNow('2026-04-02 09:00:00');

    $user = createDashboardChartSettingsOwner();

    $this
        ->actingAs($user)
        ->get(route('settings.dashboard'))
        ->assertOk();

    $this
        ->actingAs($user)
        ->patch(route('pengaturan-dashboard.defaults.update'), dashboardChartDefaultsPayload([
            'hero' => [
                'show_previous_comparison' => 'maybe',
            ],
        ]))
        ->assertSessionHasErrors(['charts.hero.show_previous_comparison']);
});

test('dashboard chart defaults can be updated and reset back to seeded values', function () {
    Carbon::setTestNow('2026-04-02 09:00:00');

    $user = createDashboardChartSettingsOwner();
    $toko = $user->toko;

    $this
        ->actingAs($user)
        ->get(route('settings.dashboard'))
        ->assertOk();

    $this
        ->actingAs($user)
        ->patch(route('pengaturan-dashboard.defaults.update'), dashboardChartDefaultsPayload([
            'hero' => [
                'title' => 'Ringkasan Utama',
                'subtitle' => 'Versi toko',
                'show_previous_comparison' => false,
            ],
            'card_3' => [
                'title' => 'Kas Masuk',
            ],
        ]))
        ->assertRedirect(route('settings.dashboard'))
        ->assertSessionHas('status', 'dashboard-chart-defaults-updated');

    expect(DashboardChartPreset::query()->where('toko_id', $toko->id)->where('slot_key', 'hero')->value('title'))->toBe('Ringkasan Utama');
    expect(DashboardChartPreset::query()->where('toko_id', $toko->id)->where('slot_key', 'card_3')->value('title'))->toBe('Kas Masuk');
    expect(DashboardChartPreset::query()->where('toko_id', $toko->id)->where('slot_key', 'hero')->value('show_previous_comparison'))->toBeFalse();

    $this
        ->actingAs($user)
        ->post(route('pengaturan-dashboard.defaults.reset', 'hero'))
        ->assertRedirect(route('settings.dashboard'))
        ->assertSessionHas('status', 'dashboard-chart-default-reset');

    $resolver = app(DashboardChartConfigResolver::class);

    expect($resolver->presetsForStore($toko)->firstWhere('slot_key', 'hero')->title)->toBe('Dashboard');
    expect($resolver->presetsForStore($toko)->firstWhere('slot_key', 'hero')->show_previous_comparison)->toBeTrue();
});

test('dashboard chart overrides can be enabled and removed', function () {
    Carbon::setTestNow('2026-04-02 09:00:00');

    $user = createDashboardChartSettingsOwner();
    $toko = $user->toko;

    $this
        ->actingAs($user)
        ->get(route('settings.dashboard'))
        ->assertOk();

    $this
        ->actingAs($user)
        ->patch(route('pengaturan-dashboard.overrides.update'), dashboardChartOverridesPayload([
            'hero' => [
                'use_custom' => true,
                'title' => 'Dashboard Pribadi',
                'subtitle' => 'Hanya untuk saya',
                'chart_type' => 'line_area',
                'period_granularity' => 'month',
                'period_length' => 12,
                'primary_metric' => 'orders_created',
                'secondary_metric' => 'revenue_paid',
                'accent_color' => '#ffffff',
                'show_points' => true,
                'show_previous_comparison' => false,
            ],
        ]))
        ->assertRedirect(route('settings.dashboard'))
        ->assertSessionHas('status', 'dashboard-chart-overrides-updated');

    expect(DashboardChartUserOverride::query()->count())->toBe(1);
    expect(DashboardChartPreset::query()->where('toko_id', $toko->id)->where('slot_key', 'hero')->value('show_previous_comparison'))->toBeTrue();

    $effective = app(DashboardChartConfigResolver::class)->effectiveConfigs($toko, $user);

    expect($effective['hero']['title'])->toBe('Dashboard Pribadi');
    expect($effective['hero']['show_previous_comparison'])->toBeFalse();

    $this
        ->actingAs($user)
        ->delete(route('pengaturan-dashboard.overrides.destroy', 'hero'))
        ->assertRedirect(route('settings.dashboard'))
        ->assertSessionHas('status', 'dashboard-chart-override-deleted');

    expect(DashboardChartUserOverride::query()->count())->toBe(0);
    expect(app(DashboardChartConfigResolver::class)->effectiveConfigs($toko, $user)['hero']['title'])->toBe('Dashboard');
    expect(app(DashboardChartConfigResolver::class)->effectiveConfigs($toko, $user)['hero']['show_previous_comparison'])->toBeTrue();
});
