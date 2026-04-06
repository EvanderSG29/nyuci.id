<x-app-layout title="Tambah Laundry">
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-[var(--text-strong)]">Tambah Data Laundry</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] shadow-sm">
                <div class="p-6 text-[var(--text-main)]">
                    <form method="POST" action="{{ route('laundry.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label for="nama" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Nama Pelanggan</label>
                            <input type="text" id="nama" name="nama" placeholder="Masukkan nama" value="{{ old('nama') }}" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/45 @error('nama') border-red-500 @enderror">
                            @error('nama') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="no_hp" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">No HP</label>
                            <input type="tel" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/45 @error('no_hp') border-red-500 @enderror">
                            @error('no_hp') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="berat" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Berat (kg)</label>
                                <input type="number" step="0.1" id="berat" name="berat" placeholder="Dalam kilogram" value="{{ old('berat') }}" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/45 @error('berat') border-red-500 @enderror">
                                @error('berat') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="layanan" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Jenis Layanan</label>
                                <select id="layanan" name="layanan" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/45 @error('layanan') border-red-500 @enderror">
                                    <option value="">-- Pilih Layanan --</option>
                                    <option value="cuci" {{ old('layanan') === 'cuci' ? 'selected' : '' }}>Cuci</option>
                                    <option value="setrika" {{ old('layanan') === 'setrika' ? 'selected' : '' }}>Setrika</option>
                                    <option value="keduanya" {{ old('layanan') === 'keduanya' ? 'selected' : '' }}>Keduanya</option>
                                </select>
                                @error('layanan') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="tanggal" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Tanggal Masuk</label>
                                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal') }}" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/45 @error('tanggal') border-red-500 @enderror">
                                @error('tanggal') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="estimasi_selesai" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Estimasi Selesai</label>
                                <input type="date" id="estimasi_selesai" name="estimasi_selesai" value="{{ old('estimasi_selesai') }}" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/45 @error('estimasi_selesai') border-red-500 @enderror">
                                @error('estimasi_selesai') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-3 border-t border-[var(--border-main)] pt-4">
                            <a href="{{ route('laundry.index') }}" class="inline-flex items-center rounded-full border border-[var(--border-soft)] bg-[var(--bg-surface)] px-6 py-2 text-sm font-semibold text-[var(--text-main)] transition hover:border-[#3b82f6]">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center rounded-full bg-[var(--primary)] px-6 py-2 text-sm font-semibold text-white transition hover:bg-[var(--primary-hover)]">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
