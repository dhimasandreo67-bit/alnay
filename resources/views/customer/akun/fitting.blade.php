@extends('layouts.akun')

@section('title', 'Fitting Saya — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('akun.dashboard') }}" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <h1 class="font-display text-2xl font-semibold text-ink/90">Fitting Saya</h1>
        </div>

        @if ($fittingList->isEmpty())
            <div class="bg-panel border border-line/40 rounded-xl p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center mx-auto mb-4">
                    <x-icon name="calendar" :size="28" class="text-gold/60" />
                </div>
                <h2 class="font-display text-lg font-semibold text-ink/80 mb-2">Belum ada booking fitting</h2>
                <p class="text-[11px] text-ink/50 mb-6">Anda belum melakukan booking fitting.</p>
                <a href="{{ route('fitting.create') }}" class="inline-block px-6 py-2.5 rounded-full bg-gold text-white text-[11px] font-semibold hover:bg-gold/90 transition-colors">
                    Booking Fitting
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($fittingList as $f)
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <p class="text-[11px] text-ink/50 mb-1">Kode Booking</p>
                                <p class="text-[13px] font-semibold text-ink/80">{{ $f->kode_booking }}</p>
                            </div>
                            @php
                                $statusClass = match ($f->status) {
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    'batal' => 'bg-rose-50 text-rose-700',
                                    'selesai' => 'bg-emerald-50 text-emerald-700',
                                    default => 'bg-cream text-ink/60',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-[9px] font-bold uppercase {{ $statusClass }}">{{ $f->status }}</span>
                        </div>

                        <div class="space-y-1 text-[11px] mb-3">
                            <div class="flex justify-between">
                                <span class="text-ink/50">Tanggal</span>
                                <span class="font-semibold text-ink/80">{{ $f->slot ? \Carbon\Carbon::parse($f->slot->tanggal)->translatedFormat('l, d M Y') : '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink/50">Jam</span>
                                <span class="font-semibold text-ink/80">{{ $f->slot->jam_mulai ?? '—' }} WIB</span>
                            </div>
                        </div>

                        <a href="{{ route('fitting.show', $f->id) }}" class="inline-flex items-center gap-1 text-[11px] text-gold font-semibold hover:underline">
                            Lihat Detail <x-icon name="chevron-right" :size="12" />
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection