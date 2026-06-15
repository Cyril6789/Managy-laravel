import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;

Alpine.plugin(collapse);

/**
 * Global theme store: light / dark with persistence and OS-preference fallback.
 * The initial class is applied inline in <head> (see layout) to avoid FOUC.
 */
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),

    toggle() {
        this.dark = !this.dark;
        this.apply();
    },

    apply() {
        document.documentElement.classList.toggle('dark', this.dark);
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
    },
});

Alpine.start();
