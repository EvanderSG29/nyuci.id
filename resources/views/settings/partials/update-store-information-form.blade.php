<section>
    <header>
        <h2 class="text-lg font-medium text-[var(--text-strong)]">
            Informasi Toko
        </h2>

        <p class="mt-1 text-sm text-[var(--text-muted)]">
            Atur identitas toko yang akan dipakai di dashboard dan modul operasional Nyuci.id.
        </p>
    </header>

    <form method="post" action="{{ route('pengaturan-toko.update') }}" class="mt-6 space-y-6" x-data="storeBackgroundSettings('{{ old('background_mode', $user->toko?->background_mode ?? 'system') }}', '{{ old('background_color', $user->toko?->background_color ?? '#020617') }}')">
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

            <x-theme-toggle />

            <div>
                <h3 class="text-sm font-semibold text-[var(--text-strong)]">Sumber Warna Latar</h3>
                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    Atur apakah latar belakang aplikasi mengikuti palet bawaan atau memakai warna toko sendiri.
                </p>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <label class="nyuci-theme-option" :class="{ 'is-active': mode === 'system' }">
                    <input type="radio" name="background_mode" value="system" class="sr-only" x-model="mode">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-strong)]">Bawaan Sistem</p>
                            <p class="mt-1 text-sm text-[var(--text-muted)]">Latar mengikuti tema Light atau Dark yang aktif.</p>
                        </div>
                        <span class="rounded-full bg-[var(--bg-elevated)] px-3 py-1 text-xs font-semibold text-[var(--primary-ink)]">60%</span>
                    </div>
                </label>

                <label class="nyuci-theme-option" :class="{ 'is-active': mode === 'custom' }">
                    <input type="radio" name="background_mode" value="custom" class="sr-only" x-model="mode">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-strong)]">Kustom</p>
                            <p class="mt-1 text-sm text-[var(--text-muted)]">Tentukan warna latar kanvas toko Anda sendiri.</p>
                        </div>
                        <span class="rounded-full bg-[var(--primary-soft)] px-3 py-1 text-xs font-semibold text-[var(--primary-ink)]">10%</span>
                    </div>
                </label>
            </div>

            <x-input-error class="mt-1" :messages="$errors->get('background_mode')" />

            <div>
                <h3 class="text-sm font-semibold text-[var(--text-strong)]">Warna Latar Belakang</h3>
                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    Gunakan format hex agar warna latar kustom tetap konsisten dengan komposisi 60:30:10.
                </p>
            </div>

            <div x-show="isCustom()" x-cloak class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <span
                            class="nyuci-color-preview h-12 w-12 shrink-0"
                            :style="`--preview-color: ${color || '#020617'}`"
                        ></span>
                        <div>
                            <x-input-label for="background_color" :value="__('Warna Latar Kustom')" />
                            <p class="mt-1 text-sm text-[var(--text-muted)]">Gunakan format hex agar hasilnya konsisten.</p>
                        </div>
                    </div>

                    <div class="flex w-full flex-col gap-3 sm:ml-auto sm:max-w-xs">
                        <input
                            id="background_color"
                            name="background_color"
                            type="color"
                            x-model="color"
                            class="h-12 w-full cursor-pointer rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] p-2"
                        >
                        <input
                            type="text"
                            x-model="color"
                            class="rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm"
                            disabled
                        >
                    </div>
                </div>

                <x-input-error class="mt-2" :messages="$errors->get('background_color')" />
            </div>
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
