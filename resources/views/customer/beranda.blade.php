@extends('layouts.public')

@section('title', 'ALNAY.ID — Jas & Gown Berbudaya')

@section('content')

{{-- Leaflet CSS untuk Peta Interaktif --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

{{-- ============================================================
     CUSTOM STYLES (Dari Desain Lama Anda)
     ============================================================ --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');
    
    .font-display {
        font-family: 'Playfair Display', serif;
    }
    
    body {
        font-family: 'Inter', sans-serif;
    }

    /* Responsivitas beranda tanpa mengubah desain dan fungsi yang sudah ada */
    html, body {
        max-width: 100%;
        overflow-x: clip;
    }

    #carousel-container {
        min-width: 0;
        width: 100%;
    }

    #carousel-container > div {
        min-width: 0;
    }

    #carousel-container img {
        display: block;
        max-width: 100%;
    }

    @media (max-width: 639px) {
        #carousel-container {
            padding-left: 2.5rem;
            padding-right: 2.5rem;
        }

        #carousel-container > div:first-child,
        #carousel-container > div:last-child {
            display: none;
        }

        #carousel-container > div:nth-child(2) {
            width: 100%;
        }

        #carousel-container > div:nth-child(2) > div {
            left: 0.5rem;
            right: 0.5rem;
            bottom: 0.5rem;
            padding: 0.5rem;
        }

        #carousel-container > div:nth-child(2) > div p:first-child {
            font-size: 9px;
        }

        #carousel-container > div:nth-child(2) > div p:last-child {
            font-size: 8px;
        }
    }

    @media (max-width: 374px) {
        #carousel-container {
            padding-left: 2rem;
            padding-right: 2rem;
        }
    }

    @media (min-width: 640px) {
        #carousel-container > div:first-child,
        #carousel-container > div:last-child {
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
    
    .card-shadow {
        box-shadow: 0 10px 24px rgba(0, 0, 0, .08);
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
    
    /* Fade & Slide Effect */
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    
    @keyframes slideLeft {
        from { transform: translateX(100%) scale(0.9); opacity: 0; }
        to { transform: translateX(0) scale(1); opacity: 1; }
    }
    
    @keyframes slideRight {
        from { transform: translateX(-100%) scale(0.9); opacity: 0; }
        to { transform: translateX(0) scale(1); opacity: 1; }
    }
    
    .carousel-animate-fade { animation: fadeIn 0.6s ease-out; }
    .carousel-animate-left { animation: slideLeft 0.6s ease-out; }
    .carousel-animate-right { animation: slideRight 0.6s ease-out; }
    
    /* Text Overlay Animation */
    .carousel-text { animation: fadeInUp 0.5s ease-out 0.2s both; }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Button Hover Effect */
    .carousel-btn { transition: all 0.3s ease; }
    .carousel-btn:hover {
        transform: scale(1.1);
        background-color: rgba(255, 255, 255, 0.6) !important;
    }

    /* Animasi Marker Peta */
.custom-marker {
    animation: bounce-marker 2s infinite ease-in-out;
}
@keyframes bounce-marker {
    0%, 100% { transform: translateY(0) rotate(-45deg); }
    50% { transform: translateY(-8px) rotate(-45deg); }
}

/* Efek Hover pada Container Peta */
.group:hover #peta-alnay {
    filter: brightness(1.05);
}

/* === TAMBAHKAN CSS INI DI BAWAH === */
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
#peta-alnay .leaflet-map-pane {
    z-index: 1 !important;
}

