import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('theme', {
    mode: localStorage.getItem('gdfh-theme') || 'light',

    get isDark() {
        if (this.mode === 'dark') {
            return true;
        }

        return false;
    },

    set(mode) {
        if (!['light', 'dark', 'system'].includes(mode)) {
            return;
        }

        this.mode = mode;
        localStorage.setItem('gdfh-theme', mode);

        this.apply();
    },

    apply() {
        document.documentElement.classList.toggle('dark', this.isDark);
    },

    init() {
        this.apply();

        const media = window.matchMedia('(prefers-color-scheme: dark)');

        media.addEventListener('change', () => {
            if (this.mode === 'system') {
                this.apply();
            }
        });
    },
});

Alpine.start();
