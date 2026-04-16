<x-guest-layout title="Verifikasi 2 Langkah">
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Autentikasi Dua Faktor</h2>
            <p class="mt-2 text-sm text-[var(--text-muted)]">
                @if (session('auth.uses_recovery_code'))
                    Silakan masukkan salah satu kode pemulihan Anda.
                @else
                    Silakan konfirmasi akses ke akun Anda dengan memasukkan kode autentikasi dari aplikasi autentikator Anda.
                @endif
            </p>
        </div>

        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="code" class="sr-only">Kode</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    inputmode="numeric"
                    required
                    autofocus
                    class="block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-3 py-2 text-[var(--text-main)] shadow-sm placeholder:text-[var(--text-muted)] focus:border-[var(--primary)] focus:outline-none focus:ring-[var(--primary)]"
                    placeholder="@if (session('auth.uses_recovery_code')) Kode Pemulihan @else Kode Autentikasi @endif"
                >
                @error('code')
                    <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-full bg-[var(--primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--primary-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2"
            >
                Verifikasi
            </button>

            <div class="text-center">
                @if (! session('auth.uses_recovery_code'))
                    <button type="submit" formaction="{{ route('two-factor.login.recovery') }}" class="text-sm font-medium text-[var(--primary-hover)] hover:text-[var(--primary)]">
                        Menggunakan kode pemulihan?
                    </button>
                @else
                    <button type="submit" formaction="{{ route('two-factor.login') }}" class="text-sm font-medium text-[var(--primary-hover)] hover:text-[var(--primary)]">
                        Menggunakan kode autentikator?
                    </button>
                @endif
            </div>
        </form>
    </div>
</x-guest-layout>
