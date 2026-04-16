<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-[var(--border-main)] bg-[var(--bg-card)] backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <x-application-logo variant="white" class="h-10 w-10" />
                <div class="hidden sm:block">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Nyuci.id</p>
                    <p class="text-xs text-[var(--text-muted)]">{{ Auth::user()->toko?->nama_toko ?? 'Laundry digital' }}</p>
                </div>
            </a>

            <div class="hidden items-center gap-2 md:flex">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-nav-link>
                <x-nav-link :href="route('laundry.index')" :active="request()->routeIs('laundry.*')">
                    Laundry
                </x-nav-link>
                <x-nav-link :href="route('pembayaran.index')" :active="request()->routeIs('pembayaran.*')">
                    Pembayaran
                </x-nav-link>
            </div>
        </div>

        <div class="hidden items-center gap-3 md:flex">
            <div class="hidden text-right lg:block">
                <p class="text-sm font-medium text-[var(--text-main)]">{{ Auth::user()->name }}</p>
                <p class="text-xs text-[var(--text-muted)]">{{ Auth::user()->email }}</p>
            </div>

            <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-full border border-[var(--border-soft)] bg-[var(--bg-surface)] px-4 py-2 text-sm font-medium text-[var(--text-main)] shadow-sm transition hover:border-[var(--primary)] hover:text-[var(--text-strong)]">
                Profil
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-full bg-[var(--primary)] px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-[var(--primary-hover)]">
                    Keluar
                </button>
            </form>
        </div>

        <div class="flex items-center md:hidden">
            <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl border border-[var(--border-soft)] p-2 text-[var(--text-muted)] transition hover:bg-[var(--bg-surface)] hover:text-[var(--text-main)] focus:outline-none">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-[var(--border-main)] bg-[var(--bg-card)] md:hidden">
        <div class="space-y-1 px-4 py-4">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('laundry.index')" :active="request()->routeIs('laundry.*')">
                Laundry
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pembayaran.index')" :active="request()->routeIs('pembayaran.*')">
                Pembayaran
            </x-responsive-nav-link>
        </div>

        <div class="border-t border-[var(--border-main)] px-4 py-4">
            <div class="mb-3">
                <p class="text-sm font-medium text-[var(--text-main)]">{{ Auth::user()->name }}</p>
                <p class="text-xs text-[var(--text-muted)]">{{ Auth::user()->email }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-responsive-nav-link :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    Keluar
                </x-responsive-nav-link>
            </form>
        </div>
    </div>
</nav>
