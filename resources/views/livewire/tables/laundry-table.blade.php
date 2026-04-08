@php($rows = $this->laundries)
@php($summary = $this->summary)
@php($jasaOptions = $this->jasaOptions)

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
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari pelanggan, nomor, atau jasa"
                icon="magnifying-glass"
            />

            <flux:select wire:model.live="status">
                <option value="">Semua status</option>
                <option value="belum_selesai">Belum Selesai</option>
                <option value="proses">Proses</option>
                <option value="selesai">Selesai</option>
            </flux:select>

            <flux:select wire:model.live="dibayar">
                <option value="">Semua pembayaran</option>
                <option value="belum_bayar">Belum Bayar</option>
                <option value="sudah_bayar">Sudah Bayar</option>
            </flux:select>

            <flux:select wire:model.live="jasaId">
                <option value="0">Semua jasa</option>
                @foreach ($jasaOptions as $jasa)
                    <option value="{{ $jasa->id }}">{{ $jasa->nama_jasa }} / {{ $jasa->satuan }}</option>
                @endforeach
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
                <p class="text-base font-semibold text-[var(--text-strong)]">Belum ada order laundry.</p>
                <p class="mt-2 text-sm text-[var(--text-muted)]">Tambahkan order pertama untuk mulai mencatat proses operasional.</p>
            </div>
        @elseif ($rows->isEmpty())
            <div class="py-14 text-center">
                <p class="text-base font-semibold text-[var(--text-strong)]">Tidak ada order yang cocok.</p>
                <p class="mt-2 text-sm text-[var(--text-muted)]">Sesuaikan filter untuk menampilkan order lain.</p>
            </div>
        @else
            <div class="mt-6">
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
            </div>

            <p class="mt-4 text-sm text-[var(--text-muted)]">
                Showing {{ $rows->firstItem() ?? 0 }}-{{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} entries
            </p>
        @endif
    </section>
</div>
