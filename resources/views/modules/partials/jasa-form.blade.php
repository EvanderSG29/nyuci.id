<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="nama_jasa" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Nama Jasa</label>
        <input
            type="text"
            id="nama_jasa"
            name="nama_jasa"
            value="{{ old('nama_jasa', $jasa?->nama_jasa) }}"
            placeholder="Contoh: Cuci Lipat"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('nama_jasa') border-[var(--danger)] @enderror"
        >
        @error('nama_jasa')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="satuan" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Satuan</label>
        <input
            type="text"
            id="satuan"
            name="satuan"
            value="{{ old('satuan', $jasa?->satuan) }}"
            placeholder="Contoh: kg atau pcs"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('satuan') border-[var(--danger)] @enderror"
        >
        @error('satuan')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="harga" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Harga per Satuan</label>
        <input
            type="number"
            id="harga"
            name="harga"
            min="0"
            value="{{ old('harga', $jasa?->harga) }}"
            placeholder="Contoh: 5000"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('harga') border-[var(--danger)] @enderror"
        >
        @error('harga')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-[var(--border-main)] pt-4 sm:flex-row sm:justify-end">
    <a href="{{ route('biaya-jasa.index') }}" class="nyuci-btn-secondary">
        Batal
    </a>

    <button type="submit" class="nyuci-btn-primary">
        {{ $submitLabel }}
    </button>
</div>
