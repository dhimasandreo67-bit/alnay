@extends('layouts.public')

@section('title', 'Cara Sewa - ALNAY.ID')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');
    
    .font-display { font-family: 'Playfair Display', serif; }
    body { font-family: 'Inter', sans-serif; }

    /* === PAKSA LAYOUT AGAR TIDAK MEPET === */
    * { box-sizing: border-box !important; }
    body { overflow-x: clip; }

    .max-w-6xl, .max-w-4xl {
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 2rem !important;
        padding-right: 2rem !important;
        max-width: 1200px !important;
    }
    
    @media (max-width: 768px) {
        .max-w-6xl, .max-w-4xl {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }

    /* === TIMELINE HORIZONTAL 5 KOLOM === */
    .timeline-horizontal {
        display: grid !important;
        grid-template-columns: repeat(5, 1fr) !important;
        gap: 1rem !important;
        position: relative !important;
    }

    @media (max-width: 767px) {
        .timeline-horizontal {
            grid-template-columns: 1fr !important;
            gap: 2rem !important;
        }
    }

    .timeline-horizontal::before {
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
        .timeline-horizontal::before { display: none !important; }
    }

    /* === GRID 2 KOLOM (Metode Sewa & Beli) === */
    .method-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 2rem !important;
    }

    @media (max-width: 767px) {
        .method-grid {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
        }
    }

    /* === GRID 3 KOLOM (Kebijakan) === */
    .policy-grid {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 1.5rem !important;
    }

    @media (max-width: 767px) {
        .policy-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Section padding */
    section {
        padding-top: 4rem !important;
        padding-bottom: 4rem !important;
    }

    /* Card styling */
    .method-card {
        padding: 2rem !important;
        background: white !important;
        border-radius: 1rem !important;
        border: 1px solid #e5e7eb !important;
        transition: all 0.4s ease !important;
        position: relative;
        overflow: hidden;
    }

    .method-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 20px 40px -10px rgba(201, 154, 50, 0.2) !important;
        border-color: #c99a32 !important;
    }

    /* Timeline step */
    .timeline-step {
        text-align: center !important;
        position: relative !important;
        z-index: 1 !important;
        transition: all 0.3s ease;
    }
    
    .timeline-step:hover {
        transform: translateY(-5px);
    }

    .timeline-step .step-number {
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
    
    .timeline-step:hover .step-number {
        transform: scale(1.15);
        box-shadow: 0 0 20px rgba(201, 154, 50, 0.6);
    }

    /* Fade-up animation */
    .fade-up {
        opacity: 0;
        transform: translateY(40px);
        transition: all 1s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .gold-divider {
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #c99a32, #e6c877);
        margin: 0 auto;
    }

    /* Efek Tombol Premium */
    .btn-premium {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .btn-premium:hover {
        box-shadow: 0 10px 25px rgba(201, 154, 50, 0.4);
    }
    .btn-premium::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 50%; height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-25deg);
        transition: all 0.7s ease;
    }
    .btn-premium:hover::before {
        left: 150%;
    }
</style>

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
            Cara Sewa di <span style="color: #c99a32;">ALNAY.ID</span>
        </h1>
        <div class="gold-divider mb-6"></div>
        <p class="text-base md:text-lg max-w-2xl mx-auto leading-relaxed" style="color: #4b5563;">
            Kami merancang proses penyewaan yang mudah, transparan, dan nyaman. 
            Ikuti 5 langkah sederhana ini untuk tampil memukau di momen istimewa Anda.
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
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Hubungi CS kami untuk konfirmasi ukuran. Fitting langsung di studio atau via video call.</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 200ms;">
                <div class="step-number">3</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pembayaran & Deposit</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Lakukan pembayaran sewa dan deposit jaminan (dikembalikan 100% setelah barang kembali aman).</p>
            </div>

            <div class="fade-up timeline-step" style="transition-delay: 300ms;">
                <div class="step-number">4</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: #15233d;">Pengiriman / Ambil</h3>
                <p class="text-sm leading-relaxed" style="color: #6b7280;">Barang dikirim via kurir khusus (H-3 acara) atau diambil langsung di studio kami.</p>
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
     3. METODE SEWA: Ambil di Studio vs Kirim
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
            {{-- Card 1: Ambil di Studio --}}
            <div class="fade-up method-card group">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 transition-transform duration-500 group-hover:rotate-6" style="background-color: #fbf6e9;">
                        <svg class="w-8 h-8" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color: #15233d;">Ambil di Studio</h3>
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
                            <p class="text-xs" style="color: #6b7280;">Pilih tanggal & waktu via WhatsApp</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Fitting Langsung di Studio</p>
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
                        Lokasi Studio:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Jl. Terusan Rajabasa No.11, Bandar Lampung</p>
                    <p class="text-xs mt-2 font-bold flex items-center gap-1.5" style="color: #c99a32;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Senin - Minggu, 09:00 - 20:00 WIB
                    </p>
                </div>

                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank"
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
                    <p class="text-xs" style="color: #6b7280;">Estimasi Rp 50.000 - 150.000 (tergantung lokasi)</p>
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
                        <p class="text-xs" style="color: #6b7280;">Cocok untuk custom bespoke</p>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Konsultasi Desain</p>
                            <p class="text-xs" style="color: #6b7280;">Diskusikan desain & bahan dengan desainer</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Fitting & Pengukuran</p>
                            <p class="text-xs" style="color: #6b7280;">Pengukuran presisi di studio</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Produksi 2-4 Minggu</p>
                            <p class="text-xs" style="color: #6b7280;">Dibuat oleh pengrajin ahli</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Final Fitting & Ambil</p>
                            <p class="text-xs" style="color: #6b7280;">Coba hasil akhir, revisi jika perlu</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                        Keunggulan:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Custom bespoke, garansi fitting ulang hingga pas sempurna</p>
                </div>

                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank"
                   class="btn-premium block w-full py-3 rounded-lg text-center text-sm font-bold uppercase tracking-wider text-white transition-all" 
                   style="background-color: #c99a32;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Konsultasi Custom Bespoke
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
                            <p class="text-xs" style="color: #6b7280;">Ukuran standar S, M, L, XL</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Checkout & Pembayaran</p>
                            <p class="text-xs" style="color: #6b7280;">Transfer bank atau e-wallet</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Pengiriman 1-3 Hari</p>
                            <p class="text-xs" style="color: #6b7280;">Packaging premium & aman</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold" style="color: #15233d;">Garansi 7 Hari</p>
                            <p class="text-xs" style="color: #6b7280;">Tukar ukuran jika tidak pas</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl mb-6 border" style="background-color: #fbf6e9; border-color: rgba(201, 154, 50, 0.2);">
                    <p class="text-xs font-bold mb-1 flex items-center gap-1.5" style="color: #15233d;">
                        <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Bonus:
                    </p>
                    <p class="text-xs" style="color: #6b7280;">Garment bag premium + sertifikat keaslian</p>
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
     5. KEBIJAKAN PENTING
     ============================================================ --}}
