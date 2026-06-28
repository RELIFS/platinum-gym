<script>
    (() => {
        const storageKey = 'theme';
        const lightThemeColor = '#fafafa';
        const darkThemeColor = '#09090b';
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        let releaseThemeTransitionSuppressionTimer = null;

        const suppressThemeTransitions = () => {
            const root = document.documentElement;

            root.classList.add('theme-switching');
            window.clearTimeout(releaseThemeTransitionSuppressionTimer);

            const release = () => {
                root.classList.remove('theme-switching');
                releaseThemeTransitionSuppressionTimer = null;
            };

            if (typeof window.requestAnimationFrame === 'function') {
                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(release);
                });

                releaseThemeTransitionSuppressionTimer = window.setTimeout(release, 180);

                return;
            }

            releaseThemeTransitionSuppressionTimer = window.setTimeout(release, 80);
        };

        const readStoredTheme = () => {
            try {
                return localStorage.getItem(storageKey);
            } catch (error) {
                return null;
            }
        };

        const writeStoredTheme = (theme) => {
            try {
                localStorage.setItem(storageKey, theme);
            } catch (error) {
                // Theme still changes for this page even when storage is unavailable.
            }
        };

        const resolveTheme = () => {
            const storedTheme = readStoredTheme();

            if (storedTheme === 'dark' || storedTheme === 'light') {
                return storedTheme;
            }

            return mediaQuery.matches ? 'dark' : 'light';
        };

        const syncThemeControls = (theme) => {
            const isDark = theme === 'dark';
            const themeColor = document.querySelector('meta[name="theme-color"]');

            document.documentElement.classList.toggle('dark', isDark);
            themeColor?.setAttribute('content', isDark ? darkThemeColor : lightThemeColor);

            document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
                const label = isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap';
                button.setAttribute('aria-label', label);
                button.setAttribute('title', label);
            });
        };

        const applyTheme = (theme, shouldPersist = false, shouldSuppressTransitions = false) => {
            if (shouldSuppressTransitions) {
                suppressThemeTransitions();
            }

            syncThemeControls(theme);

            if (shouldPersist) {
                writeStoredTheme(theme);
            }
        };

        applyTheme(resolveTheme());

        document.addEventListener('DOMContentLoaded', () => {
            syncThemeControls(resolveTheme());

            document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
                    applyTheme(nextTheme, true, true);
                });
            });
        });

        const handleSystemThemeChange = () => {
            if (!readStoredTheme()) {
                applyTheme(resolveTheme(), false, true);
            }
        };

        if (typeof mediaQuery.addEventListener === 'function') {
            mediaQuery.addEventListener('change', handleSystemThemeChange);
        } else if (typeof mediaQuery.addListener === 'function') {
            mediaQuery.addListener(handleSystemThemeChange);
        }
    })();
</script>
