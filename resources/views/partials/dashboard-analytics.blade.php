<div class="py-8 sm:py-10">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
        @if (! $toko)
            @include('partials.dashboard-analytics.empty-state')
        @else
            @include('partials.dashboard-analytics.overview')
            @include('partials.dashboard-analytics.chart-breakdowns')
            @include('partials.dashboard-analytics.performance')
        @endif
    </div>
</div>
