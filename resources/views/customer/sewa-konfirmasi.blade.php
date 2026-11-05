@extends('layouts.public')

@section('title', 'Konfirmasi Pembayaran — ' . config('alnay.brand.nama'))

@section('content')

    <style>
        [x-cloak] {
            display: none !important;
        }

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

        .pd-up {
            animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both;
            animation-delay: var(--d, 0s);
        }
    </style>

    @php
        $dp = $dp ?? 0;
        $deposit = $deposit ?? 0;
        $produk = $transaksi->detail->first()->produk ?? null;

        // Jumlah yang ditagih = pembayaran DP yang tersimpan (sudah mencakup DP semua item + deposit)
        $dibayarSekarang = $dp > 0 ? $dp : ($produk->dp ?? 0) + $deposit;
        $dpMurni = max(0, $dibayarSekarang - $deposit);
        $totalSewa = max(0, $total - $deposit);   // total transaksi sudah ditambah deposit
        $lunasDp = in_array($transaksi->status_pembayaran, ['dp_dibayar', 'lunas'], true);

        // Ukuran, warna & tanggal tiap item dibaca dari awal catatan: "[Ukuran: M · Warna: X · 12/03/2026–14/03/2026]"
        $spesifikasi = [];
        if (preg_match('/^(?:\s*\[[^\]]*\])+/u', (string) $transaksi->catatan, $awal)) {
            preg_match_all('/\[([^\]]*)\]/u', $awal[0], $mm);
            $spesifikasi = $mm[1];
        }
    @endphp

    <div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans">
        <div class="max-w-[1180px] mx-auto px-4 md:px-6">

            <nav class="pd-up text-[11px] text-ink/50 mb-6" style="--d: .05s;">
                <a href="{{ route('koleksi.index') }}" class="hover:text-gold transition-colors flex items-center gap-1">
                    <x-icon name="chevron-right" :size="12" class="rotate-180" />
                    Kembali ke Katalog
                </a>
            </nav>

            @if (session('success'))
                <div class="pd-up p-3 mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-xs text-emerald-700"
                    style="--d: .1s;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-6 lg:gap-8">

                {{-- KIRI — QRIS --}}
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-6 md:p-8" style="--d: .15s;">
                    <h1 class="font-display text-2xl md:text-3xl font-medium text-ink/85 mb-2">
                        {{ $lunasDp ? 'Pembayaran DP Diterima' : 'Bayar dengan QRIS' }}
                    </h1>
                    <p class="text-[12px] text-ink/55 mb-6">
                        {{ $lunasDp
                            ? 'Terima kasih. Tim kami akan menghubungi Anda untuk langkah berikutnya.'
                            : 'Pindai kode di bawah menggunakan aplikasi m-banking, dompet digital, atau QRIS scanner favorit Anda.' }}
                    </p>

                    @include('customer.partials.qris-panel', [
                        'transaksi' => $transaksi,
                        'nominal' => (int) $dibayarSekarang,
                    ])
                </div>

                {{-- KANAN — Ringkasan --}}
                <div class="lg:sticky lg:top-24 h-fit">
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .2s;">
                        <h2 class="text-[10px] font-medium uppercase tracking-wider text-ink/50 mb-4">Ringkasan Pembayaran
                        </h2>

                        <div class="space-y-3 border-b border-line/30 pb-4 mb-4">
                            @foreach ($transaksi->detail as $detail)
                                <div class="flex gap-3 items-center">
                                    <div
                                        class="w-12 h-12 rounded-lg bg-cream-dim border border-line/40 flex items-center justify-center text-muted text-[8px] uppercase shrink-0">
                                        Foto</div>
                                    <div class="min-w-0">
                                        <p class="text-[12px] font-medium text-ink/80">{{ $detail->produk->nama ?? 'Produk' }}</p>
                                        @if (! empty($spesifikasi[$loop->index]))
                                            <p class="text-[10px] text-ink/50">{{ $spesifikasi[$loop->index] }}</p>
                                        @elseif ($loop->first && $transaksi->sewa)
                                            <p class="text-[10px] text-ink/50">
                                                {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_pinjam)->format('d M') }} –
                                                {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_kembali_rencana)->format('d M Y') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-2 text-[11px] mb-4">
                            <div class="flex justify-between">
                                <span class="text-ink/50">Total Biaya Sewa</span>
                                <span
                                    class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($totalSewa, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink/50">DP (Uang Muka)</span>
                                <span
                                    class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($dpMurni, 0, ',', '.') }}</span>
                            </div>
                            @if ($deposit > 0)
                                <div class="flex justify-between">
                                    <span class="text-ink/50">Deposit jaminan KTP</span>
                                    <span
                                        class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($deposit, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-between items-center border-t border-line/40 pt-4 mb-4">
                            <div>
                                <span class="text-[11px] font-medium text-ink/80 block">Total Dibayar Sekarang</span>
                                <span class="text-[9px] text-ink/40">(DP + Deposit Jaminan)</span>
                            </div>
                            <span
                                class="font-sans text-xl font-semibold text-gold tabular-nums">Rp{{ number_format($dibayarSekarang, 0, ',', '.') }}</span>
                        </div>

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