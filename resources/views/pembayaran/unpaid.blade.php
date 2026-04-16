<x-app-layout title="Kelola Belum Bayar">
    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <x-card>
                    <p class="text-sm font-semibold text-[var(--text-muted)]">Total Belum Bayar</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Semua order yang menunggu pembayaran.</p>
                </x-card>

                <x-card>
                    <p class="text-sm font-semibold text-[var(--primary-ink)]">Belum Selesai</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['belum_selesai'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Order yang masih berjalan dan belum dibayar.</p>
                </x-card>

                <x-card class="nyuci-summary-navy">
                    <p class="text-sm font-semibold text-[var(--summary-label)]">Selesai</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['selesai'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Sudah selesai, tinggal tuntaskan pembayaran.</p>
                </x-card>
            </section>

            <x-datatable-panel
                table-id="unpaid-laundry-table"
                heading="Manage Belum Bayar"
                :ajax-url="route('pembayaran.unpaid.data')"
                :create-url="route('pembayaran.create')"
                :columns="['Pelanggan', 'Layanan', 'Tanggal Masuk', 'Estimasi', 'Status', 'Total', '']"
                :datatable-columns="[
                    ['data' => 'customer', 'name' => 'nama'],
                    ['data' => 'service', 'name' => 'jenis_jasa'],
                    ['data' => 'received_at', 'name' => 'tanggal'],
                    ['data' => 'due_at', 'name' => 'estimasi_selesai'],
                    ['data' => 'status_badge', 'name' => 'status'],
                    ['data' => 'total_display', 'name' => 'total'],
                    ['data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
                ]"
                :order="[[2, 'desc']]"
                search-placeholder="Cari order belum bayar..."
            >
                <x-slot:filters>
                    <x-datatable-select name="status" :options="$statusOptions" placeholder="Filter Status" />
                </x-slot:filters>
            </x-datatable-panel>
        </div>
    </div>
</x-app-layout>
