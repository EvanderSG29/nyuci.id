<x-app-layout title="Laundry">
    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-end">
                <a href="{{ route('pembayaran.unpaid') }}" class="nyuci-btn-secondary">
                    Kelola Belum Bayar
                </a>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-card>
                    <p class="text-sm font-semibold text-[var(--text-muted)]">Total Order</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Semua transaksi laundry yang tercatat.</p>
                </x-card>

                <x-card>
                    <p class="text-sm font-semibold text-[var(--primary-ink)]">Belum Selesai</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['belum_selesai'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Order baru yang belum masuk proses.</p>
                </x-card>

                <x-card class="nyuci-summary-navy">
                    <p class="text-sm font-semibold text-[var(--summary-label)]">Proses</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['proses'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Sedang dikerjakan oleh tim laundry.</p>
                </x-card>

                <x-card>
                    <p class="text-sm font-semibold text-[var(--text-muted)]">Selesai</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['selesai'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Sudah siap diambil pelanggan.</p>
                </x-card>
            </section>

            <x-datatable-panel
                table-id="laundry-table"
                heading="Manage Laundry"
                :ajax-url="route('laundry.data')"
                :create-url="route('laundry.create')"
                :columns="['Pelanggan', 'Layanan', 'Qty', 'Tanggal Masuk', 'ETS', 'Status', 'Pembayaran', '']"
                :datatable-columns="[
                    ['data' => 'customer', 'name' => 'nama_klien'],
                    ['data' => 'service', 'name' => 'jasa'],
                    ['data' => 'qty_display', 'name' => 'qty'],
                    ['data' => 'received_at', 'name' => 'tanggal_dimulai'],
                    ['data' => 'due_at', 'name' => 'ets_selesai'],
                    ['data' => 'status_badge', 'name' => 'status'],
                    ['data' => 'payment_badge', 'name' => 'dibayar'],
                    ['data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
                ]"
                :order="[[3, 'desc']]"
                search-placeholder="Cari laundry..."
            >
                <x-slot:filters>
                    <x-datatable-select name="status" :options="$statusOptions" placeholder="Filter Status" />
                    <x-datatable-select name="dibayar" :options="$paymentOptions" placeholder="Filter Pembayaran" />
                    <x-datatable-select name="jasa_id" :options="$serviceOptions" placeholder="Filter Jasa" />
                </x-slot:filters>
            </x-datatable-panel>
        </div>
    </div>
</x-app-layout>
