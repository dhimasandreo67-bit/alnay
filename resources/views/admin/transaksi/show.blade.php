@extends('admin.layouts.admin')
@section('title', 'Detail Transaksi')

@section('content')
@php
    $rp = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $label = fn($s) => ucwords(str_replace('_', ' ', (string) $s));

    // Warna status berdasarkan kata kunci (aman untuk nilai status apa pun)
    $nada = function ($s): string {
        $s = strtolower((string) $s);
        return match (true) {
            str_contains($s, 'lunas') || str_contains($s, 'settle') || str_contains($s, 'success')
                || str_contains($s, 'berhasil') || str_contains($s, 'capture') || str_contains($s, 'selesai') => 'emerald',
            str_contains($s, 'batal') || str_contains($s, 'gagal') || str_contains($s, 'expire')
                || str_contains($s, 'cancel') || str_contains($s, 'deny') || str_contains($s, 'tolak')
                || str_contains($s, 'failure') => 'rose',
            str_contains($s, 'aktif') || str_contains($s, 'pinjam') || str_contains($s, 'berlangsung')
                || str_contains($s, 'proses') || str_contains($s, 'kirim') => 'sky',
            default => 'amber',
        };
    };

    $chipTerang = [
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'rose'    => 'bg-rose-50 text-rose-700 ring-rose-200',
        'sky'     => 'bg-sky-50 text-sky-700 ring-sky-200',
        'amber'   => 'bg-amber-50 text-amber-700 ring-amber-200',
    ];
    $chipGelap = [
        'emerald' => 'bg-emerald-400/15 text-emerald-300 ring-emerald-400/30',
        'rose'    => 'bg-rose-400/15 text-rose-300 ring-rose-400/30',
        'sky'     => 'bg-sky-400/15 text-sky-300 ring-sky-400/30',
        'amber'   => 'bg-amber-400/15 text-amber-300 ring-amber-400/30',
    ];
    $titik = [
        'emerald' => 'bg-emerald-500', 'rose' => 'bg-rose-500', 'sky' => 'bg-sky-500', 'amber' => 'bg-amber-500',
    ];

    $user = $transaksi->user;
    $namaUser = $user?->name ?? '-';
    $inisial = strtoupper(mb_substr(trim($namaUser) ?: '?', 0, 1));

    // Link WhatsApp (08xxx -> 628xxx)
    $hp = preg_replace('/\D+/', '', (string) ($user?->no_hp ?? ''));
    if ($hp !== '' && str_starts_with($hp, '0')) {
        $hp = '62' . substr($hp, 1);
    }
    $waUrl = $hp !== '' ? 'https://wa.me/' . $hp : null;

    $nadaTransaksi = $nada($transaksi->status_pembayaran);
    $sewa = $transaksi->sewa;
    $tglPinjam = $sewa ? \Carbon\Carbon::parse($sewa->tanggal_pinjam)->locale('id') : null;
    $tglKembali = $sewa ? \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->locale('id') : null;

    // Tindakan admin (aturan sama seperti sebelumnya)
    $bisaLunasi = ! in_array(strtolower((string) $transaksi->status_pembayaran), ['lunas', 'settlement', 'success'], true);
    $bisaSelesai = $sewa && strtolower((string) $sewa->status) === 'aktif';
@endphp

<style>
    .dt-rise { animation: dtRise .55s cubic-bezier(.22, 1, .36, 1) both; animation-delay: var(--d, 0s); }
    @keyframes dtRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .dt-rise { animation: none; } }
</style>

<div class="p-4 sm:p-6 space-y-6 max-w-5xl mx-auto">

    {{-- Tombol Kembali --}}
    <a href="{{ route('admin.transaksi.index') }}"
       class="group inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-amber-600 transition-colors">
        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali ke Daftar Transaksi
    </a>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div role="status" class="dt-rise flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div role="alert" class="dt-rise flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ================= HERO ================= --}}
    <section class="dt-rise relative overflow-hidden rounded-3xl bg-[#0A1526] p-6 sm:p-7 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-10 -top-12 h-44 w-44 rounded-full bg-amber-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-16 -left-8 h-40 w-40 rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[.2em] text-amber-400">Kode Transaksi</p>
                <h1 class="mt-1.5 break-all font-mono text-xl sm:text-2xl font-black tracking-tight">{{ $transaksi->kode_transaksi }}</h1>
                <p class="mt-1.5 text-xs text-gray-400">
                    Dibuat pada {{ $transaksi->created_at?->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}
                </p>
            </div>

            <div class="flex items-end justify-between gap-4 sm:flex-col sm:items-end">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide ring-1 {{ $chipGelap[$nadaTransaksi] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $titik[$nadaTransaksi] }}"></span>
                    {{ $label($transaksi->status_pembayaran) }}
                </span>
                <div class="text-right">
                    <p class="text-[10px] uppercase tracking-wider text-gray-400">Total Tagihan</p>
                    <p class="text-2xl font-black tabular-nums text-amber-400">{{ $rp($transaksi->total) }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= TINDAKAN ADMIN ================= --}}
    @if($bisaLunasi || $bisaSelesai)
        <section class="dt-rise flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between" style="--d:.04s">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Tindakan Admin</p>
                <p class="mt-0.5 text-xs text-gray-500">Perubahan status langsung tersimpan setelah kamu konfirmasi.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if($bisaLunasi)
                    <form action="{{ route('admin.transaksi.lunasi', $transaksi->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menandai transaksi ini sebagai LUNAS?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-emerald-700 active:scale-[.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Tandai Lunas
                        </button>
                    </form>
                @endif

                @if($bisaSelesai)
                    <form action="{{ route('admin.transaksi.selesai-sewa', $transaksi->id) }}" method="POST" onsubmit="return confirm('Yakin baju sudah dikembalikan dan sewa selesai?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-amber-700 active:scale-[.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Selesaikan Sewa
                        </button>
                    </form>
                @endif
            </div>
        </section>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- ================= KOLOM KIRI ================= --}}
        <div class="md:col-span-2 space-y-6">

            {{-- Detail Item --}}
            <section class="dt-rise rounded-3xl border border-gray-100 bg-white p-5 sm:p-6 shadow-sm" style="--d:.08s">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Detail Item</p>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold text-gray-500">
                        {{ $transaksi->detail->count() }} item
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($transaksi->detail as $d)
                        <div class="group flex items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50/50 p-3 transition hover:border-amber-200 hover:bg-white hover:shadow-sm">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-white">
                                @if($d->produk && $d->produk->foto_produk)
                                    <img src="{{ asset('storage/' . $d->produk->foto_produk) }}" alt="{{ $d->produk->nama }}"
                                         class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-gray-900">{{ $d->produk->nama ?? 'Produk Terhapus' }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ $d->jumlah }} × {{ $rp($d->harga_satuan) }}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-[10px] uppercase tracking-wider text-gray-400">Subtotal</p>
                                <p class="text-sm font-bold tabular-nums text-gray-900">{{ $rp($d->jumlah * $d->harga_satuan) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-gray-200 py-8 text-center text-xs italic text-gray-400">
                            Belum ada item pada transaksi ini.
                        </p>
                    @endforelse
                </div>
            </section>

            {{-- Informasi Penyewa --}}
            <section class="dt-rise rounded-3xl border border-gray-100 bg-white p-5 sm:p-6 shadow-sm" style="--d:.16s">
                <p class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Informasi Penyewa</p>

                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-600 to-amber-400 text-lg font-black text-white shadow-sm">
                            {{ $inisial }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-gray-900">{{ $namaUser }}</p>
                            <p class="mt-0.5 text-xs text-gray-400">Penyewa</p>
                        </div>
                    </div>

                    @if($waUrl)
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100 hover:shadow-sm">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.04 2a9.9 9.9 0 00-8.46 15.04L2 22l5.1-1.55A9.9 9.9 0 1012.04 2zm5.8 14.1c-.25.7-1.45 1.34-2 1.4-.52.06-1.18.09-1.9-.12a17 17 0 01-1.72-.64c-3.02-1.3-5-4.34-5.15-4.54-.15-.2-1.23-1.64-1.23-3.12 0-1.49.78-2.22 1.06-2.52.28-.3.6-.38.8-.38l.58.01c.19 0 .44-.07.68.52.25.6.86 2.08.93 2.23.08.15.12.33.02.52-.1.2-.15.32-.3.5l-.45.52c-.15.15-.3.31-.13.6.17.3.77 1.26 1.65 2.04 1.13 1 2.09 1.31 2.39 1.46.3.15.47.12.65-.07.17-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.98.3.15.5.22.57.35.08.12.08.72-.17 1.42z"/>
                            </svg>
                            Hubungi via WhatsApp
                        </a>
                    @endif
                </div>

                <dl class="mt-5 grid grid-cols-1 gap-3 border-t border-gray-100 pt-5 sm:grid-cols-2">
                    <div class="rounded-xl bg-gray-50 px-4 py-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Email</dt>
                        <dd class="mt-1 break-all text-sm font-medium text-gray-800">{{ $user?->email ?? '-' }}</dd>
                    </div>
                    <div class="rounded-xl bg-gray-50 px-4 py-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">No. WhatsApp</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800">{{ $user?->no_hp ?? '-' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- ================= KOLOM KANAN ================= --}}
        <div class="space-y-6">

            {{-- Jadwal Penyewaan --}}
            @if($sewa)
                @php $nadaSewa = $nada($sewa->status); @endphp
                <section class="dt-rise rounded-3xl border border-gray-100 bg-white p-5 sm:p-6 shadow-sm" style="--d:.12s">
                    <p class="mb-5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Jadwal Penyewaan</p>

                    <ol class="relative space-y-6 pl-8">
                        <span class="absolute bottom-2 left-[11px] top-2 w-0.5 rounded bg-gradient-to-b from-amber-400 to-gray-200"></span>

                        <li class="relative">
                            <span class="absolute -left-8 top-0.5 flex h-6 w-6 items-center justify-center rounded-full border-2 border-amber-500 bg-white shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            </span>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Tanggal Ambil</p>
                            <p class="mt-0.5 text-sm font-bold text-gray-900">{{ $tglPinjam->translatedFormat('d F Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $tglPinjam->translatedFormat('l') }}</p>
                        </li>

                        <li class="relative">
                            <span class="absolute -left-8 top-0.5 flex h-6 w-6 items-center justify-center rounded-full border-2 border-gray-300 bg-white shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                            </span>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Tanggal Kembali</p>
                            <p class="mt-0.5 text-sm font-bold text-gray-900">{{ $tglKembali->translatedFormat('d F Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $tglKembali->translatedFormat('l') }}</p>
                        </li>
                    </ol>

                    <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4">
                        <span class="text-xs text-gray-500">Status Sewa</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide ring-1 {{ $chipTerang[$nadaSewa] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $titik[$nadaSewa] }}"></span>
                            {{ $label($sewa->status) }}
                        </span>
                    </div>
                </section>
            @endif

            {{-- Informasi Pembayaran --}}
            <section class="dt-rise rounded-3xl border border-gray-100 bg-white p-5 sm:p-6 shadow-sm" style="--d:.2s">
                <p class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Informasi Pembayaran</p>

                <div class="rounded-2xl bg-gradient-to-br from-amber-50 to-white px-4 py-4 ring-1 ring-amber-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700/70">Total Tagihan</p>
                    <p class="mt-1 text-2xl font-black tabular-nums text-gray-900">{{ $rp($transaksi->total) }}</p>
                </div>

                <p class="mb-2 mt-5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Riwayat Transaksi</p>

                <div class="space-y-2">
                    @forelse($transaksi->pembayaran as $p)
                        @php $nadaBayar = $nada($p->status_midtrans); @endphp
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 transition hover:border-amber-200 hover:bg-white">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase text-gray-800">{{ $label($p->jenis_bayar) }}</p>
                                    <p class="mt-0.5 text-[11px] text-gray-500">{{ $label($p->metode) }}</p>
                                </div>
                                <p class="shrink-0 text-xs font-bold tabular-nums text-gray-900">{{ $rp($p->jumlah) }}</p>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-2">
                                <span class="text-[10px] text-gray-400">
                                    {{ $p->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ring-1 {{ $chipTerang[$nadaBayar] }}">
                                    <span class="h-1 w-1 rounded-full {{ $titik[$nadaBayar] }}"></span>
                                    {{ $label($p->status_midtrans) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-gray-200 py-5 text-center text-xs italic text-gray-400">
                            Belum ada riwayat pembayaran.
                        </p>
                    @endforelse
                </div>
            </section>

        </div>
    </div>
</div>
@endsection