@extends('layouts.public')

@section('title', 'Testimoni - ' . config('alnay.brand.nama'))
@section('meta_description', 'Ulasan pengantin dan klien yang sudah menyewa atau membeli busana adat di ' . config('alnay.brand.nama'))

@section('content')

@php
    $daftar = $ulasan instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($ulasan->items())
        : collect($ulasan);

    $n = $daftar->count();

    $ulang = $n > 0 ? (int) ceil(8 / $n) : 0;
    $set = $ulang > 0
        ? collect(range(1, $ulang))->flatMap(fn () => $daftar)->values()
        : collect();

    $durasi = max(40, $set->count() * 7);
@endphp

<style>
    .tm-glow {
        background:
            radial-gradient(60% 80% at 85% 0%, rgba(201, 154, 50, .22), transparent 65%),
            radial-gradient(40% 60% at 0% 100%, rgba(201, 154, 50, .10), transparent 70%);
    }

    .tm-viewport {
        overflow: hidden;
        -webkit-mask-image: linear-gradient(to right, transparent, #000 7%, #000 93%, transparent);
                mask-image: linear-gradient(to right, transparent, #000 7%, #000 93%, transparent);
    }
    .tm-track {
        display: flex;
        width: max-content;
        animation: tm-jalan var(--dur, 60s) linear infinite;
        will-change: transform;
    }
    .tm-viewport:hover .tm-track,
    .tm-viewport:focus-within .tm-track {
        animation-play-state: paused;
    }
    .tm-group {
        display: flex;
        gap: 1rem;
        padding-right: 1rem;
    }
    @keyframes tm-jalan {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }

    .tm-card {
        position: relative;
        width: 270px;
        flex-shrink: 0;
        transition: transform .4s cubic-bezier(0.22, 0.8, 0.3, 1), box-shadow .4s ease, border-color .4s ease;
        background: #fff;
        border-radius: 1.25rem;
        border: 1px solid #f3f4f6;
    }
    @media (min-width: 768px) { .tm-card { width: 310px; } }
    
    .tm-card:hover {
        transform: translateY(-4px);
        border-color: rgba(201, 154, 50, .4);
        box-shadow: 0 14px 28px -14px rgba(21, 35, 61, .18);
    }
    
    .tm-quote {
        position: absolute;
        top: .5rem;
        right: 1.5rem;
        font-size: 5rem;
        line-height: 1;
        color: rgba(201, 154, 50, .08);
        pointer-events: none;
        user-select: none;
    }

    @media (prefers-reduced-motion: reduce) {
        .tm-viewport { overflow-x: auto; -webkit-mask-image: none; mask-image: none; }
        .tm-track { animation: none; }
        .tm-group[aria-hidden="true"] { display: none; }
        .tm-card, .tm-card:hover { transition: none; transform: none; }
    }

    /* ---------- animasi halus ---------- */
    @keyframes tm-pop   { 0% { opacity: 0; transform: scale(.4) rotate(-25deg); } 65% { transform: scale(1.25) rotate(8deg); } 100% { opacity: 1; transform: none; } }
    @keyframes tm-fill  { from { transform: scaleX(0); } to { transform: scaleX(1); } }
    @keyframes tm-rise  { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: none; } }
    @keyframes tm-twinkle { 0%, 100% { filter: drop-shadow(0 0 0 rgba(201,154,50,0)); } 50% { filter: drop-shadow(0 0 5px rgba(201,154,50,.8)); } }

    .tm-rise { animation: tm-rise .8s cubic-bezier(.22,.8,.3,1) both; animation-delay: var(--d, 0s); }
    .tm-bar  { transform-origin: left; animation: tm-fill 1.1s cubic-bezier(.22,.8,.3,1) both; animation-delay: var(--d, .3s); }

    /* bintang: muncul berurutan, lalu berkilau berurutan saat kartu di-hover */
    .tm-star { display: inline-flex; transition: transform .35s cubic-bezier(.3,1.5,.5,1), color .3s ease; }
    .tm-star.on { animation: tm-pop .55s cubic-bezier(.3,1.4,.5,1) both; animation-delay: calc(var(--i) * .07s + .15s); }
    .tm-card:hover .tm-star.on { animation: tm-twinkle 1.1s ease-in-out calc(var(--i) * .09s) 1; transform: scale(1.22) rotate(-6deg); transition-delay: calc(var(--i) * .05s); }

    .tm-chip { transition: transform .25s ease, box-shadow .25s ease, background-color .25s ease, color .25s ease, border-color .25s ease; }
    .tm-chip:hover { transform: translateY(-2px); }
    .tm-chip:active { transform: scale(.96); }

    .tm-avatar { transition: transform .35s cubic-bezier(.3,1.4,.5,1), box-shadow .35s ease; }
    .tm-card:hover .tm-avatar { transform: scale(1.08); box-shadow: 0 0 0 3px rgba(201,154,50,.25); }

    .tm-viewport { transition: -webkit-mask-position .3s; }
    .tm-track { animation-timing-function: linear; }

    @media (prefers-reduced-motion: reduce) {
        .tm-rise, .tm-bar, .tm-star.on, .tm-card:hover .tm-star.on { animation: none !important; }
        .tm-card:hover .tm-star.on { transform: none; }
    }

    /* ---------- polish kartu ---------- */
    .tm-card { background: #fff; overflow: hidden; border-radius: 1rem; box-shadow: none; }
    .tm-quote { top: .5rem; right: 1rem; font-size: 3.25rem; color: rgba(201,154,50,.12); transition: transform .5s ease, color .5s ease; }
    .tm-card:hover .tm-quote { transform: translateY(-3px) rotate(-6deg); color: rgba(201,154,50,.28); }
    .tm-text { font-size: .8125rem; line-height: 1.65; color: #4b5563; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; min-height: 3.2rem; }
    .tm-score { font-size: .7rem; font-weight: 700; color: #a37b2a; background: #fbf6e9; border: 1px solid #f1e3bd; border-radius: 999px; padding: .1rem .5rem; }
    .tm-verif { display: inline-flex; align-items: center; gap: .25rem; font-size: 10px; font-weight: 600; color: #047857; }
    .tm-thumb { transition: transform .4s cubic-bezier(.3,1.4,.5,1), box-shadow .4s ease; }
    .tm-thumb:hover { transform: scale(1.1) rotate(2deg); box-shadow: 0 8px 18px -6px rgba(21,35,61,.35); }
    .tm-card:hover .tm-avatar { box-shadow: 0 0 0 3px rgba(201,154,50,.3); }

    /* ---------- bintang gold melayang di hero (ringan: hanya transform + opacity) ---------- */
    .tm-stars { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; contain: strict; }
    .tm-sparkle {
        position: absolute;
        left: var(--x); top: var(--y);
        width: var(--s); height: var(--s);
        opacity: 0;
        animation: tm-melayang var(--fd) ease-in-out var(--dl) infinite;
        will-change: transform, opacity;
    }
    /* glow statis (bukan filter), jadi tidak dihitung ulang tiap frame */
    .tm-sparkle::before {
        content: "";
        position: absolute;
        inset: -90%;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(230,200,119,.55) 0%, rgba(201,154,50,.18) 40%, transparent 70%);
    }
    .tm-sparkle svg { position: relative; width: 100%; height: 100%; display: block; color: #f3d98b; }
    @keyframes tm-melayang {
        0%, 100% { transform: translate3d(0, 0, 0) scale(.5);        opacity: 0; }
        30%      { transform: translate3d(10px, -22px, 0) scale(1.1); opacity: 1; }
        60%      { transform: translate3d(-8px, -40px, 0) scale(.85); opacity: .6; }
        80%      { transform: translate3d(-12px, -52px, 0) scale(1);  opacity: .9; }
    }
    .tm-stars.is-paused .tm-sparkle { animation-play-state: paused; }
    @media (max-width: 767px) { .tm-sparkle:nth-child(n+8) { display: none; } }
    @media (prefers-reduced-motion: reduce) {
        .tm-sparkle { animation: none; opacity: .5; }
    }
</style>

{{-- 1. HERO + RINGKASAN RATING --}}
<section class="tm-glow relative overflow-hidden text-white" style="background-color: #1a1610;">
    {{-- Bintang gold shine melayang --}}
    <div class="tm-stars" aria-hidden="true">
        @for ($s = 0; $s < 14; $s++)
            <span class="tm-sparkle"
                  style="--x: {{ ($s * 37 + 11) % 96 }}%;
                         --y: {{ ($s * 53 + 7) % 90 }}%;
                         --s: {{ 8 + ($s * 7) % 12 }}px;
                         --fd: {{ 8 + ($s * 3) % 7 }}s;
                         --dl: -{{ ($s * 17) % 10 }}s;">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c.6 6.4 5.6 11.4 12 12-6.4.6-11.4 5.6-12 12-.6-6.4-5.6-11.4-12-12C6.4 11.4 11.4 6.4 12 0z"/></svg>
            </span>
        @endfor
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 py-14 md:py-20 grid gap-10 md:grid-cols-2 md:items-center">
        <div class="tm-rise">
            <div class="flex items-center gap-3">
                <span class="h-px w-10 bg-gradient-to-r from-transparent to-gold"></span>
                <p class="text-[11px] font-semibold uppercase tracking-[0.3em]" style="color: #e6c877;">{{ config('alnay.brand.nama') }}</p>
            </div>
            <h1 class="font-display mt-4 text-3xl md:text-5xl font-bold leading-tight">Testimoni<br>&amp; Ulasan Klien</h1>
            <p class="mt-4 max-w-md text-sm leading-relaxed text-white/70">
                Terimaksih kepada semua yang sudah mempercayakan momen istimewanya pada fashion busana kami.
            </p>
        </div>

        <div class="tm-rise rounded-2xl border border-white/10 bg-white/[0.07] p-6" style="--d: .15s">
            <div class="flex items-center gap-6">
                <div class="shrink-0 text-center">
                    <p class="font-display text-5xl font-bold text-gold-soft">{{ $totalRating > 0 ? number_format($rataRata, 1, ',', '') : '-' }}</p>
                    <div class="mt-2 flex justify-center gap-0.5" aria-hidden="true">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="tm-star on" style="--i: {{ $i }}">
                                <svg class="w-4 h-4 {{ $i <= round($rataRata) ? 'text-[#e6c877]' : 'text-white/20' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </span>
                        @endfor
                    </div>
                    <p class="mt-2 text-xs text-white/60">{{ number_format($totalRating, 0, ',', '.') }} ulasan</p>
                </div>

                <div class="flex-1 space-y-1.5">
                    @foreach ([5, 4, 3, 2, 1] as $b)
                        @php
                            $jumlah = $sebaran[$b] ?? 0;
                            $persen = $totalRating > 0 ? round($jumlah / $totalRating * 100) : 0;
                        @endphp
                        <a href="{{ route('testimoni', ['bintang' => $b]) }}" class="flex items-center gap-2 text-[11px] text-white/70 transition hover:text-white">
                            <span class="w-3 text-right">{{ $b }}</span>
                            <span style="color: #e6c877;">★</span>
                            <span class="h-1.5 flex-1 overflow-hidden rounded-full" style="background-color: rgba(255,255,255,0.1);">
                                <span class="tm-bar block h-full rounded-full" style="background-color: #c99a32; width: {{ $persen }}%; --d: {{ .3 + (5 - $b) * .1 }}s"></span>
                            </span>
                            <span class="w-8 text-right tabular-nums">{{ $jumlah }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>


    <script>
        (function () {
            var el = document.querySelector('.tm-stars');
            if (!el || !('IntersectionObserver' in window)) return;
            var visible = true;
            function sync() { el.classList.toggle('is-paused', !visible || document.hidden); }
            new IntersectionObserver(function (e) { visible = e[0].isIntersecting; sync(); }).observe(el);
            document.addEventListener('visibilitychange', sync);
        })();
    </script>

{{-- 2. FILTER + ULASAN BERJALAN --}}
<section class="py-10 md:py-14 overflow-hidden">

    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-wrap items-center justify-center gap-2 mb-10">
            <a href="{{ route('testimoni') }}"
               class="tm-chip rounded-full px-5 py-2.5 text-xs font-bold
                      {{ $bintang === null ? 'bg-[#c99a32] text-white shadow-md' : 'bg-white border border-gray-200 text-gray-500 hover:border-[#c99a32] hover:text-[#c99a32]' }}">
                Semua Ulasan
            </a>
            @foreach ([5, 4, 3, 2, 1] as $b)
                <a href="{{ route('testimoni', ['bintang' => $b]) }}"
                   class="tm-chip rounded-full px-5 py-2.5 text-xs font-bold flex items-center gap-1.5
                          {{ $bintang === $b ? 'bg-[#c99a32] text-white shadow-md' : 'bg-white border border-gray-200 text-gray-500 hover:border-[#c99a32] hover:text-[#c99a32]' }}">
                    {{ $b }} <span class="text-[10px]">★</span>
                </a>
            @endforeach
        </div>
    </div>

    @if ($n > 0)
        <div class="tm-viewport mt-4 py-4" role="region" aria-label="Ulasan pelanggan">
            <div class="tm-track" style="--dur: {{ $durasi }}s">

                @foreach ([false, true] as $salinan)
                    <div class="tm-group" @if ($salinan) aria-hidden="true" @endif>
                        @foreach ($set as $r)
                                                        @php
                                // 1. Ambil data user
                                $userData = $r->user ?? null;
                                $nama = $userData->name ?? 'Pelanggan';
                                $inisial = strtoupper(mb_substr($nama, 0, 2));
                                
                                // 2. Panggil foto profil (Persis seperti di Edit Profil)
                                $fotoUser = $userData->foto_profil ?? null;
                                
                                // 3. Tentukan URL gambar (Link Google atau Storage Lokal)
                                $urlFotoUser = null;
                                if ($fotoUser) {
                                    $urlFotoUser = \Illuminate\Support\Str::startsWith($fotoUser, ['http://', 'https://']) 
                                        ? $fotoUser 
                                        : asset('storage/' . $fotoUser);
                                }

                                // 4. Ambil foto produk
                                $fotoProduk = $r->produk->foto_produk ?? null;
                                if (is_array($fotoProduk)) $fotoProduk = $fotoProduk[0] ?? null;
                            @endphp

                            <article class="tm-card flex flex-col p-5">
                                <span class="tm-quote font-display" aria-hidden="true">&ldquo;</span>

                                {{-- Bintang + skor --}}
                                <div class="relative z-10 mb-3 flex items-center gap-2" aria-label="{{ $r->rating }} dari 5 bintang">
                                    <div class="flex gap-1">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <span class="tm-star {{ $star <= $r->rating ? 'on' : '' }}" style="--i: {{ $star }}">
                                                <svg class="w-3.5 h-3.5 {{ $star <= $r->rating ? 'text-[#c99a32]' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            </span>
                                        @endfor
                                    </div>
                                </div>

                                {{-- Teks ulasan --}}
                                <p class="tm-text relative flex-1">{{ $r->ulasan }}</p>

                                {{-- Profil & foto produk --}}
                                <div class="relative mt-5 flex items-center justify-between gap-3 border-t border-gray-100 pt-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="tm-avatar relative h-9 w-9 shrink-0 rounded-full">
                                            <div class="absolute inset-0 flex items-center justify-center rounded-full border border-amber-100 bg-[#fbf6e9] text-[11px] font-bold text-[#c99a32]">{{ $inisial }}</div>
                                            @if ($urlFotoUser)
                                                <img src="{{ $urlFotoUser }}" alt="{{ $nama }}" loading="lazy" referrerpolicy="no-referrer"
                                                     onerror="this.remove()"
                                                     class="absolute inset-0 h-full w-full rounded-full border border-gray-200 object-cover shadow-sm">
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <p class="truncate text-[13px] font-semibold text-gray-900">{{ $nama }}</p>
                                            <p class="truncate text-[11px] text-gray-400">
                                                @if ($r->produk){{ $r->produk->nama }} &middot; @endif{{ $r->tanggal?->copy()->locale('id')->translatedFormat('F Y') }}
                                            </p>
                                        </div>
                                    </div>

                                    @if ($fotoProduk && $r->produk)
                                        <a href="{{ route('produk.detail', $r->produk->sku) }}" @if ($salinan) tabindex="-1" @endif
                                           title="Lihat {{ $r->produk->nama }}" class="tm-thumb shrink-0">
                                            <img src="{{ asset('storage/' . $fotoProduk) }}" alt="{{ $r->produk->nama }}" loading="lazy"
                                                 onerror="this.closest('a').remove()"
                                                 class="h-10 w-10 rounded-lg border border-gray-100 object-cover">
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endforeach

            </div>
        </div>
    @else
        <div class="max-w-7xl mx-auto px-4 mt-8">
            <x-empty-state
                title="Belum ada testimoni"
                :message="$bintang ? 'Belum ada ulasan dengan ' . $bintang . ' bintang.' : 'Jadilah yang pertama membagikan pengalaman Anda.'" />
        </div>
    @endif
</section>

@endsection