@php($rows = $this->laundries)
@php($summary = $this->summary)

<div class="space-y-6" id="unpaid-laundry-table">
    <div class="flex flex-col gap-3">
        <p class="text-sm font-medium text-[var(--text-muted)]">Daftar tindak lanjut</p>
        <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Kelola Belum Bayar</h2>
        <p class="max-w-2xl text-sm text-[var(--text-muted)]">
            Selesaikan Pembayaran untuk order yang belum memiliki transaksi atau masih berstatus belum lunas.
        </p>
    </div>

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

            <flux:select wire:model="status">
                <option value="">Semua status</option>
                <option value="belum_selesai">Belum Selesai</option>
                <option value="selesai">Selesai</option>
            </flux:select>

            <flux:select wire:model="perPage">
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
                <p class="text-base font-semibold text-[var(--text-strong)]">Tidak ada order belum bayar.</p>
                <p class="mt-2 text-sm text-[var(--text-muted)]">Semua pembayaran sudah tertangani dengan baik.</p>
            </div>
        @elseif ($rows->isEmpty())
            <div class="py-14 text-center">
                <p class="text-base font-semibold text-[var(--text-strong)]">Tidak ada data yang cocok.</p>
                <p class="mt-2 text-sm text-[var(--text-muted)]">Ubah filter untuk menampilkan order lain.</p>
            </div>
        @else
            <div class="mt-6">
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
            </div>

            <p class="mt-4 text-sm text-[var(--text-muted)]">
                Showing {{ $rows->firstItem() ?? 0 }}-{{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} entries
            </p>
        @endif
    </section>
</div>
