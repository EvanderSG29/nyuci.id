<section>
    <header>
        <h2 class="text-lg font-medium text-[var(--text-strong)]">
            {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-[var(--text-muted)]">
            {{ __("Perbarui informasi profil dan alamat email akun Anda.") }}
        </p>
    </header>

    @if (Route::has('verification.send'))
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>
    @endif

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (Route::has('verification.send') && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-[var(--text-main)]">
                        {{ __('Alamat email Anda belum diverifikasi.') }}

                        <button form="send-verification" class="rounded-md text-sm text-[var(--primary-soft)] underline hover:text-white focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-[var(--primary-soft)]">
                            {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

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
                class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
            >{{ old('alamat', $user->toko?->alamat) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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
