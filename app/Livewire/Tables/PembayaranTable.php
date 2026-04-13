<?php

namespace App\Livewire\Tables;

use App\Models\Laundry;
use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PembayaranTable extends Component
{
    use WithPagination;

    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SORT_OPTIONS = [
        'nama_klien',
        'no_hp_klien',
        'total',
        'metode_pembayaran',
        'tgl_pembayaran',
        'status',
        'created_at',
    ];

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'metode_pembayaran', except: '')]
    public string $metodePembayaran = '';

    #[Url(as: 'sort', except: 'tgl_pembayaran')]
    public string $sortBy = 'tgl_pembayaran';

    #[Url(as: 'direction', except: 'desc')]
    public string $sortDirection = 'desc';

    #[Url(as: 'per_page', except: 10)]
    public int $perPage = 10;

    public function mount(): void
    {
        $this->normalizeState();
    }

    public function updated(string $property): void
    {
        if ($property === 'perPage') {
            $this->perPage = $this->resolvePerPage($this->perPage);
        }

        if (in_array($property, ['search', 'status', 'metodePembayaran', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function sort(string $column): void
    {
        if (! in_array($column, self::SORT_OPTIONS, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status', 'metodePembayaran');
        $this->sortBy = 'tgl_pembayaran';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->resetPage();
    }

    #[Computed]
    public function paymentMethods(): array
    {
        return Pembayaran::query()
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->where('laundries.toko_id', $this->tokoId())
            ->whereNotNull('pembayarans.metode_pembayaran')
            ->where('pembayarans.metode_pembayaran', '!=', '')
            ->select('pembayarans.metode_pembayaran')
            ->distinct()
            ->orderBy('pembayarans.metode_pembayaran')
            ->pluck('pembayarans.metode_pembayaran')
            ->mapWithKeys(fn (string $method): array => [
                $method => (new Pembayaran(['metode_pembayaran' => $method]))->metode_pembayaran_label,
            ])
            ->all();
    }

    #[Computed]
    public function statusOptions(): array
    {
        $statuses = Pembayaran::query()
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->where('laundries.toko_id', $this->tokoId())
            ->select('pembayarans.status')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('pembayarans.status')
            ->pluck('aggregate', 'pembayarans.status');

        $labels = [
            'belum_bayar' => 'Belum Bayar',
            'sudah_bayar' => 'Sudah Bayar',
        ];

        $options = collect($labels)
            ->filter(fn (string $label, string $value): bool => (int) ($statuses[$value] ?? 0) > 0)
            ->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ])
            ->values();

        return $options
            ->prepend([
                'value' => '',
                'label' => 'Semua status',
            ])
            ->all();
    }

    #[Computed]
    public function paymentMethodOptions(): array
    {
        return collect($this->paymentMethods())
            ->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ])
            ->prepend([
                'value' => '',
                'label' => 'Semua metode',
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function summary(): array
    {
        $tokoId = $this->tokoId();

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

    #[Computed]
    public function pembayarans()
    {
        $query = Pembayaran::query()
            ->select('pembayarans.*')
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->leftJoin('kliens', 'kliens.id', '=', 'pembayarans.klien_id')
            ->leftJoin('jasas', 'jasas.id', '=', 'laundries.jasa_id')
            ->where('laundries.toko_id', $this->tokoId())
            ->with(['laundry.klien', 'laundry.jasa', 'klien']);

        if ($this->search !== '') {
            $query->where(function (Builder $builder): void {
                $builder
                    ->where('laundries.nama', 'like', '%'.$this->search.'%')
                    ->orWhere('laundries.no_hp', 'like', '%'.$this->search.'%')
                    ->orWhere('kliens.nama_klien', 'like', '%'.$this->search.'%')
                    ->orWhere('kliens.no_hp_klien', 'like', '%'.$this->search.'%')
                    ->orWhere('jasas.nama_jasa', 'like', '%'.$this->search.'%')
                    ->orWhere('jasas.satuan', 'like', '%'.$this->search.'%')
                    ->orWhere('pembayarans.metode_pembayaran', 'like', '%'.$this->search.'%')
                    ->orWhere('pembayarans.catatan', 'like', '%'.$this->search.'%')
                    ->orWhere('pembayarans.total_biaya', 'like', '%'.$this->search.'%')
                    ->orWhere('pembayarans.total', 'like', '%'.$this->search.'%');
            });
        }

        if (in_array($this->status, ['belum_bayar', 'sudah_bayar'], true)) {
            $query->where('pembayarans.status', $this->status);
        }

        if ($this->metodePembayaran !== '') {
            $query->where('pembayarans.metode_pembayaran', $this->metodePembayaran);
        }

        $column = match ($this->sortBy) {
            'nama_klien' => 'laundries.nama',
            'no_hp_klien' => 'laundries.no_hp',
            'total' => 'pembayarans.total_biaya',
            'metode_pembayaran' => 'pembayarans.metode_pembayaran',
            'status' => 'pembayarans.status',
            'created_at' => 'pembayarans.created_at',
            default => 'pembayarans.tgl_pembayaran',
        };

        return $query
            ->orderBy($column, $this->sortDirection)
            ->orderBy('pembayarans.id', 'desc')
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.tables.pembayaran-table');
    }

    private function unpaidLaundryQuery(int $tokoId): Builder
    {
        return Laundry::query()
            ->where('toko_id', $tokoId)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereDoesntHave('pembayaran')
                    ->orWhereHas('pembayaran', fn (Builder $relation) => $relation->where('status', 'belum_bayar'));
            });
    }

    private function normalizeState(): void
    {
        $this->perPage = $this->resolvePerPage($this->perPage);
        $this->sortBy = in_array($this->sortBy, self::SORT_OPTIONS, true) ? $this->sortBy : 'tgl_pembayaran';
        $this->sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $this->status = in_array($this->status, ['belum_bayar', 'sudah_bayar'], true) ? $this->status : '';
        $this->metodePembayaran = array_key_exists($this->metodePembayaran, $this->paymentMethods()) ? $this->metodePembayaran : '';
    }

    private function resolvePerPage(int $value): int
    {
        return in_array($value, self::PER_PAGE_OPTIONS, true) ? $value : 10;
    }

    private function tokoId(): int
    {
        return (int) auth()->user()?->toko?->id;
    }
}
