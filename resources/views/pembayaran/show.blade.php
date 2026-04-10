<x-app-layout title="Detail Pembayaran">
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium text-[var(--text-muted)]">Payment detail</p>
                <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Detail Pembayaran</h2>
                <p class="mt-2 max-w-2xl text-sm text-[var(--text-muted)]">Ringkasan transaksi untuk pelanggan dan status pembayaran terakhir.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="button" onclick="window.print()" class="nyuci-btn-secondary no-print">
                    Cetak
                </button>
                <a href="{{ route('pembayaran.edit', $pembayaran) }}" class="nyuci-btn-primary no-print">
                    Edit Pembayaran
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            @page {
                margin: 12mm;
            }

            body {
                background: white !important;
                color: black !important;
            }

            header,
            aside,
            nav,
            .no-print {
                display: none !important;
            }

            .sticky.top-0.z-30 {
                display: none !important;
            }

            .no-print {
                display: none !important;
            }

            .print-wrap {
                padding: 0 !important;
            }

            .print-card {
                border: none !important;
                background: white !important;
                color: black !important;
                box-shadow: none !important;
            }

            .print-muted {
                color: black !important;
            }
        }
    </style>

    <div class="py-8 sm:py-10 print-wrap">
        <div class="mx-auto flex max-w-4xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-5 print-card">
                <div class="flex flex-col gap-4 border-b border-[var(--border-main)] pb-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--text-muted)] print-muted">Nyuci.id</p>
                        <h3 class="mt-1 text-2xl font-semibold text-[var(--text-strong)]">{{ $pembayaran->laundry?->toko?->nama_toko ?? 'Struk Pembayaran' }}</h3>
                        <p class="mt-2 text-sm text-[var(--text-muted)] print-muted">{{ $pembayaran->laundry?->toko?->alamat ?? '-' }}</p>
                        <p class="mt-1 text-sm text-[var(--text-muted)] print-muted">{{ $pembayaran->laundry?->toko?->no_hp ?? '-' }}</p>
                    </div>

                    <div class="text-left sm:text-right">
                        <p class="text-sm font-medium text-[var(--text-muted)] print-muted">Invoice</p>
                        <h3 class="mt-1 text-2xl font-semibold text-[var(--text-strong)]">#{{ $pembayaran->id }}</h3>
                        <p class="mt-2 text-sm text-[var(--text-muted)] print-muted">{{ $pembayaran->created_at?->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <x-card class="bg-[var(--bg-surface)] print-card">
                        <p class="text-sm font-semibold text-[var(--text-muted)] print-muted">Status</p>
                        <p class="mt-3 text-2xl font-semibold text-[var(--text-strong)]">{{ $pembayaran->status_label }}</p>
                    </x-card>

                    <x-card class="bg-[var(--bg-surface)] print-card">
                        <p class="text-sm font-semibold text-[var(--text-muted)] print-muted">Metode</p>
                        <p class="mt-3 text-2xl font-semibold text-[var(--text-strong)]">{{ $pembayaran->metode_pembayaran_label }}</p>
                    </x-card>

                    <x-card class="bg-[var(--bg-surface)] print-card">
                        <p class="text-sm font-semibold text-[var(--text-muted)] print-muted">Total</p>
                        <p class="mt-3 text-2xl font-semibold text-[var(--text-strong)]">Rp {{ number_format((int) $pembayaran->resolved_total, 0, ',', '.') }}</p>
                    </x-card>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <x-card class="print-card">
                    <p class="text-sm font-semibold text-[var(--text-muted)] print-muted">Nama Klien</p>
                    <p class="mt-3 text-2xl font-semibold text-[var(--text-strong)]">{{ $pembayaran->klien?->nama_klien ?? $pembayaran->laundry?->klien?->nama_klien ?? $pembayaran->laundry?->nama ?? '-' }}</p>
                </x-card>

                <x-card class="print-card">
                    <p class="text-sm font-semibold text-[var(--text-muted)] print-muted">No HP</p>
                    <p class="mt-3 text-2xl font-semibold text-[var(--text-strong)]">{{ $pembayaran->klien?->no_hp_klien ?? $pembayaran->laundry?->klien?->no_hp_klien ?? $pembayaran->laundry?->no_hp ?? '-' }}</p>
                </x-card>

                <x-card class="print-card">
                    <p class="text-sm font-semibold text-[var(--text-muted)] print-muted">Tanggal Bayar</p>
                    <p class="mt-3 text-2xl font-semibold text-[var(--text-strong)]">{{ $pembayaran->tgl_pembayaran?->format('d M Y') ?? '-' }}</p>
                </x-card>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.4fr_0.8fr]">
                <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-5 print-card">
                    <div class="flex items-start justify-between gap-4 border-b border-[var(--border-main)] pb-4">
                        <div>
                            <p class="text-sm font-medium text-[var(--text-muted)] print-muted">Detail Laundry</p>
                            <h3 class="mt-1 text-lg font-semibold text-[var(--text-strong)]">{{ $pembayaran->laundry?->jasa?->nama_jasa ?? $pembayaran->laundry?->jenis_jasa_label ?? 'Transaksi' }}</h3>
                        </div>
                        <x-status-badge :variant="$pembayaran->status === 'sudah_bayar' ? 'paid' : 'unpaid'">
                            {{ $pembayaran->status_label }}
                        </x-status-badge>
                    </div>

                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)] print-muted">Satuan</dt>
                            <dd class="mt-2 text-base font-semibold text-[var(--text-strong)]">{{ $pembayaran->laundry?->satuan_label ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)] print-muted">Tgl Masuk</dt>
                            <dd class="mt-2 text-base font-semibold text-[var(--text-strong)]">{{ $pembayaran->laundry?->tanggal_dimulai?->format('d M Y') ?? $pembayaran->laundry?->tanggal?->format('d M Y') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)] print-muted">Est. Selesai</dt>
                            <dd class="mt-2 text-base font-semibold text-[var(--text-strong)]">{{ $pembayaran->laundry?->ets_selesai?->format('d M Y') ?? $pembayaran->laundry?->estimasi_selesai?->format('d M Y') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)] print-muted">Selesai Pada</dt>
                            <dd class="mt-2 text-base font-semibold text-[var(--text-strong)]">{{ $pembayaran->laundry?->tgl_selesai?->format('d M Y') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)] print-muted">Status Laundry</dt>
                            <dd class="mt-2 text-base font-semibold text-[var(--text-strong)]">{{ $pembayaran->laundry?->status_label ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)] print-muted">Catatan</p>
                        <p class="mt-2 whitespace-pre-line text-sm text-[var(--text-main)] print-muted">{{ $pembayaran->catatan ?: '-' }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-5 print-card no-print">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-[var(--text-muted)] print-muted">Gateway QRIS</p>
                                <p class="mt-1 text-xs text-[var(--text-muted)] print-muted">
                                    Sesi checkout publik untuk pembayaran QRIS statis lokal.
                                </p>
                            </div>

                            <x-status-badge :variant="$pembayaran->gateway_status_variant">
                                {{ $pembayaran->gateway_status_label }}
                            </x-status-badge>
                        </div>

                        <dl class="mt-4 grid gap-3 text-sm">
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3">
                                <dt class="text-[var(--text-muted)]">Referensi</dt>
                                <dd class="font-semibold text-[var(--text-strong)]">{{ $pembayaran->gateway_reference ?? '-' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3">
                                <dt class="text-[var(--text-muted)]">Invoice ID</dt>
                                <dd class="font-semibold text-[var(--text-strong)]">{{ $pembayaran->gateway_invoice_id ?? '-' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3">
                                <dt class="text-[var(--text-muted)]">Berakhir</dt>
                                <dd class="font-semibold text-[var(--text-strong)]">{{ $pembayaran->gateway_expires_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd>
                            </div>
                        </dl>

                        @if ($pembayaran->gateway_qr_image)
                            <div class="mt-4 flex items-center justify-center rounded-2xl border border-[var(--border-main)] bg-white p-4">
                                <img src="{{ $pembayaran->gateway_qr_image }}" alt="QRIS checkout preview" class="max-h-48 w-full max-w-48 object-contain">
                            </div>
                        @endif

                        @if (filled(data_get($pembayaran->gateway_payload, 'merchant_name')))
                            <div class="mt-4 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)] print-muted">Merchant QRIS</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]">{{ data_get($pembayaran->gateway_payload, 'merchant_name') }}</p>
                            </div>
                        @endif

                        <div class="mt-4 flex flex-col gap-3">
                            @if ($pembayaran->gateway_checkout_url)
                                <a href="{{ $pembayaran->gateway_checkout_url }}" target="_blank" rel="noopener" class="nyuci-btn-primary w-full">
                                    Buka Checkout
                                </a>
                            @endif

                            @if ($pembayaran->gateway_sync_url && $pembayaran->gateway_token)
                                <form method="POST" action="{{ $pembayaran->gateway_sync_url }}">
                                    @csrf
                                    <button type="submit" class="nyuci-btn-secondary w-full">
                                        Muat Ulang Status
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if ($pembayaran->gateway_checkout_url)
                            <div class="mt-4 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Link checkout</p>
                                    <button
                                        type="button"
                                        class="text-xs font-semibold text-[var(--primary)] transition hover:text-[var(--primary-hover)]"
                                        x-data
                                        @click="navigator.clipboard.writeText(@js($pembayaran->gateway_checkout_url)); $el.textContent = 'Tersalin'; window.setTimeout(() => { $el.textContent = 'Salin'; }, 1500)"
                                    >
                                        Salin
                                    </button>
                                </div>
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $pembayaran->gateway_checkout_url }}"
                                    class="mt-2 w-full rounded-xl border border-[var(--border-main)] bg-[var(--bg-card)] px-3 py-2 text-xs text-[var(--text-main)]"
                                >
                            </div>
                        @endif

                        @if ($pembayaran->status !== 'sudah_bayar')
                            <form method="POST" action="{{ route('pembayaran.gateway.issue', $pembayaran) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="nyuci-btn-primary w-full">
                                    Buat / Refresh QRIS
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-5 print-card">
                        <p class="text-sm font-semibold text-[var(--text-muted)] print-muted">Tanggal Input</p>
                        <p class="mt-3 text-2xl font-semibold text-[var(--text-strong)]">{{ $pembayaran->created_at?->format('d M Y H:i') ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-5 print-card">
                        <p class="text-sm font-semibold text-[var(--text-muted)] print-muted">Aksi Cepat</p>
                        <div class="mt-4 flex flex-col gap-3">
                            @if ($pembayaran->status === 'belum_bayar')
                                <a href="{{ route('pembayaran.paid', $pembayaran) }}" class="nyuci-btn-primary no-print">
                                    Tandai Lunas
                                </a>
                            @endif
                            <form method="POST" action="{{ route('pembayaran.destroy', $pembayaran) }}" onsubmit="return confirm('Yakin hapus pembayaran ini?')" class="no-print">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="nyuci-btn-danger w-full">
                                    Hapus Pembayaran
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
