<section class="-mt-16 grid gap-4 lg:grid-cols-3">
    @foreach ($cardCharts as $chart)
        <x-card class="nyuci-dashboard-panel relative z-20 overflow-hidden rounded-[1.85rem] p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-[var(--text-muted)]">
                        {{ $chart['title'] }}
                    </p>
                    <h3 class="mt-1 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                        {{ $chart['summary_items'][0]['value'] ?? '0' }}
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">
                        {{ $chart['subtitle'] ?? '' }}
                    </p>
                </div>

                <span class="nyuci-dashboard-badge shrink-0">
                    {{ $chart['summary_items'][0]['caption'] ?? 'Total periode' }}
                </span>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($chart['summary_items'] ?? [] as $summaryItem)
                    <span class="inline-flex items-center rounded-full border border-[var(--border-main)] bg-[var(--bg-surface)] px-3 py-1 text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-[var(--text-muted)]">
                        {{ $summaryItem['label'] }}: {{ $summaryItem['value'] }}
                    </span>
                @endforeach
            </div>

            <div class="mt-5 h-[16rem]">
                <div data-dashboard-chart='@json($chart)' class="h-full w-full">
                    <canvas class="h-full w-full"></canvas>
                </div>
            </div>
        </x-card>
    @endforeach
</section>
