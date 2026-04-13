<?php

namespace App\DataTables;

use App\Models\Jasa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JasaTable extends BaseTable
{
    public function summary(int $tokoId): array
    {
        $query = Jasa::query()->where('toko_id', $tokoId);

        return [
            'total' => (clone $query)->count(),
            'kiloan' => (clone $query)->where('satuan', 'like', '%kg%')->count(),
            'per_unit' => (clone $query)->where('satuan', 'not like', '%kg%')->count(),
        ];
    }

    public function satuanOptions(int $tokoId): array
    {
        return Jasa::query()
            ->where('toko_id', $tokoId)
            ->whereNotNull('satuan')
            ->where('satuan', '!=', '')
            ->select('satuan')
            ->distinct()
            ->orderBy('satuan')
            ->pluck('satuan')
            ->map(fn (string $satuan): array => [
                'value' => $satuan,
                'label' => $satuan,
            ])
            ->prepend([
                'value' => '',
                'label' => 'Semua satuan',
            ])
            ->values()
            ->all();
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->query($this->tokoId($request));
        $search = $this->searchValue($request);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('service_name', fn (Jasa $jasa): string => $this->strongText($jasa->nama_jasa))
            ->addColumn('unit_badge', function (Jasa $jasa): string {
                $variant = str_contains(strtolower($jasa->satuan), 'kg') ? 'pending' : 'default';

                return $this->badge($jasa->satuan, $variant);
            })
            ->addColumn('price_display', fn (Jasa $jasa): string => $this->strongText($this->formatCurrency($jasa->harga)))
            ->addColumn('total_order_display', fn (Jasa $jasa): string => $this->strongText((string) $jasa->total_order))
            ->addColumn('actions', fn (Jasa $jasa): string => $this->actionGroup([
                $this->actionPreview(route('biaya-jasa.preview', $jasa)),
                $this->actionLink(route('biaya-jasa.edit', $jasa), 'Edit'),
                $this->actionForm(
                    route('biaya-jasa.destroy', $jasa),
                    'Hapus',
                    'DELETE',
                    'danger',
                    'Yakin ingin menghapus jasa ini?'
                ),
            ]))
            ->rawColumns(['service_name', 'unit_badge', 'price_display', 'total_order_display', 'actions'])
            ->filter(function (Builder $query) use ($request, $search): void {
                if ($request->filled('satuan')) {
                    $query->where('satuan', $request->string('satuan')->toString());
                }

                if ($search !== '') {
                    $query->where(function (Builder $builder) use ($search): void {
                        $builder
                            ->where('nama_jasa', 'like', '%'.$search.'%')
                            ->orWhere('satuan', 'like', '%'.$search.'%');
                    });
                }
            }, false)
            ->order(function (Builder $query) use ($request): void {
                $this->applyOrdering($query, $request, [
                    'nama_jasa' => fn (Builder $builder, string $direction) => $builder->orderBy('nama_jasa', $direction)->orderBy('id'),
                    'satuan' => fn (Builder $builder, string $direction) => $builder->orderBy('satuan', $direction)->orderBy('nama_jasa'),
                    'harga' => fn (Builder $builder, string $direction) => $builder->orderBy('harga', $direction)->orderBy('nama_jasa'),
                    'total_order' => fn (Builder $builder, string $direction) => $builder->orderBy('total_order', $direction)->orderBy('nama_jasa'),
                    'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('created_at', $direction)->orderBy('id'),
                ], 'created_at');
            })
            ->toJson();
    }

    protected function query(int $tokoId): Builder
    {
        return Jasa::query()
            ->where('toko_id', $tokoId)
            ->withCount(['laundries as total_order']);
    }
}
