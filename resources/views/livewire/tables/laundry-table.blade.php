@php($rows = $this->laundries)
@php($summary = $this->summary)
@php($jasaOptions = $this->jasaOptions)
@php($loadingTargets = 'search,status,dibayar,jasaId,perPage,sort,clearFilters,gotoPage,previousPage,nextPage,setPage')
@php($statusOptions = [
    ['value' => '', 'label' => 'Semua status'],
    ['value' => 'belum_selesai', 'label' => 'Belum Selesai'],
    ['value' => 'proses', 'label' => 'Proses'],
    ['value' => 'selesai', 'label' => 'Selesai'],
])
@php($paymentOptions = [
    ['value' => '', 'label' => 'Semua pembayaran'],
    ['value' => 'belum_bayar', 'label' => 'Belum Bayar'],
    ['value' => 'sudah_bayar', 'label' => 'Sudah Bayar'],
])
@php($serviceOptions = $jasaOptions->map(fn ($jasa) => [
    'value' => $jasa->id,
    'label' => $jasa->nama_jasa,
    'meta' => $jasa->satuan,
])->prepend([
    'value' => 0,
    'label' => 'Semua jasa',
])->values()->all())
@php($perPageOptions = collect([10, 20, 50])->map(fn ($value) => [
    'value' => $value,
    'label' => $value.' per halaman',
])->all())

