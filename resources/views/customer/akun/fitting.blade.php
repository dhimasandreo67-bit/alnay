@extends('layouts.akun')

@section('title', 'Fitting Saya — ' . config('alnay.brand.nama'))

@section('content')

<style>
    @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
    @keyframes pd-pop  { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }

    .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .pd-pop   { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }

    .pd-reveal { transition: opacity .7s ease, transform .7s cubic-bezier(.22, .8, .3, 1); transition-delay: var(--d, 0s); }
    .pd-js .pd-reveal:not(.is-in) { opacity: 0; transform: translateY(20px); }

    .pd-shine { position: relative; overflow: hidden; }
    .pd-shine::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%);
        transform: translateX(-120%); transition: transform .8s ease; pointer-events: none;
    }
    .pd-shine:hover::after { transform: translateX(120%); }

    @media (prefers-reduced-motion: reduce) {
        .pd-up, .pd-pop { animation: none !important; }
        .pd-js .pd-reveal:not(.is-in) { opacity: 1; transform: none; }
        .pd-reveal, .pd-shine::after { transition: none !important; }
    }
</style>

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4">

        <div class="pd-up flex items-center gap-3 mb-6" style="--d: .05s;">
            <a href="{{ route('akun.dashboard') }}" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 hover:-translate-x-0.5 transition-all duration-300">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <h1 class="font-display text-2xl font-medium text-ink/85">Fitting Saya</h1>
        </div>

        @if ($fittingList->isEmpty())
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-12 text-center" style="--d: .1s;">
                <div class="pd-pop w-16 h-16 rounded-full bg-cream flex items-center justify-center mx-auto mb-4">
                    <x-icon name="calendar" :size="28" class="text-gold/60" />
                </div>
                <h2 class="font-display text-lg font-medium text-ink/80 mb-2">Belum ada booking fitting</h2>
                <p class="text-[11px] text-ink/50 mb-6">Anda belum melakukan booking fitting.</p>
                <a href="{{ route('fitting.create') }}" class="pd-shine inline-block px-6 py-2.5 rounded-full bg-gold text-white text-[11px] font-medium hover:bg-gold/90 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                    Booking Fitting
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($fittingList as $index => $f)
                    @php
                        $statusClass = match ($f->status) {
                            'pending' => 'bg-amber-50 text-amber-700',
                            'batal' => 'bg-rose-50 text-rose-700',
                            'selesai' => 'bg-emerald-50 text-emerald-700',
                            'terkonfirmasi' => 'bg-blue-50 text-blue-700',
                            default => 'bg-cream text-ink/60',
                        };
                    @endphp
                    <div class="pd-reveal bg-panel border border-line/40 rounded-xl p-5 hover:border-gold/50 hover:-translate-y-0.5 hover:shadow-md transition-all duration-300" style="--d: {{ $index * 0.08 }}s;">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <p class="text-[11px] text-ink/50 mb-1">Kode Booking</p>
                                <p class="text-[13px] font-medium text-ink/80">{{ $f->kode_booking }}</p>
                            </div>
                            <span class="px-2 py-1 rounded text-[9px] font-semibold uppercase {{ $statusClass }}">{{ $f->status }}</span>
                        </div>

                        <div class="space-y-1 text-[11px] mb-3">
                            <div class="flex justify-between">
                                <span class="text-ink/50">Tanggal</span>
                                <span class="font-medium text-ink/80">{{ $f->slot ? \Carbon\Carbon::parse($f->slot->tanggal)->translatedFormat('l, d M Y') : '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink/50">Jam</span>
                                <span class="font-medium text-ink/80">{{ $f->slot->jam_mulai ?? '—' }} WIB</span>
                            </div>
                        </div>

                        <a href="{{ route('fitting.show', $f->id) }}" class="inline-flex items-center gap-1 text-[11px] text-gold font-medium hover:underline group">
                            Lihat Detail <x-icon name="chevron-right" :size="12" class="group-hover:translate-x-0.5 transition-transform" />
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection