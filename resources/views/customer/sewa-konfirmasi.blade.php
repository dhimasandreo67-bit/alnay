@extends('layouts.public')

@section('title', 'Konfirmasi Pembayaran — ' . config('alnay.brand.nama'))

@section('content')

<style>
    [x-cloak] { display: none !important; }

    @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
    @keyframes pd-pop  { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }
    @keyframes pd-pulse { 0%, 100% { opacity: .4; } 50% { opacity: 1; } }

    .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .pd-pop   { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }
    .pd-pulse { animation: pd-pulse 2s ease-in-out infinite; }

    .pd-shine { position: relative; overflow: hidden; }
    .pd-shine::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%);
        transform: translateX(-120%); transition: transform .8s ease; pointer-events: none;
    }
    .pd-shine:hover::after { transform: translateX(120%); }

    @media (prefers-reduced-motion: reduce) {
        .pd-up, .pd-pop, .pd-pulse { animation: none !important; }
        .pd-shine::after { transition: none !important; }
    }
</style>

@php
    $totalBayar = $dp + $deposit;
    $cfg = [
        'kode'    => $transaksi->kode_transaksi,
        'total'   => (float) $totalBayar,
        'menit'   => 15,
    ];
@endphp

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans"
     x-data="konfirmasiSewa(@js($cfg))">
    <div class="max-w-[1180px] mx-auto px-4 md:px-6">

        {{-- Breadcrumb --}}
        <nav class="pd-up text-[11px] text-ink/50 mb-6" style="--d: .05s;">
            <a href="{{ route('koleksi.index') }}" class="hover:text-gold transition-colors flex items-center gap-1">
                <x-icon name="chevron-right" :size="12" class="rotate-180" />
                Kembali ke Katalog
            </a>
        </nav>

        @if (session('success'))
            <div class="pd-up p-3 mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-xs text-emerald-700" style="--d: .1s;">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-6 lg:gap-8">

            {{-- ==================== KIRI — QRIS ==================== --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-6 md:p-8" style="--d: .15s;">
                <h1 class="font-display text-2xl md:text-3xl font-medium text-ink/85 mb-2">Bayar dengan QRIS</h1>
                <p class="text-[12px] text-ink/55 mb-6">Pindai kode di bawah menggunakan aplikasi m-banking, dompet digital, atau QRIS scanner favorit Anda.</p>

                {{-- Countdown --}}
                <div class="bg-gold/5 border border-gold/20 rounded-lg px-4 py-3 flex justify-between items-center mb-6 transition-colors"
                     :class="{ 'border-rose-300 bg-rose-50': habis }">
                    <span class="text-[11px] text-ink/60" x-text="habis ? 'Waktu pembayaran habis' : 'Selesaikan pembayaran dalam'"></span>
                    <span class="font-display text-lg font-medium tabular-nums"
                          :class="habis ? 'text-rose-600' : 'text-gold'"
                          x-text="formatWaktu"></span>
                </div>

                {{-- QR Code --}}
                <div class="flex flex-col items-center mb-6">
                    <div class="pd-pop w-64 h-64 bg-white border border-line/40 rounded-lg flex items-center justify-center" style="--d: .3s;">
                        <div class="text-center">
                            <x-icon name="sparkle" :size="48" class="text-ink/20 mx-auto mb-2" />
                            <p class="text-[10px] text-ink/30 uppercase tracking-wider">QR Code</p>
                            <p class="text-[9px] text-ink/20 mt-1">(Placeholder)</p>
                        </div>
                    </div>
                    <p class="font-sans text-3xl font-semibold text-ink/85 mt-4 tabular-nums">Rp{{ number_format($totalBayar, 0, ',', '.') }}</p>
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
                        <div class="pd-up flex items-start gap-3" style="--d: {{ 0.35 + ($i * 0.05) }}s;">
                            <span class="w-6 h-6 rounded-full bg-cream text-ink/60 text-[10px] font-semibold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                            <p class="text-[11px] text-ink/60 leading-relaxed">{{ $step }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex items-center gap-2 text-[10px] text-ink/40">
                    <span class="w-2 h-2 rounded-full bg-gold pd-pulse"></span>
                    Menunggu pembayaran — halaman ini diperbarui otomatis
                </div>
            </div>

            {{-- ==================== KANAN — Ringkasan ==================== --}}
            <div class="lg:sticky lg:top-24 h-fit">
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .2s;">
                    <h2 class="text-[10px] font-medium uppercase tracking-wider text-ink/50 mb-4">Ringkasan Pembayaran</h2>

                    <div class="flex gap-3 items-center border-b border-line/30 pb-4 mb-4">
                        <div class="w-12 h-12 rounded-lg bg-cream-dim border border-line/40 flex items-center justify-center text-muted text-[8px] uppercase shrink-0">Foto</div>
                        <div>
                            <p class="text-[12px] font-medium text-ink/80">{{ $produk->nama }}</p>
                            <p class="text-[10px] text-ink/50">
                                @if ($transaksi->sewa)
                                    {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_pinjam)->format('d M') }} – 
                                    {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_kembali_rencana)->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2 text-[11px] mb-4">
                        <div class="flex justify-between">
                            <span class="text-ink/50">Total Biaya Sewa</span>
                            <span class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">DP dibayar sekarang</span>
                            <span class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($dp, 0, ',', '.') }}</span>
                        </div>
                        @if ($deposit > 0)
                            <div class="flex justify-between">
                                <span class="text-ink/50">Deposit jaminan KTP</span>
                                <span class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($deposit, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between items-center border-t border-line/40 pt-4 mb-4">
                        <span class="text-[11px] font-medium text-ink/80">Total Dibayar Sekarang</span>
                        <span class="font-sans text-xl font-semibold text-gold tabular-nums">Rp{{ number_format($totalBayar, 0, ',', '.') }}</span>
                    </div>

                    <div class="bg-cream/60 rounded-md px-3 py-2 mb-4 flex justify-between items-center">
                        <span class="text-[9px] text-ink/40">ID Transaksi</span>
                        <span class="text-[10px] font-medium text-ink/80">{{ $transaksi->kode_transaksi }}</span>
                    </div>

                    <button type="button" @click="showSuccessModal = true" :disabled="habis"
                            class="pd-shine w-full h-[42px] rounded-lg bg-gold text-white text-[11px] font-semibold tracking-wider hover:bg-gold/90 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 transition-all duration-300 mb-2">
                        Cek Status Pembayaran
                    </button>
                    <button type="button" class="w-full h-[38px] rounded-lg border border-line/50 bg-white text-ink/65 text-[11px] font-medium hover:bg-cream/50 transition-all duration-300">
                        Unduh Kode QR
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL SUKSES ==================== --}}
    <div x-show="showSuccessModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 backdrop-blur-sm px-4">
        <div x-show="showSuccessModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             @click.away="showSuccessModal = false"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 md:p-8 relative">
            <button @click="showSuccessModal = false" class="absolute top-4 right-4 w-7 h-7 rounded-full bg-cream flex items-center justify-center text-ink/40 hover:text-ink/80 hover:rotate-90 transition-all duration-300">
                <x-icon name="close" :size="14" />
            </button>

            <div class="flex justify-center mb-4">
                <div class="pd-pop w-16 h-16 rounded-full border-2 border-emerald-500 flex items-center justify-center">
                    <x-icon name="check" :size="32" class="text-emerald-500" />
                </div>
            </div>

            <h2 class="font-display text-2xl font-medium text-ink/85 text-center mb-2">Pembayaran Berhasil</h2>
            <p class="text-[12px] text-ink/55 text-center mb-6 leading-relaxed">
                DP sebesar Rp{{ number_format($totalBayar, 0, ',', '.') }} telah kami terima. Jadwal sewa Anda kini terkunci otomatis.
            </p>

            <div class="bg-cream/60 rounded-lg p-4 mb-6 space-y-2 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-ink/50">ID Transaksi</span>
                    <span class="font-medium text-ink/80">{{ $transaksi->kode_transaksi }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink/50">Jumlah</span>
                    <span class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($totalBayar, 0, ',', '.') }}</span>
                </div>
            </div>

            <a href="{{ route('fitting.create') }}" class="pd-shine block w-full py-3 rounded-lg bg-gold text-white text-[11px] font-semibold tracking-wider text-center hover:bg-gold/90 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 mb-2">
                Lanjut ke Booking Fitting
            </a>
            <a href="{{ route('akun.pesanan.index') }}" class="block w-full py-3 rounded-lg border border-line/50 text-ink/65 text-[11px] font-medium text-center hover:bg-cream/50 transition-all duration-300">
                Lihat Riwayat Transaksi
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('konfirmasiSewa', (cfg) => ({
            showSuccessModal: false,
            sisaDetik: cfg.menit * 60,
            _timer: null,

            init() {
                this.mulaiCountdown();
            },

            mulaiCountdown() {
                this._timer = setInterval(() => {
                    if (this.sisaDetik > 0) {
                        this.sisaDetik--;
                    } else {
                        clearInterval(this._timer);
                    }
                }, 1000);
            },

            get habis() {
                return this.sisaDetik <= 0;
            },

            get formatWaktu() {
                const m = Math.floor(this.sisaDetik / 60);
                const s = this.sisaDetik % 60;
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            },
        }));
    });
</script>

@endsection