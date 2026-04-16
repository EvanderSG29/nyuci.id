<section>
    @php
        $store = $user->toko;
        $resolvedPayload = $store?->resolvedPaymentGatewayQrisPayload() ?? trim((string) config('payment_gateway.qris_static.payload', ''));
        $resolvedMerchantName = $store?->resolvedPaymentGatewayQrisMerchantName() ?? (trim((string) config('payment_gateway.qris_static.merchant_name', '')) ?: null);
        $resolvedTtl = $store?->resolvedPaymentGatewayCheckoutTtlMinutes() ?? max((int) config('payment_gateway.checkout_ttl_minutes', 30), 1);
        $configSource = $store?->paymentGatewayQrisConfigSource() ?? 'server';
    @endphp

    <header>
        <h2 class="text-lg font-medium text-[var(--text-strong)]">
            Informasi Toko
        </h2>

        <p class="mt-1 text-sm text-[var(--text-muted)]">
            Atur identitas toko yang akan dipakai di dashboard dan modul operasional Nyuci.id.
        </p>
    </header>

    <form method="post" action="{{ route('pengaturan-toko.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="nama_toko" :value="__('Nama Toko')" />
            <x-text-input id="nama_toko" name="nama_toko" type="text" class="mt-1 block w-full" :value="old('nama_toko', $user->toko?->nama_toko)" required autocomplete="organization" />
            <x-input-error class="mt-2" :messages="$errors->get('nama_toko')" />
        </div>

        <div>
            <x-input-label for="no_hp" :value="__('No. HP Toko')" />
            <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full" :value="old('no_hp', $user->toko?->no_hp)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
        </div>

        <div>
            <x-input-label for="alamat" :value="__('Alamat Toko')" />
            <textarea
                id="alamat"
                name="alamat"
                rows="4"
                class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
            >{{ old('alamat', $user->toko?->alamat) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
        </div>

        <div class="space-y-4 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-[var(--text-strong)]">QRIS Pembayaran</h3>
                    <p class="mt-1 text-sm leading-6 text-[var(--text-muted)]">
                        Isi payload QRIS statis toko Anda. Sistem akan membuat QR transaksi otomatis dari nominal order, jadi admin tidak perlu upload gambar QR manual.
                    </p>
                </div>

                <span class="nyuci-badge {{ ($store?->hasResolvedPaymentGatewayQrisConfig() ?? ($resolvedPayload !== '')) ? 'nyuci-badge-success' : 'nyuci-badge-pending' }}">
                    {{ ($store?->hasResolvedPaymentGatewayQrisConfig() ?? ($resolvedPayload !== '')) ? 'Siap dipakai' : 'Belum dikonfigurasi' }}
                </span>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Sumber konfigurasi</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]">{{ $configSource === 'toko' ? 'Toko ini' : 'Fallback server' }}</p>
                </div>
                <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Merchant aktif</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]">{{ $resolvedMerchantName ?: 'Belum diatur' }}</p>
                </div>
                <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] p-4">
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
        </div>

        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-[var(--text-strong)]">Mode Tampilan</h3>
                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    Pilih tema terang, gelap, atau ikuti pengaturan sistem perangkat.
                </p>
            </div>

            <x-theme-switch />

            <p class="text-sm text-[var(--text-muted)]">
                Tema mengikuti pilihan Anda atau pengaturan sistem. Latar belakang aplikasi tetap netral agar semua card dan panel
                menyatu dengan mode terang maupun gelap.
            </p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan Pengaturan') }}</x-primary-button>
        </div>
    </form>
</section>
