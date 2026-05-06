<div class="space-y-6">
    @if (! $store)
        <x-card class="p-6">
            <p class="text-sm font-semibold text-[var(--primary-ink)]">Lengkapi toko terlebih dahulu</p>
            <h3 class="mt-2 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                QRIS butuh profil toko yang aktif
            </h3>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--text-muted)]">
                Buat identitas toko lebih dulu agar konfigurasi QRIS bisa disimpan dengan konteks toko yang benar.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('settings.toko') }}" wire:navigate class="nyuci-btn-primary">
                    Buka pengaturan toko
                </a>
            </div>
        </x-card>
    @else
        <x-card as="section" class="p-4 sm:p-6">
            <div class="max-w-3xl">
                @include('settings.partials.payment-qris-form')
            </div>
        </x-card>
    @endif
</div>
