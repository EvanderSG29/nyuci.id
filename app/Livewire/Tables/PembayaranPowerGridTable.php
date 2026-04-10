<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\Jasa;
use App\Models\Pembayaran;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class PembayaranPowerGridTable extends PowerGridComponent
{
    use WithExport;

    public ?string $primaryKeyAlias = 'id';

    public string $primaryKey = 'id';

    public string $tableName = 'pembayaran-powergrid-table';

    public string $sortField = 'pembayarans.tgl_pembayaran';

    public bool $filtersOutside = true;

    public bool $multiSort = true;

    public bool $showExporting = true;

    /**
     * Replace this with your real Wire Elements modal alias once it exists.
     */
    public ?string $editModalComponent = 'pembayaran.edit-modal';

    protected function queryString(): array
    {
        return $this->powerGridQueryString();
    }

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('pembayaran-'.now()->format('Y-m-d-His'))
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV)
                ->striped()
                ->queues(6)
                ->onQueue('exports')
                ->onConnection((string) config('queue.default', 'database')),

            PowerGrid::header()
                ->showToggleColumns()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),

            PowerGrid::responsive()
                ->fixedColumns('customer_name', 'actions'),
        ];
    }

    public function header(): array
    {
        return [
            Button::add('bulk-mark-paid')
                ->slot('Tandai lunas (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700')
                ->dispatch('bulkMarkPaid.'.$this->tableName, []),

            Button::add('bulk-delete')
                ->slot('Hapus terpilih (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-50')
                ->dispatch('bulkDelete.'.$this->tableName, []),
        ];
    }

    public function datasource(): Builder
    {
        return Pembayaran::query()
            ->join('laundries', 'laundries.id', '=', 'pembayarans.laundry_id')
            ->leftJoin('kliens', 'kliens.id', '=', 'pembayarans.klien_id')
            ->leftJoin('jasas', 'jasas.id', '=', 'laundries.jasa_id')
            ->where('laundries.toko_id', $this->tokoId())
            ->select([
                'pembayarans.*',
                'kliens.nama_klien as customer_name',
                'kliens.no_hp_klien as customer_phone',
                'kliens.email_klien as customer_email',
                'jasas.nama_jasa as service_name',
                'jasas.satuan as service_unit',
                'laundries.qty as laundry_qty',
                'laundries.status as laundry_status',
            ])
            ->with(['klien', 'laundry.klien', 'laundry.jasa']);
    }

    public function relationSearch(): array
    {
        return [
            'klien' => [
                'nama_klien',
                'no_hp_klien',
                'email_klien',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('customer_name', fn (Pembayaran $row): string => (string) ($row->customer_name ?: $row->klien?->nama_klien ?: $row->laundry?->nama ?: '-'))
            ->add('customer_phone', fn (Pembayaran $row): string => (string) ($row->customer_phone ?: $row->klien?->no_hp_klien ?: $row->laundry?->no_hp ?: '-'))
            ->add('customer_card', function (Pembayaran $row): string {
                $name = e((string) ($row->customer_name ?: $row->klien?->nama_klien ?: $row->laundry?->nama ?: '-'));
                $phone = e((string) ($row->customer_phone ?: $row->klien?->no_hp_klien ?: $row->laundry?->no_hp ?: '-'));

                return sprintf(
                    '<a href="%s" class="block leading-tight"><span class="font-semibold text-sky-700 hover:underline">%s</span><span class="mt-1 block text-xs text-slate-500">%s</span></a>',
                    e(route('pembayaran.show', $row)),
                    $name,
                    $phone
                );
            })
            ->add('service_name', fn (Pembayaran $row): string => (string) ($row->service_name ?: $row->laundry?->jasa?->nama_jasa ?: $row->laundry?->jenis_jasa_label ?: '-'))
            ->add('service_summary', function (Pembayaran $row): string {
                $service = e((string) ($row->service_name ?: $row->laundry?->jasa?->nama_jasa ?: $row->laundry?->jenis_jasa_label ?: '-'));
                $unit = e((string) ($row->service_unit ?: $row->laundry?->satuan_label ?: '-'));

                return sprintf(
                    '<div class="leading-tight"><span class="font-medium text-slate-800">%s</span><span class="mt-1 block text-xs text-slate-500">%s</span></div>',
                    $service,
                    $unit
                );
            })
            ->add('metode_pembayaran')
            ->add('metode_pembayaran_label', fn (Pembayaran $row): string => $row->metode_pembayaran_label)
            ->add('status')
            ->add('status_label', fn (Pembayaran $row): string => $row->status_label)
            ->add('status_badge', function (Pembayaran $row): string {
                $classes = $row->status === 'sudah_bayar'
                    ? 'bg-emerald-100 text-emerald-700 ring-emerald-200'
                    : 'bg-amber-100 text-amber-700 ring-amber-200';

                return sprintf(
                    '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset %s">%s</span>',
                    $classes,
                    e($row->status_label)
                );
            })
            ->add('gateway_status')
            ->add('gateway_status_label', fn (Pembayaran $row): string => $row->gateway_status_label)
            ->add('gateway_status_badge', function (Pembayaran $row): string {
                $classes = match ($row->gateway_status_variant) {
                    'paid' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                    'pending' => 'bg-sky-100 text-sky-700 ring-sky-200',
                    'danger' => 'bg-rose-100 text-rose-700 ring-rose-200',
                    default => 'bg-slate-100 text-slate-600 ring-slate-200',
                };

                return sprintf(
                    '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset %s">%s</span>',
                    $classes,
                    e($row->gateway_status_label)
                );
            })
            ->add('gateway_qr_thumb', function (Pembayaran $row): string {
                if (! filled($row->gateway_qr_image)) {
                    return '<span class="text-xs text-slate-400">No QR</span>';
                }

                return sprintf(
                    '<img src="%s" alt="QR pembayaran %s" class="h-10 w-10 rounded-lg border border-slate-200 object-cover">',
                    e((string) $row->gateway_qr_image),
                    e((string) $row->id)
                );
            })
            ->add('gateway_checkout_link', function (Pembayaran $row): string {
                if (! filled($row->gateway_checkout_url)) {
                    return '<span class="text-xs text-slate-400">-</span>';
                }

                return sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-sky-700 hover:underline">Open</a>',
                    e((string) $row->gateway_checkout_url)
                );
            })
            ->add('tgl_pembayaran')
            ->add('tgl_pembayaran_formatted', fn (Pembayaran $row): string => $row->tgl_pembayaran?->translatedFormat('d M Y') ?? '-')
            ->add('total')
            ->add('resolved_total', fn (Pembayaran $row): int => $row->resolved_total)
            ->add('total_formatted', fn (Pembayaran $row): string => $this->formatCurrency($row->resolved_total))
            ->add('catatan', fn (Pembayaran $row): string => (string) ($row->catatan ?? ''))
            ->add('is_paid_toggle', fn (Pembayaran $row): bool => $row->status === 'sudah_bayar');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable()
                ->withCount('Jumlah transaksi', header: true, footer: true),

            Column::make('QR', 'gateway_qr_thumb', 'gateway_qr_image')
                ->visibleInExport(false),

            Column::make('Pelanggan', 'customer_card', 'customer_name')
                ->searchable()
                ->sortable()
                ->contentClasses('!whitespace-normal')
                ->visibleInExport(false)
                ->fixedOnResponsive(),

            Column::make('Pelanggan', 'customer_name')
                ->hidden(isHidden: true, isForceHidden: true),

            Column::make('Kontak', 'customer_phone')
                ->hidden(isHidden: true, isForceHidden: true),

            Column::make('Layanan', 'service_summary', 'service_name')
                ->searchable()
                ->sortable()
                ->contentClasses('!whitespace-normal')
                ->visibleInExport(false),

            Column::make('Layanan', 'service_name')
                ->hidden(isHidden: true, isForceHidden: true),

            Column::make('Metode', 'metode_pembayaran_label', 'metode_pembayaran')
                ->searchable()
                ->sortable(),

            Column::make('Tanggal Bayar', 'tgl_pembayaran_formatted', 'tgl_pembayaran')
                ->sortable(),

            Column::make('Status', 'status_badge', 'status')
                ->sortable()
                ->visibleInExport(false),

            Column::make('Status', 'status_label')
                ->hidden(isHidden: true, isForceHidden: true),

            Column::make('Gateway', 'gateway_status_badge', 'gateway_status')
                ->sortable()
                ->visibleInExport(false),

            Column::make('Gateway', 'gateway_status_label')
                ->hidden(isHidden: true, isForceHidden: true),

            Column::make('Checkout', 'gateway_checkout_link')
                ->visibleInExport(false),

            Column::make('Lunas', 'is_paid_toggle', 'status')
                ->toggleable(hasPermission: $this->canUpdateRows(), trueLabel: 'ya', falseLabel: 'tidak')
                ->sortable(),

            Column::make('Catatan', 'catatan')
                ->searchable()
                ->editOnClick(hasPermission: $this->canUpdateRows())
                ->placeholder('Klik untuk menambah catatan')
                ->contentClasses('!whitespace-normal'),

            Column::make('Total', 'total_formatted', 'total')
                ->sortable()
                ->searchable()
                ->withSum('Total omzet', header: true, footer: true)
                ->withAvg('Rata-rata', header: true, footer: true),

            Column::action('Aksi')
                ->fixedOnResponsive(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('customer_name')->placeholder('Nama pelanggan'),

            Filter::select('service_name', 'service_name')
                ->dataSource($this->serviceOptions())
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::select('metode_pembayaran', 'metode_pembayaran')
                ->dataSource($this->paymentMethodOptions())
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::select('status', 'status')
                ->dataSource($this->statusOptions())
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::boolean('gateway_status')
                ->label('Ada checkout QR', 'Tanpa checkout QR')
                ->builder(function (Builder $query, string $value): Builder {
                    return $query->when(
                        $value === 'true',
                        fn (Builder $builder): Builder => $builder->whereNotNull('pembayarans.gateway_token'),
                        fn (Builder $builder): Builder => $builder->whereNull('pembayarans.gateway_token'),
                    );
                }),

            Filter::boolean('total')
                ->label('Nominal >= Rp100.000', 'Nominal < Rp100.000')
                ->builder(function (Builder $query, string $value): Builder {
                    $operator = $value === 'true' ? '>=' : '<';

                    return $query->where('pembayarans.total', $operator, 100000);
                }),

            Filter::datepicker('tgl_pembayaran'),
        ];
    }

    public function actions(Pembayaran $row): array
    {
        return [
            Button::add('action-menu')
                ->tag('div')
                ->class('!p-0')
                ->slot(Blade::render(
                    '<x-powergrid.pembayaran-action-menu
                        :row-id="$rowId"
                        :view-url="$viewUrl"
                        :edit-url="$editUrl"
                        :checkout-url="$checkoutUrl"
                        :mark-paid-url="$markPaidUrl"
                        :delete-url="$deleteUrl"
                        :modal-component="$modalComponent"
                        :can-view="$canView"
                        :can-update="$canUpdate"
                        :can-delete="$canDelete"
                        :can-mark-paid="$canMarkPaid"
                    />',
                    [
                        'rowId' => (int) $row->id,
                        'viewUrl' => route('pembayaran.show', $row),
                        'editUrl' => route('pembayaran.edit', $row),
                        'checkoutUrl' => $row->gateway_checkout_url,
                        'markPaidUrl' => $row->status === 'belum_bayar' ? route('pembayaran.paid', $row) : null,
                        'deleteUrl' => route('pembayaran.destroy', $row),
                        'modalComponent' => $this->wireElementsModalIsAvailable() ? $this->editModalComponent : null,
                        'canView' => $this->canViewRow($row),
                        'canUpdate' => $this->canUpdateRow($row),
                        'canDelete' => $this->canDeleteRow($row),
                        'canMarkPaid' => $row->status === 'belum_bayar' && $this->canUpdateRow($row),
                    ]
                )),
        ];
    }

    public function actionRules(): array
    {
        return [
            Rule::button('action-menu')
                ->when(fn (Pembayaran $row): bool => ! $this->canViewRow($row))
                ->hide(),

            Rule::button('action-menu')
                ->when(fn (Pembayaran $row): bool => $row->status === 'sudah_bayar')
                ->setAttribute('title', 'Pembayaran ini sudah lunas'),

            Rule::checkbox()
                ->when(fn (Pembayaran $row): bool => $row->gateway_status === 'paid')
                ->hide(),

            Rule::rows()
                ->when(fn (Pembayaran $row): bool => $row->status === 'belum_bayar')
                ->setAttribute('class', 'bg-amber-50/60'),

            Rule::rows()
                ->when(fn (Pembayaran $row): bool => $row->gateway_status === 'paid')
                ->hideToggleable(),
        ];
    }

    public function summarizeFormat(): array
    {
        return [
            'id.{count}' => fn (int|float|string $value): string => 'Total data: '.Number::format((int) $value, locale: 'id'),
            'total.{sum,avg}' => fn (int|float|string $value): string => $this->formatCurrency((float) $value),
        ];
    }

    public function onUpdatedEditable(int|string $id, string $field, string $value): void
    {
        if ($field !== 'catatan') {
            return;
        }

        $validated = Validator::validate(
            ['catatan' => $value],
            ['catatan' => ['nullable', 'string', 'max:500']]
        );

        $pembayaran = $this->ownedPembayarans()
            ->whereKey((int) $id)
            ->firstOrFail();

        $pembayaran->update([
            'catatan' => filled(trim($validated['catatan'])) ? trim($validated['catatan']) : null,
        ]);
    }

    public function onUpdatedToggleable(int|string $id, string $field, bool|int|string $value): void
    {
        if (! in_array($field, ['is_paid_toggle', 'status'], true)) {
            return;
        }

        $pembayaran = $this->ownedPembayarans()
            ->whereKey((int) $id)
            ->firstOrFail();

        $isPaid = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $markAsPaid = $isPaid ?? in_array((string) $value, ['1', 'true', 'ya', 'yes'], true);

        $updates = [
            'status' => $markAsPaid ? 'sudah_bayar' : 'belum_bayar',
            'gateway_status' => $markAsPaid
                ? 'paid'
                : (filled($pembayaran->gateway_token) ? 'pending' : null),
            'gateway_paid_at' => $markAsPaid ? ($pembayaran->gateway_paid_at ?? Carbon::now()) : null,
        ];

        if ($markAsPaid && $pembayaran->tgl_pembayaran === null) {
            $updates['tgl_pembayaran'] = Carbon::today()->toDateString();
        }

        $pembayaran->update($updates);
    }

    #[On('bulkMarkPaid.{tableName}')]
    public function bulkMarkPaid(): void
    {
        foreach ($this->selectedPembayarans() as $pembayaran) {
            if ($pembayaran->status === 'sudah_bayar') {
                continue;
            }

            $pembayaran->update([
                'status' => 'sudah_bayar',
                'tgl_pembayaran' => $pembayaran->tgl_pembayaran ?? Carbon::today()->toDateString(),
                'gateway_status' => 'paid',
                'gateway_paid_at' => $pembayaran->gateway_paid_at ?? Carbon::now(),
            ]);
        }

        $this->checkboxValues = [];
    }

    #[On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        foreach ($this->selectedPembayarans() as $pembayaran) {
            if ($pembayaran->status === 'sudah_bayar') {
                continue;
            }

            $pembayaran->delete();
        }

        $this->checkboxValues = [];
    }

    public function noDataLabel(): string|View
    {
        return 'Belum ada data pembayaran yang cocok dengan pencarian atau filter aktif.';
    }

    private function ownedPembayarans(): Builder
    {
        return Pembayaran::query()
            ->whereHas('laundry', fn (Builder $builder): Builder => $builder->where('toko_id', $this->tokoId()));
    }

    /**
     * @return Collection<int, Pembayaran>
     */
    private function selectedPembayarans(): Collection
    {
        $ids = collect($this->checkboxValues ?? [])
            ->map(static fn (int|string $value): int => (int) $value)
            ->filter(static fn (int $value): bool => $value > 0)
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->ownedPembayarans()
            ->whereIn('id', $ids->all())
            ->get();
    }

    private function serviceOptions(): Collection
    {
        return Jasa::query()
            ->where('toko_id', $this->tokoId())
            ->orderBy('nama_jasa')
            ->get(['id', 'nama_jasa'])
            ->map(fn (Jasa $jasa): array => [
                'id' => $jasa->nama_jasa,
                'name' => $jasa->label,
            ]);
    }

    private function paymentMethodOptions(): Collection
    {
        return collect([
            ['id' => 'cash', 'name' => 'Cash'],
            ['id' => 'qris', 'name' => 'QRIS'],
            ['id' => 'transfer', 'name' => 'Transfer'],
            ['id' => 'ewallet', 'name' => 'E-Wallet'],
        ]);
    }

    private function statusOptions(): Collection
    {
        return collect([
            ['id' => 'belum_bayar', 'name' => 'Belum Bayar'],
            ['id' => 'sudah_bayar', 'name' => 'Sudah Bayar'],
        ]);
    }

    private function formatCurrency(int|float $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    private function tokoId(): int
    {
        return (int) auth()->user()?->toko?->id;
    }

    private function canViewRow(Pembayaran $row): bool
    {
        return (bool) auth()->user()?->can('view', $row);
    }

    private function canUpdateRow(Pembayaran $row): bool
    {
        return (bool) auth()->user()?->can('update', $row);
    }

    private function canDeleteRow(Pembayaran $row): bool
    {
        return (bool) auth()->user()?->can('delete', $row);
    }

    private function canUpdateRows(): bool
    {
        return auth()->check();
    }

    private function wireElementsModalIsAvailable(): bool
    {
        return class_exists(\LivewireUI\Modal\ModalComponent::class)
            || class_exists(\WireElements\Modal\ModalComponent::class);
    }
}
