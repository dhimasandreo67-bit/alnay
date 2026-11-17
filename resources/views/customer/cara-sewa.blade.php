@extends('layouts.public')

@section('title', 'Cara Sewa & Beli - ALNAY.ID')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

{{-- Semua aturan CSS di bawah dibatasi ke .cara-sewa supaya tidak mengubah header/footer --}}
<style>
    .cara-sewa .font-display { font-family: 'Playfair Display', serif; }
    .cara-sewa { font-family: 'Inter', sans-serif; overflow-x: clip; }

    /* === PAKSA LAYOUT AGAR TIDAK MEPET === */
    .cara-sewa, .cara-sewa * { box-sizing: border-box !important; }

    .cara-sewa .max-w-6xl, .cara-sewa .max-w-4xl {
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 2rem !important;
        padding-right: 2rem !important;
        max-width: 1200px !important;
    }
    
    @media (max-width: 768px) {
        .cara-sewa .max-w-6xl, .cara-sewa .max-w-4xl {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }

    /* === TIMELINE HORIZONTAL 5 KOLOM === */
    .cara-sewa .timeline-horizontal {
        display: grid !important;
        grid-template-columns: repeat(5, 1fr) !important;
        gap: 1rem !important;
        position: relative !important;
    }

    @media (max-width: 767px) {
        .cara-sewa .timeline-horizontal {
            grid-template-columns: 1fr !important;
            gap: 2rem !important;
        }
    }

    .cara-sewa .timeline-horizontal::before {
        content: '' !important;
        position: absolute !important;
        top: 24px !important;
        left: 10% !important;
        right: 10% !important;
        height: 2px !important;
        background: linear-gradient(90deg, transparent, #c99a32, #e6c877, #c99a32, transparent) !important;
        z-index: 0 !important;
    }

    @media (max-width: 767px) {
        .cara-sewa .timeline-horizontal::before { display: none !important; }
    }

    /* === GRID 2 KOLOM (Metode Sewa & Beli) === */
    .cara-sewa .method-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 2rem !important;
    }

    @media (max-width: 767px) {
        .cara-sewa .method-grid {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
        }
    }

    /* === GRID 3 KOLOM (Kebijakan) === */
    .cara-sewa .policy-grid {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 1.5rem !important;
    }

    @media (max-width: 767px) {
        .cara-sewa .policy-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Section padding */
    .cara-sewa section {
        padding-top: 4rem !important;
        padding-bottom: 4rem !important;
    }

    /* Card styling */
    .cara-sewa .method-card {
        padding: 2rem !important;
        background: white !important;
        border-radius: 1rem !important;
        border: 1px solid #e5e7eb !important;
        transition: all 0.4s ease !important;
        position: relative;
        overflow: hidden;
    }

    .cara-sewa .method-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 20px 40px -10px rgba(201, 154, 50, 0.2) !important;
        border-color: #c99a32 !important;
    }

    /* Timeline step */
    .cara-sewa .timeline-step {
        text-align: center !important;
        position: relative !important;
        z-index: 1 !important;
        transition: all 0.3s ease;
    }
    
    .cara-sewa .timeline-step:hover {
        transform: translateY(-5px);
    }

    .cara-sewa .timeline-step .step-number {
        width: 3rem !important;
        height: 3rem !important;
        border-radius: 9999px !important;
        background-color: #c99a32 !important;
        color: white !important;
        font-weight: bold !important;
        font-size: 1.125rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto 1rem auto !important;
        box-shadow: 0 4px 15px rgba(201, 154, 50, 0.4) !important;
        transition: all 0.3s ease;
    }
    
    .cara-sewa .timeline-step:hover .step-number {
        transform: scale(1.15);
        box-shadow: 0 0 20px rgba(201, 154, 50, 0.6);
    }

    /* Fade-up animation */
    .cara-sewa .fade-up {
        opacity: 0;
        transform: translateY(40px);
        transition: all 1s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    .cara-sewa .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .cara-sewa .gold-divider {
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #c99a32, #e6c877);
        margin: 0 auto;
    }

    /* Efek Tombol Premium */
    .cara-sewa .btn-premium {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .cara-sewa .btn-premium:hover {
        box-shadow: 0 10px 25px rgba(201, 154, 50, 0.4);
    }
    .cara-sewa .btn-premium::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 50%; height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-25deg);
        transition: all 0.7s ease;
    }
    .cara-sewa .btn-premium:hover::before {
        left: 150%;
    }
</style>

<div class="cara-sewa">

{{-- ============================================================
     1. HERO SECTION
     ============================================================ --}}
<section class="relative" style="background-color: #faf7f0;">
    <div class="max-w-4xl mx-auto text-center fade-up">
        <span class="inline-block px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-[0.3em] mb-6" 
              style="background-color: rgba(201, 154, 50, 0.15); color: #c99a32; border: 1px solid rgba(201, 154, 50, 0.3);">
            Panduan Lengkap
        </span>
        <h1 class="font-display text-4xl md:text-5xl font-bold leading-tight mb-6" style="color: #15233d;">
            Cara Sewa & Beli di <span style="color: #c99a32;">ALNAY.ID</span>
        </h1>
        <div class="gold-divider mb-6"></div>
        <p class="text-base md:text-lg max-w-2xl mx-auto leading-relaxed" style="color: #4b5563;">
            Kami merancang proses sewa dan pembelian yang mudah, transparan, dan nyaman.
            Ikuti panduan sewa dan beli di bawah ini untuk tampil memukau di momen istimewa Anda.
        </p>
    </div>
</section>


{{-- ============================================================
     2. LANGKAH-LANGKAH SEWA (Timeline Horizontal)
     ============================================================ --}}
<section style="background-color: white;">
    <div class="max-w-6xl mx-auto">
        <div class="timeline-horizontal">
            
            <div class="fade-up timeline-step">
                <div class="step-number">1</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pilih Produk</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Jelajahi katalog kami, pilih busana atau aksesoris yang Anda inginkan, dan tambahkan ke keranjang.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 100ms;">
                <div class="step-number">2</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Konsultasi & Fitting</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Hubungi CS kami untuk konfirmasi ukuran. Fitting langsung di toko atau via video call.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 200ms;">
                <div class="step-number">3</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pembayaran & Deposit</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Lakukan pembayaran sewa dan deposit jaminan (dikembalikan 100% setelah barang kembali aman).</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 300ms;">
                <div class="step-number">4</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pengiriman / Ambil</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Barang dikirim via kurir khusus (H-3 acara) atau diambil langsung di toko kami.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 400ms;">
                <div class="step-number">5</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pengembalian Mudah</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Kembalikan setelah acara selesai. Tidak perlu dicuci! Kami handle laundry & sterilisasi.</p>
            </div>

        </div>
    </div>
</section>


{{-- ============================================================
     3. METODE SEWA: Ambil di Toko vs Kirim
     ============================================================ --}}
<section style="background-color: #faf7f0;">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 fade-up">
            <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Pilih Metode yang Sesuai</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-3" style="color: #15233d;">Metode Pengambilan Sewa</h2>
            <div class="gold-divider mt-6"></div>
            <p class="text-sm mt-4 max-w-2xl mx-auto" style="color: #6b7280;">
                Kami menyediakan 2 metode pengambilan untuk kenyamanan Anda
            </p>
        </div>

        <div class="method-grid">
            {{-- Card 1: Ambil di Toko --}}
            <div class="fade-up method-card group">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 transition-transform duration-500 group-hover:rotate-6" style="background-color: #fbf6e9;">
                        <svg class="w-8 h-8" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color: #15233d;">Ambil di Toko</h3>
                        <p class="text-xs" style="color: #6b7280;">Booking jadwal fitting langsung</p>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Booking Jadwal Fitting</p>
                            <p class="text-xs" style="color: #6b7280;">Pilih tanggal & waktu lewat reservasi online</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Fitting Langsung di Toko</p>
                            <p class="text-xs" style="color: #6b7280;">Coba langsung dengan penjahit profesional</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Gratis Alterasi On-Spot</p>
                            <p class="text-xs" style="color: #6b7280;">Penyesuaian ukuran langsung di tempat</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Lokasi ALNAY:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Jl. Terusan Ragom Gawi III, Perumahan Taman Cinta A7, Kelurahan Kedaung, Kecamatan Kemiling, Kota Bandar Lampung, Lampung 35158</p>
                    <p class="text-xs mt-2 font-bold flex items-center gap-1.5" style="color: #c99a32;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Jam operasional fleksibel. Konfirmasi via WA.
                    </p>
                </div>

                <a href="{{ route('public.fitting.create') }}"
                   class="btn-premium block w-full py-3 rounded-lg text-center text-sm font-bold uppercase tracking-wider text-white transition-all" 
                   style="background-color: #c99a32;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Booking Jadwal Fitting
                    </div>
                </a>
            </div>

            {{-- Card 2: Kirim ke Alamat --}}
            <div class="fade-up method-card group" style="transition-delay: 150ms;">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 transition-transform duration-500 group-hover:-rotate-6" style="background-color: #fbf6e9;">
                        <svg class="w-8 h-8" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color: #15233d;">Kirim ke Alamat</h3>
                        <p class="text-xs" style="color: #6b7280;">Pengiriman aman ke seluruh Indonesia</p>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Kurir Khusus & Asuransi</p>
                            <p class="text-xs" style="color: #6b7280;">JNE YES, GoSend, GrabExpress, Paxel</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Pengiriman H-3 Acara</p>
                            <p class="text-xs" style="color: #6b7280;">Dikirim 3 hari sebelum acara</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Panduan Ukuran via Video Call</p>
                            <p class="text-xs" style="color: #6b7280;">Konsultasi sebelum pengiriman</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Ongkos Kirim:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Dihitung otomatis saat checkout sesuai alamat dan kurir yang Anda pilih</p>
                    <p class="text-xs mt-2 font-bold flex items-center gap-1.5" style="color: #c99a32;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Express tersedia untuk mendesak
                    </p>
                </div>

                <a href="{{ route('koleksi.index') }}" 
                   class="btn-premium block w-full py-3 rounded-lg text-center text-sm font-bold uppercase tracking-wider text-white transition-all" 
                   style="background-color: #c99a32;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Mulai Sewa Sekarang
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>


{{-- ============================================================
     4. CARA BELI
     ============================================================ --}}
<section style="background-color: white;">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 fade-up">
            <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Miliki Selamanya</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-3" style="color: #15233d;">Cara Membeli di ALNAY.ID</h2>
            <div class="gold-divider mt-6"></div>
            <p class="text-sm mt-4 max-w-2xl mx-auto" style="color: #6b7280;">
                Ingin memiliki busana adat sebagai koleksi pribadi? Kami melayani penjualan dengan 2 metode
            </p>
        </div>

        <div class="method-grid">
            {{-- Beli & Ambil di Toko --}}
            <div class="fade-up method-card group">
                <div class="flex items-center gap-4 mb-6">
<div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 transition-transform duration-500 group-hover:rotate-6" style="background-color: #fbf6e9;">
                        <svg class="w-8 h-8" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color: #15233d;">Beli & Ambil di Toko</h3>
                        <p class="text-xs" style="color: #6b7280;">Tanpa ongkir, ambil langsung</p>
                    </div>
                </div>
 
                <div class="space-y-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Pilih Produk Ready Stock</p>
                            <p class="text-xs" style="color: #6b7280;">Pilih ukuran dan warna yang tersedia di katalog</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Checkout & Bayar via QRIS</p>
                            <p class="text-xs" style="color: #6b7280;">Pilih opsi ambil di toko saat checkout</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Ambil di Toko</p>
                            <p class="text-xs" style="color: #6b7280;">Ambil sesuai jam buka setelah pembayaran terkonfirmasi</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Refund 7 Hari</p>
                            <p class="text-xs" style="color: #6b7280;">Ajukan dalam 7 hari setelah barang diterima (syarat berlaku)</p>
                        </div>
                    </div>
                </div>
 
                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Lokasi ALNAY:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Jl. Terusan Ragom Gawi III, Perumahan Taman Cinta A7, Kelurahan Kedaung, Kecamatan Kemiling, Kota Bandar Lampung, Lampung 35158</p>
                    <p class="text-xs mt-2 font-bold flex items-center gap-1.5" style="color: #c99a32;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Jam operasional fleksibel. Konfirmasi via WA.
                    </p>
                </div>
 
                <a href="{{ route('koleksi.index') }}"
                   class="btn-premium block w-full py-3 rounded-lg text-center text-sm font-bold uppercase tracking-wider text-white transition-all"
                   style="background-color: #c99a32;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Belanja Sekarang
                    </div>
                </a>
            </div>

            {{-- Beli & Kirim ke Rumah --}}
            <div class="fade-up method-card group" style="transition-delay: 150ms;">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 transition-transform duration-500 group-hover:-rotate-6" style="background-color: #fbf6e9;">
                        <svg class="w-8 h-8" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color: #15233d;">Beli & Kirim ke Rumah</h3>
                        <p class="text-xs" style="color: #6b7280;">Untuk produk ready stock</p>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Pilih dari Katalog Ready</p>
                            <p class="text-xs" style="color: #6b7280;">Ukuran dan warna sesuai ketersediaan di katalog</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Checkout & Pembayaran</p>
                            <p class="text-xs" style="color: #6b7280;">QRIS, bisa dari semua e-wallet dan m-banking</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Dikirim via Kurir Pilihan Anda</p>
                            <p class="text-xs" style="color: #6b7280;">Estimasi tiba mengikuti kurir yang dipilih saat checkout</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Refund 7 Hari</p>
                            <p class="text-xs" style="color: #6b7280;">Ajukan dalam 7 hari setelah barang diterima (syarat berlaku)</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Pembayaran:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Semua transaksi dibayar lewat QRIS, status terkonfirmasi otomatis</p>
                </div>

                <a href="{{ route('koleksi.index') }}" 
                   class="btn-premium block w-full py-3 rounded-lg text-center text-sm font-bold uppercase tracking-wider text-white transition-all" 
                   style="background-color: #c99a32;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Belanja Sekarang
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     @extends('layouts.public')

@section('title', 'Cara Sewa & Beli - ALNAY.ID')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

{{-- Semua aturan CSS di bawah dibatasi ke .cara-sewa supaya tidak mengubah header/footer --}}
<style>
    .cara-sewa .font-display { font-family: 'Playfair Display', serif; }
    .cara-sewa { font-family: 'Inter', sans-serif; overflow-x: clip; }

    /* === PAKSA LAYOUT AGAR TIDAK MEPET === */
    .cara-sewa, .cara-sewa * { box-sizing: border-box !important; }

    .cara-sewa .max-w-6xl, .cara-sewa .max-w-4xl {
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 2rem !important;
        padding-right: 2rem !important;
        max-width: 1200px !important;
    }
    
    @media (max-width: 768px) {
        .cara-sewa .max-w-6xl, .cara-sewa .max-w-4xl {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }

    /* === TIMELINE HORIZONTAL 5 KOLOM === */
    .cara-sewa .timeline-horizontal {
        display: grid !important;
        grid-template-columns: repeat(5, 1fr) !important;
        gap: 1rem !important;
        position: relative !important;
    }

    @media (max-width: 767px) {
        .cara-sewa .timeline-horizontal {
            grid-template-columns: 1fr !important;
            gap: 2rem !important;
        }
    }

    .cara-sewa .timeline-horizontal::before {
        content: '' !important;
        position: absolute !important;
        top: 24px !important;
        left: 10% !important;
        right: 10% !important;
        height: 2px !important;
        background: linear-gradient(90deg, transparent, #c99a32, #e6c877, #c99a32, transparent) !important;
        z-index: 0 !important;
    }

    @media (max-width: 767px) {
        .cara-sewa .timeline-horizontal::before { display: none !important; }
    }

    /* === GRID 2 KOLOM (Metode Sewa & Beli) === */
    .cara-sewa .method-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 2rem !important;
    }

    @media (max-width: 767px) {
        .cara-sewa .method-grid {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
        }
    }

    /* === GRID 3 KOLOM (Kebijakan) === */
    .cara-sewa .policy-grid {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 1.5rem !important;
    }

    @media (max-width: 767px) {
        .cara-sewa .policy-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Section padding */
    .cara-sewa section {
        padding-top: 4rem !important;
        padding-bottom: 4rem !important;
    }

    /* Card styling */
    .cara-sewa .method-card {
        padding: 2rem !important;
        background: white !important;
        border-radius: 1rem !important;
        border: 1px solid #e5e7eb !important;
        transition: all 0.4s ease !important;
        position: relative;
        overflow: hidden;
    }

    .cara-sewa .method-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 20px 40px -10px rgba(201, 154, 50, 0.2) !important;
        border-color: #c99a32 !important;
    }

    /* Timeline step */
    .cara-sewa .timeline-step {
        text-align: center !important;
        position: relative !important;
        z-index: 1 !important;
        transition: all 0.3s ease;
    }
    
    .cara-sewa .timeline-step:hover {
        transform: translateY(-5px);
    }

    .cara-sewa .timeline-step .step-number {
        width: 3rem !important;
        height: 3rem !important;
        border-radius: 9999px !important;
        background-color: #c99a32 !important;
        color: white !important;
        font-weight: bold !important;
        font-size: 1.125rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto 1rem auto !important;
        box-shadow: 0 4px 15px rgba(201, 154, 50, 0.4) !important;
        transition: all 0.3s ease;
    }
    
    .cara-sewa .timeline-step:hover .step-number {
        transform: scale(1.15);
        box-shadow: 0 0 20px rgba(201, 154, 50, 0.6);
    }

    /* Fade-up animation */
    .cara-sewa .fade-up {
        opacity: 0;
        transform: translateY(40px);
        transition: all 1s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    .cara-sewa .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .cara-sewa .gold-divider {
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #c99a32, #e6c877);
        margin: 0 auto;
    }

    /* Efek Tombol Premium */
    .cara-sewa .btn-premium {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .cara-sewa .btn-premium:hover {
        box-shadow: 0 10px 25px rgba(201, 154, 50, 0.4);
    }
    .cara-sewa .btn-premium::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 50%; height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-25deg);
        transition: all 0.7s ease;
    }
    .cara-sewa .btn-premium:hover::before {
        left: 150%;
    }
</style>

<div class="cara-sewa">

{{-- ============================================================
     1. HERO SECTION
     ============================================================ --}}
<section class="relative" style="background-color: #faf7f0;">
    <div class="max-w-4xl mx-auto text-center fade-up">
        <span class="inline-block px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-[0.3em] mb-6" 
              style="background-color: rgba(201, 154, 50, 0.15); color: #c99a32; border: 1px solid rgba(201, 154, 50, 0.3);">
            Panduan Lengkap
        </span>
        <h1 class="font-display text-4xl md:text-5xl font-bold leading-tight mb-6" style="color: #15233d;">
            Cara Sewa & Beli di <span style="color: #c99a32;">ALNAY.ID</span>
        </h1>
        <div class="gold-divider mb-6"></div>
        <p class="text-base md:text-lg max-w-2xl mx-auto leading-relaxed" style="color: #4b5563;">
            Kami merancang proses sewa dan pembelian yang mudah, transparan, dan nyaman.
            Ikuti panduan sewa dan beli di bawah ini untuk tampil memukau di momen istimewa Anda.
        </p>
    </div>
</section>


{{-- ============================================================
     2. LANGKAH-LANGKAH SEWA (Timeline Horizontal)
     ============================================================ --}}
<section style="background-color: white;">
    <div class="max-w-6xl mx-auto">
        <div class="timeline-horizontal">
            
            <div class="fade-up timeline-step">
                <div class="step-number">1</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pilih Produk</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Jelajahi katalog kami, pilih busana atau aksesoris yang Anda inginkan, dan tambahkan ke keranjang.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 100ms;">
                <div class="step-number">2</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Konsultasi & Fitting</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Hubungi CS kami untuk konfirmasi ukuran. Fitting langsung di toko atau via video call.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 200ms;">
                <div class="step-number">3</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pembayaran & Deposit</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Lakukan pembayaran sewa dan deposit jaminan (dikembalikan 100% setelah barang kembali aman).</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 300ms;">
                <div class="step-number">4</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pengiriman / Ambil</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Barang dikirim via kurir khusus (H-3 acara) atau diambil langsung di toko kami.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 400ms;">
                <div class="step-number">5</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pengembalian Mudah</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Kembalikan setelah acara selesai. Tidak perlu dicuci! Kami handle laundry & sterilisasi.</p>
            </div>

        </div>
    </div>
</section>


{{-- ============================================================
     3. METODE SEWA: Ambil di Toko vs Kirim
     ============================================================ --}}
<section style="background-color: #faf7f0;">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 fade-up">
            <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Pilih Metode yang Sesuai</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-3" style="color: #15233d;">Metode Pengambilan Sewa</h2>
            <div class="gold-divider mt-6"></div>
            <p class="text-sm mt-4 max-w-2xl mx-auto" style="color: #6b7280;">
                Kami menyediakan 2 metode pengambilan untuk kenyamanan Anda
            </p>
        </div>

        <div class="method-grid">
            {{-- Card 1: Ambil di Toko --}}
            <div class="fade-up method-card group">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 transition-transform duration-500 group-hover:rotate-6" style="background-color: #fbf6e9;">
                        <svg class="w-8 h-8" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color: #15233d;">Ambil di Toko</h3>
                        <p class="text-xs" style="color: #6b7280;">Booking jadwal fitting langsung</p>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Booking Jadwal Fitting</p>
                            <p class="text-xs" style="color: #6b7280;">Pilih tanggal & waktu lewat reservasi online</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Fitting Langsung di Toko</p>
                            <p class="text-xs" style="color: #6b7280;">Coba langsung dengan penjahit profesional</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Gratis Alterasi On-Spot</p>
                            <p class="text-xs" style="color: #6b7280;">Penyesuaian ukuran langsung di tempat</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Lokasi ALNAY:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Jl. Terusan Ragom Gawi III, Perumahan Taman Cinta A7, Kelurahan Kedaung, Kecamatan Kemiling, Kota Bandar Lampung, Lampung 35158</p>
                    <p class="text-xs mt-2 font-bold flex items-center gap-1.5" style="color: #c99a32;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Jam operasional fleksibel. Konfirmasi via WA.
                    </p>
                </div>

                <a href="{{ route('public.fitting.create') }}"
                   class="btn-premium block w-full py-3 rounded-lg text-center text-sm font-bold uppercase tracking-wider text-white transition-all" 
                   style="background-color: #c99a32;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Booking Jadwal Fitting
                    </div>
                </a>
            </div>

            {{-- Card 2: Kirim ke Alamat --}}
            <div class="fade-up method-card group" style="transition-delay: 150ms;">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 transition-transform duration-500 group-hover:-rotate-6" style="background-color: #fbf6e9;">
                        <svg class="w-8 h-8" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color: #15233d;">Kirim ke Alamat</h3>
                        <p class="text-xs" style="color: #6b7280;">Pengiriman aman ke seluruh Indonesia</p>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Kurir Khusus & Asuransi</p>
                            <p class="text-xs" style="color: #6b7280;">JNE YES, GoSend, GrabExpress, Paxel</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Pengiriman H-3 Acara</p>
                            <p class="text-xs" style="color: #6b7280;">Dikirim 3 hari sebelum acara</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Panduan Ukuran via Video Call</p>
                            <p class="text-xs" style="color: #6b7280;">Konsultasi sebelum pengiriman</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Ongkos Kirim:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Dihitung otomatis saat checkout sesuai alamat dan kurir yang Anda pilih</p>
                    <p class="text-xs mt-2 font-bold flex items-center gap-1.5" style="color: #c99a32;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Express tersedia untuk mendesak
                    </p>
                </div>

                <a href="{{ route('koleksi.index') }}" 
                   class="btn-premium block w-full py-3 rounded-lg text-center text-sm font-bold uppercase tracking-wider text-white transition-all" 
                   style="background-color: #c99a32;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Mulai Sewa Sekarang
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>


{{-- ============================================================
     4. CARA BELI
     ============================================================ --}}
<section style="background-color: white;">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 fade-up">
            <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Miliki Selamanya</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-3" style="color: #15233d;">Cara Membeli di ALNAY.ID</h2>
            <div class="gold-divider mt-6"></div>
            <p class="text-sm mt-4 max-w-2xl mx-auto" style="color: #6b7280;">
                Ingin memiliki busana adat sebagai koleksi pribadi? Kami melayani penjualan dengan 2 metode
            </p>
        </div>

        <div class="method-grid">
            {{-- Beli & Ambil di Toko --}}
            <div class="fade-up method-card group">
                <div class="flex items-center gap-4 mb-6">
<div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 transition-transform duration-500 group-hover:rotate-6" style="background-color: #fbf6e9;">
                        <svg class="w-8 h-8" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color: #15233d;">Beli & Ambil di Toko</h3>
                        <p class="text-xs" style="color: #6b7280;">Tanpa ongkir, ambil langsung</p>
                    </div>
                </div>
 
                <div class="space-y-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Pilih Produk Ready Stock</p>
                            <p class="text-xs" style="color: #6b7280;">Pilih ukuran dan warna yang tersedia di katalog</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Checkout & Bayar via QRIS</p>
                            <p class="text-xs" style="color: #6b7280;">Pilih opsi ambil di toko saat checkout</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Ambil di Toko</p>
                            <p class="text-xs" style="color: #6b7280;">Ambil di toko setelah pembayaran terkonfirmasi</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Refund 7 Hari</p>
                            <p class="text-xs" style="color: #6b7280;">Ajukan dalam 7 hari setelah barang diterima (syarat berlaku)</p>
                        </div>
                    </div>
                </div>
 
                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Lokasi ALNAY:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Jl. Terusan Ragom Gawi III, Perumahan Taman Cinta A7, Kelurahan Kedaung, Kecamatan Kemiling, Kota Bandar Lampung, Lampung 35158</p>
                    <p class="text-xs mt-2 font-bold flex items-center gap-1.5" style="color: #c99a32;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Jam operasional fleksibel. Konfirmasi via WA.
                    </p>
                </div>
 
                <a href="{{ route('koleksi.index') }}"
                   class="btn-premium block w-full py-3 rounded-lg text-center text-sm font-bold uppercase tracking-wider text-white transition-all"
                   style="background-color: #c99a32;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Belanja Sekarang
                    </div>
                </a>
            </div>

            {{-- Beli & Kirim ke Rumah --}}
            <div class="fade-up method-card group" style="transition-delay: 150ms;">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 transition-transform duration-500 group-hover:-rotate-6" style="background-color: #fbf6e9;">
                        <svg class="w-8 h-8" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color: #15233d;">Beli & Kirim ke Rumah</h3>
                        <p class="text-xs" style="color: #6b7280;">Untuk produk ready stock</p>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Pilih dari Katalog Ready</p>
                            <p class="text-xs" style="color: #6b7280;">Ukuran dan warna sesuai ketersediaan di katalog</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Checkout & Pembayaran</p>
                            <p class="text-xs" style="color: #6b7280;">QRIS, bisa dari semua e-wallet dan m-banking</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Dikirim via Kurir Pilihan Anda</p>
                            <p class="text-xs" style="color: #6b7280;">Estimasi tiba mengikuti kurir yang dipilih saat checkout</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Refund 7 Hari</p>
                            <p class="text-xs" style="color: #6b7280;">Ajukan dalam 7 hari setelah barang diterima (syarat berlaku)</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Pembayaran:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Semua transaksi dibayar lewat QRIS, status terkonfirmasi otomatis</p>
                </div>

                <a href="{{ route('koleksi.index') }}" 
                   class="btn-premium block w-full py-3 rounded-lg text-center text-sm font-bold uppercase tracking-wider text-white transition-all" 
                   style="background-color: #c99a32;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Belanja Sekarang
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     4b. ALUR PEMBELIAN
     ============================================================ --}}
