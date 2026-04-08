<x-app-layout :title="$title">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">{{ $eyebrow }}</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $title }}</h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <x-card as="section" class="p-4 sm:p-6">
                <p class="text-sm font-semibold text-[var(--primary-ink)]">Struktur halaman siap</p>
                <h3 class="mt-2 text-xl font-semibold text-[var(--text-strong)]">{{ $title }} akan dibangun di fase berikutnya</h3>
                <p class="mt-3 text-sm leading-6 text-[var(--text-muted)]">{{ $description }}</p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard') }}" class="nyuci-btn-primary">
                        Kembali ke Beranda
                    </a>

                    @isset($actionRoute)
                        <a href="{{ route($actionRoute) }}" class="nyuci-btn-secondary">
                            {{ $actionLabel }}
                        </a>
                    @endisset
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
