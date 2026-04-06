<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-full border border-transparent bg-[var(--primary)] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out duration-150 hover:bg-[var(--primary-hover)] focus:bg-[var(--primary-hover)] active:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
