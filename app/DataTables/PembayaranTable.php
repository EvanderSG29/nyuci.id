<?php

namespace App\DataTables;

use App\Models\Laundry;
use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PembayaranTable extends BaseTable
{
    public function summary(int $tokoId): array
    {
        return [
            'total' => Pembayaran::query()
                ->whereHas('laundry', fn (Builder $builder) => $builder->where('toko_id', $tokoId))
                ->count(),
            'sudah_bayar' => Pembayaran::query()
                ->whereHas('laundry', fn (Builder $builder) => $builder->where('toko_id', $tokoId))
                ->where('status', 'sudah_bayar')
                ->count(),
            'belum_bayar' => $this->unpaidLaundryQuery($tokoId)->count(),
        ];
    }

    public function statusOptions(int $tokoId): array
    {
        $statuses = Pembayaran::query()
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->where('laundries.toko_id', $tokoId)
            ->select('pembayarans.status')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('pembayarans.status')
            ->pluck('aggregate', 'pembayarans.status');

        $labels = [
            'belum_bayar' => 'Belum Bayar',
            'sudah_bayar' => 'Sudah Bayar',
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

    public function paymentMethodOptions(int $tokoId): array
    {
        return Pembayaran::query()
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->where('laundries.toko_id', $tokoId)
            ->whereNotNull('pembayarans.metode_pembayaran')
            ->where('pembayarans.metode_pembayaran', '!=', '')
            ->select('pembayarans.metode_pembayaran')
            ->distinct()
            ->orderBy('pembayarans.metode_pembayaran')
            ->pluck('pembayarans.metode_pembayaran')
            ->map(fn (string $method): array => [
                'value' => $method,
                'label' => (new Pembayaran(['metode_pembayaran' => $method]))->metode_pembayaran_label,
            ])
            ->prepend([
                'value' => '',
                'label' => 'Semua metode',
            ])
            ->values()
            ->all();
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->query($this->tokoId($request));
        $search = $this->searchValue($request);

        return DataTables::eloquent($query)
            ->addColumn('customer', fn (Pembayaran $pembayaran): string => $this->stackedText(
                $pembayaran->klien?->nama_klien ?? $pembayaran->laundry?->nama ?? '-',
                $pembayaran->klien?->no_hp_klien ?? $pembayaran->laundry?->no_hp ?? '-'
            ))
            ->addColumn('service', fn (Pembayaran $pembayaran): string => $this->stackedText(
                $pembayaran->laundry?->jasa?->nama_jasa ?? $pembayaran->laundry?->jenis_jasa_label ?? '-',
                $pembayaran->laundry?->satuan_label ?? '-'
            ))
            ->addColumn('method_display', fn (Pembayaran $pembayaran): string => e($pembayaran->metode_pembayaran_label))
            ->addColumn('date_display', fn (Pembayaran $pembayaran): string => e($this->formatDate($pembayaran->tgl_pembayaran)))
            ->addColumn('status_badge', fn (Pembayaran $pembayaran): string => $this->badge(
                $pembayaran->status_label,
                $pembayaran->status === 'sudah_bayar' ? 'success' : 'pending'
            ))
            ->addColumn('total_display', fn (Pembayaran $pembayaran): string => $this->strongText($this->formatCurrency($pembayaran->resolved_total)))
            ->addColumn('actions', function (Pembayaran $pembayaran): string {
                $actions = [
                    $this->actionPreview(route('pembayaran.preview', $pembayaran)),
                ];

                if ($pembayaran->gateway_token) {
                    $actions[] = $this->actionLink($pembayaran->gateway_checkout_url, 'Buka Checkout', 'secondary', true);
                }

                $actions[] = $this->actionLink(route('pembayaran.edit', $pembayaran), 'Edit');

                if ($pembayaran->status === 'belum_bayar') {
                    $actions[] = $this->actionLink(route('pembayaran.paid', $pembayaran), 'Tandai Lunas', 'primary');
                }

                return $this->actionGroup($actions);
            })
            ->rawColumns(['customer', 'service', 'status_badge', 'total_display', 'actions'])
            ->filter(function (Builder $query) use ($request, $search): void {
                if ($search !== '') {
                    $query->where(function (Builder $builder) use ($search): void {
                        $builder
                            ->where('laundries.nama', 'like', '%'.$search.'%')
                            ->orWhere('laundries.no_hp', 'like', '%'.$search.'%')
                            ->orWhere('kliens.nama_klien', 'like', '%'.$search.'%')
                            ->orWhere('kliens.no_hp_klien', 'like', '%'.$search.'%')
                            ->orWhere('jasas.nama_jasa', 'like', '%'.$search.'%')
                            ->orWhere('jasas.satuan', 'like', '%'.$search.'%')
                            ->orWhere('pembayarans.metode_pembayaran', 'like', '%'.$search.'%')
                            ->orWhere('pembayarans.catatan', 'like', '%'.$search.'%')
                            ->orWhere('pembayarans.total_biaya', 'like', '%'.$search.'%')
                            ->orWhere('pembayarans.total', 'like', '%'.$search.'%');
                    });
                }

                $status = $request->string('status')->toString();

                if (in_array($status, ['belum_bayar', 'sudah_bayar'], true)) {
                    $query->where('pembayarans.status', $status);
                }

                $method = $request->string('metode_pembayaran')->toString();

                if ($method !== '') {
                    $query->where('pembayarans.metode_pembayaran', $method);
                }
            }, false)
            ->order(function (Builder $query) use ($request): void {
                $this->applyOrdering($query, $request, [
                    'nama_klien' => fn (Builder $builder, string $direction) => $builder->orderBy('laundries.nama', $direction)->orderBy('pembayarans.id', 'desc'),
                    'metode_pembayaran' => fn (Builder $builder, string $direction) => $builder->orderBy('pembayarans.metode_pembayaran', $direction)->orderBy('pembayarans.id', 'desc'),
                    'tgl_pembayaran' => fn (Builder $builder, string $direction) => $builder->orderBy('pembayarans.tgl_pembayaran', $direction)->orderBy('pembayarans.id', 'desc'),
                    'status' => fn (Builder $builder, string $direction) => $builder->orderBy('pembayarans.status', $direction)->orderBy('pembayarans.id', 'desc'),
                    'total' => fn (Builder $builder, string $direction) => $builder->orderBy('pembayarans.total_biaya', $direction)->orderBy('pembayarans.id', 'desc'),
                    'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('pembayarans.created_at', $direction)->orderBy('pembayarans.id', 'desc'),
                ], 'tgl_pembayaran');
            })
            ->toJson();
    }

    protected function query(int $tokoId): Builder
    {
        return Pembayaran::query()
            ->select('pembayarans.*')
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->leftJoin('kliens', 'kliens.id', '=', 'pembayarans.klien_id')
            ->leftJoin('jasas', 'jasas.id', '=', 'laundries.jasa_id')
            ->where('laundries.toko_id', $tokoId)
            ->with(['laundry.klien', 'laundry.jasa', 'klien']);
    }

    protected function unpaidLaundryQuery(int $tokoId): Builder
    {
        return Laundry::query()
            ->where('toko_id', $tokoId)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereDoesntHave('pembayaran')
                    ->orWhereHas('pembayaran', fn (Builder $relation) => $relation->where('status', 'belum_bayar'));
            });
    }
}
