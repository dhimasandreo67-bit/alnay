@extends('layouts.public')

@section('title', 'Rincian Booking Fitting — ' . config('alnay.brand.nama'))

@section('content')

<style>
    @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
    @keyframes pd-pop  { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }

    .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .pd-pop   { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }

    .pd-shine { position: relative; overflow: hidden; }
    .pd-shine::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%);
        transform: translateX(-120%); transition: transform .8s ease; pointer-events: none;
    }
    .pd-shine:hover::after { transform: translateX(120%); }

    @media (prefers-reduced-motion: reduce) {
        .pd-up, .pd-pop { animation: none !important; }
        .pd-shine::after { transition: none !important; }
    }
</style>

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans">
    <div class="max-w-[900px] mx-auto px-4 md:px-6">

        {{-- Breadcrumb (SINKRONISASI) --}}
        <nav class="pd-up text-[11px] text-ink/50 mb-3" style="--d: .05s;">
            <a href="{{ route('akun.dashboard') }}#fitting" class="hover:text-gold transition-colors">Riwayat Pemesanan</a>
            <span class="mx-1">/</span>
            <span class="text-ink/70">{{ $reservasi->kode_booking }}</span>
        </nav>

        {{-- Header (SINKRONISASI) --}}
        <div class="pd-up flex items-center gap-3 mb-6" style="--d: .1s;">
            <a href="{{ route('akun.dashboard') }}#fitting" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 hover:-translate-x-0.5 transition-all duration-300">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <h1 class="font-display text-2xl md:text-3xl font-medium text-ink/85">Rincian Booking Fitting</h1>
        </div>

        @if (session('success'))
            <div class="pd-up p-3 mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-xs text-emerald-700 flex items-center gap-2" style="--d: .15s;">
                <x-icon name="check" :size="16" />
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            {{-- Status --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5 flex items-center gap-4" style="--d: .2s;">
                <div class="pd-pop w-10 h-10 rounded-full bg-emerald-50 border-2 border-emerald-500 flex items-center justify-center shrink-0">
                    <x-icon name="check" :size="20" class="text-emerald-500" />
                </div>
                <div>
                    <p class="text-sm font-medium text-ink/85">
                        @if ($reservasi->status === 'pending')
                            Jadwal Fitting Terkonfirmasi
                        @elseif ($reservasi->status === 'batal')
                            Booking Dibatalkan
                        @else
                            Jadwal Fitting {{ ucfirst($reservasi->status) }}
                        @endif
                    </p>
                    <p class="text-[11px] text-ink/50 mt-0.5">Booking ini wajib diselesaikan sebelum tanggal pengambilan busana Anda</p>
                </div>
            </div>

            {{-- Lokasi --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .25s;">
                <h2 class="font-display text-base font-medium text-ink/80 mb-3">Lokasi Fitting</h2>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                        <x-icon name="location" :size="16" class="text-rose-500" />
                    </div>
                    <div>
                        <p class="text-[13px] font-medium text-ink/85">ALNAY.ID Studio</p>
                        <p class="text-[11px] text-ink/50 mt-0.5">{{ config('alnay.studio.alamat') ?? 'Alamat studio belum diatur' }}</p>
                        <p class="text-[10px] text-ink/40 mt-1">Jam Buka: {{ config('alnay.studio.jam_buka') ?? 'Belum diatur' }}</p>
                    </div>
                </div>
            </div>

            {{-- Produk Fitting --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .3s;">
                <h2 class="font-display text-base font-medium text-ink/80 mb-4">Detail Sesi Fitting</h2>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center shrink-0 overflow-hidden border border-line/50">
                        @if($fotoProduk)
                            <img src="{{ asset('storage/' . $fotoProduk) }}" alt="{{ $namaProduk }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gold/10 text-gold font-bold text-lg">
                                {{ substr($namaProduk ?? 'F', 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-[13px] font-medium text-ink/85">{{ $namaProduk ?? 'Sesi Fitting' }}</p>
                        <p class="text-[10px] text-ink/50 mt-0.5">Kode Booking: #{{ $reservasi->kode_booking }}</p>
                    </div>
                    <span class="text-[11px] font-medium text-emerald-600">Gratis</span>
                </div>
                <div class="mt-4 pt-3 border-t border-line/30 flex justify-between items-center">
                    <span class="text-[11px] text-ink/50">Jadwal:</span>
                    <span class="text-[12px] font-medium text-ink/85">
                        {{ $reservasi->slot ? \Carbon\Carbon::parse($reservasi->slot->tanggal)->translatedFormat('l, d F Y') : '—' }}
                        · {{ $reservasi->slot->jam_mulai ?? '—' }} WIB
                    </span>
                </div>
            </div>

            {{-- Butuh Bantuan --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .35s;">
                <h2 class="font-display text-base font-medium text-ink/80 mb-3">Butuh Bantuan?</h2>
                <div class="divide-y divide-line/30">
                    <a href="{{ route('fitting.edit', $reservasi->id) }}" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors group">
                        <span class="flex items-center gap-2"><x-icon name="calendar" :size="14" /> Ubah Tanggal / Jam Fitting</span>
                        <x-icon name="chevron-right" :size="14" class="text-ink/30 group-hover:text-gold group-hover:translate-x-0.5 transition-all" />
                    </a>
                    <form method="POST" action="{{ route('fitting.cancel', $reservasi->id) }}" onsubmit="return confirm('Yakin ingin membatalkan booking fitting ini?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-red-500 transition-colors group">
                            <span class="flex items-center gap-2"><x-icon name="close" :size="14" /> Batalkan Booking Fitting</span>
                            <x-icon name="chevron-right" :size="14" class="text-ink/30 group-hover:text-red-500 group-hover:translate-x-0.5 transition-all" />
                        </button>
                    </form>
                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors group">
                        <span class="flex items-center gap-2"><x-icon name="whatsapp" :size="14" /> Hubungi Customer Service</span>
                        <x-icon name="chevron-right" :size="14" class="text-ink/30 group-hover:text-gold group-hover:translate-x-0.5 transition-all" />
                    </a>
                </div>
            </div>

            {{-- Detail Booking --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .4s;">
                <div class="space-y-2 text-[12px]">
                    <div class="flex justify-between items-center">
                        <span class="text-ink/50">No. Booking</span>
                        <span class="flex items-center gap-2">
                            <span class="font-medium text-ink/80">{{ $reservasi->kode_booking }}</span>
                            <button onclick="navigator.clipboard.writeText('{{ $reservasi->kode_booking }}'); this.innerText='Tersalin'" class="px-2 py-0.5 rounded border border-line/50 text-[9px] text-ink/50 hover:border-gold/50 hover:text-gold transition-all">Salin</button>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink/50">Biaya Fitting</span>
                        <span class="font-medium text-emerald-600">Gratis</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink/50">Waktu Booking Dibuat</span>
                        <span class="font-medium text-ink/80 px-2 py-1 rounded-md bg-gray-50 border border-gray-200 text-[11px]">
                            {{ $reservasi->created_at->translatedFormat('d F Y, H:i') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi (SINKRONISASI) --}}
            <div class="pd-up grid grid-cols-1 sm:grid-cols-2 gap-3" style="--d: .45s;">
                <a href="{{ route('fitting.edit', $reservasi->id) }}" class="w-full h-[44px] rounded-lg border border-gold/60 bg-white text-gold text-[11px] font-semibold hover:bg-gold/5 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                    Ubah Jadwal
                </a>
                <a href="{{ route('akun.dashboard') }}#penyewaan" class="pd-shine w-full h-[44px] rounded-lg bg-gold text-white text-[11px] font-semibold hover:bg-gold/90 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center">
                    Lihat Pesanan Sewa
                </a>
            </div>
        </div>
    </div>
</div>

@endsection