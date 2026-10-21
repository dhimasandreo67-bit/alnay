@extends('layouts.public')

@section('title', 'Rincian Booking Fitting — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans">
    <div class="max-w-[900px] mx-auto px-4 md:px-6">

        {{-- Breadcrumb & Judul --}}
        <nav class="text-[11px] text-ink/50 mb-3">
            <a href="{{ route('beranda') }}" class="hover:text-gold transition-colors">Riwayat Pemesanan</a>
            <span class="mx-1">/</span>
            <span class="text-ink/70">{{ $reservasi->kode_booking }}</span>
        </nav>

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('beranda') }}" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <h1 class="font-display text-2xl md:text-3xl font-semibold text-ink/90">Rincian Booking Fitting</h1>
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="p-3 mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-xs text-emerald-700 flex items-center gap-2">
                <x-icon name="check" :size="16" />
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">

            {{-- Status --}}
            <div class="bg-panel border border-line/40 rounded-xl p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-emerald-50 border-2 border-emerald-500 flex items-center justify-center shrink-0">
                    <x-icon name="check" :size="20" class="text-emerald-500" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink/80">
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
            <div class="bg-panel border border-line/40 rounded-xl p-5">
                <h2 class="font-display text-base font-semibold text-ink/80 mb-3">Lokasi Fitting</h2>
                <div class="flex items-start gap-2 mb-2">
                    <x-icon name="location" :size="16" class="text-rose-500 shrink-0 mt-0.5" />
                    <div>
                        <p class="text-[13px] font-semibold text-ink/80">AL-NAY.ID Studio Bandung</p>
                        <p class="text-[11px] text-ink/50 mt-0.5">
                            {{ config('alnay.studio.alamat') }}
                            <a href="#" class="text-gold hover:underline ml-1">Buka di Peta ›</a>
                        </p>
                    </div>
                </div>
                <span class="inline-block mt-2 px-3 py-1 rounded-md bg-cream/60 border border-line/30 text-[10px] text-ink/60">
                    {{ config('alnay.studio.jam_buka') }}
                </span>
            </div>

            {{-- Produk Fitting --}}
            <div class="bg-panel border border-line/40 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-line/30 flex justify-between items-center">
                    <span class="inline-flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-gold/10 text-gold text-[9px] font-bold uppercase">Official</span>
                        <span class="text-[12px] font-medium text-ink/70">AL-NAY.ID Studio Bandung</span>
                    </span>
                    <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-lg bg-rose-900/80 flex items-center justify-center shrink-0">
                            <x-icon name="sparkle" :size="20" class="text-white/40" />
                        </div>
                        <div class="flex-1">
                            <p class="text-[13px] font-semibold text-ink/80">Set Pasangan Sunda Siger Emas</p>
                            <p class="text-[10px] text-ink/50 mt-0.5">Untuk pesanan sewa #ALN-SW-10234</p>
                        </div>
                        <span class="text-[11px] font-semibold text-emerald-600">Gratis</span>
                    </div>
                    <div class="mt-4 pt-3 border-t border-line/30 text-right">
                        <span class="text-[11px] text-ink/50">Jadwal: </span>
                        <span class="text-[12px] font-semibold text-ink/80">
                            {{ $reservasi->slot ? \Carbon\Carbon::parse($reservasi->slot->tanggal)->translatedFormat('l, d F Y') : '—' }}
                            · {{ $reservasi->slot->jam_mulai ?? '—' }} WIB
                        </span>
                    </div>
                </div>
            </div>

            {{-- Butuh Bantuan --}}
            <div class="bg-panel border border-line/40 rounded-xl p-5">
                <h2 class="font-display text-base font-semibold text-ink/80 mb-3">Butuh Bantuan?</h2>
                <div class="divide-y divide-line/30">
                    <a href="#" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors">
                        <span class="flex items-center gap-2"><x-icon name="calendar" :size="14" /> Ubah Tanggal / Jam Fitting</span>
                        <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                    </a>
                    <form method="POST" action="{{ route('fitting.cancel', $reservasi->id) }}" onsubmit="return confirm('Yakin ingin membatalkan booking fitting ini?')">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-red-500 transition-colors">
                            <span class="flex items-center gap-2"><x-icon name="close" :size="14" /> Batalkan Booking Fitting</span>
                            <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                        </button>
                    </form>
                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" class="flex items-center justify-between py-3 text-[12px] text-ink/70 hover:text-gold transition-colors">
                        <span class="flex items-center gap-2"><x-icon name="whatsapp" :size="14" /> Hubungi Customer Service</span>
                        <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                    </a>
                </div>
            </div>

            {{-- Detail Booking --}}
            <div class="bg-panel border border-line/40 rounded-xl p-5">
                <div class="space-y-2 text-[12px]">
                    <div class="flex justify-between items-center">
                        <span class="text-ink/50">No. Booking</span>
                        <span class="flex items-center gap-2">
                            <span class="font-semibold text-ink/80">{{ $reservasi->kode_booking }}</span>
                            <button onclick="navigator.clipboard.writeText('{{ $reservasi->kode_booking }}'); this.innerText='Tersalin'" class="px-2 py-0.5 rounded border border-line/50 text-[9px] text-ink/50 hover:border-gold/50 transition-all">Salin</button>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink/50">Biaya Fitting</span>
                        <span class="font-semibold text-emerald-600">Gratis</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink/50">Waktu Booking Dibuat</span>
                        <span class="font-semibold text-ink/80">{{ $reservasi->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="#" class="w-full h-[44px] rounded-lg border border-gold/60 bg-white text-gold text-[11px] font-semibold hover:bg-gold/5 transition-all duration-300 flex items-center justify-center">
                    Ubah Jadwal
                </a>
                <a href="#" class="w-full h-[44px] rounded-lg bg-gold text-white text-[11px] font-semibold hover:bg-gold/90 transition-all duration-300 flex items-center justify-center">
                    Lihat Pesanan Sewa Terkait
                </a>
            </div>
        </div>
    </div>
</div>

@endsection