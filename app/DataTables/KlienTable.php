<?php

namespace App\DataTables;

use App\Models\Klien;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KlienTable extends BaseTable
{
    public function summary(int $tokoId): array
    {
        $activeSince = now()->subDays(30)->toDateString();
        $query = $this->query($tokoId);

        return [
            'total' => (clone $query)->count(),
            'aktif' => (clone $query)->where('terakhir_order', '>=', $activeSince)->count(),
            'perlu_follow_up' => (clone $query)->where('belum_bayar', '>', 0)->count(),
        ];
    }

    public function activeSinceLabel(): string
    {
        return now()->subDays(30)->translatedFormat('d M Y');
    }

    public function statusOptions(int $tokoId): array
    {
        $activeSince = now()->subDays(30)->toDateString();
        $query = $this->query($tokoId);

        return collect([
            [
                'value' => 'aktif',
                'label' => 'Aktif',
                'count' => (clone $query)->where('terakhir_order', '>=', $activeSince)->count(),
            ],
            [
                'value' => 'perlu_follow_up',
                'label' => 'Perlu Follow Up',
                'count' => (clone $query)->where('belum_bayar', '>', 0)->count(),
            ],
            [
                'value' => 'arsip',
                'label' => 'Arsip',
                'count' => (clone $query)
                    ->where(function (Builder $builder) use ($activeSince): void {
                        $builder
                            ->whereNull('terakhir_order')
                            ->orWhere('terakhir_order', '<', $activeSince);
                    })
                    ->where('belum_bayar', 0)
                    ->count(),
            ],
        ])
            ->filter(fn (array $option): bool => $option['count'] > 0)
            ->map(fn (array $option): array => [
                'value' => $option['value'],
                'label' => $option['label'],
            ])
            ->prepend([
                'value' => '',
                'label' => 'Semua status',
            ])
            ->values()
            ->all();
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->query($this->tokoId($request));
        $search = $this->searchValue($request);
        $activeSince = now()->subDays(30)->toDateString();

        return DataTables::eloquent($query)
            ->addColumn('customer', fn (Klien $klien): string => $this->stackedText(
                $klien->nama_klien,
                $klien->alamat_klien ?: 'Alamat belum diisi'
            ))
            ->addColumn('contact', fn (Klien $klien): string => e($klien->no_hp_klien))
            ->addColumn('total_order_display', fn (Klien $klien): string => $this->strongText((string) $klien->total_order))
            ->addColumn('unpaid_display', function (Klien $klien): string {
                $variant = $klien->belum_bayar > 0 ? 'pending' : 'success';

                return $this->badge($klien->belum_bayar.' tagihan', $variant);
            })
            ->addColumn('last_order_display', fn (Klien $klien): string => e($this->formatDate($klien->terakhir_order)))
            ->addColumn('actions', fn (Klien $klien): string => $this->actionGroup([
                $this->actionPreview(route('pelanggan.preview', $klien)),
                $this->actionLink(route('pelanggan.edit', $klien), 'Edit'),
            ]))
            ->rawColumns(['customer', 'total_order_display', 'unpaid_display', 'actions'])
            ->filter(function (Builder $query) use ($request, $search, $activeSince): void {
                if ($search !== '') {
                    $query->where(function (Builder $builder) use ($search): void {
                        $builder
                            ->where('nama_klien', 'like', '%'.$search.'%')
                            ->orWhere('no_hp_klien', 'like', '%'.$search.'%');
                    });
                }

                $status = $request->string('status')->toString();

                if ($status === 'aktif') {
                    $query->where('terakhir_order', '>=', $activeSince);
                } elseif ($status === 'perlu_follow_up') {
                    $query->where('belum_bayar', '>', 0);
                } elseif ($status === 'arsip') {
                    $query
                        ->where(function (Builder $builder) use ($activeSince): void {
                            $builder
                                ->whereNull('terakhir_order')
                                ->orWhere('terakhir_order', '<', $activeSince);
                        })
                        ->where('belum_bayar', 0);
                }
            }, false)
            ->order(function (Builder $query) use ($request): void {
                $this->applyOrdering($query, $request, [
                    'nama_klien' => fn (Builder $builder, string $direction) => $builder->orderBy('nama_klien', $direction),
                    'no_hp_klien' => fn (Builder $builder, string $direction) => $builder->orderBy('no_hp_klien', $direction),
                    'total_order' => fn (Builder $builder, string $direction) => $builder->orderBy('total_order', $direction)->orderBy('nama_klien'),
                    'belum_bayar' => fn (Builder $builder, string $direction) => $builder->orderBy('belum_bayar', $direction)->orderBy('nama_klien'),
                    'terakhir_order' => fn (Builder $builder, string $direction) => $builder->orderBy('terakhir_order', $direction)->orderBy('nama_klien'),
                    'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('created_at', $direction)->orderBy('nama_klien'),
                ], 'terakhir_order');
            })
            ->toJson();
    }

    protected function query(int $tokoId): Builder
    {
        return Klien::query()
            ->where('toko_id', $tokoId)
            ->withCount([
                'laundries as total_order',
                'pembayarans as belum_bayar' => fn (Builder $builder) => $builder->where('status', 'belum_bayar'),
            ])
            ->withMax('laundries as terakhir_order', 'tanggal_dimulai');
    }
}
