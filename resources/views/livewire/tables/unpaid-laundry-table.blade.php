@php($rows = $this->laundries)
@php($summary = $this->summary)
@php($statusOptions = $this->statusOptions)
@php($loadingTargets = 'search,status,perPage,sort,clearFilters,gotoPage,previousPage,nextPage,setPage')
@php($perPageOptions = collect([10, 20, 50])->map(fn ($value) => [
    'value' => $value,
    'label' => $value.' per halaman',
])->all())

<div class="space-y-6" id="unpaid-laundry-table">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-card>
            <p class="text-sm font-semibold text-[var(--text-muted)]">Total Belum Bayar</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Semua order yang menunggu pembayaran.</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-[var(--primary-ink)]">Belum Selesai</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['belum_selesai'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Order yang masih berjalan dan belum dibayar.</p>
        </x-card>

        <x-card class="nyuci-summary-navy">
            <p class="text-sm font-semibold text-[var(--summary-label)]">Selesai</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['selesai'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Sudah selesai, tinggal tuntaskan pembayaran.</p>
        </x-card>
    </section>

    <section class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4 shadow-sm sm:p-6">
        <div class="grid gap-3 xl:grid-cols-[minmax(0,1.6fr)_220px_180px_auto]">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari pelanggan, nomor, atau layanan"
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
                    :columns="['Pelanggan', 'Layanan', 'Tanggal Masuk', 'Estimasi', 'Status', 'Total', 'Aksi']"
                    :detail-columns="[0, 1]"
                    :avatar-columns="[0]"
                    :badge-columns="[4]"
                    :action-column="6"
                />
            </div>

            <div wire:loading.remove wire:target="{{ $loadingTargets }}">
                @if ($summary['total'] === 0)
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Tidak ada order belum bayar.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Semua pembayaran sudah tertangani dengan baik.</p>
                    </div>
                @elseif ($rows->isEmpty())
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Tidak ada data yang cocok.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Ubah filter untuk menampilkan order lain.</p>
                    </div>
                @else
                    <flux:table :paginate="$rows" pagination:scroll-to="#unpaid-laundry-table">
                    <flux:table.columns>
                        <flux:table.column sortable :sorted="$sortBy === 'nama'" :direction="$sortDirection" wire:click="sort('nama')">Pelanggan</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'jenis_jasa'" :direction="$sortDirection" wire:click="sort('jenis_jasa')">Layanan</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'tanggal'" :direction="$sortDirection" wire:click="sort('tanggal')">Tanggal Masuk</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'estimasi_selesai'" :direction="$sortDirection" wire:click="sort('estimasi_selesai')">Estimasi</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Total</flux:table.column>
                        <flux:table.column align="end">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($rows as $laundry)
                            @php($existingPayment = $laundry->pembayaran)
                            @php($total = (int) round(($laundry->qty ?? 0) * ($laundry->jasa?->harga ?? 0)))

                            <flux:table.row :key="$laundry->id">
                                <flux:table.cell class="space-y-1">
                                    <div class="font-medium text-[var(--text-strong)]">{{ $laundry->klien?->nama_klien ?? $laundry->nama }}</div>
                                    <div class="text-xs text-[var(--text-muted)]">{{ $laundry->klien?->no_hp_klien ?? $laundry->no_hp }}</div>
                                </flux:table.cell>

                                <flux:table.cell class="space-y-1">
                                    <div>{{ $laundry->jasa?->nama_jasa ?? $laundry->jenis_jasa_label }}</div>
                                    <div class="text-xs text-[var(--text-muted)]">{{ $laundry->satuan_label }}</div>
                                </flux:table.cell>

                                <flux:table.cell>{{ $laundry->tanggal_dimulai?->translatedFormat('d M Y') ?? '-' }}</flux:table.cell>

                                <flux:table.cell>{{ $laundry->ets_selesai?->translatedFormat('d M Y') ?? '-' }}</flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$laundry->status === 'selesai' ? 'emerald' : 'amber'">
                                        {{ $laundry->status_label }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell variant="strong">Rp {{ number_format($total, 0, ',', '.') }}</flux:table.cell>

                                <flux:table.cell align="end">
                                    @if ($existingPayment)
                                        @if ($existingPayment->gateway_token)
                                            <flux:button size="sm" href="{{ $existingPayment->gateway_checkout_url }}" target="_blank" rel="noopener" icon="qr-code">
                                                Buka Checkout QRIS
                                            </flux:button>
                                        @else
                                            <flux:button size="sm" href="{{ route('pembayaran.edit', $existingPayment) }}" wire:navigate icon="wallet">
                                                Selesaikan Pembayaran
                                            </flux:button>
                                        @endif
                                    @else
                                        <flux:button size="sm" variant="primary" href="{{ route('pembayaran.create', ['laundry_id' => $laundry->id]) }}" wire:navigate icon="banknotes">
                                            Bayar Sekarang
                                        </flux:button>
                                    @endif
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
