<x-app-layout title="Edit Pembayaran">
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-[var(--text-strong)]">Edit Pembayaran</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] shadow-sm">
                <div class="p-6 text-[var(--text-main)]">
                    <form method="POST" action="{{ route('pembayaran.update', $pembayaran->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="laundry_id" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Pelanggan</label>
                            <input type="text" readonly value="{{ $pembayaran->laundry->nama }} ({{ $pembayaran->laundry->no_hp }})" class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-[var(--text-main)]">
                            <input type="hidden" name="laundry_id" value="{{ $pembayaran->laundry_id }}">
                        </div>

                        <div>
                            <label for="total" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Total Pembayaran (Rp)</label>
                            <input type="number" id="total" name="total" placeholder="Masukkan jumlah pembayaran" value="{{ old('total', $pembayaran->total) }}" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/45 @error('total') border-red-500 @enderror">
                            @error('total') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="status" class="mb-2 block text-sm font-semibold text-[var(--text-main)]">Status Pembayaran</label>
                            <select id="status" name="status" required class="w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/45 @error('status') border-red-500 @enderror">
                                <option value="belum_bayar" {{ old('status', $pembayaran->status) === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                                <option value="sudah_bayar" {{ old('status', $pembayaran->status) === 'sudah_bayar' ? 'selected' : '' }}>Sudah Bayar</option>
                            </select>
                            @error('status') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-8 flex justify-end gap-3 border-t border-[var(--border-main)] pt-4">
                            <a href="{{ route('pembayaran.index') }}" class="inline-flex items-center rounded-full border border-[var(--border-soft)] bg-[var(--bg-surface)] px-6 py-2 text-sm font-semibold text-[var(--text-main)] transition hover:border-[#3b82f6]">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center rounded-full bg-[var(--primary)] px-6 py-2 text-sm font-semibold text-white transition hover:bg-[var(--primary-hover)]">
                                Perbarui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
