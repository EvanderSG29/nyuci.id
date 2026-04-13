<x-app-layout title="Pelanggan">
    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <x-card>
                    <p class="text-sm font-semibold text-[var(--text-muted)]">Total Pelanggan</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Semua kontak aktif yang tersimpan di toko.</p>
                </x-card>

                <x-card>
                    <p class="text-sm font-semibold text-[var(--primary-ink)]">Aktif 30 Hari</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['aktif'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Pelanggan dengan order sejak {{ $activeSinceLabel }}.</p>
                </x-card>

                <x-card class="nyuci-summary-navy">
                    <p class="text-sm font-semibold text-[var(--summary-label)]">Perlu Follow Up</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['perlu_follow_up'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Masih punya pembayaran yang belum lunas.</p>
                </x-card>
            </section>

            <x-datatable-panel
                table-id="klien-table"
                heading="Manage Pelanggan"
                :ajax-url="route('pelanggan.data')"
                :create-url="route('pelanggan.create')"
                :columns="['Pelanggan', 'Kontak', 'Order', 'Belum Bayar', 'Terakhir Order', '']"
                :datatable-columns="[
                    ['data' => 'customer', 'name' => 'nama_klien'],
                    ['data' => 'contact', 'name' => 'no_hp_klien'],
                    ['data' => 'total_order_display', 'name' => 'total_order'],
                    ['data' => 'unpaid_display', 'name' => 'belum_bayar'],
                    ['data' => 'last_order_display', 'name' => 'terakhir_order'],
                    ['data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
                ]"
                :order="[[4, 'desc']]"
                search-placeholder="Cari pelanggan..."
            >
                <x-slot:filters>
                    <x-datatable-select name="status" :options="$statusOptions" placeholder="Filter Status" />
                </x-slot:filters>
            </x-datatable-panel>
        </div>
    </div>
</x-app-layout>
