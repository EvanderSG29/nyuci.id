<div class="pb-8 pt-0 sm:pb-10">
    @if (! $toko)
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('partials.dashboard-analytics.empty-state')
        </div>
    @else
        <div class="w-full px-4 sm:px-6 lg:px-8">
            @include('partials.dashboard-analytics.hero')
            @include('partials.dashboard-analytics.chart-cards')
        </div>

        <div class="mx-auto mt-8 max-w-6xl px-4 sm:px-6 lg:px-8">
            @include('partials.dashboard-analytics.insights')
        </div>
    @endif
</div>