#peta-alnay .leaflet-tile-pane { z-index: 1 !important; }
#peta-alnay .leaflet-overlay-pane { z-index: 2 !important; }
#peta-alnay .leaflet-shadow-pane { z-index: 3 !important; }
#peta-alnay .leaflet-marker-pane { z-index: 4 !important; }
#peta-alnay .leaflet-tooltip-pane { z-index: 5 !important; }
#peta-alnay .leaflet-popup-pane { z-index: 6 !important; }
/* === AKHIR CSS === */
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
    <div class="flex flex-wrap justify-center gap-6 sm:gap-10">
        @foreach ($kategori as $k)
            <a href="{{ route('koleksi.kategori', ['kode' => $k['kode_kategori']]) }}"
               class="group flex w-20 flex-col items-center gap-2 text-center">

                <div class="h-16 w-16 overflow-hidden rounded-full border" style="border-color: #e5e7eb; background-color: white;">
                    @if (! empty($k['foto']))
                        <img src="{{ asset($k['foto']) }}" alt="{{ $k['nama_kategori'] }}" loading="lazy" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center font-display text-lg" style="color: #c99a32;">
                            {{ mb_substr($k['nama_kategori'], 0, 1) }}
                        </div>
                    @endif
                </div>

                <div>
                    <p class="text-[11px] font-semibold group-hover:text-[#c99a32] transition-colors" style="color: #15233d;">
                        {{ $k['nama_kategori'] }}
                    </p>
                    @if (! empty($k['subjudul']))
                        <p class="text-[9px]" style="color: #9ca3af;">{{ $k['subjudul'] }}</p>
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
                <h3 class="font-display text-xl md:text-2xl font-bold text-white mt-3">
                    Event hebat ALNAY.ID<br>
                    <span style="color: #c99a32;">Ayo vote</span>
                </h3>
                <p class="text-xs text-gray-400 mt-2">Beri suara & menangkan hadiah menarik!</p>
                <a href="#" class="inline-block mt-4 px-5 py-2.5 rounded-md text-xs font-bold uppercase tracking-wider transition-colors hover:opacity-90 cursor-pointer" style="background-color: #c99a32; color: white;">
    Lihat & Vote
</a>
            </div>
            {{-- Image overlay --}}
            <div class="absolute right-0 top-0 h-full w-1/3 opacity-60">
                <img src="{{ asset('images/home/event-vote.jpg') }}" alt="Event Vote" class="h-full w-full object-cover">
            </div>
        </div>

        {{-- Banner 2: Daftar Event --}}
        <div class="rounded-2xl overflow-hidden relative" style="background-color: #1a1610;">
            <div class="p-6 md:p-8 relative z-10">
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" style="background-color: #c99a32; color: white;">
                    ● Akan Datang
                </span>
                <h3 class="font-display text-xl md:text-2xl font-bold text-white mt-3">
                    Event hebat ALNAY.ID<br>
                    <span style="color: #c99a32;">Ayo daftar</span>
                </h3>
                <p class="text-xs text-gray-400 mt-2">Daftar sekarang & dapatkan diskon spesial!</p>
                <a href="#" class="inline-block mt-4 px-5 py-2.5 rounded-md text-xs font-bold uppercase tracking-wider transition-colors hover:opacity-90 cursor-pointer" style="background-color: #c99a32; color: white;">
    Daftar Sekarang
</a>
            </div>
            {{-- Image overlay --}}
            <div class="absolute right-0 top-0 h-full w-1/3 opacity-60">
                <img src="{{ asset('images/home/event-daftar.jpg') }}" alt="Event Daftar" class="h-full w-full object-cover">
            </div>
        </div>
    </div>
</section>


