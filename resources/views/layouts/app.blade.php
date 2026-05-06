<!DOCTYPE html>
@php
    $appName = $appName ?? config('app.name', 'Nyuci.id');
    $pageTitle = trim($__env->yieldContent('title', $pageTitle ?? $title ?? $appName));
    $pageTitle = $pageTitle !== '' ? $pageTitle : $appName;
    $pageNavbarEyebrow = trim((string) ($pageNavbarEyebrow ?? ''));
    $isDashboardRoute = request()->routeIs('dashboard');
    $routeName = request()->route()?->getName();
    $globalSearchEnabled = Auth::user()?->toko !== null;
    $routeSegmentLabels = [
        'settings' => 'Pengaturan',
    ];
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
                    'label' => $routeSegmentLabels[$segments[0]] ?? \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', $segments[0])),
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

    $hasPageIntro = isset($pageIntro) && ! $pageIntro->isEmpty();
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
                'store-identity-updated' => ['text' => 'Informasi toko berhasil diperbarui.', 'variant' => 'success'],
                'payment-qris-updated' => ['text' => 'Pengaturan QRIS berhasil diperbarui.', 'variant' => 'success'],
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

            <x-dashboard-navbar
                :page-title="$pageTitle"
                :page-navbar-eyebrow="$pageNavbarEyebrow"
                :search-enabled="$globalSearchEnabled"
                :is-dashboard="$isDashboardRoute"
            />

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

                        @if ($hasPageIntro)
                            <div class="nyuci-page-intro-shell" data-page-intro>
                                <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-4 sm:px-6 lg:px-8">
                                    {{ $pageIntro }}
                                </div>
                            </div>
                        @endif
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
