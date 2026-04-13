@php($rows = $this->kliens)
@php($summary = $this->summary)
@php($statusOptions = $this->statusOptions)
@php($loadingTargets = 'search,status,perPage,sort,clearFilters,gotoPage,previousPage,nextPage,setPage')
@php($perPageOptions = collect([10, 20, 50])->map(fn ($value) => [
    'value' => $value,
    'label' => $value.' per halaman',
])->all())

<div class="space-y-6" id="klien-table">
    <div class="flex justify-end">
        <flux:button variant="primary" href="{{ route('pelanggan.create') }}" wire:navigate icon="plus">
            Tambah Pelanggan
        </flux:button>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-card>
            <p class="text-sm font-semibold text-[var(--text-muted)]">Total Pelanggan</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Semua kontak aktif yang tersimpan di toko.</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-[var(--primary-ink)]">Aktif 30 Hari</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['aktif'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Pelanggan dengan order sejak {{ $this->activeSinceLabel }}.</p>
        </x-card>

        <x-card class="nyuci-summary-navy">
            <p class="text-sm font-semibold text-[var(--summary-label)]">Perlu Follow Up</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['perlu_follow_up'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Masih punya pembayaran yang belum lunas.</p>
        </x-card>
    </section>

    <section class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4 shadow-sm sm:p-6">
        <div class="grid gap-3 xl:grid-cols-[minmax(0,1.5fr)_220px_180px_auto]">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama atau nomor HP"
                icon="magnifying-glass"
            />

            <x-filter-select
                wire:model.live="status"
                :options="$statusOptions"
                placeholder="Semua status"
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
                    :columns="['Pelanggan', 'Kontak', 'Order', 'Belum Bayar', 'Terakhir Order', 'Aksi']"
                    :detail-columns="[0]"
                    :avatar-columns="[0]"
                    :badge-columns="[3]"
                    :action-column="5"
                />
            </div>

            <div wire:loading.remove wire:target="{{ $loadingTargets }}">
                @if ($summary['total'] === 0)
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Belum ada pelanggan.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Tambahkan data pelanggan pertama untuk mulai mencatat order.</p>
                    </div>
                @elseif ($rows->isEmpty())
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Tidak ada pelanggan yang cocok.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Ubah kata kunci atau status filter.</p>
                    </div>
                @else
                    <flux:table :paginate="$rows" pagination:scroll-to="#klien-table">
                    <flux:table.columns>
                        <flux:table.column sortable :sorted="$sortBy === 'nama_klien'" :direction="$sortDirection" wire:click="sort('nama_klien')">Pelanggan</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'no_hp_klien'" :direction="$sortDirection" wire:click="sort('no_hp_klien')">Kontak</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'total_order'" :direction="$sortDirection" wire:click="sort('total_order')">Order</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'belum_bayar'" :direction="$sortDirection" wire:click="sort('belum_bayar')">Belum Bayar</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'terakhir_order'" :direction="$sortDirection" wire:click="sort('terakhir_order')">Terakhir Order</flux:table.column>
                        <flux:table.column align="end">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($rows as $klien)
                            <flux:table.row :key="$klien->id">
                                <flux:table.cell class="space-y-1">
                                    <div class="font-medium text-[var(--text-strong)]">{{ $klien->nama_klien }}</div>
                                    <div class="truncate text-xs text-[var(--text-muted)]">{{ $klien->alamat_klien ?: 'Alamat belum diisi' }}</div>
                                </flux:table.cell>

                                <flux:table.cell>{{ $klien->no_hp_klien }}</flux:table.cell>

                                <flux:table.cell variant="strong">{{ $klien->total_order }}</flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$klien->belum_bayar > 0 ? 'amber' : 'emerald'">
                                        {{ $klien->belum_bayar }} tagihan
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ $klien->terakhir_order ? \Illuminate\Support\Carbon::parse($klien->terakhir_order)->translatedFormat('d M Y') : '-' }}
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <flux:dropdown align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" inset="top bottom"></flux:button>

                                        <flux:menu>
                                            <flux:menu.item href="{{ route('pelanggan.edit', $klien) }}" wire:navigate icon="pencil-square">
                                                Edit
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
