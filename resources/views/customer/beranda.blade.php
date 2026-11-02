@extends('layouts.public')

@section('title', 'ALNAY.ID — Jas & Gown Berbudaya')

@section('content')

    {{-- Sprite ikon (didefinisikan sekali, dipakai lewat <use>) --}}
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

    {{-- Leaflet CSS untuk Peta Interaktif --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    {{-- ============================================================
     CUSTOM STYLES
     ============================================================ --}}
    <style>
        :root {
            --gold: #c99a32;
            --gold-soft: #e6c877;
            --gold-bg: #fbf6e9;
            --ink: #15233d;
            --dark: #17140f;
            --dark-2: #1a1610;
            --muted: #6b7280;
            --line: #f0ece4;
            --ease: cubic-bezier(0.22, 0.8, 0.3, 1);
        }

        html {
            scroll-behavior: smooth;
        }

        html,
        body {
            max-width: 100%;
            overflow-x: clip;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* ---------- Ikon ---------- */
        .ico {
            width: 1em;
            height: 1em;
            flex-shrink: 0;
            display: inline-block;
            vertical-align: -0.125em;
        }

        .ico-dot {
            width: 8px;
            height: 8px;
            flex-shrink: 0;
        }

        .ico-check {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 1px;
            color: var(--gold);
        }

        .stars {
            display: inline-flex;
            gap: 2px;
            font-size: 16px;
        }

        .stars .off {
            color: #e5dfd0;
        }

        .stars .on {
            color: var(--gold);
        }

        /* Teks panjang tidak mendorong layout */
        .card-shadow {
            min-width: 0;
        }

        .card-shadow h3,
        .card-shadow p,
        section h1,
        section h2,
        section h3 {
            overflow-wrap: anywhere;
        }

        /* ---------- Header section seragam ---------- */
        .sec-eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .sec-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--ink);
            font-size: 1.5rem;
            line-height: 1.25;
        }

        @media (min-width: 768px) {
            .sec-title {
                font-size: 2rem;
            }
        }

        .sec-title::after {
            content: '';
            display: block;
            width: 44px;
            height: 3px;
            margin-top: 12px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--gold), var(--gold-soft));
        }

        .sec-title.center::after {
            margin-left: auto;
            margin-right: auto;
        }

        .sec-sub {
            margin-top: 10px;
            font-size: 0.875rem;
            color: var(--muted);
        }

        .link-more {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gold);
            transition: gap 0.3s var(--ease), opacity 0.3s ease;
        }

        .link-more .ico {
            width: 16px;
            height: 16px;
        }

        .link-more:hover {
            gap: 10px;
            opacity: 0.85;
        }

        /* ---------- Tombol ---------- */
        .btn-smooth {
            transition: transform 0.3s var(--ease), box-shadow 0.3s var(--ease), background-color 0.3s ease;
            border-radius: 12px;
        }

        .btn-smooth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(201, 154, 50, 0.28);
        }

        .btn-smooth:active {
            transform: translateY(0);
        }

        /* ---------- Animasi masuk hero ---------- */
        @keyframes fade-up {
            from {
                opacity: 0;
                transform: translateY(22px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-in {
            opacity: 0;
            animation: fade-up 0.9s var(--ease) forwards;
            animation-delay: calc(var(--d, 0) * 1s);
        }

        /* ---------- Hero background ---------- */
        .hero-bg {
            position: relative;
            background:
                radial-gradient(900px 400px at 85% 10%, rgba(201, 154, 50, 0.16), transparent 60%),
                radial-gradient(600px 300px at 0% 100%, rgba(201, 154, 50, 0.08), transparent 60%),
                var(--dark);
        }

        /* ---------- Carousel (posisi digeser, bukan dirender ulang) ---------- */
        .stage {
            position: relative;
            width: 100%;
            height: 280px;
        }

        @media (min-width: 640px) {
            .stage {
                height: 420px;
            }
        }

        .slide {
            --x: 0%;
            --s: 1;
            position: absolute;
            top: 0;
            left: 50%;
            width: 78%;
            height: 100%;
            transform: translateX(calc(-50% + var(--x))) scale(var(--s));
            opacity: 0;
            pointer-events: none;
            transition: transform 0.7s var(--ease), opacity 0.7s var(--ease), filter 0.7s var(--ease);
            will-change: transform, opacity;
        }

        @media (min-width: 640px) {
            .slide {
                width: 46%;
            }
        }

        .slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 18px;
        }

        .slide.is-active {
            --x: 0%;
            --s: 1;
            opacity: 1;
            z-index: 3;
            pointer-events: auto;
        }

        .slide.is-active img {
            border: 3px solid var(--gold);
            box-shadow: 0 24px 50px -12px rgba(0, 0, 0, 0.55);
        }

        .slide.is-prev,
        .slide.is-next {
            opacity: 0;
            z-index: 1;
            --s: 0.82;
            filter: blur(2px);
        }

        .slide.is-prev {
            --x: -58%;
        }

        .slide.is-next {
            --x: 58%;
        }

        @media (min-width: 640px) {

            .slide.is-prev,
            .slide.is-next {
                opacity: 0.5;
                --x: 74%;
            }

            .slide.is-prev {
                --x: -74%;
            }
        }

        .slide-caption {
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 14px;
            padding: 10px 14px;
            border-radius: 12px;
            color: #fff;
            background: rgba(10, 8, 5, 0.58);
            backdrop-filter: blur(8px);
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.6s var(--ease) 0.25s, transform 0.6s var(--ease) 0.25s;
        }

        .slide.is-active .slide-caption {
            opacity: 1;
            transform: translateY(0);
        }

        .slide-caption p:first-child {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--gold-soft);
        }

        .slide-caption p:last-child {
            margin-top: 2px;
            font-size: 11px;
            color: #d6d3cc;
        }

        .carousel-btn {
            transition: transform 0.3s var(--ease), background-color 0.3s ease;
            backdrop-filter: blur(6px);
        }

        .carousel-btn:hover {
            transform: translateY(-50%) scale(1.1);
            background-color: rgba(255, 255, 255, 0.35) !important;
        }

        .dot {
            height: 6px;
            width: 6px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.3);
            transition: width 0.4s var(--ease), background-color 0.4s ease;
        }

        .dot.on {
            width: 22px;
            background: var(--gold);
        }

        /* ---------- Kategori bulat ---------- */
        .cat-circle {
            border: 2px solid #f3f4f6;
            transition: border-color 0.3s ease, box-shadow 0.4s var(--ease), transform 0.4s var(--ease);
        }

        .cat-item:hover .cat-circle {
            border-color: var(--gold);
            box-shadow: 0 12px 24px -8px rgba(201, 154, 50, 0.35);
        }

        .cat-item:hover .cat-name {
            color: var(--gold);
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* ---------- Kartu (testimoni, opsi) ---------- */
        .card-soft {
            border-radius: 20px;
            border: 1px solid var(--line);
            background: #fff;
            transition: transform 0.45s var(--ease), box-shadow 0.45s var(--ease), border-color 0.3s ease;
        }

        .card-soft:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 40px -14px rgba(21, 35, 61, 0.14);
            border-color: #e8e1d2;
        }

        .quote-mark {
            position: absolute;
            top: 8px;
            right: 20px;
            font-family: 'Playfair Display', serif;
            font-size: 72px;
            line-height: 1;
            color: var(--gold);
            opacity: 0.12;
            pointer-events: none;
        }

        /* ---------- Banner event ---------- */
        .event-card {
            position: relative;
            overflow: hidden;
            background: var(--dark-2);
        }

        .event-card::before {
            content: '';
            position: absolute;
            inset: -40% -10% auto auto;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201, 154, 50, 0.22), transparent 65%);
            transition: transform 0.8s var(--ease);
        }

        .event-card:hover::before {
            transform: scale(1.25) translate(-20px, 20px);
        }

        /* ---------- Peta ---------- */
        .pin-bounce {
            animation: bounce-marker 2s infinite ease-in-out;
        }

        @keyframes bounce-marker {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
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
        #peta-alnay .leaflet-control {
            z-index: 1 !important;
        }

        /* ---------- Scroll reveal (bisa bertahap) ---------- */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.8s var(--ease), transform 0.8s var(--ease);
            transition-delay: var(--delay, 0s);
        }

        .reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .stagger>.reveal {
            transition-duration: 0.7s;
        }

        @media (prefers-reduced-motion: reduce) {

            .reveal,
            .hero-in {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
                animation: none !important;
            }

            .slide,
            .slide-caption {
                transition: none !important;
            }

            .pin-bounce {
                animation: none;
            }

            html {
                scroll-behavior: auto;
            }
        }
    </style>
    <noscript>
        <style>
            .reveal,
            .hero-in {
                opacity: 1 !important;
                transform: none !important;
                animation: none !important;
            }

            .slide.is-active {
                opacity: 1;
            }
        </style>
    </noscript>


    {{-- ============================================================
     1. HERO SECTION (Carousel 3D)
     ============================================================ --}}
    <section class="hero-bg overflow-hidden">
        <div class="max-w-7xl mx-auto p-4 sm:p-10 lg:flex lg:items-center lg:gap-10 lg:py-20">

            {{-- KIRI: Teks --}}
            <div class="min-w-0 text-white lg:w-1/2 mb-10 lg:mb-0">
                <span class="hero-in inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[10px] font-semibold"
                    style="--d:.05; color: var(--gold-soft);">
                    <svg class="ico-dot" aria-hidden="true"><use href="#i-dot" /></svg>
                    Jas &amp; Gown Berbudaya — Elegan dalam Setiap Momen
                </span>

                <h1 class="hero-in font-display mt-4 text-[28px] leading-tight font-bold min-[375px]:text-[32px] sm:text-[40px] lg:text-[44px]"
                    style="--d:.18;">
                    Jas &amp; Gown Berbudaya, Dicipta untuk Momen Abadi Anda.
                </h1>

                <p class="hero-in mt-4 max-w-[460px] text-sm leading-relaxed" style="--d:.32; color: #cfcac0;">
                    Temukan koleksi Fashion Etnik pada ALNAY.ID dengan sentuhan elegan dan modern. Tersedia untuk pembelian
                    maupun penyewaan untuk melengkapi berbagai momen istimewa Anda.
                </p>

                <div class="hero-in mt-7 flex flex-col sm:flex-row gap-3" style="--d:.46;">
                    <a href="{{ route('koleksi.index') }}"
                        class="btn-smooth inline-flex items-center justify-center px-6 py-3 text-xs font-bold text-white"
                        style="background-color: var(--gold);">
                        Jelajahi Koleksi Produk
                    </a>
                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener"
                        class="btn-smooth inline-flex items-center justify-center gap-2 border border-white/30 px-6 py-3 text-xs font-bold text-white hover:bg-white/10">
                        <svg class="ico text-[15px]" aria-hidden="true"><use href="#i-chat" /></svg>
                        Konsultasi WhatsApp
                    </a>
                </div>
            </div>

            {{-- KANAN: Carousel 3 Foto --}}
            <div class="hero-in relative w-full min-w-0 lg:w-1/2" style="--d:.3;">
                <button type="button" onclick="prevPhoto()" aria-label="Foto sebelumnya"
                    class="carousel-btn absolute left-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 text-white rounded-full p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="stage" id="carousel-container"></div>

                <button type="button" onclick="nextPhoto()" aria-label="Foto berikutnya"
                    class="carousel-btn absolute right-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 text-white rounded-full p-2">
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
     2. KATEGORI (Ikon Bulat)
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 mt-12 md:mt-16">
        <div class="overflow-x-auto sm:overflow-visible hide-scrollbar py-4 -mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="flex w-max min-w-full gap-4 sm:w-full sm:gap-12 sm:flex-wrap sm:justify-center snap-x snap-mandatory">
                @foreach ($kategori as $k)
                    @continue(empty($k['kode_kategori']))
                    <a href="{{ route('koleksi.kategori', ['kode' => $k['kode_kategori']]) }}"
                        class="cat-item group flex w-20 sm:w-24 md:w-28 shrink-0 snap-center flex-col items-center gap-2 sm:gap-3 text-center transition-transform duration-500 hover:-translate-y-1.5">

                        <div class="cat-circle relative h-20 w-20 shrink-0 md:h-24 md:w-24 overflow-hidden rounded-full bg-white shadow-sm">
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
    <section class="max-w-7xl mx-auto px-4 py-14 md:py-20">
        <div class="reveal flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
                <p class="sec-eyebrow mb-2">Koleksi Baru</p>
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
     4. PRODUK TERPOPULER
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-14 md:py-20">
        <div class="reveal flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
                <p class="sec-eyebrow mb-2">Pilihan Pelanggan</p>
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
    </section>


    {{-- ============================================================
     5. EVENT BANNER
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 py-8 md:py-12">
        <a href="https://eventalnay.id/beranda" target="_blank" rel="noopener"
            class="event-card group flex flex-col md:flex-row items-center justify-between gap-6 rounded-3xl p-6 md:p-10 transition-all duration-500 hover:shadow-2xl hover:-translate-y-1">

            <div class="relative flex-1 min-w-0">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider text-white"
                    style="background-color: var(--gold);">
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
                    class="inline-flex items-center justify-center gap-2 w-full md:w-auto px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider text-white transition-all duration-300 group-hover:gap-4"
                    style="background-color: var(--gold);">
                    Kunjungi Halaman Event
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <p class="sec-eyebrow">Transparansi Mutu</p>
            <h2 class="sec-title center mt-3 md:text-4xl">Opsi Fleksibel: Sewa atau Beli</h2>
            <p class="text-sm md:text-base text-gray-500 mt-4 max-w-2xl mx-auto leading-relaxed">
                Kejelasan komitmen higienitas, fitting presisi, dan garansi busana tanpa biaya tersembunyi.
            </p>
        </div>

        <div class="stagger grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 max-w-4xl mx-auto">
            {{-- Card 1: Layanan Sewa Busana --}}
            <div class="reveal card-soft p-8 md:p-10" style="--delay:.05s;">
                <span class="inline-block px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                    style="background-color: #fef3c7; color: #92400e;">
                    Paling Diminati
                </span>
                <h3 class="font-display text-xl md:text-2xl font-bold mt-4" style="color: var(--ink);">Layanan Sewa Busana</h3>
                <p class="text-sm text-gray-600 mt-3 leading-relaxed">Solusi praktis dan hemat untuk akad nikah, resepsi,
                    wisuda, atau pentas budaya.</p>

                <ul class="mt-6 space-y-3 text-sm">
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
                        <span style="color: #374151;"><strong>Termasuk Laundry:</strong> Busana kembali tidak perlu dicuci
                            sendiri, harum dan wangi sekali!</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #374151;"><strong>Steril & Higienis:</strong> Ozon steaming dan garment bag
                            anti debu.</span>
                    </li>
                </ul>

                <a href="{{ route('koleksi.index') }}"
                    class="btn-smooth block w-full mt-8 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-white"
                    style="background-color: var(--gold);">
                    Eksplorasi Katalog Sewa
                </a>
            </div>

            {{-- Card 2: Beli & Custom Bespoke --}}
            <div class="reveal card-soft p-8 md:p-10" style="--delay:.15s; background-color: var(--dark-2); border-color: var(--dark-2);">
                <span class="inline-block px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                    style="background-color: rgba(201,154,50,.18); color: var(--gold-soft);">
                    Eksklusif
                </span>
                <h3 class="font-display text-xl md:text-2xl font-bold text-white mt-4">Beli & Custom Bespoke</h3>
                <p class="text-sm text-gray-400 mt-3 leading-relaxed">Mahakarya eksklusif milik selamanya sebagai busana
                    warisan keluarga.</p>

                <ul class="mt-6 space-y-3 text-sm">
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #e5e7eb;"><strong>Full Tailoring Personal:</strong> Disesuaikan 100% dengan
                            lekuk tubuh.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #e5e7eb;"><strong>Material Sutera & Songket Asli:</strong> Pilihan bahan tenun
                            tangan terbaik.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #e5e7eb;"><strong>Sulam Emas Tradisional:</strong> Dikerjakan manual oleh
                            perajin adibusana.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="ico-check" aria-hidden="true"><use href="#i-check" /></svg>
                        <span style="color: #e5e7eb;"><strong>Garansi Fitting Ulang:</strong> Revisi hingga nyaman dan pas
                            dipakai.</span>
                    </li>
                </ul>

                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener"
                    class="btn-smooth block w-full mt-8 py-3.5 border text-center text-xs font-bold uppercase tracking-wider text-white hover:bg-white/10"
                    style="border-color: #4b5563;">
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
            style="border-color: var(--line);">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                <div>
                    <p class="sec-eyebrow">Galeri & Ruang Fitting</p>
                    <h2 class="sec-title mt-3">Offline Atelier & Studio ALNAY.ID</h2>
                    <p class="text-sm md:text-base text-gray-600 mt-5 leading-relaxed">
                        Kunjungi studio kami untuk konsultasi langsung, fitting presisi, dan melihat koleksi secara nyata.
                        Dilengkapi ruang fitting privat ber-AC dan penjahit profesional siap membantu.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener"
                            class="btn-smooth inline-block px-6 py-3.5 border text-xs font-bold uppercase tracking-wider hover:bg-gray-50"
                            style="border-color: var(--ink); color: var(--ink);">
                            Info Lokasi & Booking
                        </a>
                        <a href="#"
                            class="btn-smooth inline-block px-6 py-3.5 border text-xs font-bold uppercase tracking-wider hover:bg-gray-50"
                            style="border-color: #d1d5db; color: #374151;">
                            Panduan Sewa Online
                        </a>
                    </div>
                </div>

                <div class="relative rounded-2xl overflow-hidden group cursor-pointer border shadow-sm"
                    style="border-color: var(--line); z-index: 1;">
                    <div id="peta-alnay" class="w-full h-[280px] md:h-[320px]"></div>

                    <div class="absolute top-4 left-4 right-4 p-4 rounded-xl shadow-lg transition-transform duration-500 group-hover:-translate-y-1"
                        style="background-color: rgba(23, 20, 15, 0.92); backdrop-filter: blur(6px); z-index: 10;">
                        <p class="text-white text-sm font-bold flex items-center gap-2">
                            <svg class="w-4 h-4" style="color: var(--gold);" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Atelier & Fashion Etnik
                        </p>
                        <p class="text-gray-300 text-xs mt-1.5 leading-relaxed">Jl. Terusan Rajabasa No.11, Perum Bumi
                            Permata Cirmai, Kel. Kedung, Kec. Kemiling, Bandar Lampung</p>
                        <p class="text-gray-400 text-[10px] mt-1.5">Buka: 09.00 - 20.00 WIB</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ============================================================
     8. TESTIMONI
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-14 md:py-24">
        <div class="reveal flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-10">
            <div>
                <p class="sec-eyebrow mb-2">Kisah Bahagia Bersama ALNAY.ID</p>
                <h2 class="sec-title">Testimoni Pengantin &amp; Klien</h2>
            </div>

            <div class="flex flex-col items-start gap-1 sm:items-end">
                <p class="text-sm font-medium inline-flex items-center gap-1.5" style="color: var(--muted);">
                    Kepuasan
                    <svg class="ico" style="color: var(--gold);" aria-hidden="true"><use href="#i-star" /></svg>
                    4.97 dari 1.800+ Acara
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

                    <div class="reveal card-soft relative p-5 sm:p-6 md:p-7 flex flex-col">
                        <span class="quote-mark" aria-hidden="true">“</span>

                        <div class="stars" role="img" aria-label="Rating {{ $t['bintang'] }} dari 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="ico {{ $i <= $t['bintang'] ? 'on' : 'off' }}" aria-hidden="true"><use href="#i-star" /></svg>
                            @endfor
                        </div>

                        <p class="mt-4 text-sm leading-relaxed italic flex-1" style="color: #4b5563;">"{{ $t['teks'] }}"</p>

                        <div class="mt-6 flex items-center gap-3.5">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                                style="background-color: var(--gold-bg); color: var(--gold);">
                                {{ $inisial }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold truncate" style="color: var(--ink);">{{ $t['nama'] }}</p>
                                <p class="text-xs mt-0.5" style="color: #9ca3af;">{{ $t['konteks'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- === JAVASCRIPT === --}}
    <script>
        // =====================================================
        // 1. CAROUSEL (slide dibuat sekali, posisi digeser lewat class)
        // =====================================================
        const photos = [{
                src: "{{ asset('images/home/hero-pasangan.jpg') }}",
                title: 'Busana Adat Berpasangan',
                subtitle: 'Pesona budaya dalam balutan busana istimewa'
            },
            {
                src: "{{ asset('images/home/hero-gown.jpg') }}",
                title: 'Gown Pengantin',
                subtitle: 'Elegan dan anggun untuk momen spesial'
            },
            {
                src: "{{ asset('images/home/hero-jas.jpg') }}",
                title: 'Jas Beskap Modern',
                subtitle: 'Sentuhan modern pada busana adat'
            }
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

        // Swipe di layar sentuh
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


        // =====================================================
        // 2. PETA (Leaflet dimuat saat section mendekati layar)
        // =====================================================
        function initMap() {
            const el = document.getElementById('peta-alnay');
            if (!el || el.dataset.ready) return;
            el.dataset.ready = '1';

            const script = document.createElement('script');
            script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
            script.integrity = "sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=";
            script.crossOrigin = "";
            script.onload = function () {
                const alnayLocation = [-5.3971, 105.2668];

                const map = L.map('peta-alnay', {
                    zoomControl: false,
                    scrollWheelZoom: false,
                    dragging: false
                }).setView(alnayLocation, 17);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap',
                    maxZoom: 19
                }).addTo(map);

                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div class="pin-bounce"><div style="background-color: #c99a32; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                        <svg style="transform: rotate(45deg); width: 16px; height: 16px; fill: white;" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                       </div></div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32]
                });

                L.marker(alnayLocation, { icon: customIcon }).addTo(map);

                const googleMapsUrl =
                    `https://www.google.com/maps/search/?api=1&query=${alnayLocation[0]},${alnayLocation[1]}`;

                el.addEventListener('click', function () {
                    window.open(googleMapsUrl, '_blank', 'noopener');
                });
            };
            document.head.appendChild(script);
        }


        // =====================================================
        // 3. INITIALIZATION
        // =====================================================
        document.addEventListener('DOMContentLoaded', function () {
            buildCarousel();
            bindSwipe();
            startAuto();

            // Pause autoplay saat tab tidak aktif
            document.addEventListener('visibilitychange', () => document.hidden ? stopAuto() : startAuto());

            // Peta: muat saat mendekati viewport
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

            // Scroll reveal + stagger otomatis untuk anak dalam .stagger
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