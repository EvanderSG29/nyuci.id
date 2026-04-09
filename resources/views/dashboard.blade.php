<x-app-layout title="Beranda">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">Dashboard statistik</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                {{ $toko?->nama_toko ?? 'Lengkapi profil toko Anda' }}
            </h2>
        </div>
    </x-slot>

    @include('partials.dashboard-analytics')
</x-app-layout>
