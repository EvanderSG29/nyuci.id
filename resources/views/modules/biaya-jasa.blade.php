<x-app-layout title="Biaya Jasa">
    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <x-card>
                    <p class="text-sm font-semibold text-[var(--text-muted)]">Total Jasa</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['total'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Semua layanan aktif di toko ini.</p>
                </x-card>

                <x-card>
                    <p class="text-sm font-semibold text-[var(--primary-ink)]">Kiloan</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $summary['kiloan'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Layanan berbasis kilogram.</p>
                </x-card>

                <x-card class="nyuci-summary-navy">
                    <p class="text-sm font-semibold text-[var(--summary-label)]">Per Unit</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[var(--summary-value)]">{{ $summary['per_unit'] }}</p>
                    <p class="mt-2 text-sm text-[var(--text-muted)]">Layanan potong, pcs, dan satuan khusus.</p>
                </x-card>
            </section>

            <x-datatable-panel
                table-id="jasa-table"
                heading="Manage Jasa"
                :ajax-url="route('biaya-jasa.data')"
                :create-url="route('biaya-jasa.create')"
                :columns="['No', 'Nama Jasa', 'Satuan', 'Harga', 'Dipakai', '']"
                :datatable-columns="[
                    ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false],
                    ['data' => 'service_name', 'name' => 'nama_jasa'],
                    ['data' => 'unit_badge', 'name' => 'satuan'],
                    ['data' => 'price_display', 'name' => 'harga'],
                    ['data' => 'total_order_display', 'name' => 'total_order'],
                    ['data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
                ]"
                :order="[[1, 'asc']]"
                search-placeholder="Cari jasa..."
            >
                <x-slot:filters>
                    <x-datatable-select name="satuan" :options="$satuanOptions" placeholder="Filter Satuan" />
                </x-slot:filters>
            </x-datatable-panel>
        </div>
    </div>
</x-app-layout>
