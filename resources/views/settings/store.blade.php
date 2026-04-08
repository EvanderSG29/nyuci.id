<x-app-layout title="Pengaturan Toko">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">Administrasi toko</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Pengaturan Toko</h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (! $user->toko)
                <x-card as="section" class="p-4 sm:p-6">
                    <p class="text-sm font-semibold text-[var(--primary-ink)]">Profil toko belum lengkap</p>
                    <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                        Isi nama toko, nomor kontak, dan alamat agar identitas laundry tampil konsisten di dashboard serta modul operasional.
                    </p>
                </x-card>
            @endif

            <x-card as="section" class="p-4 sm:p-6">
                <div class="max-w-2xl">
                    @include('settings.partials.update-store-information-form')
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
