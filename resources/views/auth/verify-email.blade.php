<x-guest-layout title="Verifikasi Email">
    <div class="mb-4 text-sm text-slate-600">
        {{ __('Terima kasih telah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email dengan mengklik tautan yang baru saja kami kirimkan? Jika Anda tidak menerima email, kami akan dengan senang hati mengirimkan yang lain.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-[#1d4ed8]">
            {{ __('Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Kirim Ulang Email Verifikasi') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="rounded-md text-sm text-slate-600 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2">
                {{ __('Keluar') }}
            </button>
        </form>
    </div>
</x-guest-layout>
