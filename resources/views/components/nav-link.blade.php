@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-[var(--primary)] px-1 pt-1 text-sm font-medium leading-5 text-[var(--primary-ink)] focus:border-[var(--primary-hover)] focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-[var(--text-muted)] transition duration-150 ease-in-out hover:border-[var(--border-soft)] hover:text-[var(--text-main)] focus:border-[var(--border-soft)] focus:text-[var(--text-main)] focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
