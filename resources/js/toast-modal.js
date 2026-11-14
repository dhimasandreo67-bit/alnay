/**
 * Toast + Modal universal.
 *
 * Pemakaian:
 *   showToast('Pesan', 'success' | 'error' | 'warning' | 'info', durasiMs?)
 *   openModal('id-modal', triggerEl?)   closeModal('id-modal')
 *   <button data-modal-open="id-modal">   <button data-modal-close>
 *
 * Event pada elemen modal: 'modal:open' (detail.trigger = tombol pemicu), 'modal:close'.
 * Toast dari session flash dikirim komponen <x-toast /> lewat window.__toastQueue,
 * jadi urutan pemuatan script tidak berpengaruh.
 */
(() => {
    if (window.__uiOverlayReady) return;
    window.__uiOverlayReady = true;

    const CSS = `
    /* ---------- Modal ---------- */
    .ui-modal{position:fixed;inset:0;z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem;opacity:0;transition:opacity .18s ease}
    .ui-modal[hidden]{display:none}
    .ui-modal.is-open{opacity:1}
    .ui-modal__backdrop{position:absolute;inset:0;background:rgba(10,21,38,.55);backdrop-filter:blur(2px)}
    .ui-modal__panel{position:relative;width:100%;max-height:calc(100vh - 2rem);max-height:calc(100dvh - 2rem);display:flex;flex-direction:column;background:#fff;color:#111827;border-radius:1.5rem;box-shadow:0 24px 60px -12px rgba(10,21,38,.35);transform:translateY(12px) scale(.98);transition:transform .2s cubic-bezier(.22,1,.36,1);outline:none}
    .ui-modal.is-open .ui-modal__panel{transform:none}
    .ui-modal__panel--sm{max-width:24rem}
    .ui-modal__panel--md{max-width:32rem}
    .ui-modal__panel--lg{max-width:44rem}
    .ui-modal__panel--xl{max-width:60rem}
    .ui-modal__header{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.25rem 1.5rem .75rem}
    .ui-modal__title{margin:0;font-size:1.05rem;font-weight:800;letter-spacing:-.01em;color:#111827}
    .ui-modal__close{flex-shrink:0;width:2rem;height:2rem;margin-left:auto;border:0;border-radius:.6rem;background:transparent;color:#9ca3af;font-size:1.4rem;line-height:1;cursor:pointer;transition:background-color .15s,color .15s}
    .ui-modal__close:hover{background:#f3f4f6;color:#374151}
    .ui-modal__body{padding:.25rem 1.5rem 1.25rem;overflow-y:auto}
    .ui-modal__footer{display:flex;justify-content:flex-end;gap:.5rem;padding:1rem 1.5rem;border-top:1px solid #f3f4f6;background:#f9fafb;border-radius:0 0 1.5rem 1.5rem}
    html.ui-scroll-lock{overflow:hidden}

    /* ---------- Tombol untuk footer modal ---------- */
    .ui-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.65rem 1.1rem;border:1px solid transparent;border-radius:.75rem;font-size:.75rem;font-weight:700;line-height:1;cursor:pointer;transition:background-color .15s,transform .1s}
    .ui-btn:active{transform:scale(.97)}
    .ui-btn:disabled{opacity:.6;cursor:not-allowed}
    .ui-btn:focus-visible,.ui-modal__close:focus-visible,.ui-toast__close:focus-visible{outline:2px solid #f59e0b;outline-offset:2px}
    .ui-btn--primary{background:#111827;color:#fff}
    .ui-btn--primary:hover:not(:disabled){background:#1f2937}
    .ui-btn--danger{background:#e11d48;color:#fff}
    .ui-btn--danger:hover:not(:disabled){background:#be123c}
    .ui-btn--ghost{background:#fff;border-color:#e5e7eb;color:#374151}
    .ui-btn--ghost:hover:not(:disabled){background:#f9fafb}

    /* ---------- Toast ---------- */
    .ui-toast-stack{position:fixed;top:1rem;right:1rem;z-index:10000;display:flex;flex-direction:column;gap:.5rem;width:min(23rem,calc(100vw - 2rem));pointer-events:none}
    .ui-toast{pointer-events:auto;display:flex;align-items:flex-start;gap:.7rem;padding:.8rem .9rem;background:#fff;color:#111827;border:1px solid #e5e7eb;border-left-width:4px;border-radius:1rem;box-shadow:0 12px 32px -8px rgba(10,21,38,.25);opacity:0;transform:translateX(18px);transition:opacity .2s ease,transform .25s cubic-bezier(.22,1,.36,1)}
    .ui-toast.is-in{opacity:1;transform:none}
    .ui-toast__icon{flex-shrink:0;display:flex;margin-top:.1rem}
    .ui-toast__icon svg{width:1.15rem;height:1.15rem}
    .ui-toast__text{flex:1;margin:0;font-size:.8rem;line-height:1.4;font-weight:600;word-break:break-word}
    .ui-toast__close{flex-shrink:0;width:1.5rem;height:1.5rem;border:0;border-radius:.5rem;background:transparent;color:#9ca3af;font-size:1.15rem;line-height:1;cursor:pointer}
    .ui-toast__close:hover{background:#f3f4f6;color:#374151}
    .ui-toast--success{border-left-color:#059669}.ui-toast--success .ui-toast__icon{color:#059669}
    .ui-toast--error{border-left-color:#e11d48}.ui-toast--error .ui-toast__icon{color:#e11d48}
    .ui-toast--warning{border-left-color:#d97706}.ui-toast--warning .ui-toast__icon{color:#d97706}
    .ui-toast--info{border-left-color:#0284c7}.ui-toast--info .ui-toast__icon{color:#0284c7}

    @media (prefers-reduced-motion: reduce){
        .ui-modal,.ui-modal__panel,.ui-toast{transition:none}
    }`;

    function injectStyles() {
        if (document.getElementById('ui-overlay-styles')) return;
        const style = document.createElement('style');
        style.id = 'ui-overlay-styles';
        style.textContent = CSS;
        document.head.appendChild(style);
    }

    /* =====================================================
     * TOAST
     * ===================================================== */
    const ICONS = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.8"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.4v.1"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4l9 16H3L12 4z"/><path d="M12 10v4.5M12 17.4v.1"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5M12 7.9v.1"/></svg>',
    };
    const TOAST_TYPES = Object.keys(ICONS);
    const MAX_TOAST = 4;

    function ensureStack() {
        let stack = document.getElementById('ui-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'ui-toast-stack';
            stack.className = 'ui-toast-stack';
            stack.setAttribute('aria-live', 'polite');
            document.body.appendChild(stack);
        }
        return stack;
    }

    function removeToast(toast) {
        if (!toast || toast.__removing) return;
        toast.__removing = true;
        clearTimeout(toast.__timer);
        toast.classList.remove('is-in');
        setTimeout(() => toast.remove(), 220);
    }

    function showToast(message, type = 'info', duration) {
        if (!message) return;
        if (!TOAST_TYPES.includes(type)) type = 'info';
        injectStyles();

        const stack = ensureStack();
        const text = String(message);

        // Hindari toast kembar (mis. komponen <x-toast /> terpasang dua kali)
        const kembar = Array.from(stack.children).find((t) => t.dataset.msg === text && t.dataset.type === type && !t.__removing);
        if (kembar) return;

        while (stack.children.length >= MAX_TOAST) removeToast(stack.firstElementChild);

        const toast = document.createElement('div');
        toast.className = 'ui-toast ui-toast--' + type;
        toast.dataset.msg = text;
        toast.dataset.type = type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.innerHTML =
            '<span class="ui-toast__icon">' + ICONS[type] + '</span>' +
            '<p class="ui-toast__text"></p>' +
            '<button type="button" class="ui-toast__close" aria-label="Tutup notifikasi">&times;</button>';
        toast.querySelector('.ui-toast__text').textContent = text; // textContent: aman dari XSS
        toast.querySelector('.ui-toast__close').addEventListener('click', () => removeToast(toast));

        const ms = typeof duration === 'number' ? duration : (type === 'error' ? 7000 : 4500);
        const mulaiTimer = (delay) => {
            clearTimeout(toast.__timer);
            if (ms > 0) toast.__timer = setTimeout(() => removeToast(toast), delay);
        };
        toast.addEventListener('mouseenter', () => clearTimeout(toast.__timer));
        toast.addEventListener('mouseleave', () => mulaiTimer(2000));

        stack.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('is-in'));
        mulaiTimer(ms);
    }

    /* =====================================================
     * MODAL
     * ===================================================== */
    const FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]):not([type="hidden"]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';
    const openStack = [];

    const getModal = (ref) => (typeof ref === 'string' ? document.getElementById(ref) : ref);

    // Pindahkan modal ke <body>. Ini penting: kalau modal berada di dalam elemen yang punya
    // transform/animasi (mis. wrapper animate-fade-in), position:fixed jadi relatif ke elemen itu
    // dan modal bisa terpotong, salah posisi, atau tertutup sidebar.
    function pasangKeBody(modal) {
        if (modal.parentElement !== document.body) document.body.appendChild(modal);
    }

    function kunciScroll() {
        document.documentElement.classList.add('ui-scroll-lock');
    }
    function bukaKunciScroll() {
        if (!openStack.length) document.documentElement.classList.remove('ui-scroll-lock');
    }

    function openModal(ref, trigger = null) {
        const modal = getModal(ref);
        if (!modal) {
            console.warn('[modal] elemen tidak ditemukan:', ref);
            return;
        }
        injectStyles();
        pasangKeBody(modal);
        if (openStack.includes(modal)) return;

        clearTimeout(modal.__closeTimer);
        modal.__trigger = trigger || document.activeElement;
        modal.dispatchEvent(new CustomEvent('modal:open', { bubbles: true, detail: { trigger: trigger } }));

        modal.hidden = false;
        modal.removeAttribute('aria-hidden');
        openStack.push(modal);
        kunciScroll();

        requestAnimationFrame(() => {
            modal.classList.add('is-open');
            const panel = modal.querySelector('[role="dialog"]');
            const target = modal.querySelector('[data-autofocus]') || (panel && panel.querySelector(FOCUSABLE)) || panel;
            if (target) target.focus({ preventScroll: true });
        });
    }

    function closeModal(ref) {
        const modal = getModal(ref);
        if (!modal) return;
        const idx = openStack.indexOf(modal);
        if (idx === -1) return;

        openStack.splice(idx, 1);
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        bukaKunciScroll();

        modal.__closeTimer = setTimeout(() => {
            modal.hidden = true;
            modal.dispatchEvent(new CustomEvent('modal:close', { bubbles: true }));
        }, 200);

        const balik = modal.__trigger;
        if (balik && typeof balik.focus === 'function' && document.contains(balik)) balik.focus({ preventScroll: true });
    }

    document.addEventListener('click', (e) => {
        const pembuka = e.target.closest('[data-modal-open],[data-modal-target]');
        if (pembuka) {
            e.preventDefault();
            openModal(pembuka.dataset.modalOpen || pembuka.dataset.modalTarget, pembuka);
            return;
        }
        const penutup = e.target.closest('[data-modal-close]');
        if (penutup) {
            const modal = penutup.closest('[data-modal]');
            if (modal) closeModal(modal);
        }
    });

    document.addEventListener('keydown', (e) => {
        const atas = openStack[openStack.length - 1];
        if (!atas) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            closeModal(atas);
            return;
        }

        if (e.key === 'Tab') {
            const items = Array.from(atas.querySelectorAll(FOCUSABLE)).filter((el) => el.offsetParent !== null);
            if (!items.length) return;
            const awal = items[0];
            const akhir = items[items.length - 1];
            if (e.shiftKey && document.activeElement === awal) {
                e.preventDefault();
                akhir.focus();
            } else if (!e.shiftKey && document.activeElement === akhir) {
                e.preventDefault();
                awal.focus();
            }
        }
    });

    /* =====================================================
     * INISIALISASI
     * ===================================================== */
    function flushAntrianToast() {
        const antrian = window.__toastQueue;
        if (Array.isArray(antrian)) {
            antrian.splice(0).forEach((i) => showToast(i.message, i.type, i.duration));
        }
        // Setelah ini, push() dari <x-toast /> langsung ditampilkan
        window.__toastQueue = {
            push: (...items) => items.forEach((i) => showToast(i.message, i.type, i.duration)),
        };
    }

    function init() {
        injectStyles();
        document.querySelectorAll('[data-modal]').forEach((m) => {
            pasangKeBody(m);
            if (!openStack.includes(m)) m.hidden = true;
        });
        flushAntrianToast();
        ubahConfirmLama();
    }

    /* =====================================================
     * KONFIRMASI (pengganti confirm() bawaan browser)
     *   if (!(await konfirmasi('Pesan'))) return;
     *   <form data-confirm="Pesan">  <button data-confirm="Pesan">
     * onsubmit/onclick="return confirm('...')" lama otomatis diubah jadi modal ini.
     * ===================================================== */
    function konfirmasi(pesan, opsi = {}) {
        injectStyles();
        return new Promise((selesai) => {
            const teks = String(pesan == null ? '' : pesan);
            const bahaya = opsi.bahaya != null ? !!opsi.bahaya : /hapus|batalkan|tolak|buang/i.test(teks);

            const modal = document.createElement('div');
            modal.className = 'ui-modal';
            modal.setAttribute('data-modal', '');
            modal.hidden = true;
            modal.innerHTML =
                '<div class="ui-modal__backdrop" data-modal-close></div>' +
                '<div class="ui-modal__panel ui-modal__panel--sm" role="alertdialog" aria-modal="true" aria-labelledby="ui-konf-judul" aria-describedby="ui-konf-isi" tabindex="-1">' +
                '<div class="ui-modal__header"><h2 class="ui-modal__title" id="ui-konf-judul"></h2></div>' +
                '<div class="ui-modal__body"><p id="ui-konf-isi" style="margin:0;font-size:.875rem;line-height:1.55;color:#4b5563;white-space:pre-line;word-break:break-word"></p></div>' +
                '<div class="ui-modal__footer">' +
                '<button type="button" class="ui-btn ui-btn--ghost" data-modal-close></button>' +
                '<button type="button" class="ui-btn ' + (bahaya ? 'ui-btn--danger' : 'ui-btn--primary') + '" data-konf-ya></button>' +
                '</div></div>';
            modal.querySelector('.ui-modal__title').textContent = opsi.judul || 'Konfirmasi';
            modal.querySelector('#ui-konf-isi').textContent = teks;
            const bBatal = modal.querySelector('.ui-btn--ghost');
            const bYa = modal.querySelector('[data-konf-ya]');
            bBatal.textContent = opsi.batal || 'Batal';
            bYa.textContent = opsi.ya || 'Ya, lanjutkan';
            (bahaya ? bBatal : bYa).setAttribute('data-autofocus', '');

            bYa.addEventListener('click', () => { selesai(true); closeModal(modal); });
            modal.addEventListener('modal:close', () => { selesai(false); modal.remove(); });

            document.body.appendChild(modal);
            openModal(modal, document.activeElement);
        });
    }

    // Ubah  onsubmit/onclick="return confirm(...)"  lama menjadi data-confirm.
    function ubahConfirmLama() {
        document.querySelectorAll('[onsubmit*="confirm("],[onclick*="confirm("]').forEach((n) => {
            ['onsubmit', 'onclick'].forEach((a) => {
                const v = n.getAttribute(a);
                if (!v) return;
                const m = v.match(/^\s*return\s+confirm\(([\s\S]*)\)\s*;?\s*$/);
                if (!m) return;
                let pesan;
                try { pesan = (new Function('return (' + m[1] + ');'))(); } catch (e) { return; } // gagal: biarkan confirm bawaan
                n.removeAttribute(a);
                n.setAttribute('data-confirm', String(pesan));
            });
        });
    }

    document.addEventListener('click', (e) => {
        const el = e.target.closest && e.target.closest('[data-confirm]');
        if (!el || el.tagName === 'FORM' || el.__konfLewat) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        konfirmasi(el.getAttribute('data-confirm')).then((ok) => {
            if (!ok) return;
            const form = el.form;
            if (form && (el.tagName === 'BUTTON' || el.tagName === 'INPUT') && el.type !== 'button' && el.type !== 'reset') {
                form.__konfLewat = true;
                try { form.requestSubmit(el); } finally { form.__konfLewat = false; }
            } else if (el.tagName === 'A' && el.href) {
                window.location.href = el.href;
            } else {
                el.__konfLewat = true; el.click(); el.__konfLewat = false;
            }
        });
    }, true);

    document.addEventListener('submit', (e) => {
        const f = e.target;
        if (!f.hasAttribute || !f.hasAttribute('data-confirm') || f.__konfLewat) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        const pengirim = e.submitter || null;
        konfirmasi(f.getAttribute('data-confirm')).then((ok) => {
            if (!ok) return;
            f.__konfLewat = true;
            try { pengirim ? f.requestSubmit(pengirim) : f.requestSubmit(); } finally { f.__konfLewat = false; }
        });
    }, true);

    // alert() bawaan browser diganti toast
    window.alert = (pesan) => showToast(pesan, 'warning');

    window.showToast = showToast;
    window.openModal = openModal;
    window.closeModal = closeModal;
    window.konfirmasi = konfirmasi;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();