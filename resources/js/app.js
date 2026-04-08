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
});

Alpine.start();
