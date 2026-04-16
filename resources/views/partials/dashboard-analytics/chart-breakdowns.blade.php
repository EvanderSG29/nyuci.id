@php
    $monthlyRevenueCard = collect($highlights)->firstWhere('label', 'Pendapatan bulan ini');
@endphp

<section class="grid gap-6 xl:grid-cols-3">
    <x-card class="nyuci-dashboard-panel rounded-[1.85rem] p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-[var(--text-muted)]">Ringkasan bisnis</p>
                <h3 class="mt-1 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                    {{ $monthlyRevenueCard['value'] ?? 'Rp 0' }}
                </h3>
            </div>

            <span class="nyuci-dashboard-badge">
                Cashflow
            </span>
        </div>

        <p class="mt-4 text-sm leading-7 text-[var(--text-main)]">
            {{ $overview['attentionLine'] }}
        </p>

        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            <div class="rounded-[1.2rem] border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                <p class="text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                    Pembayaran lunas
                </p>
                <p class="mt-3 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                    {{ number_format($overview['paidCount'], 0, ',', '.') }}
                </p>
            </div>

            <div class="rounded-[1.2rem] border border-[var(--border-main)] bg-[var(--bg-surface)] p-4">
                <p class="text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                    Nilai belum lunas
                </p>
                <p class="mt-3 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                    {{ $overview['unpaidValue'] }}
                </p>
            </div>
        </div>
    </x-card>

    @foreach ([$statusBreakdown, $paymentBreakdown] as $breakdown)
        <x-card class="nyuci-dashboard-panel rounded-[1.85rem] p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-[var(--text-muted)]">{{ $breakdown['title'] }}</p>
                    <h3 class="mt-1 text-xl font-semibold tracking-tight text-[var(--text-strong)]">
                        {{ $breakdown['title'] === 'Status order' ? 'Ringkasan progres kerja' : 'Komposisi kanal pembayaran' }}
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">
                        {{ $breakdown['description'] }}
                    </p>
                </div>

                <div class="nyuci-dashboard-donut" style="background: {{ $breakdown['gradient'] }};">
                    <div class="nyuci-dashboard-donut-content">
                        <span class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                            {{ number_format($breakdown['total'], 0, ',', '.') }}
                        </span>
                        <span class="mt-1 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                            {{ $breakdown['title'] === 'Status order' ? 'total' : 'transaksi' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                @foreach ($breakdown['segments'] as $segment)
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
    @endforeach
</section>
