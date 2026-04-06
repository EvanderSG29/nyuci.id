<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-full border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[var(--text-main)] shadow-sm transition ease-in-out duration-150 hover:border-[#3b82f6] hover:text-[var(--text-strong)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