<div class="space-y-6" id="laundry-table">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-[var(--text-muted)]">Operasional laundry</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Laundry</h2>
            <p class="mt-2 max-w-2xl text-sm text-[var(--text-muted)]">
                Pantau semua order, progres pengerjaan, dan status pembayaran dari satu tabel Livewire.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button variant="outline" href="{{ route('pembayaran.unpaid') }}" wire:navigate icon="wallet">
                Kelola Belum Bayar
            </flux:button>

            <flux:button variant="primary" href="{{ route('laundry.create') }}" wire:navigate icon="plus">
                Tambah Laundry
            </flux:button>
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-card>
            <p class="text-sm font-semibold text-[var(--text-muted)]">Total Order</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Semua transaksi laundry yang tercatat.</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-[var(--primary-ink)]">Belum Selesai</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['belum_selesai'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Order baru yang belum masuk proses.</p>
        </x-card>

        <x-card class="nyuci-summary-navy">
            <p class="text-sm font-semibold text-[var(--summary-label)]">Proses</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['proses'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Sedang dikerjakan oleh tim laundry.</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-[var(--text-muted)]">Selesai</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['selesai'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Sudah siap diambil pelanggan.</p>
        </x-card>
    </section>

    <section class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4 shadow-sm sm:p-6">
        <div class="grid gap-3 xl:grid-cols-[minmax(0,1.4fr)_180px_180px_220px_180px_auto]">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[var(--text-muted)]">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.766l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.629-3.63A5.5 5.5 0 0 0 9 3.5Zm-4 5.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                    </svg>
                </span>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari pelanggan, nomor, atau jasa"
                    class="w-full rounded-xl border border-[var(--border-main)] bg-[var(--bg-surface)] py-3 pl-11 pr-4 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]"
                >
            </div>

            <x-filter-select
                wire:model.live="status"
                :options="$statusOptions"
                placeholder="Semua status"
            />

            <x-filter-select
                wire:model.live="dibayar"
                :options="$paymentOptions"
                placeholder="Semua pembayaran"
            />

            <x-filter-select
                wire:model.live.number="jasaId"
                :options="$serviceOptions"
                placeholder="Semua jasa"
                searchable
                search-placeholder="Cari jasa..."
            />

            <x-filter-select
                wire:model.live.number="perPage"
                :options="$perPageOptions"
                :searchable="false"
                placeholder="Baris per halaman"
            />

            <div class="flex items-center justify-end">
                <flux:button variant="ghost" wire:click="clearFilters" icon="arrow-path">
                    Reset
                </flux:button>
            </div>
        </div>

        <div class="mt-6">
            <div wire:loading.delay wire:target="{{ $loadingTargets }}">
                <x-table-skeleton
                    :columns="['Pelanggan', 'Layanan', 'Qty', 'Tanggal Masuk', 'ETS', 'Status', 'Pembayaran', 'Aksi']"
                    :detail-columns="[0, 1]"
                    :avatar-columns="[0]"
                    :badge-columns="[5, 6]"
                    :action-column="7"
                />
            </div>

            <div wire:loading.remove wire:target="{{ $loadingTargets }}">
                @if ($summary['total'] === 0)
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Belum ada order laundry.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Tambahkan order pertama untuk mulai mencatat proses operasional.</p>
                    </div>
                @elseif ($rows->isEmpty())
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Tidak ada order yang cocok.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Sesuaikan filter untuk menampilkan order lain.</p>
                    </div>
                @else
                    <flux:table :paginate="$rows" pagination:scroll-to="#laundry-table">
                    <flux:table.columns>
                        <flux:table.column sortable :sorted="$sortBy === 'nama_klien'" :direction="$sortDirection" wire:click="sort('nama_klien')">Pelanggan</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'jasa'" :direction="$sortDirection" wire:click="sort('jasa')">Layanan</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'qty'" :direction="$sortDirection" wire:click="sort('qty')">Qty</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'tanggal_dimulai'" :direction="$sortDirection" wire:click="sort('tanggal_dimulai')">Tanggal Masuk</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'ets_selesai'" :direction="$sortDirection" wire:click="sort('ets_selesai')">ETS</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">Status</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'dibayar'" :direction="$sortDirection" wire:click="sort('dibayar')">Pembayaran</flux:table.column>
                        <flux:table.column align="end">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($rows as $laundry)
                            <flux:table.row :key="$laundry->id">
                                <flux:table.cell class="space-y-1">
                                    <div class="font-medium text-[var(--text-strong)]">{{ $laundry->klien?->nama_klien ?? $laundry->nama }}</div>
                                    <div class="text-xs text-[var(--text-muted)]">{{ $laundry->klien?->no_hp_klien ?? $laundry->no_hp }}</div>
                                </flux:table.cell>

                                <flux:table.cell class="space-y-1">
                                    <div>{{ $laundry->jasa?->nama_jasa ?? $laundry->jenis_jasa_label }}</div>
                                    <div class="text-xs text-[var(--text-muted)]">{{ $laundry->satuan_label }}</div>
                                </flux:table.cell>

                                <flux:table.cell variant="strong">{{ $laundry->formatted_qty }}</flux:table.cell>

                                <flux:table.cell>{{ $laundry->tanggal_dimulai?->translatedFormat('d M Y') ?? '-' }}</flux:table.cell>

                                <flux:table.cell>{{ $laundry->ets_selesai?->translatedFormat('d M Y') ?? '-' }}</flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="match ($laundry->status) { 'selesai' => 'emerald', 'proses' => 'blue', default => 'amber' }">
                                        {{ $laundry->status_label }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$laundry->pembayaran?->status === 'sudah_bayar' ? 'emerald' : 'amber'">
                                        {{ $laundry->pembayaran?->status_label ?? 'Belum Bayar' }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <flux:dropdown align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" inset="top bottom"></flux:button>

                                        <flux:menu>
                                            <flux:menu.item href="{{ route('laundry.edit', $laundry) }}" wire:navigate icon="pencil-square">
                                                Edit
                                            </flux:menu.item>

                                            @if (! $laundry->pembayaran)
                                                <flux:menu.item href="{{ route('pembayaran.create', ['laundry_id' => $laundry->id]) }}" wire:navigate icon="banknotes">
                                                    Buat Pembayaran
                                                </flux:menu.item>
                                            @elseif ($laundry->pembayaran->status === 'belum_bayar')
                                                <flux:menu.item href="{{ route('pembayaran.edit', $laundry->pembayaran) }}" wire:navigate icon="wallet">
                                                    Selesaikan Pembayaran
                                                </flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                    </flux:table>

                    <p class="mt-4 text-sm text-[var(--text-muted)]">
                        Showing {{ $rows->firstItem() ?? 0 }}-{{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} entries
                    </p>
                @endif
            </div>
        </div>
    </section>
</div>
