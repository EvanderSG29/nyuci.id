<section>
    <header>
        <h2 class="text-lg font-medium text-[var(--text-strong)]">
            Identitas Toko
        </h2>

        <p class="mt-1 text-sm text-[var(--text-muted)]">
            Informasi ini dipakai sebagai identitas utama toko di dashboard, invoice, dan alur operasional.
        </p>
    </header>

    <form id="settings-store-form" method="post" action="{{ route('settings.toko.update') }}" class="mt-6 space-y-6" data-settings-form>
        @csrf
        @method('patch')

        <div>
            <x-input-label for="settings_nama_toko" :value="__('Nama Toko')" />
            <x-text-input
                id="settings_nama_toko"
                name="nama_toko"
                type="text"
                class="mt-1 block w-full"
                :value="old('nama_toko', $user->toko?->nama_toko)"
                required
                autocomplete="organization"
            />
            <x-input-error class="mt-2" :messages="$errors->get('nama_toko')" />
        </div>

        <div>
            <x-input-label for="settings_no_hp" :value="__('No. HP Toko')" />
            <x-text-input
                id="settings_no_hp"
                name="no_hp"
                type="text"
                class="mt-1 block w-full"
                :value="old('no_hp', $user->toko?->no_hp)"
                autocomplete="tel"
            />
            <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
        </div>

        <div>
            <x-input-label for="settings_alamat" :value="__('Alamat Toko')" />
            <textarea
                id="settings_alamat"
                name="alamat"
                rows="4"
                class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
            >{{ old('alamat', $user->toko?->alamat) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan Informasi') }}</x-primary-button>
        </div>
    </form>
</section>
