import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import './animations.js';
import './toast-modal.js';
import './sidebar-scroll.js';

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();