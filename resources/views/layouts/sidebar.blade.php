@php
    $user = Auth::user();
    $storeName = $user->toko?->nama_toko ?? 'Laundry digital';
    $storeInitials = str($storeName)
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn ($segment) => str($segment)->substr(0, 1)->upper()->toString())
        ->implode('');
    $userInitials = str($user->name ?? 'Nyuci')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn ($segment) => str($segment)->substr(0, 1)->upper()->toString())
        ->implode('');
    $userInitials = $userInitials !== '' ? $userInitials : 'NY';

    $laundryIcon = new \Illuminate\Support\HtmlString(<<<'HTML'
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4 shrink-0" aria-hidden="true">
            <path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9zM1 7v1h14V7zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5"/>
        </svg>
    HTML);

    $primaryItems = [
        ['label' => 'Beranda', 'route' => 'dashboard', 'icon' => 'home', 'active' => 'dashboard'],
        ['label' => 'Biaya Jasa', 'route' => 'biaya-jasa.index', 'icon' => 'banknotes', 'active' => 'biaya-jasa.*'],
        ['label' => 'Pelanggan', 'route' => 'pelanggan.index', 'icon' => 'users', 'active' => 'pelanggan.*'],
        ['label' => 'Laundry', 'route' => 'laundry.index', 'icon' => $laundryIcon, 'active' => 'laundry.*'],
        ['label' => 'Pembayaran', 'route' => 'pembayaran.index', 'icon' => 'credit-card', 'active' => 'pembayaran.*'],
    ];

@endphp

<flux:sidebar sticky collapsible="mobile" class="nyuci-sidebar-shell border-r !border-[var(--border-main)] !bg-[var(--bg-card)]">
    <flux:sidebar.header class="!border-b !border-[var(--border-main)] !px-3 !py-3">
        <flux:sidebar.brand
            class="nyuci-sidebar-brand !rounded-2xl !px-3 !py-2"
            href="{{ route('dashboard') }}"
            name="Nyuci.id"
            logo="{{ url('/storage/icon_black.png') }}"
            logo:dark="{{ url('/storage/icon.white.png') }}"
            alt="Nyuci.id"
        />

        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <div class="px-3 pt-3">
        <div class="nyuci-sidebar-store">
            <div class="flex items-center gap-3">
                <div class="nyuci-sidebar-avatar">{{ $storeInitials !== '' ? $storeInitials : 'NY' }}</div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-[var(--text-strong)]">{{ $storeName }}</p>
                    <p class="truncate text-xs text-[var(--text-muted)]">{{ $user->name }}</p>
                </div>
            </div>

            <p class="mt-3 truncate text-[11px] text-[var(--text-muted)]">{{ $user->email }}</p>
        </div>
    </div>

    <flux:sidebar.nav class="nyuci-sidebar-scroll gap-1 px-2 pt-3">
        <flux:sidebar.group heading="Menu">
            @foreach ($primaryItems as $item)
                @php($itemClasses = 'nyuci-sidebar-link'.(request()->routeIs($item['active']) ? ' is-active' : ''))
                @if ($item['route'] === 'laundry.index')
                    <flux:sidebar.item href="{{ route($item['route']) }}" :icon="$item['icon']" class="{{ $itemClasses }}">
                        {{ $item['label'] }}
                    </flux:sidebar.item>
                @else
                    <flux:sidebar.item href="{{ route($item['route']) }}" wire:navigate :icon="$item['icon']" class="{{ $itemClasses }}">
                        {{ $item['label'] }}
                    </flux:sidebar.item>
                @endif
            @endforeach
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <div class="space-y-2 px-2 pb-2">
        <a
            href="{{ route('settings.profile') }}"
            wire:navigate
            class="nyuci-sidebar-footer-link flex items-center gap-3 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-3 py-3 transition hover:border-[var(--primary)] hover:bg-[var(--bg-card)]"
        >
            <span class="nyuci-sidebar-avatar">{{ $userInitials }}</span>

            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold text-[var(--text-strong)]">{{ $user->name }}</span>
                <span class="block truncate text-xs text-[var(--text-muted)]">Pengaturan</span>
            </span>

            <flux:icon.chevron-right class="size-4 text-[var(--text-muted)]" />
        </a>

        <form method="POST" action="{{ route('logout') }}" data-settings-guard-action="logout">
            @csrf

            <button type="submit" class="nyuci-sidebar-footer-link flex w-full items-center gap-3 rounded-2xl border border-[var(--border-main)] bg-transparent px-3 py-3 text-left transition hover:border-[var(--danger)] hover:bg-[color-mix(in_srgb,var(--danger)_10%,var(--bg-card))]">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-[color-mix(in_srgb,var(--danger)_14%,var(--bg-card))] text-[var(--danger)]">
                    <flux:icon.arrow-right-start-on-rectangle class="size-5" />
                </span>

                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-[var(--text-strong)]">Keluar</span>
                    <span class="block truncate text-xs text-[var(--text-muted)]">Tutup sesi akun ini</span>
                </span>
            </button>
        </form>
    </div>
</flux:sidebar>
