<section>
    <header>
        <h2 class="text-lg font-medium text-[var(--text-strong)]">
            {{ __('Informasi Akun') }}
        </h2>

        <p class="mt-1 text-sm text-[var(--text-muted)]">
            {{ __("Perbarui nama dan alamat email yang dipakai untuk masuk ke Nyuci.id.") }}
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
            <x-input-label for="name" :value="__('Nama Lengkap')" />
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

                        <button form="send-verification" class="rounded-md text-sm text-[var(--primary-ink)] underline hover:text-[var(--text-strong)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-[var(--primary-ink)]">
                            {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
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
