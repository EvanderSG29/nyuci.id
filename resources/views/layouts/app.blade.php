<!DOCTYPE html>
@php
    $appName = $appName ?? config('app.name', 'Nyuci.id');
    $pageTitle = trim($__env->yieldContent('title', $pageTitle ?? $title ?? $appName));
    $pageTitle = $pageTitle !== '' ? $pageTitle : $appName;
    $storeName = Auth::user()->toko?->nama_toko ?? 'Laundry digital';
    $isDashboardRoute = request()->routeIs('dashboard');
    $routeName = request()->route()?->getName();
    $globalSearchEnabled = Auth::user()?->toko !== null;
    $normalizeBreadcrumbLabel = static function (?string $label): string {
        return \Illuminate\Support\Str::of((string) $label)
            ->replace(['-', '_'], ' ')
            ->squish()
            ->lower()
            ->toString();
    };
    $breadcrumbs = collect();

    if (! $isDashboardRoute && $routeName) {
        $segments = explode('.', $routeName);

        if (count($segments) > 1) {
            $parentRoute = $segments[0].'.index';

            if (Route::has($parentRoute) && $parentRoute !== 'dashboard.index' && $parentRoute !== $routeName) {
                $breadcrumbs->push([
                    'label' => \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', $segments[0])),
                    'url' => route($parentRoute),
                ]);
            }
        }

        if ($normalizeBreadcrumbLabel($pageTitle) !== '' && $normalizeBreadcrumbLabel($pageTitle) !== $normalizeBreadcrumbLabel(data_get($breadcrumbs->last(), 'label'))) {
            $breadcrumbs->push([
                'label' => $pageTitle,
                'url' => null,
            ]);
        }
    }

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

            $openNewTabUrl = session('open_new_tab_url');
            $openNewTabName = session('open_new_tab_name');
        @endphp

        <div
            x-data="dashboardChrome({
                isDashboard: @js($isDashboardRoute),
                searchEnabled: @js($globalSearchEnabled),
                searchUrl: @js(route('search.global')),
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
                            @include('layouts.partials.global-search', [
                                'searchEnabled' => $globalSearchEnabled,
                            ])
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
                <flux:header
                    container
                    x-bind:class="{ 'is-scrolled': scrolled }"
                    class="nyuci-app-header nyuci-dashboard-navbar nyuci-subpage-navbar !border-b"
                >
                    <div class="flex w-full flex-wrap items-start gap-3 lg:gap-4 xl:flex-nowrap">
                        <div class="order-1 flex min-w-0 flex-1 items-start gap-3 xl:w-[24rem] xl:min-w-[20rem] xl:max-w-[28rem] xl:flex-none">
                            <flux:sidebar.toggle class="nyuci-dashboard-icon-button mt-0.5 shrink-0 lg:hidden" />

                            <div class="nyuci-subpage-header min-w-0 flex-1">
                                <p class="nyuci-dashboard-nav-kicker text-[11px] font-semibold uppercase tracking-[0.24em]">
                                    {{ $storeName }}
                                </p>

                                <div class="nyuci-subpage-header-content mt-2">
                                    @isset($header)
                                        {{ $header }}
                                    @else
                                        <div class="nyuci-subpage-heading">
                                            <h1 class="text-lg font-semibold leading-tight tracking-tight text-[var(--text-strong)] sm:text-xl">
                                                {{ $pageTitle }}
                                            </h1>
                                        </div>
                                    @endisset
                                </div>
                            </div>
                        </div>

                        <div class="relative order-3 w-full xl:order-none xl:flex-1 xl:self-center xl:flex xl:justify-center" @click.outside="closeSearch()">
                            <div class="w-full xl:max-w-2xl">
                                @include('layouts.partials.global-search', [
                                    'searchEnabled' => $globalSearchEnabled,
                                ])
                            </div>
                        </div>

                        <div class="order-2 ml-auto flex items-center gap-2 xl:w-[14rem] xl:flex-none xl:justify-end">
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
            @endif

            <flux:main class="p-0">
                <div class="flex min-w-0 w-full flex-col">
                    @unless ($isDashboardRoute)
                        <div class="nyuci-subpage-breadcrumb-shell no-print" data-page-breadcrumbs>
                            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                                <div class="nyuci-subpage-breadcrumb-scroll">
                                    @hasSection('breadcrumbs')
                                        <nav class="nyuci-breadcrumbs" aria-label="Breadcrumb">
                                            @yield('breadcrumbs')
                                        </nav>
                                    @else
                                        <nav class="nyuci-breadcrumbs" aria-label="Breadcrumb">
                                            <a href="{{ route('dashboard') }}" class="nyuci-breadcrumb-item text-[var(--text-muted)] hover:text-[var(--text-strong)]">
                                                Beranda
                                            </a>

                                            @foreach ($breadcrumbs as $item)
                                                <span class="nyuci-breadcrumb-separator">/</span>

                                                @if ($item['url'])
                                                    <a href="{{ $item['url'] }}" class="nyuci-breadcrumb-item text-[var(--text-muted)] hover:text-[var(--text-strong)]">
                                                        {{ $item['label'] }}
                                                    </a>
                                                @else
                                                    <span class="nyuci-breadcrumb-item truncate text-[var(--text-strong)]">
                                                        {{ $item['label'] }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </nav>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endunless

                    <div class="min-w-0">
                        @hasSection('content')
                            @yield('content')
                        @else
                            {{ $slot ?? '' }}
                        @endif
                    </div>
                </div>
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

        @if ($openNewTabUrl)
            <script>
                queueMicrotask(() => {
                    const checkoutUrl = @js($openNewTabUrl);
                    const checkoutTabName = @js($openNewTabName ?: config('payment_gateway.checkout_window_name', 'nyuci-qris-checkout'));
                    const checkoutWindow = window.open(checkoutUrl, checkoutTabName, 'noopener');

                    checkoutWindow?.focus();
                });
            </script>
        @endif
    </body>
</html>
