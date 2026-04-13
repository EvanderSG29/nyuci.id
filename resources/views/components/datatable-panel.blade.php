@props([
    'tableId',
    'heading',
    'ajaxUrl',
    'columns' => [],
    'datatableColumns' => [],
    'createUrl' => null,
    'createLabel' => 'New',
    'order' => [],
    'searchPlaceholder' => 'Search...',
])

@php
    $detailFlyoutName = "{$tableId}-detail";
    $config = [
        'tableId' => $tableId,
        'ajaxUrl' => $ajaxUrl,
        'columns' => array_values($datatableColumns),
        'detailFlyoutName' => $detailFlyoutName,
        'order' => array_values($order),
        'searchPlaceholder' => $searchPlaceholder,
    ];
@endphp

<section
    class="overflow-hidden rounded-[1.75rem] border border-[var(--border-main)] bg-[var(--bg-card)] shadow-sm"
    data-nyuci-datatable='@json($config)'
    data-dt-flyout-name="{{ $detailFlyoutName }}"
>
    <div class="border-b border-[var(--border-main)] px-5 py-4">
        <h2 class="text-base font-semibold text-[var(--text-strong)]">{{ $heading }}</h2>
    </div>

    <div class="space-y-4 p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-3">
            @if ($createUrl)
                <a href="{{ $createUrl }}" class="nyuci-toolbar-button nyuci-toolbar-button-primary">
                    {{ $createLabel }}
                </a>
            @endif

            <div class="nyuci-toolbar-group">
                <button type="button" class="nyuci-toolbar-button nyuci-toolbar-button-muted" data-dt-action="copy" title="Copy">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M9 9h8v10H9z" stroke="currentColor" stroke-width="1.7" />
                        <path d="M7 15H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.7" />
                    </svg>
                </button>

                <button type="button" class="nyuci-toolbar-button nyuci-toolbar-button-muted" data-dt-action="csv" title="CSV">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 8h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        <path d="M8 12h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        <path d="M8 16h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        <path d="M6 3h9l3 3v15H6z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                    </svg>
                </button>

                <button type="button" class="nyuci-toolbar-button nyuci-toolbar-button-muted" data-dt-action="pdf" title="PDF">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 3h7l4 4v14H7z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                        <path d="M14 3v5h5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                        <path d="M9 15h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                    </svg>
                </button>

                <button type="button" class="nyuci-toolbar-button nyuci-toolbar-button-muted" data-dt-action="print" title="Print">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 8V4h10v4" stroke="currentColor" stroke-width="1.7" />
                        <path d="M7 14H6a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-1" stroke="currentColor" stroke-width="1.7" />
                        <path d="M7 12h10v8H7z" stroke="currentColor" stroke-width="1.7" />
                    </svg>
                </button>

                <button type="button" class="nyuci-toolbar-button nyuci-toolbar-button-muted" data-dt-action="reset" title="Reset">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 8H4V4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M4 8a8 8 0 1 1-1 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                    </svg>
                </button>

                <button type="button" class="nyuci-toolbar-button nyuci-toolbar-button-muted" data-dt-action="reload" title="Reload">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M20 12a8 8 0 0 1-13.66 5.66L4 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M4 20v-5h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M4 12a8 8 0 0 1 13.66-5.66L20 9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M20 4v5h-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>

        @isset($filters)
            <div class="nyuci-filter-grid">
                {{ $filters }}
            </div>
        @endisset

        <div class="nyuci-datatable-host">
            <table id="{{ $tableId }}" class="display stripe hover dataTable w-full">
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <x-datatable-detail-flyout :name="$detailFlyoutName" />
</section>
