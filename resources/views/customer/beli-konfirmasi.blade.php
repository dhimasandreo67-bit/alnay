@extends('layouts.public')

@section('title', 'Konfirmasi Pembayaran — ' . config('alnay.brand.nama'))

@section('content')

    <style>
        [x-cloak] { display: none !important; }
        @keyframes pd-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        .pd-up { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    </style>

    @php
        $lunas = in_array($transaksi->status_pembayaran, ['lunas', 'dp_dibayar']);
        $alamat = $transaksi->pengiriman->alamat_tujuan ?? null;
        $isSewa = $transaksi->jenis === 'sewa';
    @endphp

    <div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans">
        <div class="max-w-[1180px] mx-auto px-4 md:px-6">

            <nav class="pd-up text-[11px] text-ink/50 mb-6" style="--d: .05s;">
                <a href="{{ route('koleksi.index') }}" class="hover:text-gold transition-colors flex items-center gap-1">
                    <x-icon name="chevron-right" :size="12" class="rotate-180" />
                    Kembali ke Katalog
                </a>
            </nav>

            @if (session('success') && ! $lunas)
                <div class="pd-up p-3 mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-xs text-emerald-700" style="--d: .1s;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-6 lg:gap-8">

                {{-- KIRI — QRIS atau Status Lunas --}}
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-6 md:p-8" style="--d: .15s;">
                    <h1 class="font-display text-2xl md:text-3xl font-medium text-ink/85 mb-2">
                        {{ $lunas ? 'Pembayaran Berhasil' : 'Bayar dengan QRIS' }}
                    </h1>
                    <p class="text-[12px] text-ink/55 mb-6">
                        {{ $lunas
                            ? ($isSewa ? 'Terima kasih. Silakan datang ke studio untuk fitting sesuai jadwal.' : 'Terima kasih. Pesanan Anda akan segera kami siapkan dan kirim.')
                            : 'Pindai kode di bawah menggunakan aplikasi m-banking, dompet digital, atau QRIS scanner favorit Anda.' }}
                    </p>

                    @if($lunas)
                        {{-- Tampilan Jika Sudah Lunas --}}
                        <div class="text-center py-10">
                            <div class="w-20 h-20 mx-auto rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h3 class="font-display text-xl font-bold text-gray-900 mb-2">Terima Kasih!</h3>
                            <p class="text-sm text-gray-500 mb-6">Pembayaran Anda sudah kami terima.</p>
                            
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                @if($isSewa)
                                    <a href="{{ route('fitting.create') }}" class="px-6 py-3 rounded-xl bg-gold text-white text-sm font-bold hover:bg-gold/90 transition-all">Booking Fitting</a>
                                @endif
                                <a href="{{ route('akun.pesanan.index') }}" class="px-6 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm font-bold hover:bg-gray-50 transition-all">Lihat Riwayat</a>
                            </div>
                        </div>
                    @else
                        {{-- Tampilan QRIS (Jika Belum Lunas) --}}
                        @include('customer.partials.qris-panel', ['transaksi' => $transaksi, 'nominal' => (int) $total])
                    @endif
                </div>

                {{-- KANAN — Ringkasan --}}
                <div class="lg:sticky lg:top-24 h-fit">
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .2s;">
                        <h2 class="text-[10px] font-medium uppercase tracking-wider text-ink/50 mb-4">Ringkasan Pesanan</h2>

                        <div class="space-y-3 mb-4 max-h-60 overflow-y-auto pr-2">
                            @foreach($transaksi->detail as $item)
                                <div class="flex gap-3 items-center border-b border-line/30 pb-3">
                                    <div class="w-10 h-10 rounded-lg bg-cream-dim border border-line/40 flex items-center justify-center text-muted text-[8px] uppercase shrink-0">
                                        <x-icon name="sparkle" :size="16" class="text-ink/30" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[12px] font-medium text-ink/80 truncate">{{ $item->produk->nama ?? 'Produk' }}</p>
                                        <p class="text-[10px] text-ink/50">{{ $item->jumlah }}x Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                                    </div>
                                    <span class="text-[11px] font-bold text-ink/80 shrink-0">Rp{{ number_format($item->jumlah * $item->harga_satuan, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-2 text-[11px] mb-4">
                            <div class="flex justify-between">
                                <span class="text-ink/50">Subtotal</span>
                                <span class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($transaksi->total - ($transaksi->pengiriman->ongkir ?? 0), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink/50">Ongkos kirim</span>
                                <span class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($transaksi->pengiriman->ongkir ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center border-t border-line/40 pt-4 mb-4">
                            <span class="text-[11px] font-medium text-ink/80">Total</span>
                            <span class="font-sans text-xl font-semibold text-gold tabular-nums">Rp{{ number_format($transaksi->total, 0, ',', '.') }}</span>
                        </div>

                        @if ($alamat)
                            <div class="bg-cream/60 rounded-md px-3 py-2 mb-3">
                                <p class="text-[9px] text-ink/40 mb-0.5">Dikirim ke</p>
                                <p class="text-[10px] text-ink/75 leading-relaxed">{{ $alamat }}</p>
                            </div>
                        @endif

                        <div class="bg-cream/60 rounded-md px-3 py-2 flex justify-between items-center">
                            <span class="text-[9px] text-ink/40">ID Transaksi</span>
                            <span class="text-[10px] font-medium text-ink/80">{{ $transaksi->kode_transaksi }}</span>
                        </div>

                        <p class="text-[10px] text-ink/40 leading-relaxed text-center mt-4">
                            Status pembayaran diperbarui otomatis.<br>Halaman ini bisa ditutup setelah Anda membayar.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection