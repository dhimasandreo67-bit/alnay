// ===== TOAST =====
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type] ?? icons.info}</span>
        <span class="toast-message">${message}</span>
        <button type="button" class="toast-close" aria-label="Tutup">&times;</button>
    `;
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('toast-show'));

    const remove = () => {
        toast.classList.remove('toast-show');
        toast.classList.add('toast-hide');
        setTimeout(() => toast.remove(), 300);
    };
    const timer = setTimeout(remove, duration);
    toast.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(timer); remove(); });
}
window.showToast = showToast;

// ===== MODAL =====
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('modal-open');
    document.body.classList.add('modal-no-scroll');
}
window.openModal = openModal;

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('modal-open');
    document.body.classList.remove('modal-no-scroll');
}
window.closeModal = closeModal;

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-modal-target]').forEach((btn) => {
        btn.addEventListener('click', () => openModal(btn.dataset.modalTarget));
    });
    document.querySelectorAll('[data-modal]').forEach((modal) => {
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal.id); });
        modal.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', () => closeModal(modal.id)));
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.modal-open').forEach((m) => closeModal(m.id));
    });
});