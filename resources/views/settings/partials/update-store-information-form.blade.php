<section>
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

            @if (session('status') === 'store-settings-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-[var(--text-muted)]"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
