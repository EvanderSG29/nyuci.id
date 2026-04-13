<?php

namespace App\DataTables;

use App\Models\Laundry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UnpaidLaundryTable extends BaseTable
{
    public function summary(int $tokoId): array
    {
        $query = $this->query($tokoId);

        return [
            'total' => (clone $query)->count(),
            'belum_selesai' => (clone $query)->where('laundries.status', 'belum_selesai')->count(),
            'selesai' => (clone $query)->where('laundries.status', 'selesai')->count(),
        ];
    }

    public function statusOptions(int $tokoId): array
    {
        $statuses = $this->query($tokoId)
            ->select('laundries.status')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('laundries.status')
            ->pluck('aggregate', 'laundries.status');

        $labels = [
            'belum_selesai' => 'Belum Selesai',
            'selesai' => 'Selesai',
        ];

        return collect($labels)
            ->filter(fn (string $label, string $value): bool => (int) ($statuses[$value] ?? 0) > 0)
            ->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
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

        return DataTables::eloquent($query)
            ->addColumn('customer', fn (Laundry $laundry): string => $this->stackedText(
                $laundry->klien?->nama_klien ?? $laundry->nama,
                $laundry->klien?->no_hp_klien ?? $laundry->no_hp
            ))
            ->addColumn('service', fn (Laundry $laundry): string => $this->stackedText(
                $laundry->jasa?->nama_jasa ?? $laundry->jenis_jasa_label,
                $laundry->satuan_label
            ))
            ->addColumn('received_at', fn (Laundry $laundry): string => e($this->formatDate($laundry->tanggal_dimulai)))
            ->addColumn('due_at', fn (Laundry $laundry): string => e($this->formatDate($laundry->ets_selesai)))
            ->addColumn('status_badge', fn (Laundry $laundry): string => $this->badge(
                $laundry->status_label,
                $laundry->status === 'selesai' ? 'success' : 'pending'
            ))
            ->addColumn('total_display', function (Laundry $laundry): string {
                $total = $laundry->pembayaran?->resolved_total ?? (int) round(($laundry->qty ?? 0) * ($laundry->jasa?->harga ?? 0));

                return $this->strongText($this->formatCurrency($total));
            })
            ->addColumn('actions', function (Laundry $laundry): string {
                $existingPayment = $laundry->pembayaran;
                $actions = [
                    $this->actionPreview(route('laundry.preview', $laundry)),
                ];

                if ($existingPayment?->gateway_token) {
                    $actions[] = $this->actionLink($existingPayment->gateway_checkout_url, 'Buka Checkout QRIS', 'secondary', true);

                    return $this->actionGroup($actions);
                }

                if ($existingPayment) {
                    $actions[] = $this->actionLink(route('pembayaran.edit', $existingPayment), 'Selesaikan Pembayaran', 'primary');

                    return $this->actionGroup($actions);
                }

                $actions[] = $this->actionLink(route('pembayaran.create', ['laundry_id' => $laundry->id]), 'Bayar Sekarang', 'primary');

                return $this->actionGroup($actions);
            })
            ->rawColumns(['customer', 'service', 'status_badge', 'total_display', 'actions'])
            ->filter(function (Builder $query) use ($request, $search): void {
                if ($search !== '') {
                    $query->where(function (Builder $builder) use ($search): void {
                        $builder
                            ->where('laundries.nama', 'like', '%'.$search.'%')
                            ->orWhere('laundries.no_hp', 'like', '%'.$search.'%')
                            ->orWhere('laundries.jenis_jasa', 'like', '%'.$search.'%')
                            ->orWhere('laundries.satuan', 'like', '%'.$search.'%')
                            ->orWhereHas('klien', function (Builder $relation) use ($search): void {
                                $relation
                                    ->where('nama_klien', 'like', '%'.$search.'%')
                                    ->orWhere('no_hp_klien', 'like', '%'.$search.'%');
                            })
                            ->orWhereHas('jasa', function (Builder $relation) use ($search): void {
                                $relation
                                    ->where('nama_jasa', 'like', '%'.$search.'%')
                                    ->orWhere('satuan', 'like', '%'.$search.'%');
                            });
                    });
                }

                $status = $request->string('status')->toString();

                if (in_array($status, ['belum_selesai', 'selesai'], true)) {
                    $query->where('laundries.status', $status);
                }
            }, false)
            ->order(function (Builder $query) use ($request): void {
                $this->applyOrdering($query, $request, [
                    'nama' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.nama', $direction)->orderBy('laundries.id', 'desc'),
                    'jenis_jasa' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.jenis_jasa', $direction)->orderBy('laundries.id', 'desc'),
                    'tanggal' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.tanggal_dimulai', $direction)->orderBy('laundries.id', 'desc'),
                    'estimasi_selesai' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.ets_selesai', $direction)->orderBy('laundries.id', 'desc'),
                ], 'tanggal');
            })
            ->toJson();
    }

    protected function query(int $tokoId): Builder
    {
        return Laundry::query()
            ->where('toko_id', $tokoId)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereDoesntHave('pembayaran')
                    ->orWhereHas('pembayaran', fn (Builder $relation) => $relation->where('status', 'belum_bayar'));
            })
            ->with(['pembayaran', 'klien', 'jasa']);
    }
}
