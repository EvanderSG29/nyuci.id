<x-app-layout title="Tambah Biaya Jasa" navbar-eyebrow="Master jasa">
    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-card as="section" class="p-4 sm:p-6">
                <form method="POST" action="{{ route('biaya-jasa.store') }}" class="space-y-6">
                    @csrf

                    @include('modules.partials.jasa-form', [
                        'jasa' => null,
                        'submitLabel' => 'Simpan Jasa',
                    ])
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
