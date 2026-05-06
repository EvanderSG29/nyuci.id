<div class="space-y-6">
    <x-card as="section" class="p-4 sm:p-6">
        <div class="max-w-3xl">
            <header>
                <h3 class="text-lg font-medium text-[var(--text-strong)]">Mode Tampilan</h3>
                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    Pilih mode terang, gelap, atau ikuti pengaturan sistem perangkat Anda. Perubahan diterapkan langsung tanpa perlu tombol simpan.
                </p>
            </header>

            <div class="mt-6 space-y-5">
                <x-theme-toggle />

                <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Preferensi ini bersifat personal</p>
                    <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                        Tema disimpan di browser yang sedang Anda pakai. Setelan ini tidak mengubah data toko dan tidak mempengaruhi pengguna lain.
                    </p>
                </div>
            </div>
        </div>
    </x-card>
</div>
