<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Nyuci.id | Laundry digital yang rapi dan ringan</title>
        <link rel="icon" type="image/x-icon" href="{{ url('/storage/icon_blue.ico') }}">
        <link rel="shortcut icon" href="{{ url('/storage/icon_blue.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @include('layouts.partials.theme-init')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="themeManager()" :class="{ 'dark theme-dark': resolvedTheme === 'dark' }" class="nyuci-landing-shell min-h-screen antialiased">
        <header class="border-b border-[var(--border-main)]">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="nyuci-logo-light">
                        <x-application-logo variant="black" class="h-10 w-10" />
                    </span>
                    <span class="nyuci-logo-dark">
                        <x-application-logo variant="white" class="h-10 w-10" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[var(--text-strong)]">Nyuci.id</p>
                        <p class="text-xs text-[var(--text-muted)]">Laundry digital untuk operasional harian</p>
                    </div>
                </a>



                <div class="flex flex-wrap items-center justify-end gap-3">
                    <x-theme-switch />
                    <a href="{{ route('login') }}" class="rounded-full border border-[var(--border-soft)] px-4 py-2 text-sm font-medium text-[var(--text-main)] transition hover:border-[var(--primary)] hover:text-[var(--text-strong)]">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-full bg-[var(--primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--primary-hover)]">
                            Daftar
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main>
            <section class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.2fr_0.8fr] lg:px-8 lg:py-24">
                <div class="max-w-2xl">
                    <span class="nyuci-pill-soft inline-flex rounded-full border bg-[var(--primary-soft)] px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-[var(--primary-ink)]">
                        Simpel, cepat, dan enak dipakai
                    </span>
                    <h1 class="mt-6 text-4xl font-semibold tracking-tight text-[var(--text-strong)] sm:text-5xl lg:text-6xl">
                        Kelola laundry tanpa dashboard yang ribet.
                    </h1>
                    <p class="mt-6 text-base leading-7 text-[var(--text-main)] sm:text-lg">
                        Catat order, pantau cucian yang belum diambil, dan cek pembayaran selesai dalam satu alur yang ringan.
                        Tampilan dibuat ringkas supaya nyaman dipakai di kasir desktop maupun handphone.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-[var(--accent)] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[var(--accent-deep)]">
                            Masuk ke dashboard
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full border border-[var(--border-soft)] px-6 py-3 text-sm font-semibold text-[var(--text-main)] transition hover:border-[var(--primary)] hover:bg-[var(--bg-card)]">
                                Buat akun baru
                            </a>
                        @endif
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div class="nyuci-glass-panel rounded-3xl p-4">
                            <p class="text-2xl font-semibold text-[var(--text-strong)]">1 layar</p>
                            <p class="mt-2 text-sm text-[var(--text-main)]">Ringkasan toko, laundry, dan pembayaran tanpa berpindah-pindah.</p>
                        </div>
                        <div class="nyuci-glass-panel rounded-3xl p-4">
                            <p class="text-2xl font-semibold text-[var(--text-strong)]">Mobile-ready</p>
                            <p class="mt-2 text-sm text-[var(--text-main)]">Komponen tetap rapih di layar kecil, cocok untuk penggunaan cepat.</p>
                        </div>
                        <div class="nyuci-glass-panel rounded-3xl p-4">
                            <p class="text-2xl font-semibold text-[var(--text-strong)]">Fokus</p>
                            <p class="mt-2 text-sm text-[var(--text-main)]">UI dibuat minimal supaya pekerjaan operasional terasa jelas.</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="nyuci-auth-panel rounded-[2rem] p-4">
                        <div class="rounded-[1.5rem] border border-[var(--border-main)] bg-[var(--bg-surface)] p-5">
                            <div class="flex items-center justify-between border-b border-[var(--border-main)] pb-4">
                                <div>
                                    <p class="text-sm font-medium text-[var(--text-strong)]">Dashboard operasional</p>
                                    <p class="text-xs text-[var(--text-muted)]">Semua hal penting langsung terlihat</p>
                                </div>
                                <span class="rounded-full bg-[var(--primary-soft)] px-3 py-1 text-xs font-medium text-[var(--primary-ink)]">Aktif</span>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-[var(--bg-card)] p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-[var(--text-muted)]">Laundry masuk</p>
                                    <p class="mt-2 text-3xl font-semibold text-[var(--text-strong)]">128</p>
                                    <p class="mt-1 text-sm text-[var(--text-muted)]">Tercatat rapi per toko</p>
                                </div>
                                <div class="rounded-2xl bg-[var(--bg-card)] p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-[var(--text-muted)]">Belum diambil</p>
                                    <p class="mt-2 text-3xl font-semibold text-[var(--primary)]">14</p>
                                    <p class="mt-1 text-sm text-[var(--text-muted)]">Mudah dipantau harian</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3">
                                <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-[var(--text-strong)]">Pembayaran selesai</p>
                                            <p class="mt-1 text-sm text-[var(--text-muted)]">Status lunas mudah dicek oleh admin.</p>
                                        </div>
                                        <span class="rounded-full bg-[var(--primary-soft)] px-3 py-1 text-xs font-medium text-[var(--primary-ink)]">Tersinkron</span>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4">
                                    <p class="font-medium text-[var(--text-strong)]">Aksi cepat</p>
                                    <p class="mt-1 text-sm text-[var(--text-muted)]">Tambah laundry baru, cek daftar order, lalu buat pembayaran dari satu tempat.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-full max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="nyuci-glass-panel rounded-3xl p-6">
                        <p class="text-sm font-semibold text-[var(--text-strong)]">Catat order lebih cepat</p>
                        <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">Data pelanggan, berat, layanan, dan estimasi selesai tetap ringkas dan mudah dibaca.</p>
                    </div>
                    <div class="nyuci-glass-panel rounded-3xl p-6">
                        <p class="text-sm font-semibold text-[var(--text-strong)]">Kontrol status lebih jelas</p>
                        <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">Laundry yang belum diambil langsung terlihat tanpa harus cek satu per satu.</p>
                    </div>
                    <div class="nyuci-glass-panel rounded-3xl p-6">
                        <p class="text-sm font-semibold text-[var(--text-strong)]">Nyaman di berbagai ukuran layar</p>
                        <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">Komponen dibuat hybrid untuk desktop dan handphone dengan jarak dan tipografi yang rapi.</p>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
