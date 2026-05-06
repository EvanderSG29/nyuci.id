<x-app-layout title="Tambah Pembayaran" navbar-eyebrow="Transaksi pembayaran">
    <x-slot name="pageIntro">
        <div class="max-w-3xl">
            <x-card class="nyuci-page-intro-card p-5 sm:p-6">
                <div class="nyuci-page-intro-copy">
                    <p class="text-sm leading-6 text-[var(--text-muted)]">
                        Pilih order laundry dan simpan pembayaran dengan total biaya yang dihitung otomatis.
                    </p>
                </div>
            </x-card>
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
