@extends('admin.layouts.admin')
@section('title', 'Detail Jaminan Sewa')

@section('content')
@php
    $sewa      = $jaminan->sewa;
    $transaksi = $sewa?->transaksi;
    $produk    = $transaksi?->detail?->first()?->produk;

    // ---------- Pembayaran: DP di awal sewa ----------
    $semuaBayar = $transaksi?->pembayaran ?? collect();
    $berhasil   = $semuaBayar->filter(fn ($p) => in_array(strtolower((string) $p->status_midtrans), ['success', 'settlement', 'capture'], true));
    $dpList     = $berhasil->where('jenis_bayar', 'dp');
    $dpDibayar  = (float) $dpList->sum('jumlah');
    $dpPertama  = $dpList->sortBy('tanggal_bayar')->first();
    $totalBayar = (float) $berhasil->sum('jumlah');
    $totalTrx   = (float) ($transaksi->total ?? 0);
    $sisaTagihan = max(0, $totalTrx - $totalBayar);
    $persenDp   = $totalTrx > 0 ? round($dpDibayar / $totalTrx * 100) : 0;

    // ---------- Jaminan / KTP ----------
    $isFisik  = $jaminan->metode_jaminan === 'ktp_tatap_muka';
    $verified = $jaminan->status_verifikasi === 'verified';
    $nik      = (string) $jaminan->nomor_ktp;
    $nikMask  = $nik !== '' ? substr($nik, 0, 4) . str_repeat('•', max(0, strlen($nik) - 8)) . substr($nik, -4) : '-';
    $tglBayar = fn ($v) => $v ? \Carbon\Carbon::parse($v)->translatedFormat('d M Y, H:i') . ' WIB' : '-';

    $terlambat = $sewa && $sewa->status === 'aktif' && $sewa->tanggal_kembali_rencana?->isPast();
@endphp

