@extends('layouts.akun')

@section('title', 'Refund Saya — ' . config('alnay.brand.nama'))

@section('content')
@php
    $chip = fn ($st) => match (true) {
        $st === 'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        in_array($st, ['ditolak', 'dibatalkan'], true) => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-amber-50 text-amber-700 border-amber-200',
    };

    $aktif = $refundList->whereNotIn('status', ['selesai', 'ditolak', 'dibatalkan'])->count();
@endphp

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-2xl mx-auto px-4">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('akun.dashboard') }}#refund" aria-label="Kembali"
               class="w-9 h-9 shrink-0 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <div>
                <h1 class="font-display text-2xl font-medium text-ink/90 leading-tight">Refund Saya</h1>
                <p class="text-[11px] text-ink/50">Ajukan pengembalian barang dan pantau prosesnya.</p>
            </div>
        </div>

        @foreach (['success' => 'bg-emerald-50 border-emerald-200 text-emerald-700', 'error' => 'bg-rose-50 border-rose-200 text-rose-700'] as $tipe => $kelas)
            @if (session($tipe))
                <div class="mb-4 px-4 py-3 rounded-xl border text-sm {{ $kelas }}">{{ session($tipe) }}</div>
            @endif
        @endforeach

        {{-- Ringkasan --}}
        <div class="grid grid-cols-3 gap-3 mb-6">
            @foreach ([
                ['Bisa direfund', $bisaDirefund->count()],
                ['Sedang diproses', $aktif],
                ['Total pengajuan', $refundList->count()],
            ] as [$label, $angka])
                <div class="bg-panel border border-line/40 rounded-xl p-3 text-center">
                    <p class="text-xl font-display font-medium text-ink/90 tabular-nums">{{ $angka }}</p>
                    <p class="text-[10px] text-ink/50 mt-0.5">{{ $label }}</p>
                </div>
            @endforeach
        </div>

        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-xl p-4 mb-6 text-xs text-amber-900 leading-relaxed">
            <p class="font-bold mb-1">Ketentuan singkat</p>
            Refund hanya untuk <strong>pembelian</strong>, diajukan maksimal <strong>{{ \App\Models\Refund::BATAS_HARI }} hari</strong>
            setelah barang diterima, dengan foto bukti. Barang perlu dikirim kembali, lalu dana ditransfer setelah barang dicek.
            Untuk <strong>sewa</strong>, DP tidak dapat dikembalikan.
        </div>

        {{-- Pesanan yang bisa diajukan refund --}}
        <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Pesanan yang bisa direfund</h2>
        <div class="space-y-3 mb-8">
            @forelse ($bisaDirefund as $t)
                <div class="bg-panel border border-line/40 rounded-xl p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <p class="text-[11px] text-ink/50 font-mono">{{ $t->kode_transaksi }}</p>
                            <p class="text-[11px] text-ink/50">
                                {{ $t->detail->count() }} produk · {{ $t->detail->sum('jumlah') }} item · Total
                                <span class="font-semibold text-ink/80 tabular-nums">Rp{{ number_format($t->total, 0, ',', '.') }}</span>
                            </p>
                        </div>
                        <a href="{{ route('akun.refund.create', $t->id) }}"
                           class="shrink-0 px-4 py-2 rounded-lg border border-rose-200 text-rose-600 text-[12px] font-semibold hover:bg-rose-50 transition-colors">
                            Ajukan Refund
                        </a>
                    </div>
                    <div class="space-y-3 pt-3 border-t border-line/30">
                        @foreach ($t->detail->take(2) as $d)
                            @include('customer.akun._refund-item', ['d' => $d])
                        @endforeach

                        @if ($t->detail->count() > 2)
                            <details class="group/lain">
                                <summary class="cursor-pointer list-none text-[11px] font-semibold text-gold hover:underline">
                                    <span class="group-open/lain:hidden">Lihat {{ $t->detail->count() - 2 }} produk lainnya</span>
                                    <span class="hidden group-open/lain:inline">Sembunyikan</span>
                                </summary>
                                <div class="space-y-3 mt-3">
                                    @foreach ($t->detail->slice(2) as $d)
                                        @include('customer.akun._refund-item', ['d' => $d])
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-[12px] text-ink/55 bg-panel border border-line/40 rounded-xl p-4 leading-relaxed">
                    <p class="font-semibold text-ink/70 mb-1">Belum ada pesanan yang memenuhi syarat refund.</p>
                    Pesanan pembelian yang sudah diterima dan masih dalam batas {{ \App\Models\Refund::BATAS_HARI }} hari akan muncul di sini.
                </div>
            @endforelse
        </div>

        {{-- Riwayat pengajuan --}}
        <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Pengajuan saya</h2>
        <div class="space-y-3 mb-8">
            @forelse ($refundList as $r)
                @php $items = $r->transaksi->detail ?? collect(); @endphp
                <a href="{{ route('akun.refund.show', $r->id) }}"
                   class="block bg-panel border border-line/40 rounded-xl p-4 hover:border-gold/50 hover:shadow-sm transition-all">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] text-ink/50 font-mono">{{ $r->transaksi->kode_transaksi ?? '-' }}</p>
                            <p class="text-[13px] font-semibold text-ink/85 truncate">{{ $items->first()->produk->nama ?? '-' }}
                                @if ($items->count() > 1)<span class="font-normal text-ink/45">+{{ $items->count() - 1 }} produk lainnya</span>@endif
                            </p>
                            <p class="text-[11px] text-ink/55 mt-0.5 tabular-nums">Rp{{ number_format($r->jumlah, 0, ',', '.') }}</p>
                            <p class="text-[10px] text-ink/45 mt-1">Diajukan {{ $r->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase shrink-0 {{ $chip($r->status) }}">{{ $r->label_status }}</span>
                    </div>
                </a>
            @empty
                <p class="text-[12px] text-ink/50 bg-panel border border-line/40 rounded-xl p-4">Belum ada pengajuan refund.</p>
            @endforelse
        </div>

        {{-- Panduan lengkap --}}
        @include('customer.akun._refund-panduan')
    </div>
</div>
@endsection