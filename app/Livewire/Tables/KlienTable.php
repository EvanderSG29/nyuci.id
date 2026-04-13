<?php

namespace App\Livewire\Tables;

use App\Models\Klien;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class KlienTable extends Component
{
    use WithPagination;

    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SORT_OPTIONS = [
        'nama_klien',
        'no_hp_klien',
        'total_order',
        'belum_bayar',
        'terakhir_order',
        'created_at',
    ];

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'sort', except: 'terakhir_order')]
    public string $sortBy = 'terakhir_order';

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
        $this->sortBy = 'terakhir_order';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->resetPage();
    }

    #[Computed]
    public function statusOptions(): array
    {
        $activeSince = now()->subDays(30)->toDateString();
        $query = $this->baseQuery($this->tokoId());

        $options = collect([
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
            ]);

        return $options
            ->prepend([
                'value' => '',
                'label' => 'Semua status',
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function summary(): array
    {
        $activeSince = now()->subDays(30)->toDateString();
        $query = $this->baseQuery($this->tokoId());

        return [
            'total' => (clone $query)->count(),
            'aktif' => (clone $query)->where('terakhir_order', '>=', $activeSince)->count(),
            'perlu_follow_up' => (clone $query)->where('belum_bayar', '>', 0)->count(),
        ];
    }

    #[Computed]
    public function activeSinceLabel(): string
    {
        return now()->subDays(30)->translatedFormat('d M Y');
    }

    #[Computed]
    public function kliens()
    {
        $activeSince = now()->subDays(30)->toDateString();
        $query = $this->baseQuery($this->tokoId());

        if ($this->search !== '') {
            $query->where(function (Builder $builder): void {
                $builder
                    ->where('nama_klien', 'like', '%'.$this->search.'%')
                    ->orWhere('no_hp_klien', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status === 'aktif') {
            $query->where('terakhir_order', '>=', $activeSince);
        } elseif ($this->status === 'perlu_follow_up') {
            $query->where('belum_bayar', '>', 0);
        } elseif ($this->status === 'arsip') {
            $query->where(function (Builder $builder) use ($activeSince): void {
                $builder
                    ->whereNull('terakhir_order')
                    ->orWhere('terakhir_order', '<', $activeSince);
            })->where('belum_bayar', 0);
        }

        $column = match ($this->sortBy) {
            'nama_klien' => 'nama_klien',
            'no_hp_klien' => 'no_hp_klien',
            'total_order' => 'total_order',
            'belum_bayar' => 'belum_bayar',
            'created_at' => 'created_at',
            default => 'terakhir_order',
        };

        return $query
            ->orderBy($column, $this->sortDirection)
            ->orderBy('nama_klien')
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.tables.klien-table');
    }

    private function baseQuery(int $tokoId): Builder
    {
        return Klien::query()
            ->where('toko_id', $tokoId)
            ->withCount([
                'laundries as total_order',
                'pembayarans as belum_bayar' => fn (Builder $builder) => $builder->where('status', 'belum_bayar'),
            ])
            ->withMax('laundries as terakhir_order', 'tanggal_dimulai');
    }

    private function normalizeState(): void
    {
        $this->perPage = $this->resolvePerPage($this->perPage);
        $this->sortBy = in_array($this->sortBy, self::SORT_OPTIONS, true) ? $this->sortBy : 'terakhir_order';
        $this->sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $this->status = in_array($this->status, ['aktif', 'perlu_follow_up', 'arsip'], true) ? $this->status : '';
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
