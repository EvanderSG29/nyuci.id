import './bootstrap';
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

window.Alpine = Alpine;
Alpine.plugin(persist);

document.addEventListener('alpine:init', () => {
    Alpine.data('themeManager', () => ({
        themeMode: Alpine.$persist(window.NyuciTheme?.getStoredMode() ?? 'auto').as('selected-radio'),
        resolvedTheme: window.NyuciTheme?.resolveTheme(window.NyuciTheme?.getStoredMode() ?? 'auto') ?? 'light',
        mediaQuery: null,

        init() {
            this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            this.applyTheme();

            this.$watch('themeMode', () => {
                this.applyTheme();
            });

            const syncWithSystem = () => {
                if (this.themeMode === 'auto') {
                    this.applyTheme();
                }
            };

            if (typeof this.mediaQuery.addEventListener === 'function') {
                this.mediaQuery.addEventListener('change', syncWithSystem);
            } else if (typeof this.mediaQuery.addListener === 'function') {
                this.mediaQuery.addListener(syncWithSystem);
            }
        },

        applyTheme() {
            const activeMode = this.themeMode || 'auto';

            if (! window.NyuciTheme) {
                this.resolvedTheme = activeMode === 'dark' ? 'dark' : 'light';

                return;
            }

            this.resolvedTheme = window.NyuciTheme.applyTheme(activeMode);
        },

        setThemeMode(mode) {
            this.themeMode = mode;
        },

        toggleSimpleTheme() {
            this.themeMode = this.resolvedTheme === 'dark' ? 'light' : 'dark';
        },
    }));

    Alpine.data('nyuciFilterSelect', (config = {}) => ({
        open: false,
        query: '',
        options: Array.isArray(config.options) ? config.options : [],
        searchable: Boolean(config.searchable),
        searchPlaceholder: config.searchPlaceholder ?? 'Cari pilihan...',
        emptyMessage: config.emptyMessage ?? 'Tidak ada pilihan yang cocok.',
        selected: config.selected ?? '',

        init() {
            if (this.selected === undefined || this.selected === null) {
                this.selected = this.options[0]?.value ?? '';
            }

            this.$watch('open', (value) => {
                if (value && this.searchable) {
                    this.$nextTick(() => this.$refs.search?.focus());
                }

                if (! value) {
                    this.query = '';
                }
            });
        },

        toggle() {
            this.open = ! this.open;
        },

        close() {
            this.open = false;
        },

        normalize(value) {
            return value === undefined || value === null ? '' : String(value);
        },

        isSelected(option) {
            return this.normalize(this.selected) === this.normalize(option.value);
        },

        select(option) {
            this.selected = option.value;
            this.close();
        },

        selectedOption() {
            return this.options.find((option) => this.isSelected(option)) ?? this.options[0] ?? null;
        },

        selectedLabel() {
            const option = this.selectedOption();

            if (! option) {
                return '';
            }

            return option.meta ? `${option.label} / ${option.meta}` : option.label;
        },

        filteredOptions() {
            const term = this.query.trim().toLowerCase();

            if (! this.searchable || term === '') {
                return this.options;
            }

            return this.options.filter((option) => {
                const haystack = `${option.label ?? ''} ${option.meta ?? ''}`.toLowerCase();

                return haystack.includes(term);
            });
        },
    }));
});

const datatableLanguage = {
    emptyTable: 'No data available in table',
    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
    infoEmpty: 'Showing 0 to 0 of 0 entries',
    infoFiltered: '(filtered from _MAX_ total entries)',
    lengthMenu: 'Show _MENU_ entries',
    loadingRecords: 'Loading...',
    processing: 'Processing...',
    search: 'Search:',
    zeroRecords: 'No matching records found',
    paginate: {
        first: 'First',
        last: 'Last',
        next: 'Next',
        previous: 'Previous',
    },
};

const parseDatatableConfig = (value) => {
    if (! value) {
        return null;
    }

    try {
        return JSON.parse(value);
    } catch {
        return null;
    }
};

