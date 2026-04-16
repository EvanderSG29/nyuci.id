@props(['as' => 'div'])

<{{ $as }} {{ $attributes->class(['nyuci-card']) }}>
    {{ $slot }}
</{{ $as }}>
