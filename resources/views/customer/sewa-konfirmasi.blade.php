@extends('layouts.public')

@section('title', 'Konfirmasi Pembayaran — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans" x-data="{ 
    showSuccessModal: false,
    countdown: '14:42',
    idTransaksi: 'TRX-ALNAY-20260919-0842'
}">
    <div class="max-w-[1180px] mx-auto px-4 md:px-6">

        {{-- Breadcrumb --}}
        <nav class="text-[11px] text-ink/50 mb-6">
            <a href="{{ route('sewa.checkout') }}" class="hover:text-gold transition-colors flex items-center gap-1">
                <x-icon name="chevron-right" :size="12" class="rotate-180" />
                Kembali ke Ringkasan Sewa
            </a>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-6 lg:gap-8">

            {{-- ==================== KOLOM KIRI — INSTRUKSI PEMBAYARAN ==================== --}}
            <div class="bg-panel border border-line/40 rounded-xl p-6 md:p-8">
                
                <h1 class="font-display text-2xl md:text-3xl font-semibold text-ink/90 mb-2">Bayar dengan QRIS</h1>
                <p class="text-[12px] text-ink/50 mb-6">Pindai kode di bawah menggunakan aplikasi m-banking, dompet digital, atau QRIS scanner favorit Anda.</p>

                {{-- Countdown --}}
                <div class="bg-gold/5 border border-gold/20 rounded-lg px-4 py-3 flex justify-between items-center mb-6">
                    <span class="text-[11px] text-ink/60">Selesaikan pembayaran dalam</span>
                    <span class="font-display text-lg font-semibold text-gold" x-text="countdown">14:42</span>
                </div>

                {{-- QR Code Placeholder --}}
                <div class="flex flex-col items-center mb-6">
                    <div class="w-64 h-64 bg-white border border-line/40 rounded-lg flex items-center justify-center">
                        {{-- Ganti dengan QR code asli nanti --}}
                        <div class="text-center">
                            <x-icon name="sparkle" :size="48" class="text-ink/20 mx-auto mb-2" />
                            <p class="text-[10px] text-ink/30 uppercase tracking-wider">QR Code</p>
                            <p class="text-[9px] text-ink/20 mt-1">(Placeholder)</p>
                        </div>
                    </div>

                    <p class="font-display text-3xl font-bold text-ink/90 mt-4">Rp1.200.000</p>
                    <p class="text-[10px] text-ink/40 mt-1">AL-NAY BUSANA ADAT — NMID ID2026093000112</p>
                    
                    <span class="inline-flex items-center gap-1.5 mt-3 px-3 py-1 rounded-full bg-cream text-ink/60 text-[9px] font-medium">
                        <x-icon name="check" :size="10" class="text-emerald-600" />
                        QRIS - Semua Bank & Dompet Digital
                    </span>
                </div>

                <hr class="border-line/30 my-6">

                {{-- Langkah-langkah --}}
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-cream text-ink/60 text-[10px] font-bold flex items-center justify-center shrink-0">1</span>
                        <p class="text-[11px] text-ink/60 leading-relaxed">Buka aplikasi pembayaran (m-banking, GoPay, OVO, ShopeePay, Dana, dsb).</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-cream text-ink/60 text-[10px] font-bold flex items-center justify-center shrink-0">2</span>
                        <p class="text-[11px] text-ink/60 leading-relaxed">Pilih menu Scan QRIS, lalu arahkan kamera ke kode di atas.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-cream text-ink/60 text-[10px] font-bold flex items-center justify-center shrink-0">3</span>
                        <div class="flex-1">
                            <p class="text-[11px] text-ink/60 leading-relaxed mb-1">Periksa nama merchant dan nominal — pastikan sesuai</p>
                            <p class="text-right font-semibold text-ink/80 text-[12px]">Rp1.200.000</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-cream text-ink/60 text-[10px] font-bold flex items-center justify-center shrink-0">4</span>
                        <p class="text-[11px] text-ink/60 leading-relaxed">Selesaikan pembayaran dan tunggu konfirmasi otomatis di halaman ini.</p>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-2 text-[10px] text-ink/40">
                    <span class="w-2 h-2 rounded-full bg-gold animate-pulse"></span>
                    Menunggu pembayaran — halaman ini diperbarui otomatis
                </div>
            </div>

            {{-- ==================== KOLOM KANAN — RINGKASAN ==================== --}}
            <div class="lg:sticky lg:top-24 h-fit">
                <div class="bg-panel border border-line/40 rounded-xl p-5">
                    <h2 class="text-[10px] font-bold uppercase tracking-wider text-ink/50 mb-4">Ringkasan Pembayaran</h2>

                    {{-- Produk --}}
                    <div class="flex gap-3 items-center border-b border-line/30 pb-4 mb-4">
                        <div class="w-12 h-12 rounded-lg bg-cream-dim border border-line/40 flex items-center justify-center text-muted text-[8px] uppercase shrink-0">Foto</div>
                        <div>
                            <p class="text-[12px] font-semibold text-ink/80">{{ $produk->nama }}</p>
                            <p class="text-[10px] text-ink/50">
                                Ukuran {{ $sewaData['ukuran'] }} · 
                                {{ \Carbon\Carbon::parse($sewaData['tanggal_ambil'])->format('d M') }} – 
                                {{ \Carbon\Carbon::parse($sewaData['tanggal_kembali'])->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    {{-- Rincian Biaya --}}
                    <div class="space-y-2 text-[11px] mb-4">
                        <div class="flex justify-between">
                            <span class="text-ink/50">Total Biaya Sewa</span>
                            <span class="font-semibold text-ink/80">Rp{{ number_format($totalSewa, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">DP dibayar sekarang</span>
                            <span class="font-semibold text-ink/80">Rp{{ number_format($dp, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Deposit jaminan KTP</span>
                            <span class="font-semibold text-ink/80">Rp{{ number_format($deposit, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-line/40 pt-4 mb-4">
                        <span class="text-[11px] font-semibold text-ink/80">Total Dibayar Sekarang</span>
                        <span class="font-display text-xl font-bold text-gold">Rp1.200.000</span>
                    </div>

                    {{-- ID Transaksi --}}
                    <div class="bg-cream/60 rounded-md px-3 py-2 mb-4 flex justify-between items-center">
                        <span class="text-[9px] text-ink/40">ID Transaksi</span>
                        <span class="text-[10px] font-semibold text-ink/80" x-text="idTransaksi"></span>
                    </div>

                    {{-- Tombol --}}
                    <button type="button" 
                            @click="showSuccessModal = true"
                            class="w-full h-[42px] rounded-lg bg-gold/95 text-white text-[10px] font-semibold tracking-wider hover:bg-gold transition-all duration-300 mb-2">
                        Cek Status Pembayaran
                    </button>
                    <button type="button" 
                            class="w-full h-[38px] rounded-lg border border-line/50 bg-white text-ink/70 text-[10px] font-medium hover:bg-cream/50 transition-all duration-300">
                        Unduh Kode QR
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL SUKSES ==================== --}}
    <div x-show="showSuccessModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 backdrop-blur-sm px-4"
         style="display: none;">
        
        <div x-show="showSuccessModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.away="showSuccessModal = false"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 md:p-8 relative">
            
            {{-- Close --}}
            <button @click="showSuccessModal = false" class="absolute top-4 right-4 w-7 h-7 rounded-full bg-cream flex items-center justify-center text-ink/40 hover:text-ink/80 transition-colors">
                <x-icon name="close" :size="14" />
            </button>

            {{-- Icon Sukses --}}
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 rounded-full border-2 border-emerald-500 flex items-center justify-center">
                    <x-icon name="check" :size="32" class="text-emerald-500" />
                </div>
            </div>

            {{-- Judul --}}
            <h2 class="font-display text-2xl font-bold text-ink/90 text-center mb-2">Pembayaran Berhasil</h2>
            <p class="text-[12px] text-ink/50 text-center mb-6 leading-relaxed">
                DP sebesar Rp1.200.000 telah kami terima. Jadwal sewa Anda kini terkunci otomatis.
            </p>

            {{-- Detail --}}
            <div class="bg-cream/60 rounded-lg p-4 mb-6 space-y-2 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-ink/50">ID Transaksi</span>
                    <span class="font-semibold text-ink/80" x-text="idTransaksi"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink/50">Metode</span>
                    <span class="font-semibold text-ink/80">QRIS — BCA Mobile</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink/50">Waktu</span>
                    <span class="font-semibold text-ink/80">19 Sep 2026, 09.27 WIB</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink/50">Jumlah</span>
                    <span class="font-semibold text-ink/80">Rp1.200.000</span>
                </div>
            </div>

            {{-- Notifikasi Fitting --}}
            <div class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gold/5 border border-gold/20 text-[10px] text-gold mb-4">
                <span class="w-3 h-3 border-2 border-gold border-t-transparent rounded-full animate-spin"></span>
                Mengalihkan ke Booking Fitting dalam 3 detik
            </div>

            {{-- Tombol --}}
            <a href="{{ route('fitting.create') }}" class="block w-full py-3 rounded-lg bg-gold text-white text-[11px] font-bold tracking-wider text-center hover:bg-gold/90 transition-all duration-300 mb-2">
                Lanjut ke Booking Fitting Sekarang
            </a>
            <a href="#" class="block w-full py-3 rounded-lg border border-line/50 text-ink/70 text-[11px] font-medium text-center hover:bg-cream/50 transition-all duration-300">
                Lihat Riwayat Transaksi
            </a>
        </div>
    </div>
</div>

@endsection