const stripHtml = (value) => {
    if (value === null || value === undefined) {
        return '';
    }

    const template = document.createElement('template');
    template.innerHTML = String(value);

    return template.content.textContent?.replace(/\s+/g, ' ').trim() ?? '';
};

const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

const csvField = (value) => `"${String(value ?? '').replaceAll('"', '""')}"`;

const collectFilterValues = (root) => {
    const values = {};

    root.querySelectorAll('[data-dt-filter]').forEach((element) => {
        values[element.dataset.dtFilter] = element.value;
    });

    return values;
};

const collectExportColumns = (root, config) => {
    const headers = Array.from(root.querySelectorAll('thead th')).map((header) => header.textContent?.trim() ?? '');
    const columns = Array.isArray(config?.columns) ? config.columns : [];

    return columns
        .map((column, index) => ({
            data: column.data,
            header: headers[index] ?? `Column ${index + 1}`,
            index,
        }))
        .filter((column) => column.data !== 'actions');
};

const collectExportRows = (dataTable, exportColumns) => {
    const rows = dataTable.rows({ page: 'current' }).data().toArray();

    return rows.map((row) =>
        exportColumns.map((column) => {
            const rawValue = Array.isArray(row) ? row[column.index] : row?.[column.data];

            return stripHtml(rawValue);
        }),
    );
};

const downloadBlob = (filename, content, mimeType) => {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = filename;
    link.click();

    URL.revokeObjectURL(url);
};

const copyText = async (value) => {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(value);

        return;
    }

    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    textarea.remove();
};

const exportTitle = (root, config) => root.querySelector('h2')?.textContent?.trim() ?? config?.tableId ?? 'datatable';

const buildExportHtml = (title, headers, rows, subtitle = '') => {
    const dateLabel = new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date());

    const tableHead = headers.map((header) => `<th>${escapeHtml(header)}</th>`).join('');
    const tableRows = rows.length > 0
        ? rows
            .map(
                (row) =>
                    `<tr>${row.map((cell) => `<td>${escapeHtml(cell)}</td>`).join('')}</tr>`,
            )
            .join('')
        : `<tr><td colspan="${headers.length}">Tidak ada data pada halaman ini.</td></tr>`;

    return `<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>${escapeHtml(title)}</title>
    <style>
        :root {
            color-scheme: light;
        }
        body {
            font-family: Arial, sans-serif;
            background: #ffffff;
            color: #1f2937;
            margin: 24px;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 22px;
        }
        p {
            margin: 0 0 18px;
            color: #4b5563;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            border: 1px solid #d1d5db;
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
            font-size: 13px;
        }
        th {
            background: #f3f4f6;
            font-weight: 700;
        }
        @media print {
            @page {
                margin: 12mm;
            }
            body {
                margin: 12px;
            }
        }
    </style>
</head>
<body>
    <h1>${escapeHtml(title)}</h1>
    <p>${escapeHtml(subtitle || `Exported ${dateLabel}`)}</p>
    <table>
        <thead>
            <tr>${tableHead}</tr>
        </thead>
        <tbody>
            ${tableRows}
        </tbody>
    </table>
</body>
</html>`;
};

const openExportWindow = (title, headers, rows, subtitle = '', autoPrint = false) => {
    const exportWindow = window.open('', '_blank', 'width=1200,height=800');

    if (! exportWindow) {
        return null;
    }

    const html = buildExportHtml(title, headers, rows, subtitle);

    exportWindow.document.open();
    exportWindow.document.write(html);
    exportWindow.document.close();

    if (autoPrint) {
        window.setTimeout(() => {
            try {
                exportWindow.focus();
                exportWindow.print();
            } catch {
                // no-op
            }
        }, 350);
    }

    return exportWindow;
};

const syncSearchInput = (root, config) => {
    const searchInput = root.querySelector('.dt-search input');

    if (! searchInput) {
        return;
    }

    searchInput.placeholder = config?.searchPlaceholder ?? 'Search...';
    searchInput.autocomplete = 'off';
};

