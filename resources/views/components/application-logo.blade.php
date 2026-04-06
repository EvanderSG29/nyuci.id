@props(['variant' => 'black'])

@php
    $src = match ($variant) {
        'white' => url('/storage/icon.white.png'),
        'blue' => url('/storage/icon.blue.png'),
        default => url('/storage/icon_black.png'),
    };
@endphp

<img src="{{ $src }}" alt="Nyuci.id Logo" {{ $attributes->merge(['class' => 'h-10 w-10 object-contain']) }}>
