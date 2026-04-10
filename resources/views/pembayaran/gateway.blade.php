<x-guest-layout title="Checkout QRIS">
    @php
        $gatewayStatus = $gateway['status'] ?? 'pending';
        $checkoutUrl = $pembayaran->gateway_checkout_url;
        $syncUrl = $pembayaran->gateway_sync_url;
        $paid = $gatewayStatus === 'paid' || $pembayaran->status === 'sudah_bayar';
        $expired = $gatewayStatus === 'expired';
        $total = (int) $pembayaran->resolved_total;
        $customerName = $pembayaran->klien?->nama_klien ?? $pembayaran->laundry?->nama ?? '-';
        $serviceName = $pembayaran->laundry?->jasa?->nama_jasa ?? $pembayaran->laundry?->jenis_jasa_label ?? '-';
        $merchantName = $gateway['merchant_name'] ?? data_get($gateway, 'payload.merchant_name') ?? 'QRIS Statis';
    @endphp

    <div class="space-y-5">
        <x-card class="space-y-4 border-[var(--border-main)] bg-[var(--bg-card)]">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--text-muted)]">Checkout QRIS</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Pembayaran {{ $customerName }}</h1>
                    <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                        Invoice #{{ $pembayaran->id }} untuk layanan {{ $serviceName }}. QR dibuat lokal dari payload QRIS statis, tanpa simulator eksternal.
                    </p>
                </div>

                <x-status-badge :variant="$pembayaran->gateway_status_variant">
                    {{ $pembayaran->gateway_status_label }}
                </x-status-badge>
            </div>

            <div class="grid gap-3 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-[var(--text-muted)]">Total tagihan</span>
                    <span class="text-sm font-semibold text-[var(--text-strong)]">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-[var(--text-muted)]">Metode pembayaran</span>
                    <span class="text-sm font-semibold text-[var(--text-strong)]">{{ $pembayaran->metode_pembayaran_label }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-[var(--text-muted)]">Merchant QRIS</span>
                    <span class="text-sm font-semibold text-[var(--text-strong)]">{{ $merchantName }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-[var(--text-muted)]">Tanggal input</span>
                    <span class="text-sm font-semibold text-[var(--text-strong)]">{{ $pembayaran->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</span>
                </div>
                @if ($gateway['expires_at'] ?? null)
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-[var(--text-muted)]">Berakhir</span>
                        <span class="text-sm font-semibold text-[var(--text-strong)]">{{ $gateway['expires_at']->translatedFormat('d M Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </x-card>

        @if (session('success'))
            <x-card class="border-[var(--success)] bg-[color-mix(in_srgb,var(--success)_10%,var(--bg-card))] text-[var(--text-strong)]">
                {{ session('success') }}
            </x-card>
        @endif

        @if (session('warning'))
            <x-card class="border-[var(--danger)] bg-[color-mix(in_srgb,var(--danger)_10%,var(--bg-card))] text-[var(--text-strong)]">
                {{ session('warning') }}
            </x-card>
        @endif

        <x-card class="space-y-4 border-[var(--border-main)] bg-[var(--bg-card)]">
            @if ($paid)
                <div class="rounded-2xl border border-[color-mix(in_srgb,var(--success)_28%,var(--border-main))] bg-[color-mix(in_srgb,var(--success)_10%,var(--bg-surface))] p-4">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Pembayaran sudah lunas</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">
                        {{ $gateway['customer_name'] ?? $pembayaran->gateway_customer_name ?? 'Customer' }}
                        telah menyelesaikan pembayaran melalui {{ $gateway['method_by'] ?? $pembayaran->gateway_method_by ?? 'QRIS' }}.
                    </p>
                </div>
            @elseif ($expired)
                <div class="rounded-2xl border border-[color-mix(in_srgb,var(--danger)_28%,var(--border-main))] bg-[color-mix(in_srgb,var(--danger)_10%,var(--bg-surface))] p-4">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Sesi pembayaran kedaluwarsa</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">
                        Link ini sudah melewati batas waktu pembayaran. Minta admin membuat sesi QRIS baru untuk melanjutkan transaksi.
                    </p>
                </div>
            @elseif (filled($gateway['qr_image'] ?? null))
                <div class="space-y-4">
                    <div class="flex items-center justify-center rounded-3xl border border-[var(--border-main)] bg-white p-4">
                        <img src="{{ $gateway['qr_image'] }}" alt="QRIS payment code" class="max-h-[18rem] w-full max-w-[18rem] object-contain">
                    </div>

                    <div class="text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--text-muted)]">Merchant QRIS</p>
                        <p class="mt-2 text-lg font-semibold text-[var(--text-strong)]">{{ $merchantName }}</p>
                    </div>
                </div>

                @if (filled($gateway['qris_text'] ?? null))
                    <details class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-[var(--text-strong)]">Lihat data QRIS</summary>
                        <p class="mt-3 break-words font-mono text-xs leading-6 text-[var(--text-muted)]">{{ $gateway['qris_text'] }}</p>
                    </details>
                @endif
            @else
                <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">QR code belum tersedia</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">
                        Sesi pembayaran ini belum memiliki QR aktif. Hubungi admin untuk membuat ulang sesi QRIS.
                    </p>
                </div>
            @endif

            @if (! $paid)
                <form method="POST" action="{{ $syncUrl }}">
                    @csrf
                    <button type="submit" class="nyuci-btn-secondary w-full">
                        Muat Ulang Status
                    </button>
                </form>
            @endif
        </x-card>

        <x-card class="space-y-3 border-[var(--border-main)] bg-[var(--bg-card)]">
            <p class="text-sm font-semibold text-[var(--text-strong)]">Langkah pembayaran</p>
            <ol class="space-y-2 text-sm leading-6 text-[var(--text-muted)]">
                <li>1. Scan QR yang tampil di atas dengan aplikasi pembayaran yang mendukung QRIS.</li>
                <li>2. Selesaikan pembayaran sesuai nominal tagihan.</li>
                <li>3. Klik muat ulang status setelah pembayaran dikonfirmasi admin.</li>
            </ol>
        </x-card>

        <x-card class="space-y-3 border-[var(--border-main)] bg-[var(--bg-card)]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Link checkout</p>
                    <p class="mt-1 text-xs text-[var(--text-muted)]">Bagikan link ini ke pelanggan jika perlu.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full border border-[var(--border-soft)] bg-[var(--bg-surface)] px-3 py-2 text-xs font-semibold text-[var(--text-main)] transition hover:border-[var(--primary)] hover:text-[var(--text-strong)]"
                    x-data
                    @click="navigator.clipboard.writeText(@js($checkoutUrl)); $el.textContent = 'Tersalin'; window.setTimeout(() => { $el.textContent = 'Salin'; }, 1500)"
                >
                    Salin
                </button>
            </div>

            <input
                type="text"
                readonly
                value="{{ $checkoutUrl }}"
                class="w-full rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)]"
            >
        </x-card>
    </div>
</x-guest-layout>
