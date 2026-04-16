<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Nyuci.id | Operasional laundry yang ringkas dan rapi</title>
        <link rel="icon" type="image/x-icon" href="{{ url('/storage/icon_blue.ico') }}">
        <link rel="shortcut icon" href="{{ url('/storage/icon_blue.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        x-data="{
            scrolled: false,
            init() {
                const syncScrollState = () => {
                    this.scrolled = window.scrollY > 24;
                };

                syncScrollState();
                window.addEventListener('scroll', syncScrollState, { passive: true });
            },
        }"
        class="nyuci-landing-shell min-h-screen antialiased"
    >
        <header class="nyuci-landing-nav" :class="{ 'is-scrolled': scrolled }">
            <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="nyuci-landing-brandmark">
                        <x-application-logo variant="white" class="h-9 w-9" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-white">Nyuci.id</p>
                        <p class="text-xs text-white/60">Software operasional laundry modern</p>
                    </div>
                </a>

                <nav class="nyuci-landing-nav-links sm:ml-auto" aria-label="Navigasi landing page">
                    <a href="#beranda" class="nyuci-landing-nav-link">Beranda</a>
                    <a href="#preview" class="nyuci-landing-nav-link">Preview</a>
                    <a href="#fitur" class="nyuci-landing-nav-link">Fitur</a>
                    <a href="#cta" class="nyuci-landing-nav-link">CTA</a>
                </nav>

                <div class="flex items-center gap-3 max-sm:w-full max-sm:justify-between sm:ml-2">
                    <a href="{{ route('login') }}" class="nyuci-landing-btn-secondary">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="nyuci-landing-btn-primary">
                            Daftar
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main>
            <section id="beranda" class="nyuci-masthead scroll-mt-28">
                <div class="mx-auto grid min-h-screen w-full max-w-7xl items-center gap-14 px-4 pb-16 pt-28 sm:px-6 lg:grid-cols-[0.92fr_1.08fr] lg:px-8 lg:pb-20 lg:pt-32">
                    <div class="relative z-10 max-w-2xl">
                        <span class="nyuci-landing-kicker">
                            Inspirasi Grayscale, disesuaikan untuk Nyuci.id
                        </span>

                        <h1 class="mt-6 text-4xl font-semibold tracking-[-0.04em] text-white sm:text-5xl lg:text-7xl">
                            Semua order, status, dan pembayaran dalam satu alur yang enak dipakai.
                        </h1>

                        <p class="mt-6 max-w-xl text-base leading-8 text-white/72 sm:text-lg">
                            Nyuci.id membantu kasir dan owner melihat order masuk, memperbarui proses laundry, lalu menutup pembayaran
                            tanpa membuka layar yang terasa berat. Fokusnya sederhana: cepat dibaca, cepat dipakai, dan tetap rapi.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('login') }}" class="nyuci-landing-btn-primary nyuci-landing-btn-large">
                                Masuk ke dashboard
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="nyuci-landing-btn-secondary nyuci-landing-btn-large">
                                    Buat akun baru
                                </a>
                            @endif
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="nyuci-proof-card">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/52">Ringkas</p>
                                <p class="mt-3 text-lg font-semibold text-white">Dashboard langsung kebaca</p>
                                <p class="mt-2 text-sm leading-6 text-white/62">Order aktif, revenue, dan antrian harian terlihat tanpa klik berlapis.</p>
                            </div>
                            <div class="nyuci-proof-card">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/52">Cepat</p>
                                <p class="mt-3 text-lg font-semibold text-white">Update proses dalam beberapa field</p>
                                <p class="mt-2 text-sm leading-6 text-white/62">Edit status, estimasi selesai, dan detail order tetap nyaman di layar kecil.</p>
                            </div>
                            <div class="nyuci-proof-card">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/52">Terkontrol</p>
                                <p class="mt-3 text-lg font-semibold text-white">Invoice dan pembayaran tetap jelas</p>
                                <p class="mt-2 text-sm leading-6 text-white/62">Detail klien, QRIS, dan status lunas tampil rapi dalam satu tampilan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="nyuci-hero-glow"></div>

                        <div class="nyuci-product-frame nyuci-product-frame-dashboard">
                            <div class="nyuci-product-frame-topbar">
                                <div class="nyuci-product-frame-dots" aria-hidden="true">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="nyuci-product-frame-address">app.nyuci.id/dashboard</div>
                            </div>

                            <img
                                src="{{ asset('storage/halaman_dashboard.png') }}"
                                alt="Dashboard Nyuci.id untuk memantau order, revenue, dan aktivitas laundry."
                                class="nyuci-product-frame-image"
                            >
                        </div>

                        <div class="nyuci-floating-note nyuci-floating-note-top">
                            <span class="nyuci-floating-note-label">Order Masuk</span>
                            <strong>30 order</strong>
                            <p>Lihat ritme operasional tanpa pindah halaman.</p>
                        </div>

                        <div class="nyuci-floating-note nyuci-floating-note-bottom">
                            <span class="nyuci-floating-note-label">Revenue</span>
                            <strong>Rp 1.358.600</strong>
                            <p>Ringkasan angka utama tampil di area hero produk.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="preview" class="nyuci-landing-band-light scroll-mt-28">
                <div class="mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <span class="nyuci-section-label">Preview produk</span>
                        <h2 class="mt-4 text-3xl font-semibold tracking-[-0.03em] text-[var(--text-strong)] sm:text-5xl">
                            Landing yang menjual pengalaman produk, bukan hanya daftar fitur.
                        </h2>
                        <p class="mt-5 text-base leading-8 text-[var(--text-main)] sm:text-lg">
                            Struktur halaman depan ini mengambil ritme one-page ala Grayscale, lalu diganti dengan visual produk Nyuci.id yang
                            lebih relevan untuk bisnis laundry: ringkasan operasional, form update yang jelas, dan detail pembayaran yang mudah dibaca.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                        <div class="nyuci-overview-panel">
                            <span class="nyuci-section-label text-[var(--primary)]">Dashboard utama</span>
                            <h3 class="mt-4 text-2xl font-semibold tracking-[-0.03em] text-[var(--text-strong)] sm:text-3xl">
                                Halaman pertama harus langsung menjelaskan kekuatan produk.
                            </h3>
                            <p class="mt-4 max-w-2xl text-base leading-8 text-[var(--text-main)]">
                                Hero menampilkan dashboard asli karena di sanalah value Nyuci.id paling cepat dipahami: owner tahu ada order aktif,
                                kasir tahu apa yang harus dikerjakan hari ini, dan semua angka penting langsung terlihat.
                            </p>

                            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                <div class="nyuci-overview-stat">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--text-muted)]">First scan</p>
                                    <p class="mt-3 text-3xl font-semibold text-[var(--text-strong)]">5 detik</p>
                                    <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">User langsung paham kondisi order dan pembayaran.</p>
                                </div>
                                <div class="nyuci-overview-stat">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--text-muted)]">Aksi utama</p>
                                    <p class="mt-3 text-3xl font-semibold text-[var(--text-strong)]">2 tombol</p>
                                    <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">Tambah laundry dan kelola pembayaran tetap dominan.</p>
                                </div>
                                <div class="nyuci-overview-stat">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--text-muted)]">Rasa produk</p>
                                    <p class="mt-3 text-3xl font-semibold text-[var(--text-strong)]">Modern</p>
                                    <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">Dark hero memberi kesan fokus, premium, dan rapi.</p>
                                </div>
                            </div>
                        </div>

                        <div class="nyuci-overview-stack">
                            <div class="nyuci-overview-note">
                                <span class="nyuci-section-label text-[var(--primary)]">Apa yang di-highlight</span>
                                <p class="mt-4 text-lg font-semibold text-[var(--text-strong)]">Order masuk, revenue, dan tren operasional.</p>
                                <p class="mt-3 text-sm leading-7 text-[var(--text-main)]">
                                    Ini yang paling mudah menjawab pertanyaan calon user: “Kalau saya pakai Nyuci.id, apa yang langsung terasa?”
                                </p>
                            </div>

                            <div class="nyuci-overview-note">
                                <span class="nyuci-section-label text-[var(--primary)]">Kenapa tidak terlalu ramai</span>
                                <p class="mt-4 text-lg font-semibold text-[var(--text-strong)]">Supaya landing tetap terasa tajam seperti halaman SaaS.</p>
                                <p class="mt-3 text-sm leading-7 text-[var(--text-main)]">
                                    Section ini menahan diri dari terlalu banyak badge dan ornamen supaya fokus tetap jatuh ke screenshot produk.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="fitur" class="nyuci-landing-band-dark scroll-mt-28">
                <div class="mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <span class="nyuci-section-label text-white/56">Fitur kunci</span>
                        <h2 class="mt-4 text-3xl font-semibold tracking-[-0.03em] text-white sm:text-5xl">
                            Alur kerja yang terasa ringan saat dipakai, bukan hanya terlihat bagus di demo.
                        </h2>
                        <p class="mt-5 text-base leading-8 text-white/68 sm:text-lg">
                            Dua section ini menampilkan bagian yang paling sering dipakai setelah dashboard: memperbarui order dan memeriksa detail invoice.
                            Layout dibuat alternating supaya ritmenya tetap hidup saat di-scroll.
                        </p>
                    </div>

                    <div class="mt-14 space-y-10">
                        <article class="nyuci-showcase-panel lg:flex-row">
                            <div class="nyuci-showcase-copy">
                                <span class="nyuci-section-label text-[var(--primary)]">Edit order</span>
                                <h3 class="mt-4 text-2xl font-semibold tracking-[-0.03em] text-white sm:text-3xl">
                                    Ubah status dan detail laundry tanpa form yang melelahkan.
                                </h3>
                                <p class="mt-4 text-base leading-8 text-white/68">
                                    Tampilan edit difokuskan ke field yang benar-benar penting saat operasional berjalan: pelanggan, layanan, qty,
                                    status laundry, tanggal mulai, dan estimasi selesai. Sisanya diletakkan rapi tanpa mengganggu ritme kerja.
                                </p>

                                <ul class="mt-6 space-y-3">
                                    <li class="nyuci-showcase-bullet">
                                        <span class="nyuci-showcase-bullet-dot"></span>
                                        Status laundry terlihat besar dan mudah diubah saat order sedang diproses.
                                    </li>
                                    <li class="nyuci-showcase-bullet">
                                        <span class="nyuci-showcase-bullet-dot"></span>
                                        Struktur form dua kolom tetap nyaman di desktop dan tetap rapat di mobile.
                                    </li>
                                    <li class="nyuci-showcase-bullet">
                                        <span class="nyuci-showcase-bullet-dot"></span>
                                        Area hapus dipisahkan jelas supaya aksi berisiko tidak bercampur dengan update harian.
                                    </li>
                                </ul>
                            </div>

                            <div class="nyuci-showcase-visual">
                                <div class="nyuci-product-frame nyuci-product-frame-portrait">
                                    <div class="nyuci-product-frame-topbar">
                                        <div class="nyuci-product-frame-dots" aria-hidden="true">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                        <div class="nyuci-product-frame-address">app.nyuci.id/laundry/edit</div>
                                    </div>

                                    <img
                                        src="{{ asset('storage/edit_page.png') }}"
                                        alt="Form edit laundry di Nyuci.id untuk memperbarui status dan detail order."
                                        class="nyuci-product-frame-image"
                                    >
                                </div>
                            </div>
                        </article>

                        <article class="nyuci-showcase-panel lg:flex-row-reverse">
                            <div class="nyuci-showcase-copy">
                                <span class="nyuci-section-label text-[var(--primary)]">Detail pembayaran</span>
                                <h3 class="mt-4 text-2xl font-semibold tracking-[-0.03em] text-white sm:text-3xl">
                                    Invoice, status pembayaran, dan QRIS disajikan dalam tampilan yang tetap bersih.
                                </h3>
                                <p class="mt-4 text-base leading-8 text-white/68">
                                    Saat admin membuka detail, informasi penting tidak tersebar ke banyak tab. Klien, total, metode bayar,
                                    status laundry, hingga referensi gateway tetap berada di satu layar yang gampang dipahami.
                                </p>

                                <ul class="mt-6 space-y-3">
                                    <li class="nyuci-showcase-bullet">
                                        <span class="nyuci-showcase-bullet-dot"></span>
                                        Blok identitas invoice ditempatkan di atas agar validasi data terasa instan.
                                    </li>
                                    <li class="nyuci-showcase-bullet">
                                        <span class="nyuci-showcase-bullet-dot"></span>
                                        Kartu QRIS tetap menonjol tanpa mengalahkan informasi transaksi utama.
                                    </li>
                                    <li class="nyuci-showcase-bullet">
                                        <span class="nyuci-showcase-bullet-dot"></span>
                                        Cocok untuk pengecekan cepat saat pelanggan bertanya soal status dan pembayaran.
                                    </li>
                                </ul>
                            </div>

                            <div class="nyuci-showcase-visual">
                                <div class="nyuci-product-frame nyuci-product-frame-portrait">
                                    <div class="nyuci-product-frame-topbar">
                                        <div class="nyuci-product-frame-dots" aria-hidden="true">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                        <div class="nyuci-product-frame-address">app.nyuci.id/pembayaran/detail</div>
                                    </div>

                                    <img
                                        src="{{ asset('storage/detail_page.png') }}"
                                        alt="Halaman detail pembayaran Nyuci.id dengan invoice, QRIS, dan status transaksi."
                                        class="nyuci-product-frame-image"
                                    >
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <section id="cta" class="nyuci-landing-band-cta scroll-mt-28">
                <div class="mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                    <div class="nyuci-cta-panel">
                        <div class="max-w-2xl">
                            <span class="nyuci-section-label text-[var(--primary)]">Siap dipakai</span>
                            <h2 class="mt-4 text-3xl font-semibold tracking-[-0.03em] text-[var(--text-strong)] sm:text-5xl">
                                Halaman depan yang menjelaskan produk dengan cepat, lalu langsung mengarahkan user ke aksi.
                            </h2>
                            <p class="mt-5 text-base leading-8 text-[var(--text-main)] sm:text-lg">
                                Visual hero menampilkan dashboard asli, section tengah menjelaskan workflow penting, dan penutupnya mendorong user
                                masuk atau membuat akun tanpa terasa dipaksa. Itu alur utama landing ini.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('login') }}" class="nyuci-landing-btn-primary nyuci-landing-btn-large">
                                Masuk sekarang
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="nyuci-landing-btn-secondary nyuci-landing-btn-large">
                                    Buat akun Nyuci.id
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="nyuci-landing-footer">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-3 px-4 py-6 text-sm text-white/52 sm:px-6 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <p>&copy; {{ now()->year }} Nyuci.id. Landing page publik untuk software operasional laundry.</p>
                <p>Tampilan depan menonjolkan dashboard, edit order, dan detail pembayaran.</p>
            </div>
        </footer>
    </body>
</html>