<div class="p-6 space-y-6 max-w-6xl mx-auto transition-all duration-500 animate-fade-in"
     x-data="{ zoom: false, lihatNik: false, nik: @js($nik !== '' ? $nik : '-'), masked: @js($nikMask) }"
     @keydown.escape.window="zoom = false">

    @if (session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm" role="alert">
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.jaminan.index') }}" class="hover:text-amber-600">Verifikasi Jaminan</a> /
                <span>Detail Jaminan</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 flex flex-wrap items-center gap-3">
                Detail Jaminan Sewa
                <span class="text-gray-400">#SEWA-{{ str_pad($jaminan->sewa_id, 3, '0', STR_PAD_LEFT) }}</span>
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if ($verified)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-emerald-100 text-emerald-700 border-emerald-200">Terverifikasi</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-amber-100 text-amber-700 border-amber-200">Menunggu Verifikasi</span>
                @endif
                @if ($isFisik)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-sky-100 text-sky-700 border-sky-200">Fisik (KTP)</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-violet-100 text-violet-700 border-violet-200">Digital (Upload)</span>
                @endif
                @if ($terlambat)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-rose-100 text-rose-700 border-rose-200">Sewa Terlambat</span>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.jaminan.index') }}"
               class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-semibold hover:bg-gray-50">&larr; Kembali</a>
            <a href="{{ route('admin.sewa.show', $jaminan->sewa_id) }}"
               class="inline-flex items-center gap-1.5 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold px-4 py-2 rounded-xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all text-sm">
                Verifikasi / Kelola Sewa
            </a>
        </div>
    </div>

    {{-- Ringkasan sewa --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Pelanggan</p>
            <h3 class="text-base font-bold text-gray-900 truncate">
                {{ $sewa?->nama_pelanggan ?? '-' }}
                @if ($sewa?->is_tamu)
                    <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-bold uppercase text-gray-500">Tamu</span>
                @endif
            </h3>
            <p class="text-xs text-gray-500 mt-1">{{ $sewa?->no_hp_pelanggan ?: '-' }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Busana</p>
            <h3 class="text-base font-bold text-gray-900 truncate">{{ $produk->nama ?? '-' }}</h3>
            <p class="text-xs text-gray-500 mt-1">#{{ $transaksi->kode_transaksi ?? '-' }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Jadwal Sewa</p>
            <h3 class="text-base font-bold text-gray-900">
                {{ $sewa?->tanggal_pinjam?->format('d M') ?? '-' }} - {{ $sewa?->tanggal_kembali_rencana?->format('d M Y') ?? '-' }}
            </h3>
            <p class="text-xs {{ $terlambat ? 'text-rose-600 font-bold' : 'text-gray-500' }} mt-1">
                {{ $terlambat ? 'Melewati tenggat' : ($sewa && $sewa->status === 'selesai' ? 'Sudah dikembalikan' : 'Sedang disewa') }}
            </p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Total Transaksi</p>
            <h3 class="text-base font-bold text-gray-900 tabular-nums">Rp {{ number_format($totalTrx, 0, ',', '.') }}</h3>
            <p class="text-xs text-gray-500 mt-1 uppercase">{{ str_replace('_', ' ', $transaksi->status_pembayaran ?? '-') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- ================= KTP ================= --}}
        <section class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Identitas KTP
                </h2>
                @if ($jaminan->foto_ktp)
                    <button type="button" @click="zoom = true" class="text-xs font-bold text-amber-600 hover:underline">Perbesar Foto</button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Foto KTP --}}
                <div class="w-full h-56 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-center overflow-hidden">
                    @if ($jaminan->foto_ktp)
                        <img src="{{ asset('storage/' . $jaminan->foto_ktp) }}" alt="Foto KTP" class="w-full h-full object-contain cursor-zoom-in" @click="zoom = true">
                    @else
                        <div class="text-center px-4 text-gray-400">
                            <svg class="mx-auto h-10 w-10 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 3h2" />
                            </svg>
                            <p class="text-sm font-semibold">{{ $isFisik ? 'KTP fisik disimpan di studio' : 'Belum ada foto KTP' }}</p>
                            @if ($isFisik)
                                <p class="text-[11px] mt-1">Simpan dalam amplop berkode transaksi (Slot B-04).</p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Data KTP --}}
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Nomor NIK</dt>
                        <dd class="mt-1 flex items-center gap-2">
                            <span class="font-mono font-bold text-gray-900 tracking-wide" x-text="lihatNik ? nik : masked"></span>
                            @if ($nik !== '')
                                <button type="button" @click="lihatNik = !lihatNik" class="text-[11px] font-bold text-amber-600 hover:underline" x-text="lihatNik ? 'Sembunyikan' : 'Tampilkan'"></button>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Nama Sesuai KTP</dt>
                        <dd class="mt-1 font-bold text-gray-900">{{ $jaminan->nama_ktp ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Metode Jaminan</dt>
                        <dd class="mt-1 font-semibold text-gray-800">{{ $isFisik ? 'KTP asli, tatap muka di studio' : 'Upload foto KTP (digital)' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Status Verifikasi</dt>
                        <dd class="mt-1 font-semibold {{ $verified ? 'text-emerald-700' : 'text-amber-700' }}">
                            @if ($verified)
                                Diverifikasi{{ $jaminan->verifikator ? ' oleh ' . $jaminan->verifikator->name : '' }}
                                <span class="block text-xs font-normal text-gray-500">{{ $tglBayar($jaminan->tanggal_verifikasi) }}</span>
                            @else
                                Belum diverifikasi
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        {{-- ================= DP & DEPOSIT ================= --}}
        <aside class="space-y-6">
            <section class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-4">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> DP di Awal Sewa
                </h2>

                @if ($dpDibayar > 0)
                    <p class="text-3xl font-extrabold tracking-tight text-gray-900 tabular-nums">Rp {{ number_format($dpDibayar, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $persenDp }}% dari total transaksi</p>

                    <div class="mt-3 h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-blue-500" style="width: {{ min(100, $persenDp) }}%"></div>
                    </div>

                    <dl class="mt-4 space-y-2 text-xs">
                        <div class="flex justify-between gap-3"><dt class="text-gray-400">Metode</dt><dd class="font-semibold text-gray-800 uppercase">{{ $dpPertama->metode ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-400">Dibayar</dt><dd class="font-semibold text-gray-800 text-right">{{ $tglBayar($dpPertama->tanggal_bayar ?? null) }}</dd></div>
                    </dl>
                @else
                    <p class="text-sm font-semibold text-gray-600">Tidak ada DP.</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $totalBayar >= $totalTrx && $totalTrx > 0 ? 'Transaksi ini dibayar lunas di awal.' : 'Belum ada pembayaran DP yang berhasil.' }}
                    </p>
                @endif

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-xs">
                    <div class="flex justify-between"><span class="text-gray-400">Total dibayar</span><span class="font-bold text-emerald-700 tabular-nums">Rp {{ number_format($totalBayar, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Sisa tagihan</span><span class="font-bold {{ $sisaTagihan > 0 ? 'text-rose-600' : 'text-gray-700' }} tabular-nums">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</span></div>
                </div>
            </section>

            <section class="bg-sky-50/60 rounded-3xl border border-sky-100 p-6">
                <h2 class="text-sm font-bold text-sky-900 flex items-center gap-2 mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> Deposit Jaminan
                </h2>
                <p class="text-2xl font-extrabold tracking-tight text-sky-950 tabular-nums">Rp {{ number_format((float) $jaminan->jumlah_deposit, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs font-semibold {{ $jaminan->status_pengembalian_deposit === 'dikembalikan' ? 'text-emerald-700' : 'text-sky-700' }}">
                    {{ $jaminan->status_pengembalian_deposit === 'dikembalikan' ? 'Sudah dikembalikan' : 'Masih tertahan' }}
                </p>
            </section>
        </aside>
    </div>

    {{-- ================= RIWAYAT PEMBAYARAN ================= --}}
    <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Riwayat Pembayaran Transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3 text-[11px] font-bold text-gray-400 uppercase">Jenis</th>
                        <th class="px-6 py-3 text-[11px] font-bold text-gray-400 uppercase">Metode</th>
                        <th class="px-6 py-3 text-[11px] font-bold text-gray-400 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-right text-[11px] font-bold text-gray-400 uppercase">Jumlah</th>
                        <th class="px-6 py-3 text-center text-[11px] font-bold text-gray-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80">
                    @forelse ($semuaBayar->sortBy('tanggal_bayar') as $p)
                        @php $ok = in_array(strtolower((string) $p->status_midtrans), ['success', 'settlement', 'capture'], true); @endphp
                        <tr>
                            <td class="px-6 py-3 font-bold text-gray-900 uppercase">{{ $p->jenis_bayar }}</td>
                            <td class="px-6 py-3 text-gray-600 uppercase">{{ $p->metode ?? '-' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $tglBayar($p->tanggal_bayar) }}</td>
                            <td class="px-6 py-3 text-right font-bold tabular-nums text-gray-900">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $ok ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                    {{ $p->status_midtrans ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada riwayat pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Zoom foto KTP --}}
    @if ($jaminan->foto_ktp)
        <div x-show="zoom" x-cloak x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
             @click="zoom = false" role="dialog" aria-label="Foto KTP diperbesar">
            <img src="{{ asset('storage/' . $jaminan->foto_ktp) }}" alt="Foto KTP" class="max-h-full max-w-full rounded-xl shadow-2xl" @click.stop>
            <button type="button" @click="zoom = false" class="absolute right-6 top-6 h-9 w-9 rounded-full bg-white/90 text-gray-700 text-xl font-bold hover:bg-white" aria-label="Tutup">&times;</button>
        </div>
    @endif
</div>
@endsection