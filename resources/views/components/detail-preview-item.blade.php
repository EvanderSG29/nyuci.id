@props([
    'label',
    'value' => null,
    'emphasis' => false,
])

@php
    $slotContent = trim((string) $slot);
    $hasSlotContent = $slotContent !== '';
@endphp

<div class="nyuci-detail-preview-item">
    <p class="nyuci-detail-preview-label">{{ $label }}</p>

    @if ($hasSlotContent)
        <div class="{{ $emphasis ? 'nyuci-detail-preview-value nyuci-detail-preview-value-strong' : 'nyuci-detail-preview-value' }}">
            {{ $slot }}
        </div>
    @else
        <p class="{{ $emphasis ? 'nyuci-detail-preview-value nyuci-detail-preview-value-strong' : 'nyuci-detail-preview-value' }}">
            {{ filled($value) ? $value : '-' }}
        </p>
    @endif
</div>
