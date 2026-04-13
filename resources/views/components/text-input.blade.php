@props(['disabled' => false])

@php
    $inputAttributes = [
        'class' => 'rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]',
    ];

    if ($attributes->get('type') === 'password') {
        $inputAttributes = [
            ...$inputAttributes,
            'inputmode' => 'text',
            'autocapitalize' => 'off',
            'autocorrect' => 'off',
            'spellcheck' => 'false',
        ];
    }
@endphp

<input @disabled($disabled) {{ $attributes->merge($inputAttributes) }}>
