<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InsightController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SERVICE_SORT_OPTIONS = [
        'kategori',
        'jenis_jasa',
        'satuan',
        'total_order',
        'rata_rata_pembayaran',
        'total_pendapatan',
        'terakhir_dipakai',
    ];

    private const CUSTOMER_SORT_OPTIONS = [
        'nama',
        'no_hp',
        'total_order',
        'total_pembayaran',
        'belum_bayar',
        'terakhir_order',
    ];

    public function biayaJasa(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum melihat biaya jasa.');
        }

        $perPage = $this->resolvePerPage($request);
        $sort = $this->resolveSort($request->string('sort')->toString(), self::SERVICE_SORT_OPTIONS, 'terakhir_dipakai');
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $search = trim($request->string('search')->toString());
        $kategori = $request->string('kategori')->toString();
        $jenisJasa = $request->string('jenis_jasa')->toString();

        $baseQuery = DB::table('laundries')
            ->leftJoin('pembayarans', 'pembayarans.laundry_id', '=', 'laundries.id')
            ->where('laundries.toko_id', $toko->id);

        $serviceSummaryQuery = DB::query()->fromSub(
            $this->buildServiceReferenceQuery($baseQuery),
            'service_refs'
        );

        $summary = [
            'total' => (clone $serviceSummaryQuery)->count(),
            'kiloan' => (clone $serviceSummaryQuery)->where('kategori', 'kiloan')->count(),
            'per_unit' => (clone $serviceSummaryQuery)->where('kategori', 'per_unit')->count(),
        ];

        $jenisJasaOptions = DB::table('laundries')
            ->where('toko_id', $toko->id)
            ->whereNotNull('jenis_jasa')
            ->select('jenis_jasa')
            ->distinct()
            ->orderBy('jenis_jasa')
            ->pluck('jenis_jasa');

        $tableQuery = DB::query()->fromSub(
            $this->buildServiceReferenceQuery($baseQuery),
            'service_refs'
        );

        if ($search !== '') {
            $tableQuery->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('jenis_jasa', 'like', "%{$search}%")
                    ->orWhere('satuan', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if (in_array($kategori, ['kiloan', 'per_unit'], true)) {
            $tableQuery->where('kategori', $kategori);
        }

        if ($jenisJasa !== '') {
            $tableQuery->where('jenis_jasa', $jenisJasa);
        }

        $serviceRows = $this->applyServiceSorting($tableQuery, $sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('modules.biaya-jasa', [
            'data' => $serviceRows,
            'summary' => $summary,
            'jenisJasaOptions' => $jenisJasaOptions,
            'filters' => [
                'search' => $search,
                'kategori' => $kategori,
                'jenis_jasa' => $jenisJasa,
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function pelanggan(Request $request): View|RedirectResponse
    {
        $toko = $request->user()?->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum melihat data pelanggan.');
        }

        $perPage = $this->resolvePerPage($request);
        $sort = $this->resolveSort($request->string('sort')->toString(), self::CUSTOMER_SORT_OPTIONS, 'terakhir_order');
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();

        $customerBaseQuery = DB::table('laundries')
            ->leftJoin('pembayarans', 'pembayarans.laundry_id', '=', 'laundries.id')
            ->where('laundries.toko_id', $toko->id);

        $customerSummaryQuery = DB::query()->fromSub(
            $this->buildCustomerReferenceQuery($customerBaseQuery),
            'customer_refs'
        );

        $activeSince = now()->subDays(30)->toDateString();
        $summary = [
            'total' => (clone $customerSummaryQuery)->count(),
            'aktif' => (clone $customerSummaryQuery)->where('terakhir_order', '>=', $activeSince)->count(),
            'total_transaksi' => (int) DB::table('laundries')
                ->where('toko_id', $toko->id)
                ->count(),
        ];

        $tableQuery = DB::query()->fromSub(
            $this->buildCustomerReferenceQuery($customerBaseQuery),
            'customer_refs'
        );

        if ($search !== '') {
            $tableQuery->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($status === 'aktif') {
            $tableQuery->where('terakhir_order', '>=', $activeSince);
        } elseif ($status === 'perlu_follow_up') {
            $tableQuery->where('belum_bayar', '>', 0);
        } elseif ($status === 'arsip') {
            $tableQuery->where('terakhir_order', '<', $activeSince)->where('belum_bayar', 0);
        }

        $customers = $this->applyCustomerSorting($tableQuery, $sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('modules.pelanggan', [
            'data' => $customers,
            'summary' => $summary,
            'activeSinceLabel' => now()->subDays(30)->translatedFormat('d M Y'),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    private function buildServiceReferenceQuery(Builder $baseQuery): Builder
    {
        return (clone $baseQuery)
            ->selectRaw("
                CASE
                    WHEN LOWER(laundries.satuan) LIKE '%kg%' THEN 'kiloan'
                    ELSE 'per_unit'
                END as kategori,
                laundries.jenis_jasa,
                laundries.satuan,
                COUNT(laundries.id) as total_order,
                COALESCE(AVG(CASE WHEN pembayarans.status = 'sudah_bayar' THEN pembayarans.total END), 0) as rata_rata_pembayaran,
                COALESCE(SUM(CASE WHEN pembayarans.status = 'sudah_bayar' THEN pembayarans.total ELSE 0 END), 0) as total_pendapatan,
                MAX(laundries.tanggal) as terakhir_dipakai
            ")
            ->groupByRaw("
                CASE
                    WHEN LOWER(laundries.satuan) LIKE '%kg%' THEN 'kiloan'
                    ELSE 'per_unit'
                END,
                laundries.jenis_jasa,
                laundries.satuan
            ");
    }

    private function buildCustomerReferenceQuery(Builder $baseQuery): Builder
    {
        return (clone $baseQuery)
            ->selectRaw("
                MAX(laundries.nama) as nama,
                laundries.no_hp,
                COUNT(laundries.id) as total_order,
                COALESCE(SUM(CASE WHEN pembayarans.status = 'sudah_bayar' THEN pembayarans.total ELSE 0 END), 0) as total_pembayaran,
                COALESCE(SUM(CASE WHEN pembayarans.status = 'belum_bayar' THEN 1 ELSE 0 END), 0) as belum_bayar,
                MAX(laundries.tanggal) as terakhir_order
            ")
            ->groupBy('laundries.no_hp');
    }

    private function applyServiceSorting(Builder $query, string $sort, string $direction): Builder
    {
        $column = match ($sort) {
            'kategori' => 'kategori',
            'jenis_jasa' => 'jenis_jasa',
            'satuan' => 'satuan',
            'total_order' => 'total_order',
            'rata_rata_pembayaran' => 'rata_rata_pembayaran',
            'total_pendapatan' => 'total_pendapatan',
            default => 'terakhir_dipakai',
        };

        return $query->orderBy($column, $direction)->orderBy('jenis_jasa');
    }

    private function applyCustomerSorting(Builder $query, string $sort, string $direction): Builder
    {
        $column = match ($sort) {
            'nama' => 'nama',
            'no_hp' => 'no_hp',
            'total_order' => 'total_order',
            'total_pembayaran' => 'total_pembayaran',
            'belum_bayar' => 'belum_bayar',
            default => 'terakhir_order',
        };

        return $query->orderBy($column, $direction)->orderBy('nama');
    }

    private function resolvePerPage(Request $request): int
    {
        return in_array($request->integer('per_page', 10), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page', 10)
            : 10;
    }

    private function resolveSort(string $sort, array $options, string $default): string
    {
        return in_array($sort, $options, true) ? $sort : $default;
    }
}
