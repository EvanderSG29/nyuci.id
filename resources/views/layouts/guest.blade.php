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
            $flashToasts = [];

            if (session('success')) {
                $flashToasts[] = ['text' => session('success'), 'variant' => 'success', 'duration' => 5000];
            }

            if (session('warning')) {
                $flashToasts[] = ['text' => session('warning'), 'variant' => 'warning', 'duration' => 5000];
            }
        @endphp

        <div class="nyuci-landing-shell min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div class="nyuci-auth-panel mx-auto grid min-h-[calc(100vh-3rem)] max-w-6xl overflow-hidden rounded-[2rem] lg:grid-cols-[0.95fr_1.05fr]">
                <div class="nyuci-gradient-brand relative hidden overflow-hidden px-8 py-10 text-white lg:flex lg:flex-col lg:justify-between">
                    <div class="absolute inset-0 -z-10">
                        <div class="nyuci-blob-primary absolute left-10 top-10 h-40 w-40 rounded-full blur-3xl"></div>
                        <div class="nyuci-blob-soft absolute bottom-10 right-10 h-52 w-52 rounded-full blur-3xl"></div>
                    </div>

                    <div>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                            <x-application-logo variant="white" class="h-11 w-11" />
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-white/80">Nyuci.id</p>
                                <p class="text-xs text-white/70">Sistem operasional laundry yang ringan</p>
                            </div>
                        </a>
                    </div>

                    <div class="max-w-md">
                        <p class="text-sm font-medium text-white/80">Masuk dan lanjutkan pekerjaan Anda.</p>
                        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white">
                            Tampilan tenang, alur kerja cepat, dan nyaman dipakai setiap hari.
                        </h1>
                        <p class="mt-5 text-sm leading-7 text-white/70">
                            Nyuci.id dirancang untuk membantu toko laundry bekerja lebih rapi tanpa halaman yang berlebihan.
                            Tetap jelas saat dibuka di laptop maupun handphone.
                        </p>
                    </div>

                    <div class="grid gap-3 text-sm text-white/70 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">Pencatatan laundry yang cepat dan mudah dibaca.</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">Pantau status pembayaran dan cucian yang belum diambil.</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">Responsif untuk admin desktop maupun penggunaan mobile.</div>
                    </div>
                </div>

                <div class="flex items-center bg-[var(--bg-card)] px-5 py-8 sm:px-8 lg:px-12">
                    <div class="mx-auto w-full max-w-md">
                        <div class="mb-8 lg:hidden">
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                                <span class="nyuci-logo-light">
                                    <x-application-logo variant="black" class="h-11 w-11" />
                                </span>
                                <span class="nyuci-logo-dark">
                                    <x-application-logo variant="white" class="h-11 w-11" />
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-[var(--text-strong)]">Nyuci.id</p>
                                    <p class="text-xs text-[var(--text-muted)]">Laundry digital yang ringan</p>
                                </div>
                            </a>
                        </div>

                        @hasSection('content')
                            @yield('content')
                        @else
                            {{ $slot ?? '' }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed right-4 top-4 z-[90] sm:right-6 sm:top-6">
            <flux:toast position="top end" />
        </div>

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