<section style="background-color: #faf7f0;">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 fade-up">
            <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Langkah Demi Langkah</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-3" style="color: #15233d;">Alur Pembelian</h2>
            <div class="gold-divider mt-6"></div>
        </div>

        <div class="timeline-horizontal">
            <div class="fade-up timeline-step">
                <div class="step-number">1</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pilih Produk</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Pilih busana ready stock, tentukan ukuran dan warna, lalu tambahkan ke keranjang.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 100ms;">
                <div class="step-number">2</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Checkout</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Pilih kirim ke alamat atau ambil di toko, lalu periksa total pembayaran.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 200ms;">
                <div class="step-number">3</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Bayar via QRIS</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Scan QRIS dari e-wallet atau m-banking. Status pembayaran terkonfirmasi otomatis.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 300ms;">
                <div class="step-number">4</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Terima Barang</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Barang dikirim lewat kurir pilihan Anda, atau diambil langsung di toko.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 400ms;">
                <div class="step-number">5</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Refund bila Perlu</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Ada masalah dengan barang? Ajukan refund dalam 7 hari setelah barang diterima.</p>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     5. KEBIJAKAN PENTING
     ============================================================ --}}
<section style="background-color: white;">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 fade-up">
            <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Transparansi Tanpa Biaya Tersembunyi</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-3" style="color: #15233d;">Kebijakan Sewa Kami</h2>
            <div class="gold-divider mt-6"></div>
        </div>

        <div class="policy-grid">
            <div class="fade-up bg-white p-8 rounded-2xl border hover:border-[#c99a32] transition-colors duration-300" style="border-color: #e5e7eb;">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-5" style="background-color: #fbf6e9;">
                    <svg class="w-6 h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold mb-3" style="color: #15233d;">Deposit 100% Kembali</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Deposit jaminan dikembalikan penuh setelah busana diterima kembali dalam kondisi wajar.</p>
            </div>

            <div class="fade-up bg-white p-8 rounded-2xl border hover:border-[#c99a32] transition-colors duration-300" style="border-color: #e5e7eb;">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-5" style="background-color: #fbf6e9;">
                    <svg class="w-6 h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold mb-3" style="color: #15233d;">Denda Keterlambatan</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Keterlambatan pengembalian dikenakan denda proporsional per hari sesuai detail produk.</p>
            </div>

            <div class="fade-up bg-white p-8 rounded-2xl border hover:border-[#c99a32] transition-colors duration-300" style="border-color: #e5e7eb;">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-5" style="background-color: #fbf6e9;">
                    <svg class="w-6 h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold mb-3" style="color: #15233d;">Gratis Laundry & Steril</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Tidak perlu cuci! Kami handle dry clean, ozon sterilization, dan packaging ulang.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     5b. KEBIJAKAN PEMBELIAN & REFUND
     ============================================================ --}}
