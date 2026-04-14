<section class="nyuci-dashboard-hero rounded-[2rem] px-6 py-6 sm:px-8 sm:py-8">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/70">Dashboard</p>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                    {{ $toko->nama_toko }}
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/78 sm:text-base">
                    {{ $overview['headline'] }}
                </p>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-white/72 sm:text-[0.95rem]">
                    {{ $overview['attentionLine'] }}
                </p>
                <p class="mt-4 text-xs font-medium uppercase tracking-[0.18em] text-white/55">
                    {{ $heroChart['period_label'] ?? '' }}
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('laundry.create') }}" wire:navigate class="nyuci-dashboard-cta">
                        Tambah Laundry
                    </a>
                    <a href="{{ route('pembayaran.index') }}" wire:navigate class="nyuci-dashboard-cta-secondary">
                        Kelola Pembayaran
                    </a>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 xl:max-w-2xl xl:justify-end">
                @foreach ($heroChart['summary_items'] ?? [] as $summaryItem)
                    <div class="nyuci-dashboard-chart-pill">
                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-white/60">
                            {{ $summaryItem['label'] }}
                        </p>
                        <p class="mt-2 text-base font-semibold text-white">
                            {{ $summaryItem['value'] }}
                        </p>
                        @if (! empty($summaryItem['trend']))
                            <p class="mt-1 text-[0.72rem] font-medium text-white/72">
                                {{ $summaryItem['trend'] }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="nyuci-dashboard-chart-shell rounded-[1.8rem] p-4 sm:p-5">
            <div class="h-[22rem] sm:h-[24rem]">
                <div data-dashboard-chart='@json($heroChart)' class="h-full w-full">
                    <canvas class="h-full w-full"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>
