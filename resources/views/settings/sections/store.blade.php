<div class="space-y-6">
    @if (! $user->toko)
        <x-card as="section" class="p-4 sm:p-6">
            <p class="text-sm font-semibold text-[var(--primary-ink)]">Identitas toko belum lengkap</p>
            <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                Isi nama toko, nomor kontak, dan alamat agar dashboard, invoice, dan modul operasional memakai identitas yang konsisten.
            </p>
        </x-card>
    @endif

    <x-card as="section" class="p-4 sm:p-6">
        <div class="max-w-2xl">
            @include('settings.partials.store-identity-form')
        </div>
    </x-card>
</div>
