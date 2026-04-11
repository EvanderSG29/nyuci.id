@php
    $primaryItems = [
        ['label' => 'Beranda', 'route' => 'dashboard', 'icon' => 'home'],
        ['label' => 'Biaya Jasa', 'route' => 'biaya-jasa.index', 'icon' => 'banknotes'],
        ['label' => 'Pelanggan', 'route' => 'pelanggan.index', 'icon' => 'users'],
        ['label' => 'Laundry', 'route' => 'laundry.index', 'icon' => 'truck'],
        ['label' => 'Pembayaran', 'route' => 'pembayaran.index', 'icon' => 'credit-card'],
    ];

    $secondaryItems = [
        ['label' => 'Pengaturan Toko', 'route' => 'pengaturan-toko.edit', 'icon' => 'cog-6-tooth'],
        ['label' => 'Profil Saya', 'route' => 'profile.edit', 'icon' => 'user-circle'],
    ];
@endphp

<flux:sidebar sticky collapsible="mobile" class="border-r !border-[var(--border-main)] !bg-[var(--bg-card)]">
    <flux:sidebar.header class="!border-b !border-[var(--border-main)]">
        <flux:sidebar.brand
            href="{{ route('dashboard') }}"
            name="Nyuci.id"
            logo="{{ url('/storage/icon_black.png') }}"
            logo:dark="{{ url('/storage/icon.white.png') }}"
            alt="Nyuci.id"
        />

        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <div class="px-2 pt-4">
        <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">Operasional toko</p>
            <p class="mt-3 truncate text-sm font-semibold text-[var(--text-strong)]">{{ Auth::user()->name }}</p>
            <p class="mt-1 truncate text-xs text-[var(--text-muted)]">{{ Auth::user()->email }}</p>
        </div>
    </div>

    <flux:sidebar.nav class="gap-1 pt-4">
        <flux:sidebar.group heading="Menu">
            @foreach ($primaryItems as $item)
                @if ($item['route'] === 'laundry.index')
                    <flux:sidebar.item href="{{ route($item['route']) }}" icon="{{ $item['icon'] }}">
                        {{ $item['label'] }}
                    </flux:sidebar.item>
                @else
                    <flux:sidebar.item href="{{ route($item['route']) }}" wire:navigate icon="{{ $item['icon'] }}">
                        {{ $item['label'] }}
                    </flux:sidebar.item>
                @endif
            @endforeach
        </flux:sidebar.group>

        <flux:separator class="my-2" />

        <flux:sidebar.group heading="Akun">
            @foreach ($secondaryItems as $item)
                <flux:sidebar.item href="{{ route($item['route']) }}" wire:navigate icon="{{ $item['icon'] }}">
                    {{ $item['label'] }}
                </flux:sidebar.item>
            @endforeach
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <div class="px-2 pb-2">
        <flux:dropdown position="top start" align="start" class="w-full">
            <flux:sidebar.profile :name="Auth::user()->name" :chevron="true" />

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
