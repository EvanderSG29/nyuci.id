<?php

namespace App\DataTables;

use App\Models\Jasa;
use App\Models\Laundry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LaundryTable extends BaseTable
{
    public function summary(int $tokoId): array
    {
        $query = Laundry::query()->where('toko_id', $tokoId);

        return [
            'total' => (clone $query)->count(),
            'belum_selesai' => (clone $query)->where('status', 'belum_selesai')->count(),
            'proses' => (clone $query)->where('status', 'proses')->count(),
            'selesai' => (clone $query)->where('status', 'selesai')->count(),
        ];
    }

    public function statusOptions(int $tokoId): array
    {
        $statuses = Laundry::query()
            ->where('toko_id', $tokoId)
            ->select('status')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $labels = [
            'belum_selesai' => 'Belum Selesai',
            'proses' => 'Proses',
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

    public function paymentOptions(int $tokoId): array
    {
        $paidCount = Laundry::query()
            ->where('toko_id', $tokoId)
            ->whereHas('pembayaran', fn (Builder $builder) => $builder->where('status', 'sudah_bayar'))
            ->count();

        $unpaidCount = Laundry::query()
            ->where('toko_id', $tokoId)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereDoesntHave('pembayaran')
                    ->orWhereHas('pembayaran', fn (Builder $relation) => $relation->where('status', 'belum_bayar'));
            })
            ->count();

        $options = collect();

        if ($unpaidCount > 0) {
            $options->push([
                'value' => 'belum_bayar',
                'label' => 'Belum Bayar',
            ]);
        }

        if ($paidCount > 0) {
            $options->push([
                'value' => 'sudah_bayar',
                'label' => 'Sudah Bayar',
            ]);
        }

        return $options
            ->prepend([
                'value' => '',
                'label' => 'Semua pembayaran',
            ])
            ->values()
            ->all();
    }

    public function serviceOptions(int $tokoId): array
    {
        return Jasa::query()
            ->select('jasas.id', 'jasas.nama_jasa', 'jasas.satuan')
            ->join('laundries', 'laundries.jasa_id', '=', 'jasas.id')
            ->where('laundries.toko_id', $tokoId)
            ->distinct()
            ->orderBy('jasas.nama_jasa')
            ->get()
            ->map(fn (Jasa $jasa): array => [
                'value' => $jasa->id,
                'label' => $jasa->nama_jasa,
                'meta' => $jasa->satuan,
            ])
            ->prepend([
                'value' => 0,
                'label' => 'Semua jasa',
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
            ->addColumn('qty_display', fn (Laundry $laundry): string => $this->strongText($laundry->formatted_qty))
            ->addColumn('received_at', fn (Laundry $laundry): string => e($this->formatDate($laundry->tanggal_dimulai)))
            ->addColumn('due_at', fn (Laundry $laundry): string => e($this->formatDate($laundry->ets_selesai)))
            ->addColumn('status_badge', function (Laundry $laundry): string {
                $variant = match ($laundry->status) {
                    'selesai' => 'success',
                    'proses' => 'default',
                    default => 'pending',
                };

                return $this->badge($laundry->status_label, $variant);
            })
            ->addColumn('payment_badge', function (Laundry $laundry): string {
                $status = $laundry->pembayaran?->status_label ?? 'Belum Bayar';
                $variant = $laundry->pembayaran?->status === 'sudah_bayar' ? 'success' : 'pending';

                return $this->badge($status, $variant);
            })
            ->addColumn('actions', function (Laundry $laundry): string {
                $actions = [
                    $this->actionPreview(route('laundry.preview', $laundry)),
                    $this->actionLink(route('laundry.edit', $laundry), 'Edit'),
                ];

                if (! $laundry->pembayaran) {
                    $actions[] = $this->actionLink(
                        route('pembayaran.create', ['laundry_id' => $laundry->id]),
                        'Buat Pembayaran',
                        'primary'
                    );
                } elseif ($laundry->pembayaran->status === 'belum_bayar') {
                    $actions[] = $this->actionLink(
                        route('pembayaran.edit', $laundry->pembayaran),
                        'Selesaikan Pembayaran',
                        'primary'
                    );
                }

                return $this->actionGroup($actions);
            })
            ->rawColumns(['customer', 'service', 'qty_display', 'status_badge', 'payment_badge', 'actions'])
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

                if (in_array($status, ['belum_selesai', 'proses', 'selesai'], true)) {
                    $query->where('laundries.status', $status);
                }

                $dibayar = $request->string('dibayar')->toString();

                if ($dibayar === 'sudah_bayar') {
                    $query->whereHas('pembayaran', fn (Builder $builder) => $builder->where('status', 'sudah_bayar'));
                } elseif ($dibayar === 'belum_bayar') {
                    $query->where(function (Builder $builder): void {
                        $builder
                            ->whereDoesntHave('pembayaran')
                            ->orWhereHas('pembayaran', fn (Builder $relation) => $relation->where('status', 'belum_bayar'));
                    });
                }

                $jasaId = $request->integer('jasa_id');

                if ($jasaId > 0) {
                    $query->where('laundries.jasa_id', $jasaId);
                }
            }, false)
            ->order(function (Builder $query) use ($request): void {
                $this->applyOrdering($query, $request, [
                    'nama_klien' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.nama', $direction)->orderBy('laundries.id', 'desc'),
                    'jasa' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.jenis_jasa', $direction)->orderBy('laundries.id', 'desc'),
                    'qty' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.qty', $direction)->orderBy('laundries.id', 'desc'),
                    'tanggal_dimulai' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.tanggal_dimulai', $direction)->orderBy('laundries.id', 'desc'),
                    'ets_selesai' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.ets_selesai', $direction)->orderBy('laundries.id', 'desc'),
                    'status' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.status', $direction)->orderBy('laundries.id', 'desc'),
                    'dibayar' => fn (Builder $builder, string $direction) => $builder->orderBy('payment_rank', $direction)->orderBy('laundries.tanggal_dimulai', 'desc'),
                ], 'tanggal_dimulai');
            })
            ->toJson();
    }

    protected function query(int $tokoId): Builder
    {
        return Laundry::query()
            ->where('laundries.toko_id', $tokoId)
            ->leftJoin('pembayarans as pembayaran_sort', 'pembayaran_sort.laundry_id', '=', 'laundries.id')
            ->select('laundries.*')
            ->selectRaw("
                CASE
                    WHEN pembayaran_sort.status = 'sudah_bayar' THEN 1
                    WHEN pembayaran_sort.status = 'belum_bayar' THEN 0
                    ELSE 0
                END as payment_rank
            ")
            ->with(['klien', 'jasa', 'pembayaran']);
    }
}
