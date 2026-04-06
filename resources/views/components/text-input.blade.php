@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border border-[var(--border-soft)] bg-[var(--bg-surface)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]']) }}>
