<section class="grid gap-6 xl:grid-cols-[1fr_1fr]">
    <x-card class="rounded-[2rem] p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-[var(--text-muted)]">Layanan terlaris</p>
                <h3 class="text-xl font-semibold tracking-tight text-[var(--text-strong)]">
                    Kontributor order paling aktif
                </h3>
            </div>
            <a href="{{ route('biaya-jasa.index') }}" wire:navigate class="text-sm font-medium text-[var(--primary-ink)] transition hover:text-[var(--text-strong)]">
                Kelola jasa
            </a>
        </div>

        @forelse ($topServices as $service)
            <div class="nyuci-service-row mt-4 rounded-3xl p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-base font-semibold text-[var(--text-strong)]">{{ $service['name'] }}</p>
                        <p class="mt-1 text-sm text-[var(--text-muted)]">
                            {{ $service['count'] }} order - {{ $service['qty'] }} {{ $service['unit'] }}
                        </p>
                    </div>

                    <p class="text-sm font-semibold text-[var(--text-strong)]">{{ $service['revenue'] }}</p>
                </div>

                <div class="nyuci-service-bar mt-4">
                    <span style="width: {{ $service['share'] }}%;"></span>
                </div>
            </div>
        @empty
            <div class="mt-6 rounded-3xl border border-dashed border-[var(--border-soft)] bg-[var(--bg-surface)] px-6 py-10 text-center">
                <p class="text-base font-medium text-[var(--text-main)]">Belum ada layanan yang bisa dibandingkan.</p>
                <p class="mt-2 text-sm text-[var(--text-muted)]">
                    Tambahkan order terlebih dahulu agar performa tiap jasa bisa dibaca.
                </p>
            </div>
        @endforelse
    </x-card>

    <x-card as="section" class="rounded-[2rem] p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-[var(--text-muted)]">Laundry terbaru</p>
                <h3 class="text-xl font-semibold tracking-tight text-[var(--text-strong)]">Aktivitas terakhir toko Anda</h3>
            </div>
            <a href="{{ route('laundry.index') }}" wire:navigate class="text-sm font-medium text-[var(--primary-ink)] transition hover:text-[var(--text-strong)]">
                Lihat semua data
            </a>
        </div>

        @if ($recentLaundries->isEmpty())
            <div class="mt-6 rounded-3xl border border-dashed border-[var(--border-soft)] bg-[var(--bg-surface)] px-6 py-10 text-center">
                <p class="text-base font-medium text-[var(--text-main)]">Belum ada data laundry.</p>
                <p class="mt-2 text-sm text-[var(--text-muted)]">Mulai dari order pertama agar dashboard ini langsung terisi.</p>
                <div class="mt-5">
                    <a href="{{ route('laundry.create') }}" wire:navigate class="nyuci-btn-primary">
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
                                <p class="mt-1 text-sm text-[var(--text-muted)]">
                                    {{ $laundry->jenis_jasa_label }} - {{ $laundry->satuan_label }}
                                </p>
                            </div>
                            <x-status-badge :variant="$laundry->status === 'selesai' ? 'success' : ($laundry->status === 'proses' ? 'paid' : 'pending')">
                                {{ $laundry->status_label }}
                            </x-status-badge>
                        </div>

                        <div class="mt-4 flex items-center justify-between text-sm text-[var(--text-muted)]">
                            <span>{{ $laundry->created_at->format('d M Y') }}</span>
                            <span>{{ $laundry->pembayaran?->status === 'sudah_bayar' ? 'Sudah bayar' : 'Belum bayar' }}</span>
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div class="mt-6 hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-[var(--border-main)] text-left text-sm text-[var(--text-main)]">
                    <thead>
                        <tr class="text-xs uppercase tracking-[0.18em] text-[var(--text-muted)]">
                            <th class="px-4 py-3 font-semibold">Customer</th>
                            <th class="px-4 py-3 font-semibold">Layanan</th>
                            <th class="px-4 py-3 font-semibold">Qty</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Bayar</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-main)]">
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
                                <td class="px-4 py-4">
                                    <x-status-badge :variant="$laundry->pembayaran?->status === 'sudah_bayar' ? 'success' : 'pending'">
                                        {{ $laundry->pembayaran?->status === 'sudah_bayar' ? 'Sudah bayar' : 'Belum bayar' }}
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
</section>
