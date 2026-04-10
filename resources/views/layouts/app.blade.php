<!DOCTYPE html>
@php
    $appName = $appName ?? config('app.name', 'Nyuci.id');
    $pageTitle = trim($__env->yieldContent('title', $pageTitle ?? $title ?? $appName));
    $pageTitle = $pageTitle !== '' ? $pageTitle : $appName;
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

        <div class="min-h-screen bg-[var(--bg-main)] text-[var(--text-main)]">
            @include('layouts.sidebar')

            <flux:header container class="!border-b !border-[var(--border-main)] !bg-[var(--bg-card)] backdrop-blur">
                <div class="flex w-full items-center gap-3">
                    <flux:sidebar.toggle class="lg:hidden" />

                    <div class="min-w-0 lg:hidden">
                        <p class="truncate text-xs font-medium text-[var(--text-muted)]">
                            {{ Auth::user()->toko?->nama_toko ?? 'Laundry digital' }}
                        </p>
                        <p class="truncate text-sm font-semibold text-[var(--text-strong)]">
                            {{ $pageTitle }}
                        </p>
                    </div>

                    <div class="hidden min-w-0 flex-1 lg:flex lg:flex-col">
                        @isset($header)
                            {{ $header }}
                        @else
                            <div class="flex flex-col gap-1">
                                <p class="text-sm font-medium text-[var(--text-muted)]">
                                    {{ Auth::user()->toko?->nama_toko ?? 'Laundry digital' }}
                                </p>
                                <h1 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                                    {{ $pageTitle }}
                                </h1>
                            </div>
                        @endisset
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        @include('partials.notification-dropdown')
                    </div>
                </div>
            </flux:header>

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
