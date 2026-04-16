<x-app-layout title="Tambah Pembayaran">
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-sm font-medium text-[var(--text-muted)]">Transaksi pembayaran</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Tambah Pembayaran</h2>
            <p class="text-sm text-[var(--text-muted)]">Pilih order laundry dan simpan pembayaran dengan total biaya yang dihitung otomatis.</p>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-card>
                @include('pembayaran.partials.form', [
                    'action' => route('pembayaran.store'),
                    'method' => 'POST',
                    'payment' => null,
                    'selectedLaundryId' => $selectedLaundryId ?? null,
                    'selectedLaundry' => $selectedLaundry ?? null,
                    'mode' => 'create',
                    'submitLabel' => 'Simpan',
                ])
            </x-card>
        </div>
    </div>
</x-app-layout>
