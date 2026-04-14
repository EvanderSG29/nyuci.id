<div class="space-y-6">
    @if ($dashboardCards['highlights'] ?? false)
        <x-card class="nyuci-dashboard-panel rounded-[1.85rem] p-5 sm:p-6">
            <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr] xl:items-center">
                <div>
                    <p class="text-sm font-semibold text-[var(--text-muted)]">Ringkasan bisnis</p>
                    <h3 class="mt-1 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                        {{ $overview['headline'] }}
                    </h3>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-[var(--text-main)]">
                        {{ $overview['attentionLine'] }}
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($overview['miniStats'] as $item)
                        <div class="rounded-[1.2rem] border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                            <p class="text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                {{ $item['label'] }}
                            </p>
                            <p class="mt-3 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                                {{ $item['value'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-card>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($highlights as $card)
                <x-card class="nyuci-dashboard-panel rounded-[1.85rem] p-5">
                    <p class="text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                        {{ $card['label'] }}
                    </p>
                    <p class="mt-4 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">
                        {{ $card['value'] }}
                    </p>
                    <p class="mt-3 text-sm leading-6 text-[var(--text-main)]">
                        {{ $card['caption'] }}
                    </p>
                </x-card>
            @endforeach
        </div>
    @endif

    @if (($dashboardCards['status_breakdown'] ?? false) || ($dashboardCards['payment_breakdown'] ?? false))
        <div class="grid gap-6 xl:grid-cols-2">
            @if ($dashboardCards['status_breakdown'] ?? false)
                <x-card class="nyuci-dashboard-panel rounded-[1.85rem] p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-muted)]">{{ $statusBreakdown['title'] }}</p>
                            <h3 class="mt-1 text-xl font-semibold tracking-tight text-[var(--text-strong)]">
                                Ringkasan progres kerja
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">
                                {{ $statusBreakdown['description'] }}
                            </p>
                        </div>

                        <div class="nyuci-donut-chart" style="background: {{ $statusBreakdown['gradient'] }};">
                            <div class="nyuci-donut-content">
                                <span class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                                    {{ number_format($statusBreakdown['total'], 0, ',', '.') }}
                                </span>
                                <span class="mt-1 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                    total
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        @foreach ($statusBreakdown['segments'] as $segment)
                            <div class="nyuci-dashboard-breakdown rounded-[1.25rem] p-4">
                                <div class="flex items-center gap-3">
                                    <span class="nyuci-dashboard-breakdown-dot" style="background: {{ $segment['color'] }};"></span>
                                    <span class="text-sm font-medium text-[var(--text-strong)]">{{ $segment['label'] }}</span>
                                </div>

                                <div class="nyuci-meter">
                                    <span style="--meter-width: {{ $segment['meterWidth'] }}%; --meter-color: {{ $segment['color'] }};"></span>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-semibold text-[var(--text-strong)]">{{ number_format($segment['count'], 0, ',', '.') }}</p>
                                    <p class="text-xs text-[var(--text-muted)]">{{ rtrim(rtrim(number_format($segment['percentage'], 1, '.', ''), '0'), '.') }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            @if ($dashboardCards['payment_breakdown'] ?? false)
                <x-card class="nyuci-dashboard-panel rounded-[1.85rem] p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-muted)]">{{ $paymentBreakdown['title'] }}</p>
                            <h3 class="mt-1 text-xl font-semibold tracking-tight text-[var(--text-strong)]">
                                Komposisi kanal pembayaran
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">
                                {{ $paymentBreakdown['description'] }}
                            </p>
                        </div>

                        <div class="nyuci-donut-chart" style="background: {{ $paymentBreakdown['gradient'] }};">
                            <div class="nyuci-donut-content">
                                <span class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                                    {{ number_format($paymentBreakdown['total'], 0, ',', '.') }}
                                </span>
                                <span class="mt-1 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                    transaksi
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        @foreach ($paymentBreakdown['segments'] as $segment)
                            <div class="nyuci-dashboard-breakdown rounded-[1.25rem] p-4">
                                <div class="flex items-center gap-3">
                                    <span class="nyuci-dashboard-breakdown-dot" style="background: {{ $segment['color'] }};"></span>
                                    <span class="text-sm font-medium text-[var(--text-strong)]">{{ $segment['label'] }}</span>
                                </div>

                                <div class="nyuci-meter">
                                    <span style="--meter-width: {{ $segment['meterWidth'] }}%; --meter-color: {{ $segment['color'] }};"></span>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-semibold text-[var(--text-strong)]">{{ number_format($segment['count'], 0, ',', '.') }}</p>
                                    <p class="text-xs text-[var(--text-muted)]">{{ rtrim(rtrim(number_format($segment['percentage'], 1, '.', ''), '0'), '.') }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    @endif

    @if (($dashboardCards['top_services'] ?? false) || ($dashboardCards['recent_laundries'] ?? false))
        <div class="grid gap-6 xl:grid-cols-2">
            @if ($dashboardCards['top_services'] ?? false)
                <x-card class="nyuci-dashboard-panel rounded-[1.85rem] p-6">
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
                        <div class="nyuci-dashboard-service mt-4 rounded-[1.35rem] p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-base font-semibold text-[var(--text-strong)]">{{ $service['name'] }}</p>
                                    <p class="mt-1 text-sm text-[var(--text-muted)]">
                                        {{ $service['count'] }} order • {{ $service['qty'] }} {{ $service['unit'] }}
                                    </p>
                                </div>

                                <p class="text-sm font-semibold text-[var(--text-strong)]">{{ $service['revenue'] }}</p>
                            </div>

                            <div class="nyuci-service-bar mt-4">
                                <span style="width: {{ $service['share'] }}%;"></span>
                            </div>
                        </div>
                    @empty
                        <div class="mt-6 rounded-[1.5rem] border border-dashed border-[var(--border-soft)] bg-[var(--bg-surface)] px-6 py-10 text-center">
                            <p class="text-base font-medium text-[var(--text-main)]">Belum ada layanan yang bisa dibandingkan.</p>
                            <p class="mt-2 text-sm text-[var(--text-muted)]">
                                Tambahkan order terlebih dahulu agar performa tiap jasa bisa dibaca.
                            </p>
                        </div>
                    @endforelse
                </x-card>
            @endif

            @if ($dashboardCards['recent_laundries'] ?? false)
                <x-card as="section" class="nyuci-dashboard-panel rounded-[1.85rem] p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-muted)]">Laundry terbaru</p>
                            <h3 class="text-xl font-semibold tracking-tight text-[var(--text-strong)]">
                                Aktivitas terakhir toko Anda
                            </h3>
                        </div>
                        <a href="{{ route('laundry.index') }}" wire:navigate class="text-sm font-medium text-[var(--primary-ink)] transition hover:text-[var(--text-strong)]">
                            Lihat semua data
                        </a>
                    </div>

                    @if ($recentLaundries->isEmpty())
                        <div class="mt-6 rounded-[1.5rem] border border-dashed border-[var(--border-soft)] bg-[var(--bg-surface)] px-6 py-10 text-center">
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
                                <div class="nyuci-dashboard-activity rounded-[1.35rem] p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-[var(--text-strong)]">{{ $laundry->nama }}</p>
                                            <p class="mt-1 text-sm text-[var(--text-muted)]">
                                                {{ $laundry->jenis_jasa_label }} • {{ $laundry->satuan_label }}
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
                                </div>
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
            @endif
        </div>
    @endif
</div>
