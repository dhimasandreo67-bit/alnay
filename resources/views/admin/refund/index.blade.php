@extends('admin.layouts.admin')
@section('title', 'Refund & Return')

@section('content')
    <div class="p-6 space-y-6 max-w-7xl mx-auto transition-all duration-500 animate-fade-in">

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

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Refund & Return</h1>
            <p class="text-xs text-gray-500 mt-1">Kelola pengajuan pengembalian dana atau barang dari pelanggan.</p>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

            {{-- Card 1: Menunggu (Amber) --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Menunggu</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-amber-950 tabular-nums">{{ $totalPending }}</span>
                    <span class="text-sm font-bold text-amber-800/60">Pengajuan</span>
                </div>
                <p class="mt-2 text-xs font-medium text-amber-700/80">Perlu ditindaklanjuti</p>
            </div>

            {{-- Card 2: Disetujui (Emerald) --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Disetujui</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-emerald-950 tabular-nums">{{ $totalDisetujui }}</span>
                    <span class="text-sm font-bold text-emerald-800/60">Pengajuan</span>
                </div>
                <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Sudah disetujui</span>
                </div>
            </div>

            {{-- Card 3: Ditolak (Rose) --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-br from-rose-50/80 via-white to-rose-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-rose-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-800/80">Ditolak</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-rose-950 tabular-nums">{{ $totalDitolak }}</span>
                    <span class="text-sm font-bold text-rose-800/60">Pengajuan</span>
                </div>
                <p class="mt-2 text-xs font-medium text-rose-700/80">Pengajuan ditolak</p>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-2 overflow-x-auto" role="tablist" aria-label="Filter status refund">
            <a href="{{ route('admin.refund.index', ['status' => 'semua']) }}"
               role="tab" aria-selected="{{ $filter === 'semua' ? 'true' : 'false' }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold whitespace-nowrap transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400
                      {{ $filter === 'semua' ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-lg shadow-amber-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
                Semua
            </a>
            <a href="{{ route('admin.refund.index', ['status' => 'pending']) }}"
               role="tab" aria-selected="{{ $filter === 'pending' ? 'true' : 'false' }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold whitespace-nowrap transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400
                      {{ $filter === 'pending' ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-lg shadow-amber-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
                Pending
            </a>
            <a href="{{ route('admin.refund.index', ['status' => 'disetujui']) }}"
               role="tab" aria-selected="{{ $filter === 'disetujui' ? 'true' : 'false' }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold whitespace-nowrap transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400
                      {{ $filter === 'disetujui' ? 'border-emerald-600 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-emerald-400 hover:text-emerald-700' }}">
                Disetujui
            </a>
            <a href="{{ route('admin.refund.index', ['status' => 'ditolak']) }}"
               role="tab" aria-selected="{{ $filter === 'ditolak' ? 'true' : 'false' }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold whitespace-nowrap transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400
                      {{ $filter === 'ditolak' ? 'border-rose-600 bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-rose-400 hover:text-rose-700' }}">
                Ditolak
            </a>
        </div>

        {{-- Tabel --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Kode Transaksi</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pelanggan</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Alasan</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">No. Resi / Info</th>
                            <th class="px-6 py-4 text-center text-[11px] font-bold text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/80">
                        @forelse($refundList as $r)
                            @php
                                $namaPelanggan = $r->transaksi->user->name ?? '-';
                                $inisial = strtoupper(mb_substr(trim($namaPelanggan) ?: '?', 0, 1));
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900">{{ $r->transaksi->kode_transaksi ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $r->transaksi->detail->first()->produk->nama ?? '-' }}</p>
                                    <span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide border
                                        {{ $r->transaksi->jenis === 'sewa' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-sky-100 text-sky-700 border-sky-200' }}">
                                        {{ $r->transaksi->jenis }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black border bg-amber-50 text-amber-700 border-amber-100">{{ $inisial }}</span>
                                        <div class="min-w-0">
                                            <p class="font-bold text-gray-900 truncate">{{ $namaPelanggan }}</p>
                                            <p class="text-xs text-gray-500">{{ $r->transaksi->user->no_hp ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-600 max-w-xs truncate">{{ $r->alasan ?? '-' }}</td>
                                <td class="px-6 py-4 text-xs text-gray-600">
                                    @if ($r->transaksi->jenis === 'beli')
                                        {{ $r->no_resi_penjemputan ?? '-' }}
                                    @else
                                        <span class="italic text-gray-400">Via Gojek/Manual</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($r->status === 'disetujui')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-emerald-100 text-emerald-700 border-emerald-200">Disetujui</span>
                                    @elseif($r->status === 'ditolak')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-rose-100 text-rose-700 border-rose-200">Ditolak</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-amber-100 text-amber-700 border-amber-200">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($r->status === 'pending')
                                        {{-- Form Setujui --}}
                                        <form action="{{ route('admin.refund.update-status', $r->id) }}" method="POST"
                                            class="flex flex-col gap-1.5 items-end">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="disetujui">

                                            {{-- Hanya tampilkan input resi untuk Beli --}}
                                            @if ($r->transaksi->jenis === 'beli')
                                                <input type="text" name="no_resi_penjemputan"
                                                    placeholder="No. Resi Penjemputan" required
                                                    class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg w-40 bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                            @endif

                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 shadow-lg shadow-emerald-500/20 active:scale-95 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Setujui
                                            </button>
                                        </form>
                                        {{-- Form Tolak --}}
                                        <form action="{{ route('admin.refund.update-status', $r->id) }}" method="POST"
                                            class="inline mt-1">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="ditolak">
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 hover:text-rose-700 hover:underline active:scale-95 transition-all">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Tolak
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 italic">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">Belum ada pengajuan refund.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $refundList->links() }}</div>

    </div>
@endsection