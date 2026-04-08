<script>
    (() => {
        const html = document.documentElement;
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const darkModeKey = 'dark-mode';
        const legacyThemeKey = 'nyuci-theme';
        const selectedRadioKey = 'selected-radio';

        const readStorage = (key) => {
            const value = localStorage.getItem(key);

            if (value === null) {
                return null;
            }

            try {
                return JSON.parse(value);
            } catch {
                return value;
            }
        };

        const mapLegacyPreference = (value) => {
            if (value === 'light' || value === 'dark') {
                return value;
            }

            return 'auto';
        };

        const getStoredMode = () => readStorage(selectedRadioKey)
            || mapLegacyPreference(readStorage(legacyThemeKey))
            || (readStorage(darkModeKey) ? 'dark' : 'auto');

        const resolveTheme = (mode) => {
            if (mode === 'dark' || mode === 'light') {
                return mode;
            }

            return mediaQuery.matches ? 'dark' : 'light';
        };

        const applyThemeClass = (mode) => {
            const resolvedTheme = resolveTheme(mode);

            html.classList.toggle('dark', resolvedTheme === 'dark');
            html.classList.toggle('theme-dark', resolvedTheme === 'dark');
            html.dataset.theme = resolvedTheme;
            html.style.colorScheme = resolvedTheme;

            return resolvedTheme;
        };

        const applyTheme = (mode) => {
            const normalizedMode = mode === 'system' ? 'auto' : mode;
            const resolvedTheme = applyThemeClass(normalizedMode);

            localStorage.setItem(selectedRadioKey, JSON.stringify(normalizedMode));
            localStorage.setItem(legacyThemeKey, normalizedMode === 'auto' ? 'system' : normalizedMode);

            if (resolvedTheme === 'dark') {
                localStorage.setItem(darkModeKey, 'true');
            } else {
                localStorage.removeItem(darkModeKey);
            }

            window.dispatchEvent(new CustomEvent('nyuci-theme-changed', {
                detail: {
                    preference: normalizedMode === 'auto' ? 'system' : normalizedMode,
                    selectedRadio: normalizedMode,
                    resolvedTheme,
                },
            }));

            return resolvedTheme;
        };

        const syncSwitches = () => {
            document.querySelectorAll('.switches [type="radio"]').forEach((input) => {
                input.checked = input.id === getStoredMode();
            });
        };

        const initializeSwitches = () => {
            syncSwitches();

            document.querySelectorAll('.switches [type="radio"]').forEach((input) => {
                input.addEventListener('change', (event) => {
                    applyTheme(event.target.value);
                    syncSwitches();
                });
            });
        };

        window.NyuciTheme = {
            getStoredMode,
            getPreference: () => {
                const mode = getStoredMode();

                return mode === 'auto' ? 'system' : mode;
            },
            getResolvedTheme: () => resolveTheme(getStoredMode()),
            resolveTheme,
            applyTheme,
        };

        applyThemeClass(getStoredMode());

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeSwitches, { once: true });
        } else {
            initializeSwitches();
        }
    })();
</script>
