@php($rows = $this->jasas)
@php($summary = $this->summary)
@php($loadingTargets = 'search,kategori,perPage,sort,clearFilters,gotoPage,previousPage,nextPage,setPage')
@php($categoryOptions = [
    ['value' => '', 'label' => 'Semua kategori'],
    ['value' => 'kiloan', 'label' => 'Kiloan'],
    ['value' => 'per_unit', 'label' => 'Per Unit'],
])
@php($perPageOptions = collect([10, 20, 50])->map(fn ($value) => [
    'value' => $value,
    'label' => $value.' per halaman',
])->all())

<div class="space-y-6" id="jasa-table">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-[var(--text-muted)]">Master jasa</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Biaya Jasa</h2>
            <p class="mt-2 max-w-2xl text-sm text-[var(--text-muted)]">
                Kelola daftar layanan laundry, satuan, dan harga dasar dengan format tabel Livewire yang lebih ringkas.
            </p>
        </div>

        <flux:button variant="primary" href="{{ route('biaya-jasa.create') }}" wire:navigate icon="plus">
            Tambah Jasa
        </flux:button>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-card>
            <p class="text-sm font-semibold text-[var(--text-muted)]">Total Jasa</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Semua layanan aktif di toko ini.</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-[var(--primary-ink)]">Kiloan</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['kiloan'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Layanan berbasis kilogram.</p>
        </x-card>

        <x-card class="nyuci-summary-navy">
            <p class="text-sm font-semibold text-[var(--summary-label)]">Per Unit</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['per_unit'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Layanan potong, pcs, dan satuan khusus.</p>
        </x-card>
    </section>

    <section class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4 shadow-sm sm:p-6">
        <div class="grid gap-3 xl:grid-cols-[minmax(0,1.5fr)_220px_180px_auto]">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama jasa atau satuan"
                icon="magnifying-glass"
            />

            <x-filter-select
                wire:model.live="kategori"
                :options="$categoryOptions"
                placeholder="Semua kategori"
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
                    :columns="['No', 'Nama Jasa', 'Satuan', 'Harga', 'Dipakai', 'Aksi']"
                    :badge-columns="[2]"
                    :action-column="5"
                />
            </div>

            <div wire:loading.remove wire:target="{{ $loadingTargets }}">
                @if ($summary['total'] === 0)
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Belum ada biaya jasa.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Tambahkan jasa pertama agar order laundry bisa langsung dipetakan.</p>
                    </div>
                @elseif ($rows->isEmpty())
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Tidak ada jasa yang cocok.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Ubah pencarian atau reset filter untuk melihat semua jasa.</p>
                    </div>
                @else
                    <flux:table :paginate="$rows" pagination:scroll-to="#jasa-table">
                    <flux:table.columns>
                        <flux:table.column>No</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'nama_jasa'" :direction="$sortDirection" wire:click="sort('nama_jasa')">Nama Jasa</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'satuan'" :direction="$sortDirection" wire:click="sort('satuan')">Satuan</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'harga'" :direction="$sortDirection" wire:click="sort('harga')">Harga</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'total_order'" :direction="$sortDirection" wire:click="sort('total_order')">Dipakai</flux:table.column>
                        <flux:table.column align="end">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($rows as $jasa)
                            <flux:table.row :key="$jasa->id">
                                <flux:table.cell>{{ ($rows->firstItem() ?? 1) + $loop->index }}</flux:table.cell>

                                <flux:table.cell variant="strong">{{ $jasa->nama_jasa }}</flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="str_contains(strtolower($jasa->satuan), 'kg') ? 'blue' : 'sky'">
                                        {{ $jasa->satuan }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell variant="strong">Rp {{ number_format((int) $jasa->harga, 0, ',', '.') }}</flux:table.cell>

                                <flux:table.cell>{{ $jasa->total_order }}</flux:table.cell>

                                <flux:table.cell align="end">
                                    <flux:dropdown align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" inset="top bottom"></flux:button>

                                        <flux:menu>
                                            <flux:menu.item href="{{ route('biaya-jasa.edit', $jasa) }}" wire:navigate icon="pencil-square">
                                                Edit
                                            </flux:menu.item>
                                            <form id="delete-form-{{ $jasa->id }}" action="{{ route('biaya-jasa.destroy', $jasa) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <flux:menu.item 
                                                icon="trash" 
                                                variant="danger"
                                                @click="if (confirm('Yakin ingin menghapus jasa ini?')) document.getElementById('delete-form-{{ $jasa->id }}').submit()">
                                                Hapus
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        </div>
    </section>
</div>
