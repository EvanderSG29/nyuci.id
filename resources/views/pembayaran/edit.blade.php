<x-app-layout title="Edit Pembayaran" navbar-eyebrow="Transaksi pembayaran">
    <x-slot name="pageIntro">
        <div class="max-w-3xl">
            <x-card class="nyuci-page-intro-card p-5 sm:p-6">
                <div class="nyuci-page-intro-copy">
                    <p class="text-sm leading-6 text-[var(--text-muted)]">
                        Perbarui metode, tanggal, catatan, atau status pembayaran untuk order yang sama.
                    </p>
                </div>
            </x-card>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card class="p-4 sm:p-6">
                @include('pembayaran.partials.form', [
                    'action' => route('pembayaran.update', $pembayaran),
                    'method' => 'PUT',
                    'payment' => $pembayaran->loadMissing('laundry'),
                    'selectedLaundryId' => $pembayaran->laundry_id,
                    'selectedLaundry' => $pembayaran->laundry,
                    'mode' => 'edit',
                    'submitLabel' => 'Perbarui',
                ])
            </x-card>

            <x-delete-resource-card
                :action="route('pembayaran.destroy', $pembayaran)"
                modal-name="confirm-pembayaran-deletion"
                title="Hapus pembayaran ini"
                description="Gunakan opsi ini jika data pembayaran salah atau perlu dibatalkan. Penghapusan akan menghapus transaksi ini dari sistem."
                trigger-label="Hapus Pembayaran"
                modal-title="Hapus data pembayaran?"
                modal-description="Tindakan ini bersifat permanen. Pastikan data pembayaran ini memang perlu dihapus sebelum Anda melanjutkan."
                confirm-label="Ya, hapus"
            />
        </div>
    </div>
</x-app-layout>
