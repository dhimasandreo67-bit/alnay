@extends('admin.layouts.admin')

@section('title', 'Jadwal Sewa Busana')

@section('pageTitle', 'Jadwal Sewa Busana')
@section('pageSubtitle', 'Pantau sirkulasi peminjaman gaun pengantin & jas beskap, status verifikasi jaminan.')

@section('content')
<div class="space-y-6 transition-all duration-500 animate-fade-in">

    <style>
        .group-shine {
            position: relative;
            overflow: hidden;
        }

        .group-shine::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 20%;
            height: 200%;
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(30deg);
            transition: all 0.6s ease;
        }

        .group-shine:hover::after {
            left: 130%;
        }
    </style>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Kartu Ringkasan (Statistik) --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

        {{-- Card 1: Total Sedang Dipinjam (Amber) --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Total Sedang Dipinjam</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-amber-950 tabular-nums">{{ $totalDipinjam }}</span>
                <span class="text-sm font-bold text-amber-800/60">Gaun</span>
            </div>
            <p class="mt-2 text-xs font-medium text-amber-700/80">Aktif disewa saat ini</p>
        </div>

        {{-- Card 2: Jatuh Tempo / Terlambat (Rose) --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-br from-rose-50/80 via-white to-rose-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-rose-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-800/80">Jatuh Tempo / Terlambat</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-rose-950 tabular-nums">{{ $totalTerlambat }}</span>
                <span class="text-sm font-bold text-rose-800/60">Pesanan Kritis</span>
            </div>
            <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-rose-700 bg-rose-100/70 px-2.5 py-1 rounded-lg">
                <span class="h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Perlu follow-up WhatsApp</span>
            </div>
        </div>

        {{-- Card 3: Selesai Kembali Bulan Ini (Emerald) --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Selesai Kembali</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-emerald-950 tabular-nums">{{ $totalSelesai }}</span>
                <span class="text-sm font-bold text-emerald-800/60">Gaun Lolos QC</span>
            </div>
            <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Bulan ini</span>
            </div>
        </div>

        {{-- Card 4: Jaminan Tertahan (Sky) --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50/80 via-white to-sky-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-sky-800/80">Jaminan Tertahan</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-blue-500 text-white shadow-md shadow-sky-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold tracking-tight text-sky-950 tabular-nums">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</span>
            </div>
            <p class="mt-2 text-xs font-medium text-sky-700/80">{{ \App\Models\JaminanSewa::where('status_pengembalian_deposit', 'belum')->count() }} Jaminan aktif</p>
        </div>
    </div>

    {{-- Tab dan Filter --}}
    <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-2 overflow-x-auto" role="tablist" aria-label="Filter status sewa">

        {{-- Tab Semua Sewa --}}
        <a href="{{ route('admin.sewa.index', ['status' => 'semua']) }}"
           role="tab" aria-selected="{{ ($filter ?? 'semua') === 'semua' ? 'true' : 'false' }}"
           class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold whitespace-nowrap transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400
                  {{ ($filter ?? 'semua') === 'semua' ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-lg shadow-amber-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
            Semua Sewa
            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold tabular-nums {{ ($filter ?? 'semua') === 'semua' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ \App\Models\Sewa::count() }}
            </span>
        </a>

        {{-- Tab Dipinjam --}}
        <a href="{{ route('admin.sewa.index', ['status' => 'dipinjam']) }}"
           role="tab" aria-selected="{{ ($filter ?? '') === 'dipinjam' ? 'true' : 'false' }}"
           class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold whitespace-nowrap transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400
                  {{ ($filter ?? '') === 'dipinjam' ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-lg shadow-amber-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
            Dipinjam
            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold tabular-nums {{ ($filter ?? '') === 'dipinjam' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ $totalDipinjam }}
            </span>
        </a>

        {{-- Tab Terlambat --}}
        <a href="{{ route('admin.sewa.index', ['status' => 'terlambat']) }}"
           role="tab" aria-selected="{{ ($filter ?? '') === 'terlambat' ? 'true' : 'false' }}"
           class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold whitespace-nowrap transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400
                  {{ ($filter ?? '') === 'terlambat' ? 'border-rose-600 bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-rose-400 hover:text-rose-700' }}">
            Terlambat
            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold tabular-nums {{ ($filter ?? '') === 'terlambat' ? 'bg-white/25 text-white' : 'bg-rose-100 text-rose-600' }}">
                {{ $totalTerlambat }}
            </span>
        </a>

        {{-- Tab Dikembalikan --}}
        <a href="{{ route('admin.sewa.index', ['status' => 'dikembalikan']) }}"
           role="tab" aria-selected="{{ ($filter ?? '') === 'dikembalikan' ? 'true' : 'false' }}"
           class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold whitespace-nowrap transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400
                  {{ ($filter ?? '') === 'dikembalikan' ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-lg shadow-amber-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
            Dikembalikan
            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold tabular-nums {{ ($filter ?? '') === 'dikembalikan' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ $totalSelesai }}
            </span>
        </a>
    </div>

    {{-- Tabel Daftar Sewa --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Kode Sewa &amp; Busana Adat</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pelanggan &amp; Kontak</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Jadwal Pinjam &amp; Durasi</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Status Sewa</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Status Verifikasi Jaminan</th>
                        <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80">
                    @forelse($daftarSewa as $sewa)
                        @php
                            $namaPelanggan = $sewa->transaksi->user->name ?? 'Pelanggan';
                            $inisial = strtoupper(mb_substr(trim($namaPelanggan) ?: '?', 0, 1));
                        @endphp
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-400 mb-1">#SEWA-{{ $sewa->id }}</p>
                                <p class="font-bold text-gray-900">{{ $sewa->transaksi->detail->first()->produk->nama ?? 'Produk' }}</p>
                                <p class="text-xs text-gray-500">SKU: {{ $sewa->transaksi->detail->first()->produk->sku ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black border bg-amber-50 text-amber-700 border-amber-100">{{ $inisial }}</span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 truncate">{{ $namaPelanggan }}</p>
                                        <p class="text-xs text-gray-500">{{ $sewa->transaksi->user->no_hp ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">
                                    {{ \Carbon\Carbon::parse($sewa->tanggal_pinjam)->format('d M') }} -
                                    {{ \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->format('d M Y') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Durasi: {{ \Carbon\Carbon::parse($sewa->tanggal_pinjam)->diffInDays(\Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)) }} Hari
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                @if($sewa->status === 'aktif' && \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->isPast())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-rose-100 text-rose-700 border-rose-200">Terlambat</span>
                                @elseif($sewa->status === 'aktif')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-amber-100 text-amber-700 border-amber-200">Dipinjam</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-emerald-100 text-emerald-700 border-emerald-200">Dikembalikan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($sewa->jaminan)
                                    @if($sewa->jaminan->status_verifikasi === 'verified')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border bg-emerald-50 text-emerald-700 border-emerald-200">Terverifikasi</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border bg-gray-100 text-gray-500 border-gray-200">Pending</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.sewa.show', $sewa->id) }}"
                                   class="inline-flex items-center gap-1.5 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold px-4 py-2 rounded-xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all text-xs">
                                    DETAIL
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">Belum ada data sewa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection