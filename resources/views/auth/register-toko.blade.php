<x-guest-layout title="Informasi Toko">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Lengkapi Informasi Toko</h1>
        <p class="mt-2 text-sm text-slate-600">
            Akun berhasil dibuat. Satu langkah lagi, isi data toko agar dashboard siap dipakai.
        </p>
    </div>

    <form method="POST" action="{{ route('register.toko.store') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="nama_toko" :value="__('Nama Toko')" />
            <x-text-input id="nama_toko" class="mt-1 block w-full" type="text" name="nama_toko" :value="old('nama_toko')" required autofocus autocomplete="organization" />
            <x-input-error :messages="$errors->get('nama_toko')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="no_hp" :value="__('No. HP Toko')" />
            <x-text-input id="no_hp" class="mt-1 block w-full" type="text" name="no_hp" :value="old('no_hp')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="alamat" :value="__('Alamat Toko')" />
            <textarea
                id="alamat"
                name="alamat"
                rows="4"
                class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
            >{{ old('alamat') }}</textarea>
            <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center">
                {{ __('Simpan & Lanjut ke Dashboard') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
