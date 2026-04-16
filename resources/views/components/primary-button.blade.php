<button {{ $attributes->merge(['type' => 'submit', 'class' => 'nyuci-btn-primary focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2 focus:ring-offset-[var(--bg-card)]']) }}>
    {{ $slot }}
</button>
