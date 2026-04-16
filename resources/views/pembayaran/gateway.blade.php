<x-guest-layout title="Checkout QRIS" variant="centered">
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
        $expiresAt = $gateway['expires_at'] ?? null;
    @endphp

    <div
        class="space-y-5"
        x-data="{
            expiresAt: @js($expiresAt?->toIso8601String()),
            remainingLabel: null,
            countdownExpired: @js($expired),
            intervalId: null,
            init() {
                this.updateCountdown();

                if (this.expiresAt && !@js($paid) && !this.countdownExpired) {
                    this.intervalId = window.setInterval(() => this.updateCountdown(), 1000);
                }
            },
            updateCountdown() {
                if (!this.expiresAt || @js($paid)) {
                    return;
                }

                const diff = new Date(this.expiresAt).getTime() - Date.now();

                if (diff <= 0) {
                    this.countdownExpired = true;
                    this.remainingLabel = '00:00';

                    if (this.intervalId) {
                        window.clearInterval(this.intervalId);
                    }

                    return;
                }

                const totalSeconds = Math.floor(diff / 1000);
                const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
                const seconds = String(totalSeconds % 60).padStart(2, '0');

                this.remainingLabel = `${hours}:${minutes}:${seconds}`;
            }
        }"
        x-init="init()"
    >
        <x-card class="space-y-6 border-[var(--border-main)] bg-[var(--bg-card)] text-center">
            <div class="flex flex-col items-center gap-3">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--text-muted)]">Checkout QRIS</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Pembayaran {{ $customerName }}</h1>
                    <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                        Invoice #{{ $pembayaran->id }} untuk layanan {{ $serviceName }}. QR dibuat lokal dari payload QRIS statis, tanpa simulator eksternal.
                    </p>
                </div>

                <x-status-badge :variant="$pembayaran->gateway_status_variant" class="self-center">
                    {{ $pembayaran->gateway_status_label }}
                </x-status-badge>
            </div>

            <div class="mx-auto grid w-full max-w-2xl gap-3 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4 sm:grid-cols-2">
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
                @if ($expiresAt)
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-[var(--text-muted)]">Berakhir</span>
                        <span class="text-sm font-semibold text-[var(--text-strong)]">{{ $expiresAt->translatedFormat('d M Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </x-card>

        @if (session('success'))
            <x-card class="border-[var(--success)] bg-[color-mix(in_srgb,var(--success)_10%,var(--bg-card))] text-center text-[var(--text-strong)]">
                {{ session('success') }}
            </x-card>
        @endif

        @if (session('warning'))
            <x-card class="border-[var(--danger)] bg-[color-mix(in_srgb,var(--danger)_10%,var(--bg-card))] text-center text-[var(--text-strong)]">
                {{ session('warning') }}
            </x-card>
        @endif

        @if (! $paid && $expiresAt)
            <x-card x-show="!countdownExpired" class="border-[color-mix(in_srgb,var(--primary)_22%,var(--border-main))] bg-[color-mix(in_srgb,var(--primary)_7%,var(--bg-card))]">
                <div class="flex flex-col items-center gap-4 text-center">
                    <div class="max-w-xl">
                        <p class="text-sm font-semibold text-[var(--text-strong)]">Sisa waktu pembayaran</p>
                        <p class="mt-1 text-sm text-[var(--text-muted)]">Selesaikan pembayaran sebelum timer habis agar QR tetap aktif.</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] px-4 py-3 text-lg font-semibold tracking-[0.18em] text-[var(--text-strong)]" x-text="remainingLabel ?? '--:--:--'"></div>
                </div>
            </x-card>
        @endif

        <x-card class="space-y-5 border-[var(--border-main)] bg-[var(--bg-card)] text-center">
            @if ($paid)
                <div class="rounded-2xl border border-[color-mix(in_srgb,var(--success)_28%,var(--border-main))] bg-[color-mix(in_srgb,var(--success)_10%,var(--bg-surface))] p-4">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Pembayaran sudah lunas</p>
                    <p class="mx-auto mt-2 max-w-xl text-sm text-[var(--text-muted)]">
                        {{ $gateway['customer_name'] ?? $pembayaran->gateway_customer_name ?? 'Customer' }}
                        telah menyelesaikan pembayaran melalui {{ $gateway['method_by'] ?? $pembayaran->gateway_method_by ?? 'QRIS' }}.
                    </p>
                </div>
            @elseif ($expired)
                <div class="rounded-2xl border border-[color-mix(in_srgb,var(--danger)_28%,var(--border-main))] bg-[color-mix(in_srgb,var(--danger)_10%,var(--bg-surface))] p-4">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Sesi pembayaran kedaluwarsa</p>
                    <p class="mx-auto mt-2 max-w-xl text-sm text-[var(--text-muted)]">
                        Link ini sudah melewati batas waktu pembayaran. Hubungi admin untuk membuat sesi QRIS baru sebelum melanjutkan transaksi.
                    </p>
                </div>
            @else
                <div x-show="countdownExpired" x-cloak class="rounded-2xl border border-[color-mix(in_srgb,var(--danger)_28%,var(--border-main))] bg-[color-mix(in_srgb,var(--danger)_10%,var(--bg-surface))] p-4">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Waktu pembayaran habis</p>
                    <p class="mx-auto mt-2 max-w-xl text-sm text-[var(--text-muted)]">
                        QRIS ini sudah tidak aktif. Minta admin merefresh atau membuat ulang sesi pembayaran.
                    </p>
                </div>

                @if (filled($gateway['qr_image'] ?? null))
                    <div x-show="!countdownExpired" class="space-y-4">
                        <div class="flex items-center justify-center rounded-3xl border border-[var(--border-main)] bg-white p-4">
                            <img src="{{ $gateway['qr_image'] }}" alt="QRIS payment code" class="max-h-[18rem] w-full max-w-[18rem] object-contain">
                        </div>

                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--text-muted)]">Merchant QRIS</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--text-strong)]">{{ $merchantName }}</p>
                        </div>
                    </div>
                @else
                    <div x-show="!countdownExpired" class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                        <p class="text-sm font-semibold text-[var(--text-strong)]">QR code belum tersedia</p>
                        <p class="mx-auto mt-2 max-w-xl text-sm text-[var(--text-muted)]">
                            Sesi pembayaran ini belum memiliki QR aktif. Hubungi admin untuk membuat ulang sesi QRIS.
                        </p>
                    </div>
                @endif

                @if (filled($gateway['qris_text'] ?? null))
                    <details x-show="!countdownExpired" class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-[var(--text-strong)]">Lihat data QRIS</summary>
                        <p class="mt-3 break-words font-mono text-xs leading-6 text-[var(--text-muted)]">{{ $gateway['qris_text'] }}</p>
                    </details>
                @endif
            @endif

            @if (! $paid && ! $expired)
                <form x-show="!countdownExpired" method="POST" action="{{ $syncUrl }}">
                    @csrf
                    <button type="submit" class="nyuci-btn-secondary w-full">
                        Muat Ulang Status
                    </button>
                </form>
            @endif
        </x-card>

        <x-card class="space-y-3 border-[var(--border-main)] bg-[var(--bg-card)] text-center">
            <p class="text-sm font-semibold text-[var(--text-strong)]">Langkah pembayaran</p>
            <ol class="mx-auto max-w-xl space-y-2 text-left text-sm leading-6 text-[var(--text-muted)]">
                <li>1. Scan QR yang tampil di atas dengan aplikasi pembayaran yang mendukung QRIS.</li>
                <li>2. Selesaikan pembayaran sesuai nominal tagihan sebelum timer habis.</li>
                <li>3. Jika pembayaran sudah dilakukan, muat ulang status untuk melihat konfirmasi terbaru.</li>
            </ol>
        </x-card>

        <x-card class="space-y-3 border-[var(--border-main)] bg-[var(--bg-card)] text-center">
            <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between sm:text-left">
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
