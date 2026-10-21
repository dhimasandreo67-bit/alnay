@extends('layouts.akun')

@section('title', 'Detail Pesanan — ' . config('alnay.brand.nama'))

@section('content')

<style>
    @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
    @keyframes pd-pop  { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }

    .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .pd-pop   { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }

    .pd-shine { position: relative; overflow: hidden; }
    .pd-shine::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%);
        transform: translateX(-120%); transition: transform .8s ease; pointer-events: none;
    }
    .pd-shine:hover::after { transform: translateX(120%); }

    @media (prefers-reduced-motion: reduce) {
        .pd-up, .pd-pop { animation: none !important; }
        .pd-shine::after { transition: none !important; }
    }
</style>

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-3xl mx-auto px-4">

        {{-- Breadcrumb --}}
        <nav class="pd-up text-[11px] text-ink/50 mb-4" style="--d: .05s;">
            <a href="{{ route('akun.dashboard') }}" class="hover:text-gold">Akun</a>
            <span class="mx-1">/</span>
            <a href="{{ route('akun.pesanan.index') }}" class="hover:text-gold">Pesanan</a>
            <span class="mx-1">/</span>
            <span class="text-ink/70">{{ $transaksi->kode_transaksi }}</span>
        </nav>

        {{-- Header --}}
        <div class="pd-up flex items-center gap-3 mb-6" style="--d: .1s;">
            <a href="{{ route('akun.pesanan.index') }}" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 hover:-translate-x-0.5 transition-all duration-300">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <div>
                <h1 class="font-display text-xl md:text-2xl font-medium text-ink/85">Detail Pesanan</h1>
                <p class="text-[11px] text-ink/50">{{ $transaksi->kode_transaksi }}</p>
            </div>
        </div>

        <div class="space-y-4">

            {{-- Status --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .15s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-ink/40 mb-1">Status Pesanan</p>
                        @php
                            $statusClass = match ($transaksi->status_pembayaran) {
                                'lunas' => 'bg-emerald-50 text-emerald-700',
                                'dp' => 'bg-amber-50 text-amber-700',
                                default => 'bg-cream text-ink/60',
                            };
                            $statusLabel = match ($transaksi->status_pembayaran) {
                                'lunas' => 'Lunas',
                                'dp' => 'DP Terbayar',
                                default => 'Menunggu Pembayaran',
                            };
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold uppercase {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-wider text-ink/40 mb-1">Total</p>
                        <p class="font-sans text-xl font-semibold text-ink/85 tabular-nums">
                            Rp{{ number_format($transaksi->total, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Detail Produk --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .2s;">
                <h2 class="font-display text-base font-medium text-ink/85 mb-3">Produk</h2>

                @foreach ($transaksi->detail as $detail)
                    <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-line/30' : '' }}">
                        <div class="w-14 h-14 rounded-lg bg-cream-dim border border-line/40 flex items-center justify-center text-muted text-[8px] uppercase shrink-0">
                            @if ($detail->produk->foto_produk)
                                <img src="{{ asset('storage/' . $detail->produk->foto_produk) }}" alt="{{ $detail->produk->nama }}" class="w-full h-full object-cover rounded-lg">
                            @else
                                Foto
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-[13px] font-medium text-ink/80">{{ $detail->produk->nama }}</p>
                            <p class="text-[10px] text-ink/50">Jumlah: {{ $detail->jumlah }} × Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}</p>
                        </div>
                        <p class="text-[12px] font-medium text-ink/80 tabular-nums">
                            Rp{{ number_format($detail->harga_satuan * $detail->jumlah, 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Detail Sewa (kalau sewa) --}}
            @if ($transaksi->jenis === 'sewa' && $transaksi->sewa)
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .25s;">
                    <h2 class="font-display text-base font-medium text-ink/85 mb-3">Detail Sewa</h2>

                    <div class="space-y-2 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-ink/50">Tanggal Ambil</span>
                            <span class="font-medium text-ink/80">
                                {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_pinjam)->translatedFormat('l, d M Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Tanggal Kembali</span>
                            <span class="font-medium text-ink/80">
                                {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_kembali_rencana)->translatedFormat('l, d M Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Status Sewa</span>
                            <span class="font-medium text-ink/80 capitalize">{{ $transaksi->sewa->status }}</span>
                        </div>
                    </div>

                    @if ($transaksi->sewa->jaminan)
                        <div class="mt-3 pt-3 border-t border-line/30">
                            <p class="text-[10px] uppercase tracking-wider text-ink/40 mb-2">Jaminan</p>
                            <p class="text-[11px] text-ink/60">
                                Metode: <strong class="text-ink/80">{{ str_replace('_', ' ', $transaksi->sewa->jaminan->metode_jaminan) }}</strong>
                                @if ($transaksi->sewa->jaminan->jumlah_deposit > 0)
                                    · Deposit: <strong class="text-ink/80 tabular-nums">Rp{{ number_format($transaksi->sewa->jaminan->jumlah_deposit, 0, ',', '.') }}</strong>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Detail Pengiriman --}}
            @if ($transaksi->pengiriman)
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .3s;">
                    <h2 class="font-display text-base font-medium text-ink/85 mb-3">Pengiriman</h2>

                    <div class="space-y-2 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-ink/50">Alamat</span>
                            <span class="font-medium text-ink/80 text-right max-w-[200px]">{{ $transaksi->pengiriman->alamat_tujuan }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Status</span>
                            <span class="font-medium text-ink/80 capitalize">{{ $transaksi->pengiriman->status_kirim }}</span>
                        </div>
                        @if ($transaksi->pengiriman->no_resi)
                            <div class="flex justify-between">
                                <span class="text-ink/50">No. Resi</span>
                                <span class="font-medium text-ink/80">{{ $transaksi->pengiriman->no_resi }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Detail Pembayaran --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .35s;">
                <h2 class="font-display text-base font-medium text-ink/85 mb-3">Pembayaran</h2>

                <div class="space-y-2 text-[11px]">
                    @foreach ($transaksi->pembayaran as $bayar)
                        <div class="flex justify-between">
                            <span class="text-ink/50 capitalize">{{ $bayar->jenis_bayar }} ({{ $bayar->metode }})</span>
                            <span class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($bayar->jumlah, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="pd-up grid grid-cols-1 sm:grid-cols-2 gap-3" style="--d: .4s;">
                <a href="{{ route('akun.pesanan.index') }}" class="w-full h-[42px] rounded-lg border border-line/50 bg-white text-ink/65 text-[11px] font-medium hover:bg-cream/50 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                    Kembali ke Pesanan
                </a>
                @if ($transaksi->jenis === 'beli' && $transaksi->status_pembayaran === 'lunas')
                    <a href="{{ route('akun.refund.index') }}" class="pd-shine w-full h-[42px] rounded-lg bg-gold text-white text-[11px] font-semibold hover:bg-gold/90 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center">
                        Ajukan Refund
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection