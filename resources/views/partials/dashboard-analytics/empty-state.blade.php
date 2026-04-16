<section class="nyuci-dashboard-hero rounded-[2rem] px-6 py-8 sm:px-8">
    <div class="grid gap-8 xl:grid-cols-[1.15fr_0.85fr] xl:items-center">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/70">Dashboard belum aktif</p>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                Lengkapi data toko supaya dashboard bisa dipakai penuh.
            </h1>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/78 sm:text-base">
                Saat ini akun Anda sudah login, tetapi informasi toko belum tersedia. Lengkapi nama toko, alamat, dan
                nomor kontak agar data laundry, pelanggan, dan pembayaran bisa dipisahkan per toko dengan aman.
            </p>

            <div class="mt-6">
                <a href="{{ route('register.toko.create') }}" class="nyuci-dashboard-cta">
                    Lengkapi profil toko
                </a>
            </div>
        </div>

        <div class="rounded-[1.8rem] border border-white/12 bg-white/8 p-6 backdrop-blur">
            <p class="text-sm font-semibold text-white/78">Setelah profil toko lengkap, dashboard akan menampilkan:</p>

            <div class="mt-5 grid gap-3">
                @foreach (['Tren order 14 hari terakhir', 'Ringkasan pembayaran dan status kerja', 'Akses cepat ke laundry terbaru'] as $item)
                    <div class="rounded-[1.2rem] border border-white/10 bg-white/6 px-4 py-4 text-sm text-white/72">
                        {{ $item }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
