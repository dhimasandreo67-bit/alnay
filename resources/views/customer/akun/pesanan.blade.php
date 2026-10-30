@extends('layouts.akun')

@section('title', 'Pesanan Saya — ' . config('alnay.brand.nama'))



@section('content')

    <style>
        @keyframes pd-up {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes pd-pop {
            0% {
                opacity: 0;
                transform: scale(.5);
            }

            70% {
                transform: scale(1.15);
            }

            100% {
                opacity: 1;
                transform: none;
            }
        }

        .pd-up {
            animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both;
            animation-delay: var(--d, 0s);
        }

        .pd-pop {
            animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both;
            animation-delay: var(--d, 0s);
        }

        .pd-reveal {
            transition: opacity .7s ease, transform .7s cubic-bezier(.22, .8, .3, 1);
            transition-delay: var(--d, 0s);
        }

        .pd-js .pd-reveal:not(.is-in) {
            opacity: 0;
            transform: translateY(20px);
        }

        @media (prefers-reduced-motion: reduce) {

            .pd-up,
            .pd-pop {
                animation: none !important;
            }

            .pd-js .pd-reveal:not(.is-in) {
                opacity: 1;
                transform: none;
            }

            .pd-reveal {
                transition: none !important;
            }
        }

        
    </style>

    <div class="bg-cream min-h-screen py-8 md:py-12">
        <div class="max-w-4xl mx-auto px-4">

            <div class="pd-up flex items-center gap-3 mb-6" style="--d: .05s;">
                <a href="{{ route('akun.dashboard') }}"
                    class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 hover:-translate-x-0.5 transition-all duration-300">
                    <x-icon name="chevron-right" :size="14" class="rotate-180" />
                </a>
                <h1 class="font-display text-2xl font-medium text-ink/85">Pesanan Saya</h1>
            </div>

            {{-- Toggle Slide: Sewa / Beli (Minimalis Underline) --}}
            <div class="pd-up mb-8 border-b border-gray-200" style="--d: .08s;">
                <div class="flex gap-8 -mb-[1px]">
                    <a href="{{ route('akun.pesanan.index', ['jenis' => 'sewa']) }}" 
                    class="pb-3 text-sm font-semibold transition-all border-b-2 
                    {{ ($filter ?? 'sewa') === 'sewa' ? 'border-[#c99a32] text-[#c99a32]' : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                        Pesanan Sewa
                    </a>
                    <a href="{{ route('akun.pesanan.index', ['jenis' => 'beli']) }}" 
                    class="pb-3 text-sm font-semibold transition-all border-b-2 
                    {{ ($filter ?? '') === 'beli' ? 'border-[#c99a32] text-[#c99a32]' : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                        Pesanan Beli
                    </a>
                </div>
            </div>
            @if (session('info'))
                <div class="pd-up p-3 mb-4 rounded-md bg-blue-50 border border-blue-200 text-[11px] text-blue-700"
                    style="--d: .1s;">
                    {{ session('info') }}
                </div>
            @endif

            @if ($pesananList->isEmpty())
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-12 text-center" style="--d: .1s;">
                    <div class="pd-pop w-16 h-16 rounded-full bg-cream flex items-center justify-center mx-auto mb-4">
                        <x-icon name="cart" :size="28" class="text-gold/60" />
                    </div>
                    <h2 class="font-display text-lg font-medium text-ink/80 mb-2">
                        Belum ada pesanan {{ $filter === 'sewa' ? 'sewa' : 'beli' }}
                    </h2>
                    <p class="text-[11px] text-ink/50 mb-6">
                        Anda belum melakukan transaksi {{ $filter === 'sewa' ? 'sewa' : 'beli' }}.
                    </p>
                    <a href="{{ route('koleksi.index') }}"
                        class="pd-shine inline-block px-6 py-2.5 rounded-full bg-gold text-white text-[11px] font-medium hover:bg-gold/90 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                        Jelajahi Koleksi
                    </a>
                </div>
            @else
                <div class="space-y-3">
                @foreach ($pesananList as $index => $trx)
                    <a href="{{ route('akun.pesanan.detail', $trx->id) }}" 
                    class="pd-up block bg-panel border border-line/40 rounded-xl p-5 hover:border-gold/50 hover:shadow-sm transition-all duration-300 group"
                    style="--d: {{ $index * 0.08 }}s;">
                        
                        {{-- Header Card --}}
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    {{-- Badge Jenis (Sewa/Beli) lebih minimalis --}}
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-ink/40">{{ $trx->jenis }}</span>
                                    <span class="w-1 h-1 rounded-full bg-line/50"></span>
                                    <span class="text-[10px] text-ink/40 font-medium">{{ $trx->kode_transaksi }}</span>
                                </div>
                                <p class="text-[13px] font-semibold text-ink/85">{{ $trx->detail->first()->produk->nama ?? '-' }}</p>
                                <p class="text-[10px] text-ink/50 mt-1">{{ $trx->tanggal->format('d M Y') }}</p>
                            </div>
                            
                            {{-- Status (Hanya teks berwarna, tanpa kotak) --}}
                            @php
                                $statusClass = match ($trx->status_pembayaran) {
                                    'lunas' => 'text-emerald-600',
                                    'dp', 'dp_dibayar' => 'text-amber-600',
                                    default => 'text-ink/50',
                                };
                            @endphp
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                {{ str_replace('_', ' ', $trx->status_pembayaran) }}
                            </span>
                        </div>

                        {{-- Footer Card --}}
                        <div class="flex items-center justify-between pt-3 border-t border-dashed border-line/30">
                            <span class="text-[10px] text-ink/40 uppercase tracking-wider font-medium">Total</span>
                            <div class="flex items-center gap-3">
                                <span class="font-sans font-bold text-[13px] text-ink/85 tabular-nums">
                                    Rp{{ number_format($trx->total, 0, ',', '.') }}
                                </span>

                                @php
                                    $detailPertama = $trx->detail->first();
                                    $sudahRating = $detailPertama
                                        ? \App\Models\Rating::where('detail_transaksi_id', $detailPertama->id)->exists()
                                        : true;
                                @endphp

                                {{-- Tombol Beri Rating Minimalis --}}
                                @if ($trx->status_pembayaran === 'lunas' && !$sudahRating)
                                    <span onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('rating.create', $detailPertama->id) }}';"
                                        class="inline-flex items-center gap-1 text-gold text-[10px] font-semibold hover:text-amber-600 transition-colors whitespace-nowrap cursor-pointer mr-2">
                                        ★ Beri Rating
                                    </span>
                                @endif

                                {{-- Panah Chevron Minimalis --}}
                                <x-icon name="chevron-right" :size="14" class="text-ink/20 group-hover:text-gold group-hover:translate-x-0.5 transition-all duration-300 shrink-0" />
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

@endsection
