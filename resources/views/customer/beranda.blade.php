@extends('layouts.public')

@section('title', 'ALNAY.ID — Jas & Gown Berbudaya')

@section('content')

    {{-- Leaflet CSS untuk Peta Interaktif --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

        .font-display { font-family: 'Playfair Display', serif; }
        body { font-family: 'Inter', sans-serif; }

        html, body { max-width: 100%; overflow-x: clip; }

        #carousel-container { min-width: 0; width: 100%; }
        #carousel-container > div { min-width: 0; }
        #carousel-container img { display: block; max-width: 100%; }

        @media (max-width: 639px) {
            #carousel-container { padding-left: 2.5rem; padding-right: 2.5rem; }
            #carousel-container > div:first-child, #carousel-container > div:last-child { display: none; }
            #carousel-container > div:nth-child(2) { width: 100%; }
            #carousel-container > div:nth-child(2) > div { left: 0.5rem; right: 0.5rem; bottom: 0.5rem; padding: 0.5rem; }
            #carousel-container > div:nth-child(2) > div p:first-child { font-size: 9px; }
            #carousel-container > div:nth-child(2) > div p:last-child { font-size: 8px; }
        }

        @media (max-width: 374px) { #carousel-container { padding-left: 2rem; padding-right: 2rem; } }
        @media (min-width: 640px) { #carousel-container > div:first-child, #carousel-container > div:last-child { display: block; } }

        .card-shadow { min-width: 0; box-shadow: 0 10px 24px rgba(0, 0, 0, .08); }
        .card-shadow h3, .card-shadow p, section h1, section h2, section h3 { overflow-wrap: anywhere; }

        .carousel-item { transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0.5; transform: scale(0.9); filter: blur(2px); }
        .carousel-item.active { opacity: 1; transform: scale(1); filter: blur(0); }
        
        .carousel-btn { transition: all 0.3s ease; }
        .carousel-btn:hover { transform: scale(1.1); background-color: rgba(255, 255, 255, 0.6) !important; }

        .custom-marker { animation: bounce-marker 2s infinite ease-in-out; }
        @keyframes bounce-marker {
            0%, 100% { transform: translateY(0) rotate(-45deg); }
            50% { transform: translateY(-8px) rotate(-45deg); }
        }

        #peta-alnay { position: relative !important; z-index: 1 !important; isolation: isolate !important; }
        #peta-alnay .leaflet-pane, #peta-alnay .leaflet-top, #peta-alnay .leaflet-bottom, #peta-alnay .leaflet-control, #peta-alnay .leaflet-map-pane { z-index: 1 !important; }
        #peta-alnay .leaflet-tile-pane { z-index: 1 !important; }
        #peta-alnay .leaflet-overlay-pane { z-index: 2 !important; }
        #peta-alnay .leaflet-shadow-pane { z-index: 3 !important; }
        #peta-alnay .leaflet-marker-pane { z-index: 4 !important; }
        #peta-alnay .leaflet-tooltip-pane { z-index: 5 !important; }
        #peta-alnay .leaflet-popup-pane { z-index: 6 !important; }
    </style>

    {{-- ============================================================
     1. HERO SECTION (Carousel 3D)
     ============================================================ --}}
<section class="overflow-hidden bg-[#17140f]">
    <div class="p-4 sm:p-10 lg:flex lg:items-center lg:gap-8 lg:py-22">
        {{-- KIRI: Teks --}}
        <div class="min-w-0 text-white lg:w-1/2 mb-8 lg:mb-0">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[10px] font-semibold" style="color: #e6c877;">
                ● Jas &amp; Gown Berbudaya — Elegan dalam Setiap Momen
            </span>

            <h1 class="font-display mt-4 text-[28px] leading-tight font-bold min-[375px]:text-[32px] sm:text-[40px] lg:text-[44px]">
                Jas &amp; Gown Berbudaya, Dicipta untuk Momen Abadi Anda.
            </h1>

            <p class="mt-4 max-w-[460px] text-sm leading-relaxed" style="color: #cfcac0;">
                Temukan koleksi Fashion Etnik pada ALNAY.ID dengan sentuhan elegan dan modern. Tersedia untuk pembelian maupun penyewaan untuk melengkapi berbagai momen istimewa Anda.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('koleksi.index') }}"
                   class="inline-flex items-center rounded-md px-6 py-3 text-xs font-bold text-white transition-colors"
                   style="background-color: #c99a32;">
                    Jelajahi Koleksi Produk
                </a>
                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 rounded-md border border-white/30 px-6 py-3 text-xs font-bold text-white transition-colors hover:bg-white/10">
                    💬 Konsultasi WhatsApp
                </a>
            </div>
        </div>

        {{-- KANAN: Carousel 3 Foto --}}
        <div class="relative w-full min-w-0 lg:w-1/2">
            <button onclick="prevPhoto()" 
                    class="absolute left-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 text-white rounded-full p-2 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="flex w-full min-w-0 items-center justify-center gap-2 px-8 sm:px-12" id="carousel-container">
                {{-- Foto akan di-render oleh JavaScript --}}
            </div>

            <button onclick="nextPhoto()" 
                    class="absolute right-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 text-white rounded-full p-2 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</section>


{{-- ============================================================
     2. KATEGORI (Styling Sesuai Desain Lama)
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 mt-10">
    <div class="flex flex-wrap justify-center gap-8 sm:gap-12">
        @foreach ($kategori as $k)
            <a href="{{ route('koleksi.kategori', ['kode' => $k['kode_kategori']]) }}"
               class="group flex w-28 md:w-32 flex-col items-center gap-3 text-center">

                <div class="h-24 w-24 md:h-28 md:w-28 overflow-hidden rounded-full border" style="border-color: #e5e7eb; background-color: white;">
                    @if (! empty($k['foto']))
                        <img src="{{ asset($k['foto']) }}" alt="{{ $k['nama_kategori'] }}" loading="lazy" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center font-display text-2xl" style="color: #c99a32;">
                            {{ mb_substr($k['nama_kategori'], 0, 1) }}
                        </div>
                    @endif
                </div>

                <div>
                    <p class="text-sm font-semibold group-hover:text-[#c99a32] transition-colors" style="color: #15233d;">
                        {{ $k['nama_kategori'] }}
                    </p>
                    @if (! empty($k['subjudul']))
                        <p class="text-xs" style="color: #9ca3af;">{{ $k['subjudul'] }}</p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</section>


{{-- ============================================================
     3. PRODUK TERBARU (Styling Sesuai Desain Lama)
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="font-display text-xl font-bold sm:text-2xl" style="color: #15233d;">Produk Terbaru</h2>
            <p class="mt-1 text-xs" style="color: #6b7280;">Rilisan busana adat paling baru</p>
        </div>

        <a href="{{ route('koleksi.index') }}" class="whitespace-nowrap text-xs font-semibold hover:underline" style="color: #c99a32;">
            Lihat Semua →
        </a>
    </div>

    @if (count($produkTerbaru) > 0)
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($produkTerbaru as $p)
                @php
                    $badge = $p['stok'] > 0 ? 'Tersedia' : 'Habis';
                    $badgeColor = $p['stok'] > 0 ? '#2f7a4a' : '#c0392b';
                    $tag = $p['dapat_dijual'] ? 'Sewa & Beli' : 'Khusus Sewa';
                    $mode = $p['dapat_dijual'] ? 'dua' : 'satu';
                @endphp

                {{-- PERBAIKAN: flex flex-col h-full --}}
                <div class="card-shadow overflow-hidden rounded-lg border flex flex-col h-full" 
                     style="border-color: #f3f4f6; background-color: white;">
                    
                    <div class="relative h-[200px]">
                        @if ($p['foto_produk'])
                            <img src="{{ asset($p['foto_produk']) }}" alt="{{ $p['nama'] }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center" style="background-color: #f5f0e6;">
                                <span style="color: #15233d;">{{ $p['nama'] }}</span>
                            </div>
                        @endif

                        <span class="absolute left-2.5 top-2.5 rounded px-2 py-1 text-[9px] font-bold uppercase text-white"
                              style="background-color: {{ $badgeColor }};">
                            {{ $badge }}
                        </span>

                        <span class="absolute bottom-2.5 left-2.5 rounded bg-black/70 px-2 py-1 text-[9px] font-semibold text-white">
                            {{ $tag }}
                        </span>
                    </div>

                    {{-- PERBAIKAN: flex-grow untuk mendorong button ke bawah --}}
                    <div class="p-3 flex-grow flex flex-col">
                        <h3 class="text-[13px] font-bold" style="color: #15233d;">{{ $p['nama'] }}</h3>
                        <p class="mt-0.5 text-[11px]" style="color: #6b7280;">{{ $p['deskripsi'] }}</p>

                        <div class="mt-2.5 space-y-0.5 text-[11px]">
                            <div class="flex justify-between">
                                <span style="color: #6b7280;">Sewa:</span>
                                <span class="font-bold" style="color: #15233d;">Rp {{ number_format($p['harga_sewa'], 0, ',', '.') }}</span>
                            </div>

                            @if($p['dapat_dijual'])
                                <div class="flex justify-between">
                                    <span style="color: #6b7280;">Beli Baru:</span>
                                    <span class="font-bold" style="color: #c99a32;">Rp {{ number_format($p['harga_jual'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- PERBAIKAN: Button section tetap di bawah --}}
                    <div class="p-3 pt-0">
                        <div class="flex gap-2">
                            @if($mode === 'dua')
                                <a href="#" class="flex-1 rounded-md border py-2 text-center text-[10px] font-bold hover:bg-gray-50"
                                   style="border-color: #d1d5db; color: #333;">Sewa</a>
                                <a href="#" class="flex-1 rounded-md py-2 text-center text-[10px] font-bold text-white"
                                   style="background-color: #c99a32;">Beli Baru</a>
                            @else
                                <a href="#" class="w-full rounded-md py-2 text-center text-[10px] font-bold text-white"
                                   style="background-color: #c99a32;">Sewa Sekarang</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>


{{-- ============================================================
     4. PRODUK TERPOPULER (Styling Sesuai Desain Lama)
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="font-display text-xl font-bold sm:text-2xl" style="color: #15233d;">Produk Terpopuler</h2>
            <p class="mt-1 text-xs" style="color: #6b7280;">Favorit pilihan pelanggan dari beberapa waktu terakhir</p>
        </div>

        <a href="{{ route('koleksi.index') }}" class="whitespace-nowrap text-xs font-semibold hover:underline" style="color: #c99a32;">
            Lihat Semua →
        </a>
    </div>

    @if (count($produkTerpopuler) > 0)
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($produkTerpopuler as $index => $p)
                @php
                    $badge = $p['stok'] > 0 ? 'Tersedia' : 'Habis';
                    $badgeColor = $p['stok'] > 0 ? '#2f7a4a' : '#c0392b';
                    $tag = $p['dapat_dijual'] ? 'Sewa & Beli' : 'Khusus Sewa';
                    $mode = $p['dapat_dijual'] ? 'dua' : 'satu';
                @endphp

                <div class="card-shadow overflow-hidden rounded-lg border flex flex-col h-full" 
                     style="border-color: #f3f4f6; background-color: white;">
                    
                    <div class="relative h-[200px]">
                        @if ($p['foto_produk'])
                            <img src="{{ asset($p['foto_produk']) }}" alt="{{ $p['nama'] }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center" style="background-color: #f5f0e6;">
                                <span style="color: #15233d;">{{ $p['nama'] }}</span>
                            </div>
                        @endif

                        {{-- Badge TOP --}}
                        <span class="absolute left-2.5 top-2.5 rounded px-2 py-1 text-[9px] font-bold text-white"
                              style="background-color: #c99a32;">
                            TOP #{{ $index + 1 }}
                        </span>

                        {{-- Badge Tersedia/Habis --}}
                        <span class="absolute right-2.5 top-2.5 rounded px-2 py-1 text-[9px] font-bold uppercase text-white"
                              style="background-color: {{ $badgeColor }};">
                            {{ $badge }}
                        </span>

                        {{-- Badge Sewa & Beli --}}
                        <span class="absolute bottom-2.5 left-2.5 rounded bg-black/70 px-2 py-1 text-[9px] font-semibold text-white">
                            {{ $tag }}
                        </span>
                    </div>

                    <div class="p-3 flex-grow flex flex-col">
                        {{-- Rating --}}
                        <p class="text-[10px] font-semibold" style="color: #c99a32;">
                            ★ {{ number_format($p['rating_rata_rata'], 1) }} 
                            <span style="color: #6b7280;">({{ $p['jumlah_rating'] }} ulasan)</span>
                        </p>

                        <h3 class="mt-1 text-[13px] font-bold" style="color: #15233d;">{{ $p['nama'] }}</h3>
                        <p class="mt-0.5 text-[11px]" style="color: #6b7280;">{{ $p['deskripsi'] }}</p>

                        <div class="mt-2.5 space-y-0.5 text-[11px]">
                            <div class="flex justify-between">
                                <span style="color: #6b7280;">Sewa:</span>
                                <span class="font-bold" style="color: #15233d;">Rp {{ number_format($p['harga_sewa'], 0, ',', '.') }}</span>
                            </div>

                            @if($p['dapat_dijual'])
                                <div class="flex justify-between">
                                    <span style="color: #6b7280;">Beli Baru:</span>
                                    <span class="font-bold" style="color: #c99a32;">Rp {{ number_format($p['harga_jual'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-3 pt-0">
                        <div class="flex gap-2">
                            @if($mode === 'dua')
                                <a href="#" class="flex-1 rounded-md border py-2 text-center text-[10px] font-bold hover:bg-gray-50"
                                   style="border-color: #d1d5db; color: #333;">Sewa</a>
                                <a href="#" class="flex-1 rounded-md py-2 text-center text-[10px] font-bold text-white"
                                   style="background-color: #111111;">Beli Baru</a>
                            @else
                                <a href="#" class="w-full rounded-md py-2 text-center text-[10px] font-bold text-white"
                                   style="background-color: #c99a32;">Sewa Sekarang</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- ============================================================
     6. EVENT BANNERS (Section Baru)
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Banner 1: Vote Event --}}
        <div class="rounded-2xl overflow-hidden relative" style="background-color: #1a1610;">
            <div class="p-6 md:p-8 relative z-10">
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" style="background-color: #c99a32; color: white;">
                    ● Sedang Berlangsung
                </span>

                <h1 class="font-display mt-4 text-[28px] leading-tight font-bold min-[375px]:text-[32px] sm:text-[40px] lg:text-[44px]">
                    Jas &amp; Gown Berbudaya, Dicipta untuk Momen Abadi Anda.
                </h1>

                <p class="mt-4 max-w-[460px] text-sm leading-relaxed" style="color: #cfcac0;">
                    Temukan koleksi Fashion Etnik pada ALNAY.ID dengan sentuhan elegan dan modern. Tersedia untuk pembelian maupun penyewaan untuk melengkapi berbagai momen istimewa Anda.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('koleksi.index') }}" class="inline-flex items-center rounded-md px-6 py-3 text-xs font-bold text-white transition-colors" style="background-color: #c99a32;">
                        Jelajahi Koleksi Produk
                    </a>
                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-md border border-white/30 px-6 py-3 text-xs font-bold text-white transition-colors hover:bg-white/10">
                        💬 Konsultasi WhatsApp
                    </a>
                </div>
            </div>

            <div class="relative w-full min-w-0 lg:w-1/2">
                <button onclick="prevPhoto()" class="absolute left-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 text-white rounded-full p-2 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>

                <div class="flex w-full min-w-0 items-center justify-center gap-2 px-8 sm:px-12" id="carousel-container"></div>

                <button onclick="nextPhoto()" class="absolute right-0 top-1/2 -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 text-white rounded-full p-2 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
        </div>
    </section>

    {{-- ============================================================
     2. BARIS KATEGORI
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <div class="grid grid-cols-3 md:grid-cols-6 gap-4 md:gap-6">
            @foreach ($kategori as $kat)
                <a href="{{ route('koleksi.index', ['kategori' => [$kat->slug]]) }}" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-cream-dim border border-line flex items-center justify-center mb-3 group-hover:border-gold group-hover:bg-gold/5 transition-all duration-300 overflow-hidden shadow-sm">
                        <span class="text-xl md:text-2xl font-display font-bold text-ink/30 group-hover:text-gold transition-colors">
                            {{ substr($kat->nama_kategori, 0, 1) }}
                        </span>
                    </div>
                    <span class="text-xs md:text-sm font-bold text-ink group-hover:text-gold transition-colors leading-tight">{{ $kat->nama_kategori }}</span>
                    <span class="text-[10px] text-muted mt-1">{{ $kat->produks_count ?? 0 }} Produk</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============================================================
     3. PRODUK TERBARU
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <x-section-heading title="Produk Terbaru" subtitle="Rilisan busana adat paling baru" linkText="Lihat Semua" :linkHref="route('koleksi.index')" />
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach ($produkTerbaru as $p)
                <x-product-card :produk="$p" />
            @endforeach
        </div>
    </section>

    {{-- ============================================================
     4. PRODUK TERPOPULER
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <x-section-heading title="Produk Terpopuler" subtitle="Favorit pilihan pelanggan dari beberapa waktu terakhir" linkText="Lihat Semua" :linkHref="route('koleksi.index')" />
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach ($produkTerpopuler as $i => $p)
                <x-product-card :produk="$p" />
            @endforeach
        </div>
    </section>

    {{-- ============================================================
     5. OPSI FLEKSIBEL: SEWA ATAU BELI
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <p class="text-[10px] uppercase tracking-[0.25em] text-gold mb-3">Transparansi Mutu</p>
            <h2 class="font-display text-2xl md:text-3xl text-ink mb-3">Opsi Fleksibel: Sewa atau Beli</h2>
            <p class="text-sm text-muted">Keputusan komitmen tinggi, pricing press, dan garansi busana tanpa biaya terselubung.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-4 md:gap-6">
            {{-- Sewa --}}
            <div class="bg-cream-dim border border-line rounded-2xl p-6 md:p-8 relative">
                <span class="absolute top-4 right-4 px-3 py-1 rounded-full bg-gold text-white text-[10px] font-bold uppercase tracking-wider">Paling Diminati</span>
                <h3 class="font-display text-xl md:text-2xl text-ink mb-3">Layanan Sewa Busana</h3>
                <p class="text-sm text-muted mb-6">Solusi praktis untuk tampil maksimal. Reserve, ambil, dan kembalikan sesuai jadwal.</p>
                <ul class="space-y-3 text-sm text-ink mb-8">
                    <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold mt-0.5 shrink-0" /> Durasi Sewa Fleksibel — pilih tanggal ambil & tanggal kembali.</li>
                    <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold mt-0.5 shrink-0" /> Gratis Atelier — pengukuran akhir, pencucian, dan pengemasan.</li>
                    <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold mt-0.5 shrink-0" /> Termasuk Laundry — busana kembali bersih siap dipakai.</li>
                    <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold mt-0.5 shrink-0" /> Bersih & Higienis — busana diperiksa dan disterilkan.</li>
                </ul>
                <a href="{{ route('cara-sewa') }}" class="block text-center px-6 py-3 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold/90 transition-colors min-h-[44px]">Baca Panduan Sewa</a>
            </div>

            {{-- Beli --}}
            <div class="bg-ink text-cream rounded-2xl p-6 md:p-8">
                <h3 class="font-display text-xl md:text-2xl mb-3">Beli & Custom Bespoke</h3>
                <p class="text-sm text-cream/60 mb-6">Miliki busana yang benar-benar milik Anda dengan opsi custom penuh.</p>
                <ul class="space-y-3 text-sm text-cream/90 mb-8">
                    <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold-soft mt-0.5 shrink-0" /> Pola Tailoring Personal — disesuaikan dengan postur tubuh.</li>
                    <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold-soft mt-0.5 shrink-0" /> Pilihan Bahan & Sentuhan Artistik — sesuai karakter Anda.</li>
                    <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold-soft mt-0.5 shrink-0" /> Garansi Fitting Ulang — revisi hingga nyaman.</li>
                    <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold-soft mt-0.5 shrink-0" /> Produk Milik Selamanya — tidak ada kewajiban kembali.</li>
                </ul>
                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" rel="noopener" class="block text-center px-6 py-3 rounded-full border border-gold-soft text-gold-soft text-sm font-semibold hover:bg-gold-soft/10 transition-colors min-h-[44px]">Konsultasi Custom</a>
            </div>
        </div>
    </section>

    {{-- ============================================================
     6. OFFLINE ATELIER & PETA
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <div class="bg-panel border border-line rounded-2xl p-6 md:p-10 grid md:grid-cols-2 gap-8 items-center">
            <div>
                <p class="text-[10px] uppercase tracking-[0.25em] text-gold mb-3">Galeri & Ruang Fitting</p>
                <h2 class="font-display text-2xl md:text-3xl text-ink mb-4">Offline Atelier & Studio ALNAY.ID</h2>
                <p class="text-sm text-muted leading-relaxed mb-6">
                    Temui tim kami secara langsung untuk fitting personal, konsultasi desain, dan pengalaman memilih bahan secara tatap muka. Booking terlebih dahulu untuk memastikan slot tersedia.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('fitting.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-ink text-cream text-sm font-semibold hover:bg-ink/90 transition-colors min-h-[44px]">
                        <x-icon name="location" :size="16" /> Info Lokasi & Booking
                    </a>
                    <a href="{{ route('cara-sewa') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-ink/20 text-ink text-sm font-semibold hover:bg-cream transition-colors min-h-[44px]">Panduan Sewa Online</a>
                </div>
            </div>

            {{-- Peta --}}
            <div class="relative rounded-xl overflow-hidden group cursor-pointer border border-line" style="z-index: 1;">
                <div id="peta-alnay" class="w-full h-[250px] md:h-[300px] transition-transform duration-500 group-hover:scale-105"></div>
                <div class="absolute top-3 left-3 right-3 p-3 rounded-lg shadow-lg transition-all duration-300 group-hover:-translate-y-1" style="background-color: rgba(23, 20, 15, 0.92); backdrop-filter: blur(4px); z-index: 10;">
                    <p class="text-white text-xs font-bold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" style="color: #c99a32;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                        Atelier & Fashion Etnik
                    </p>
                    <p class="text-gray-300 text-[10px] mt-1 leading-relaxed">Jl. Terusan Rajabasa No.11, Perum Bumi Permata Cirmai, Kel. Kedung, Kec. Kemiling, Bandar Lampung</p>
                    <p class="text-gray-400 text-[9px] mt-1">Buka: 09.00 - 20.00 WIB</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     7. TESTIMONI
     ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between mb-8">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-widest" style="color: #c99a32;">Kisah Bahagia Bersama ALNAY.ID</p>
                <h2 class="font-display mt-1 text-2xl font-bold" style="color: #15233d;">Testimoni Pengantin & Klien</h2>
            </div>
            <p class="text-xs" style="color: #6b7280;">Kepuasan ★ 4.97 dari 1.800+ Acara</p>
        </div>

        @if (count($testimoni) > 0)
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ($testimoni as $t)
                    @php $inisial = strtoupper(substr($t['nama'], 0, 2)); @endphp
                    <div class="card-shadow rounded-xl border p-5 border-line bg-panel">
                        <p class="text-gold">
                            @for ($i = 1; $i <= 5; $i++) {{ $i <= $t['bintang'] ? '★' : '☆' }} @endfor
                        </p>
                        <p class="mt-3 text-xs leading-relaxed text-ink/80">"{{ $t['teks'] }}"</p>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full text-[11px] font-bold bg-cream text-gold">{{ $inisial }}</div>
                            <div>
                                <p class="text-xs font-bold text-ink">{{ $t['nama'] }}</p>
                                <p class="text-[10px] text-muted">{{ $t['konteks'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- === JAVASCRIPT CAROUSEL & MAP === --}}
    <script>
        // 1. SCRIPT CAROUSEL
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
                    <img src="${photos[prevIndex].src}" alt="${photos[prevIndex].title}" class="rounded-xl w-full h-auto object-cover">
                </div>
                <div class="relative z-10 w-full min-w-0 sm:w-[40%] scale-100 transition-all duration-500 ease-out">
                    <img src="${photos[currentIndex].src}" alt="${photos[currentIndex].title}" class="rounded-xl w-full h-auto object-cover border-4" style="border-color: #c99a32;">
                    <div class="absolute bottom-2 left-2 right-2 bg-black/70 p-2 rounded-lg text-white sm:bottom-4 sm:left-4 sm:right-4 sm:p-3">
                        <p class="text-[9px] sm:text-[10px] font-semibold uppercase tracking-wider" style="color: #e6c877;">${photos[currentIndex].title}</p>
                        <p class="text-[8px] sm:text-[9px]" style="color: #d1d1d1;">${photos[currentIndex].subtitle}</p>
                    </div>
                </div>
                <div class="hidden sm:block w-[25%] min-w-0 opacity-50 scale-90 transition-all duration-500 ease-out">
                    <img src="${photos[nextIndex].src}" alt="${photos[nextIndex].title}" class="rounded-xl w-full h-auto object-cover">
                </div>
            `;
                setTimeout(() => { container.style.opacity = '1'; }, 50);
            }, 200);
        }

        function nextPhoto() {
            if (isAnimating) return;
            isAnimating = true;
            currentIndex = (currentIndex + 1) % photos.length;
            renderCarousel();
            setTimeout(() => { isAnimating = false; }, 500);
        }

        function prevPhoto() {
            if (isAnimating) return;
            isAnimating = true;
            currentIndex = (currentIndex - 1 + photos.length) % photos.length;
            renderCarousel();
            setTimeout(() => { isAnimating = false; }, 500);
        }

        // 2. INITIALIZATION (Peta + Carousel)
        document.addEventListener('DOMContentLoaded', function() {
            renderCarousel();

            const script = document.createElement('script');
            script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
            script.integrity = "sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=";
            script.crossOrigin = "";
            script.onload = function() {
                const alnayLocation = [-5.3971, 105.2668];
                const map = L.map('peta-alnay', { zoomControl: false, scrollWheelZoom: false, dragging: false }).setView(alnayLocation, 17);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(map);

                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: #c99a32; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;"><svg style="transform: rotate(45deg); width: 16px; height: 16px; fill: white;" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg></div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32]
                });

                L.marker(alnayLocation, { icon: customIcon }).addTo(map);
                const googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${alnayLocation[0]},${alnayLocation[1]}`;

                document.getElementById('peta-alnay').addEventListener('click', function() {
                    window.open(googleMapsUrl, '_blank');
                });
            };
            document.head.appendChild(script);
        });
    </script>
@endsection