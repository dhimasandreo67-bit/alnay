@extends('layouts.akun')

@section('title', 'Akun Saya — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4 space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-gold to-[#a37b2a] flex items-center justify-center text-white font-display text-xl font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="font-display text-xl md:text-2xl font-semibold text-ink">Halo, {{ $user->name }}</h1>
                <p class="text-[11px] text-ink/50">Selamat datang di akun Anda</p>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <a href="{{ route('akun.fitting.index') }}" class="bg-panel border border-line/40 rounded-xl p-4 hover:border-gold/50 transition-all group">
                <p class="text-[9px] uppercase tracking-wider text-ink/40 mb-1">Total Fitting</p>
                <p class="font-display text-2xl font-semibold text-ink/90">{{ $totalFitting }}</p>
                <p class="text-[10px] text-gold mt-1 group-hover:underline">Lihat semua →</p>
            </a>
            <a href="{{ route('akun.pesanan.index') }}" class="bg-panel border border-line/40 rounded-xl p-4 hover:border-gold/50 transition-all group">
                <p class="text-[9px] uppercase tracking-wider text-ink/40 mb-1">Pesanan Aktif</p>
                <p class="font-display text-2xl font-semibold text-ink/90">{{ count($pesananSession) }}</p>
                <p class="text-[10px] text-gold mt-1 group-hover:underline">Lihat semua →</p>
            </a>
            <a href="{{ route('akun.profil.edit') }}" class="bg-panel border border-line/40 rounded-xl p-4 hover:border-gold/50 transition-all group">
                <p class="text-[9px] uppercase tracking-wider text-ink/40 mb-1">Profil</p>
                <p class="font-display text-2xl font-semibold text-ink/90">Edit</p>
                <p class="text-[10px] text-gold mt-1 group-hover:underline">Ubah data →</p>
            </a>
        </div>

        {{-- Jadwal Fitting Aktif --}}
        @if ($fittingAktif)
            <div class="bg-panel border border-line/40 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-display text-base font-semibold text-ink/90">Jadwal Fitting Terdekat</h2>
                    <a href="{{ route('akun.fitting.index') }}" class="text-[10px] text-gold hover:underline">Lihat semua →</a>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg bg-cream/50 border border-line/30">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 border-2 border-emerald-500 flex items-center justify-center shrink-0">
                        <x-icon name="check" :size="16" class="text-emerald-500" />
                    </div>
                    <div class="flex-1">
                        <p class="text-[12px] font-semibold text-ink/80">{{ $fittingAktif->kode_booking }}</p>
                        <p class="text-[10px] text-ink/50">
                            {{ $fittingAktif->slot ? \Carbon\Carbon::parse($fittingAktif->slot->tanggal)->translatedFormat('l, d M Y') : '—' }}
                            · {{ $fittingAktif->slot->jam_mulai ?? '—' }} WIB
                        </p>
                    </div>
                    <a href="{{ route('fitting.show', $fittingAktif->id) }}" class="text-[10px] text-gold font-semibold hover:underline">Detail →</a>
                </div>
            </div>
        @endif

        {{-- Menu Cepat --}}
        <div class="bg-panel border border-line/40 rounded-xl p-5">
            <h2 class="font-display text-base font-semibold text-ink/90 mb-3">Menu Cepat</h2>
            <div class="divide-y divide-line/30">
                <a href="{{ route('akun.pesanan.index') }}" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors">
                    <span class="flex items-center gap-2"><x-icon name="cart" :size="14" /> Pesanan Saya</span>
                    <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                </a>
                <a href="{{ route('akun.fitting.index') }}" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors">
                    <span class="flex items-center gap-2"><x-icon name="calendar" :size="14" /> Fitting Saya</span>
                    <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                </a>
                <a href="{{ route('akun.refund.index') }}" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors">
                    <span class="flex items-center gap-2"><x-icon name="refresh-cw" :size="14" /> Ajukan Refund</span>
                    <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                </a>
                <a href="{{ route('akun.profil.edit') }}" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors">
                    <span class="flex items-center gap-2"><x-icon name="user" :size="14" /> Edit Profil</span>
                    <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                </a>
            </div>
        </div>
    </div>
</div>

@endsection