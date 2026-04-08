@props(['variant' => 'default'])

@php
    $classes = match ($variant) {
        'success', 'paid' => 'nyuci-badge-success',
        'pending', 'unpaid' => 'nyuci-badge-pending',
        default => 'nyuci-badge-default',
    };
@endphp

<span {{ $attributes->class(['nyuci-badge', $classes]) }}>
    {{ $slot }}
</span>
