<?php

namespace App\Services\DashboardCharts;

use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardChartDatasetBuilder
{
    /**
     * @var array<string, int>
     */
    private array $cache = [];

    public function build(Toko $toko, array $config): array
    {
        $granularity = $config['period_granularity'] ?? 'day';
        $length = (int) ($config['period_length'] ?? 30);
        $range = $this->buildRange($granularity, $length);
        $accentColor = $config['accent_color'] ?? '#4a7df0';
        $primaryMetric = (string) ($config['primary_metric'] ?? 'orders_created');
        $secondaryMetric = isset($config['secondary_metric']) && $config['secondary_metric'] !== ''
            ? (string) $config['secondary_metric']
            : null;
        $showPreviousComparison = (bool) ($config['show_previous_comparison'] ?? true);

        $series = collect(array_filter([
            $this->buildSeries($toko, $primaryMetric, $range, $accentColor, true, $showPreviousComparison),
            $secondaryMetric ? $this->buildSeries($toko, $secondaryMetric, $range, $this->secondaryColor($accentColor), false, $showPreviousComparison) : null,
        ]))->values();

        $series = $this->assignAxes($series);
        $chartType = $config['chart_type'] ?? 'line';

        return [
            'slot_key' => $config['slot_key'],
            'title' => $config['title'] ?? '',
            'subtitle' => $config['subtitle'] ?? null,
            'accent_color' => $accentColor,
            'chart_variant' => $chartType,
            'chart_type' => $chartType === 'bar' ? 'bar' : 'line',
            'surface' => $config['slot_key'] === 'hero' ? 'hero' : 'surface',
            'show_points' => (bool) ($config['show_points'] ?? true),
            'show_previous_comparison' => $showPreviousComparison,
            'period_label' => $range['label'],
            'summary_items' => $series->map(fn (array $item): array => $item['summary'])->all(),
            'has_data' => $series->pluck('data')->flatten()->contains(fn ($value) => is_numeric($value) && (float) $value !== 0.0),
            'axes' => $this->axesForSeries($series->all()),
            'chart' => [
                'type' => $chartType === 'bar' ? 'bar' : 'line',
                'showLegend' => false,
                'data' => [
                    'labels' => array_map(fn (array $bucket): string => $bucket['label'], $range['buckets']),
                    'datasets' => $series->map(function (array $seriesItem) use ($chartType): array {
                        $dataset = Arr::only($seriesItem, [
                            'label',
                            'metric_key',
                            'metric_format',
                            'axis_id',
                            'data',
                            'meta',
                            'borderColor',
                            'backgroundColor',
                            'pointBackgroundColor',
                            'pointBorderColor',
                            'fill',
                            'tension',
                            'borderWidth',
                            'pointRadius',
                            'pointHoverRadius',
                            'pointHitRadius',
                            'borderDash',
                        ]);

                        if ($chartType === 'bar') {
                            $dataset['borderRadius'] = 10;
                            $dataset['barPercentage'] = 0.78;
                            $dataset['categoryPercentage'] = 0.8;
                            $dataset['fill'] = false;
                        }

                        return $dataset;
                    })->all(),
                ],
            ],
        ];
    }

    private function buildSeries(Toko $toko, string $metricKey, array $range, string $accentColor, bool $isPrimary, bool $showPreviousComparison): array
    {
        $definition = DashboardChartConfigResolver::metricOptions()[$metricKey] ?? DashboardChartConfigResolver::metricOptions()['orders_created'];
        $values = [];

        foreach ($range['buckets'] as $bucket) {
            $values[] = $this->resolveMetricValue($toko, $metricKey, $bucket['start'], $bucket['end']);
        }

        $meta = [];

        foreach ($values as $index => $value) {
            $previous = $values[$index - 1] ?? null;
            $meta[] = $this->pointMeta($metricKey, $value, $previous, $range['buckets'][$index]['label'], $showPreviousComparison);
        }

        $summaryMode = $this->summaryModeForMetric($metricKey);
        $summaryValue = $summaryMode === 'sum'
            ? array_sum($values)
            : ($values !== [] ? $values[array_key_last($values)] : 0);

        return [
            'label' => $definition['label'],
            'metric_key' => $metricKey,
            'metric_format' => $definition['format'],
            'axis_id' => 'y',
            'data' => $values,
            'meta' => $meta,
            'borderColor' => $accentColor,
            'backgroundColor' => $this->backgroundColor($accentColor, $isPrimary),
            'pointBackgroundColor' => $accentColor,
            'pointBorderColor' => $accentColor,
            'fill' => $isPrimary,
            'tension' => 0.38,
            'borderWidth' => $isPrimary ? 3 : 2,
            'pointRadius' => 3,
            'pointHoverRadius' => 5,
            'pointHitRadius' => 12,
            'borderDash' => [],
            'summary' => [
                'label' => $definition['label'],
                'value' => $this->formatMetricValue($metricKey, (int) $summaryValue),
                'caption' => $summaryMode === 'sum' ? 'Total periode' : 'Posisi terakhir',
                'trend' => $this->trendText($metricKey, $values, $showPreviousComparison),
            ],
        ];
    }

    private function assignAxes(Collection $series): Collection
    {
        $formats = $series->pluck('metric_format')->unique()->values();

        if ($formats->count() <= 1) {
            return $series->map(function (array $item): array {
                $item['axis_id'] = 'y';

                return $item;
            });
        }

        $primaryFormat = $formats[0];

        return $series->map(function (array $item) use ($primaryFormat): array {
            $item['axis_id'] = $item['metric_format'] === $primaryFormat ? 'y' : 'y1';

            return $item;
        });
    }

    private function axesForSeries(array $series): array
    {
        $axes = [
            'y' => 'number',
        ];

        foreach ($series as $item) {
            if (($item['axis_id'] ?? 'y') === 'y1') {
                $axes['y1'] = $item['metric_format'] ?? 'number';
            }
        }

        return $axes;
    }

    private function buildRange(string $granularity, int $length): array
    {
        $length = max($length, 1);
        $now = CarbonImmutable::now()->startOfDay();
        $buckets = [];

        if ($granularity === 'month') {
            $start = $now->startOfMonth()->subMonths(max($length - 1, 0));

            for ($index = 0; $index < $length; $index++) {
                $bucketStart = $start->addMonthsNoOverflow($index)->startOfMonth();
                $buckets[] = [
                    'start' => $bucketStart,
                    'end' => $bucketStart->endOfMonth(),
                    'label' => $bucketStart->translatedFormat('M Y'),
                ];
            }

            return [
                'label' => sprintf(
                    '%s - %s',
                    $buckets[0]['start']->translatedFormat('M Y'),
                    $buckets[array_key_last($buckets)]['end']->translatedFormat('M Y')
                ),
                'buckets' => $buckets,
            ];
        }

        $start = $now->subDays(max($length - 1, 0));

        for ($index = 0; $index < $length; $index++) {
            $bucketStart = $start->addDays($index)->startOfDay();
            $buckets[] = [
                'start' => $bucketStart,
                'end' => $bucketStart->endOfDay(),
                'label' => $bucketStart->translatedFormat('d M'),
            ];
        }

        return [
            'label' => sprintf(
                '%s - %s',
                $buckets[0]['start']->translatedFormat('d M'),
                $buckets[array_key_last($buckets)]['end']->translatedFormat('d M')
            ),
            'buckets' => $buckets,
        ];
    }

    private function resolveMetricValue(Toko $toko, string $metricKey, CarbonImmutable $start, CarbonImmutable $end): int
    {
        $cacheKey = implode('|', [
            $toko->id,
            $metricKey,
            $start->toDateString(),
            $end->toDateString(),
        ]);

        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        $value = match ($metricKey) {
            'orders_created' => $this->dateRangeQuery(
                Laundry::query()->where('toko_id', $toko->id),
                'tanggal_dimulai',
                $start,
                $end,
            )->count(),
            'orders_completed' => $this->dateRangeQuery(
                Laundry::query()
                    ->where('toko_id', $toko->id)
                    ->where('status', 'selesai'),
                'tgl_selesai',
                $start,
                $end,
            )->count(),
            'revenue_paid' => (int) $this->dateRangeQuery(
                Pembayaran::query()
                    ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
                    ->where('laundries.toko_id', $toko->id)
                    ->where('pembayarans.status', 'sudah_bayar'),
                'pembayarans.tgl_pembayaran',
                $start,
                $end,
            )->sum(DB::raw('coalesce(pembayarans.total_biaya, pembayarans.total, 0)')),
            'orders_active' => Laundry::query()
                ->where('toko_id', $toko->id)
                ->whereDate('tanggal_dimulai', '<=', $end->toDateString())
                ->where(function ($query) use ($end): void {
                    $query
                        ->whereNull('tgl_selesai')
                        ->orWhereDate('tgl_selesai', '>', $end->toDateString())
                        ->orWhere('status', '!=', 'selesai');
                })
                ->count(),
            'orders_due' => Laundry::query()
                ->where('toko_id', $toko->id)
                ->whereDate('tanggal_dimulai', '<=', $end->toDateString())
                ->whereDate('ets_selesai', '<=', $end->toDateString())
                ->where('status', '!=', 'selesai')
                ->count(),
            'ready_pickup' => Laundry::query()
                ->where('toko_id', $toko->id)
                ->where('status', 'selesai')
                ->where('is_taken', false)
                ->whereDate('tgl_selesai', '<=', $end->toDateString())
                ->count(),
            'unpaid_count' => Laundry::query()
                ->where('toko_id', $toko->id)
                ->whereDate('tanggal_dimulai', '<=', $end->toDateString())
                ->where(function ($query): void {
                    $query
                        ->whereDoesntHave('pembayaran')
                        ->orWhereHas('pembayaran', fn ($paymentQuery) => $paymentQuery->where('status', 'belum_bayar'));
                })
                ->count(),
            'unpaid_value' => (int) Laundry::query()
                ->leftJoin('pembayarans', 'pembayarans.laundry_id', '=', 'laundries.id')
                ->leftJoin('jasas', 'jasas.id', '=', 'laundries.jasa_id')
                ->where('laundries.toko_id', $toko->id)
                ->whereDate('laundries.tanggal_dimulai', '<=', $end->toDateString())
                ->where(function ($query): void {
                    $query
                        ->whereNull('pembayarans.id')
                        ->orWhere('pembayarans.status', 'belum_bayar');
                })
                ->sum(DB::raw('coalesce(pembayarans.total_biaya, round(laundries.qty * jasas.harga), 0)')),
            'active_customers' => Laundry::query()
                ->where('toko_id', $toko->id)
                ->whereDate('tanggal_dimulai', '<=', $end->toDateString())
                ->select('klien_id')
                ->distinct()
                ->count('klien_id'),
            default => 0,
        };

        $this->cache[$cacheKey] = $value;

        return $value;
    }

    private function dateRangeQuery(Builder $query, string $column, CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return $query
            ->whereDate($column, '>=', $start->toDateString())
            ->whereDate($column, '<=', $end->toDateString());
    }

    private function summaryModeForMetric(string $metricKey): string
    {
        return in_array($metricKey, ['orders_active', 'orders_due', 'ready_pickup', 'unpaid_count', 'unpaid_value', 'active_customers'], true)
            ? 'last'
            : 'sum';
    }

    private function formatMetricValue(string $metricKey, int $value): string
    {
        return in_array($metricKey, ['revenue_paid', 'unpaid_value'], true)
            ? 'Rp '.number_format($value, 0, ',', '.')
            : number_format($value, 0, ',', '.');
    }

    private function trendText(string $metricKey, array $values, bool $showPreviousComparison): ?string
    {
        if (! $showPreviousComparison || count($values) < 2) {
            return null;
        }

        $lastIndex = array_key_last($values);
        $previous = (int) ($values[$lastIndex - 1] ?? 0);
        $current = (int) ($values[$lastIndex] ?? 0);
        $delta = $current - $previous;
        $deltaPercent = $previous !== 0 ? round(($delta / abs($previous)) * 100, 1) : null;
        $deltaValue = $this->formatMetricValue($metricKey, abs($delta));
        $sign = $delta >= 0 ? '+' : '-';

        if ($deltaPercent === null) {
            return sprintf('%s%s vs sebelumnya', $sign, $deltaValue);
        }

        return sprintf(
            '%s%s (%s%s%%) vs sebelumnya',
            $sign,
            $deltaValue,
            $delta >= 0 ? '+' : '-',
            abs($deltaPercent)
        );
    }

    private function pointMeta(string $metricKey, int $value, ?int $previous, string $label, bool $showPreviousComparison): array
    {
        $delta = $previous === null ? null : $value - $previous;
        $deltaPercent = $previous !== null && $previous !== 0
            ? round((($value - $previous) / abs($previous)) * 100, 1)
            : null;

        return [
            'label' => $label,
            'formattedValue' => $this->formatMetricValue($metricKey, $value),
            'rawValue' => $value,
            'deltaAbs' => $delta,
            'deltaPercent' => $deltaPercent,
            'deltaText' => ! $showPreviousComparison || $delta === null
                ? null
                : sprintf(
                    '%s%s (%s%s%%) vs sebelumnya',
                    $delta >= 0 ? '+' : '-',
                    $this->formatMetricValue($metricKey, abs($delta)),
                    $delta >= 0 ? '+' : '-',
                    abs($deltaPercent ?? 0)
                ),
        ];
    }

    private function backgroundColor(string $accentColor, bool $isPrimary): string
    {
        $alpha = $isPrimary ? 0.22 : 0.16;

        return $this->rgba($accentColor, $alpha);
    }

    private function secondaryColor(string $accentColor): string
    {
        if (in_array(strtolower($accentColor), ['#fff', '#ffffff'], true)) {
            return 'rgba(219, 234, 254, 0.86)';
        }

        return $this->mixWithWhite($accentColor, 0.56);
    }

    private function rgba(string $hex, float $alpha): string
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6) {
            return sprintf('rgba(255, 255, 255, %.2f)', $alpha);
        }

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return sprintf('rgba(%d, %d, %d, %.2f)', $red, $green, $blue, $alpha);
    }

    private function mixWithWhite(string $hex, float $ratio): string
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6) {
            return '#dbeafe';
        }

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        $mix = static function (int $channel) use ($ratio): int {
            return (int) round($channel + ((255 - $channel) * $ratio));
        };

        return sprintf('#%02x%02x%02x', $mix($red), $mix($green), $mix($blue));
    }
}
