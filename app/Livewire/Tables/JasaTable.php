<?php

namespace App\Livewire\Tables;

use App\Models\Jasa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class JasaTable extends Component
{
    use WithPagination;

    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SORT_OPTIONS = [
        'nama_jasa',
        'satuan',
        'harga',
        'total_order',
        'created_at',
    ];

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'kategori', except: '')]
    public string $kategori = '';

    #[Url(as: 'sort', except: 'created_at')]
    public string $sortBy = 'created_at';

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

        if (in_array($property, ['search', 'kategori', 'perPage'], true)) {
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
        $this->reset('search', 'kategori');
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->resetPage();
    }

    #[Computed]
    public function summary(): array
    {
        $query = Jasa::query()->where('toko_id', $this->tokoId());

        return [
            'total' => (clone $query)->count(),
            'kiloan' => (clone $query)->where('satuan', 'like', '%kg%')->count(),
            'per_unit' => (clone $query)->where('satuan', 'not like', '%kg%')->count(),
        ];
    }

    #[Computed]
    public function jasas()
    {
        $query = Jasa::query()
            ->where('toko_id', $this->tokoId())
            ->withCount(['laundries as total_order']);

        if ($this->search !== '') {
            $query->where(function (Builder $builder): void {
                $builder
                    ->where('nama_jasa', 'like', '%'.$this->search.'%')
                    ->orWhere('satuan', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->kategori === 'kiloan') {
            $query->where('satuan', 'like', '%kg%');
        } elseif ($this->kategori === 'per_unit') {
            $query->where('satuan', 'not like', '%kg%');
        }

        $column = match ($this->sortBy) {
            'nama_jasa' => 'nama_jasa',
            'satuan' => 'satuan',
            'harga' => 'harga',
            'total_order' => 'total_order',
            default => 'created_at',
        };

        return $query
            ->orderBy($column, $this->sortDirection)
            ->orderBy('id')
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.tables.jasa-table');
    }

    private function normalizeState(): void
    {
        $this->perPage = $this->resolvePerPage($this->perPage);
        $this->sortBy = in_array($this->sortBy, self::SORT_OPTIONS, true) ? $this->sortBy : 'created_at';
        $this->sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $this->kategori = in_array($this->kategori, ['kiloan', 'per_unit'], true) ? $this->kategori : '';
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
