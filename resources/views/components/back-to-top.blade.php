{{--
    Tombol "Kembali ke atas" (universal).
    Simpan di: resources/views/components/back-to-top.blade.php
    Pakai    : <x-back-to-top />                       (halaman yang scroll-nya di window)
               <x-back-to-top target="#konten-utama" /> (layout yang scroll-nya di dalam container, mis. admin/owner)
    Opsional : offset="400" -> tombol muncul setelah scroll sejauh 400px
--}}
@props(['target' => null, 'offset' => 400])

<button type="button"
        id="back-to-top"
        data-target="{{ $target }}"
        data-offset="{{ $offset }}"
        aria-label="Kembali ke atas"
        title="Kembali ke atas"
        style="bottom: max(1.25rem, env(safe-area-inset-bottom)); right: max(1.25rem, env(safe-area-inset-right));
               background: linear-gradient(160deg, #f0cf78 0%, #d9a63c 55%, #c99a32 100%);
               box-shadow: 0 6px 16px rgba(0,0,0,.28), 0 0 18px rgba(201,154,50,.45);"
        class="group fixed z-[60] flex h-12 w-12 items-center justify-center rounded-full border border-[#f6e2a3]/70 text-[#0a0a0a]
               opacity-0 translate-y-6 scale-90 pointer-events-none
               transition-all duration-300 ease-out
               hover:-translate-y-1 hover:scale-110
               active:scale-95
               focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#c99a32]
               sm:h-14 sm:w-14">

    {{-- Panah --}}
    <svg class="relative h-6 w-6 transition-transform duration-300 group-hover:-translate-y-0.5 sm:h-7 sm:w-7"
         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8"
         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 19V5"/><path d="M5 12l7-7 7 7"/>
    </svg>
</button>

<script>
(function () {
    var btn = document.getElementById('back-to-top');
    if (!btn || btn.dataset.init) return;      // cegah dobel-init
    btn.dataset.init = '1';

    var sel    = btn.dataset.target;
    var offset = parseInt(btn.dataset.offset, 10) || 400;
    var area   = sel ? document.querySelector(sel) : null;   // jika target tidak ketemu -> pakai window
    var scroller = area || window;
    var tick = false;

    function posisi() {
        return area ? area.scrollTop : (window.pageYOffset || document.documentElement.scrollTop);
    }
    function perbarui() {
        var y = posisi();
        var tampil = y > offset;
        ['opacity-0', 'translate-y-6', 'scale-90', 'pointer-events-none'].forEach(function (k) {
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