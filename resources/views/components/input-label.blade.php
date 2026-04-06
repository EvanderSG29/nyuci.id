@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-[var(--text-main)]']) }}>
    {{ $value ?? $slot }}
</label>
