<?php

namespace App\Services\DashboardCharts;

use App\Models\DashboardChartPreset;
use App\Models\DashboardChartUserOverride;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardChartConfigResolver
{
    public const SLOT_KEYS = ['hero', 'card_1', 'card_2', 'card_3'];

    public const METRIC_OPTIONS = [
        'orders_created' => ['label' => 'Order Masuk', 'format' => 'number'],
        'orders_completed' => ['label' => 'Order Selesai', 'format' => 'number'],
        'revenue_paid' => ['label' => 'Revenue', 'format' => 'currency'],
        'orders_active' => ['label' => 'Order Aktif', 'format' => 'number'],
        'orders_due' => ['label' => 'Jatuh Tempo', 'format' => 'number'],
        'ready_pickup' => ['label' => 'Siap Diambil', 'format' => 'number'],
        'unpaid_count' => ['label' => 'Belum Bayar', 'format' => 'number'],
        'unpaid_value' => ['label' => 'Nilai Belum Lunas', 'format' => 'currency'],
        'active_customers' => ['label' => 'Pelanggan Aktif', 'format' => 'number'],
    ];

    public const PERIOD_LENGTH_OPTIONS = [
        'day' => [
            7 => '7 hari',
            14 => '14 hari',
            30 => '30 hari',
            90 => '90 hari',
        ],
        'month' => [
            6 => '6 bulan',
            12 => '12 bulan',
        ],
    ];

    public const COLOR_OPTIONS = [
        '#ffffff' => 'Putih',
        '#ff6d3a' => 'Oranye',
        '#10b981' => 'Hijau',
        '#4a7df0' => 'Biru',
        '#7c3aed' => 'Ungu',
        '#06b6d4' => 'Sian',
        '#f59e0b' => 'Amber',
    ];

    private const SLOT_DEFINITIONS = [
        'hero' => [
            'label' => 'Hero utama',
            'description' => 'Grafik inti dashboard dengan dua metrik dan area biru.',
            'title' => 'Dashboard',
            'subtitle' => 'Ringkasan 12 bulan terakhir',
            'chart_type' => 'line_area',
            'period_granularity' => 'month',
            'period_length' => 12,
            'primary_metric' => 'orders_created',
            'secondary_metric' => 'revenue_paid',
            'accent_color' => '#ffffff',
            'show_points' => true,
        ],
        'card_1' => [
            'label' => 'Order Masuk',
            'description' => 'Pergerakan order baru per periode.',
            'title' => 'Order Masuk',
            'subtitle' => 'Order baru per hari',
            'chart_type' => 'line',
            'period_granularity' => 'day',
            'period_length' => 30,
            'primary_metric' => 'orders_created',
            'secondary_metric' => null,
            'accent_color' => '#ff6d3a',
            'show_points' => true,
        ],
        'card_2' => [
            'label' => 'Order Selesai',
            'description' => 'Pengerjaan yang tuntas pada periode terpilih.',
            'title' => 'Order Selesai',
            'subtitle' => 'Order selesai per hari',
            'chart_type' => 'line',
            'period_granularity' => 'day',
            'period_length' => 30,
            'primary_metric' => 'orders_completed',
            'secondary_metric' => null,
            'accent_color' => '#10b981',
            'show_points' => true,
        ],
        'card_3' => [
            'label' => 'Revenue',
            'description' => 'Pendapatan lunas pada periode yang dipilih.',
            'title' => 'Revenue',
            'subtitle' => 'Pemasukan lunas per hari',
            'chart_type' => 'bar',
            'period_granularity' => 'day',
            'period_length' => 30,
            'primary_metric' => 'revenue_paid',
            'secondary_metric' => null,
            'accent_color' => '#4a7df0',
            'show_points' => false,
        ],
    ];

    public static function slotKeys(): array
    {
        return self::SLOT_KEYS;
    }

    public static function slotDefinitions(): array
    {
        return self::SLOT_DEFINITIONS;
    }

    public static function metricOptions(): array
    {
        return self::METRIC_OPTIONS;
    }

    public static function periodLengthOptions(): array
    {
        return self::PERIOD_LENGTH_OPTIONS;
    }

    public static function colorOptions(): array
    {
        return self::COLOR_OPTIONS;
    }

    public static function chartTypeOptions(string $slotKey): array
    {
        return $slotKey === 'hero'
            ? [
                'line_area' => 'Line + area',
                'line' => 'Line',
            ]
            : [
                'line' => 'Line',
                'bar' => 'Bar',
            ];
    }

    public function ensureDefaults(Toko $toko): Collection
    {
        foreach (self::slotKeys() as $slotKey) {
            DashboardChartPreset::query()->firstOrCreate(
                [
                    'toko_id' => $toko->id,
                    'slot_key' => $slotKey,
                ],
                $this->defaultPresetAttributes($slotKey, $toko)
            );
        }

        return $this->presetsForStore($toko);
    }

    public function presetsForStore(Toko $toko): Collection
    {
        return DashboardChartPreset::query()
            ->where('toko_id', $toko->id)
            ->get()
            ->sortBy(fn (DashboardChartPreset $preset): int => array_search($preset->slot_key, self::slotKeys(), true) ?: 0)
            ->values();
    }

    public function overridesForUser(Toko $toko, User $user): Collection
    {
        $presetIds = $this->presetsForStore($toko)->pluck('id');

        if ($presetIds->isEmpty()) {
            return collect();
        }

        return DashboardChartUserOverride::query()
            ->where('user_id', $user->id)
            ->whereIn('dashboard_chart_preset_id', $presetIds->all())
            ->get()
            ->keyBy('dashboard_chart_preset_id');
    }

    public function settingsPayload(Toko $toko, ?User $user = null): array
    {
        $presets = $this->ensureDefaults($toko)->keyBy('slot_key');
        $overrides = $user ? $this->overridesForUser($toko, $user) : collect();

        $slots = [];

        foreach (self::slotKeys() as $slotKey) {
            $preset = $presets->get($slotKey);
            $override = ($user && $preset) ? $overrides->get($preset->id) : null;
            $presetAttributes = $preset?->toArray() ?? $this->defaultPresetAttributes($slotKey, $toko);

            $slots[$slotKey] = [
                'definition' => self::slotDefinitions()[$slotKey],
                'preset' => $presetAttributes,
                'override' => $override?->toArray(),
                'effective' => $this->mergePresetAndOverride($presetAttributes, $override),
                'chart_type_options' => self::chartTypeOptions($slotKey),
            ];
        }

        return [
            'slots' => $slots,
            'metric_options' => self::metricOptions(),
            'period_length_options' => self::periodLengthOptions(),
            'color_options' => self::colorOptions(),
        ];
    }

    public function dashboardPayload(Toko $toko, ?User $user = null, ?DashboardChartDatasetBuilder $builder = null): array
    {
        $builder ??= new DashboardChartDatasetBuilder();
        $configs = $this->effectiveConfigs($toko, $user);
        $payloads = [];

        foreach ($configs as $slotKey => $config) {
            $payloads[$slotKey] = $builder->build($toko, $config);
        }

        return [
            'heroChart' => $payloads['hero'] ?? null,
            'cardCharts' => array_filter([
                'card_1' => $payloads['card_1'] ?? null,
                'card_2' => $payloads['card_2'] ?? null,
                'card_3' => $payloads['card_3'] ?? null,
            ]),
            'effectiveConfigs' => $configs,
        ];
    }

    public function effectiveConfigs(Toko $toko, ?User $user = null): Collection
    {
        $presets = $this->ensureDefaults($toko)->keyBy('slot_key');
        $overrides = $user ? $this->overridesForUser($toko, $user) : collect();

        return collect(self::slotKeys())->mapWithKeys(function (string $slotKey) use ($presets, $overrides, $toko, $user): array {
            $preset = $presets->get($slotKey);
            $override = ($user && $preset) ? $overrides->get($preset->id) : null;
            $presetAttributes = $preset?->toArray() ?? $this->defaultPresetAttributes($slotKey, $toko);

            return [
                $slotKey => $this->mergePresetAndOverride($presetAttributes, $override),
            ];
        });
    }

    public function updateDefaults(Toko $toko, array $charts): Collection
    {
        foreach (self::slotKeys() as $slotKey) {
            $attributes = $this->defaultPresetAttributes($slotKey, $toko);

            if (isset($charts[$slotKey]) && is_array($charts[$slotKey])) {
                $attributes = array_merge($attributes, $this->normalisePayload($slotKey, $charts[$slotKey]));
            }

            DashboardChartPreset::query()->updateOrCreate(
                [
                    'toko_id' => $toko->id,
                    'slot_key' => $slotKey,
                ],
                $attributes
            );
        }

        return $this->presetsForStore($toko);
    }

    public function updateOverrides(Toko $toko, User $user, array $charts): void
    {
        $presets = $this->presetsForStore($toko)->keyBy('slot_key');

        foreach (self::slotKeys() as $slotKey) {
            $preset = $presets->get($slotKey);

            if (! $preset) {
                continue;
            }

            $payload = $charts[$slotKey] ?? [];
            $useCustom = filter_var($payload['use_custom'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $override = DashboardChartUserOverride::query()->firstOrNew([
                'dashboard_chart_preset_id' => $preset->id,
                'user_id' => $user->id,
            ]);

            if (! $useCustom) {
                $override->delete();

                continue;
            }

            $attributes = $this->normalisePayload($slotKey, $payload);

            $override->fill([
                'use_custom' => true,
                'title_override' => $attributes['title'],
                'subtitle_override' => $attributes['subtitle'],
                'chart_type_override' => $attributes['chart_type'],
                'period_granularity_override' => $attributes['period_granularity'],
                'period_length_override' => $attributes['period_length'],
                'primary_metric_override' => $attributes['primary_metric'],
                'secondary_metric_override' => $attributes['secondary_metric'],
                'accent_color_override' => $attributes['accent_color'],
                'show_points_override' => $attributes['show_points'],
            ])->save();
        }
    }

    public function deleteOverride(Toko $toko, User $user, string $slotKey): void
    {
        $preset = $this->presetsForStore($toko)->firstWhere('slot_key', $slotKey);

        if (! $preset) {
            return;
        }

        DashboardChartUserOverride::query()
            ->where('dashboard_chart_preset_id', $preset->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    public function resetPreset(Toko $toko, string $slotKey): DashboardChartPreset
    {
        return DashboardChartPreset::query()->updateOrCreate(
            [
                'toko_id' => $toko->id,
                'slot_key' => $slotKey,
            ],
            $this->defaultPresetAttributes($slotKey, $toko)
        );
    }

    public function defaultPresetAttributes(string $slotKey, ?Toko $toko = null): array
    {
        $definition = self::slotDefinitions()[$slotKey] ?? self::slotDefinitions()['hero'];

        return [
            'toko_id' => $toko?->id,
            'slot_key' => $slotKey,
            'title' => $definition['title'],
            'subtitle' => $definition['subtitle'],
            'chart_type' => $definition['chart_type'],
            'period_granularity' => $definition['period_granularity'],
            'period_length' => $definition['period_length'],
            'primary_metric' => $definition['primary_metric'],
            'secondary_metric' => $definition['secondary_metric'],
            'accent_color' => $definition['accent_color'],
            'show_points' => $definition['show_points'],
        ];
    }

    private function mergePresetAndOverride(array $preset, ?DashboardChartUserOverride $override): array
    {
        if (! $override || ! $override->use_custom) {
            return $preset;
        }

        $overrideValues = [
            'title' => $override->title_override,
            'subtitle' => $override->subtitle_override,
            'chart_type' => $override->chart_type_override,
            'period_granularity' => $override->period_granularity_override,
            'period_length' => $override->period_length_override,
            'primary_metric' => $override->primary_metric_override,
            'secondary_metric' => $override->secondary_metric_override,
            'accent_color' => $override->accent_color_override,
            'show_points' => $override->show_points_override,
        ];

        foreach ($overrideValues as $key => $value) {
            if ($value !== null && $value !== '') {
                $preset[$key] = $value;
            }
        }

        return $preset;
    }

    private function normalisePayload(string $slotKey, array $payload): array
    {
        $default = $this->defaultPresetAttributes($slotKey);

        $title = trim((string) ($payload['title'] ?? $default['title']));
        $subtitle = trim((string) ($payload['subtitle'] ?? ''));
        $secondaryMetric = trim((string) ($payload['secondary_metric'] ?? ''));
        $accentColor = trim((string) ($payload['accent_color'] ?? ''));

        return [
            'title' => $title !== '' ? $title : $default['title'],
            'subtitle' => $subtitle !== '' ? $subtitle : null,
            'chart_type' => (string) ($payload['chart_type'] ?? $default['chart_type']),
            'period_granularity' => (string) ($payload['period_granularity'] ?? $default['period_granularity']),
            'period_length' => (int) ($payload['period_length'] ?? $default['period_length']),
            'primary_metric' => (string) ($payload['primary_metric'] ?? $default['primary_metric']),
            'secondary_metric' => $secondaryMetric !== '' ? $secondaryMetric : null,
            'accent_color' => $accentColor !== '' ? $accentColor : $default['accent_color'],
            'show_points' => filter_var($payload['show_points'] ?? $default['show_points'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default['show_points'],
        ];
    }
}
