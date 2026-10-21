@extends('layouts.akun')

@section('title', 'Detail Pesanan — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-3xl mx-auto px-4">

        {{-- Breadcrumb --}}
        <nav class="text-[11px] text-ink/50 mb-4">
            <a href="{{ route('akun.dashboard') }}" class="hover:text-gold">Akun</a>
            <span class="mx-1">/</span>
            <a href="{{ route('akun.pesanan.index') }}" class="hover:text-gold">Pesanan</a>
            <span class="mx-1">/</span>
            <span class="text-ink/70">{{ $transaksi->kode_transaksi }}</span>
        </nav>

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('akun.pesanan.index') }}" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <div>
                <h1 class="font-display text-xl md:text-2xl font-semibold text-ink/90">Detail Pesanan</h1>
                <p class="text-[11px] text-ink/50">{{ $transaksi->kode_transaksi }}</p>
            </div>
        </div>

        <div class="space-y-4">

            {{-- Status --}}
            <div class="bg-panel border border-line/40 rounded-xl p-5">
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
                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-wider text-ink/40 mb-1">Total</p>
                        <p class="font-display text-xl font-semibold text-ink/80">
                            Rp{{ number_format($transaksi->total, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Detail Produk --}}
            <div class="bg-panel border border-line/40 rounded-xl p-5">
                <h2 class="font-display text-base font-semibold text-ink/80 mb-3">Produk</h2>

                @foreach ($transaksi->detail as $detail)
                    <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-line/30' : '' }}">
                        <div class="w-14 h-14 rounded-lg bg-cream-dim border border-line/40 flex items-center justify-center text-muted text-[8px] uppercase shrink-0">
                            Foto
                        </div>
                        <div class="flex-1">
                            <p class="text-[13px] font-semibold text-ink/80">{{ $detail->produk->nama }}</p>
                            <p class="text-[10px] text-ink/50">Jumlah: {{ $detail->jumlah }} × Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}</p>
                        </div>
                        <p class="text-[12px] font-semibold text-ink/80">
                            Rp{{ number_format($detail->harga_satuan * $detail->jumlah, 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Detail Sewa (kalau sewa) --}}
            @if ($transaksi->jenis === 'sewa' && $transaksi->sewa)
                <div class="bg-panel border border-line/40 rounded-xl p-5">
                    <h2 class="font-display text-base font-semibold text-ink/80 mb-3">Detail Sewa</h2>

                    <div class="space-y-2 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-ink/50">Tanggal Ambil</span>
                            <span class="font-semibold text-ink/80">
                                {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_pinjam)->translatedFormat('l, d M Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Tanggal Kembali</span>
                            <span class="font-semibold text-ink/80">
                                {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_kembali_rencana)->translatedFormat('l, d M Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Status Sewa</span>
                            <span class="font-semibold text-ink/80 capitalize">{{ $transaksi->sewa->status }}</span>
                        </div>
                    </div>

                    @if ($transaksi->sewa->jaminan)
                        <div class="mt-3 pt-3 border-t border-line/30">
                            <p class="text-[10px] uppercase tracking-wider text-ink/40 mb-2">Jaminan</p>
                            <p class="text-[11px] text-ink/60">
                                Metode: <strong>{{ str_replace('_', ' ', $transaksi->sewa->jaminan->metode_jaminan) }}</strong>
                                @if ($transaksi->sewa->jaminan->jumlah_deposit > 0)
                                    · Deposit: <strong>Rp{{ number_format($transaksi->sewa->jaminan->jumlah_deposit, 0, ',', '.') }}</strong>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Detail Pengiriman --}}
            @if ($transaksi->pengiriman)
                <div class="bg-panel border border-line/40 rounded-xl p-5">
                    <h2 class="font-display text-base font-semibold text-ink/80 mb-3">Pengiriman</h2>

                    <div class="space-y-2 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-ink/50">Alamat</span>
                            <span class="font-semibold text-ink/80 text-right max-w-[200px]">{{ $transaksi->pengiriman->alamat_tujuan }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Status</span>
                            <span class="font-semibold text-ink/80 capitalize">{{ $transaksi->pengiriman->status_kirim }}</span>
                        </div>
                        @if ($transaksi->pengiriman->no_resi)
                            <div class="flex justify-between">
                                <span class="text-ink/50">No. Resi</span>
                                <span class="font-semibold text-ink/80">{{ $transaksi->pengiriman->no_resi }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Detail Pembayaran --}}
            <div class="bg-panel border border-line/40 rounded-xl p-5">
                <h2 class="font-display text-base font-semibold text-ink/80 mb-3">Pembayaran</h2>

                <div class="space-y-2 text-[11px]">
                    @foreach ($transaksi->pembayaran as $bayar)
                        <div class="flex justify-between">
                            <span class="text-ink/50 capitalize">{{ $bayar->jenis_bayar }} ({{ $bayar->metode }})</span>
                            <span class="font-semibold text-ink/80">Rp{{ number_format($bayar->jumlah, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('akun.pesanan.index') }}" class="w-full h-[42px] rounded-lg border border-line/50 bg-white text-ink/70 text-[11px] font-medium hover:bg-cream/50 transition-all flex items-center justify-center">
                    Kembali ke Pesanan
                </a>
                @if ($transaksi->jenis === 'beli' && $transaksi->status_pembayaran === 'lunas')
                    <a href="{{ route('akun.refund.index') }}" class="w-full h-[42px] rounded-lg bg-gold/95 text-white text-[11px] font-semibold hover:bg-gold transition-all flex items-center justify-center">
                        Ajukan Refund
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection