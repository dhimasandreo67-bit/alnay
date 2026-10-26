@extends('layouts.akun')

@section('title', 'Ajukan Refund — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-2xl mx-auto px-4">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('akun.dashboard') }}" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <h1 class="font-display text-2xl font-semibold text-ink/90">Ajukan Refund</h1>
        </div>

        <div class="bg-panel border border-line/40 rounded-xl p-6">
            <div class="p-3 rounded-md bg-amber-50 border border-amber-200/50 text-[11px] text-amber-800 mb-5">
                <p class="font-semibold mb-1">Perhatian:</p>
                <p class="leading-relaxed">Refund hanya berlaku untuk transaksi <strong>beli</strong>. Untuk sewa, DP yang sudah dibayar tidak dapat dikembalikan (hangus jika batal sebelum barang diambil).</p>
            </div>

            <div class="text-center py-8">
                <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center mx-auto mb-4">
                    <x-icon name="refresh-cw" :size="28" class="text-gold/60" />
                </div>
                <h2 class="font-display text-lg font-semibold text-ink/80 mb-2">Form Refund Segera Hadir</h2>
                <p class="text-[11px] text-ink/50 mb-6 max-w-sm mx-auto">Form pengajuan refund akan tersedia setelah integrasi database transaksi selesai.</p>
                <a href="{{ route('akun.dashboard') }}" class="inline-block px-6 py-2.5 rounded-full bg-gold text-white text-[11px] font-semibold hover:bg-gold/90 transition-colors">
                    Kembali ke Akun
                </a>
            </div>
        </div>
    </div>
</div>

@endsection