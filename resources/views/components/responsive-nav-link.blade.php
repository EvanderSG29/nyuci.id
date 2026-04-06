@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-[var(--primary)] bg-[#0f172a] py-2 pe-4 ps-3 text-start text-base font-medium text-[var(--primary-soft)] transition duration-150 ease-in-out focus:border-[var(--primary-hover)] focus:bg-[#0f172a] focus:text-[var(--primary-soft)] focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-[var(--text-muted)] transition duration-150 ease-in-out hover:border-[var(--border-soft)] hover:bg-[var(--bg-surface)] hover:text-[var(--text-main)] focus:border-[var(--border-soft)] focus:bg-[var(--bg-surface)] focus:text-[var(--text-main)] focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
