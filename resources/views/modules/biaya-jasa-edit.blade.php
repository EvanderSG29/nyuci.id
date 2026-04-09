<x-app-layout title="Edit Biaya Jasa">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">Master jasa</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Edit Biaya Jasa</h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
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

            <x-delete-resource-card
                :action="route('biaya-jasa.destroy', $jasa)"
                modal-name="confirm-jasa-deletion"
                title="Hapus biaya jasa ini"
                description="Jasa yang sudah dipakai oleh laundry tidak bisa dihapus. Jika masih digunakan, sistem akan menolak penghapusan."
                trigger-label="Hapus Jasa"
                modal-title="Hapus data jasa?"
                modal-description="Periksa kembali apakah jasa ini benar-benar tidak dipakai lagi. Setelah dihapus, data ini tidak bisa dipulihkan dari halaman ini."
                confirm-label="Ya, hapus"
            />
        </div>
    </div>
</x-app-layout>
