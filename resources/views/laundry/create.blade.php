<x-app-layout title="Tambah Laundry" navbar-eyebrow="Input order baru">
    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-card as="section" class="p-4 sm:p-6">
                <div class="mb-6">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Detail Order</p>
                    <p class="mt-1 text-sm text-[var(--text-muted)]">Pilih pelanggan, jasa, qty, dan target selesai untuk order baru.</p>
                </div>

                <form method="POST" action="{{ route('laundry.store') }}" class="space-y-6">
                    @csrf

                    @include('laundry.partials.form', [
                        'laundry' => null,
                        'submitLabel' => 'Simpan Laundry',
                    ])
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
