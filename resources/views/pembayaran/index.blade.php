<x-app-layout title="Pembayaran">
    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-end">
                <a href="{{ route('pembayaran.unpaid') }}" class="nyuci-btn-secondary">
                    Kelola Belum Bayar
                </a>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <x-card>
                    <p class="text-sm font-semibold text-[var(--text-muted)]">Total Pembayaran</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Semua transaksi yang sudah dibuat.</p>
                </x-card>

                <x-card>
                    <p class="text-sm font-semibold text-[var(--primary-ink)]">Sudah Bayar</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['sudah_bayar'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Order yang sudah dinyatakan lunas.</p>
                </x-card>

                <x-card class="nyuci-summary-navy">
                    <p class="text-sm font-semibold text-[var(--summary-label)]">Belum Bayar</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['belum_bayar'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Perlu tindak lanjut pembayaran.</p>
                </x-card>
            </section>

            <x-datatable-panel
                table-id="pembayaran-table"
                heading="Manage Pembayaran"
                :ajax-url="route('pembayaran.data')"
                :create-url="route('pembayaran.create')"
                :columns="['Pelanggan', 'Layanan', 'Metode', 'Tanggal', 'Status', 'Total', '']"
                :datatable-columns="[
                    ['data' => 'customer', 'name' => 'nama_klien'],
                    ['data' => 'service', 'name' => 'service'],
                    ['data' => 'method_display', 'name' => 'metode_pembayaran'],
                    ['data' => 'date_display', 'name' => 'tgl_pembayaran'],
                    ['data' => 'status_badge', 'name' => 'status'],
                    ['data' => 'total_display', 'name' => 'total'],
                    ['data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
                ]"
                :order="[[3, 'desc']]"
                search-placeholder="Cari pembayaran..."
            >
                <x-slot:filters>
                    <x-datatable-select name="status" :options="$statusOptions" placeholder="Filter Status" />
                    <x-datatable-select name="metode_pembayaran" :options="$paymentMethodOptions" placeholder="Filter Metode" />
                </x-slot:filters>
            </x-datatable-panel>
        </div>
    </div>
</x-app-layout>
