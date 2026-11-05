@extends('layouts.public')

@section('title', 'ALNAY.ID — Jas & Gown Berbudaya')

@section('content')

    {{-- Sprite ikon --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M13 6l6 6-6 6" />
            </symbol>
            <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 13l4 4L19 7" />
            </symbol>
            <symbol id="i-star" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
            </symbol>
            <symbol id="i-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
            </symbol>
            <symbol id="i-dot" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="12" r="6" />
            </symbol>
        </defs>
    </svg>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    {{-- ============================================================
     CUSTOM STYLES
     ============================================================ --}}
    <style>
        :root {
            --gold: #c99a32;
            --gold-soft: #e6c877;
            --gold-light: #f5e8c4;
            --gold-bg: #fbf6e9;
            --ink: #15233d;
            --dark: #17140f;
            --dark-2: #1a1610;
            --muted: #6b7280;
            --line: #f0ece4;
            --line-soft: #f7f4ed;
            --ease: cubic-bezier(0.22, 0.8, 0.3, 1);
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);

            --shadow-xs: 0 1px 2px rgba(21, 35, 61, .04);
            --shadow-sm: 0 2px 8px rgba(21, 35, 61, .05), 0 4px 12px rgba(21, 35, 61, .03);
            --shadow-md: 0 4px 16px rgba(21, 35, 61, .06), 0 8px 24px rgba(21, 35, 61, .04);
            --shadow-lg: 0 12px 32px -8px rgba(21, 35, 61, .10), 0 20px 48px -12px rgba(21, 35, 61, .08);
            --shadow-xl: 0 24px 56px -12px rgba(21, 35, 61, .14), 0 40px 80px -20px rgba(21, 35, 61, .10);
            --shadow-gold: 0 8px 24px -6px rgba(201, 154, 50, .30);
            --shadow-gold-lg: 0 16px 40px -8px rgba(201, 154, 50, .38);
        }

        html { scroll-behavior: smooth; }
        html, body { max-width: 100%; overflow-x: clip; }
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .font-display { font-family: 'Playfair Display', serif; }

        /* ---------- Ikon ---------- */
        .ico { width: 1em; height: 1em; flex-shrink: 0; display: inline-block; vertical-align: -0.125em; }
        .ico-dot { width: 8px; height: 8px; flex-shrink: 0; }
        .ico-check { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; color: var(--gold); }
        .stars { display: inline-flex; gap: 2px; font-size: 16px; }
        .stars .off { color: #e5dfd0; }
        .stars .on { color: var(--gold); }

        .card-shadow { min-width: 0; }
        .card-shadow h3, .card-shadow p,
        section h1, section h2, section h3 { overflow-wrap: anywhere; }

        /* ---------- Header section seragam (lebih berkarakter) ---------- */
        .sec-eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gold);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .sec-eyebrow::before {
            content: '';
            width: 22px;
            height: 1px;
            background: linear-gradient(90deg, var(--gold), transparent);
            display: inline-block;
        }
        .sec-eyebrow.center::before,
        .sec-eyebrow.center::after {
            content: '';
            width: 22px;
            height: 1px;
            display: inline-block;
        }
        .sec-eyebrow.center::before { background: linear-gradient(90deg, transparent, var(--gold)); }
        .sec-eyebrow.center::after { background: linear-gradient(90deg, var(--gold), transparent); }

        .sec-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--ink);
            font-size: 1.5rem;
            line-height: 1.25;
            letter-spacing: -0.005em;
        }
        @media (min-width: 768px) { .sec-title { font-size: 2rem; } }

        .sec-title::after {
            content: '';
            display: block;
            width: 46px;
            height: 3px;
            margin-top: 14px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--gold) 0%, var(--gold-soft) 70%, transparent 100%);
        }
        .sec-title.center::after { margin-left: auto; margin-right: auto; }

        .sec-sub { margin-top: 10px; font-size: 0.875rem; color: var(--muted); }

        .link-more {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gold);
            padding: 6px 10px;
            border-radius: 999px;
            transition: gap .3s var(--ease), background-color .3s ease, opacity .3s ease;
        }
        .link-more .ico { width: 16px; height: 16px; transition: transform .3s var(--ease); }
        .link-more:hover { gap: 10px; background-color: var(--gold-bg); }
        .link-more:hover .ico { transform: translateX(2px); }

        /* ---------- Tombol dengan shine effect ---------- */
        .btn-smooth {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            transition: transform .35s var(--ease), box-shadow .35s var(--ease),
                        background-color .3s ease, border-color .3s ease;
        }
        .btn-smooth::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255, 255, 255, .22) 50%, transparent 70%);
            transform: translateX(-110%);
            transition: transform .65s var(--ease);
            pointer-events: none;
        }
        .btn-smooth:hover::before { transform: translateX(110%); }
        .btn-smooth:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-gold);
        }
        .btn-smooth:active { transform: translateY(0) scale(.98); }

        /* ---------- Animasi masuk hero ---------- */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .hero-in {
            opacity: 0;
            animation: fade-up .9s var(--ease-out) forwards;
            animation-delay: calc(var(--d, 0) * 1s);
        }

        @keyframes float-soft {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }

        /* ---------- Hero background (ukuran tetap) ---------- */
        .hero-bg {
            position: relative;
            background:
                radial-gradient(900px 420px at 85% 8%, rgba(201, 154, 50, .20), transparent 62%),
                radial-gradient(620px 320px at 0% 100%, rgba(201, 154, 50, .09), transparent 62%),
                var(--dark);
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(201, 154, 50, .10) 1px, transparent 1px);
            background-size: 26px 26px;
            -webkit-mask-image: radial-gradient(ellipse at 72% 30%, black 0%, transparent 65%);
            mask-image: radial-gradient(ellipse at 72% 30%, black 0%, transparent 65%);
            pointer-events: none;
            opacity: .8;
        }
        .hero-bg::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 154, 50, .35), transparent);
            pointer-events: none;
        }

        /* ---------- Carousel (ukuran TIDAK berubah) ---------- */
        .stage {
            position: relative;
            width: 100%;
            height: 280px;
        }
        @media (min-width: 640px) { .stage { height: 420px; } }

        .slide {
            --x: 0%; --s: 1;
            position: absolute;
            top: 0; left: 50%;
            width: 78%; height: 100%;
            transform: translateX(calc(-50% + var(--x))) scale(var(--s));
            opacity: 0;
            pointer-events: none;
            transition: transform .75s var(--ease-out), opacity .75s var(--ease-out), filter .75s var(--ease-out);
            will-change: transform, opacity;
        }
        @media (min-width: 640px) { .slide { width: 46%; } }

        .slide img {
            display: block; width: 100%; height: 100%;
            object-fit: cover; border-radius: 20px;
        }
        .slide.is-active { --x: 0%; --s: 1; opacity: 1; z-index: 3; pointer-events: auto; }
        .slide.is-active img {
            border: 3px solid var(--gold);
            box-shadow: 0 28px 56px -12px rgba(0, 0, 0, .60), 0 0 0 1px rgba(201, 154, 50, .2);
        }
        .slide.is-prev, .slide.is-next { opacity: 0; z-index: 1; --s: .82; filter: blur(2px); }
        .slide.is-prev { --x: -58%; }
        .slide.is-next { --x: 58%; }
        @media (min-width: 640px) {
            .slide.is-prev, .slide.is-next { opacity: .5; --x: 74%; }
            .slide.is-prev { --x: -74%; }
        }

        .slide-caption {
            position: absolute;
            left: 14px; right: 14px; bottom: 14px;
            padding: 10px 14px;
            border-radius: 12px;
            color: #fff;
            background: rgba(10, 8, 5, .58);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(201, 154, 50, .18);
            opacity: 0;
            transform: translateY(10px);
            transition: opacity .6s var(--ease-out) .25s, transform .6s var(--ease-out) .25s;
        }
        .slide.is-active .slide-caption { opacity: 1; transform: translateY(0); }
        .slide-caption p:first-child {
            font-size: 10px; font-weight: 600; letter-spacing: .08em;
            text-transform: uppercase; color: var(--gold-soft);
        }
        .slide-caption p:last-child { margin-top: 2px; font-size: 11px; color: #d6d3cc; }

        .carousel-btn {
            transition: transform .3s var(--ease), background-color .3s ease, box-shadow .3s ease;
            backdrop-filter: blur(6px);
            box-shadow: var(--shadow-sm);
        }
        .carousel-btn:hover {
            transform: translateY(-50%) scale(1.1);
            background-color: rgba(255, 255, 255, .38) !important;
            box-shadow: var(--shadow-md);
        }

        .dot {
            height: 6px; width: 6px; border-radius: 999px;
            background: rgba(255, 255, 255, .3);
            transition: width .4s var(--ease), background-color .4s ease;
        }
        .dot.on { width: 22px; background: var(--gold); }

        /* ---------- Kategori bulat dengan ring emas ---------- */
        .cat-circle {
            border: 2px solid var(--line-soft);
            transition: border-color .3s ease, box-shadow .4s var(--ease), transform .4s var(--ease);
            position: relative;
        }
        .cat-circle::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 1.5px solid transparent;
            transition: border-color .4s ease, transform .4s var(--ease);
            pointer-events: none;
        }
        .cat-item:hover .cat-circle {
            border-color: var(--gold);
            box-shadow: var(--shadow-gold), inset 0 0 0 2px #fff;
            transform: scale(1.02);
        }
        .cat-item:hover .cat-circle::after { border-color: var(--gold-soft); }
        .cat-item:hover .cat-name { color: var(--gold); }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* ---------- Kartu umum ---------- */
        .card-soft {
            border-radius: 22px;
            border: 1px solid var(--line);
            background: #fff;
            box-shadow: var(--shadow-xs);
            transition: transform .45s var(--ease-out), box-shadow .45s var(--ease-out),
                        border-color .3s ease;
        }
        .card-soft:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: #ece4d2;
        }

        .quote-mark {
            position: absolute; top: 8px; right: 20px;
            font-family: 'Playfair Display', serif;
            font-size: 72px; line-height: 1; color: var(--gold);
            opacity: .12; pointer-events: none;
        }

        /* ---------- Event banner ---------- */
        .event-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--dark-2) 0%, #221c14 55%, var(--dark-2) 100%);
            border: 1px solid rgba(201, 154, 50, .12);
        }
        .event-card::before {
            content: '';
            position: absolute;
            inset: -40% -10% auto auto;
            width: 460px; height: 460px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201, 154, 50, .28), transparent 65%);
            transition: transform .9s var(--ease-out);
        }
        .event-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(201, 154, 50, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(201, 154, 50, .04) 1px, transparent 1px);
            background-size: 40px 40px;
            -webkit-mask-image: radial-gradient(ellipse at 20% 100%, black, transparent 70%);
            mask-image: radial-gradient(ellipse at 20% 100%, black, transparent 70%);
            pointer-events: none;
            opacity: .6;
        }
        .event-card:hover::before { transform: scale(1.25) translate(-20px, 20px); }

        /* ---------- Peta ---------- */
        .pin-bounce { animation: bounce-marker 2s infinite ease-in-out; }
        @keyframes bounce-marker {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }
        #peta-alnay {
            position: relative !important;
            z-index: 1 !important;
            isolation: isolate !important;
            background: #efe9dc;
        }
        #peta-alnay .leaflet-pane,
        #peta-alnay .leaflet-top,
        #peta-alnay .leaflet-bottom,
        #peta-alnay .leaflet-control { z-index: 1 !important; }

        /* ---------- Scroll reveal ---------- */
        .reveal {
            opacity: 0;
            transform: translateY(28px) scale(.99);
            transition: opacity .8s var(--ease-out), transform .8s var(--ease-out);
            transition-delay: var(--delay, 0s);
        }
        .reveal.is-visible { opacity: 1; transform: translateY(0) scale(1); }
        .stagger > .reveal { transition-duration: .75s; }

        /* ---------- Garis pemisah halus antar section ---------- */
        .section-rule {
            max-width: 260px;
            margin: 0 auto;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--line) 20%, var(--line) 80%, transparent);
        }
        .section-rule::after {
            content: '';
            display: block;
            width: 6px; height: 6px;
            margin: -3px auto 0;
            border-radius: 50%;
            background: var(--gold-soft);
            box-shadow: 0 0 0 4px rgba(230, 200, 119, .18);
        }

        /* ---------- Area latar lembut ---------- */
        .section-soft {
            background: linear-gradient(180deg, #fdfbf7 0%, #ffffff 100%);
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal, .hero-in {
                opacity: 1 !important; transform: none !important;
                transition: none !important; animation: none !important;
            }
            .slide, .slide-caption { transition: none !important; }
            .pin-bounce { animation: none; }
            html { scroll-behavior: auto; }
        }
    </style>

    <noscript>
        <style>
            .reveal, .hero-in { opacity: 1 !important; transform: none !important; animation: none !important; }
            .slide.is-active { opacity: 1; }
        </style>
    </noscript>

    {{-- ============================================================
     1. HERO SECTION (ukuran tetap)
     ============================================================ --}}
    <section class="hero-bg overflow-hidden">
        <div class="relative max-w-7xl mx-auto p-4 sm:p-10 lg:flex lg:items-center lg:gap-10 lg:py-20">

            {{-- KIRI: Teks --}}
            <div class="min-w-0 text-white lg:w-1/2 mb-10 lg:mb-0">
                <span class="hero-in inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-[10px] font-semibold backdrop-blur-sm border border-white/10"
                    style="--d:.05; color: var(--gold-soft);">
                    <svg class="ico-dot" aria-hidden="true"><use href="#i-dot" /></svg>
                    Jas &amp; Gown Berbudaya — Elegan dalam Setiap Momen
                </span>

                <h1 class="hero-in font-display mt-4 text-[28px] leading-tight font-bold min-[375px]:text-[32px] sm:text-[40px] lg:text-[44px]"
                    style="--d:.18;">
                    Jas &amp; Gown Berbudaya, Dicipta untuk
                    <span class="relative inline-block">
                        Momen Abadi
                        <span class="absolute left-0 -bottom-1 h-[3px] w-full rounded-full"
                            style="background: linear-gradient(90deg, var(--gold), transparent);"></span>
                    </span> Anda.
                </h1>

                <p class="hero-in mt-4 max-w-[460px] text-sm leading-relaxed" style="--d:.32; color: #cfcac0;">
                    Temukan koleksi Fashion Etnik pada ALNAY.ID dengan sentuhan elegan dan modern. Tersedia untuk
                    pembelian maupun penyewaan untuk melengkapi berbagai momen istimewa Anda.
                </p>

                <div class="hero-in mt-7 flex flex-col sm:flex-row gap-3" style="--d:.46;">
                    <a href="{{ route('koleksi.index') }}"
                        class="btn-smooth inline-flex items-center justify-center px-6 py-3.5 text-xs font-bold text-white tracking-wide"
                        style="background: linear-gradient(135deg, var(--gold) 0%, #b8860b 100%);">
                        Jelajahi Koleksi Produk
                    </a>
                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener"
                        class="btn-smooth inline-flex items-center justify-center gap-2 border border-white/25 px-6 py-3.5 text-xs font-bold text-white hover:bg-white/10 hover:border-white/40">
                        <svg class="ico text-[15px]" aria-hidden="true"><use href="#i-chat" /></svg>
                        Konsultasi WhatsApp
                    </a>
                </div>
            </div>

            {{-- KANAN: Carousel 3 Foto --}}
            <div class="hero-in relative w-full min-w-0 lg:w-1/2" style="--d:.3;">
                <button type="button" onclick="prevPhoto()" aria-label="Foto sebelumnya"
                    class="carousel-btn absolute left-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 text-white rounded-full p-2 border border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="stage" id="carousel-container"></div>

                <button type="button" onclick="nextPhoto()" aria-label="Foto berikutnya"
                    class="carousel-btn absolute right-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 text-white rounded-full p-2 border border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div class="mt-5 flex items-center justify-center gap-2" id="carousel-dots"></div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     2. KATEGORI
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 mt-14 md:mt-20">
        <div class="overflow-x-auto sm:overflow-visible hide-scrollbar py-4 -mx-4 px-4 sm:mx-0 sm:px-0">
            <div
                class="flex w-max min-w-full gap-4 sm:w-full sm:gap-12 sm:flex-wrap sm:justify-center snap-x snap-mandatory">
                @foreach ($kategori as $k)
                    @continue(empty($k['kode_kategori']))
                    <a href="{{ route('koleksi.kategori', ['kode' => $k['kode_kategori']]) }}"
                        class="cat-item group flex w-20 sm:w-24 md:w-28 shrink-0 snap-center flex-col items-center gap-2 sm:gap-3 text-center transition-transform duration-500 hover:-translate-y-1.5">

                        <div
                            class="cat-circle relative h-20 w-20 shrink-0 md:h-24 md:w-24 overflow-hidden rounded-full bg-white shadow-sm">
                            @if (!empty($k['foto']))
                                <img src="{{ asset($k['foto']) }}" alt="{{ $k['nama_kategori'] }}" loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                            @else
                                <div class="flex h-full w-full items-center justify-center font-display text-2xl"
                                    style="color: var(--gold); background: var(--gold-bg);">
                                    {{ mb_substr($k['nama_kategori'], 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <div class="w-full">
                            <p class="cat-name text-xs font-semibold leading-tight transition-colors"
                                style="color: var(--ink);">
                                {{ $k['nama_kategori'] }}
                            </p>
                            @if (!empty($k['subjudul']))
                                <p class="text-[10px] text-gray-400 mt-0.5 truncate">{{ $k['subjudul'] }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
     3. PRODUK TERBARU
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="reveal flex flex-wrap items-end justify-between gap-4 mb-10">
            <div>
                <p class="sec-eyebrow mb-3">Koleksi Baru</p>
                <h2 class="sec-title">Produk Terbaru</h2>
                <p class="sec-sub">Rilisan busana adat paling baru</p>
            </div>
            <a href="{{ route('koleksi.index') }}" class="link-more">
                Lihat Semua <svg class="ico" aria-hidden="true"><use href="#i-arrow" /></svg>
            </a>
        </div>

        @if (count($produkTerbaru) > 0)
            <div class="stagger grid grid-cols-2 gap-3 sm:gap-6 md:grid-cols-3 lg:grid-cols-4 items-stretch">
                @foreach ($produkTerbaru as $index => $p)
                    <div class="reveal h-full">
                        <x-product-card :produk="$p" :delay="min(0.1 + $index * 0.08, 0.8)" />
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ============================================================
     4. PRODUK TERPOPULER (latar lembut)
     ============================================================ --}}
    <section class="section-soft border-y" style="border-color: var(--line-soft);">
        <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
            <div class="reveal flex flex-wrap items-end justify-between gap-4 mb-10">
                <div>
                    <p class="sec-eyebrow mb-3">Pilihan Pelanggan</p>
                    <h2 class="sec-title">Produk Terpopuler</h2>
                    <p class="sec-sub">Favorit pilihan pelanggan dari beberapa waktu terakhir</p>
                </div>
                <a href="{{ route('koleksi.index') }}" class="link-more">
                    Lihat Semua <svg class="ico" aria-hidden="true"><use href="#i-arrow" /></svg>
                </a>
            </div>

            @if (count($produkTerpopuler) > 0)
                <div class="stagger grid grid-cols-2 gap-3 sm:gap-6 md:grid-cols-3 lg:grid-cols-4 items-stretch">
                    @foreach ($produkTerpopuler as $index => $p)
                        <div class="reveal h-full">
                            <x-product-card :produk="$p" :delay="min(0.1 + $index * 0.08, 0.8)" :peringkat="$index + 1" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ============================================================
     5. EVENT BANNER
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 py-10 md:py-16">
        <a href="https://eventalnay.id/beranda" target="_blank" rel="noopener"
            class="event-card group flex flex-col md:flex-row items-center justify-between gap-6 rounded-3xl p-6 md:p-12 transition-all duration-500 hover:shadow-2xl hover:-translate-y-1">

            <div class="relative flex-1 min-w-0">
                <span
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider text-white"
                    style="background: linear-gradient(135deg, var(--gold) 0%, #b8860b 100%); box-shadow: var(--shadow-gold);">
                    <svg class="ico-dot" aria-hidden="true"><use href="#i-dot" /></svg>
                    Event Spesial ALNAY.ID
                </span>

                <h2 class="font-display text-xl md:text-3xl font-bold text-white mt-4 leading-snug">
                    Ikuti Event Budaya &amp; Menangkan Hadiah Menarik
                </h2>

                <p class="text-sm md:text-base text-gray-400 mt-3 leading-relaxed max-w-xl">
                    Kompetisi busana adat, workshop kebudayaan, hingga event seru lainnya. Semua ada di satu tempat!
                </p>
            </div>

            <div class="relative w-full md:w-auto md:shrink-0 mt-4 md:mt-0">
                <span
                    class="inline-flex items-center justify-center gap-2 w-full md:w-auto px-7 py-3.5 rounded-xl text-sm font-bold uppercase tracking-wider text-white transition-all duration-300 group-hover:gap-4"
                    style="background: linear-gradient(135deg, var(--gold) 0%, #b8860b 100%); box-shadow: var(--shadow-gold);">
                    Kunjungi Halaman Event
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </span>
            </div>
        </a>
    </section>

    {{-- ============================================================
     6. OPSI FLEKSIBEL: SEWA ATAU BELI
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="reveal text-center mb-12">
            <p class="sec-eyebrow center justify-center">Transparansi Mutu</p>
            <h2 class="sec-title center mt-3 md:text-4xl">Opsi Fleksibel: Sewa atau Beli</h2>
            <p class="text-sm md:text-base text-gray-500 mt-4 max-w-2xl mx-auto leading-relaxed">
                Kejelasan komitmen higienitas, fitting presisi, dan garansi busana tanpa biaya tersembunyi.
            </p>
        </div>

        <div class="stagger grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 max-w-4xl mx-auto">
            {{-- Card 1: Sewa --}}
            <div class="reveal card-soft p-8 md:p-10 relative overflow-hidden" style="--delay:.05s;">
                <div class="absolute top-0 left-0 w-full h-1"
                    style="background: linear-gradient(90deg, var(--gold), var(--gold-soft), transparent);"></div>
                <span class="inline-block px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                    style="background-color: #fef3c7; color: #92400e;">
                    Paling Diminati
                </span>
                <h3 class="font-display text-xl md:text-2xl font-bold mt-4" style="color: var(--ink);">Layanan Sewa
                    Busana</h3>
                <p class="text-sm text-gray-600 mt-3 leading-relaxed">Solusi praktis dan hemat untuk akad nikah,
                    resepsi, wisuda, atau pentas budaya.</p>

                <ul class="mt-6 space-y-3.5 text-sm">
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #374151;"><strong>Durasi Sewa:</strong> Fleksibel 3 sampai 7 hari kerja.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #374151;"><strong>Gratis Alteration:</strong> Penyesuaian ukuran pinggang &
                            kancing aman.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #374151;"><strong>Termasuk Laundry:</strong> Busana kembali tidak perlu
                            dicuci sendiri, harum dan wangi sekali!</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #374151;"><strong>Steril &amp; Higienis:</strong> Ozon steaming dan garment
                            bag anti debu.</span>
                    </li>
                </ul>

                <a href="{{ route('koleksi.index') }}"
                    class="btn-smooth block w-full mt-8 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-white"
                    style="background: linear-gradient(135deg, var(--gold) 0%, #b8860b 100%);">
                    Eksplorasi Katalog Sewa
                </a>
            </div>

            {{-- Card 2: Beli Bespoke --}}
            <div class="reveal card-soft p-8 md:p-10 relative overflow-hidden"
                style="--delay:.15s; background-color: var(--dark-2); border-color: rgba(201,154,50,.18);">
                <div class="absolute top-0 left-0 w-full h-1"
                    style="background: linear-gradient(90deg, var(--gold), transparent);"></div>
                <span class="inline-block px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                    style="background-color: rgba(201, 154, 50, .18); color: var(--gold-soft);">
                    Eksklusif
                </span>
                <h3 class="font-display text-xl md:text-2xl font-bold text-white mt-4">Beli &amp; Custom Bespoke</h3>
                <p class="text-sm text-gray-400 mt-3 leading-relaxed">Mahakarya eksklusif milik selamanya sebagai busana
                    warisan keluarga.</p>

                <ul class="mt-6 space-y-3.5 text-sm">
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #e5e7eb;"><strong>Full Tailoring Personal:</strong> Disesuaikan 100% dengan
                            lekuk tubuh.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #e5e7eb;"><strong>Material Sutera &amp; Songket Asli:</strong> Pilihan bahan
                            tenun tangan terbaik.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #e5e7eb;"><strong>Sulam Emas Tradisional:</strong> Dikerjakan manual oleh
                            perajin adibusana.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #e5e7eb;"><strong>Garansi Fitting Ulang:</strong> Revisi hingga nyaman dan
                            pas dipakai.</span>
                    </li>
                </ul>

                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener"
                    class="btn-smooth block w-full mt-8 py-3.5 border text-center text-xs font-bold uppercase tracking-wider text-white hover:bg-white/10"
                    style="border-color: rgba(201, 154, 50, .35); color: var(--gold-soft);">
                    Konsultasi Bespoke
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================================
     7. OFFLINE ATELIER & STUDIO
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 py-12 md:py-20">
        <div class="rounded-3xl border p-6 md:p-12 bg-white transition-shadow duration-500 hover:shadow-xl"
            style="border-color: var(--line); box-shadow: var(--shadow-sm);">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                <div>
                    <p class="sec-eyebrow">Galeri &amp; Ruang Fitting</p>
                    <h2 class="sec-title mt-3">Offline Atelier &amp; Studio ALNAY.ID</h2>
                    <p class="text-sm md:text-base text-gray-600 mt-5 leading-relaxed">
                        Kunjungi studio kami untuk konsultasi langsung, fitting presisi, dan melihat koleksi secara
                        nyata. Dilengkapi ruang fitting privat ber-AC dan penjahit profesional siap membantu.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener"
                            class="btn-smooth inline-block px-6 py-3.5 border text-xs font-bold uppercase tracking-wider hover:bg-gray-50"
                            style="border-color: var(--ink); color: var(--ink);">
                            Info Lokasi &amp; Booking
                        </a>
                        <a href="#"
                            class="btn-smooth inline-block px-6 py-3.5 border text-xs font-bold uppercase tracking-wider hover:bg-gray-50"
                            style="border-color: #d1d5db; color: #374151;">
                            Panduan Sewa Online
                        </a>
                    </div>
                </div>

                <div class="relative rounded-2xl overflow-hidden group cursor-pointer border shadow-sm transition-shadow duration-500 hover:shadow-lg"
                    style="border-color: var(--line); z-index: 1;">
                    <div id="peta-alnay" class="w-full h-[280px] md:h-[340px]"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     8. TESTIMONI
     ============================================================ --}}
    <section class="section-soft border-y" style="border-color: var(--line-soft);">
        <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
            <div class="reveal flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-12">
                <div>
                    <p class="sec-eyebrow mb-3">Kisah Bahagia Bersama ALNAY.ID</p>
                    <h2 class="sec-title">Testimoni Pengantin &amp; Klien</h2>
                </div>

                <div class="flex flex-col items-start gap-2 sm:items-end">
                    <p class="text-sm font-medium inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full"
                        style="color: var(--muted); background: var(--gold-bg); border: 1px solid rgba(201, 154, 50, .18);">
                        Kepuasan
                        <svg class="ico" style="color: var(--gold);" aria-hidden="true"><use href="#i-star" /></svg>
                        {{ number_format($rataRata ?? 0, 2, ',', '.') }} dari
                        {{ number_format($totalTransaksi ?? 0, 0, ',', '.') }}+ Pesanan
                    </p>
                    <a href="{{ route('testimoni') }}" class="link-more">
                        Lihat Semua Testimoni <svg class="ico" aria-hidden="true"><use href="#i-arrow" /></svg>
                    </a>
                </div>
            </div>

            @if (count($testimoni) > 0)
                <div class="stagger grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach ($testimoni as $t)
                        @php
                            $inisial = strtoupper(mb_substr($t['nama'], 0, 2));
                        @endphp

                        <div
                            class="reveal card-soft relative overflow-hidden group flex flex-col justify-between p-5 sm:p-6 md:p-7 min-h-[200px]">

                            @if (!empty($t['foto']))
                                <div class="absolute top-0 right-0 bottom-0 w-[55%] z-0">
                                    <div class="absolute inset-0 overflow-hidden"
                                        style="-webkit-mask-image: linear-gradient(to left, black 40%, transparent 95%); mask-image: linear-gradient(to left, black 40%, transparent 95%);">
                                        <img src="{{ $t['foto'] }}" alt="Produk"
                                            class="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105">
                                    </div>
                                </div>
                            @endif

                            <div
                                class="relative z-20 w-full md:w-[60%] pointer-events-none flex flex-col justify-between h-full">
                                <div>
                                    <div class="stars" role="img" aria-label="Rating {{ $t['bintang'] }} dari 5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="ico {{ $i <= $t['bintang'] ? 'on' : 'off' }}" aria-hidden="true">
                                                <use href="#i-star" />
                                            </svg>
                                        @endfor
                                    </div>

                                    <p class="mt-3 text-[13px] sm:text-sm leading-relaxed italic text-gray-700 line-clamp-3">
                                        "{{ $t['teks'] }}"</p>
                                </div>

                                <div class="mt-5 pt-4 flex items-center gap-3 border-t"
                                    style="border-color: var(--line-soft);">
                                    <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[11px] font-bold shadow-sm overflow-hidden border"
                                        style="background-color: var(--gold-bg); color: var(--gold); border-color: rgba(201,154,50,.2);">
                                        {{ $inisial }}
                                        @if (!empty($t['foto_user']))
                                            @php
                                                $fotoUser = \Illuminate\Support\Str::startsWith($t['foto_user'], ['http://', 'https://'])
                                                    ? $t['foto_user']
                                                    : asset('storage/' . $t['foto_user']);
                                            @endphp
                                            <img src="{{ $fotoUser }}" alt="{{ $t['nama'] }}"
                                                class="absolute inset-0 w-full h-full object-cover">
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-[13px] font-bold truncate" style="color: var(--ink);">
                                            {{ $t['nama'] }}</p>
                                        <p class="text-[10px] mt-0.5 text-gray-500 truncate">{{ $t['konteks'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- === JAVASCRIPT (logika tetap) === --}}
    <script>
        const photos = [
            { src: "{{ asset('images/home/hero-pasangan.jpg') }}", title: 'Busana Adat Berpasangan', subtitle: 'Pesona budaya dalam balutan busana istimewa' },
            { src: "{{ asset('images/home/hero-gown.jpg') }}", title: 'Gown Pengantin', subtitle: 'Elegan dan anggun untuk momen spesial' },
            { src: "{{ asset('images/home/hero-jas.jpg') }}", title: 'Jas Beskap Modern', subtitle: 'Sentuhan modern pada busana adat' }
        ];

        let currentIndex = 0;
        let autoTimer = null;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function buildCarousel() {
            const stage = document.getElementById('carousel-container');
            const dots = document.getElementById('carousel-dots');
            if (!stage) return;

            stage.innerHTML = photos.map((p, i) => `
                <div class="slide" data-i="${i}">
                    <img src="${p.src}" alt="${p.title}" ${i === 0 ? '' : 'loading="lazy"'} draggable="false">
                    <div class="slide-caption">
                        <p>${p.title}</p>
                        <p>${p.subtitle}</p>
                    </div>
                </div>`).join('');

            dots.innerHTML = photos.map((_, i) =>
                `<button type="button" class="dot" data-dot="${i}" aria-label="Foto ${i + 1}"></button>`).join('');

            dots.querySelectorAll('[data-dot]').forEach(d =>
                d.addEventListener('click', () => goTo(Number(d.dataset.dot), true)));

            updateCarousel();
        }

        function updateCarousel() {
            const n = photos.length;
            document.querySelectorAll('#carousel-container .slide').forEach((el, i) => {
                el.classList.remove('is-active', 'is-prev', 'is-next');
                if (i === currentIndex) el.classList.add('is-active');
                else if (i === (currentIndex - 1 + n) % n) el.classList.add('is-prev');
                else el.classList.add('is-next');
            });
            document.querySelectorAll('#carousel-dots .dot').forEach((d, i) =>
                d.classList.toggle('on', i === currentIndex));
        }

        function goTo(i, userAction = false) {
            currentIndex = (i + photos.length) % photos.length;
            updateCarousel();
            if (userAction) restartAuto();
        }

        function nextPhoto() { goTo(currentIndex + 1, true); }
        function prevPhoto() { goTo(currentIndex - 1, true); }

        function startAuto() {
            if (reduceMotion) return;
            stopAuto();
            autoTimer = setInterval(() => goTo(currentIndex + 1), 5000);
        }
        function stopAuto() { if (autoTimer) clearInterval(autoTimer); autoTimer = null; }
        function restartAuto() { startAuto(); }

        function bindSwipe() {
            const stage = document.getElementById('carousel-container');
            if (!stage) return;
            let startX = null;
            stage.addEventListener('touchstart', e => { startX = e.touches[0].clientX; stopAuto(); }, { passive: true });
            stage.addEventListener('touchend', e => {
                if (startX === null) return;
                const dx = e.changedTouches[0].clientX - startX;
                if (Math.abs(dx) > 40) (dx < 0 ? nextPhoto() : prevPhoto());
                else startAuto();
                startX = null;
            }, { passive: true });
            stage.addEventListener('mouseenter', stopAuto);
            stage.addEventListener('mouseleave', startAuto);
        }

        function initMap() {
            const el = document.getElementById('peta-alnay');
            if (!el || el.dataset.ready) return;
            el.dataset.ready = '1';

            const searchQuery = encodeURIComponent('Alnay.id Lampung, Kedaung, Kemiling, Bandar Lampung');
            el.innerHTML = `
                <iframe
                    width="100%"
                    height="100%"
                    style="border:0; width:100%; height:100%;"
                    loading="lazy"
                    allowfullscreen
                    src="https://maps.google.com/maps?q=${searchQuery}&hl=id&z=17&output=embed">
                </iframe>
            `;
        }

        document.addEventListener('DOMContentLoaded', function () {
            buildCarousel();
            bindSwipe();
            startAuto();

            document.addEventListener('visibilitychange', () => document.hidden ? stopAuto() : startAuto());

            const mapEl = document.getElementById('peta-alnay');
            if (mapEl && 'IntersectionObserver' in window) {
                const mapObserver = new IntersectionObserver((entries) => {
                    if (entries.some(e => e.isIntersecting)) {
                        initMap();
                        mapObserver.disconnect();
                    }
                }, { rootMargin: '300px 0px' });
                mapObserver.observe(mapEl);
            } else {
                initMap();
            }

            document.querySelectorAll('.stagger').forEach(group => {
                Array.from(group.children).forEach((child, i) => {
                    if (!child.style.getPropertyValue('--delay')) {
                        child.style.setProperty('--delay', (i % 4) * 0.08 + 's');
                    }
                });
            });

            const reveals = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window)) {
                reveals.forEach(el => el.classList.add('is-visible'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

            reveals.forEach(el => observer.observe(el));
        });
    </script>
@endsection