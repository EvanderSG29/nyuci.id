import './bootstrap';
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

Alpine.start();
