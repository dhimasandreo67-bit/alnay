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

            /* Motif etnik Lampung */
            --tapis: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='44'%3E%3Cg fill='none' stroke='%23c99a32' stroke-width='1.1'%3E%3Cpath d='M0 1.5H64M0 42.5H64'/%3E%3Cpath d='M32 11 L45 22 L32 33 L19 22Z'/%3E%3Cpath d='M32 16 L39.5 22 L32 28 L24.5 22Z' fill='%23c99a32' fill-opacity='.55'/%3E%3Cpath d='M0 1.5 L8 13 L16 1.5M48 1.5 L56 13 L64 1.5M0 42.5 L8 31 L16 42.5M48 42.5 L56 31 L64 42.5'/%3E%3C/g%3E%3Ccircle cx='8' cy='22' r='1.8' fill='%23c99a32'/%3E%3Ccircle cx='56' cy='22' r='1.8' fill='%23c99a32'/%3E%3C/svg%3E");
            --tumpal: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='12'%3E%3Cpath d='M14 1.5 L23 6 L14 10.5 L5 6Z' fill='none' stroke='%23c99a32' stroke-width='1.2'/%3E%3Cpath d='M14 4.2 L17.4 6 L14 7.8 L10.6 6Z' fill='%23c99a32'/%3E%3C/svg%3E");
            --ketupat: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='8'%3E%3Cpath d='M7 .8 L12.5 4 L7 7.2 L1.5 4Z' fill='%23c99a32'/%3E%3C/svg%3E");
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

        /* ---------- Header section seragam: ornamen ketupat ---------- */
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
        .sec-eyebrow::before,
        .sec-eyebrow.center::before,
        .sec-eyebrow.center::after {
            content: '';
            display: inline-block;
            width: 7px; height: 7px;
            background: var(--gold);
            transform: rotate(45deg);
            border-radius: 1px;
        }

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
            width: 56px;
            height: 8px;
            margin-top: 14px;
            background: var(--ketupat) repeat-x left center / 14px 8px;
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
            border-radius: 999px;
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

        /* ---------- Hero background: tekstur tapis + pita tumpal ---------- */
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
            background-image: var(--tapis);
            background-size: 64px 44px;
            -webkit-mask-image: radial-gradient(ellipse at 75% 40%, black 0%, transparent 68%);
            mask-image: radial-gradient(ellipse at 75% 40%, black 0%, transparent 68%);
            pointer-events: none;
            opacity: .22;
        }
        .hero-bg::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 14px;
            border-top: 1px solid rgba(201, 154, 50, .7);
            border-bottom: 1px solid var(--gold);
            background: var(--tumpal) repeat-x center / 28px 12px, var(--dark);
            pointer-events: none;
        }
        .hero-bg > div { padding-bottom: 2.75rem; }

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

        /* ---------- Kategori bulat: cincin sulam putus-putus ---------- */
        .cat-circle {
            border: 2px solid var(--line-soft);
            transition: border-color .3s ease, box-shadow .4s var(--ease), transform .4s var(--ease);
            position: relative;
        }
        .cat-circle::after {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: 50%;
            border: 1.5px dashed rgba(230, 200, 119, .95);
            filter: drop-shadow(0 0 1px rgba(0, 0, 0, .35));
            transition: transform .6s var(--ease), border-color .4s ease;
            pointer-events: none;
        }
        .cat-item:hover .cat-circle {
            border-color: var(--gold);
            box-shadow: var(--shadow-gold), inset 0 0 0 2px #fff;
            transform: scale(1.02);
        }
        .cat-item:hover .cat-circle::after { border-style: solid; border-color: var(--gold-soft); transform: rotate(8deg); }
        .cat-item:hover .cat-name { color: var(--gold); }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }


        /* ---------- Kartu produk (Inter, bentuk & warna seragam) ---------- */
        .pk-card {
            display: flex; flex-direction: column; height: 100%;
            background: #fff; border: 1px solid var(--line); border-radius: 20px; overflow: hidden;
            font-family: 'Inter', sans-serif; color: var(--ink);
            transition: transform .45s var(--ease-out), box-shadow .45s var(--ease-out), border-color .3s ease;
        }
        .pk-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: #ece4d2; }
        .pk-foto { position: relative; aspect-ratio: 3 / 4; background: #efe9dc; overflow: hidden; }
        .pk-foto img { width: 100%; height: 100%; object-fit: cover; transition: transform .7s var(--ease-out); }
        .pk-card:hover .pk-foto img { transform: scale(1.05); }
        .pk-chip, .pk-rank {
            position: absolute; top: 10px; padding: 4px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 600; line-height: 1.2;
        }
        .pk-chip { left: 10px; background: rgba(255, 255, 255, .92); color: var(--ink); backdrop-filter: blur(6px); }
        .pk-chip-off { background: #fee2e2; color: #b91c1c; }
        .pk-rank { right: 10px; background: var(--ink); color: #fff; }
        .pk-body { display: flex; flex-direction: column; flex: 1; padding: 12px 14px 14px; }
        @media (min-width: 640px) { .pk-body { padding: 14px 16px 16px; } }
        .pk-nama {
            font-size: 14px; font-weight: 600; line-height: 1.35; color: var(--ink);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .pk-rating { display: flex; align-items: center; gap: 4px; margin-top: 4px; font-size: 12px; color: var(--muted); }
        .pk-rating .ico { width: 13px; height: 13px; color: var(--gold); }
        .pk-rating b { font-weight: 600; color: var(--ink); }
        .pk-harga { margin: 10px 0 12px; min-height: 42px; }
        .pk-utama { font-size: 15px; font-weight: 700; color: var(--ink); line-height: 1.3; }
        .pk-utama small { font-size: 11px; font-weight: 500; color: var(--muted); }
        .pk-kedua { margin-top: 2px; font-size: 12px; font-weight: 500; color: var(--muted); }
        .pk-aksi { margin-top: auto; display: grid; grid-template-columns: 1fr; gap: 8px; }
        .pk-aksi.dua { grid-template-columns: 1fr 1fr; }
        .pk-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-height: 40px; padding: 0 14px; border-radius: 999px;
            font-size: 13px; font-weight: 600; letter-spacing: 0;
            transition: background-color .25s ease, color .25s ease, border-color .25s ease, transform .25s var(--ease);
        }
        .pk-btn:active { transform: scale(.97); }
        .pk-btn-emas { background: var(--gold); color: var(--ink); border: 1px solid var(--gold); }
        .pk-btn-emas:hover { background: #b8860b; border-color: #b8860b; color: #fff; }
        .pk-btn-garis { background: #fff; color: var(--ink); border: 1px solid var(--gold); }
        .pk-btn-garis:hover { background: var(--gold-bg); }

        /* ---------- Kartu umum ---------- */
        .card-soft {
            border-radius: 20px;
            font-family: 'Inter', sans-serif;
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

        /* ---------- Event banner: pita tumpal di tepi bawah ---------- */
        .event-card {
            position: relative;
            overflow: hidden;
            background:
                var(--tumpal) repeat-x left bottom / 28px 12px,
                linear-gradient(135deg, var(--dark-2) 0%, #221c14 55%, var(--dark-2) 100%);
            border: 1px solid rgba(201, 154, 50, .12);
            isolation: isolate;
            text-decoration: none;
            -webkit-tap-highlight-color: transparent;
        }
        .event-card::before {
            content: '';
            position: absolute;
            inset: -40% -10% auto auto;
            width: 460px; height: 460px;
            max-width: 90vw; max-height: 90vw;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201, 154, 50, .28), transparent 65%);
            transition: transform .9s var(--ease-out);
            pointer-events: none;
            z-index: -1;
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
        .event-card:focus-visible {
            outline: 3px solid var(--gold-soft);
            outline-offset: 4px;
        }
        @media (prefers-reduced-motion: reduce) {
            .event-card, .event-card::before { transition: none; }
            .event-card:hover { transform: none; }
        }

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

        /* ---------- Pemisah antar section: ketupat emas ---------- */
        .section-rule {
            max-width: 320px;
            margin: 0 auto;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 154, 50, .45) 25%, rgba(201, 154, 50, .45) 75%, transparent);
        }
        .section-rule::after {
            content: '';
            display: block;
            width: 9px; height: 9px;
            margin: -5px auto 0;
            border-radius: 1px;
            background: var(--gold);
            transform: rotate(45deg);
            box-shadow: 0 0 0 4px rgba(201, 154, 50, .15);
        }

        /* ---------- Area latar lembut + watermark tapis ---------- */
        .section-soft {
            position: relative;
            background: linear-gradient(180deg, #fdfbf7 0%, #ffffff 100%);
        }
        .section-soft::before {
            content: '';
            position: absolute; inset: 0;
            background: var(--tapis) repeat center / 64px 44px;
            opacity: .09;
            -webkit-mask-image: radial-gradient(ellipse at 100% 0%, #000, transparent 55%);
            mask-image: radial-gradient(ellipse at 100% 0%, #000, transparent 55%);
            pointer-events: none;
        }
        .section-soft > * { position: relative; }

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
                    Jas &amp; Gown Berbudaya & Elegan dalam Setiap Momen
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
                        class="cat-item group flex w-24 sm:w-28 md:w-36 shrink-0 snap-center flex-col items-center gap-2 sm:gap-3 text-center transition-transform duration-500 hover:-translate-y-1.5">

                        <div
                            class="cat-circle relative h-24 w-24 shrink-0 md:h-32 md:w-32 overflow-hidden rounded-full bg-white shadow-sm">
                            @if (!empty($k['foto']))
                                <img src="{{ asset($k['foto']) }}" alt="{{ $k['nama_kategori'] }}" loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                            @else
                                <div class="flex h-full w-full items-center justify-center font-display text-3xl md:text-4xl"
                                    style="color: var(--gold); background: var(--gold-bg);">
                                    {{ mb_substr($k['nama_kategori'], 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <div class="w-full">
                            <p class="cat-name text-sm font-semibold leading-tight transition-colors"
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
            <div class="stagger mx-auto grid max-w-7xl grid-cols-2 gap-2.5 sm:gap-4 md:grid-cols-3 lg:grid-cols-5 items-stretch">
                @foreach ($produkTerbaru as $p)
                    <div class="reveal h-full">
                        <x-product-card :produk="$p" />
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

            @php
                $ambil = function ($item, array $kunci, $default = null) {
                    foreach ($kunci as $k) {
                        $v = data_get($item, $k);
                        if ($v !== null && $v !== '') {
                            return $v;
                        }
                    }
                    return $default;
                };

                $angka = fn ($v) => (int) round((float) ($v ?? 0));
            @endphp

                        @if (count($produkTerpopuler) > 0)
                        <div class="stagger mx-auto grid max-w-7xl grid-cols-2 gap-2.5 sm:gap-4 md:grid-cols-3 lg:grid-cols-5 items-stretch">
                    @foreach ($produkTerpopuler as $index => $p)
                        <div class="reveal h-full">
                            <x-product-card :produk="$p" :peringkat="$index + 1" />
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
        <a href="{{ config('alnay.event_url', 'https://eventalnay.id/beranda') }}" target="_blank"
            rel="noopener noreferrer" aria-label="Kunjungi halaman Event ALNAY.ID (buka di tab baru)"
            class="event-card group flex flex-col md:flex-row items-center md:items-center justify-between gap-6 rounded-3xl px-6 pt-8 pb-14 md:px-12 md:pt-12 md:pb-16 transition-all duration-500 hover:shadow-2xl hover:-translate-y-1 focus:outline-none">

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

                <p class="text-sm md:text-base text-gray-300 mt-3 leading-relaxed max-w-xl">
                    Kompetisi busana adat, workshop kebudayaan, hingga event seru lainnya. Semua ada di satu tempat!
                </p>
            </div>

            <div class="relative w-full md:w-auto md:shrink-0 mt-4 md:mt-0">
                <span
                    class="inline-flex items-center justify-center gap-2 w-full md:w-auto px-7 py-3.5 rounded-full text-sm font-bold uppercase tracking-wider text-white transition-all duration-300 group-hover:gap-4"
                    style="background: linear-gradient(135deg, var(--gold) 0%, #b8860b 100%); box-shadow: var(--shadow-gold);">
                    Kunjungi Halaman Event
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
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
                <h3 class="text-xl md:text-2xl font-semibold mt-4" style="color: var(--ink);">Layanan Sewa
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
                <h3 class="text-xl md:text-2xl font-semibold text-white mt-4">Beli &amp; Custom Bespoke</h3>
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
     7. OFFLINE ATELIER  (ganti seluruh section 7 lama dengan blok ini)
     Tetap memakai id #peta-alnay dan #open-status, jadi script initMap()
     dan initOpenStatus() di bawah halaman tidak perlu diubah.
     ============================================================ --}}
    <style>
        /* ---------- Kartu: info di kiri, peta penuh di kanan ---------- */
        .atelier {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow-sm);
        }
        @media (min-width: 1024px) { .atelier { grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); } }

        .atelier-info { display: flex; flex-direction: column; padding: 20px 18px 22px; }
        @media (min-width: 768px)  { .atelier-info { padding: 28px; } }
        @media (min-width: 1024px) { .atelier-info { padding: 32px 34px; justify-content: center; } }
        .atelier .sec-title { font-size: 1.375rem; }
        @media (min-width: 768px) { .atelier .sec-title { font-size: 1.625rem; } }
        .atelier .sec-title::after { margin-top: 10px; }

        .atelier-desc { margin-top: 10px; max-width: 32rem; font-size: .875rem; line-height: 1.6; color: #4b5563; }

        /* ---------- Alamat & jam ---------- */
        .atelier-list { margin: 18px 0 0; padding: 0; list-style: none; display: grid; gap: 8px; }
        .atelier-row {
            display: flex; gap: 14px; align-items: flex-start;
            padding: 10px 12px;
            border: 1px solid var(--line-soft);
            border-radius: 14px;
            background: #fdfbf7;
        }
        .atelier-ico {
            width: 34px; height: 34px; flex-shrink: 0;
            display: grid; place-items: center;
            border-radius: 10px; background: var(--gold-bg); color: var(--gold);
        }
        .atelier-ico svg { width: 16px; height: 16px; }
        .atelier-label { font-size: 12px; font-weight: 500; line-height: 1.3; color: var(--muted); }
        .atelier-value { margin-top: 2px; font-size: 14px; font-weight: 600; line-height: 1.45; color: var(--ink); }
        .atelier-jam { margin: 6px 0 0; }
        .atelier-jam > div { display: flex; justify-content: space-between; gap: 16px; padding: 2px 0; font-size: 13px; }
        .atelier-jam dt { color: var(--muted); }
        .atelier-jam dd { margin: 0; font-weight: 600; color: var(--ink); font-variant-numeric: tabular-nums; }

        /* Lencana Buka/Tutup (diisi script lama lewat #open-status) dibuat seragam */
        .atelier #open-status > span {
            padding: 3px 10px; border-radius: 999px; font-size: 12px;
            border-color: transparent; box-shadow: none; background: #f6f3ea;
        }

        /* ---------- Tombol ---------- */
        .atelier-cta { margin-top: 18px; display: flex; flex-direction: column; gap: 10px; }
        @media (min-width: 640px) { .atelier-cta { flex-direction: row; } }
        .atelier-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 42px; padding: 0 20px;
            font-size: 13px; font-weight: 600; text-align: center;
        }
        .atelier-btn svg { width: 16px; height: 16px; }
        .atelier-btn-emas { color: #fff; background: linear-gradient(135deg, var(--gold) 0%, #b8860b 100%); }
        .atelier-btn-garis { color: var(--ink); background: #fff; border: 1px solid #e5dfd0; }
        .atelier-btn-garis:hover { background: var(--gold-bg); border-color: var(--gold); }

        /* ---------- Peta (kartu info bawaan Google tampil di kiri atas) ---------- */
        .atelier-peta {
            position: relative; isolation: isolate;
            min-height: 400px;   /* cukup tinggi agar pin tidak tertutup kartu info Google di HP */
            background: linear-gradient(110deg, #efe9dc 30%, #f6f1e7 50%, #efe9dc 70%) 0 0 / 200% 100%;
            animation: atelier-muat 1.6s linear infinite;
        }
        @media (min-width: 1024px) { .atelier-peta { min-height: 380px; } }
        @keyframes atelier-muat { to { background-position: -200% 0; } }

        .atelier-peta > #peta-alnay {
            position: absolute !important; inset: 0; overflow: hidden;
            height: auto; background: transparent;
        }
        .atelier-peta > #peta-alnay iframe {
            position: absolute !important; inset: 0;
            width: 100% !important; height: 100% !important;
            filter: saturate(.92);
        }
        @media (max-width: 1023px) { .atelier-peta > #peta-alnay { pointer-events: none !important; } }

        /* HP: seluruh peta bisa diketuk untuk membuka Google Maps, jadi scroll halaman tidak tersangkut */
        .atelier-tap { position: absolute; inset: 0; z-index: 4; }
        @media (min-width: 1024px) { .atelier-tap { display: none; } }

        @media (prefers-reduced-motion: reduce) { .atelier-peta { animation: none; } }
    </style>

    <section class="reveal max-w-6xl mx-auto px-4 py-8 md:py-12" aria-labelledby="atelier-judul">
        <div class="atelier">

            {{-- ============ KIRI: INFO ============ --}}
            <div class="atelier-info">
                <p class="sec-eyebrow">Galeri &amp; Ruang Fitting</p>
                <h2 id="atelier-judul" class="sec-title mt-3">Offline Atelier &amp; Toko ALNAY.ID</h2>
                <p class="atelier-desc">
                    Datang langsung untuk konsultasi, fitting presisi, dan melihat koleksi secara nyata.
                    Tersedia ruang fitting privat ber-AC dan penjahit profesional.
                </p>

                <ul class="atelier-list">
                    <li class="atelier-row">
                        <span class="atelier-ico" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="atelier-label">Alamat</p>
                            <p class="atelier-value">Kedaung, Kemiling, Bandar Lampung</p>
                        </div>
                    </li>

                    <li class="atelier-row">
                        <span class="atelier-ico" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="atelier-label">Jam buka (WIB)</p>
                                <div id="open-status">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-white/95 text-[10px] font-bold text-gray-700 border border-gray-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        <span>Memuat...</span>
                                    </span>
                                </div>
                            </div>
                            <dl class="atelier-jam">
                                <div><dt>Senin–Sabtu</dt><dd>09.00–20.00</dd></div>
                                <div><dt>Minggu</dt><dd>10.00–17.00</dd></div>
                            </dl>
                        </div>
                    </li>
                </ul>

                <div class="atelier-cta">
                    <a href="https://www.google.com/maps/dir/?api=1&destination=Alnay.id+Kedaung+Kemiling+Bandar+Lampung"
                        target="_blank" rel="noopener" class="btn-smooth atelier-btn atelier-btn-emas">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        Petunjuk arah
                    </a>
                    <a href="{{ url('cara-sewa') }}" class="btn-smooth atelier-btn atelier-btn-garis">Panduan sewa online</a>
                </div>
            </div>

            {{-- ============ KANAN: PETA (Google Maps) ============ --}}
            <div class="atelier-peta">
                <div id="peta-alnay" role="region" aria-label="Peta lokasi ALNAY.ID"></div>

                {{-- HP: ketuk di mana saja pada peta untuk membuka Google Maps --}}
                <a href="https://www.google.com/maps/search/?api=1&query=Alnay.id+Kedaung+Kemiling+Bandar+Lampung"
                    target="_blank" rel="noopener" class="atelier-tap" aria-label="Buka peta di Google Maps"></a>
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
                                class="relative z-20 w-full md:w-[60%] flex flex-col justify-between h-full">
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

        /* ---------- Load peta dengan fade-in ---------- */
        function initMap() {
            const el = document.getElementById('peta-alnay');
            if (!el || el.dataset.ready) return;
            el.dataset.ready = '1';

            // Menggunakan query pencarian yang lebih sederhana (menghindari kotak info besar Google)
            const q = encodeURIComponent('Alnay.id Bandar Lampung');

            const iframe = document.createElement('iframe');
            iframe.width = '100%';
            iframe.height = '100%';
            iframe.loading = 'lazy';
            iframe.allowFullscreen = true;
            iframe.referrerPolicy = 'no-referrer-when-downgrade';
            iframe.style.cssText =
                'border:0; width:100%; height:100%; display:block; opacity:0; ' +
                'transition:opacity .6s ease;';
            iframe.src = `https://maps.google.com/maps?q=${q}&hl=id&z=16&output=embed`;

            iframe.addEventListener('load', () => {
                iframe.style.opacity = '1';
            });

            el.appendChild(iframe);
        }

        /* ---------- Hitung status buka/tutup realtime (WIB) ---------- */
        function initOpenStatus() {
            const badge = document.getElementById('open-status');
            if (!badge) return;

            const now = new Date();
            const wibMs = now.getTime() + now.getTimezoneOffset() * 60000 + 7 * 3600000;
            const wib = new Date(wibMs);
            const day = wib.getDay();                       // 0=Min, 6=Sab
            const t = wib.getHours() + wib.getMinutes() / 60;

            // Senin–Sabtu: 09.00–20.00 | Minggu: 10.00–17.00
            const isOpen = day === 0 ? (t >= 10 && t < 17) : (t >= 9 && t < 20);

            const dot = isOpen ? 'bg-green-500 animate-pulse' : 'bg-red-500';
            const label = isOpen ? 'Buka' : 'Tutup';

            badge.innerHTML = `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white/95 backdrop-blur-md text-[10px] font-bold text-gray-700 shadow-sm border border-gray-100">
                    <span class="w-1.5 h-1.5 rounded-full ${dot}"></span>
                    <span>${label}</span>
                </span>`;
        }

        /* ---------- Boot ---------- */
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initMap();
                initOpenStatus();
                setInterval(initOpenStatus, 60000);
            });
        } else {
            initMap();
            initOpenStatus();
            setInterval(initOpenStatus, 60000);
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