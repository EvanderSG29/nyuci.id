@php
    $currentPayment = $payment ?? null;
    $selectedLaundry = $selectedLaundry ?? null;
    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit';
    $selectedStatus = old('status', $currentPayment?->status ?? 'belum_bayar');
    $selectedMethod = old('metode_pembayaran', $currentPayment?->metode_pembayaran ?? 'cash');
    $selectedDate = old('tgl_pembayaran', $currentPayment?->tgl_pembayaran?->format('Y-m-d') ?? now()->format('Y-m-d'));
    $selectedLaundryId = (string) ($selectedLaundryId ?? '');
    $laundryMap = collect($laundries ?? [])->mapWithKeys(function ($laundry) {
        $total = (int) round(($laundry->qty ?? 0) * ($laundry->jasa?->harga ?? 0));

        return [
            $laundry->id => [
                'customer' => $laundry->klien?->nama_klien ?? $laundry->nama,
                'phone' => $laundry->klien?->no_hp_klien ?? $laundry->no_hp,
                'service' => $laundry->jasa?->nama_jasa ?? $laundry->jenis_jasa,
                'unit' => $laundry->satuan_label,
                'total' => $total,
                'total_formatted' => 'Rp '.number_format($total, 0, ',', '.'),
            ],
        ];
    });
    $initialSelectedLaundry = $selectedLaundry
        ? [
            'customer' => $selectedLaundry->klien?->nama_klien ?? $selectedLaundry->nama,
            'phone' => $selectedLaundry->klien?->no_hp_klien ?? $selectedLaundry->no_hp,
            'service' => $selectedLaundry->jasa?->nama_jasa ?? $selectedLaundry->jenis_jasa,
            'unit' => $selectedLaundry->satuan_label,
            'total' => (int) round(($selectedLaundry->qty ?? 0) * ($selectedLaundry->jasa?->harga ?? 0)),
        ]
        : null;
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-6 text-[var(--text-main)]"
    x-data="{
        selectedLaundryId: @js($selectedLaundryId),
        laundries: @js($laundryMap),
        currentLaundry: @js($initialSelectedLaundry),
        updateLaundry(id) {
            this.currentLaundry = id && this.laundries[id] ? this.laundries[id] : null;
        },
        currency(value) {
            if (value === null || value === undefined) {
                return '-';
            }
            return new Intl.NumberFormat('id-ID').format(value);
        }
    }"
    x-init="updateLaundry(selectedLaundryId)"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="laundry_id" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Order Laundry</label>

        @if ($isEdit)
            <input type="text" readonly value="{{ $selectedLaundry?->klien?->nama_klien ?? $selectedLaundry?->nama }} - {{ $selectedLaundry?->jasa?->nama_jasa ?? $selectedLaundry?->jenis_jasa }} / {{ $selectedLaundry?->satuan_label }}" class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-[var(--text-main)]">
            <input type="hidden" name="laundry_id" value="{{ $selectedLaundryId }}">
            @error('laundry_id')
                <p class="mt-1 text-sm text-[var(--danger)]">{{ $message }}</p>
            @enderror
        @else
            <select id="laundry_id" name="laundry_id" required x-model="selectedLaundryId" @change="updateLaundry($event.target.value)" class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('laundry_id') border-[var(--danger)] @enderror">
                <option value="">-- Pilih Order Laundry --</option>
                @foreach ($laundries as $laundry)
                    <option value="{{ $laundry->id }}" @selected((string) old('laundry_id', $selectedLaundryId) === (string) $laundry->id)>
                        {{ $laundry->klien?->nama_klien ?? $laundry->nama }} - {{ $laundry->jasa?->nama_jasa ?? $laundry->jenis_jasa }} / {{ $laundry->satuan_label }}
                    </option>
                @endforeach
            </select>
            @error('laundry_id')
                <p class="mt-1 text-sm text-[var(--danger)]">{{ $message }}</p>
            @enderror
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Pelanggan</label>
            <input type="text" readonly :value="currentLaundry ? `${currentLaundry.customer} (${currentLaundry.phone})` : '-'" class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-[var(--text-main)]">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Layanan</label>
            <input type="text" readonly :value="currentLaundry ? `${currentLaundry.service} / ${currentLaundry.unit}` : '-'" class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-[var(--text-main)]">
        </div>
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Total Pembayaran</label>
        <div class="rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm font-semibold text-[var(--text-main)]">
            <span x-text="currentLaundry ? `Rp ${currency(currentLaundry.total)}` : '-'"></span>
        </div>
        <p class="mt-2 text-xs text-[var(--text-muted)]">Total dihitung otomatis dari `qty x harga jasa`.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="metode_pembayaran" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Metode Pembayaran</label>
            <select id="metode_pembayaran" name="metode_pembayaran" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('metode_pembayaran') border-[var(--danger)] @enderror">
                @foreach ($paymentMethods as $value => $label)
                    <option value="{{ $value }}" @selected($selectedMethod === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-[var(--text-muted)]">Pilih <span class="font-semibold text-[var(--text-strong)]">QRIS</span> jika transaksi ini akan dibuka lewat checkout publik.</p>
            @error('metode_pembayaran')
                <p class="mt-1 text-sm text-[var(--danger)]">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tgl_pembayaran" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Tanggal Pembayaran</label>
            <input type="date" id="tgl_pembayaran" name="tgl_pembayaran" value="{{ $selectedDate }}" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('tgl_pembayaran') border-[var(--danger)] @enderror">
            @error('tgl_pembayaran')
                <p class="mt-1 text-sm text-[var(--danger)]">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="catatan" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Catatan</label>
        <textarea id="catatan" name="catatan" rows="4" placeholder="Tambahkan catatan pembayaran bila perlu" class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('catatan') border-[var(--danger)] @enderror">{{ old('catatan', $currentPayment?->catatan) }}</textarea>
        @error('catatan')
            <p class="mt-1 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Status Pembayaran</label>
        <select id="status" name="status" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('status') border-[var(--danger)] @enderror">
            <option value="belum_bayar" @selected($selectedStatus === 'belum_bayar')>Belum Bayar</option>
            <option value="sudah_bayar" @selected($selectedStatus === 'sudah_bayar')>Sudah Bayar</option>
        </select>
        @error('status')
            <p class="mt-1 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-[var(--border-main)] pt-4 sm:flex-row sm:justify-end">
        <a href="{{ route('pembayaran.index') }}" class="nyuci-btn-secondary">
            Batal
        </a>
        <button type="submit" class="nyuci-btn-primary">
            {{ $submitLabel }}
        </button>
    </div>
</form>
