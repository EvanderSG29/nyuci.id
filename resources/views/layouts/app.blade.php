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
            $notifications = [
                [
                    'title' => 'Pembayaran menunggu',
                    'description' => '3 tagihan belum lunas hari ini.',
                    'href' => route('pembayaran.unpaid'),
                    'icon' => 'wallet',
                ],
                [
                    'title' => 'Laundry siap diambil',
                    'description' => 'Cek order yang sudah selesai diproses.',
                    'href' => route('laundry.index'),
                    'icon' => 'truck',
                ],
                [
                    'title' => 'Periksa pengaturan toko',
                    'description' => 'Identitas toko bisa dicek dari halaman pengaturan.',
                    'href' => route('pengaturan-toko.edit'),
                    'icon' => 'cog-6-tooth',
                ],
            ];
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
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="subtle" square aria-label="Notifikasi" class="relative">
                                <flux:icon.bell variant="outline" class="size-5" />
                                <span class="absolute right-2 top-2 size-2 rounded-full bg-[var(--primary)] ring-2 ring-[var(--bg-card)]"></span>
                            </flux:button>

                            <flux:menu class="w-[22rem] max-w-[calc(100vw-2rem)] !border-[var(--border-main)] !bg-[var(--bg-card)]">
                                <div class="px-2 py-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-[var(--text-strong)]">Notifikasi</p>
                                            <p class="mt-1 text-xs text-[var(--text-muted)]">Pembaruan terbaru dari toko Anda</p>
                                        </div>

                                        <span class="rounded-full bg-[var(--bg-surface)] px-2.5 py-1 text-[0.7rem] font-semibold text-[var(--text-muted)]">
                                            {{ count($notifications) }}
                                        </span>
                                    </div>
                                </div>

                                <flux:separator class="my-1" />

                                @foreach ($notifications as $notification)
                                    <flux:menu.item href="{{ $notification['href'] }}" wire:navigate :icon="$notification['icon']">
                                        <div class="flex flex-col items-start gap-0.5 py-0.5">
                                            <span class="text-sm font-medium leading-5 text-[var(--text-strong)]">
                                                {{ $notification['title'] }}
                                            </span>
                                            <span class="text-xs leading-5 text-[var(--text-muted)]">
                                                {{ $notification['description'] }}
                                            </span>
                                        </div>
                                    </flux:menu.item>
                                @endforeach

                                <flux:separator class="my-1" />

                                <flux:menu.item href="{{ route('pembayaran.unpaid') }}" wire:navigate icon="credit-card">
                                    Buka halaman pembayaran
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
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
