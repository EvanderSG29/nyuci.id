@php
    $user = Auth::user();
    $storeName = $user->toko?->nama_toko ?? 'Laundry digital';
    $storeInitials = str($storeName)
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn ($segment) => str($segment)->substr(0, 1)->upper()->toString())
        ->implode('');

    $primaryItems = [
        ['label' => 'Beranda', 'route' => 'dashboard', 'icon' => 'home', 'active' => 'dashboard'],
        ['label' => 'Biaya Jasa', 'route' => 'biaya-jasa.index', 'icon' => 'banknotes', 'active' => 'biaya-jasa.*'],
        ['label' => 'Pelanggan', 'route' => 'pelanggan.index', 'icon' => 'users', 'active' => 'pelanggan.*'],
        ['label' => 'Laundry', 'route' => 'laundry.index', 'icon' => 'truck', 'active' => 'laundry.*'],
        ['label' => 'Pembayaran', 'route' => 'pembayaran.index', 'icon' => 'credit-card', 'active' => 'pembayaran.*'],
    ];

    $secondaryItems = [
        ['label' => 'Pengaturan Toko', 'route' => 'pengaturan-toko.edit', 'icon' => 'cog-6-tooth', 'active' => 'pengaturan-toko.*'],
        ['label' => 'Profil Saya', 'route' => 'profile.edit', 'icon' => 'user-circle', 'active' => 'profile.*'],
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
                    <flux:sidebar.item href="{{ route($item['route']) }}" icon="{{ $item['icon'] }}" class="{{ $itemClasses }}">
                        {{ $item['label'] }}
                    </flux:sidebar.item>
                @else
                    <flux:sidebar.item href="{{ route($item['route']) }}" wire:navigate icon="{{ $item['icon'] }}" class="{{ $itemClasses }}">
                        {{ $item['label'] }}
                    </flux:sidebar.item>
                @endif
            @endforeach
        </flux:sidebar.group>

        <flux:separator class="my-2" />

        <flux:sidebar.group heading="Akun">
            @foreach ($secondaryItems as $item)
                <flux:sidebar.item
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    icon="{{ $item['icon'] }}"
                    class="{{ 'nyuci-sidebar-link'.(request()->routeIs($item['active']) ? ' is-active' : '') }}"
                >
                    {{ $item['label'] }}
                </flux:sidebar.item>
            @endforeach
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <div class="px-2 pb-2">
        <flux:dropdown position="top start" align="start" class="w-full">
            <flux:sidebar.profile class="nyuci-sidebar-profile" :name="$user->name" :chevron="true" />

            <flux:menu>
                <flux:menu.item href="{{ route('dashboard') }}" wire:navigate icon="home">
                    Dashboard
                </flux:menu.item>
                <flux:menu.item href="{{ route('profile.edit') }}" wire:navigate icon="user-circle">
                    Profil Saya
                </flux:menu.item>
                <flux:menu.item href="{{ route('pengaturan-toko.edit') }}" wire:navigate icon="cog-6-tooth">
                    Pengaturan Toko
                </flux:menu.item>

                <flux:separator class="my-1" />

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item type="submit" variant="danger" icon="arrow-right-start-on-rectangle">
                        Keluar
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </div>
</flux:sidebar>