const bindToolbarAction = (root, dataTable, config) => async (event) => {
    const action = event.currentTarget.dataset.dtAction;
    const exportColumns = collectExportColumns(root, config);
    const headers = exportColumns.map((column) => column.header);
    const rows = collectExportRows(dataTable, exportColumns);
    const title = exportTitle(root, config);
    const fileBase = `${config?.tableId ?? 'datatable'}-${new Date().toISOString().slice(0, 10)}`;

    if (action === 'copy') {
        const copyValue = [headers.join('\t'), ...rows.map((row) => row.join('\t'))].join('\n');
        await copyText(copyValue);

        return;
    }

    if (action === 'csv') {
        const csv = [headers.map(csvField).join(','), ...rows.map((row) => row.map(csvField).join(','))].join('\n');
        downloadBlob(`${fileBase}.csv`, csv, 'text/csv;charset=utf-8;');

        return;
    }

    if (action === 'print' || action === 'pdf') {
        const subtitle = action === 'pdf'
            ? 'Gunakan dialog print browser untuk Save as PDF.'
            : undefined;
        openExportWindow(title, headers, rows, subtitle, true);

        return;
    }

    if (action === 'reset') {
        root.querySelectorAll('[data-dt-filter]').forEach((element) => {
            element.value = '';
        });

        const searchInput = root.querySelector('.dt-search input');

        if (searchInput) {
            searchInput.value = '';
        }

        dataTable.search('');

        if (Array.isArray(config?.order) && config.order.length > 0) {
            dataTable.order(config.order);
        }

        dataTable.page.len(10);
        dataTable.draw();

        return;
    }

    if (action === 'reload') {
        dataTable.ajax.reload(null, false);
    }
};

const initializeNyuciDataTable = (root) => {
    if (root.dataset.nyuciDatatableMounted === '1') {
        return;
    }

    const config = parseDatatableConfig(root.dataset.nyuciDatatable);
    const table = root.querySelector('table');

    if (! config || ! table) {
        return;
    }

    const dataTable = new DataTable(table, {
        ajax: {
            data: (payload) => Object.assign(payload, collectFilterValues(root)),
            url: config.ajaxUrl,
        },
        autoWidth: false,
        columns: Array.isArray(config.columns) ? config.columns : [],
        language: datatableLanguage,
        lengthMenu: [10, 25, 50, 100],
        order: Array.isArray(config.order) ? config.order : [],
        pageLength: 10,
        processing: true,
        scrollX: true,
        searchDelay: 350,
        serverSide: true,
        layout: {
            topStart: 'pageLength',
            topEnd: 'search',
            bottomStart: 'info',
            bottomEnd: 'paging',
        },
        initComplete: () => syncSearchInput(root, config),
        drawCallback: () => syncSearchInput(root, config),
    });

    root.querySelectorAll('[data-dt-filter]').forEach((element) => {
        element.addEventListener('change', () => dataTable.ajax.reload());
    });

    root.querySelectorAll('[data-dt-action]').forEach((button) => {
        button.addEventListener('click', bindToolbarAction(root, dataTable, config));
    });

    root.dataset.nyuciDatatableMounted = '1';
};

const initializeNyuciDataTables = () => {
    document.querySelectorAll('[data-nyuci-datatable]').forEach(initializeNyuciDataTable);
};

const closeNyuciActionMenus = (except = null) => {
    document.querySelectorAll('.nyuci-action-menu[open]').forEach((menu) => {
        if (menu !== except) {
            menu.removeAttribute('open');
        }
    });
};

Alpine.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNyuciDataTables);
} else {
    initializeNyuciDataTables();
}

document.addEventListener('livewire:navigated', initializeNyuciDataTables);
document.addEventListener('click', (event) => {
    if (! event.target.closest('.nyuci-action-menu')) {
        closeNyuciActionMenus();
    }
});

document.addEventListener('toggle', (event) => {
    const menu = event.target;

    if (!(menu instanceof HTMLDetailsElement) || ! menu.classList.contains('nyuci-action-menu')) {
        return;
    }

    if (menu.open) {
        closeNyuciActionMenus(menu);
    }
}, true);

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeNyuciActionMenus();
    }
});
