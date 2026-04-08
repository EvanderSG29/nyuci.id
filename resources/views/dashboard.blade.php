<x-app-layout title="Beranda">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">Dashboard operasional</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                {{ $toko?->nama_toko ?? 'Lengkapi profil toko Anda' }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            @if (! $toko)
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
            @else
                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <div class="nyuci-dashboard-hero rounded-[2rem] px-6 py-7 sm:px-8">
                        <p class="text-sm font-medium text-[var(--hero-eyebrow)]">Ringkasan toko</p>
                        <h3 class="mt-3 text-3xl font-semibold tracking-tight">{{ $toko->nama_toko }}</h3>
                        <p class="mt-4 max-w-2xl text-sm leading-6 text-[var(--hero-copy)]">
                            Semua data laundry dan pembayaran toko Anda diringkas di sini agar operasional harian lebih jelas dan cepat dipantau.
                        </p>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="nyuci-dashboard-hero-panel rounded-2xl p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-[var(--hero-panel-label)]">Pemilik</p>
                                <p class="mt-2 text-base font-medium text-[var(--hero-panel-value)]">{{ auth()->user()->name }}</p>
                            </div>
                            <div class="nyuci-dashboard-hero-panel rounded-2xl p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-[var(--hero-panel-label)]">Kontak</p>
                                <p class="mt-2 text-base font-medium text-[var(--hero-panel-value)]">{{ $toko->no_hp ?: '-' }}</p>
                            </div>
                        </div>

                        @if ($toko->alamat)
                            <div class="nyuci-dashboard-hero-panel mt-4 rounded-2xl p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-[var(--hero-panel-label)]">Alamat</p>
                                <p class="mt-2 text-sm leading-6 text-[var(--hero-panel-value)]">{{ $toko->alamat }}</p>
                            </div>
                        @endif
                    </div>

                    <x-card class="rounded-[2rem] p-6">
                        <p class="text-sm font-semibold text-[var(--text-muted)]">Aksi cepat</p>
                        <div class="mt-5 grid gap-3">
                            <a href="{{ route('laundry.create') }}" class="inline-flex items-center justify-between rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-4 text-sm font-medium text-[var(--text-main)] transition hover:border-[var(--primary)] hover:text-[var(--text-strong)]">
                                <span>Tambah laundry baru</span>
                                <span class="text-[var(--primary-ink)]">+</span>
                            </a>
                            <a href="{{ route('pelanggan.create') }}" class="inline-flex items-center justify-between rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-4 text-sm font-medium text-[var(--text-main)] transition hover:border-[var(--primary)] hover:text-[var(--text-strong)]">
                                <span>Tambah pelanggan</span>
                                <span class="text-[var(--primary-ink)]">+</span>
                            </a>
                            <a href="{{ route('laundry.index') }}" class="inline-flex items-center justify-between rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-4 text-sm font-medium text-[var(--text-main)] transition hover:border-[var(--primary)] hover:text-[var(--text-strong)]">
                                <span>Lihat daftar laundry</span>
                                <span class="text-[var(--primary-ink)]">&gt;</span>
                            </a>
                            <a href="{{ route('pembayaran.index') }}" class="inline-flex items-center justify-between rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-4 text-sm font-medium text-[var(--text-main)] transition hover:border-[var(--primary)] hover:text-[var(--text-strong)]">
                                <span>Kelola pembayaran</span>
                                <span class="text-[var(--primary-ink)]">&gt;</span>
                            </a>
                            <a href="{{ route('pengaturan-toko.edit') }}" class="inline-flex items-center justify-between rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-4 text-sm font-medium text-[var(--text-main)] transition hover:border-[var(--primary)] hover:text-[var(--text-strong)]">
                                <span>Perbarui profil toko</span>
                                <span class="text-[var(--primary-ink)]">&gt;</span>
                            </a>
                        </div>
                    </x-card>
                </section>

                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <x-card class="rounded-3xl p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--text-muted)]">Total laundry</p>
                        <p class="mt-3 text-4xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $stats['totalLaundry'] }}</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Semua order yang sudah tercatat di toko ini.</p>
                    </x-card>
                    <div class="nyuci-dashboard-metric rounded-3xl p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--metric-eyebrow)]">Belum selesai</p>
                        <p class="mt-3 text-4xl font-semibold tracking-tight text-[var(--metric-number)]">{{ $stats['pendingLaundry'] }}</p>
                        <p class="mt-2 text-sm text-[var(--metric-copy)]">Order yang masih berada di tahap awal atau sedang proses.</p>
                    </div>
                    <div class="nyuci-dashboard-metric nyuci-dashboard-metric-navy rounded-3xl p-6 shadow-sm sm:col-span-2 xl:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--metric-eyebrow)]">Total pelanggan</p>
                        <p class="mt-3 text-4xl font-semibold tracking-tight text-[var(--metric-number)]">{{ $stats['totalPelanggan'] }}</p>
                        <p class="mt-2 text-sm text-[var(--metric-copy)]">Jumlah pelanggan yang sudah tersimpan di master data.</p>
                    </div>
                </section>

                <x-card as="section" class="rounded-[2rem] p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-muted)]">Laundry terbaru</p>
                            <h3 class="text-xl font-semibold tracking-tight text-[var(--text-strong)]">Aktivitas terakhir toko Anda</h3>
                        </div>
                        <a href="{{ route('laundry.index') }}" class="text-sm font-medium text-[var(--primary-ink)] transition hover:text-[var(--text-strong)]">
                            Lihat semua data
                        </a>
                    </div>

                    @if ($recentLaundries->isEmpty())
                        <div class="mt-6 rounded-3xl border border-dashed border-[var(--border-soft)] bg-[var(--bg-surface)] px-6 py-10 text-center">
                            <p class="text-base font-medium text-[var(--text-main)]">Belum ada data laundry.</p>
                            <p class="mt-2 text-sm text-[var(--text-muted)]">Mulai dari order pertama agar dashboard ini langsung terisi.</p>
                            <div class="mt-5">
                                <a href="{{ route('laundry.create') }}" class="nyuci-btn-primary">
                                    Tambah laundry pertama
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 space-y-3 lg:hidden">
                            @foreach ($recentLaundries as $laundry)
                                <x-card class="rounded-3xl bg-[var(--bg-surface)]">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-[var(--text-strong)]">{{ $laundry->nama }}</p>
                                            <p class="mt-1 text-sm text-[var(--text-muted)]">{{ $laundry->jenis_jasa_label }} - {{ $laundry->satuan_label }}</p>
                                        </div>
                                        <x-status-badge :variant="$laundry->status === 'selesai' ? 'success' : ($laundry->status === 'proses' ? 'paid' : 'pending')">
                                            {{ $laundry->status_label }}
                                        </x-status-badge>
                                    </div>
                                    <p class="mt-3 text-sm text-[var(--text-muted)]">{{ $laundry->created_at->format('d M Y') }}</p>
                                </x-card>
                            @endforeach
                        </div>

                        <div class="mt-6 hidden overflow-x-auto lg:block">
                            <table class="min-w-full divide-y divide-[var(--border-main)] text-left text-sm text-[var(--text-main)]">
                                <thead>
                                    <tr class="text-xs uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                        <th class="px-4 py-3 font-semibold">Customer</th>
                                        <th class="px-4 py-3 font-semibold">Layanan</th>
                                        <th class="px-4 py-3 font-semibold">Berat</th>
                                        <th class="px-4 py-3 font-semibold">Status</th>
                                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#1e293b]/70">
                                    @foreach ($recentLaundries as $laundry)
                                        <tr class="transition hover:bg-[var(--bg-surface)]">
                                            <td class="px-4 py-4 font-medium text-[var(--text-strong)]">{{ $laundry->nama }}</td>
                                            <td class="px-4 py-4">{{ $laundry->jenis_jasa_label }}</td>
                                            <td class="px-4 py-4">{{ $laundry->satuan_label }}</td>
                                            <td class="px-4 py-4">
                                                <x-status-badge :variant="$laundry->status === 'selesai' ? 'success' : ($laundry->status === 'proses' ? 'paid' : 'pending')">
                                                    {{ $laundry->status_label }}
                                                </x-status-badge>
                                            </td>
                                            <td class="px-4 py-4">{{ $laundry->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-card>
            @endif
        </div>
    </div>
</x-app-layout>
