@extends('layouts.akun')

@section('title', 'Pesanan Saya — ' . config('alnay.brand.nama'))

@section('content')

<style>
    @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
    @keyframes pd-pop  { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }

    .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .pd-pop   { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }

    .pd-reveal { transition: opacity .7s ease, transform .7s cubic-bezier(.22, .8, .3, 1); transition-delay: var(--d, 0s); }
    .pd-js .pd-reveal:not(.is-in) { opacity: 0; transform: translateY(20px); }

    @media (prefers-reduced-motion: reduce) {
        .pd-up, .pd-pop { animation: none !important; }
        .pd-js .pd-reveal:not(.is-in) { opacity: 1; transform: none; }
        .pd-reveal { transition: none !important; }
    }
</style>

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4">

        <div class="pd-up flex items-center gap-3 mb-6" style="--d: .05s;">
            <a href="{{ route('akun.dashboard') }}" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 hover:-translate-x-0.5 transition-all duration-300">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <h1 class="font-display text-2xl font-medium text-ink/85">Pesanan Saya</h1>
        </div>

        @if (session('info'))
            <div class="pd-up p-3 mb-4 rounded-md bg-blue-50 border border-blue-200 text-[11px] text-blue-700" style="--d: .1s;">
                {{ session('info') }}
            </div>
        @endif

        @if ($pesananList->isEmpty())
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-12 text-center" style="--d: .1s;">
                <div class="pd-pop w-16 h-16 rounded-full bg-cream flex items-center justify-center mx-auto mb-4">
                    <x-icon name="cart" :size="28" class="text-gold/60" />
                </div>
                <h2 class="font-display text-lg font-medium text-ink/80 mb-2">Belum ada pesanan</h2>
                <p class="text-[11px] text-ink/50 mb-6">Anda belum melakukan transaksi sewa atau beli.</p>
                <a href="{{ route('koleksi.index') }}" class="pd-shine inline-block px-6 py-2.5 rounded-full bg-gold text-white text-[11px] font-medium hover:bg-gold/90 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                    Jelajahi Koleksi
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($pesananList as $index => $trx)
                    <a href="{{ route('akun.pesanan.detail', $trx->id) }}" 
                       class="pd-reveal block bg-panel border border-line/40 rounded-xl p-5 hover:border-gold/50 hover:-translate-y-0.5 hover:shadow-md transition-all duration-300 group"
                       style="--d: {{ $index * 0.08 }}s;">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded bg-gold/10 text-gold text-[9px] font-semibold uppercase">{{ $trx->jenis }}</span>
                                    <span class="text-[10px] text-ink/40">{{ $trx->kode_transaksi }}</span>
                                </div>
                                <p class="text-[13px] font-medium text-ink/80">{{ $trx->detail->first()->produk->nama ?? '-' }}</p>
                                <p class="text-[10px] text-ink/50">{{ $trx->tanggal->format('d M Y') }}</p>
                            </div>
                            @php
                                $statusClass = match ($trx->status_pembayaran) {
                                    'lunas' => 'bg-emerald-50 text-emerald-700',
                                    'dp' => 'bg-amber-50 text-amber-700',
                                    default => 'bg-cream text-ink/60',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-[9px] font-semibold uppercase {{ $statusClass }}">
                                {{ ucfirst($trx->status_pembayaran) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-line/30">
                            <span class="text-[10px] text-ink/50">Total</span>
                            <div class="flex items-center gap-2">
                                <span class="font-sans font-medium text-[13px] text-ink/85 tabular-nums">
                                    Rp{{ number_format($trx->total, 0, ',', '.') }}
                                </span>
                                
                                @php
                                    $detailPertama = $trx->detail->first();
                                    $sudahRating = $detailPertama ? \App\Models\Rating::where('detail_transaksi_id', $detailPertama->id)->exists() : true;
                                @endphp

                                @if($trx->status_pembayaran === 'lunas' && !$sudahRating)
                                    <a href="{{ route('rating.create', $detailPertama->id) }}" 
                                       class="px-3 py-1 bg-gold text-white text-[10px] font-bold rounded-lg hover:bg-amber-600 transition-colors"
                                       onclick="event.stopPropagation();">
                                        ⭐ Beri Rating
                                    </a>
                                @endif

                                <x-icon name="chevron-right" :size="14" class="text-gold group-hover:translate-x-0.5 transition-transform" />
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection