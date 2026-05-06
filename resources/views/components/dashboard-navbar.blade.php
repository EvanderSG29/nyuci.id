@props([
    'pageNavbarEyebrow' => '',
    'pageTitle' => '',
    'searchEnabled' => false,
    'isDashboard' => false,
])

@php
    $isDashboard = filter_var($isDashboard, FILTER_VALIDATE_BOOL);
    $storeName = Auth::user()?->toko?->nama_toko ?? 'Laundry digital';
    $userInitials = str(Auth::user()?->name ?? 'Nyuci')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn ($segment) => str($segment)->substr(0, 1)->upper()->toString())
        ->implode('');
    $userInitials = $userInitials !== '' ? $userInitials : 'NY';
@endphp

<flux:header
    container
    x-bind:class="{ 'is-scrolled': scrolled }"
    class="nyuci-app-header nyuci-dashboard-navbar {{ $isDashboard ? '!border-b' : 'nyuci-subpage-navbar !border-b' }}"
>
    <div class="flex w-full {{ $isDashboard ? 'items-center' : 'flex-wrap items-start' }} gap-3 lg:gap-4 {{ $isDashboard ? '' : 'xl:flex-nowrap' }}">
        @if ($isDashboard)
            <div class="hidden min-w-0 shrink-0 lg:block">
                <p class="nyuci-dashboard-nav-kicker text-[11px] font-semibold uppercase tracking-[0.24em]">
                    Dashboard
                </p>
                <p class="nyuci-dashboard-nav-store mt-1 truncate text-sm font-semibold">
                    {{ $storeName }}
                </p>
            </div>
        @else
            <div class="order-1 flex min-w-0 flex-1 items-start gap-3 xl:w-[24rem] xl:min-w-[20rem] xl:max-w-[28rem] xl:flex-none">
                <flux:sidebar.toggle class="nyuci-dashboard-icon-button mt-0.5 shrink-0 lg:hidden" />

                <div class="nyuci-subpage-header min-w-0 flex-1">
                    <div class="nyuci-subpage-heading">
                        @if ($pageNavbarEyebrow !== '')
                            <p class="nyuci-subpage-eyebrow nyuci-dashboard-nav-kicker text-[11px] font-semibold uppercase tracking-[0.24em]">
                                {{ $pageNavbarEyebrow }}
                            </p>
                        @endif

                        <h1 class="nyuci-subpage-title text-[var(--text-strong)]">
                            {{ $pageTitle }}
                        </h1>
                    </div>
                </div>
            </div>
        @endif

        <div class="relative {{ $isDashboard ? 'w-full max-w-2xl flex-1' : 'order-3 w-full xl:order-none xl:flex-1 xl:self-center xl:flex xl:justify-center' }}" @click.outside="closeSearch()">
            <div class="{{ $isDashboard ? '' : 'w-full xl:max-w-2xl' }}">
                @include('layouts.partials.global-search', [
                    'searchEnabled' => $searchEnabled,
                ])
            </div>
        </div>

        <div class="{{ $isDashboard ? 'ml-auto flex items-center gap-2' : 'order-2 ml-auto flex items-center gap-2 xl:w-[14rem] xl:flex-none xl:justify-end' }}">
            @include('partials.notification-dropdown')

            <a
                href="{{ route('settings.profile') }}"
                wire:navigate
                class="nyuci-dashboard-icon-button inline-flex size-11 items-center justify-center rounded-2xl"
                aria-label="Buka pengaturan"
            >
                <flux:icon.cog-6-tooth variant="outline" class="size-5" />
            </a>

            <a
                href="{{ route('settings.profile') }}"
                wire:navigate
                class="nyuci-dashboard-profile"
                aria-label="Buka profil"
            >
                <span class="nyuci-dashboard-profile-avatar">{{ $userInitials }}</span>
                <span class="hidden min-w-0 text-left sm:block">
                    <span class="nyuci-dashboard-nav-profile-name block truncate text-sm font-semibold">{{ Auth::user()->name }}</span>
                    <span class="nyuci-dashboard-nav-profile-meta block truncate text-[0.72rem]">Profil Saya</span>
                </span>
            </a>
        </div>
    </div>
</flux:header>
