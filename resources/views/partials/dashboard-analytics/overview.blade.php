<section class="grid gap-6 xl:grid-cols-[1.55fr_0.95fr]">
    <div class="nyuci-analytics-hero rounded-[2rem] px-6 py-7 sm:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--hero-eyebrow)]">Analytics Dashboard</p>
                <h3 class="mt-4 text-3xl font-semibold tracking-tight text-[var(--text-strong)] sm:text-4xl">
                    {{ $toko->nama_toko }}
                </h3>
                <p class="mt-4 max-w-2xl text-sm leading-6 text-[var(--hero-copy)]">
                    {{ $overview['headline'] }}
                </p>
            </div>

            <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-[21rem]">
                @foreach ($overview['miniStats'] as $item)
                    <div class="nyuci-mini-stat rounded-3xl p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                            {{ $item['label'] }}
                        </p>
                        <p class="mt-3 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($highlights as $card)
                <div class="nyuci-kpi-card rounded-3xl p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                        {{ $card['label'] }}
                    </p>
                    <p class="mt-4 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">
                        {{ $card['value'] }}
                    </p>
                    <p class="mt-3 text-sm leading-6 text-[var(--text-muted)]">
                        {{ $card['caption'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    <x-card class="nyuci-analytics-side rounded-[2rem] p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-[var(--text-muted)]">Aksi cepat</p>
                <h3 class="mt-1 text-xl font-semibold tracking-tight text-[var(--text-strong)]">
                    Jalankan operasional tanpa pindah-pindah modul
                </h3>
            </div>

            <span class="nyuci-kpi-pill">
                Live
            </span>
        </div>

        <div class="mt-5 grid gap-3">
            <a href="{{ route('laundry.create') }}" wire:navigate class="nyuci-quick-action rounded-2xl px-4 py-4">
                <span>Tambah laundry baru</span>
                <span>+</span>
            </a>
            <a href="{{ route('pelanggan.create') }}" wire:navigate class="nyuci-quick-action rounded-2xl px-4 py-4">
                <span>Tambah pelanggan</span>
                <span>+</span>
            </a>
            <a href="{{ route('laundry.index') }}" wire:navigate class="nyuci-quick-action rounded-2xl px-4 py-4">
                <span>Lihat daftar laundry</span>
                <span>&gt;</span>
            </a>
            <a href="{{ route('pembayaran.index') }}" wire:navigate class="nyuci-quick-action rounded-2xl px-4 py-4">
                <span>Kelola pembayaran</span>
                <span>&gt;</span>
            </a>
            <a href="{{ route('pengaturan-toko.edit') }}" wire:navigate class="nyuci-quick-action rounded-2xl px-4 py-4">
                <span>Perbarui profil toko</span>
                <span>&gt;</span>
            </a>
        </div>

        <div class="nyuci-side-note mt-5 rounded-3xl p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Kondisi kas</p>
            <p class="mt-3 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                {{ $overview['unpaidValue'] }}
            </p>
            <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                {{ $overview['attentionLine'] }}
            </p>

            <div class="mt-4 flex items-center justify-between text-sm text-[var(--text-muted)]">
                <span>Pembayaran lunas</span>
                <span class="font-semibold text-[var(--text-strong)]">
                    {{ number_format($overview['paidCount'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </x-card>
</section>
