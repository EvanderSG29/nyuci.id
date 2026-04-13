@php($rows = $this->pembayarans)
@php($summary = $this->summary)
@php($paymentMethods = $this->paymentMethods)
@php($loadingTargets = 'search,status,metodePembayaran,perPage,sort,clearFilters,gotoPage,previousPage,nextPage,setPage')
@php($statusOptions = [
    ['value' => '', 'label' => 'Semua status'],
    ['value' => 'belum_bayar', 'label' => 'Belum Bayar'],
    ['value' => 'sudah_bayar', 'label' => 'Sudah Bayar'],
])
@php($paymentMethodOptions = collect($paymentMethods)->map(fn ($label, $value) => [
    'value' => $value,
    'label' => $label,
])->prepend([
    'value' => '',
    'label' => 'Semua metode',
])->values()->all())
@php($perPageOptions = collect([10, 20, 50])->map(fn ($value) => [
    'value' => $value,
    'label' => $value.' per halaman',
])->all())

<div class="space-y-6" id="pembayaran-table">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-[var(--text-muted)]">Transaksi pembayaran</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Pembayaran</h2>
            <p class="mt-2 max-w-2xl text-sm text-[var(--text-muted)]">
                Pantau seluruh pembayaran, metode bayar, dan order yang masih perlu diselesaikan.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button variant="outline" href="{{ route('pembayaran.unpaid') }}" wire:navigate icon="wallet">
                Kelola Belum Bayar
            </flux:button>

            <flux:button variant="primary" href="{{ route('pembayaran.create') }}" wire:navigate icon="plus">
                Tambah Pembayaran
            </flux:button>
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-card>
            <p class="text-sm font-semibold text-[var(--text-muted)]">Total Pembayaran</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Semua transaksi yang sudah dibuat.</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-[var(--primary-ink)]">Sudah Bayar</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['sudah_bayar'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Order yang sudah dinyatakan lunas.</p>
        </x-card>

        <x-card class="nyuci-summary-navy">
            <p class="text-sm font-semibold text-[var(--summary-label)]">Belum Bayar</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['belum_bayar'] }}</p>
            <p class="mt-2 text-sm text-[var(--text-muted)]">Perlu tindak lanjut pembayaran.</p>
        </x-card>
    </section>

    <section class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4 shadow-sm sm:p-6">
        <div class="grid gap-3 xl:grid-cols-[minmax(0,1.4fr)_180px_220px_180px_auto]">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari pelanggan, metode, atau catatan"
                icon="magnifying-glass"
            />

            <x-filter-select
                wire:model.live="status"
                :options="$statusOptions"
                placeholder="Semua status"
            />

            <x-filter-select
                wire:model.live="metodePembayaran"
                :options="$paymentMethodOptions"
                placeholder="Semua metode"
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
                    :columns="['Pelanggan', 'Layanan', 'Metode', 'Tanggal', 'Status', 'Total', 'Aksi']"
                    :detail-columns="[0, 1]"
                    :avatar-columns="[0]"
                    :badge-columns="[4]"
                    :action-column="6"
                />
            </div>

            <div wire:loading.remove wire:target="{{ $loadingTargets }}">
                @if ($summary['total'] === 0)
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Belum ada pembayaran.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Buat transaksi pembayaran pertama dari daftar order laundry.</p>
                    </div>
                @elseif ($rows->isEmpty())
                    <div class="py-14 text-center">
                        <p class="text-base font-semibold text-[var(--text-strong)]">Tidak ada pembayaran yang cocok.</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Sesuaikan pencarian atau filter yang sedang aktif.</p>
                    </div>
                @else
                    <flux:table :paginate="$rows" pagination:scroll-to="#pembayaran-table">
                    <flux:table.columns>
                        <flux:table.column sortable :sorted="$sortBy === 'nama_klien'" :direction="$sortDirection" wire:click="sort('nama_klien')">Pelanggan</flux:table.column>
                        <flux:table.column>Layanan</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'metode_pembayaran'" :direction="$sortDirection" wire:click="sort('metode_pembayaran')">Metode</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'tgl_pembayaran'" :direction="$sortDirection" wire:click="sort('tgl_pembayaran')">Tanggal</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">Status</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'total'" :direction="$sortDirection" wire:click="sort('total')">Total</flux:table.column>
                        <flux:table.column align="end">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($rows as $pembayaran)
                            <flux:table.row :key="$pembayaran->id">
                                <flux:table.cell class="space-y-1">
                                    <div class="font-medium text-[var(--text-strong)]">{{ $pembayaran->klien?->nama_klien ?? $pembayaran->laundry?->nama }}</div>
                                    <div class="text-xs text-[var(--text-muted)]">{{ $pembayaran->klien?->no_hp_klien ?? $pembayaran->laundry?->no_hp }}</div>
                                </flux:table.cell>

                                <flux:table.cell class="space-y-1">
                                    <div>{{ $pembayaran->laundry?->jasa?->nama_jasa ?? $pembayaran->laundry?->jenis_jasa_label ?? '-' }}</div>
                                    <div class="text-xs text-[var(--text-muted)]">{{ $pembayaran->laundry?->satuan_label ?? '-' }}</div>
                                </flux:table.cell>

                                <flux:table.cell>{{ $pembayaran->metode_pembayaran_label }}</flux:table.cell>

                                <flux:table.cell>{{ $pembayaran->tgl_pembayaran?->translatedFormat('d M Y') ?? '-' }}</flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$pembayaran->status === 'sudah_bayar' ? 'emerald' : 'amber'">
                                        {{ $pembayaran->status_label }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell variant="strong">Rp {{ number_format($pembayaran->resolved_total, 0, ',', '.') }}</flux:table.cell>

                                <flux:table.cell align="end">
                                    <flux:dropdown align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" inset="top bottom"></flux:button>

                                        <flux:menu>
                                            <flux:menu.item href="{{ route('pembayaran.show', $pembayaran) }}" wire:navigate icon="document-text">
                                                Detail
                                            </flux:menu.item>

                                            @if ($pembayaran->gateway_token)
                                                <flux:menu.item href="{{ $pembayaran->gateway_checkout_url }}" target="_blank" rel="noopener" icon="qr-code">
                                                    Buka Checkout
                                                </flux:menu.item>
                                            @endif

                                            <flux:menu.item href="{{ route('pembayaran.edit', $pembayaran) }}" wire:navigate icon="pencil-square">
                                                Edit
                                            </flux:menu.item>

                                            @if ($pembayaran->status === 'belum_bayar')
                                                <flux:menu.item href="{{ route('pembayaran.paid', $pembayaran) }}" icon="check-circle">
                                                    Tandai Lunas
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
