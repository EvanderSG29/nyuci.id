<?php

namespace App\Http\Controllers;

use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('toko');
        $toko = $user->toko;

        if (! $toko) {
            return view('dashboard', [
                'toko' => null,
                'overview' => $this->emptyOverview(),
                'highlights' => collect(),
                'trend' => $this->emptyTrend(),
                'statusBreakdown' => $this->emptyBreakdown('Status order'),
                'paymentBreakdown' => $this->emptyBreakdown('Metode pembayaran'),
                'topServices' => collect(),
                'recentLaundries' => collect(),
            ]);
        }

        $today = CarbonImmutable::now()->startOfDay();
        $trendStart = $today->subDays(13);
        $trendEnd = $today;

        $totalLaundry = $toko->laundries()->count();
        $pendingLaundry = $toko->laundries()->where('status', '!=', 'selesai')->count();
        $totalPelanggan = Klien::query()->where('toko_id', $toko->id)->count();
        $paidCount = Pembayaran::query()
            ->whereHas('laundry', fn ($query) => $query->where('toko_id', $toko->id))
            ->where('status', 'sudah_bayar')
            ->count();
        $monthlyRevenue = (int) Pembayaran::query()
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->where('laundries.toko_id', $toko->id)
            ->where('pembayarans.status', 'sudah_bayar')
            ->whereBetween('pembayarans.tgl_pembayaran', [$today->startOfMonth()->toDateString(), $today->endOfMonth()->toDateString()])
            ->sum(DB::raw('coalesce(pembayarans.total_biaya, pembayarans.total, 0)'));
        $activeCustomers = (int) Laundry::query()
            ->where('toko_id', $toko->id)
            ->where('tanggal_dimulai', '>=', $today->subDays(29)->toDateString())
            ->distinct('klien_id')
            ->count('klien_id');
        $dueToday = $toko->laundries()
            ->where('status', '!=', 'selesai')
            ->whereDate('ets_selesai', '<=', $today->toDateString())
            ->count();
        $readyPickup = $toko->laundries()
            ->where('status', 'selesai')
            ->count();
        $unpaidCount = Laundry::query()
            ->where('toko_id', $toko->id)
            ->where(function ($query): void {
                $query
                    ->whereDoesntHave('pembayaran')
                    ->orWhereHas('pembayaran', fn ($paymentQuery) => $paymentQuery->where('status', 'belum_bayar'));
            })
            ->count();
        $unpaidValue = (int) Laundry::query()
            ->leftJoin('pembayarans', 'pembayarans.laundry_id', '=', 'laundries.id')
            ->leftJoin('jasas', 'jasas.id', '=', 'laundries.jasa_id')
            ->where('laundries.toko_id', $toko->id)
            ->where(function ($query): void {
                $query
                    ->whereNull('pembayarans.id')
                    ->orWhere('pembayarans.status', 'belum_bayar');
            })
            ->sum(DB::raw('coalesce(pembayarans.total_biaya, round(laundries.qty * jasas.harga), 0)'));
        $ordersToday = $toko->laundries()
            ->whereDate('tanggal_dimulai', $today->toDateString())
            ->count();
        $completionRate = $totalLaundry > 0
            ? (int) round((($totalLaundry - $pendingLaundry) / $totalLaundry) * 100)
            : 0;

        $overview = [
            'headline' => $totalLaundry > 0
                ? sprintf('%d order masih aktif dan %d tagihan butuh tindakan hari ini.', $pendingLaundry, $unpaidCount)
                : 'Belum ada order tercatat. Tambahkan laundry pertama untuk mulai membaca performa toko.',
            'miniStats' => [
                ['label' => 'Masuk hari ini', 'value' => (string) $ordersToday],
                ['label' => 'Jatuh tempo', 'value' => (string) $dueToday],
                ['label' => 'Siap diambil', 'value' => (string) $readyPickup],
                ['label' => 'Selesai', 'value' => $completionRate.'%'],
            ],
            'attentionLine' => $unpaidCount > 0
                ? sprintf('%d transaksi belum beres dengan potensi nilai %s.', $unpaidCount, $this->formatCurrency($unpaidValue))
                : 'Tidak ada tagihan tertunda. Arus kas toko sedang bersih.',
            'paidCount' => $paidCount,
            'unpaidValue' => $this->formatCurrency($unpaidValue),
        ];

        $highlights = collect([
            [
                'label' => 'Total order',
                'value' => number_format($totalLaundry, 0, ',', '.'),
                'caption' => 'Semua laundry yang pernah masuk ke toko ini.',
            ],
            [
                'label' => 'Pendapatan bulan ini',
                'value' => $this->formatCurrency($monthlyRevenue),
                'caption' => 'Akumulasi pembayaran lunas pada bulan berjalan.',
            ],
            [
                'label' => 'Order aktif',
                'value' => number_format($pendingLaundry, 0, ',', '.'),
                'caption' => 'Masih perlu proses, pengecekan, atau penyerahan.',
            ],
            [
                'label' => 'Pelanggan aktif',
                'value' => number_format($activeCustomers, 0, ',', '.'),
                'caption' => 'Pelanggan yang bertransaksi dalam 30 hari terakhir.',
            ],
        ]);

        return view('dashboard', [
            'toko' => $toko,
            'overview' => $overview,
            'highlights' => $highlights,
            'trend' => $this->buildTrend($toko->id, $trendStart, $trendEnd),
            'statusBreakdown' => $this->buildStatusBreakdown($toko->id),
            'paymentBreakdown' => $this->buildPaymentBreakdown($toko->id),
            'topServices' => $this->buildTopServices($toko->id, $totalLaundry),
            'recentLaundries' => $toko->laundries()->with(['klien', 'jasa', 'pembayaran'])->latest()->take(6)->get(),
        ]);
    }

    private function buildTrend(int $tokoId, CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $period = collect(range(0, $startDate->diffInDays($endDate)))
            ->map(fn (int $offset) => $startDate->addDays($offset));

        $orderCounts = Laundry::query()
            ->where('toko_id', $tokoId)
            ->whereBetween('tanggal_dimulai', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('date(tanggal_dimulai) as period_date, count(*) as aggregate_total')
            ->groupBy('period_date')
            ->pluck('aggregate_total', 'period_date');

        $finishedCounts = Laundry::query()
            ->where('toko_id', $tokoId)
            ->where('status', 'selesai')
            ->whereBetween('tgl_selesai', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('date(tgl_selesai) as period_date, count(*) as aggregate_total')
            ->groupBy('period_date')
            ->pluck('aggregate_total', 'period_date');

        $revenueTotals = Pembayaran::query()
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->where('laundries.toko_id', $tokoId)
            ->where('pembayarans.status', 'sudah_bayar')
            ->whereBetween('pembayarans.tgl_pembayaran', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('date(pembayarans.tgl_pembayaran) as period_date, sum(coalesce(pembayarans.total_biaya, pembayarans.total, 0)) as aggregate_total')
            ->groupBy('period_date')
            ->pluck('aggregate_total', 'period_date');

        $points = $period->values()->map(function (CarbonImmutable $date) use ($orderCounts, $finishedCounts, $revenueTotals): array {
            $key = $date->toDateString();

            return [
                'label' => $date->translatedFormat('d M'),
                'shortLabel' => $date->format('d/m'),
                'orders' => (int) ($orderCounts[$key] ?? 0),
                'finished' => (int) ($finishedCounts[$key] ?? 0),
                'revenue' => (int) round($revenueTotals[$key] ?? 0),
            ];
        });

        $chart = $this->buildLineChart($points);
        $axisLabels = $points
            ->filter(fn (array $point, int $index) => in_array($index, [0, 3, 6, 9, $points->count() - 1], true))
            ->values()
            ->pluck('shortLabel');

        return [
            'heading' => 'Pergerakan order 14 hari terakhir',
            'period' => $startDate->translatedFormat('d M').' - '.$endDate->translatedFormat('d M'),
            'totals' => [
                ['label' => 'Order masuk', 'value' => number_format($points->sum('orders'), 0, ',', '.')],
                ['label' => 'Order selesai', 'value' => number_format($points->sum('finished'), 0, ',', '.')],
                ['label' => 'Revenue', 'value' => $this->formatCurrency((int) $points->sum('revenue'))],
            ],
            'points' => $chart['points'],
            'ordersPath' => $chart['ordersPath'],
            'ordersAreaPath' => $chart['ordersAreaPath'],
            'finishedPath' => $chart['finishedPath'],
            'gridLines' => $chart['gridLines'],
            'axisLabels' => $axisLabels,
            'hasData' => $points->sum('orders') > 0 || $points->sum('finished') > 0 || $points->sum('revenue') > 0,
        ];
    }

    private function buildLineChart(Collection $points): array
    {
        $width = 100;
        $chartTop = 8;
        $chartBottom = 48;
        $chartHeight = $chartBottom - $chartTop;
        $divisor = max($points->count() - 1, 1);
        $maxValue = max((int) $points->max('orders'), (int) $points->max('finished'), 1);

        $mappedPoints = $points->values()->map(function (array $point, int $index) use ($width, $divisor, $chartTop, $chartBottom, $chartHeight, $maxValue): array {
            $x = round(4 + (($width - 8) * ($index / $divisor)), 2);

            return [
                ...$point,
                'x' => $x,
                'ordersY' => round($chartBottom - (($point['orders'] / $maxValue) * $chartHeight), 2),
                'finishedY' => round($chartBottom - (($point['finished'] / $maxValue) * $chartHeight), 2),
            ];
        });

        $ordersCoordinates = $mappedPoints
            ->map(fn (array $point) => $point['x'].' '.$point['ordersY'])
            ->implode(' L ');

        $finishedCoordinates = $mappedPoints
            ->map(fn (array $point) => $point['x'].' '.$point['finishedY'])
            ->implode(' L ');

        $firstPoint = $mappedPoints->first() ?? ['x' => 4];
        $lastPoint = $mappedPoints->last() ?? ['x' => 96];

        $gridLines = collect(range(0, 3))->map(function (int $index) use ($chartTop, $chartHeight, $maxValue): array {
            $fraction = $index / 3;

            return [
                'y' => round($chartTop + ($chartHeight * $fraction), 2),
                'label' => (string) (int) round($maxValue - ($maxValue * $fraction)),
            ];
        });

        return [
            'points' => $mappedPoints,
            'ordersPath' => 'M '.$ordersCoordinates,
            'ordersAreaPath' => 'M '.$ordersCoordinates.' L '.$lastPoint['x'].' '.$chartBottom.' L '.$firstPoint['x'].' '.$chartBottom.' Z',
            'finishedPath' => 'M '.$finishedCoordinates,
            'gridLines' => $gridLines,
        ];
    }

    private function buildStatusBreakdown(int $tokoId): array
    {
        $segments = [
            ['label' => 'Belum selesai', 'count' => Laundry::query()->where('toko_id', $tokoId)->where('status', 'belum_selesai')->count(), 'color' => '#f59e0b'],
            ['label' => 'Proses', 'count' => Laundry::query()->where('toko_id', $tokoId)->where('status', 'proses')->count(), 'color' => '#4a7df0'],
            ['label' => 'Selesai', 'count' => Laundry::query()->where('toko_id', $tokoId)->where('status', 'selesai')->count(), 'color' => '#10b981'],
        ];

        return $this->buildBreakdown(
            'Status order',
            'Distribusi seluruh laundry berdasarkan progres kerja terbaru.',
            $segments,
            'total order'
        );
    }

    private function buildPaymentBreakdown(int $tokoId): array
    {
        $records = Pembayaran::query()
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->where('laundries.toko_id', $tokoId)
            ->selectRaw("coalesce(nullif(pembayarans.metode_pembayaran, ''), 'belum_diatur') as metode, count(*) as aggregate_total")
            ->groupBy('metode')
            ->pluck('aggregate_total', 'metode');

        $labelMap = [
            'cash' => 'Cash',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            'ewallet' => 'E-Wallet',
            'belum_diatur' => 'Belum diatur',
        ];

        $colorMap = [
            'cash' => '#4a7df0',
            'qris' => '#10b981',
            'transfer' => '#7c3aed',
            'ewallet' => '#06b6d4',
            'belum_diatur' => '#94a3b8',
        ];

        $segments = collect($labelMap)
            ->map(fn (string $label, string $key): array => [
                'label' => $label,
                'count' => (int) ($records[$key] ?? 0),
                'color' => $colorMap[$key],
            ])
            ->values()
            ->all();

        return $this->buildBreakdown(
            'Metode pembayaran',
            'Komposisi metode pada data pembayaran yang sudah tercatat.',
            $segments,
            'transaksi'
        );
    }

    private function buildBreakdown(string $title, string $description, array $segments, string $summarySuffix): array
    {
        $total = array_sum(array_column($segments, 'count'));

        $normalizedSegments = collect($segments)->map(function (array $segment) use ($total): array {
            $percentage = $total > 0 ? round(($segment['count'] / $total) * 100, 1) : 0.0;

            return [
                ...$segment,
                'percentage' => $percentage,
                'meterWidth' => $total > 0 ? max((int) round(($segment['count'] / $total) * 100), $segment['count'] > 0 ? 8 : 0) : 0,
            ];
        });

        $angle = 0.0;
        $gradientParts = [];

        foreach ($normalizedSegments as $segment) {
            if ($segment['count'] === 0 || $total === 0) {
                continue;
            }

            $slice = ($segment['count'] / $total) * 360;
            $gradientParts[] = sprintf(
                '%s %sdeg %sdeg',
                $segment['color'],
                $this->formatDegrees($angle),
                $this->formatDegrees($angle + $slice)
            );
            $angle += $slice;
        }

        return [
            'title' => $title,
            'description' => $description,
            'total' => $total,
            'summary' => $total > 0
                ? number_format($total, 0, ',', '.').' '.$summarySuffix
                : 'Belum ada data',
            'gradient' => $gradientParts !== []
                ? 'conic-gradient('.implode(', ', $gradientParts).')'
                : 'conic-gradient(var(--bg-elevated) 0deg 360deg)',
            'segments' => $normalizedSegments,
        ];
    }

    private function buildTopServices(int $tokoId, int $totalLaundry): Collection
    {
        $rows = Laundry::query()
            ->join('jasas', 'jasas.id', '=', 'laundries.jasa_id')
            ->where('laundries.toko_id', $tokoId)
            ->selectRaw('jasas.nama_jasa, jasas.satuan, count(laundries.id) as total_order, sum(laundries.qty) as total_qty, sum(laundries.qty * jasas.harga) as estimated_revenue')
            ->groupBy('jasas.id', 'jasas.nama_jasa', 'jasas.satuan')
            ->orderByDesc('total_order')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return $rows->map(function ($row) use ($totalLaundry): array {
            $share = $totalLaundry > 0 ? (int) round(($row->total_order / $totalLaundry) * 100) : 0;

            return [
                'name' => str($row->nama_jasa)->replace('_', ' ')->title()->toString(),
                'unit' => $row->satuan,
                'count' => (int) $row->total_order,
                'qty' => $this->formatQty((float) $row->total_qty),
                'revenue' => $this->formatCurrency((int) round($row->estimated_revenue)),
                'share' => max($share, $row->total_order > 0 ? 12 : 0),
            ];
        });
    }

    private function emptyOverview(): array
    {
        return [
            'headline' => '',
            'miniStats' => [],
            'attentionLine' => '',
            'paidCount' => 0,
            'unpaidValue' => $this->formatCurrency(0),
        ];
    }

    private function emptyTrend(): array
    {
        return [
            'heading' => 'Pergerakan order 14 hari terakhir',
            'period' => '',
            'totals' => [],
            'points' => collect(),
            'ordersPath' => '',
            'ordersAreaPath' => '',
            'finishedPath' => '',
            'gridLines' => collect(),
            'axisLabels' => collect(),
            'hasData' => false,
        ];
    }

    private function emptyBreakdown(string $title): array
    {
        return [
            'title' => $title,
            'description' => '',
            'total' => 0,
            'summary' => 'Belum ada data',
            'gradient' => 'conic-gradient(var(--bg-elevated) 0deg 360deg)',
            'segments' => collect(),
        ];
    }

    private function formatCurrency(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function formatQty(float $qty): string
    {
        return rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
    }

    private function formatDegrees(float $degrees): string
    {
        $formatted = rtrim(rtrim(number_format($degrees, 2, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }
}
