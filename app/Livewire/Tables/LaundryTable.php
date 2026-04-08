<?php

namespace App\Livewire\Tables;

use App\Models\Laundry;
use App\Models\Jasa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LaundryTable extends Component
{
    use WithPagination;

    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private const SORT_OPTIONS = [
        'nama_klien',
        'no_hp_klien',
        'jasa',
        'qty',
        'tanggal_dimulai',
        'ets_selesai',
        'tgl_selesai',
        'status',
        'dibayar',
    ];

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'dibayar', except: '')]
    public string $dibayar = '';

    #[Url(as: 'jasa_id', except: 0)]
    public int $jasaId = 0;

    #[Url(as: 'sort', except: 'tanggal_dimulai')]
    public string $sortBy = 'tanggal_dimulai';

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

        if (in_array($property, ['search', 'status', 'dibayar', 'jasaId', 'perPage'], true)) {
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
        $this->reset('search', 'status', 'dibayar', 'jasaId');
        $this->sortBy = 'tanggal_dimulai';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->resetPage();
    }

    #[Computed]
    public function summary(): array
    {
        $query = Laundry::query()->where('toko_id', $this->tokoId());

        return [
            'total' => (clone $query)->count(),
            'belum_selesai' => (clone $query)->where('status', 'belum_selesai')->count(),
            'proses' => (clone $query)->where('status', 'proses')->count(),
            'selesai' => (clone $query)->where('status', 'selesai')->count(),
        ];
    }

    #[Computed]
    public function jasaOptions(): Collection
    {
        return Jasa::query()
            ->where('toko_id', $this->tokoId())
            ->orderBy('nama_jasa')
            ->get();
    }

    #[Computed]
    public function laundries()
    {
        $query = Laundry::query()
            ->where('laundries.toko_id', $this->tokoId())
            ->with(['klien', 'jasa', 'pembayaran']);

        if ($this->search !== '') {
            $query->where(function (Builder $builder): void {
                $builder
                    ->where('laundries.nama', 'like', '%'.$this->search.'%')
                    ->orWhere('laundries.no_hp', 'like', '%'.$this->search.'%')
                    ->orWhere('laundries.jenis_jasa', 'like', '%'.$this->search.'%')
                    ->orWhere('laundries.satuan', 'like', '%'.$this->search.'%');
            });
        }

        if (in_array($this->status, ['belum_selesai', 'proses', 'selesai'], true)) {
            $query->where('laundries.status', $this->status);
        }

        if ($this->dibayar === 'sudah_bayar') {
            $query->whereHas('pembayaran', fn (Builder $builder) => $builder->where('status', 'sudah_bayar'));
        } elseif ($this->dibayar === 'belum_bayar') {
            $query->where(function (Builder $builder): void {
                $builder
                    ->whereDoesntHave('pembayaran')
                    ->orWhereHas('pembayaran', fn (Builder $relation) => $relation->where('status', 'belum_bayar'));
            });
        }

        if ($this->jasaId > 0) {
            $query->where('laundries.jasa_id', $this->jasaId);
        }

        if ($this->sortBy === 'dibayar') {
            $query
                ->leftJoin('pembayarans as pembayaran_sort', 'pembayaran_sort.laundry_id', '=', 'laundries.id')
                ->select('laundries.*')
                ->orderByRaw(
                    "case when pembayaran_sort.status = 'sudah_bayar' then 1 when pembayaran_sort.status = 'belum_bayar' then 0 else 0 end {$this->sortDirection}"
                )
                ->orderBy('laundries.tanggal_dimulai', 'desc');
        } else {
            $column = match ($this->sortBy) {
                'nama_klien' => 'laundries.nama',
                'no_hp_klien' => 'laundries.no_hp',
                'jasa' => 'laundries.jenis_jasa',
                'qty' => 'laundries.qty',
                'ets_selesai' => 'laundries.ets_selesai',
                'tgl_selesai' => 'laundries.tgl_selesai',
                'status' => 'laundries.status',
                default => 'laundries.tanggal_dimulai',
            };

            $query
                ->orderBy($column, $this->sortDirection)
                ->orderBy('laundries.id', 'desc');
        }

        return $query->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.tables.laundry-table');
    }

    private function normalizeState(): void
    {
        $this->perPage = $this->resolvePerPage($this->perPage);
        $this->sortBy = in_array($this->sortBy, self::SORT_OPTIONS, true) ? $this->sortBy : 'tanggal_dimulai';
        $this->sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $this->status = in_array($this->status, ['belum_selesai', 'proses', 'selesai'], true) ? $this->status : '';
        $this->dibayar = in_array($this->dibayar, ['belum_bayar', 'sudah_bayar'], true) ? $this->dibayar : '';
        $this->jasaId = max(0, $this->jasaId);
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
