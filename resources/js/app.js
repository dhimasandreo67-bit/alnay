import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import './animations.js';
import './toast-modal.js';
import './sidebar-scroll.js';
import koleksiFilter from './pages/koleksi.js';

window.Alpine = Alpine;
Alpine.plugin(collapse);

// Didaftarkan sebelum Alpine.start() supaya x-data="koleksiFilter(...)" selalu ditemukan.
Alpine.data('koleksiFilter', koleksiFilter);

Alpine.start();