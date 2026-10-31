@extends('layouts.public')

@section('title', 'ALNAY.ID — Jas & Gown Berbudaya')

@section('content')

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
        .font-display {
            font-family: 'Playfair Display', serif;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Responsivitas beranda */
        html,
        body {
            max-width: 100%;
            overflow-x: clip;
        }

        #carousel-container {
            min-width: 0;
            width: 100%;
        }

        #carousel-container>div {
            min-width: 0;
        }

        #carousel-container img {
            display: block;
            max-width: 100%;
        }

        @media (max-width: 639px) {
            #carousel-container {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            #carousel-container>div:first-child,
            #carousel-container>div:last-child {
                display: none;
            }

            #carousel-container>div:nth-child(2) {
                width: 100%;
            }

            #carousel-container>div:nth-child(2)>div {
                left: 0.5rem;
                right: 0.5rem;
                bottom: 0.5rem;
                padding: 0.5rem;
            }

            #carousel-container>div:nth-child(2)>div p:first-child {
                font-size: 9px;
            }

            #carousel-container>div:nth-child(2)>div p:last-child {
                font-size: 8px;
            }
        }

        @media (max-width: 374px) {
            #carousel-container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
        }

        @media (min-width: 640px) {

            #carousel-container>div:first-child,
            #carousel-container>div:last-child {
                display: block;
            }
        }

        /* Mencegah teks panjang mendorong card melewati lebar layar */
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

        /* Header judul section tetap rapi saat lebar layar terbatas */
        @media (max-width: 374px) {
            section .flex.items-end.justify-between {
                flex-wrap: wrap;
                gap: 0.75rem;
            }
        }

        /* === CAROUSEL TRANSITIONS === */
        .carousel-item {
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.5;
            transform: scale(0.9);
            filter: blur(2px);
        }

        .carousel-item.active {
            opacity: 1;
            transform: scale(1);
            filter: blur(0);
        }

        .carousel-item.prev,
        .carousel-item.next {
            opacity: 0.5;
            transform: scale(0.9);
            filter: blur(2px);
        }

        /* Button Hover Effect */
        .carousel-btn {
            transition: all 0.3s ease;
        }

        .carousel-btn:hover {
            transform: scale(1.1);
            background-color: rgba(255, 255, 255, 0.6) !important;
        }

        /* Animasi Marker Peta */
        /* Animasi harus di elemen DALAM; elemen .custom-marker dipakai Leaflet untuk posisi (transform) */
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

        /* Efek Hover pada Container Peta */
        .group:hover #peta-alnay {
            filter: brightness(1.05);
        }

        /* Batasi Z-Index Leaflet Map */
        #peta-alnay {
            position: relative !important;
            z-index: 1 !important;
            isolation: isolate !important;
        }

        #peta-alnay .leaflet-pane,
        #peta-alnay .leaflet-top,
        #peta-alnay .leaflet-bottom,
        #peta-alnay .leaflet-control,
        #peta-alnay .leaflet-map-pane,
        #peta-alnay .leaflet-tile-pane,
        #peta-alnay .leaflet-overlay-pane,
        #peta-alnay .leaflet-shadow-pane,
        #peta-alnay .leaflet-marker-pane,
        #peta-alnay .leaflet-tooltip-pane,
        #peta-alnay .leaflet-popup-pane {
            z-index: 1 !important;
        }

        /* === ANIMASI SCROLL REVEAL === */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.22, 0.8, 0.3, 1), transform 0.8s cubic-bezier(0.22, 0.8, 0.3, 1);
        }

        .reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }

            .pin-bounce {
                animation: none;
            }
        }

        /* === HOVER EFFECT KARTU PRODUK (Lebih Modern) === */
        .card-produk {
            transition: all 0.4s cubic-bezier(0.22, 0.8, 0.3, 1);
            border-radius: 16px;
            border: 1px solid #f3f4f6;
            background-color: white;
            overflow: hidden;
        }

        .card-produk:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 35px -10px rgba(0, 0, 0, 0.1);
            border-color: #e5e7eb;
        }

        /* Zoom in gambar produk saat dihover */
        .card-produk:hover img {
            transform: scale(1.08);
        }

        .card-produk img {
            transition: transform 0.6s cubic-bezier(0.22, 0.8, 0.3, 1);
        }

        /* === TRANSISI TOMBOL (Lebih Lembut) === */
        .btn-smooth {
            transition: all 0.3s ease;
            border-radius: 10px;
        }

        .btn-smooth:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(201, 154, 50, 0.3);
        }

        /* Sembunyikan scrollbar untuk kategori di HP */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    <noscript>
        <style>
            .reveal {
                opacity: 1 !important;
                transform: none !important;
            }
        </style>
    </noscript>


    {{-- ============================================================
     1. HERO SECTION (Carousel 3D)
     ============================================================ --}}
    <section class="overflow-hidden bg-[#17140f]">
        <div class="p-4 sm:p-10 lg:flex lg:items-center lg:gap-8 lg:py-20">
            {{-- KIRI: Teks --}}
            <div class="min-w-0 text-white lg:w-1/2 mb-8 lg:mb-0">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[10px] font-semibold"
                    style="color: #e6c877;">
                    ● Jas &amp; Gown Berbudaya — Elegan dalam Setiap Momen
                </span>

                <h1
                    class="font-display mt-4 text-[28px] leading-tight font-bold min-[375px]:text-[32px] sm:text-[40px] lg:text-[44px]">
                    Jas &amp; Gown Berbudaya, Dicipta untuk Momen Abadi Anda.
                </h1>

                <p class="mt-4 max-w-[460px] text-sm leading-relaxed" style="color: #cfcac0;">
                    Temukan koleksi Fashion Etnik pada ALNAY.ID dengan sentuhan elegan dan modern. Tersedia untuk pembelian
                    maupun penyewaan untuk melengkapi berbagai momen istimewa Anda.
                </p>

                <div class="mt-6 flex flex-col sm:flex-row gap-2.5 sm:gap-3">
                    <a href="{{ route('koleksi.index') }}"
                        class="btn-smooth inline-flex items-center justify-center rounded-md px-6 py-3 text-xs font-bold text-white"
                        style="background-color: #c99a32;">
                        Jelajahi Koleksi Produk
                    </a>
                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener"
                        class="btn-smooth inline-flex items-center justify-center gap-2 rounded-md border border-white/30 px-6 py-3 text-xs font-bold text-white hover:bg-white/10">
                        💬 Konsultasi WhatsApp
                    </a>
                </div>
            </div>

            {{-- KANAN: Carousel 3 Foto --}}
            <div class="relative w-full min-w-0 lg:w-1/2">
                <button type="button" onclick="prevPhoto()" aria-label="Foto sebelumnya"
                    class="carousel-btn absolute left-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 text-white rounded-full p-2 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="flex w-full min-w-0 items-center justify-center gap-2 px-8 sm:px-12" id="carousel-container">
                    {{-- Foto akan di-render oleh JavaScript --}}
                </div>

                <button type="button" onclick="nextPhoto()" aria-label="Foto berikutnya"
                    class="carousel-btn absolute right-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 text-white rounded-full p-2 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </section>
    {{-- ============================================================
     2. KATEGORI (Ikon Bulat)
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 mt-12 md:mt-16">
        {{-- Wrapper luar untuk scroll di HP --}}
        <div class="overflow-x-auto sm:overflow-visible hide-scrollbar py-4 -mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="flex w-max min-w-full gap-4 sm:w-full sm:gap-12 sm:flex-wrap sm:justify-center snap-x snap-mandatory">
                @foreach ($kategori as $k)
                    @continue(empty($k['kode_kategori']))
                    <a href="{{ route('koleksi.kategori', ['kode' => $k['kode_kategori']]) }}"
                        class="group flex w-20 sm:w-24 md:w-28 shrink-0 snap-center flex-col items-center gap-2 sm:gap-3 text-center transition-all duration-300 hover:-translate-y-1">

                        {{-- Lingkaran Foto Kategori --}}
                        <div class="relative h-20 w-20 shrink-0 md:h-24 md:w-24 overflow-hidden rounded-full border-2 border-gray-100 bg-white shadow-sm transition-all duration-300 group-hover:border-gold group-hover:shadow-md">
                            @if (!empty($k['foto']))
                                <img src="{{ asset($k['foto']) }}" alt="{{ $k['nama_kategori'] }}" loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div
                                    class="flex h-full w-full items-center justify-center font-display text-2xl text-gold bg-gold/5">
                                    {{ mb_substr($k['nama_kategori'], 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- Nama Kategori --}}
                        <div class="w-full">
                            <p class="text-xs font-semibold text-ink group-hover:text-gold transition-colors leading-tight">
                                {{ $k['nama_kategori'] }}
                            </p>
                            @if (!empty($k['subjudul']))
                                <p class="text-[10px] text-gray-400 mt-0.5 truncate">{{ $k['subjudul'] }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div> {{-- Tutup div flex --}}
        </div> {{-- Tutup div overflow-x-auto --}}
    </section>


    {{-- ============================================================
     3. PRODUK TERBARU
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 py-12 md:py-20">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
                <h2 class="font-display text-2xl font-bold sm:text-3xl" style="color: #15233d;">Produk Terbaru</h2>
                <p class="mt-1.5 text-sm" style="color: #6b7280;">Rilisan busana adat paling baru</p>
            </div>
            <a href="{{ route('koleksi.index') }}"
                class="btn-smooth whitespace-nowrap text-sm font-semibold hover:underline" style="color: #c99a32;">
                Lihat Semua →
            </a>
        </div>

        @if (count($produkTerbaru) > 0)
            <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-6 md:grid-cols-3 lg:grid-cols-4 items-stretch">
                @foreach ($produkTerbaru as $index => $p)
                    <x-product-card :produk="$p" :delay="min(0.1 + $index * 0.08, 0.8)" />
                @endforeach
            </div>
        @endif
    </section>


    {{-- ============================================================
     4. PRODUK TERPOPULER (Badge TOP dihilangkan)
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 py-12 md:py-20">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
                <h2 class="font-display text-2xl font-bold sm:text-3xl" style="color: #15233d;">Produk Terpopuler</h2>
                <p class="mt-1.5 text-sm" style="color: #6b7280;">Favorit pilihan pelanggan dari beberapa waktu terakhir</p>
            </div>
            <a href="{{ route('koleksi.index') }}"
                class="btn-smooth whitespace-nowrap text-sm font-semibold hover:underline" style="color: #c99a32;">
                Lihat Semua →
            </a>
        </div>

        @if (count($produkTerpopuler) > 0)
            <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-6 md:grid-cols-3 lg:grid-cols-4 items-stretch">
                @foreach ($produkTerpopuler as $index => $p)
                    <x-product-card :produk="$p" :delay="min(0.1 + $index * 0.08, 0.8)" :peringkat="$index + 1" />
                @endforeach
            </div>
        @endif
    </section>


    {{-- ============================================================
     6. EVENT BANNER (Satu Card Ringkas)
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 py-8 md:py-12">
        <a href="https://eventalnay.id/beranda" target="_blank" rel="noopener"
            class="group flex flex-col md:flex-row items-center justify-between gap-6 rounded-3xl p-6 md:p-10 transition-all duration-500 hover:shadow-2xl hover:-translate-y-1"
            style="background-color: #1a1610;">

            {{-- Kiri: Teks --}}
            <div class="flex-1 min-w-0">
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                    style="background-color: #c99a32; color: white;">
                    ● Event Spesial ALNAY.ID
                </span>

                <h2 class="font-display text-xl md:text-3xl font-bold text-white mt-4 leading-snug">
                    Ikuti Event Budaya &amp; Menangkan Hadiah Menarik
                </h2>

                <p class="text-sm md:text-base text-gray-400 mt-3 leading-relaxed max-w-xl">
                    Kompetisi busana adat, workshop kebudayaan, hingga event seru lainnya. Semua ada di satu tempat!
                </p>
            </div>

            {{-- Kanan: Tombol --}}
            <div class="w-full md:w-auto md:shrink-0 mt-4 md:mt-0">
                <span
                    class="inline-flex items-center justify-center gap-2 w-full md:w-auto px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider transition-all duration-300 group-hover:gap-4"
                    style="background-color: #c99a32; color: white;">
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
     7. OPSI FLEKSIBEL: SEWA ATAU BELI
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="text-center mb-12">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em]" style="color: #c99a32;">Transparansi Mutu</p>
            <h2 class="font-display text-2xl md:text-4xl font-bold mt-3" style="color: #15233d;">Opsi Fleksibel: Sewa atau
                Beli</h2>
            <p class="text-sm md:text-base text-gray-500 mt-3 max-w-2xl mx-auto leading-relaxed">
                Kejelasan komitmen higienitas, fitting presisi, dan garansi busana tanpa biaya tersembunyi.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 max-w-4xl mx-auto">
            {{-- Card 1: Layanan Sewa Busana --}}
            <div class="rounded-3xl border p-8 md:p-10 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl"
                style="border-color: #f3f4f6; background-color: white;">
                <span class="inline-block px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                    style="background-color: #fef3c7; color: #92400e;">
                    Paling Diminati
                </span>
                <h3 class="font-display text-xl md:text-2xl font-bold mt-4" style="color: #15233d;">Layanan Sewa Busana
                </h3>
                <p class="text-sm text-gray-600 mt-3 leading-relaxed">Solusi praktis dan hemat untuk akad nikah, resepsi,
                    wisuda, atau pentas budaya.</p>

                <ul class="mt-6 space-y-3 text-sm">
                    <li class="flex items-start gap-3">
                        <span class="text-lg leading-none" style="color: #c99a32;">✓</span>
                        <span style="color: #374151;"><strong>Durasi Sewa:</strong> Fleksibel 3 sampai 7 hari kerja.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-lg leading-none" style="color: #c99a32;">✓</span>
                        <span style="color: #374151;"><strong>Gratis Alteration:</strong> Penyesuaian ukuran pinggang &
                            kancing aman.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-lg leading-none" style="color: #c99a32;">✓</span>
                        <span style="color: #374151;"><strong>Termasuk Laundry:</strong> Busana kembali tidak perlu dicuci
                            sendiri, harum dan wangi sekali!</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-lg leading-none" style="color: #c99a32;">✓</span>
                        <span style="color: #374151;"><strong>Steril & Higienis:</strong> Ozon steaming dan garment bag
                            anti debu.</span>
                    </li>
                </ul>

                <a href="{{ route('koleksi.index') }}"
                    class="btn-smooth block w-full mt-8 py-3.5 rounded-xl text-center text-xs font-bold uppercase tracking-wider"
                    style="background-color: #c99a32; color: white;">
                    Eksplorasi Katalog Sewa
                </a>
            </div>

            {{-- Card 2: Beli & Custom Bespoke --}}
            <div class="rounded-3xl p-8 md:p-10 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl"
                style="background-color: #1a1610;">
                <h3 class="font-display text-xl md:text-2xl font-bold text-white mt-4 md:mt-10">Beli & Custom Bespoke</h3>
                <p class="text-sm text-gray-400 mt-3 leading-relaxed">Mahakarya eksklusif milik selamanya sebagai busana
                    warisan keluarga.</p>

                <ul class="mt-6 space-y-3 text-sm">
                    <li class="flex items-start gap-3">
                        <span class="text-lg leading-none" style="color: #c99a32;">✓</span>
                        <span style="color: #e5e7eb;"><strong>Full Tailoring Personal:</strong> Disesuaikan 100% dengan
                            lekuk tubuh.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-lg leading-none" style="color: #c99a32;">✓</span>
                        <span style="color: #e5e7eb;"><strong>Material Sutera & Songket Asli:</strong> Pilihan bahan tenun
                            tangan terbaik.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-lg leading-none" style="color: #c99a32;">✓</span>
                        <span style="color: #e5e7eb;"><strong>Sulam Emas Tradisional:</strong> Dikerjakan manual oleh
                            perajin adibusana.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-lg leading-none" style="color: #c99a32;">✓</span>
                        <span style="color: #e5e7eb;"><strong>Garansi Fitting Ulang:</strong> Revisi hingga nyaman dan pas
                            dipakai.</span>
                    </li>
                </ul>

                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank"
                    class="btn-smooth block w-full mt-8 py-3.5 rounded-xl border text-center text-xs font-bold uppercase tracking-wider hover:bg-white/10"
                    style="border-color: #374151; color: white;">
                    Konsultasi Bespoke
                </a>
            </div>
        </div>
    </section>


    {{-- ============================================================
     8. OFFLINE ATELIER & STUDIO
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 py-16 md:py-24 bg-gray-50/50">
        <div class="rounded-3xl border p-6 md:p-12 transition-all duration-300 hover:shadow-xl bg-white"
            style="border-color: #f3f4f6;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                {{-- Left: Text Content --}}
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em]" style="color: #c99a32;">Galeri & Ruang
                        Fitting</p>
                    <h2 class="font-display text-2xl md:text-3xl font-bold mt-3" style="color: #15233d;">Offline Atelier &
                        Studio ALNAY.ID</h2>
                    <p class="text-sm md:text-base text-gray-600 mt-4 leading-relaxed">
                        Kunjungi studio kami untuk konsultasi langsung, fitting presisi, dan melihat koleksi secara nyata.
                        Dilengkapi ruang fitting privat ber-AC dan penjahit profesional siap membantu.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank"
                            class="btn-smooth inline-block px-6 py-3.5 rounded-xl border text-xs font-bold uppercase tracking-wider hover:bg-gray-50"
                            style="border-color: #15233d; color: #15233d;">
                            Info Lokasi & Booking
                        </a>
                        <a href="#"
                            class="btn-smooth inline-block px-6 py-3.5 rounded-xl border text-xs font-bold uppercase tracking-wider hover:bg-gray-50"
                            style="border-color: #d1d5db; color: #374151;">
                            Panduan Sewa Online
                        </a>
                    </div>
                </div>

                {{-- Right: Map Container --}}
                <div class="relative rounded-2xl overflow-hidden group cursor-pointer border shadow-sm"
                    style="border-color: #f3f4f6; z-index: 1;">
                    <div id="peta-alnay"
                        class="w-full h-[280px] md:h-[320px] transition-transform duration-500 group-hover:scale-105">
                    </div>

                    <div class="absolute top-4 left-4 right-4 p-4 rounded-xl shadow-lg transition-all duration-300 group-hover:-translate-y-1"
                        style="background-color: rgba(23, 20, 15, 0.92); backdrop-filter: blur(6px); z-index: 10;">
                        <p class="text-white text-sm font-bold flex items-center gap-2">
                            <svg class="w-4 h-4" style="color: #c99a32;" fill="currentColor" viewBox="0 0 20 20">
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
     5. TESTIMONI
     ============================================================ --}}
    <section class="reveal max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-10">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest" style="color: #c99a32;">Kisah Bahagia Bersama
                    ALNAY.ID</p>
                <h2 class="font-display mt-2 text-2xl md:text-3xl font-bold" style="color: #15233d;">Testimoni Pengantin
                    &amp; Klien</h2>
            </div>

            <div class="flex flex-col items-start gap-1 sm:items-end">
                <p class="text-sm font-medium" style="color: #6b7280;">Kepuasan ★ 4.97 dari 1.800+ Acara</p>
                <a href="{{ route('testimoni') }}"
                    class="btn-smooth whitespace-nowrap text-sm font-semibold hover:underline"
                    style="color: #c99a32;">Lihat Semua Testimoni →</a>
            </div>
        </div>

        @if (count($testimoni) > 0)
            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($testimoni as $t)
                    @php
                        $inisial = strtoupper(mb_substr($t['nama'], 0, 2));
                    @endphp

                    <div
                        class="card-produk rounded-2xl p-4 sm:p-6 md:p-7 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                        <p class="text-base" style="color: #c99a32;">
                            @for ($i = 1; $i <= 5; $i++)
                                {{ $i <= $t['bintang'] ? '★' : '☆' }}
                            @endfor
                        </p>

                        <p class="mt-4 text-sm leading-relaxed italic" style="color: #4b5563;">"{{ $t['teks'] }}"</p>

                        <div class="mt-6 flex items-center gap-3.5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-bold"
                                style="background-color: #fbf6e9; color: #c99a32;">
                                {{ $inisial }}
                            </div>
                            <div>
                                <p class="text-sm font-bold" style="color: #15233d;">{{ $t['nama'] }}</p>
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
        // 1. CAROUSEL
        // =====================================================
        let currentIndex = 0;
        let isAnimating = false;

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

        function renderCarousel() {
            const container = document.getElementById('carousel-container');
            if (!container) return;
            const prevIndex = (currentIndex - 1 + photos.length) % photos.length;
            const nextIndex = (currentIndex + 1) % photos.length;
            container.style.transition = 'opacity 0.4s ease';
            container.style.opacity = '0.7';
            setTimeout(() => {
                container.innerHTML = `
            <div class="hidden sm:block w-[25%] min-w-0 opacity-50 scale-90 transition-all duration-500 ease-out">
                <img src="${photos[prevIndex].src}" alt="${photos[prevIndex].title}" class="rounded-xl w-full h-[250px] sm:h-[350px] object-cover">
            </div>
            <div class="relative z-10 w-full min-w-0 sm:w-[40%] scale-100 transition-all duration-500 ease-out">
                <img src="${photos[currentIndex].src}" alt="${photos[currentIndex].title}" class="rounded-xl w-full h-[220px] sm:h-[400px] object-cover border-4" style="border-color: #c99a32;">
                    <div class="absolute bottom-1 left-1 right-1 bg-black/60 p-1.5 rounded-md text-white sm:bottom-4 sm:left-4 sm:right-4 sm:p-3">
                        <p class="text-[9px] sm:text-[10px] font-semibold uppercase tracking-wider" style="color: #e6c877;">${photos[currentIndex].title}</p>
                        <p class="text-[8px] sm:text-[9px]" style="color: #d1d1d1;">${photos[currentIndex].subtitle}</p>
                    </div>
                </div>
                    <div class="hidden sm:block w-[25%] min-w-0 opacity-50 scale-90 transition-all duration-500 ease-out">
                    <img src="${photos[nextIndex].src}" alt="${photos[nextIndex].title}" class="rounded-xl w-full h-[250px] sm:h-[350px] object-cover">
                </div>
            `;
                setTimeout(() => {
                    container.style.opacity = '1';
                }, 50);
            }, 200);
        }

        function nextPhoto() {
            if (isAnimating) return;
            isAnimating = true;
            currentIndex = (currentIndex + 1) % photos.length;
            renderCarousel();
            setTimeout(() => {
                isAnimating = false;
            }, 500);
        }

        function prevPhoto() {
            if (isAnimating) return;
            isAnimating = true;
            currentIndex = (currentIndex - 1 + photos.length) % photos.length;
            renderCarousel();
            setTimeout(() => {
                isAnimating = false;
            }, 500);
        }


        // =====================================================
        // 2. INITIALIZATION (Peta + Carousel + Scroll Reveal)
        // =====================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Render carousel pertama kali
            renderCarousel();

            // Load Leaflet JS untuk peta
            const script = document.createElement('script');
            script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
            script.integrity = "sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=";
            script.crossOrigin = "";
            script.onload = function() {
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

                L.marker(alnayLocation, {
                    icon: customIcon
                }).addTo(map);

                const googleMapsUrl =
                    `https://www.google.com/maps/search/?api=1&query=${alnayLocation[0]},${alnayLocation[1]}`;

                document.getElementById('peta-alnay').addEventListener('click', function() {
                    window.open(googleMapsUrl, '_blank');
                });
            };
            document.head.appendChild(script);

            // === ANIMASI SCROLL REVEAL ===
            const reveals = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -40px 0px'
            });

            reveals.forEach(el => observer.observe(el));
        });
    </script>
@endsection