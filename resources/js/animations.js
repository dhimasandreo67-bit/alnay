// resources/js/animations.js

document.documentElement.classList.add('pd-js');

// ============================================
// Format Rupiah (didefinisikan dulu)
// ============================================
export function formatRupiah(n) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
}

// ============================================
// Tween harga
// ============================================
export function tweenHarga(el, dari, target, dur = 450) {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || dari === target) {
        el.textContent = formatRupiah(target);
        return;
    }
    const mulai = performance.now();
    const langkah = (t) => {
        const p = Math.min(1, (t - mulai) / dur);
        const e = 1 - Math.pow(1 - p, 3);
        el.textContent = formatRupiah(Math.round(dari + (target - dari) * e));
        if (p < 1) requestAnimationFrame(langkah);
    };
    requestAnimationFrame(langkah);
}

// ============================================
// Scroll reveal
// ============================================
export function initScrollReveal() {
    const els = document.querySelectorAll('.pd-reveal');
    if (!('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-in'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((en) => {
            if (en.isIntersecting) {
                en.target.classList.add('is-in');
                io.unobserve(en.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    els.forEach((el) => io.observe(el));
}

// ============================================
// Auto-init
// ============================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollReveal);
} else {
    initScrollReveal();
}

// Re-run saat ada elemen baru (untuk card yang dirender dinamis)
// Gunakan MutationObserver untuk detect card baru
if ('MutationObserver' in window) {
    const mo = new MutationObserver(() => {
        initScrollReveal();
    });
    mo.observe(document.body, { childList: true, subtree: true });
}