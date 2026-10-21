@extends('layouts.public')

@section('title', 'Konfirmasi Pembayaran — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans" x-data="{ 
    showSuccessModal: false,
    countdown: '14:42',
    idTransaksi: 'TRX-ALNAY-20260919-0842'
}">
    <div class="max-w-[1180px] mx-auto px-4 md:px-6">

        <nav class="text-[11px] text-ink/50 mb-6">
            <a href="{{ route('beli.checkout') }}" class="hover:text-gold transition-colors flex items-center gap-1">
                <x-icon name="chevron-right" :size="12" class="rotate-180" />
                Kembali ke Ringkasan Pembelian
            </a>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-6 lg:gap-8">

            {{-- KIRI — QRIS --}}
            <div class="bg-panel border border-line/40 rounded-xl p-6 md:p-8">
                <h1 class="font-display text-2xl md:text-3xl font-semibold text-ink/90 mb-2">Bayar dengan QRIS</h1>
                <p class="text-[12px] text-ink/50 mb-6">Pindai kode di bawah menggunakan aplikasi m-banking, dompet digital, atau QRIS scanner favorit Anda.</p>

                <div class="bg-gold/5 border border-gold/20 rounded-lg px-4 py-3 flex justify-between items-center mb-6">
                    <span class="text-[11px] text-ink/60">Selesaikan pembayaran dalam</span>
                    <span class="font-display text-lg font-semibold text-gold" x-text="countdown">14:42</span>
                </div>

                <div class="flex flex-col items-center mb-6">
                    <div class="w-64 h-64 bg-white border border-line/40 rounded-lg flex items-center justify-center">
                        <div class="text-center">
                            <x-icon name="sparkle" :size="48" class="text-ink/20 mx-auto mb-2" />
                            <p class="text-[10px] text-ink/30 uppercase tracking-wider">QR Code</p>
                        </div>
                    </div>
                    <p class="font-display text-3xl font-bold text-ink/90 mt-4">Rp{{ number_format($total, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-ink/40 mt-1">AL-NAY BUSANA ADAT — NMID ID2026093000112</p>
                    <span class="inline-flex items-center gap-1.5 mt-3 px-3 py-1 rounded-full bg-cream text-ink/60 text-[9px] font-medium">
                        <x-icon name="check" :size="10" class="text-emerald-600" />
                        QRIS - Semua Bank & Dompet Digital
                    </span>
                </div>

                <hr class="border-line/30 my-6">

                <div class="space-y-4">
                    @foreach ([
                        'Buka aplikasi pembayaran (m-banking, GoPay, OVO, ShopeePay, Dana, dsb).',
                        'Pilih menu Scan QRIS, lalu arahkan kamera ke kode di atas.',
                        'Periksa nama merchant dan nominal — pastikan sesuai.',
                        'Selesaikan pembayaran dan tunggu konfirmasi otomatis di halaman ini.',
                    ] as $i => $step)
                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-cream text-ink/60 text-[10px] font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                            <p class="text-[11px] text-ink/60 leading-relaxed">{{ $step }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex items-center gap-2 text-[10px] text-ink/40">
                    <span class="w-2 h-2 rounded-full bg-gold animate-pulse"></span>
                    Menunggu pembayaran — halaman ini diperbarui otomatis
                </div>
            </div>

            {{-- KANAN — Ringkasan --}}
            <div class="lg:sticky lg:top-24 h-fit">
                <div class="bg-panel border border-line/40 rounded-xl p-5">
                    <h2 class="text-[10px] font-bold uppercase tracking-wider text-ink/50 mb-4">Ringkasan Pembayaran</h2>

                    <div class="flex gap-3 items-center border-b border-line/30 pb-4 mb-4">
                        <div class="w-12 h-12 rounded-lg bg-gold/20 border border-gold/30 flex items-center justify-center shrink-0">
                            <x-icon name="sparkle" :size="18" class="text-gold/60" />
                        </div>
                        <div>
                            <p class="text-[12px] font-semibold text-ink/80">{{ $produk->nama }}</p>
                            <p class="text-[10px] text-ink/50">Varian {{ $varian }} ×{{ $qty }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 text-[11px] mb-4">
                        <div class="flex justify-between">
                            <span class="text-ink/50">Subtotal</span>
                            <span class="font-semibold text-ink/80">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Ongkir</span>
                            <span class="font-semibold text-ink/80">Rp{{ number_format($ongkir, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-line/40 pt-4 mb-4">
                        <span class="text-[11px] font-semibold text-ink/80">Total Dibayar</span>
                        <span class="font-display text-xl font-bold text-gold">Rp{{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div class="bg-cream/60 rounded-md px-3 py-2 mb-4 flex justify-between items-center">
                        <span class="text-[9px] text-ink/40">ID Transaksi</span>
                        <span class="text-[10px] font-semibold text-ink/80" x-text="idTransaksi"></span>
                    </div>

                    <button type="button" @click="showSuccessModal = true" class="w-full h-[42px] rounded-lg bg-gold/95 text-white text-[10px] font-semibold tracking-wider hover:bg-gold transition-all duration-300 mb-2">
                        Cek Status Pembayaran
                    </button>
                    <button type="button" class="w-full h-[38px] rounded-lg border border-line/50 bg-white text-ink/70 text-[10px] font-medium hover:bg-cream/50 transition-all duration-300">
                        Unduh Kode QR
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Sukses --}}
    <div x-show="showSuccessModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 backdrop-blur-sm px-4"
         style="display: none;">
        
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 md:p-8 relative" @click.away="showSuccessModal = false">
            <button @click="showSuccessModal = false" class="absolute top-4 right-4 w-7 h-7 rounded-full bg-cream flex items-center justify-center text-ink/40 hover:text-ink/80 transition-colors">
                <x-icon name="close" :size="14" />
            </button>

            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 rounded-full border-2 border-emerald-500 flex items-center justify-center">
                    <x-icon name="check" :size="32" class="text-emerald-500" />
                </div>
            </div>

            <h2 class="font-display text-2xl font-bold text-ink/90 text-center mb-2">Pembayaran Berhasil</h2>
            <p class="text-[12px] text-ink/50 text-center mb-6 leading-relaxed">
                Pembayaran sebesar Rp{{ number_format($total, 0, ',', '.') }} telah kami terima. Pesanan akan segera diproses.
            </p>

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
                    <span class="text-ink/50">Jumlah</span>
                    <span class="font-semibold text-ink/80">Rp{{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>

            <a href="{{ route('beranda') }}" class="block w-full py-3 rounded-lg bg-gold text-white text-[11px] font-bold tracking-wider text-center hover:bg-gold/90 transition-all duration-300 mb-2">
                Kembali ke Beranda
            </a>
            <a href="#" class="block w-full py-3 rounded-lg border border-line/50 text-ink/70 text-[11px] font-medium text-center hover:bg-cream/50 transition-all duration-300">
                Lihat Riwayat Transaksi
            </a>
        </div>
    </div>
</div>

@endsection