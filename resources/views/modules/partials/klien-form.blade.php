<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="nama_klien" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Nama Klien</label>
        <input
            type="text"
            id="nama_klien"
            name="nama_klien"
            value="{{ old('nama_klien', $klien?->nama_klien) }}"
            placeholder="Masukkan nama pelanggan"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('nama_klien') border-[var(--danger)] @enderror"
        >
        @error('nama_klien')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="email_klien" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Email</label>
        <input
            type="email"
            id="email_klien"
            name="email_klien"
            value="{{ old('email_klien', $klien?->email_klien) }}"
            placeholder="pelanggan@email.com"
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('email_klien') border-[var(--danger)] @enderror"
        >
        <p class="mt-2 text-xs text-[var(--text-muted)]">Opsional, tetapi dibutuhkan jika ingin mengirim notifikasi email otomatis.</p>
        @error('email_klien')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="alamat_klien" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Alamat</label>
        <textarea
            id="alamat_klien"
            name="alamat_klien"
            rows="4"
            placeholder="Masukkan alamat pelanggan"
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('alamat_klien') border-[var(--danger)] @enderror"
        >{{ old('alamat_klien', $klien?->alamat_klien) }}</textarea>
        @error('alamat_klien')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="no_hp_klien" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">No HP</label>
        <input
            type="text"
            id="no_hp_klien"
            name="no_hp_klien"
            value="{{ old('no_hp_klien', $klien?->no_hp_klien) }}"
            placeholder="08xxxxxxxxxx"
            required
            class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] @error('no_hp_klien') border-[var(--danger)] @enderror"
        >
        @error('no_hp_klien')
            <p class="mt-2 text-sm text-[var(--danger)]">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-[var(--border-main)] pt-4 sm:flex-row sm:justify-end">
    <a href="{{ route('pelanggan.index') }}" class="nyuci-btn-secondary">
        Batal
    </a>

    <button type="submit" class="nyuci-btn-primary">
        {{ $submitLabel }}
    </button>
</div>
