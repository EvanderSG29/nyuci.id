<?php

namespace App\Livewire\Tables;

use App\Models\Laundry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UnpaidLaundryTable extends Component
{
    use WithPagination;

    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SORT_OPTIONS = [
        'nama',
        'no_hp',
        'jenis_jasa',
        'satuan',
        'tanggal',
        'estimasi_selesai',
    ];

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'sort', except: 'tanggal')]
    public string $sortBy = 'tanggal';

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

        if (in_array($property, ['search', 'status', 'perPage'], true)) {
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
        $this->reset('search', 'status');
        $this->sortBy = 'tanggal';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->resetPage();
    }

    #[Computed]
    public function summary(): array
    {
        $query = $this->unpaidLaundryQuery($this->tokoId());

        return [
            'total' => (clone $query)->count(),
            'belum_selesai' => (clone $query)->where('laundries.status', 'belum_selesai')->count(),
            'selesai' => (clone $query)->where('laundries.status', 'selesai')->count(),
        ];
    }

    #[Computed]
    public function laundries()
    {
        $query = $this->unpaidLaundryQuery($this->tokoId())
            ->with(['pembayaran', 'klien', 'jasa']);

        if ($this->search !== '') {
            $query->where(function (Builder $builder): void {
                $builder
                    ->where('laundries.nama', 'like', '%'.$this->search.'%')
                    ->orWhere('laundries.no_hp', 'like', '%'.$this->search.'%')
                    ->orWhere('laundries.jenis_jasa', 'like', '%'.$this->search.'%')
                    ->orWhere('laundries.satuan', 'like', '%'.$this->search.'%');
            });
        }

        if (in_array($this->status, ['belum_selesai', 'selesai'], true)) {
            $query->where('laundries.status', $this->status);
        }

        $column = match ($this->sortBy) {
            'nama' => 'laundries.nama',
            'no_hp' => 'laundries.no_hp',
            'jenis_jasa' => 'laundries.jenis_jasa',
            'satuan' => 'laundries.satuan',
            'estimasi_selesai' => 'laundries.ets_selesai',
            default => 'laundries.tanggal_dimulai',
        };

        return $query
            ->orderBy($column, $this->sortDirection)
            ->orderBy('laundries.id', 'desc')
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.tables.unpaid-laundry-table');
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
        $this->sortBy = in_array($this->sortBy, self::SORT_OPTIONS, true) ? $this->sortBy : 'tanggal';
        $this->sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $this->status = in_array($this->status, ['belum_selesai', 'selesai'], true) ? $this->status : '';
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
