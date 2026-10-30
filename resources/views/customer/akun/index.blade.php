@extends('layouts.akun')

@section('title', 'Akun Saya — ' . config('alnay.brand.nama'))

@section('content')

<style>
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

    /* Animasi Khusus Hover Kartu Statistik */
    .stat-card:hover .icon-box {
        transform: rotate(-10deg) scale(1.15);
        background-color: rgba(201, 154, 50, 0.15);
        color: #c99a32;
    }
    .stat-card:hover .arrow-icon {
        transform: translateX(3px);
    }
    .stat-card:hover {
        border-color: rgba(201, 154, 50, 0.4);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        transform: translateY(-4px);
    }
</style>

<div class="bg-cream min-h-screen py-10 md:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-8">

        {{-- 1. HEADER PROFIL --}}
        <div class="pd-up flex flex-col sm:flex-row sm:items-center gap-5" style="--d: .05s;">
            @if($user->foto_profil)
                <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="{{ $user->name }}" 
                     class="pd-pop w-20 h-20 md:w-24 md:h-24 rounded-full object-cover shadow-lg border-4 border-white">
            @else
                <div class="pd-pop w-20 h-20 md:w-24 md:h-24 rounded-full bg-gradient-to-br from-gold to-[#a37b2a] flex items-center justify-center text-white font-display text-3xl font-semibold shadow-lg border-4 border-white">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h1 class="font-display text-2xl md:text-3xl font-bold text-ink/90">Halo, {{ $user->name }}!</h1>
                <p class="text-sm text-ink/60 mt-1">Selamat datang kembali di akun Anda.</p>
            </div>
        </div>

        {{-- 2. KARTU STATISTIK --}}
                {{-- 2. KARTU STATISTIK --}}
        <div class="grid grid-cols-3 gap-2 sm:gap-4">
            {{-- Fitting --}}
            <a href="{{ route('akun.fitting.index') }}" 
               class="stat-card pd-up group bg-panel border border-line/40 rounded-2xl p-3 sm:p-4 transition-all duration-300 flex flex-col h-full"
               style="--d: .1s;">
                {{-- Header: Judul & Ikon --}}
                <div class="flex items-start justify-between gap-1 mb-2 sm:mb-3">
                    <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-ink/40 leading-tight pt-1">Total Fitting</p>
                    <div class="icon-box w-6 h-6 sm:w-8 sm:h-8 rounded-md bg-gold/10 text-gold flex items-center justify-center transition-all duration-300 shrink-0">
                        <x-icon name="calendar" class="w-3 h-3 sm:w-4 sm:h-4" />
                    </div>
                </div>
                
                {{-- Angka --}}
                <p class="font-display text-2xl sm:text-3xl font-black text-ink/90">{{ $totalFitting }}</p>
                
                {{-- Tombol Bawah --}}
                <div class="text-[9px] sm:text-xs font-semibold text-gold mt-auto pt-3 flex items-center gap-0.5 sm:gap-1">
                    Lihat <span class="hidden sm:inline">semua</span>
                    <x-icon name="chevron-right" class="w-3 h-3 sm:w-3.5 sm:h-3.5 arrow-icon transition-transform duration-300" />
                </div>
            </a>

            {{-- Pesanan --}}
            <a href="{{ route('akun.pesanan.index') }}" 
               class="stat-card pd-up group bg-panel border border-line/40 rounded-2xl p-3 sm:p-4 transition-all duration-300 flex flex-col h-full"
               style="--d: .15s;">
                <div class="flex items-start justify-between gap-1 mb-2 sm:mb-3">
                    <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-ink/40 leading-tight pt-1">Pesanan Aktif</p>
                    <div class="icon-box w-6 h-6 sm:w-8 sm:h-8 rounded-md bg-gold/10 text-gold flex items-center justify-center transition-all duration-300 shrink-0">
                        <x-icon name="cart" class="w-3 h-3 sm:w-4 sm:h-4" />
                    </div>
                </div>
                
                <p class="font-display text-2xl sm:text-3xl font-black text-ink/90">{{ count($pesananSession) }}</p>
                
                <div class="text-[9px] sm:text-xs font-semibold text-gold mt-auto pt-3 flex items-center gap-0.5 sm:gap-1">
                    Lihat <span class="hidden sm:inline">semua</span>
                    <x-icon name="chevron-right" class="w-3 h-3 sm:w-3.5 sm:h-3.5 arrow-icon transition-transform duration-300" />
                </div>
            </a>

            {{-- Profil --}}
            <a href="{{ route('akun.profil.edit') }}" 
               class="stat-card pd-up group bg-panel border border-line/40 rounded-2xl p-3 sm:p-4 transition-all duration-300 flex flex-col h-full"
               style="--d: .2s;">
                <div class="flex items-start justify-between gap-1 mb-2 sm:mb-3">
                    <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-ink/40 leading-tight pt-1">Profil Saya</p>
                    <div class="icon-box w-6 h-6 sm:w-8 sm:h-8 rounded-md bg-gold/10 text-gold flex items-center justify-center transition-all duration-300 shrink-0">
                        <x-icon name="user" class="w-3 h-3 sm:w-4 sm:h-4" />
                    </div>
                </div>
                
                {{-- Teks pengganti angka --}}
                <p class="font-display text-sm sm:text-base font-black text-ink/90 leading-tight">Edit<br class="hidden sm:block"> Data</p>
                
                <div class="text-[9px] sm:text-xs font-semibold text-gold mt-auto pt-3 flex items-center gap-0.5 sm:gap-1">
                    Ubah <span class="hidden sm:inline">data</span>
                    <x-icon name="chevron-right" class="w-3 h-3 sm:w-3.5 sm:h-3.5 arrow-icon transition-transform duration-300" />
                </div>
            </a>
        </div>

        {{-- 3. JADWAL FITTING TERDEKAT --}}
        @if ($fittingAktif)
            <div class="pd-up bg-panel border border-line/40 rounded-2xl shadow-sm overflow-hidden" style="--d: .25s;">
                <div class="p-5 md:p-6 border-b border-line/30 flex items-center justify-between">
                    <h2 class="font-display text-lg font-bold text-ink/90">Jadwal Fitting Terdekat</h2>
                    <a href="{{ route('akun.fitting.index') }}" class="text-xs font-bold text-gold hover:underline flex items-center gap-1">
                        Lihat semua <x-icon name="chevron-right" :size="14" />
                    </a>
                </div>
                
                <div class="p-5 md:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border border-emerald-100 bg-emerald-50/40 hover:border-emerald-200 transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="pd-pop w-12 h-12 rounded-full bg-emerald-100 border-2 border-emerald-500 flex items-center justify-center shrink-0">
                                <x-icon name="check" :size="20" class="text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-ink/90">{{ $fittingAktif->kode_booking }}</p>
                                <p class="text-xs text-ink/60 mt-0.5">
                                    {{ $fittingAktif->slot ? \Carbon\Carbon::parse($fittingAktif->slot->tanggal)->translatedFormat('l, d M Y') : '—' }} 
                                    • {{ $fittingAktif->slot->jam_mulai ?? '—' }} WIB
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('fitting.show', $fittingAktif->id) }}" class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-white border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-50 transition-colors shadow-sm shrink-0">
                            Lihat Detail <x-icon name="chevron-right" :size="14" />
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- 4. MENU CEPAT --}}
        <div class="pd-up bg-panel border border-line/40 rounded-2xl shadow-sm overflow-hidden" style="--d: .3s;">
            <div class="p-5 md:p-6 border-b border-line/30">
                <h2 class="font-display text-lg font-bold text-ink/90">Menu Cepat</h2>
            </div>
            
            <div class="divide-y divide-line/30">
                {{-- Pesanan Saya --}}
                <a href="{{ route('akun.pesanan.index') }}" class="flex items-center justify-between p-5 hover:bg-cream/40 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-cream/50 text-ink/60 flex items-center justify-center group-hover:bg-gold/10 group-hover:text-gold transition-colors">
                            <x-icon name="cart" :size="18" />
                        </div>
                        <span class="text-sm font-semibold text-ink/80">Pesanan Saya</span>
                    </div>
                    <x-icon name="chevron-right" :size="16" class="text-ink/30 group-hover:text-gold group-hover:translate-x-1 transition-all" />
                </a>

                {{-- Fitting Saya --}}
                <a href="{{ route('akun.fitting.index') }}" class="flex items-center justify-between p-5 hover:bg-cream/40 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-cream/50 text-ink/60 flex items-center justify-center group-hover:bg-gold/10 group-hover:text-gold transition-colors">
                            <x-icon name="calendar" :size="18" />
                        </div>
                        <span class="text-sm font-semibold text-ink/80">Fitting Saya</span>
                    </div>
                    <x-icon name="chevron-right" :size="16" class="text-ink/30 group-hover:text-gold group-hover:translate-x-1 transition-all" />
                </a>

                {{-- Ajukan Refund --}}
                <a href="{{ route('akun.refund.index') }}" class="flex items-center justify-between p-5 hover:bg-cream/40 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-cream/50 text-ink/60 flex items-center justify-center group-hover:bg-gold/10 group-hover:text-gold transition-colors">
                            <x-icon name="refresh-cw" :size="18" />
                        </div>
                        <span class="text-sm font-semibold text-ink/80">Ajukan Refund</span>
                    </div>
                    <x-icon name="chevron-right" :size="16" class="text-ink/30 group-hover:text-gold group-hover:translate-x-1 transition-all" />
                </a>

                {{-- Edit Profil --}}
                <a href="{{ route('akun.profil.edit') }}" class="flex items-center justify-between p-5 hover:bg-cream/40 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-cream/50 text-ink/60 flex items-center justify-center group-hover:bg-gold/10 group-hover:text-gold transition-colors">
                            <x-icon name="user" :size="18" />
                        </div>
                        <span class="text-sm font-semibold text-ink/80">Edit Profil</span>
                    </div>
                    <x-icon name="chevron-right" :size="16" class="text-ink/30 group-hover:text-gold group-hover:translate-x-1 transition-all" />
                </a>
            </div>
        </div>

    </div>
</div>

@endsection