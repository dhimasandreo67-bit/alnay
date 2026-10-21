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
</style>

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4 space-y-6">

        {{-- Header --}}
        <div class="pd-up flex items-center gap-4" style="--d: .05s;">
            <div class="pd-pop w-14 h-14 rounded-full bg-gradient-to-br from-gold to-[#a37b2a] flex items-center justify-center text-white font-display text-xl font-semibold shadow-md">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="font-display text-xl md:text-2xl font-medium text-ink/85">Halo, {{ $user->name }}</h1>
                <p class="text-[11px] text-ink/50">Selamat datang di akun Anda</p>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <a href="{{ route('akun.fitting.index') }}" 
               class="pd-up bg-panel border border-line/40 rounded-xl p-4 hover:border-gold/50 hover:-translate-y-0.5 hover:shadow-md transition-all duration-300 group"
               style="--d: .1s;">
                <p class="text-[9px] uppercase tracking-wider text-ink/40 mb-1">Total Fitting</p>
                <p class="font-display text-2xl font-medium text-ink/85">{{ $totalFitting }}</p>
                <p class="text-[10px] text-gold mt-1 group-hover:underline">Lihat semua →</p>
            </a>
            <a href="{{ route('akun.pesanan.index') }}" 
               class="pd-up bg-panel border border-line/40 rounded-xl p-4 hover:border-gold/50 hover:-translate-y-0.5 hover:shadow-md transition-all duration-300 group"
               style="--d: .15s;">
                <p class="text-[9px] uppercase tracking-wider text-ink/40 mb-1">Pesanan Aktif</p>
                <p class="font-display text-2xl font-medium text-ink/85">{{ count($pesananSession) }}</p>
                <p class="text-[10px] text-gold mt-1 group-hover:underline">Lihat semua →</p>
            </a>
            <a href="{{ route('akun.profil.edit') }}" 
               class="pd-up bg-panel border border-line/40 rounded-xl p-4 hover:border-gold/50 hover:-translate-y-0.5 hover:shadow-md transition-all duration-300 group"
               style="--d: .2s;">
                <p class="text-[9px] uppercase tracking-wider text-ink/40 mb-1">Profil</p>
                <p class="font-display text-2xl font-medium text-ink/85">Edit</p>
                <p class="text-[10px] text-gold mt-1 group-hover:underline">Ubah data →</p>
            </a>
        </div>

        {{-- Jadwal Fitting Aktif --}}
        @if ($fittingAktif)
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .25s;">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-display text-base font-medium text-ink/85">Jadwal Fitting Terdekat</h2>
                    <a href="{{ route('akun.fitting.index') }}" class="text-[10px] text-gold font-medium hover:underline">Lihat semua →</a>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg bg-cream/50 border border-line/30 hover:border-gold/40 transition-all duration-300">
                    <div class="pd-pop w-10 h-10 rounded-full bg-emerald-50 border-2 border-emerald-500 flex items-center justify-center shrink-0">
                        <x-icon name="check" :size="16" class="text-emerald-500" />
                    </div>
                    <div class="flex-1">
                        <p class="text-[12px] font-medium text-ink/80">{{ $fittingAktif->kode_booking }}</p>
                        <p class="text-[10px] text-ink/50">
                            {{ $fittingAktif->slot ? \Carbon\Carbon::parse($fittingAktif->slot->tanggal)->translatedFormat('l, d M Y') : '—' }}
                            · {{ $fittingAktif->slot->jam_mulai ?? '—' }} WIB
                        </p>
                    </div>
                    <a href="{{ route('fitting.show', $fittingAktif->id) }}" class="text-[10px] text-gold font-medium hover:underline">Detail →</a>
                </div>
            </div>
        @endif

        {{-- Menu Cepat --}}
        <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .3s;">
            <h2 class="font-display text-base font-medium text-ink/85 mb-3">Menu Cepat</h2>
            <div class="divide-y divide-line/30">
                <a href="{{ route('akun.pesanan.index') }}" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors group">
                    <span class="flex items-center gap-2"><x-icon name="cart" :size="14" /> Pesanan Saya</span>
                    <x-icon name="chevron-right" :size="14" class="text-ink/30 group-hover:text-gold group-hover:translate-x-0.5 transition-all" />
                </a>
                <a href="{{ route('akun.fitting.index') }}" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors group">
                    <span class="flex items-center gap-2"><x-icon name="calendar" :size="14" /> Fitting Saya</span>
                    <x-icon name="chevron-right" :size="14" class="text-ink/30 group-hover:text-gold group-hover:translate-x-0.5 transition-all" />
                </a>
                <a href="{{ route('akun.refund.index') }}" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors group">
                    <span class="flex items-center gap-2"><x-icon name="refresh-cw" :size="14" /> Ajukan Refund</span>
                    <x-icon name="chevron-right" :size="14" class="text-ink/30 group-hover:text-gold group-hover:translate-x-0.5 transition-all" />
                </a>
                <a href="{{ route('akun.profil.edit') }}" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors group">
                    <span class="flex items-center gap-2"><x-icon name="user" :size="14" /> Edit Profil</span>
                    <x-icon name="chevron-right" :size="14" class="text-ink/30 group-hover:text-gold group-hover:translate-x-0.5 transition-all" />
                </a>
            </div>
        </div>
    </div>
</div>

@endsection