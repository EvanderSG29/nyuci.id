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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" data-theme="dark">
        <div class="min-h-screen bg-[var(--bg-main)] text-[var(--text-main)]">
            <div class="absolute inset-x-0 top-0 -z-10 h-80 bg-gradient-to-b from-[#0f172a] via-[#1e3a8a]/35 to-transparent"></div>
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-[#1e293b]/80 bg-[#0b1220]/80 backdrop-blur">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                    @if (session('warning'))
                        <div class="rounded-2xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-4 py-3 text-sm text-[var(--text-main)]">
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="rounded-2xl border border-[#3b82f6]/40 bg-[var(--bg-surface)] px-4 py-3 text-sm text-[var(--primary-soft)]">
                            {{ session('success') }}
                        </div>
                    @endif
                </div>

                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
    </body>
</html>
