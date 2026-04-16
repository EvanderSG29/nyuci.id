@props(['variant' => 'default'])

@php
    $classes = match ($variant) {
        'success', 'paid' => 'nyuci-badge-success',
        'pending', 'unpaid' => 'nyuci-badge-pending',
        'danger' => 'nyuci-badge-danger',
        default => 'nyuci-badge-default',
    };
@endphp

<span {{ $attributes->class(['nyuci-badge', $classes]) }}>
    {{ $slot }}
</span>
