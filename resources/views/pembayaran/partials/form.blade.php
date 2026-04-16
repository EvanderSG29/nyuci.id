@php
    $currentPayment = $payment ?? null;
    $selectedLaundry = $selectedLaundry ?? null;
    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit';
    $selectedStatus = old('status', $currentPayment?->status ?? 'belum_bayar');
    $selectedMethod = old('metode_pembayaran', $currentPayment?->metode_pembayaran ?? 'cash');
    $selectedDate = old('tgl_pembayaran', $currentPayment?->tgl_pembayaran?->format('Y-m-d') ?? now()->format('Y-m-d'));
    $selectedLaundryId = (string) old('laundry_id', $selectedLaundryId ?? $selectedLaundry?->id ?? '');
    $laundryCollection = $isEdit ? collect([$selectedLaundry])->filter() : collect($laundries ?? []);
    $laundryMap = $laundryCollection->mapWithKeys(function ($laundry) {
        $total = (int) round(($laundry->qty ?? 0) * ($laundry->jasa?->harga ?? 0));

        return [
            (string) $laundry->id => [
                'order_label' => trim(($laundry->klien?->nama_klien ?? $laundry->nama).' - '.($laundry->jasa?->nama_jasa ?? $laundry->jenis_jasa_label ?? 'Layanan')),
                'customer_name' => $laundry->klien?->nama_klien ?? $laundry->nama ?? 'Pelanggan belum tersedia',
                'customer_phone' => $laundry->klien?->no_hp_klien ?? $laundry->no_hp ?? 'Nomor belum tersedia',
                'service_name' => $laundry->jasa?->nama_jasa ?? $laundry->jenis_jasa_label ?? 'Layanan belum tersedia',
                'unit_label' => $laundry->satuan_label,
                'qty_label' => $laundry->formatted_qty.' '.($laundry->jasa?->satuan ?? ''),
                'total' => $total,
                'total_formatted' => 'Rp '.number_format($total, 0, ',', '.'),
            ],
        ];
    });
    $initialSelectedLaundry = $selectedLaundry
        ? [
            'order_label' => trim(($selectedLaundry->klien?->nama_klien ?? $selectedLaundry->nama).' - '.($selectedLaundry->jasa?->nama_jasa ?? $selectedLaundry->jenis_jasa_label ?? 'Layanan')),
            'customer_name' => $selectedLaundry->klien?->nama_klien ?? $selectedLaundry->nama ?? 'Pelanggan belum tersedia',
            'customer_phone' => $selectedLaundry->klien?->no_hp_klien ?? $selectedLaundry->no_hp ?? 'Nomor belum tersedia',
            'service_name' => $selectedLaundry->jasa?->nama_jasa ?? $selectedLaundry->jenis_jasa_label ?? 'Layanan belum tersedia',
            'unit_label' => $selectedLaundry->satuan_label,
            'qty_label' => $selectedLaundry->formatted_qty.' '.($selectedLaundry->jasa?->satuan ?? ''),
            'total' => (int) round(($selectedLaundry->qty ?? 0) * ($selectedLaundry->jasa?->harga ?? 0)),
            'total_formatted' => 'Rp '.number_format((int) round(($selectedLaundry->qty ?? 0) * ($selectedLaundry->jasa?->harga ?? 0)), 0, ',', '.'),
        ]
        : null;
    $existingGatewayCheckoutUrl = $currentPayment?->gateway_checkout_url;
    $existingGatewayRefreshFormId = $currentPayment ? 'pembayaran-gateway-refresh-'.$currentPayment->id : null;
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-6 text-[var(--text-main)]"
    x-data="{
        checkoutTabName: @js($checkoutTabName ?? config('payment_gateway.checkout_window_name', 'nyuci-qris-checkout')),
        selectedLaundryId: @js($selectedLaundryId),
        selectedMethod: @js($selectedMethod),
        selectedStatus: @js($selectedStatus),
        laundries: @js($laundryMap),
        currentLaundry: @js($initialSelectedLaundry),
        updateLaundry(id) {
            this.selectedLaundryId = id ?? '';

            if (id && this.laundries[id]) {
                this.currentLaundry = this.laundries[id];
                return;
            }

            this.currentLaundry = null;
        },
        prepareCheckoutTab() {
            if (this.selectedMethod !== 'qris' || this.selectedStatus !== 'belum_bayar') {
                return;
            }

            window.open('about:blank', this.checkoutTabName, 'noopener');
        }
    }"
    x-init="updateLaundry(selectedLaundryId)"
    @submit="prepareCheckoutTab()"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="space-y-4 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4 sm:p-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Order Laundry</p>
            <h3 class="mt-2 text-lg font-semibold text-[var(--text-strong)]">{{ $isEdit ? 'Order yang terhubung dengan pembayaran ini' : 'Pilih order yang akan dibayar' }}</h3>
            <p class="mt-1 text-sm text-[var(--text-muted)]">
                {{ $isEdit ? 'Order tetap sama, tetapi ringkasan di bawah akan terus menampilkan detail laundry yang sedang dibayar.' : 'Setelah order dipilih, ringkasan pelanggan, layanan, dan total akan terisi otomatis.' }}
            </p>
        </div>

        <div>
            <label for="laundry_id" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Order Laundry</label>

            @if ($isEdit)
                <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-4 py-3 text-sm font-semibold text-[var(--text-strong)]">
                    {{ $selectedLaundry?->klien?->nama_klien ?? $selectedLaundry?->nama ?? 'Order tidak tersedia' }}
                    <span class="text-[var(--text-muted)]">/</span>
                    {{ $selectedLaundry?->jasa?->nama_jasa ?? $selectedLaundry?->jenis_jasa_label ?? 'Layanan' }}
                    <span class="text-[var(--text-muted)]">/</span>
                    {{ $selectedLaundry?->satuan_label ?? '-' }}
                </div>
                <input type="hidden" name="laundry_id" value="{{ $selectedLaundryId }}">
            @else
                <select
                    id="laundry_id"
                    name="laundry_id"
                    required
                    x-model="selectedLaundryId"
                    @change="updateLaundry($event.target.value)"
                    class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('laundry_id') border-[var(--danger)] @enderror"
                >
                    <option value="">-- Pilih Order Laundry --</option>
                    @foreach ($laundryCollection as $laundry)
                        <option value="{{ $laundry->id }}" @selected($selectedLaundryId === (string) $laundry->id)>
                            {{ $laundry->klien?->nama_klien ?? $laundry->nama }} - {{ $laundry->jasa?->nama_jasa ?? $laundry->jenis_jasa_label ?? 'Layanan' }} / {{ $laundry->satuan_label }}
                        </option>
                    @endforeach
                </select>
            @endif

            @error('laundry_id')
                <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <section class="space-y-4 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4 sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Ringkasan Order</p>
                <h3 class="mt-2 text-lg font-semibold text-[var(--text-strong)]">Info order yang akan dibayar</h3>
                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    Ringkasan ini hanya baca. Total dihitung otomatis dari qty x harga jasa.
                </p>
            </div>

            @if ($currentPayment && ($currentPayment->gateway_token || $currentPayment->gateway_status))
                <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Status QRIS</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <x-status-badge :variant="$currentPayment->gateway_status_variant">
                            {{ $currentPayment->gateway_status_label }}
                        </x-status-badge>
                        @if ($currentPayment->gateway_expires_at)
                            <span class="text-xs text-[var(--text-muted)]">Aktif sampai {{ $currentPayment->gateway_expires_at->translatedFormat('d M Y H:i') }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div x-show="currentLaundry" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Pelanggan</p>
                <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]" x-text="currentLaundry ? currentLaundry.customer_name : 'Pelanggan belum tersedia'"></p>
                <p class="mt-1 text-sm text-[var(--text-muted)]" x-text="currentLaundry ? currentLaundry.customer_phone : 'Nomor belum tersedia'"></p>
            </div>

            <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Layanan</p>
                <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]" x-text="currentLaundry ? currentLaundry.service_name : 'Layanan belum tersedia'"></p>
                <p class="mt-1 text-sm text-[var(--text-muted)]" x-text="currentLaundry ? currentLaundry.unit_label : '-'"></p>
            </div>

            <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Qty</p>
                <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]" x-text="currentLaundry ? currentLaundry.qty_label : 'Belum tersedia'"></p>
            </div>

            <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Total</p>
                <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]" x-text="currentLaundry ? currentLaundry.total_formatted : 'Pilih order terlebih dahulu'"></p>
            </div>
        </div>

        <div x-show="!currentLaundry" class="rounded-2xl border border-dashed border-[var(--border-soft)] bg-[var(--bg-card)] p-4 text-sm text-[var(--text-muted)]">
            Pilih order laundry terlebih dahulu agar pelanggan, layanan, qty, dan total pembayaran muncul di sini.
        </div>
    </section>

    <section class="space-y-5 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4 sm:p-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Data Pembayaran</p>
            <h3 class="mt-2 text-lg font-semibold text-[var(--text-strong)]">Atur metode, status, dan catatan pembayaran</h3>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="metode_pembayaran" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Metode Pembayaran</label>
                <select
                    id="metode_pembayaran"
                    name="metode_pembayaran"
                    required
                    x-model="selectedMethod"
                    class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('metode_pembayaran') border-[var(--danger)] @enderror"
                >
                    @foreach ($paymentMethods as $value => $label)
                        <option value="{{ $value }}" @selected($selectedMethod === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('metode_pembayaran')
                    <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Status Pembayaran</label>
                <select
                    id="status"
                    name="status"
                    required
                    x-model="selectedStatus"
                    class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('status') border-[var(--danger)] @enderror"
                >
                    <option value="belum_bayar" @selected($selectedStatus === 'belum_bayar')>Belum Bayar</option>
                    <option value="sudah_bayar" @selected($selectedStatus === 'sudah_bayar')>Sudah Bayar</option>
                </select>
                @error('status')
                    <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div x-show="selectedStatus === 'sudah_bayar'" x-cloak>
            <label for="tgl_pembayaran" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Tanggal Pembayaran</label>
            <input
                type="date"
                id="tgl_pembayaran"
                name="tgl_pembayaran"
                value="{{ $selectedDate }}"
                :disabled="selectedStatus !== 'sudah_bayar'"
                class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('tgl_pembayaran') border-[var(--danger)] @enderror"
            >
            @error('tgl_pembayaran')
                <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="selectedStatus !== 'sudah_bayar'" x-cloak class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] p-4 text-sm text-[var(--text-muted)]">
            Tanggal pembayaran akan diisi otomatis saat transaksi ditandai lunas atau saat pembayaran QRIS terkonfirmasi.
        </div>

        <div>
            <label for="catatan" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Catatan</label>
            <textarea
                id="catatan"
                name="catatan"
                rows="4"
                placeholder="Tambahkan catatan internal bila perlu"
                class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('catatan') border-[var(--danger)] @enderror"
            >{{ old('catatan', $currentPayment?->catatan) }}</textarea>
            @error('catatan')
                <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="selectedMethod === 'qris'" x-cloak class="rounded-2xl border border-[color-mix(in_srgb,var(--primary)_22%,var(--border-main))] bg-[color-mix(in_srgb,var(--primary)_8%,var(--bg-card))] p-4">
            <p class="text-sm font-semibold text-[var(--text-strong)]">Flow QRIS</p>
            <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                Saat pembayaran disimpan dalam status belum bayar, sistem akan membuat atau memakai sesi QRIS aktif lalu langsung membuka halaman checkout publik dengan timer pembayaran.
            </p>

            @if ($currentPayment && $existingGatewayCheckoutUrl)
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ $existingGatewayCheckoutUrl }}" target="_blank" rel="noopener" class="nyuci-btn-primary">
                        Buka Checkout
                    </a>

                    <button
                        type="button"
                        class="nyuci-btn-secondary"
                        x-data
                        @click="navigator.clipboard.writeText(@js($existingGatewayCheckoutUrl)); $el.textContent = 'Tersalin'; window.setTimeout(() => { $el.textContent = 'Salin Link'; }, 1500)"
                    >
                        Salin Link
                    </button>

                    @if ($existingGatewayRefreshFormId)
                        <button type="submit" form="{{ $existingGatewayRefreshFormId }}" class="nyuci-btn-secondary">
                            Refresh QRIS
                        </button>
                    @endif
                </div>

                @if ($currentPayment->gateway_expires_at)
                    <p class="mt-3 text-xs text-[var(--text-muted)]">
                        Sesi saat ini berakhir pada {{ $currentPayment->gateway_expires_at->translatedFormat('d M Y H:i') }}.
                    </p>
                @endif
            @else
                <p class="mt-3 text-sm text-[var(--text-muted)]">
                    Belum ada sesi QRIS aktif. Simpan pembayaran ini lebih dulu untuk membuka checkout pelanggan.
                </p>
            @endif
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 border-t border-[var(--border-main)] pt-4 sm:flex-row sm:justify-end">
        <a href="{{ route('pembayaran.index') }}" class="nyuci-btn-secondary">
            Batal
        </a>
        <button type="submit" class="nyuci-btn-primary">
            {{ $submitLabel }}
        </button>
    </div>
</form>

@if ($existingGatewayRefreshFormId)
    <form
        id="{{ $existingGatewayRefreshFormId }}"
        method="POST"
        action="{{ route('pembayaran.gateway.issue', $currentPayment) }}"
        class="hidden"
        x-data
        @submit="window.open('about:blank', @js($checkoutTabName ?? config('payment_gateway.checkout_window_name', 'nyuci-qris-checkout')), 'noopener')"
    >
        @csrf
    </form>
@endif
