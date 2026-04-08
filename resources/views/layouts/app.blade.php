<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php($pageTitle = trim($__env->yieldContent('title', $title ?? 'Nyuci.id')))
        <title>{{ $pageTitle === 'Nyuci.id' ? 'Nyuci.id' : $pageTitle . ' - Nyuci.id' }}</title>
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
        <div class="min-h-screen bg-zinc-50 text-[var(--text-main)] dark:bg-zinc-900">
            @include('layouts.sidebar')

            <flux:header container class="border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/90">
                <div class="flex w-full items-center gap-3">
                    <flux:sidebar.toggle class="lg:hidden" />

                    <div class="min-w-0 lg:hidden">
                        <p class="truncate text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            {{ Auth::user()->toko?->nama_toko ?? 'Laundry digital' }}
                        </p>
                        <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $pageTitle }}
                        </p>
                    </div>

                    <div class="hidden min-w-0 flex-1 lg:flex lg:flex-col">
                        @isset($header)
                            {{ $header }}
                        @else
                            <div class="flex flex-col gap-1">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    {{ Auth::user()->toko?->nama_toko ?? 'Laundry digital' }}
                                </p>
                                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                                    {{ $pageTitle }}
                                </h1>
                            </div>
                        @endisset
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <x-theme-toggle />
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

        @if (session('success') || session('warning'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    window.dispatchEvent(new CustomEvent('toast-show', {
                        detail: {
                            duration: 4500,
                            dataset: {
                                variant: @js(session('warning') ? 'warning' : 'success'),
                            },
                            slots: {
                                text: @js(session('warning') ?: session('success')),
                            },
                        },
                    }));
                });
            </script>
        @endif

        @livewireScripts
        @fluxScripts
    </body>
</html>
