// resources/js/sidebar-scroll.js
(() => {
    const KEY = 'admin-sidebar-scroll';
    const SELECTOR = '[data-sidebar-scroll]';

    function getSidebar() {
        return document.querySelector(SELECTOR);
    }

    function restore() {
        const sidebar = getSidebar();
        if (!sidebar) return;
        const posisi = sessionStorage.getItem(KEY);
        if (posisi !== null) {
            sidebar.scrollTop = parseInt(posisi, 10);
        }
    }

    function simpan() {
        const sidebar = getSidebar();
        if (!sidebar) return;
        sessionStorage.setItem(KEY, sidebar.scrollTop);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = getSidebar();
        if (!sidebar) return;

        restore();

        let timer = null;
        sidebar.addEventListener('scroll', () => {
            clearTimeout(timer);
            timer = setTimeout(simpan, 100);
        });
    });

    window.addEventListener('beforeunload', simpan);
})();