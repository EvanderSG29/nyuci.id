@props([
    'title',
    'subtitle' => null,
])

<div class="nyuci-detail-preview">
    <div class="nyuci-detail-preview-header">
        <p class="nyuci-detail-preview-kicker">Ringkasan</p>
        <h3 class="nyuci-detail-preview-title">{{ $title }}</h3>

        @if (filled($subtitle))
            <p class="nyuci-detail-preview-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="nyuci-detail-preview-grid">
        {{ $slot }}
    </div>
</div>
