import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import './animations.js'; // ← tambahkan ini
import './toast-modal.js';

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();