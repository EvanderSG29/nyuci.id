<x-app-layout title="Edit Laundry">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">Pemeliharaan order</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Edit Laundry</h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card as="section" class="p-4 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[var(--text-strong)]">{{ $laundry->klien?->nama_klien ?? $laundry->nama }}</p>
                        <p class="mt-1 text-sm text-[var(--text-muted)]">{{ $laundry->klien?->no_hp_klien ?? $laundry->no_hp }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <x-status-badge :variant="$laundry->status === 'selesai' ? 'success' : ($laundry->status === 'proses' ? 'paid' : 'pending')">
                            {{ $laundry->status_label }}
                        </x-status-badge>
                        <x-status-badge variant="default">
                            {{ $laundry->satuan_label }}
                        </x-status-badge>
                    </div>
                </div>
            </x-card>

            <x-card as="section" class="p-4 sm:p-6">
                <div class="mb-6">
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Perbarui Detail Order</p>
                    <p class="mt-1 text-sm text-[var(--text-muted)]">Gunakan aksi "Ubah Status" di tabel Laundry untuk memperbarui progres dan tanggal selesai aktual.</p>
                </div>

                <form method="POST" action="{{ route('laundry.update', $laundry) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('laundry.partials.form', [
                        'laundry' => $laundry,
                        'submitLabel' => 'Perbarui Laundry',
                    ])
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
