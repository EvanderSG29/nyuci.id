@php
    $rows = $data;
    $activeFilters = $filters ?? [];
    $search = (string) ($activeFilters['search'] ?? '');
    $status = (string) ($activeFilters['status'] ?? '');
    $dibayar = (string) ($activeFilters['dibayar'] ?? '');
    $jasaId = (int) ($activeFilters['jasa_id'] ?? 0);
    $perPage = (int) ($activeFilters['per_page'] ?? 10);
    $sortBy = (string) ($activeFilters['sort'] ?? 'tanggal_dimulai');
    $sortDirection = (string) ($activeFilters['direction'] ?? 'desc');

    $sortUrl = function (string $column) use ($search, $status, $dibayar, $jasaId, $perPage, $sortBy, $sortDirection): string {
        return route('laundry.index', array_filter([
            'search' => $search !== '' ? $search : null,
            'status' => $status !== '' ? $status : null,
            'dibayar' => $dibayar !== '' ? $dibayar : null,
            'jasa_id' => $jasaId > 0 ? $jasaId : null,
            'per_page' => $perPage,
            'sort' => $column,
            'direction' => $sortBy === $column && $sortDirection === 'asc' ? 'desc' : 'asc',
        ], static fn (mixed $value): bool => $value !== null && $value !== ''));
    };

    $sortIndicator = function (string $column) use ($sortBy, $sortDirection): string {
        if ($sortBy !== $column) {
            return '';
        }

        return $sortDirection === 'asc' ? '↑' : '↓';
    };
@endphp

<x-app-layout title="Laundry">
    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-6" id="laundry-table">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--text-muted)]">Operasional laundry</p>
                        <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Laundry</h2>
                        <p class="mt-2 max-w-2xl text-sm text-[var(--text-muted)]">
                            Pantau semua order, progres pengerjaan, dan status pembayaran dari satu tabel.
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
                    <form
                        method="GET"
                        action="{{ route('laundry.index') }}"
                        x-data="{ timer: null, queueSubmit() { clearTimeout(this.timer); this.timer = setTimeout(() => this.$el.requestSubmit(), 300); } }"
                        class="grid gap-3 xl:grid-cols-[minmax(0,1.4fr)_180px_180px_220px_180px_auto]"
                    >
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[var(--text-muted)]">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.766l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.629-3.63A5.5 5.5 0 0 0 9 3.5Zm-4 5.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                                </svg>
                            </span>

                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari pelanggan, nomor, atau jasa"
                                @input="queueSubmit()"
                                class="w-full rounded-xl border border-[var(--border-main)] bg-[var(--bg-surface)] py-3 pl-11 pr-4 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]"
                            >
                        </div>

                        <select
                            name="status"
                            onchange="this.form.requestSubmit()"
                            class="w-full rounded-xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]"
                        >
                            <option value="">Semua status</option>
                            <option value="belum_selesai" @selected($status === 'belum_selesai')>Belum Selesai</option>
                            <option value="proses" @selected($status === 'proses')>Proses</option>
                            <option value="selesai" @selected($status === 'selesai')>Selesai</option>
                        </select>

                        <select
                            name="dibayar"
                            onchange="this.form.requestSubmit()"
                            class="w-full rounded-xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]"
                        >
                            <option value="">Semua pembayaran</option>
                            <option value="belum_bayar" @selected($dibayar === 'belum_bayar')>Belum Bayar</option>
                            <option value="sudah_bayar" @selected($dibayar === 'sudah_bayar')>Sudah Bayar</option>
                        </select>

                        <select
                            name="jasa_id"
                            onchange="this.form.requestSubmit()"
                            class="w-full rounded-xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]"
                        >
                            <option value="0">Semua jasa</option>
                            @foreach ($jasaOptions as $jasa)
                                <option value="{{ $jasa->id }}" @selected($jasaId === (int) $jasa->id)>{{ $jasa->nama_jasa }} / {{ $jasa->satuan }}</option>
                            @endforeach
                        </select>

                        <select
                            name="per_page"
                            onchange="this.form.requestSubmit()"
                            class="w-full rounded-xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]"
                        >
                            @foreach ([10, 20, 50] as $perPageOption)
                                <option value="{{ $perPageOption }}" @selected($perPage === $perPageOption)>{{ $perPageOption }} per halaman</option>
                            @endforeach
                        </select>

                        <div class="flex items-center justify-end">
                            <a
                                href="{{ route('laundry.index') }}"
                                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-[var(--text-strong)] transition hover:bg-[var(--bg-surface)]"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M15.312 11.424a.75.75 0 0 1 1.06 1.06A7.5 7.5 0 1 1 17.5 7.5a.75.75 0 0 1-1.5 0 6 6 0 1 0-1.757 4.243.75.75 0 0 1 1.06-.319ZM17.53 4.47a.75.75 0 0 1 0 1.06l-2.5 2.5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.469 1.97-1.969a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                                </svg>
                                Reset
                            </a>
                        </div>
                    </form>

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
                                    <flux:table.column>
                                        <a href="{{ $sortUrl('nama_klien') }}" class="inline-flex items-center gap-1">
                                            Pelanggan
                                            <span class="text-xs text-[var(--text-muted)]">{{ $sortIndicator('nama_klien') }}</span>
                                        </a>
                                    </flux:table.column>
                                    <flux:table.column>
                                        <a href="{{ $sortUrl('jasa') }}" class="inline-flex items-center gap-1">
                                            Layanan
                                            <span class="text-xs text-[var(--text-muted)]">{{ $sortIndicator('jasa') }}</span>
                                        </a>
                                    </flux:table.column>
                                    <flux:table.column>
                                        <a href="{{ $sortUrl('qty') }}" class="inline-flex items-center gap-1">
                                            Qty
                                            <span class="text-xs text-[var(--text-muted)]">{{ $sortIndicator('qty') }}</span>
                                        </a>
                                    </flux:table.column>
                                    <flux:table.column>
                                        <a href="{{ $sortUrl('tanggal_dimulai') }}" class="inline-flex items-center gap-1">
                                            Tanggal Masuk
                                            <span class="text-xs text-[var(--text-muted)]">{{ $sortIndicator('tanggal_dimulai') }}</span>
                                        </a>
                                    </flux:table.column>
                                    <flux:table.column>
                                        <a href="{{ $sortUrl('ets_selesai') }}" class="inline-flex items-center gap-1">
                                            ETS
                                            <span class="text-xs text-[var(--text-muted)]">{{ $sortIndicator('ets_selesai') }}</span>
                                        </a>
                                    </flux:table.column>
                                    <flux:table.column>
                                        <a href="{{ $sortUrl('status') }}" class="inline-flex items-center gap-1">
                                            Status
                                            <span class="text-xs text-[var(--text-muted)]">{{ $sortIndicator('status') }}</span>
                                        </a>
                                    </flux:table.column>
                                    <flux:table.column>
                                        <a href="{{ $sortUrl('dibayar') }}" class="inline-flex items-center gap-1">
                                            Pembayaran
                                            <span class="text-xs text-[var(--text-muted)]">{{ $sortIndicator('dibayar') }}</span>
                                        </a>
                                    </flux:table.column>
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
        </div>
    </div>
</x-app-layout>
