<section>
    @php
        $resolvedPayload = $store?->resolvedPaymentGatewayQrisPayload() ?? trim((string) config('payment_gateway.qris_static.payload', ''));
        $resolvedMerchantName = $store?->resolvedPaymentGatewayQrisMerchantName() ?? (trim((string) config('payment_gateway.qris_static.merchant_name', '')) ?: null);
        $resolvedTtl = $store?->resolvedPaymentGatewayCheckoutTtlMinutes() ?? max((int) config('payment_gateway.checkout_ttl_minutes', 30), 1);
        $configSource = $store?->paymentGatewayQrisConfigSource() ?? 'server';
    @endphp

    <header>
        <h2 class="text-lg font-medium text-[var(--text-strong)]">
            QRIS Pembayaran
        </h2>

        <p class="mt-1 text-sm text-[var(--text-muted)]">
            Simpan payload QRIS statis toko agar checkout pelanggan dapat dibuat otomatis dari nominal order.
        </p>
    </header>

    <form id="settings-payment-qris-form" method="post" action="{{ route('settings.payment.qris.update') }}" class="mt-6 space-y-6" data-settings-form>
        @csrf
        @method('patch')

        <div class="grid gap-3 md:grid-cols-3">
            <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Sumber konfigurasi</p>
                <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]">{{ $configSource === 'toko' ? 'Toko ini' : 'Fallback server' }}</p>
            </div>

            <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Merchant aktif</p>
                <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]">{{ $resolvedMerchantName ?: 'Belum diatur' }}</p>
            </div>

            <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Durasi checkout</p>
                <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]">{{ $resolvedTtl }} menit</p>
            </div>
        </div>

        <div>
            <x-input-label for="payment_gateway_qris_merchant_name" :value="__('Nama Merchant QRIS')" />
            <x-text-input
                id="payment_gateway_qris_merchant_name"
                name="payment_gateway_qris_merchant_name"
                type="text"
                class="mt-1 block w-full"
                :value="old('payment_gateway_qris_merchant_name', $store?->payment_gateway_qris_merchant_name)"
                placeholder="Contoh: Nyuci Laundry"
            />
            <p class="mt-2 text-sm text-[var(--text-muted)]">
                Kosongkan jika ingin mengikuti nama merchant dari konfigurasi server atau payload QRIS.
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('payment_gateway_qris_merchant_name')" />
        </div>

        <div>
            <x-input-label for="payment_gateway_qris_payload" :value="__('Payload QRIS Statis')" />
            <textarea
                id="payment_gateway_qris_payload"
                name="payment_gateway_qris_payload"
                rows="5"
                class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 font-mono text-sm text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                placeholder="000201010211..."
            >{{ old('payment_gateway_qris_payload', $store?->payment_gateway_qris_payload) }}</textarea>
            <p class="mt-2 text-sm text-[var(--text-muted)]">
                Tempel string payload QRIS statis dari penyedia pembayaran Anda. Jika kosong, toko ini memakai fallback server bila tersedia.
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('payment_gateway_qris_payload')" />
        </div>

        <div>
            <x-input-label for="payment_gateway_checkout_ttl_minutes" :value="__('Durasi Checkout QRIS (menit)')" />
            <x-text-input
                id="payment_gateway_checkout_ttl_minutes"
                name="payment_gateway_checkout_ttl_minutes"
                type="number"
                min="1"
                max="1440"
                class="mt-1 block w-full"
                :value="old('payment_gateway_checkout_ttl_minutes', $store?->payment_gateway_checkout_ttl_minutes)"
                placeholder="{{ max((int) config('payment_gateway.checkout_ttl_minutes', 30), 1) }}"
            />
            <p class="mt-2 text-sm text-[var(--text-muted)]">
                Kosongkan untuk memakai durasi default server. Nilai ini dipakai untuk timer halaman checkout publik.
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('payment_gateway_checkout_ttl_minutes')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan QRIS') }}</x-primary-button>
        </div>
    </form>
</section>
