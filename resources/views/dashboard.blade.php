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
                <section class="overflow-hidden rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[var(--primary-soft)]">Profil belum lengkap</p>
                    <h3 class="mt-3 text-2xl font-semibold text-[var(--text-strong)]">Buat data toko dulu supaya dashboard bisa dipakai penuh.</h3>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-[var(--text-muted)]">
                        Saat ini akun Anda sudah login, tetapi informasi toko belum tersedia. Lengkapi nama toko, alamat, dan nomor
                        kontak agar data laundry dan pembayaran bisa dipisahkan per toko dengan aman.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('register.toko.create') }}" class="inline-flex items-center justify-center rounded-full bg-[var(--primary)] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[var(--primary-hover)]">
                            Lengkapi profil toko
                        </a>
                    </div>
                </section>
            @else
                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <div class="rounded-[2rem] border border-[#1e3a8a]/50 bg-gradient-to-br from-[#0f172a] to-[#1e3a8a] px-6 py-7 text-white shadow-xl shadow-[#020617]/50 sm:px-8">
                        <p class="text-sm font-medium text-[#bfdbfe]">Ringkasan toko</p>
                        <h3 class="mt-3 text-3xl font-semibold tracking-tight">{{ $toko->nama_toko }}</h3>
                        <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-200">
                            Semua data laundry dan pembayaran toko Anda diringkas di sini agar operasional harian lebih jelas dan cepat dipantau.
                        </p>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Pemilik</p>
                                <p class="mt-2 text-base font-medium text-white">{{ auth()->user()->name }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Kontak</p>
                                <p class="mt-2 text-base font-medium text-white">{{ $toko->no_hp ?: '-' }}</p>
                            </div>
                        </div>

                        @if ($toko->alamat)
                            <div class="mt-4 rounded-2xl border border-white/15 bg-white/10 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Alamat</p>
                                <p class="mt-2 text-sm leading-6 text-slate-100">{{ $toko->alamat }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-[2rem] border border-[var(--border-main)] bg-[var(--bg-card)] p-6 shadow-sm">
                        <p class="text-sm font-semibold text-[var(--text-muted)]">Aksi cepat</p>
                        <div class="mt-5 grid gap-3">
                            <a href="{{ route('laundry.create') }}" class="inline-flex items-center justify-between rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-4 text-sm font-medium text-[var(--text-main)] transition hover:border-[#3b82f6] hover:text-[var(--text-strong)]">
                                <span>Tambah laundry baru</span>
                                <span class="text-[var(--primary-soft)]">+</span>
                            </a>
                            <a href="{{ route('laundry.index') }}" class="inline-flex items-center justify-between rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-4 text-sm font-medium text-[var(--text-main)] transition hover:border-[#3b82f6] hover:text-[var(--text-strong)]">
                                <span>Lihat daftar laundry</span>
                                <span class="text-[var(--primary-soft)]">&gt;</span>
                            </a>
                            <a href="{{ route('pembayaran.index') }}" class="inline-flex items-center justify-between rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-4 text-sm font-medium text-[var(--text-main)] transition hover:border-[#3b82f6] hover:text-[var(--text-strong)]">
                                <span>Kelola pembayaran</span>
                                <span class="text-[var(--primary-soft)]">&gt;</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-between rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-4 text-sm font-medium text-[var(--text-main)] transition hover:border-[#3b82f6] hover:text-[var(--text-strong)]">
                                <span>Perbarui profil toko</span>
                                <span class="text-[var(--primary-soft)]">&gt;</span>
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--text-muted)]">Total laundry</p>
                        <p class="mt-3 text-4xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $stats['totalLaundry'] }}</p>
                        <p class="mt-2 text-sm text-[var(--text-muted)]">Semua order yang sudah tercatat di toko ini.</p>
                    </div>
                    <div class="rounded-3xl border border-[#3b82f6]/35 bg-[#0f1a2e] p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--primary-soft)]">Belum diambil</p>
                        <p class="mt-3 text-4xl font-semibold tracking-tight text-[#dbeafe]">{{ $stats['pendingPickup'] }}</p>
                        <p class="mt-2 text-sm text-[#bfdbfe]">Laundry yang masih menunggu customer datang.</p>
                    </div>
                    <div class="rounded-3xl border border-[#60a5fa]/45 bg-[#13203a] p-6 shadow-sm sm:col-span-2 xl:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#bfdbfe]">Sudah bayar</p>
                        <p class="mt-3 text-4xl font-semibold tracking-tight text-[#eff6ff]">{{ $stats['paidCount'] }}</p>
                        <p class="mt-2 text-sm text-[#bfdbfe]">Transaksi pembayaran yang sudah selesai.</p>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-[var(--border-main)] bg-[var(--bg-card)] p-6 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-muted)]">Laundry terbaru</p>
                            <h3 class="text-xl font-semibold tracking-tight text-[var(--text-strong)]">Aktivitas terakhir toko Anda</h3>
                        </div>
                        <a href="{{ route('laundry.index') }}" class="text-sm font-medium text-[var(--primary-soft)] transition hover:text-white">
                            Lihat semua data
                        </a>
                    </div>

                    @if ($recentLaundries->isEmpty())
                        <div class="mt-6 rounded-3xl border border-dashed border-[var(--border-soft)] bg-[var(--bg-surface)] px-6 py-10 text-center">
                            <p class="text-base font-medium text-[var(--text-main)]">Belum ada data laundry.</p>
                            <p class="mt-2 text-sm text-[var(--text-muted)]">Mulai dari order pertama agar dashboard ini langsung terisi.</p>
                            <div class="mt-5">
                                <a href="{{ route('laundry.create') }}" class="inline-flex items-center justify-center rounded-full bg-[var(--primary)] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[var(--primary-hover)]">
                                    Tambah laundry pertama
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 space-y-3 lg:hidden">
                            @foreach ($recentLaundries as $laundry)
                                <div class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-[var(--text-strong)]">{{ $laundry->nama }}</p>
                                            <p class="mt-1 text-sm text-[var(--text-muted)]">{{ ucfirst(str_replace('_', ' ', $laundry->layanan)) }} - {{ $laundry->berat }} kg</p>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $laundry->is_taken ? 'bg-[#1e3a8a] text-[#dbeafe]' : 'bg-[#0f1a2e] text-[#bfdbfe]' }}">
                                            {{ $laundry->is_taken ? 'Diambil' : 'Belum diambil' }}
                                        </span>
                                    </div>
                                    <p class="mt-3 text-sm text-[var(--text-muted)]">{{ $laundry->created_at->format('d M Y') }}</p>
                                </div>
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
                                            <td class="px-4 py-4">{{ ucfirst(str_replace('_', ' ', $laundry->layanan)) }}</td>
                                            <td class="px-4 py-4">{{ $laundry->berat }} kg</td>
                                            <td class="px-4 py-4">
                                                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $laundry->is_taken ? 'bg-[#1e3a8a] text-[#dbeafe]' : 'bg-[#0f1a2e] text-[#bfdbfe]' }}">
                                                    {{ $laundry->is_taken ? 'Diambil' : 'Belum diambil' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4">{{ $laundry->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
