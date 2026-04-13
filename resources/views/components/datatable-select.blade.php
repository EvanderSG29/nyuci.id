@props([
    'name',
    'options' => [],
    'placeholder' => 'Pilih opsi',
])

<label class="block">
    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
        {{ $placeholder }}
    </span>

    <select data-dt-filter="{{ $name }}" class="nyuci-filter-input">
        @foreach ($options as $option)
            @php
                $label = $option['label'] ?? '';
                $meta = $option['meta'] ?? null;
            @endphp

            <option value="{{ $option['value'] ?? '' }}">
                {{ $meta ? $label . ' / ' . $meta : $label }}
            </option>
        @endforeach
    </select>
</label>