<section style="background-color: #faf7f0;">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 fade-up">
            <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Beli dengan Tenang</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-3" style="color: #15233d;">Kebijakan Pembelian & Refund</h2>
            <div class="gold-divider mt-6"></div>
        </div>

        <div class="policy-grid">
            <div class="fade-up bg-white p-8 rounded-2xl border hover:border-[#c99a32] transition-colors duration-300" style="border-color: #e5e7eb;">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-5" style="background-color: #fbf6e9;">
                    <svg class="w-6 h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold mb-3" style="color: #15233d;">Refund 7 Hari</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Ajukan refund paling lama 7 hari setelah barang Anda terima. Berlaku untuk pembelian yang sudah lunas.</p>
            </div>

            <div class="fade-up bg-white p-8 rounded-2xl border hover:border-[#c99a32] transition-colors duration-300" style="border-color: #e5e7eb;">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-5" style="background-color: #fbf6e9;">
                    <svg class="w-6 h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold mb-3" style="color: #15233d;">Ditinjau Admin</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Ajukan lewat menu Akun, Refund, lengkapi alasan dan foto bukti. Admin meninjau paling lama 1x24 jam. Setelah disetujui, barang dikembalikan lewat kurir sesuai arahan admin.</p>
            </div>

            <div class="fade-up bg-white p-8 rounded-2xl border hover:border-[#c99a32] transition-colors duration-300" style="border-color: #e5e7eb;">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-5" style="background-color: #fbf6e9;">
                    <svg class="w-6 h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold mb-3" style="color: #15233d;">Dana Kembali ke Anda</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Setelah barang diterima dan dicek, dana ditransfer ke rekening atau e-wallet yang Anda isi. Bila barang bermasalah atau tidak sesuai, ongkir ikut dikembalikan. Alasan lain ditinjau kasus per kasus.</p>
            </div>

        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fadeElements = document.querySelectorAll('.fade-up');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        fadeElements.forEach(el => observer.observe(el));
    });
</script>
</div>

@endsection