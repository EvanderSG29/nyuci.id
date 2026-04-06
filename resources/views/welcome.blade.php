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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute left-1/2 top-0 h-[32rem] w-[32rem] -translate-x-1/2 rounded-full bg-[#3b82f6]/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-[#1e3a8a]/20 blur-3xl"></div>
        </div>

        <header class="border-b border-white/10">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <x-application-logo variant="white" class="h-10 w-10" />
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[#bfdbfe]">Nyuci.id</p>
                        <p class="text-xs text-slate-400">Laundry digital untuk operasional harian</p>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="rounded-full border border-white/15 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-[#93c5fd] hover:text-white">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-full bg-[#3b82f6] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#2563eb]">
                            Daftar
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main>
            <section class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.2fr_0.8fr] lg:px-8 lg:py-24">
                <div class="max-w-2xl">
                    <span class="inline-flex rounded-full border border-[#3b82f6]/35 bg-[#3b82f6]/15 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-[#bfdbfe]">
                        Simpel, cepat, dan enak dipakai
                    </span>
                    <h1 class="mt-6 text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Kelola laundry tanpa dashboard yang ribet.
                    </h1>
                    <p class="mt-6 text-base leading-7 text-slate-300 sm:text-lg">
                        Catat order, pantau cucian yang belum diambil, dan cek pembayaran selesai dalam satu alur yang ringan.
                        Tampilan dibuat ringkas supaya nyaman dipakai di kasir desktop maupun handphone.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-100">
                            Masuk ke dashboard
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full border border-white/15 px-6 py-3 text-sm font-semibold text-slate-100 transition hover:border-[#93c5fd] hover:bg-white/5">
                                Buat akun baru
                            </a>
                        @endif
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                            <p class="text-2xl font-semibold text-white">1 layar</p>
                            <p class="mt-2 text-sm text-slate-300">Ringkasan toko, laundry, dan pembayaran tanpa berpindah-pindah.</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                            <p class="text-2xl font-semibold text-white">Mobile-ready</p>
                            <p class="mt-2 text-sm text-slate-300">Komponen tetap rapih di layar kecil, cocok untuk penggunaan cepat.</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                            <p class="text-2xl font-semibold text-white">Fokus</p>
                            <p class="mt-2 text-sm text-slate-300">UI dibuat minimal supaya pekerjaan operasional terasa jelas.</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="rounded-[2rem] border border-white/10 bg-white/10 p-4 shadow-2xl shadow-slate-950/40 backdrop-blur">
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-900/80 p-5">
                            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                <div>
                                    <p class="text-sm font-medium text-white">Dashboard operasional</p>
                                    <p class="text-xs text-slate-400">Semua hal penting langsung terlihat</p>
                                </div>
                                <span class="rounded-full bg-[#3b82f6]/20 px-3 py-1 text-xs font-medium text-[#bfdbfe]">Aktif</span>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-slate-800/80 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Laundry masuk</p>
                                    <p class="mt-2 text-3xl font-semibold text-white">128</p>
                                    <p class="mt-1 text-sm text-slate-400">Tercatat rapi per toko</p>
                                </div>
                                <div class="rounded-2xl bg-slate-800/80 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Belum diambil</p>
                                    <p class="mt-2 text-3xl font-semibold text-[#bfdbfe]">14</p>
                                    <p class="mt-1 text-sm text-slate-400">Mudah dipantau harian</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3">
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-white">Pembayaran selesai</p>
                                            <p class="mt-1 text-sm text-slate-400">Status lunas mudah dicek oleh admin.</p>
                                        </div>
                                        <span class="rounded-full bg-[#3b82f6]/20 px-3 py-1 text-xs font-medium text-[#bfdbfe]">Tersinkron</span>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <p class="font-medium text-white">Aksi cepat</p>
                                    <p class="mt-1 text-sm text-slate-400">Tambah laundry baru, cek daftar order, lalu buat pembayaran dari satu tempat.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-full max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <p class="text-sm font-semibold text-white">Catat order lebih cepat</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Data pelanggan, berat, layanan, dan estimasi selesai tetap ringkas dan mudah dibaca.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <p class="text-sm font-semibold text-white">Kontrol status lebih jelas</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Laundry yang belum diambil langsung terlihat tanpa harus cek satu per satu.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <p class="text-sm font-semibold text-white">Nyaman di berbagai ukuran layar</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Komponen dibuat hybrid untuk desktop dan handphone dengan jarak dan tipografi yang rapi.</p>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
