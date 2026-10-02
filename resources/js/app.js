import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const prefersDarkTheme = (() => {
    try {
        return window.localStorage.getItem('theme') === 'dark';
    } catch (error) {
        return false;
    }
})();

document.documentElement.classList.toggle('dark', prefersDarkTheme);

Alpine.store('theme', {
    dark: prefersDarkTheme,
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);

        try {
            window.localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        } catch (error) {
            // The selected theme still applies for the current page.
        }
    },
});

Alpine.start();
