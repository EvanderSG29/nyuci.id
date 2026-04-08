<x-app-layout title="Edit Pembayaran">
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-sm font-medium text-[var(--text-muted)]">Transaksi pembayaran</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Edit Pembayaran</h2>
            <p class="text-sm text-[var(--text-muted)]">Perbarui metode, tanggal, catatan, atau status pembayaran untuk order yang sama.</p>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-card>
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
        </div>
    </div>
</x-app-layout>
