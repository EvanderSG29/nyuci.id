<?php

namespace App\Http\Controllers;

use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('query', ''));
        $tokoId = $request->user()?->toko?->id;

        if ($tokoId === null || mb_strlen($query) < 2) {
            return response()->json([
                'query' => $query,
                'groups' => [],
                'message' => null,
            ]);
        }

        $groups = array_values(array_filter([
            $this->searchLaundry($tokoId, $query),
            $this->searchCustomers($tokoId, $query),
            $this->searchPayments($tokoId, $query),
        ]));

        return response()->json([
            'query' => $query,
            'groups' => $groups,
            'message' => null,
        ]);
    }

    private function searchLaundry(int $tokoId, string $query): ?array
    {
        $items = Laundry::query()
            ->where('toko_id', $tokoId)
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('nama', 'like', '%'.$query.'%')
                    ->orWhere('no_hp', 'like', '%'.$query.'%')
                    ->orWhere('jenis_jasa', 'like', '%'.$query.'%')
                    ->orWhere('satuan', 'like', '%'.$query.'%')
                    ->orWhereHas('klien', function ($relation) use ($query): void {
                        $relation
                            ->where('nama_klien', 'like', '%'.$query.'%')
                            ->orWhere('no_hp_klien', 'like', '%'.$query.'%');
                    })
                    ->orWhereHas('jasa', function ($relation) use ($query): void {
                        $relation
                            ->where('nama_jasa', 'like', '%'.$query.'%')
                            ->orWhere('satuan', 'like', '%'.$query.'%');
                    });
            })
            ->with(['jasa'])
            ->latest('tanggal_dimulai')
            ->limit(5)
            ->get()
            ->map(fn (Laundry $laundry): array => [
                'title' => $laundry->nama,
                'subtitle' => trim($laundry->jenis_jasa_label.' • '.$laundry->status_label),
                'meta' => 'Masuk '.($laundry->tanggal_dimulai?->format('d M Y') ?? '-'),
                'url' => route('laundry.edit', $laundry),
            ])
            ->values()
            ->all();

        if ($items === []) {
            return null;
        }

        return [
            'key' => 'laundry',
            'label' => 'Laundry',
            'index_url' => route('laundry.index', ['search' => $query]),
            'items' => $items,
        ];
    }

    private function searchCustomers(int $tokoId, string $query): ?array
    {
        $items = Klien::query()
            ->where('toko_id', $tokoId)
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('nama_klien', 'like', '%'.$query.'%')
                    ->orWhere('no_hp_klien', 'like', '%'.$query.'%');
            })
            ->withCount([
                'laundries as total_order',
                'pembayarans as unpaid_order_count' => fn ($builder) => $builder->where('status', 'belum_bayar'),
            ])
            ->withMax('laundries as last_order_date', 'tanggal_dimulai')
            ->orderBy('nama_klien')
            ->limit(5)
            ->get()
            ->map(fn (Klien $klien): array => [
                'title' => $klien->nama_klien,
                'subtitle' => $klien->no_hp_klien ?: 'Kontak belum diisi',
                'meta' => $this->customerMeta($klien),
                'url' => route('pelanggan.edit', $klien),
            ])
            ->values()
            ->all();

        if ($items === []) {
            return null;
        }

        return [
            'key' => 'pelanggan',
            'label' => 'Pelanggan',
            'index_url' => route('pelanggan.index', ['search' => $query]),
            'items' => $items,
        ];
    }

    private function searchPayments(int $tokoId, string $query): ?array
    {
        $items = Pembayaran::query()
            ->whereHas('laundry', fn ($builder) => $builder->where('toko_id', $tokoId))
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('metode_pembayaran', 'like', '%'.$query.'%')
                    ->orWhere('total', 'like', '%'.$query.'%')
                    ->orWhere('total_biaya', 'like', '%'.$query.'%')
                    ->orWhereHas('laundry', function ($relation) use ($query): void {
                        $relation
                            ->where('nama', 'like', '%'.$query.'%')
                            ->orWhere('no_hp', 'like', '%'.$query.'%');
                    })
                    ->orWhereHas('klien', function ($relation) use ($query): void {
                        $relation
                            ->where('nama_klien', 'like', '%'.$query.'%')
                            ->orWhere('no_hp_klien', 'like', '%'.$query.'%');
                    });
            })
            ->with(['laundry', 'klien'])
            ->latest('tgl_pembayaran')
            ->limit(5)
            ->get()
            ->map(fn (Pembayaran $pembayaran): array => [
                'title' => $pembayaran->klien?->nama_klien ?: $pembayaran->laundry?->nama ?: 'Pembayaran',
                'subtitle' => trim($pembayaran->metode_pembayaran_label.' • '.$pembayaran->status_label),
                'meta' => 'Total '.$this->formatCurrency($pembayaran->resolved_total).' • '.($pembayaran->tgl_pembayaran?->format('d M Y') ?? 'Belum ada tanggal'),
                'url' => route('pembayaran.show', $pembayaran),
            ])
            ->values()
            ->all();

        if ($items === []) {
            return null;
        }

        return [
            'key' => 'pembayaran',
            'label' => 'Pembayaran',
            'index_url' => route('pembayaran.index', ['search' => $query]),
            'items' => $items,
        ];
    }

    private function customerMeta(Klien $klien): string
    {
        $segments = [
            number_format((int) $klien->total_order, 0, ',', '.').' order',
        ];

        if ((int) $klien->unpaid_order_count > 0) {
            $segments[] = number_format((int) $klien->unpaid_order_count, 0, ',', '.').' belum bayar';
        }

        if ($klien->last_order_date) {
            $segments[] = 'Terakhir '.Carbon::parse($klien->last_order_date)->format('d M Y');
        }

        return implode(' • ', $segments);
    }

    private function formatCurrency(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
