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
    <body class="font-sans text-slate-900 antialiased" data-theme="light">
        <div class="min-h-screen bg-[#020617] px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto grid min-h-[calc(100vh-3rem)] max-w-6xl overflow-hidden rounded-[2rem] border border-[#1e293b] bg-[#f1f5f9] shadow-2xl shadow-black/45 lg:grid-cols-[0.95fr_1.05fr]">
                <div class="nyuci-gradient-brand relative hidden overflow-hidden px-8 py-10 text-white lg:flex lg:flex-col lg:justify-between">
                    <div class="absolute inset-0 -z-10">
                        <div class="absolute left-10 top-10 h-40 w-40 rounded-full bg-[#3b82f6]/20 blur-3xl"></div>
                        <div class="absolute bottom-10 right-10 h-52 w-52 rounded-full bg-[#93c5fd]/20 blur-3xl"></div>
                    </div>

                    <div>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                            <x-application-logo variant="white" class="h-11 w-11" />
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[#bfdbfe]">Nyuci.id</p>
                                <p class="text-xs text-slate-300">Sistem operasional laundry yang ringan</p>
                            </div>
                        </a>
                    </div>

                    <div class="max-w-md">
                        <p class="text-sm font-medium text-[#bfdbfe]">Masuk dan lanjutkan pekerjaan Anda.</p>
                        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white">
                            Tampilan tenang, alur kerja cepat, dan nyaman dipakai setiap hari.
                        </h1>
                        <p class="mt-5 text-sm leading-7 text-slate-300">
                            Nyuci.id dirancang untuk membantu toko laundry bekerja lebih rapi tanpa halaman yang berlebihan.
                            Tetap jelas saat dibuka di laptop maupun handphone.
                        </p>
                    </div>

                    <div class="grid gap-3 text-sm text-slate-300 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">Pencatatan laundry yang cepat dan mudah dibaca.</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">Pantau status pembayaran dan cucian yang belum diambil.</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">Responsif untuk admin desktop maupun penggunaan mobile.</div>
                    </div>
                </div>

                <div class="flex items-center bg-[#f1f5f9] px-5 py-8 sm:px-8 lg:px-12">
                    <div class="mx-auto w-full max-w-md">
                        <div class="mb-8 lg:hidden">
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                                <x-application-logo variant="black" class="h-11 w-11" />
                                <div>
                                    <p class="text-sm font-semibold text-[#0f172a]">Nyuci.id</p>
                                    <p class="text-xs text-slate-600">Laundry digital yang ringan</p>
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
    </body>
</html>
