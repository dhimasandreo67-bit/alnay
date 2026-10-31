@extends('admin.layouts.admin')
@section('title', 'Pengiriman')

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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Pengiriman</h1>
                <p class="text-xs text-gray-500 mt-1">Kelola pengiriman busana ke pelanggan ALNAY.ID.</p>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

            {{-- Card 1: Menunggu Dikirim (Amber) --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Menunggu Dikirim</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-amber-950 tabular-nums">{{ $totalMenunggu }}</span>
                    <span class="text-sm font-bold text-amber-800/60">Pesanan</span>
                </div>
                <p class="mt-2 text-xs font-medium text-amber-700/80">Perlu segera diproses</p>
            </div>

            {{-- Card 2: Sedang Dikirim (Sky) --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50/80 via-white to-sky-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-800/80">Sedang Dikirim</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-blue-500 text-white shadow-md shadow-sky-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-sky-950 tabular-nums">{{ $totalDikirim }}</span>
                    <span class="text-sm font-bold text-sky-800/60">Pesanan</span>
                </div>
                <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-sky-700 bg-sky-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-sky-500 animate-pulse"></span>
                    <span>Dalam perjalanan</span>
                </div>
            </div>

            {{-- Card 3: Diterima (Emerald) --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Diterima</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-emerald-950 tabular-nums">{{ $totalDiterima }}</span>
                    <span class="text-sm font-bold text-emerald-800/60">Pesanan</span>
                </div>
                <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Sudah sampai</span>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <form method="GET" action="{{ route('admin.pengiriman.index') }}"
            class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode transaksi atau nama..."
                    class="w-full pl-10 pr-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" aria-label="Status pengiriman"
                    class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer">
                    <option value="semua" {{ $filter === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="menunggu" {{ $filter === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="dikirim" {{ $filter === 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                    <option value="diterima" {{ $filter === 'diterima' ? 'selected' : '' }}>Diterima</option>
                </select>
                <button type="submit"
                    class="bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-amber-500/20 active:scale-95 transition-all duration-200">Cari</button>
                @if (request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.pengiriman.index') }}"
                        class="text-xs font-bold text-gray-500 hover:text-amber-600 transition-colors">Reset</a>
                @endif
            </div>
        </form>

        {{-- Tabel Pengiriman --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Kode Transaksi</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pelanggan</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Alamat Tujuan</th>
                            <th class="px-6 py-4 text-center text-[11px] font-bold text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/80">
                        @forelse($pengirimanList as $p)
                            @php
                                $namaPelanggan = $p->transaksi->user->name ?? '-';
                                $inisial = strtoupper(mb_substr(trim($namaPelanggan) ?: '?', 0, 1));
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900">{{ $p->transaksi->kode_transaksi ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $p->transaksi->detail->first()->produk->nama ?? 'Produk' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black border bg-amber-50 text-amber-700 border-amber-100">{{ $inisial }}</span>
                                        <div class="min-w-0">
                                            <p class="font-bold text-gray-900 truncate">{{ $namaPelanggan }}</p>
                                            <p class="text-xs text-gray-500">{{ $p->transaksi->user->no_hp ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-600">
                                    {{ $p->kota_tujuan ?? '-' }}, {{ $p->provinsi_tujuan ?? '-' }}
                                    <br><span class="text-gray-400">{{ Str::limit($p->alamat_tujuan, 40) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($p->status_kirim === 'diterima')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-emerald-100 text-emerald-700 border-emerald-200">Diterima</span>
                                    @elseif($p->status_kirim === 'dikirim')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-sky-100 text-sky-700 border-sky-200">Dikirim</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-amber-100 text-amber-700 border-amber-200">Menunggu</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($p->status_kirim === 'menunggu')
                                        {{-- Form Kirim Barang --}}
                                        <form action="{{ route('admin.pengiriman.update-status', $p->id) }}" method="POST" class="flex flex-col gap-1.5 items-end">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status_kirim" value="dikirim">
                                            <div class="flex gap-1.5">
                                                <input type="text" name="kurir" placeholder="Kurir (JNE, dll)" required
                                                    class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg w-24 bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                                <input type="text" name="no_resi" placeholder="No. Resi" required
                                                    class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg w-28 bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                            </div>
                                            <button type="submit"
                                                class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-700 hover:to-sky-600 shadow-lg shadow-sky-500/20 active:scale-95 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/>
                                                </svg>
                                                Kirim
                                            </button>
                                        </form>

                                    @elseif($p->status_kirim === 'dikirim')
                                        {{-- Info Kurir & Tombol Tandai Diterima --}}
                                        <div class="text-right">
                                            <p class="text-xs font-bold text-gray-800">{{ $p->kurir ?? '-' }} &middot; {{ $p->no_resi ?? '-' }}</p>
                                            <form action="{{ route('admin.pengiriman.update-status', $p->id) }}" method="POST" class="inline mt-1.5">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status_kirim" value="diterima">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 shadow-lg shadow-emerald-500/20 active:scale-95 transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Tandai Diterima
                                                </button>
                                            </form>
                                        </div>

                                    @else
                                        {{-- Status Diterima & Tombol Batalkan Konfirmasi --}}
                                        <div class="text-right">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-emerald-100 text-emerald-700 border-emerald-200 mb-1.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Diterima
                                            </span>
                                            <form action="{{ route('admin.pengiriman.batalkan-terima', $p->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Batalkan konfirmasi penerimaan? Status akan kembali ke Dikirim.')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 hover:text-rose-700 hover:underline active:scale-95 transition-all">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Batalkan Konfirmasi
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">Belum ada data pengiriman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $pengirimanList->links() }}</div>

    </div>
@endsection