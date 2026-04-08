<x-app-layout title="Edit Biaya Jasa">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">Master jasa</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Edit Biaya Jasa</h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-card as="section" class="p-4 sm:p-6">
                <form method="POST" action="{{ route('biaya-jasa.update', $jasa) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('modules.partials.jasa-form', [
                        'jasa' => $jasa,
                        'submitLabel' => 'Perbarui Jasa',
                    ])
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
