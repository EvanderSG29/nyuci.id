<section class="grid gap-6 xl:grid-cols-[1.45fr_0.95fr]">
    <x-card class="rounded-[2rem] p-6">
        @php
            $gridLines = data_get($trend, 'gridLines', []);
            $points = data_get($trend, 'points', []);
            $axisLabels = data_get($trend, 'axisLabels', []);
            $ordersAreaPath = data_get($trend, 'ordersAreaPath', '');
            $ordersPath = data_get($trend, 'ordersPath', '');
            $finishedPath = data_get($trend, 'finishedPath', '');
        @endphp

        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-[var(--text-muted)]">Statistik operasional</p>
                <h3 class="mt-1 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                    {{ $trend['heading'] }}
                </h3>
                <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                    {{ $trend['period'] }}. Garis biru menunjukkan order masuk, garis hijau menunjukkan order selesai.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @foreach ($trend['totals'] as $item)
                    <div class="nyuci-kpi-chip rounded-2xl px-4 py-3">
                        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                            {{ $item['label'] }}
                        </p>
                        <p class="mt-2 text-base font-semibold text-[var(--text-strong)]">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($trend['hasData'])
            <div class="nyuci-chart-shell mt-6 rounded-[1.75rem] p-4 sm:p-5">
                <svg viewBox="0 0 100 60" class="h-64 w-full overflow-visible" preserveAspectRatio="none" aria-hidden="true">
                    @foreach ($gridLines as $line)
                        <line x1="0" y1="{{ $line['y'] }}" x2="100" y2="{{ $line['y'] }}" class="nyuci-chart-grid" />
                    @endforeach

                    <path d="{{ $ordersAreaPath }}" class="nyuci-chart-area" />
                    <path d="{{ $ordersPath }}" class="nyuci-chart-line-primary" />
                    <path d="{{ $finishedPath }}" class="nyuci-chart-line-secondary" />

                    @foreach ($points as $point)
                        <circle cx="{{ $point['x'] }}" cy="{{ $point['ordersY'] }}" r="1.3" class="nyuci-chart-dot-primary">
                            <title>{{ $point['label'] }}: {{ $point['orders'] }} order masuk</title>
                        </circle>
                        <circle cx="{{ $point['x'] }}" cy="{{ $point['finishedY'] }}" r="1.1" class="nyuci-chart-dot-secondary">
                            <title>{{ $point['label'] }}: {{ $point['finished'] }} order selesai</title>
                        </circle>
                    @endforeach
                </svg>

                <div class="mt-4 grid grid-cols-5 gap-2 text-xs font-medium text-[var(--text-muted)]">
                    @foreach ($axisLabels as $label)
                        <span>{{ $label }}</span>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mt-6 rounded-[1.75rem] border border-dashed border-[var(--border-soft)] bg-[var(--bg-surface)] px-6 py-12 text-center">
                <p class="text-base font-medium text-[var(--text-main)]">Grafik akan muncul setelah ada order masuk.</p>
                <p class="mt-2 text-sm text-[var(--text-muted)]">
                    Tambahkan laundry pertama agar tren operasional 14 hari terakhir mulai terbaca.
                </p>
                <div class="mt-5">
                    <a href="{{ route('laundry.create') }}" wire:navigate class="nyuci-btn-primary">
                        Tambah laundry pertama
                    </a>
                </div>
            </div>
        @endif

        <div class="mt-5 flex flex-wrap gap-4 text-xs font-medium text-[var(--text-muted)]">
            <span class="inline-flex items-center gap-2">
                <span class="nyuci-legend-dot bg-[var(--chart-primary)]"></span>
                Order masuk
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="nyuci-legend-dot bg-[var(--chart-emerald)]"></span>
                Order selesai
            </span>
            <span>Data disusun dari tanggal masuk, tanggal selesai, dan pembayaran lunas.</span>
        </div>
    </x-card>

    <div class="grid gap-6">
        @foreach ([$statusBreakdown, $paymentBreakdown] as $breakdown)
            <x-card class="rounded-[2rem] p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[var(--text-muted)]">{{ $breakdown['title'] }}</p>
                        <h3 class="mt-1 text-xl font-semibold tracking-tight text-[var(--text-strong)]">
                            {{ $breakdown['title'] === 'Status order' ? 'Ringkasan progres kerja' : 'Komposisi kanal pembayaran' }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                            {{ $breakdown['description'] }}
                        </p>
                    </div>

                    <div class="nyuci-donut-chart" style="background: {{ $breakdown['gradient'] }};">
                        <div class="nyuci-donut-content">
                            <span class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                                {{ number_format($breakdown['total'], 0, ',', '.') }}
                            </span>
                            <span class="mt-1 text-xs font-medium uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                {{ $breakdown['title'] === 'Status order' ? 'total' : 'transaksi' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ($breakdown['segments'] as $segment)
                        <div class="nyuci-breakdown-row rounded-3xl p-4">
                            <div class="flex items-center gap-3">
                                <span class="nyuci-breakdown-dot" style="background: {{ $segment['color'] }};"></span>
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
        @endforeach
    </div>
</section>
