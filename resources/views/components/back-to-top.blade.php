{{--
    Tombol "Kembali ke atas" (universal).
--}}
@props(['target' => null, 'offset' => 400])

<style>
    /* Posisi default: di atas bar bayar (HP) */
    #back-to-top {
        bottom: calc(6rem + env(safe-area-inset-bottom)) !important;
        right: max(1.25rem, env(safe-area-inset-right));
    }
    /* Posisi di Desktop: kembali ke pojok bawah kanan */
    @media (min-width: 1024px) {
        #back-to-top {
            bottom: max(1.5rem, env(safe-area-inset-bottom)) !important;
        }
    }
</style>

<button type="button"
        id="back-to-top"
        data-target="{{ $target }}"
        data-offset="{{ $offset }}"
        aria-label="Kembali ke atas"
        title="Kembali ke atas"
        class="group fixed z-[80] flex h-10 w-10 items-center justify-center rounded-full 
               bg-white/80 backdrop-blur-md border border-gold/30 text-gold 
               shadow-[0_4px_12px_rgba(201,154,50,0.15)]
               opacity-0 translate-y-4 pointer-events-none
               transition-all duration-300 ease-out
               hover:bg-gold hover:text-white hover:border-gold hover:-translate-y-1 hover:shadow-[0_6px_16px_rgba(201,154,50,0.3)]
               active:scale-95
               focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold
               sm:h-11 sm:w-11">

    {{-- Ikon Panah Atas (Tanpa Batang) --}}
    <svg class="h-5 w-5 transition-transform duration-300 group-hover:-translate-y-0.5 sm:h-6 sm:w-6"
         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M5 15l7-7 7 7"/>
    </svg>
</button>

<script>
(function () {
    var btn = document.getElementById('back-to-top');
    if (!btn || btn.dataset.init) return;
    btn.dataset.init = '1';

    var sel    = btn.dataset.target;
    var offset = parseInt(btn.dataset.offset, 10) || 400;
    var area   = sel ? document.querySelector(sel) : null;
    var scroller = area || window;
    var tick = false;

    function posisi() {
        return area ? area.scrollTop : (window.pageYOffset || document.documentElement.scrollTop);
    }
    
    function perbarui() {
        var y = posisi();
        var tampil = y > offset;
        
        ['opacity-0', 'translate-y-4', 'pointer-events-none'].forEach(function (k) {
            btn.classList.toggle(k, !tampil);
        });
        tick = false;
    }

    scroller.addEventListener('scroll', function () {
        if (!tick) { tick = true; requestAnimationFrame(perbarui); }
    }, { passive: true });

    btn.addEventListener('click', function () {
        var kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        scroller.scrollTo({ top: 0, behavior: kurangiGerak ? 'auto' : 'smooth' });
    });

    perbarui();
})();
</script>