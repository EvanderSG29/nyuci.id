<x-app-layout title="Tambah Pelanggan" navbar-eyebrow="Master pelanggan">
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