{{-- ============================================================
     7. OPSI FLEKSIBEL: SEWA ATAU BELI (Section Baru)
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-14">
    <div class="text-center mb-10">
        <p class="text-[10px] font-bold uppercase tracking-[0.2em]" style="color: #c99a32;">Transparansi Mutu</p>
        <h2 class="font-display text-2xl md:text-3xl font-bold mt-2" style="color: #15233d;">Opsi Fleksibel: Sewa atau Beli</h2>
        <p class="text-xs text-gray-600 mt-2 max-w-2xl mx-auto">
            Kejelasan komitmen higienitas, fitting presisi, dan garansi busana tanpa biaya tersembunyi.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
        {{-- Card 1: Layanan Sewa Busana --}}
        <div class="rounded-xl border p-6 md:p-8" style="border-color: #f3f4f6; background-color: white;">
            <span class="inline-block px-3 py-1 rounded-full text-[9px] font-bold uppercase" style="background-color: #fef3c7; color: #92400e;">
                Paling Diminati
            </span>
            <h3 class="font-display text-lg font-bold mt-3" style="color: #15233d;">Layanan Sewa Busana</h3>
            <p class="text-xs text-gray-600 mt-2">Solusi praktis dan hemat untuk akad nikah, resepsi, wisuda, atau pentas budaya.</p>
            
            <ul class="mt-4 space-y-2 text-xs">
                <li class="flex items-start gap-2">
                    <span style="color: #c99a32;">✓</span>
                    <span style="color: #374151;"><strong>Durasi Sewa:</strong> Fleksibel 3 sampai 7 hari kerja.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span style="color: #c99a32;">✓</span>
                    <span style="color: #374151;"><strong>Gratis Alteration:</strong> Penyesuaian ukuran pinggang & kancing aman.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span style="color: #c99a32;">✓</span>
                    <span style="color: #374151;"><strong>Termasuk Laundry:</strong> Busana kembali tidak perlu dicuci sendiri, harum dan wangi sekali!</span>
                </li>
                <li class="flex items-start gap-2">
                    <span style="color: #c99a32;">✓</span>
                    <span style="color: #374151;"><strong>Steril & Higienis:</strong> Ozon steaming dan garment bag anti debu.</span>
                </li>
            </ul>

            <a href="{{ route('koleksi.index') }}" class="block w-full mt-6 py-3 rounded-md text-center text-xs font-bold uppercase tracking-wider transition-colors hover:opacity-90" style="background-color: #c99a32; color: white;">
    Eksplorasi Katalog Sewa
</a>
        </div>

        {{-- Card 2: Beli & Custom Bespoke --}}
        <div class="rounded-xl p-6 md:p-8" style="background-color: #1a1610;">
            <h3 class="font-display text-lg font-bold text-white">Beli & Custom Bespoke</h3>
            <p class="text-xs text-gray-400 mt-2">Mahakarya eksklusif milik selamanya sebagai busana warisan keluarga.</p>
            
            <ul class="mt-4 space-y-2 text-xs">
                <li class="flex items-start gap-2">
                    <span style="color: #c99a32;">✓</span>
                    <span style="color: #e5e7eb;"><strong>Full Tailoring Personal:</strong> Disesuaikan 100% dengan lekuk tubuh.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span style="color: #c99a32;">✓</span>
                    <span style="color: #e5e7eb;"><strong>Material Sutera & Songket Asli:</strong> Pilihan bahan tenun tangan terbaik.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span style="color: #c99a32;">✓</span>
                    <span style="color: #e5e7eb;"><strong>Sulam Emas Tradisional:</strong> Dikerjakan manual oleh perajin adibusana.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span style="color: #c99a32;">✓</span>
                    <span style="color: #e5e7eb;"><strong>Garansi Fitting Ulang:</strong> Revisi hingga nyaman dan pas dipakai.</span>
                </li>
            </ul>

            <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" class="block w-full mt-6 py-3 rounded-md border text-center text-xs font-bold uppercase tracking-wider transition-colors hover:bg-white/10" style="border-color: #374151; color: white;">
    Konsultasi Bespoke
</a>
        </div>
    </div>
</section>


{{-- ============================================================
     8. OFFLINE ATELIER & STUDIO (Section Baru)
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-14">
    <div class="rounded-xl border p-6 md:p-10" style="border-color: #f3f4f6; background-color: white;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            {{-- Left: Text Content --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em]" style="color: #c99a32;">Galeri & Ruang Fitting</p>
                <h2 class="font-display text-2xl font-bold mt-2" style="color: #15233d;">Offline Atelier & Studio ALNAY.ID</h2>
                <p class="text-sm text-gray-600 mt-3">
                    Kunjungi studio kami untuk konsultasi langsung, fitting presisi, dan melihat koleksi secara nyata. 
                    Dilengkapi ruang fitting privat ber-AC dan penjahit profesional siap membantu.
                </p>
                
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" class="inline-block px-5 py-3 rounded-md border text-xs font-bold uppercase tracking-wider transition-colors hover:bg-gray-50" style="border-color: #15233d; color: #15233d;">
    Info Lokasi & Booking Jadwal
</a>
                   <a href="#" class="inline-block px-5 py-3 rounded-md border text-xs font-bold uppercase tracking-wider transition-colors hover:bg-gray-50 cursor-pointer" style="border-color: #d1d5db; color: #374151;">
    Panduan Sewa Online
</a>
                </div>
            </div>

                        {{-- Right: Map Container --}}
<div class="relative rounded-lg overflow-hidden group cursor-pointer border" style="border-color: #f3f4f6;">
    {{-- Wadah Peta - Ukuran lebih compact --}}
    <div id="peta-alnay" class="w-full h-[200px] md:h-[250px] rounded-lg transition-transform duration-500 group-hover:scale-105"></div>
    
    {{-- Overlay Info Card --}}
    <div class="absolute top-3 left-3 right-3 p-3 rounded-lg shadow-lg transition-all duration-300 group-hover:-translate-y-1" style="background-color: rgba(23, 20, 15, 0.92); backdrop-filter: blur(4px);">
        <p class="text-white text-xs font-bold flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" style="color: #c99a32;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
            Atelier & Fashion Etnik
        </p>
        <p class="text-gray-300 text-[10px] mt-1 leading-relaxed">Jl. Terusan Rajabasa No.11, Perum Bumi Permata Cirmai, Kel. Kedung, Kec. Kemiling, Bandar Lampung</p>
        <p class="text-gray-400 text-[9px] mt-1">Buka: 09.00 - 20.00 WIB</p>
    </div>
</div>
        </div>
    </div>
</section>


{{-- ============================================================
     5. TESTIMONI (Styling Sesuai Desain Lama)
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-widest" style="color: #c99a32;">Kisah Bahagia Bersama ALNAY.ID</p>
            <h2 class="font-display mt-1 text-2xl font-bold" style="color: #15233d;">Testimoni Pengantin &amp; Klien</h2>
        </div>

        <p class="text-xs" style="color: #6b7280;">
            Kepuasan ★ 4.97 dari 1.800+ Acara
        </p>
    </div>

    @if (count($testimoni) > 0)
        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-3">
            @foreach ($testimoni as $t)
                @php
                    $inisial = strtoupper(substr($t['nama'], 0, 2));
                @endphp

                <div class="card-shadow rounded-xl border p-5" style="border-color: #f3f4f6; background-color: white;">
                    <p style="color: #c99a32;">
                        @for($i = 1; $i <= 5; $i++)
                            {{ $i <= $t['bintang'] ? '★' : '☆' }}
                        @endfor
                    </p>

                    <p class="mt-3 text-xs leading-relaxed" style="color: #4b5563;">"{{ $t['teks'] }}"</p>

                    <div class="mt-4 flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full text-[11px] font-bold"
                             style="background-color: #fbf6e9; color: #c99a32;">
                            {{ $inisial }}
                        </div>
                        <div>
                            <p class="text-xs font-bold" style="color: #15233d;">{{ $t['nama'] }}</p>
                            <p class="text-[10px]" style="color: #9ca3af;">{{ $t['konteks'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- === JAVASCRIPT CAROUSEL === --}}
<script>
    // =====================================================
    // 1. SCRIPT CAROUSEL (Sudah ada sebelumnya)
    // =====================================================
    let currentIndex = 0;
    let isAnimating = false;
    
    // PERBAIKAN: Menggunakan tanda kutip ganda (") untuk string JavaScript
    // agar tidak bentrok dengan tanda kutip tunggal (') di dalam fungsi asset()
    const photos = [
        { src: "{{ asset('images/home/hero-pasangan.jpg') }}", title: 'Busana Adat Berpasangan', subtitle: 'Pesona budaya dalam balutan busana istimewa' },
        { src: "{{ asset('images/home/hero-gown.jpg') }}", title: 'Gown Pengantin', subtitle: 'Elegan dan anggun untuk momen spesial' },
        { src: "{{ asset('images/home/hero-jas.jpg') }}", title: 'Jas Beskap Modern', subtitle: 'Sentuhan modern pada busana adat' }
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
    function nextPhoto() { if (isAnimating) return; isAnimating = true; currentIndex = (currentIndex + 1) % photos.length; renderCarousel(); setTimeout(() => { isAnimating = false; }, 500); }
    function prevPhoto() { if (isAnimating) return; isAnimating = true; currentIndex = (currentIndex - 1 + photos.length) % photos.length; renderCarousel(); setTimeout(() => { isAnimating = false; }, 500); }


        // =====================================================
    // 2. INITIALIZATION (Peta + Carousel)
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
            // Koordinat ALNAY.ID (Bandar Lampung)
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

            // Custom Marker Icon
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: #c99a32; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                        <svg style="transform: rotate(45deg); width: 16px; height: 16px; fill: white;" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                       </div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });

            const marker = L.marker(alnayLocation, { icon: customIcon }).addTo(map);

            // Link Google Maps
            const googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${alnayLocation[0]},${alnayLocation[1]}`;
            
            // Klik peta untuk buka Google Maps
            document.getElementById('peta-alnay').addEventListener('click', function() {
                window.open(googleMapsUrl, '_blank');
            });
        };
        document.head.appendChild(script);
    });
</script>
@endsection