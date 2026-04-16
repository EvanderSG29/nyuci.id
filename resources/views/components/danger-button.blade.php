<button {{ $attributes->merge(['type' => 'submit', 'class' => 'nyuci-btn-danger focus:outline-none focus:ring-2 focus:ring-[var(--danger)] focus:ring-offset-2 focus:ring-offset-[var(--bg-card)]']) }}>
    {{ $slot }}
</button>
