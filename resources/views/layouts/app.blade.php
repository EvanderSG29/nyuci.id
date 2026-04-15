<!DOCTYPE html>
@php
    $appName = $appName ?? config('app.name', 'Nyuci.id');
    $pageTitle = trim($__env->yieldContent('title', $pageTitle ?? $title ?? $appName));
    $pageTitle = $pageTitle !== '' ? $pageTitle : $appName;
    $storeName = Auth::user()->toko?->nama_toko ?? 'Laundry digital';
    $isDashboardRoute = request()->routeIs('dashboard');
    $dashboardSearchEnabled = $isDashboardRoute && Auth::user()?->toko !== null;
    $userInitials = str(Auth::user()?->name ?? 'Nyuci')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn ($segment) => str($segment)->substr(0, 1)->upper()->toString())
        ->implode('');
    $userInitials = $userInitials !== '' ? $userInitials : 'NY';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pageTitle === $appName ? $appName : $pageTitle . ' - ' . $appName }}</title>
        <link rel="icon" type="image/x-icon" href="{{ url('/storage/icon_blue.ico') }}">
        <link rel="shortcut icon" href="{{ url('/storage/icon_blue.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @include('layouts.partials.theme-init')
        @livewireStyles
        @fluxAppearance
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="themeManager()" :class="{ 'dark theme-dark': resolvedTheme === 'dark' }" class="font-sans antialiased">
        @php
            $statusToToast = [
                'profile-updated' => ['text' => 'Profil akun berhasil diperbarui.', 'variant' => 'success'],
                'password-updated' => ['text' => 'Kata sandi berhasil diperbarui.', 'variant' => 'success'],
                'store-settings-updated' => ['text' => 'Pengaturan toko berhasil diperbarui.', 'variant' => 'success'],
                'dashboard-chart-defaults-updated' => ['text' => 'Default chart dashboard berhasil diperbarui.', 'variant' => 'success'],
                'dashboard-chart-overrides-updated' => ['text' => 'Preferensi chart pribadi berhasil diperbarui.', 'variant' => 'success'],
                'dashboard-chart-default-reset' => ['text' => 'Slot chart berhasil dikembalikan ke default.', 'variant' => 'success'],
                'dashboard-chart-override-deleted' => ['text' => 'Override chart berhasil dihapus.', 'variant' => 'success'],
                'verification-link-sent' => ['text' => 'Tautan verifikasi baru berhasil dikirim.', 'variant' => 'success'],
            ];

            $flashToasts = [];

            if (session('success')) {
                $flashToasts[] = ['text' => session('success'), 'variant' => 'success', 'duration' => 5000];
            }

            if (session('warning')) {
                $flashToasts[] = ['text' => session('warning'), 'variant' => 'warning', 'duration' => 5000];
            }

            if (session('status') && isset($statusToToast[session('status')])) {
                $flashToasts[] = [...$statusToToast[session('status')], 'duration' => 5000];
            }
        @endphp

        <div
            x-data="dashboardChrome({
                isDashboard: @js($isDashboardRoute),
                searchEnabled: @js($dashboardSearchEnabled),
                searchUrl: @js($isDashboardRoute ? route('search.global') : ''),
            })"
            @keydown.escape.window="closeSearch()"
            class="min-h-screen bg-[var(--bg-main)] text-[var(--text-main)] {{ $isDashboardRoute ? 'nyuci-dashboard-shell' : '' }}"
        >
            @include('layouts.sidebar')

            @if ($isDashboardRoute)
                <flux:header
                    container
                    x-bind:class="{ 'is-scrolled': scrolled }"
                    class="nyuci-app-header nyuci-dashboard-navbar !border-b"
                >
                    <div class="flex w-full items-center gap-3 lg:gap-4">
                        <flux:sidebar.toggle class="nyuci-dashboard-icon-button lg:hidden" />

                        <div class="hidden min-w-0 shrink-0 lg:block">
                            <p class="nyuci-dashboard-nav-kicker text-[11px] font-semibold uppercase tracking-[0.24em]">
                                Dashboard
                            </p>
                            <p class="nyuci-dashboard-nav-store mt-1 truncate text-sm font-semibold">
                                {{ $storeName }}
                            </p>
                        </div>

                        <div class="relative w-full max-w-2xl flex-1" @click.outside="closeSearch()">
                            <label class="nyuci-dashboard-search">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M21 21l-4.2-4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                    <circle cx="11" cy="11" r="6.2" stroke="currentColor" stroke-width="1.8" />
                                </svg>
                                <input
                                    type="search"
                                    x-model.debounce.250ms="query"
                                    @focus="openSearch()"
                                    placeholder="{{ $dashboardSearchEnabled ? 'Cari laundry, pelanggan, pembayaran...' : 'Lengkapi toko untuk mengaktifkan pencarian dashboard' }}"
                                    autocomplete="off"
                                    @disabled(! $dashboardSearchEnabled)
                                >
                                <button
                                    type="button"
                                    x-cloak
                                    x-show="query !== ''"
                                    @click.prevent="clearSearch()"
                                    class="nyuci-dashboard-search-clear"
                                    aria-label="Hapus pencarian"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                    </svg>
                                </button>
                            </label>

                            @unless ($dashboardSearchEnabled)
                                <p class="nyuci-dashboard-nav-helper mt-2 hidden text-xs sm:block">
                                    Search aktif setelah profil toko dilengkapi.
                                </p>
                            @endunless

                            <div
                                x-cloak
                                x-show="shouldShowSearch()"
                                x-transition.opacity.scale.origin.top
                                class="nyuci-dashboard-search-panel"
                            >
                                <div x-show="loading" class="nyuci-dashboard-search-state">
                                    Mencari data terbaru...
                                </div>

                                <div x-show="error" x-text="error" class="nyuci-dashboard-search-state is-error"></div>

                                <div x-show="!loading && !error && !hasSearchResults()" class="nyuci-dashboard-search-state">
                                    Tidak ada hasil untuk pencarian ini.
                                </div>

                                <template x-for="group in results.groups" :key="group.key">
                                    <section class="nyuci-dashboard-search-group">
                                        <div class="flex items-center justify-between gap-3 px-4 py-3">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]" x-text="group.label"></p>
                                            </div>

                                            <a
                                                class="text-xs font-semibold text-[var(--primary)] transition hover:text-[var(--primary-hover)]"
                                                x-bind:href="group.index_url"
                                            >
                                                Lihat semua
                                            </a>
                                        </div>

                                        <div class="px-2 pb-2">
                                            <template x-for="item in group.items" :key="item.url">
                                                <a x-bind:href="item.url" class="nyuci-dashboard-search-item">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-[var(--text-strong)]" x-text="item.title"></p>
                                                        <p class="mt-1 truncate text-xs text-[var(--text-main)]" x-text="item.subtitle"></p>
                                                    </div>

                                                    <p class="shrink-0 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-[var(--text-muted)]" x-text="item.meta"></p>
                                                </a>
                                            </template>
                                        </div>
                                    </section>
                                </template>
                            </div>
                        </div>

                        <div class="ml-auto flex items-center gap-2">
                            @include('partials.notification-dropdown')

                            <a
                                href="{{ route('profile.edit') }}"
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
            @else
                <flux:header container class="nyuci-app-header !border-b !border-[var(--border-main)] !bg-[var(--bg-card)]">
                    <div class="flex w-full items-center gap-3 sm:gap-4">
                        <flux:sidebar.toggle class="lg:hidden" />

                        <div class="min-w-0 flex-1 lg:hidden">
                            <p class="truncate text-sm font-semibold text-[var(--text-strong)]">
                                {{ $pageTitle }}
                            </p>
                        </div>

                        <div class="hidden min-w-0 flex-1 lg:block">
                            @php
                                $breadcrumbs = collect();
                                $routeName = request()->route()?->getName();

                                if ($routeName) {
                                    $segments = explode('.', $routeName);

                                    if (count($segments) > 1) {
                                        $parentRoute = $segments[0] . '.index';

                                        if (Route::has($parentRoute) && $parentRoute !== 'dashboard.index') {
                                            $breadcrumbs->push([
                                                'label' => \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', $segments[0])),
                                                'url' => route($parentRoute),
                                            ]);
                                        }
                                    }
                                }
                            @endphp

                            @isset($header)
                                {{ $header }}
                            @else
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                        {{ $storeName }}
                                    </p>
                                    <h1 class="mt-2 truncate text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                                        {{ $pageTitle }}
                                    </h1>
                                </div>
                            @endisset

                            @hasSection('breadcrumbs')
                                <nav class="nyuci-breadcrumbs mt-3">
                                    @yield('breadcrumbs')
                                </nav>
                            @else
                                <nav class="nyuci-breadcrumbs mt-3">
                                    <a href="{{ route('dashboard') }}" class="nyuci-breadcrumb-item text-[var(--text-muted)] hover:text-[var(--text-strong)]">
                                        Beranda
                                    </a>

                                    @foreach ($breadcrumbs as $item)
                                        <span class="nyuci-breadcrumb-separator">/</span>
                                        <a href="{{ $item['url'] }}" class="nyuci-breadcrumb-item text-[var(--text-muted)] hover:text-[var(--text-strong)]">
                                            {{ $item['label'] }}
                                        </a>
                                    @endforeach

                                    <span class="nyuci-breadcrumb-separator">/</span>
                                    <span class="nyuci-breadcrumb-item truncate text-[var(--text-strong)]">
                                        {{ $pageTitle }}
                                    </span>
                                </nav>
                            @endif
                        </div>

                        <div class="ml-auto flex items-center gap-2">
                            @include('partials.notification-dropdown')
                        </div>
                    </div>
                </flux:header>
            @endif

            <flux:main class="p-0">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </flux:main>
        </div>

        @persist('app-toast')
            <div class="fixed right-4 top-4 z-[90] sm:right-6 sm:top-6">
                <flux:toast position="top end" />
            </div>
        @endpersist

        @livewireScripts
        @fluxScripts

        @if ($flashToasts !== [])
            <script>
                queueMicrotask(() => {
                    const toasts = @js($flashToasts);

                    toasts.forEach((toast, index) => {
                        window.setTimeout(() => {
                            window.Flux?.toast(toast);
                        }, index * 120);
                    });
                });
            </script>
        @endif
    </body>
</html>
