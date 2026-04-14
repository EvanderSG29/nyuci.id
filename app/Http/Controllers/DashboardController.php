<?php

namespace App\Http\Controllers;

use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Services\DashboardCharts\DashboardChartConfigResolver;
use App\Services\DashboardCharts\DashboardChartDatasetBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        DashboardChartConfigResolver $chartResolver,
        DashboardChartDatasetBuilder $chartBuilder,
    ): View {
        $user = $request->user()->load('toko');
        $toko = $user->toko;

        if (! $toko) {
            return view('dashboard', [
                'toko' => null,
                'dashboardCards' => Toko::dashboardCardDefaults(),
                'overview' => $this->emptyOverview(),
                'highlights' => collect(),
                'heroChart' => null,
                'cardCharts' => [],
                'statusBreakdown' => $this->emptyBreakdown('Status order'),
                'paymentBreakdown' => $this->emptyBreakdown('Metode pembayaran'),
                'topServices' => collect(),
                'recentLaundries' => collect(),
            ]);
        }

        $chartResolver->ensureDefaults($toko);

        $today = CarbonImmutable::now()->startOfDay();
        $dashboardCards = $toko->dashboardCards();
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
            ->where('is_taken', false)
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

        $chartPayload = $chartResolver->dashboardPayload($toko, $user, $chartBuilder);

        return view('dashboard', [
            'toko' => $toko,
            'dashboardCards' => $dashboardCards,
            'overview' => $overview,
            'highlights' => $highlights,
            'heroChart' => $chartPayload['heroChart'] ?? null,
            'cardCharts' => $chartPayload['cardCharts'] ?? [],
            'statusBreakdown' => $this->buildStatusBreakdown($toko->id),
            'paymentBreakdown' => $this->buildPaymentBreakdown($toko->id),
            'topServices' => $this->buildTopServices($toko->id, $totalLaundry),
            'recentLaundries' => $toko->laundries()->with(['klien', 'jasa', 'pembayaran'])->latest()->take(6)->get(),
        ]);
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
