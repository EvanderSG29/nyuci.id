<button {{ $attributes->merge(['type' => 'button', 'class' => 'nyuci-btn-secondary focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2 focus:ring-offset-[var(--bg-card)] disabled:opacity-25']) }}>
    {{ $slot }}
</button>
