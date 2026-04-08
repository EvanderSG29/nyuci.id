@php
    $selectedKlienId = (string) old('klien_id', $laundry?->klien_id);
    $selectedJasaId = (string) old('jasa_id', $laundry?->jasa_id);
    $selectedStatus = old('status', $laundry?->status ?? 'belum_selesai');
@endphp

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label for="klien_id" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Pelanggan</label>
        <select
            id="klien_id"
            name="klien_id"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('klien_id') border-[var(--danger)] @enderror"
        >
            <option value="">Pilih pelanggan</option>
            @foreach ($kliens as $klienOption)
                <option value="{{ $klienOption->id }}" @selected($selectedKlienId === (string) $klienOption->id)>
                    {{ $klienOption->nama_klien }} ({{ $klienOption->no_hp_klien }})
                </option>
            @endforeach
        </select>
        @error('klien_id')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jasa_id" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Biaya Jasa</label>
        <select
            id="jasa_id"
            name="jasa_id"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('jasa_id') border-[var(--danger)] @enderror"
        >
            <option value="">Pilih jasa</option>
            @foreach ($jasas as $jasaOption)
                <option value="{{ $jasaOption->id }}" @selected($selectedJasaId === (string) $jasaOption->id)>
                    {{ $jasaOption->nama_jasa }} / {{ $jasaOption->satuan }} - Rp {{ number_format((int) $jasaOption->harga, 0, ',', '.') }}
                </option>
            @endforeach
        </select>
        @error('jasa_id')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="qty" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Qty</label>
        <input
            type="number"
            id="qty"
            name="qty"
            min="0.1"
            step="0.01"
            value="{{ old('qty', $laundry?->qty) }}"
            placeholder="Contoh: 3"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('qty') border-[var(--danger)] @enderror"
        >
        @error('qty')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Status Laundry</label>
        <select
            id="status"
            name="status"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('status') border-[var(--danger)] @enderror"
        >
            <option value="belum_selesai" @selected($selectedStatus === 'belum_selesai')>Belum Selesai</option>
            <option value="proses" @selected($selectedStatus === 'proses')>Proses</option>
            <option value="selesai" @selected($selectedStatus === 'selesai')>Selesai</option>
        </select>
        @error('status')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_dimulai" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Tanggal Dimulai</label>
        <input
            type="date"
            id="tanggal_dimulai"
            name="tanggal_dimulai"
            value="{{ old('tanggal_dimulai', $laundry?->tanggal_dimulai?->format('Y-m-d') ?? $laundry?->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('tanggal_dimulai') border-[var(--danger)] @enderror"
        >
        @error('tanggal_dimulai')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="ets_selesai" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Estimasi Selesai</label>
        <input
            type="date"
            id="ets_selesai"
            name="ets_selesai"
            value="{{ old('ets_selesai', $laundry?->ets_selesai?->format('Y-m-d') ?? $laundry?->estimasi_selesai?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('ets_selesai') border-[var(--danger)] @enderror"
        >
        @error('ets_selesai')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-[var(--border-main)] pt-4 sm:flex-row sm:justify-end">
    <a href="{{ route('laundry.index') }}" class="nyuci-btn-secondary">
        Batal
    </a>

    <button type="submit" class="nyuci-btn-primary">
        {{ $submitLabel }}
    </button>
</div>