<section style="background-color: #faf7f0;">
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
     6. CALL TO ACTION
     ============================================================ --}}
<section class="relative overflow-hidden" style="background-color: #17140f;">
    <div class="absolute top-0 left-0 w-64 h-64 rounded-full opacity-10" style="background-color: #c99a32; filter: blur(80px);"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 rounded-full opacity-10" style="background-color: #c99a32; filter: blur(80px);"></div>

    <div class="relative z-10 max-w-4xl mx-auto text-center fade-up">
        <h2 class="font-display text-3xl md:text-5xl font-bold mb-6 text-white">
            Siap Tampil Memukau<br>
            <span style="color: #c99a32;">di Momen Istimewa Anda?</span>
        </h2>
        <div class="gold-divider mb-8"></div>
        <p class="text-base mb-10 max-w-2xl mx-auto" style="color: #d1d5db;">
            Jangan ragu untuk bertanya tentang ukuran, ketersediaan tanggal, atau paket bundling. 
            Tim kami siap membantu Anda dari awal hingga hari H.
        </p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('koleksi.index') }}" 
               class="btn-premium inline-flex items-center justify-center gap-2 px-8 py-4 rounded-lg text-sm font-bold uppercase tracking-wider text-white transition-all" 
               style="background-color: #c99a32;">
                Jelajahi Koleksi Sekarang
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
            <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank"
               class="btn-premium inline-flex items-center justify-center gap-2 px-8 py-4 rounded-lg text-sm font-bold uppercase tracking-wider text-white border transition-all hover:bg-white/10" 
               style="border-color: rgba(255,255,255,0.3);">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Tanya CS via WhatsApp
            </a>
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

@endsection