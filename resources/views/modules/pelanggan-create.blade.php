<x-app-layout title="Tambah Pelanggan">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">Master pelanggan</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Tambah Pelanggan</h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-card as="section" class="p-4 sm:p-6">
                <form method="POST" action="{{ route('pelanggan.store') }}" class="space-y-6">
                    @csrf

                    @include('modules.partials.klien-form', [
                        'klien' => null,
                        'submitLabel' => 'Simpan Pelanggan',
                    ])
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
