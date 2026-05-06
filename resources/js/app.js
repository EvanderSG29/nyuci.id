import './bootstrap';
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import { Chart, registerables } from 'chart.js';
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

Chart.register(...registerables);
window.Chart = Chart;

window.Alpine = Alpine;
Alpine.plugin(persist);

document.addEventListener('alpine:init', () => {
    const createThemeManagerState = () => ({
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
    });

    Alpine.data('themeManager', createThemeManagerState);

    Alpine.data('welcomePage', (previewImages = {}) => {
        const themeState = createThemeManagerState();

        return {
            ...themeState,
            scrolled: false,
            previewImages,

            currentPreview(key) {
                const activeTheme = this.resolvedTheme === 'dark' ? 'dark' : 'light';

                return this.previewImages[key]?.[activeTheme] ?? this.previewImages[key]?.light ?? '';
            },

            init() {
                themeState.init.call(this);

                const syncScrollState = () => {
                    this.scrolled = window.scrollY > 24;
                };

                syncScrollState();
                window.addEventListener('scroll', syncScrollState, { passive: true });
            },
        };
    });

    Alpine.data('settingsGuard', (config = {}) => ({
        saveSucceeded: Boolean(config.saveSucceeded),
        pendingActionKey: config.pendingActionKey ?? 'nyuci-settings-pending-action',
        pendingActionTtl: 5 * 60 * 1000,
        forms: [],
        dirty: false,
        pendingAction: null,
        activeFormId: null,
        allowImmediateNavigation: false,
        historyGuardEnabled: false,

        init() {
            this.forms = Array.from(this.$el.querySelectorAll('[data-settings-form]'));
            this.forms.forEach((form) => this.registerForm(form));
            this.updateDirtyState();
            this.installNavigationGuards();
            this.resumePendingActionIfNeeded();
        },

        registerForm(form) {
            form.dataset.settingsBaseline = this.snapshotForm(form);

            const syncState = () => {
                this.activeFormId = form.id || null;
                this.updateDirtyState();

                if (!this.saveSucceeded) {
                    this.clearPendingAction();
                }
            };

            form.addEventListener('focusin', () => {
                this.activeFormId = form.id || null;
            });

            form.addEventListener('input', syncState);
            form.addEventListener('change', syncState);
            form.addEventListener('submit', () => {
                this.allowImmediateNavigation = true;
            });
        },

        installNavigationGuards() {
            this.handleDocumentClick = (event) => {
                if (!this.hasUnsavedChanges() || this.allowImmediateNavigation) {
                    return;
                }

                if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const anchor = event.target.closest('a[href]');

                if (!anchor || anchor.hasAttribute('data-settings-guard-ignore')) {
                    return;
                }

                const href = anchor.getAttribute('href');

                if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
                    return;
                }

                if (anchor.target && anchor.target !== '_self') {
                    return;
                }

                const currentUrl = new URL(window.location.href);
                const targetUrl = new URL(href, currentUrl);

                if (targetUrl.href === currentUrl.href) {
                    return;
                }

                event.preventDefault();
                this.pendingAction = this.buildPendingAction({
                    type: 'link',
                    href: targetUrl.href,
                });
                this.openGuardModal();
            };

            this.handleDocumentSubmit = (event) => {
                if (!this.hasUnsavedChanges() || this.allowImmediateNavigation) {
                    return;
                }

                const form = event.target;
                const actionKey = form.getAttribute('data-settings-guard-action');

                if (!actionKey) {
                    return;
                }

                event.preventDefault();
                this.pendingAction = this.buildPendingAction({
                    type: 'form',
                    actionKey,
                });
                this.openGuardModal();
            };

            this.handleBeforeUnload = (event) => {
                if (!this.hasUnsavedChanges() || this.allowImmediateNavigation) {
                    return undefined;
                }

                event.preventDefault();
                event.returnValue = '';

                return '';
            };

            this.handlePopState = () => {
                if (!this.historyGuardEnabled) {
                    return;
                }

                if (this.allowImmediateNavigation) {
                    return;
                }

                if (this.hasUnsavedChanges()) {
                    history.pushState({ __settingsGuard: true }, '', window.location.href);
                    this.pendingAction = this.buildPendingAction({
                        type: 'history-back',
                    });
                    this.openGuardModal();

                    return;
                }

                this.allowImmediateNavigation = true;
                history.back();
            };

            document.addEventListener('click', this.handleDocumentClick, true);
            document.addEventListener('submit', this.handleDocumentSubmit, true);
            window.addEventListener('beforeunload', this.handleBeforeUnload);
            window.addEventListener('popstate', this.handlePopState);

            history.pushState({ __settingsGuard: true }, '', window.location.href);
            this.historyGuardEnabled = true;
        },

        snapshotForm(form) {
            const radioGroups = new Set();
            const snapshot = [];

            Array.from(form.elements)
                .filter((element) => (
                    element.name
                    && !element.disabled
                    && !['hidden', 'submit', 'button', 'reset', 'file'].includes(element.type)
                ))
                .forEach((element) => {
                    if (element.type === 'radio') {
                        if (radioGroups.has(element.name)) {
                            return;
                        }

                        radioGroups.add(element.name);
                        const checkedOption = form.querySelector(`input[type="radio"][name="${CSS.escape(element.name)}"]:checked`);
                        snapshot.push([element.name, checkedOption ? checkedOption.value : '']);

                        return;
                    }

                    if (element.type === 'checkbox') {
                        snapshot.push([element.name, element.checked ? (element.value || '1') : '']);

                        return;
                    }

                    if (element.tagName === 'SELECT' && element.multiple) {
                        Array.from(element.selectedOptions)
                            .map((option) => option.value)
                            .sort()
                            .forEach((value) => snapshot.push([element.name, value]));

                        return;
                    }

                    snapshot.push([element.name, element.value ?? '']);
                });

            snapshot.sort(([leftName, leftValue], [rightName, rightValue]) => {
                if (leftName === rightName) {
                    return String(leftValue).localeCompare(String(rightValue));
                }

                return leftName.localeCompare(rightName);
            });

            return JSON.stringify(snapshot);
        },

        updateDirtyState() {
            this.dirty = this.forms.some((form) => this.isFormDirty(form));

            if (!this.dirty) {
                this.pendingAction = null;
            }
        },

        isFormDirty(form) {
            return form.dataset.settingsBaseline !== this.snapshotForm(form);
        },

        hasUnsavedChanges() {
            return this.dirty;
        },

        hasSavableChanges() {
            return this.getActiveDirtyForm() !== null;
        },

        getActiveDirtyForm() {
            if (this.activeFormId) {
                const activeForm = this.forms.find((form) => form.id === this.activeFormId);

                if (activeForm && this.isFormDirty(activeForm)) {
                    return activeForm;
                }
            }

            return this.forms.find((form) => this.isFormDirty(form)) ?? null;
        },

        openGuardModal() {
            this.$dispatch('open-modal', 'settings-unsaved-changes');
        },

        stayOnPage() {
            this.pendingAction = null;
        },

        discardAndLeave() {
            const action = this.pendingAction;
            this.pendingAction = null;
            this.clearPendingAction();
            this.executePendingAction(action);
        },

        saveAndLeave() {
            const action = this.pendingAction;
            const form = this.getActiveDirtyForm();

            if (!action) {
                return;
            }

            if (!form) {
                this.pendingAction = null;
                this.executePendingAction(action);

                return;
            }

            this.persistPendingAction(action);
            this.allowImmediateNavigation = true;
            form.requestSubmit();
        },

        executePendingAction(action) {
            if (!action) {
                return;
            }

            this.allowImmediateNavigation = true;

            if (action.type === 'link' && action.href) {
                window.location.assign(action.href);

                return;
            }

            if (action.type === 'form' && action.actionKey) {
                const form = document.querySelector(`[data-settings-guard-action="${action.actionKey}"]`);

                form?.submit();

                return;
            }

            if (action.type === 'history-back') {
                history.go(-2);
            }
        },

        buildPendingAction(payload) {
            return {
                ...payload,
                originPath: window.location.pathname,
                createdAt: Date.now(),
            };
        },

        persistPendingAction(action) {
            sessionStorage.setItem(this.pendingActionKey, JSON.stringify(action));
        },

        readPendingAction() {
            const raw = sessionStorage.getItem(this.pendingActionKey);

            if (!raw) {
                return null;
            }

            try {
                const action = JSON.parse(raw);

                if (!action?.originPath || !action?.createdAt) {
                    this.clearPendingAction();

                    return null;
                }

                if ((Date.now() - action.createdAt) > this.pendingActionTtl) {
                    this.clearPendingAction();

                    return null;
                }

                if (action.originPath !== window.location.pathname) {
                    this.clearPendingAction();

                    return null;
                }

                return action;
            } catch (error) {
                this.clearPendingAction();

                return null;
            }
        },

        clearPendingAction() {
            sessionStorage.removeItem(this.pendingActionKey);
        },

        resumePendingActionIfNeeded() {
            const action = this.readPendingAction();

            if (!action || !this.saveSucceeded) {
                return;
            }

            this.clearPendingAction();

            queueMicrotask(() => {
                this.executePendingAction(action);
            });
        },
    }));

    Alpine.data('dashboardChrome', (config = {}) => ({
        isDashboard: Boolean(config.isDashboard),
        searchEnabled: Boolean(config.searchEnabled),
        searchUrl: config.searchUrl ?? '',
        scrolled: false,
        query: '',
        open: false,
        loading: false,
        error: null,
        results: {
            query: '',
            groups: [],
            message: null,
        },
        searchAbortController: null,
        activeSearchToken: 0,

        init() {
            const syncScrollState = () => {
                this.scrolled = window.scrollY > 16;
            };

            syncScrollState();
            window.addEventListener('scroll', syncScrollState, { passive: true });

            this.$watch('query', (value) => {
                this.performSearch(value);
            });
        },

        performSearch(value) {
            const query = value.trim();

            if (! this.searchEnabled || query.length < 2) {
                this.abortSearch();
                this.loading = false;
                this.error = null;
                this.results = {
                    query,
                    groups: [],
                    message: null,
                };
                this.open = false;

                return;
            }

            if (! this.searchUrl) {
                return;
            }

            const token = ++this.activeSearchToken;
            const url = new URL(this.searchUrl, window.location.origin);
            url.searchParams.set('query', query);

            this.abortSearch();
            this.loading = true;
            this.error = null;
            this.open = true;
            this.results = {
                query,
                groups: [],
                message: null,
            };
            this.searchAbortController = new AbortController();

            fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: this.searchAbortController.signal,
            })
                .then(async (response) => {
                    if (! response.ok) {
                        throw new Error(`Search request failed with status ${response.status}`);
                    }

                    return response.json();
                })
                .then((payload) => {
                    if (token !== this.activeSearchToken) {
                        return;
                    }

                    this.results = {
                        query: payload.query ?? query,
                        groups: Array.isArray(payload.groups) ? payload.groups : [],
                        message: payload.message ?? null,
                    };
                    this.loading = false;
                    this.error = null;
                    this.open = true;
                })
                .catch((error) => {
                    if (error?.name === 'AbortError') {
                        return;
                    }

                    if (token !== this.activeSearchToken) {
                        return;
                    }

                    this.loading = false;
                    this.error = 'Pencarian gagal dimuat. Coba lagi.';
                    this.results = {
                        query,
                        groups: [],
                        message: null,
                    };
                    this.open = true;
                });
        },

        abortSearch() {
            if (! this.searchAbortController) {
                return;
            }

            this.searchAbortController.abort();
            this.searchAbortController = null;
        },

        closeSearch() {
            this.open = false;
        },

        openSearch() {
            if (! this.searchEnabled || this.query.trim().length < 2) {
                return;
            }

            this.open = true;
        },

        clearSearch() {
            this.abortSearch();
            this.query = '';
            this.loading = false;
            this.error = null;
            this.results = {
                query: '',
                groups: [],
                message: null,
            };
            this.open = false;
        },

        shouldShowSearch() {
            return this.searchEnabled
                && this.open
                && (
                    this.loading
                    || this.error !== null
                    || this.query.trim().length >= 2
                    || this.hasSearchResults()
                );
        },

        hasSearchResults() {
            return Array.isArray(this.results.groups) && this.results.groups.length > 0;
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
    emptyTable: 'Belum ada data pada tabel',
    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
    infoFiltered: '(difilter dari _MAX_ total data)',
    lengthMenu: 'Tampilkan _MENU_ data',
    loadingRecords: 'Memuat...',
    processing: 'Memproses...',
    search: 'Cari:',
    zeroRecords: 'Data yang dicari tidak ditemukan',
    paginate: {
        first: 'Awal',
        last: 'Akhir',
        next: 'Berikutnya',
        previous: 'Sebelumnya',
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

const showNyuciModal = (name) => {
    if (! name) {
        return;
    }

    if (window.Flux?.modal) {
        window.Flux.modal(name).show();

        return;
    }

    document.dispatchEvent(new CustomEvent('modal-show', {
        detail: { name },
    }));
};

const buildDetailStateHtml = (title, text, modifier = '') => `
    <div class="nyuci-detail-state ${modifier}">
        <p class="nyuci-detail-state-label">Detail informasi</p>
        <h3 class="nyuci-detail-state-title">${escapeHtml(title)}</h3>
        <p class="nyuci-detail-state-text">${escapeHtml(text)}</p>
    </div>
`;

const updateDetailFlyout = (root, markup) => {
    const body = root?.querySelector('[data-dt-flyout-body]');

    if (! body) {
        return false;
    }

    body.innerHTML = markup;

    return true;
};

const openDetailFlyout = async (trigger) => {
    const root = trigger.closest('[data-nyuci-datatable]');
    const detailUrl = trigger.dataset.detailUrl;
    const flyoutName = root?.dataset.dtFlyoutName;

    if (! root || ! detailUrl || ! flyoutName) {
        return;
    }

    closeNyuciActionMenus();
    updateDetailFlyout(root, buildDetailStateHtml('Memuat detail...', 'Mohon tunggu, informasi sedang disiapkan.', 'is-loading'));
    showNyuciModal(flyoutName);

    try {
        const response = await fetch(detailUrl, {
            headers: {
                Accept: 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (! response.ok) {
            throw new Error(`Failed to fetch detail preview: ${response.status}`);
        }

        const html = (await response.text()).trim();

        if (html === '') {
            updateDetailFlyout(root, buildDetailStateHtml('Detail belum tersedia', 'Data tidak memiliki ringkasan tambahan saat ini.', 'is-empty'));

            return;
        }

        updateDetailFlyout(root, html);
    } catch {
        updateDetailFlyout(root, buildDetailStateHtml('Gagal memuat detail', 'Coba lagi beberapa saat atau muat ulang halaman.', 'is-error'));
    }
};

const syncSearchInput = (root, config) => {
    const searchInput = root.querySelector('.dt-search input');

    if (! searchInput) {
        return;
    }

    searchInput.placeholder = config?.searchPlaceholder ?? 'Search...';
    searchInput.autocomplete = 'off';

    if (typeof config?.initialSearch === 'string' && config.initialSearch.trim() !== '' && searchInput.value === '') {
        searchInput.value = config.initialSearch.trim();
    }
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
    const initialSearch = typeof config?.initialSearch === 'string' ? config.initialSearch.trim() : '';

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
        search: {
            search: initialSearch,
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

const dashboardNumberFormatter = new Intl.NumberFormat('id-ID');
const isDarkThemeActive = () =>
    document.documentElement.classList.contains('dark')
    || document.documentElement.classList.contains('theme-dark');

const hexToRgba = (hex, alpha) => {
    const normalized = String(hex ?? '').trim();

    if (normalized.startsWith('rgba(') || normalized.startsWith('rgb(')) {
        return normalized;
    }

    const value = normalized.replace('#', '');

    if (value.length !== 6) {
        return `rgba(74, 125, 240, ${alpha})`;
    }

    const red = Number.parseInt(value.slice(0, 2), 16);
    const green = Number.parseInt(value.slice(2, 4), 16);
    const blue = Number.parseInt(value.slice(4, 6), 16);

    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
};

const parseDashboardChartConfig = (value) => {
    if (! value) {
        return null;
    }

    if (typeof value === 'object') {
        return value;
    }

    try {
        return JSON.parse(value);
    } catch {
        return null;
    }
};

const formatDashboardMetricValue = (metricFormat, value) =>
    metricFormat === 'currency'
        ? `Rp ${dashboardNumberFormatter.format(Math.round(Number(value) || 0))}`
        : dashboardNumberFormatter.format(Math.round(Number(value) || 0));

const buildDashboardGradient = (chart, color, topAlpha, bottomAlpha) => {
    const { chartArea, ctx } = chart;

    if (! chartArea) {
        return hexToRgba(color, bottomAlpha);
    }

    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    gradient.addColorStop(0, hexToRgba(color, topAlpha));
    gradient.addColorStop(0.55, hexToRgba(color, bottomAlpha));
    gradient.addColorStop(1, hexToRgba(color, 0.02));

    return gradient;
};

const buildDashboardAxisConfig = (axisFormat, isHero) => {
    const darkMode = isDarkThemeActive();
    const tickColor = isHero
        ? 'rgba(255, 255, 255, 0.72)'
        : (darkMode ? 'rgba(226, 232, 240, 0.72)' : 'rgba(112, 130, 155, 0.9)');
    const gridColor = isHero
        ? 'rgba(255, 255, 255, 0.12)'
        : (darkMode ? 'rgba(148, 163, 184, 0.18)' : 'rgba(216, 226, 238, 0.84)');

    return {
        beginAtZero: true,
        grid: {
            color: gridColor,
            drawBorder: false,
        },
        ticks: {
            color: tickColor,
            callback: (value) => formatDashboardMetricValue(axisFormat, value),
        },
        border: {
            display: false,
        },
    };
};

const buildDashboardChartConfig = (payload) => {
    const chartPayload = payload?.chart ?? {};
    const isHero = payload?.surface === 'hero';
    const chartType = chartPayload.type === 'bar' ? 'bar' : 'line';
    const accentColor = payload?.accent_color ?? '#4a7df0';
    const datasets = Array.isArray(chartPayload.data?.datasets) ? chartPayload.data.datasets : [];
    const showPoints = Boolean(payload?.show_points);
    const axes = chartPayload.axes ?? { y: 'number' };
    const data = {
        labels: Array.isArray(chartPayload.data?.labels) ? chartPayload.data.labels : [],
        datasets: datasets.map((dataset, index) => {
            const isPrimary = index === 0;
            const color = dataset.borderColor ?? (isPrimary ? accentColor : 'rgba(148, 163, 184, 0.8)');
            const alpha = isHero ? (isPrimary ? 0.22 : 0.16) : (chartType === 'bar' ? 0.65 : 0.18);
            const gradientAlphaTop = isHero
                ? (isPrimary ? 0.42 : 0.22)
                : (isPrimary ? 0.24 : 0.16);
            const gradientAlphaBottom = isHero
                ? (isPrimary ? 0.06 : 0.03)
                : (isPrimary ? 0.07 : 0.05);

            return {
                ...dataset,
                borderColor: color,
                backgroundColor: chartType === 'line'
                    ? ((context) => buildDashboardGradient(context.chart, color, gradientAlphaTop, gradientAlphaBottom))
                    : (dataset.backgroundColor ?? hexToRgba(color, alpha)),
                pointBackgroundColor: dataset.pointBackgroundColor ?? color,
                pointBorderColor: dataset.pointBorderColor ?? color,
                borderWidth: dataset.borderWidth ?? (isHero ? 3 : 2),
                borderRadius: dataset.borderRadius ?? 10,
                barPercentage: dataset.barPercentage ?? 0.78,
                categoryPercentage: dataset.categoryPercentage ?? 0.8,
                fill: chartType === 'line' ? Boolean(dataset.fill) : false,
                tension: dataset.tension ?? (isHero ? 0.42 : 0.38),
                pointRadius: showPoints ? (dataset.pointRadius ?? 3) : 0,
                pointHoverRadius: showPoints ? (dataset.pointHoverRadius ?? 5) : 0,
                pointHitRadius: dataset.pointHitRadius ?? 12,
                yAxisID: dataset.axis_id ?? 'y',
            };
        }),
    };

    const darkMode = isDarkThemeActive();
    const baseTooltip = {
        backgroundColor: darkMode ? '#0f172a' : '#ffffff',
        titleColor: darkMode ? '#f8fafc' : '#142338',
        bodyColor: darkMode ? '#cbd5e1' : '#43566d',
        borderColor: darkMode ? 'rgba(148, 163, 184, 0.22)' : '#d8e2ee',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 14,
        caretPadding: 10,
        titleMarginBottom: 8,
        displayColors: true,
        callbacks: {
            title(items) {
                const meta = items[0]?.dataset?.meta?.[items[0].dataIndex];

                return meta?.label ?? items[0]?.label ?? '';
            },
            label(context) {
                const meta = context.dataset?.meta?.[context.dataIndex] ?? {};
                const value = meta.formattedValue ?? context.formattedValue ?? context.raw ?? 0;
                const line = `${context.dataset?.label ?? ''}: ${value}`;

                if (meta.deltaText) {
                    return [line, meta.deltaText];
                }

                return line;
            },
        },
    };

    return {
        type: chartType,
        data,
        options: {
            layout: {
                padding: isHero
                    ? {
                        top: 8,
                        right: 6,
                        bottom: 28,
                        left: 6,
                    }
                    : {
                        top: 4,
                        right: 4,
                        bottom: 8,
                        left: 4,
                    },
            },
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: Boolean(chartPayload.showLegend),
                },
                tooltip: baseTooltip,
            },
            scales: {
                x: {
                    grid: {
                        color: isHero
                            ? 'rgba(255, 255, 255, 0.08)'
                            : (darkMode ? 'rgba(148, 163, 184, 0.14)' : 'rgba(216, 226, 238, 0.78)'),
                        drawBorder: false,
                    },
                    ticks: {
                        color: isHero
                            ? 'rgba(255, 255, 255, 0.72)'
                            : (darkMode ? 'rgba(226, 232, 240, 0.72)' : 'rgba(112, 130, 155, 0.9)'),
                    },
                },
                y: buildDashboardAxisConfig(axes.y ?? 'number', isHero),
                ...(axes.y1
                    ? {
                        y1: {
                            ...buildDashboardAxisConfig(axes.y1, isHero),
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                                drawBorder: false,
                            },
                        },
                    }
                    : {}),
            },
        },
    };
};

const initializeDashboardChart = (root) => {
    if (!(root instanceof HTMLElement) || root.dataset.dashboardChartMounted === '1') {
        return;
    }

    const payload = parseDashboardChartConfig(root.dataset.dashboardChart);
    const canvas = root.querySelector('canvas');

    if (! payload || !(canvas instanceof HTMLCanvasElement) || typeof Chart === 'undefined') {
        return;
    }

    const chart = new Chart(canvas, buildDashboardChartConfig(payload));

    root._dashboardChartInstance = chart;
    root.dataset.dashboardChartMounted = '1';
};

const initializeDashboardCharts = () => {
    document.querySelectorAll('[data-dashboard-chart]').forEach(initializeDashboardChart);
};

Alpine.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNyuciDataTables);
    document.addEventListener('DOMContentLoaded', initializeDashboardCharts);
} else {
    initializeNyuciDataTables();
    initializeDashboardCharts();
}

document.addEventListener('livewire:navigated', initializeNyuciDataTables);
document.addEventListener('livewire:navigated', initializeDashboardCharts);
document.addEventListener('click', (event) => {
    const previewTrigger = event.target.closest('[data-detail-url]');

    if (previewTrigger instanceof HTMLElement) {
        event.preventDefault();
        openDetailFlyout(previewTrigger);

        return;
    }

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
