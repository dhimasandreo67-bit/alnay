@extends('layouts.public')

@section('title', 'Riwayat Fitting — ' . config('alnay.brand.nama'))

@section('content')
<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans">
    <div class="max-w-[800px] mx-auto px-4">
        
        <div class="mb-6 flex items-center gap-3">
            {{-- SINKRONISASI: Kembali ke Dashboard Tab Fitting --}}
            <a href="{{ route('akun.dashboard') }}#fitting" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <h1 class="font-display text-2xl font-medium text-ink/85">Riwayat Booking Fitting</h1>
        </div>

        @if($reservasis->isEmpty())
            <div class="bg-white border border-line rounded-2xl p-12 text-center shadow-sm">
                <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center mx-auto mb-4">
                    <x-icon name="calendar" :size="24" class="text-gold/60" />
                </div>
                <p class="text-sm font-medium text-ink/70 mb-2">Belum ada jadwal fitting</p>
                <p class="text-xs text-ink/50 mb-6">Anda belum pernah melakukan booking jadwal fitting.</p>
                <a href="{{ route('fitting.create') }}" class="inline-block px-6 py-2.5 bg-gold text-white text-xs font-bold rounded-lg hover:bg-gold/90 transition-all">Booking Sekarang</a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($reservasis as $r)
                    <a href="{{ route('fitting.show', $r->id) }}" class="block bg-white border border-line/40 rounded-xl p-5 hover:border-gold/50 hover:shadow-md transition-all group">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-[10px] font-bold text-ink/40 uppercase tracking-wider mb-1">Kode: {{ $r->kode_booking }}</p>
                                @if ($r->slot)
                                    <p class="text-sm font-bold text-ink/85">{{ \Carbon\Carbon::parse($r->slot->tanggal)->translatedFormat('l, d F Y') }}</p>
                                    <p class="text-xs text-ink/60 mt-0.5">Jam {{ \Carbon\Carbon::parse($r->slot->jam_mulai)->format('H:i') }} WIB</p>
                                @else
                                    <p class="text-sm font-bold text-amber-700">Belum memilih jadwal</p>
                                    <p class="text-xs text-ink/60 mt-0.5">Pilih tanggal & jam agar jadwal terdaftar.</p>
                                @endif
                            </div>
                            @php
                                $badge = match(strtolower($r->status)) {
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'batal' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-md border text-[10px] font-bold uppercase tracking-wider {{ $badge }}">
                                {{ $r->status }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-line/30 mt-3">
                            <span class="text-xs text-ink/50">Lihat Tiket & Rincian</span>
                            <x-icon name="chevron-right" :size="14" class="text-gold group-hover:translate-x-0.5 transition-transform" />
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection