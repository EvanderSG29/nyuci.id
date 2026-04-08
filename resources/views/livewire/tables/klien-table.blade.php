@php($rows = $this->kliens)
@php($summary = $this->summary)

<div class="space-y-6" id="klien-table">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-[var(--text-muted)]">Master pelanggan</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Pelanggan</h2>
            <p class="mt-2 max-w-2xl text-sm text-[var(--text-muted)]">
                Daftar pelanggan tersusun untuk follow up, histori order, dan status tagihan yang masih terbuka.
            </p>
        </div>

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

            <flux:select wire:model.live="status">
                <option value="">Semua status</option>
                <option value="aktif">Aktif</option>
                <option value="perlu_follow_up">Perlu Follow Up</option>
                <option value="arsip">Arsip</option>
            </flux:select>

            <flux:select wire:model.live="perPage">
                @foreach ([10, 20, 50] as $perPageOption)
                    <option value="{{ $perPageOption }}">{{ $perPageOption }} per halaman</option>
                @endforeach
            </flux:select>

            <div class="flex items-center justify-end">
                <flux:button variant="ghost" wire:click="clearFilters" icon="arrow-path">
                    Reset
                </flux:button>
            </div>
        </div>

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
            <div class="mt-6">
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
                                    <div class="font-medium text-zinc-800 dark:text-white">{{ $klien->nama_klien }}</div>
                                    <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $klien->alamat_klien ?: 'Alamat belum diisi' }}</div>
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
            </div>

            <p class="mt-4 text-sm text-[var(--text-muted)]">
                Showing {{ $rows->firstItem() ?? 0 }}-{{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} entries
            </p>
        @endif
    </section>
</div>
