@extends('layouts.akun')

@section('title', 'Pesanan Saya — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('akun.dashboard') }}" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <h1 class="font-display text-2xl font-semibold text-ink/90">Pesanan Saya</h1>
        </div>

        @if ($pesananList->isEmpty())
            <div class="bg-panel border border-line/40 rounded-xl p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center mx-auto mb-4">
                    <x-icon name="cart" :size="28" class="text-gold/60" />
                </div>
                <h2 class="font-display text-lg font-semibold text-ink/80 mb-2">Belum ada pesanan</h2>
                <p class="text-[11px] text-ink/50 mb-6">Anda belum melakukan transaksi sewa atau beli.</p>
                <a href="{{ route('koleksi.index') }}" class="inline-block px-6 py-2.5 rounded-full bg-gold text-white text-[11px] font-semibold hover:bg-gold/90 transition-colors">
                    Jelajahi Koleksi
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($pesananList as $trx)
                    <a href="{{ route('akun.pesanan.detail', $trx->id) }}" class="block bg-panel border border-line/40 rounded-xl p-5 hover:border-gold/50 transition-all group">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded bg-gold/10 text-gold text-[9px] font-bold uppercase">{{ $trx->jenis }}</span>
                                    <span class="text-[10px] text-ink/40">{{ $trx->kode_transaksi }}</span>
                                </div>
                                <p class="text-[13px] font-semibold text-ink/80">{{ $trx->detail->first()->produk->nama ?? '-' }}</p>
                                <p class="text-[10px] text-ink/50">{{ $trx->tanggal->format('d M Y') }}</p>
                            </div>
                            @php
                                $statusClass = match ($trx->status_pembayaran) {
                                    'lunas' => 'bg-emerald-50 text-emerald-700',
                                    'dp' => 'bg-amber-50 text-amber-700',
                                    default => 'bg-cream text-ink/60',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-[9px] font-bold uppercase {{ $statusClass }}">
                                {{ ucfirst($trx->status_pembayaran) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-line/30">
                            <span class="text-[10px] text-ink/50">Total</span>
                            <div class="flex items-center gap-2">
                                <span class="font-display font-semibold text-[13px] text-ink/80">
                                    Rp{{ number_format($trx->total, 0, ',', '.') }}
                                </span>
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