<x-card as="section" class="overflow-hidden p-6">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[var(--primary-ink)]">Profil belum lengkap</p>
    <h3 class="mt-3 text-2xl font-semibold text-[var(--text-strong)]">Buat data toko dulu supaya dashboard bisa dipakai penuh.</h3>
    <p class="mt-3 max-w-2xl text-sm leading-6 text-[var(--text-muted)]">
        Saat ini akun Anda sudah login, tetapi informasi toko belum tersedia. Lengkapi nama toko, alamat, dan nomor
        kontak agar data laundry dan pembayaran bisa dipisahkan per toko dengan aman.
    </p>
    <div class="mt-6">
        <a href="{{ route('register.toko.create') }}" class="nyuci-btn-primary">
            Lengkapi profil toko
        </a>
    </div>
</x-card>
