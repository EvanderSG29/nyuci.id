@php
    $gridLines = data_get($trend, 'gridLines', []);
    $points = data_get($trend, 'points', []);
    $axisLabels = data_get($trend, 'axisLabels', []);
    $ordersAreaPath = data_get($trend, 'ordersAreaPath', '');
    $ordersPath = data_get($trend, 'ordersPath', '');
    $finishedPath = data_get($trend, 'finishedPath', '');
@endphp

<section class="nyuci-dashboard-hero rounded-[2rem] px-6 py-6 sm:px-8 sm:py-8">
    <div class="grid gap-8 xl:grid-cols-[1.05fr_1.25fr]">
        <div class="flex flex-col gap-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/70">Dashboard</p>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                    {{ $toko->nama_toko }}
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/78 sm:text-base">
                    {{ $overview['headline'] }}
                </p>
                <p class="mt-4 text-xs font-medium uppercase tracking-[0.18em] text-white/55">
                    {{ $trend['period'] }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('laundry.create') }}" wire:navigate class="nyuci-dashboard-cta">
                    Tambah Laundry
                </a>
                <a href="{{ route('pembayaran.index') }}" wire:navigate class="nyuci-dashboard-cta-secondary">
                    Kelola Pembayaran
                </a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($overview['miniStats'] as $item)
                    <div class="nyuci-dashboard-mini-stat rounded-[1.35rem] p-4">
                        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-white/60">
                            {{ $item['label'] }}
                        </p>
                        <p class="mt-3 text-2xl font-semibold tracking-tight text-white">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="nyuci-dashboard-chart-shell rounded-[1.8rem] p-5 sm:p-6">
            <div class="flex flex-wrap gap-3">
                @foreach ($trend['totals'] as $item)
                    <div class="nyuci-dashboard-chart-pill">
                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-white/60">
                            {{ $item['label'] }}
                        </p>
                        <p class="mt-2 text-base font-semibold text-white">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            @if ($trend['hasData'])
                <div class="mt-6">
                    <svg viewBox="0 0 100 60" class="h-64 w-full overflow-visible" preserveAspectRatio="none" aria-hidden="true">
                        @foreach ($gridLines as $line)
                            <line x1="0" y1="{{ $line['y'] }}" x2="100" y2="{{ $line['y'] }}" class="nyuci-dashboard-chart-grid" />
                        @endforeach

                        <path d="{{ $ordersAreaPath }}" class="nyuci-dashboard-chart-area" />
                        <path d="{{ $ordersPath }}" class="nyuci-dashboard-chart-line-primary" />
                        <path d="{{ $finishedPath }}" class="nyuci-dashboard-chart-line-secondary" />

                        @foreach ($points as $point)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['ordersY'] }}" r="1.25" class="nyuci-dashboard-chart-dot-primary">
                                <title>{{ $point['label'] }}: {{ $point['orders'] }} order masuk</title>
                            </circle>
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['finishedY'] }}" r="1.05" class="nyuci-dashboard-chart-dot-secondary">
                                <title>{{ $point['label'] }}: {{ $point['finished'] }} order selesai</title>
                            </circle>
                        @endforeach
                    </svg>

                    <div class="mt-4 grid grid-cols-5 gap-2 text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-white/58">
                        @foreach ($axisLabels as $label)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-6 rounded-[1.5rem] border border-white/12 bg-white/6 px-6 py-12 text-center">
                    <p class="text-base font-medium text-white">Grafik operasional akan muncul setelah ada order masuk.</p>
                    <p class="mt-2 text-sm text-white/70">
                        Tambahkan laundry pertama agar dashboard ini mulai menampilkan tren kerja toko.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($highlights as $card)
            <div class="nyuci-dashboard-highlight rounded-[1.6rem] p-5">
                <p class="text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-white/60">
                    {{ $card['label'] }}
                </p>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-white">
                    {{ $card['value'] }}
                </p>
                <p class="mt-3 text-sm leading-6 text-white/72">
                    {{ $card['caption'] }}
                </p>
            </div>
        @endforeach
    </div>
</section>
