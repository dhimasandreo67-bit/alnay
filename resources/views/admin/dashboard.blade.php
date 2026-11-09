@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', \Illuminate\Support\Carbon::now()->translatedFormat('l, j F Y') . ' · Ringkasan performa dan operasional atelier hari ini')

@section('content')

    @php
        // Fallback AMAN (nol/kosong), bukan data palsu. Data asli dikirim DashboardController.
        $stats ??= [
            'transaksi_hari_ini' => 0,
            'transaksi_trend' => '0%',
            'sewa_aktif' => 0,
            'sewa_jatuh_tempo' => 0,
            'sewa_terlambat' => 0,
            'refund_pending' => 0,
            'refund_saldo' => 0,
            'pendapatan_bulan_ini' => 0,
        ];
        $chart ??= [];
        $maxChart = max(1, $chart ? max(array_column($chart, 'value')) : 1);

        // Format rupiah ringkas & adaptif: rb (ribu) / jt (juta) / M (milyar).
        // Dipakai supaya nominal kecil tidak tampil "0,0jt".
        $formatRupiahRingkas = function ($nilai) {
            $nilai = (float) $nilai;

            if ($nilai >= 1_000_000_000) {
                return number_format($nilai / 1_000_000_000, 1, ',', '.') . ' M';
            }
            if ($nilai >= 1_000_000) {
                return number_format($nilai / 1_000_000, 1, ',', '.') . ' jt';
            }
            if ($nilai >= 1_000) {
                return number_format($nilai / 1_000, 1, ',', '.') . ' rb';
            }

            return number_format($nilai, 0, ',', '.');
        };

        // Transaksi terbaru: data NYATA dari tabel transaksi, diambil dengan relasi & accessor yang sama
        // seperti halaman Kelola Transaksi. Kalau controller sudah mengirim $transaksiTerbaru, itu yang dipakai.
        $transaksiTerbaru ??= \App\Models\Transaksi::with(['detail.produk', 'sewa', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $actionQueue ??= [];
        $antreanTotal ??= collect($actionQueue)->sum('count');

        $stockAlerts ??= collect();
        $stockAlertTotal ??= $stockAlerts->count();
    @endphp

    {{-- Custom Keyframe Styles untuk Animasi Halus --}}
    <style>
        @keyframes float-subtle {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-4px); }
        }
        @keyframes shine {
            100% { left: 125%; }
        }
        .animate-float { animation: float-subtle 4s ease-in-out infinite; }
        .group-shine { position: relative; overflow: hidden; }
        .group-shine::after {
            content: '';
            position: absolute;
            top: -50%; left: -60%;
            width: 20%; height: 200%;
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(30deg);
            transition: all 0.6s ease;
        }
        .group-shine:hover::after {
            left: 130%;
        }
    </style>

    {{-- SECTION 1: Stat Cards Kustom dengan Gradasi Luxe & Glow --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        
        {{-- Card 1: Transaksi Hari Ini --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Transaksi Hari Ini</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-amber-950">{{ $stats['transaksi_hari_ini'] }}</span>
                @php $trenNaik = ! str_starts_with($stats['transaksi_trend'], '-'); @endphp
                <span class="inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-bold {{ $trenNaik ? 'bg-emerald-100/80 text-emerald-700' : 'bg-rose-100/80 text-rose-700' }}">
                    <svg class="h-3 w-3 {{ $trenNaik ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    {{ $stats['transaksi_trend'] }}
                </span>
            </div>
            <p class="mt-1 text-xs text-amber-800/60">dibanding kemarin</p>
        </div>

        {{-- Card 2: Sewa Aktif --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50/80 via-white to-sky-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-sky-800/80">Sewa Aktif</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-blue-500 text-white shadow-md shadow-sky-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold tracking-tight text-sky-950">{{ $stats['sewa_aktif'] }}</span>
            </div>
            <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-100/70 px-2.5 py-1 rounded-lg">
                <span class="h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                <span>{{ $stats['sewa_jatuh_tempo'] }} gaun jatuh tempo besok</span>
            </div>
            @if (($stats['sewa_terlambat'] ?? 0) > 0)
                <div class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-rose-100/70 px-2.5 py-1 text-xs font-medium text-rose-700">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    <span>{{ $stats['sewa_terlambat'] }} sewa terlambat dikembalikan</span>
                </div>
            @endif
        </div>

        {{-- Card 3: Refund Pending --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-br from-rose-50/80 via-white to-rose-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-rose-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-800/80">Refund Pending</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold tracking-tight text-rose-950">{{ $stats['refund_pending'] }}</span>
            </div>
            <p class="mt-2 text-xs font-medium text-rose-700/80">
                Rp {{ number_format($stats['refund_saldo'], 0, ',', '.') }} total saldo
            </p>
        </div>

        {{-- Card 4: Pendapatan Bulan Ini --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Pendapatan Bulan Ini</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-950">
                    Rp {{ $formatRupiahRingkas($stats['pendapatan_bulan_ini']) }}
                </span>
            </div>
            <p class="mt-2 text-xs text-emerald-800/60">Transaksi lunas &amp; DP dibayar, {{ \Illuminate\Support\Carbon::now()->translatedFormat('F Y') }}</p>
        </div>

    </div>

    {{-- SECTION 2: Grid Utama (Grafik, Tabel, Antrean, Stok) --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Kolom Kiri (Lebih Lebar): Chart + Tabel Transaksi --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- Interactive Bar Chart --}}
            <div class="rounded-2xl border border-black/5 bg-white p-5 sm:p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-display text-lg sm:text-xl font-bold text-gray-900">Transaksi 7 Hari Terakhir</h2>
                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">Mingguan</span>
                        </div>
                        <p class="mt-0.5 text-xs sm:text-sm text-gray-500">Tren volume penyewaan &amp; pembelian atelier</p>
                    </div>
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.transaksi.index') ? route('admin.transaksi.index') : '#' }}" class="group inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-amber-700 hover:text-amber-900 transition-colors">
                        <span>Lihat detail</span>
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-50 group-hover:bg-amber-100 transition-colors">
                            <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                </div>

                {{-- Chart Content --}}
                <div class="mt-8 flex items-end justify-between gap-2 sm:gap-4 h-52 pt-8">
                    @foreach ($chart as $bar)
                        @php
                            $heightPercent = max(12, round(($bar['value'] / $maxChart) * 100));
                            $isToday = !empty($bar['today']);
                        @endphp
                        <div class="group relative flex flex-1 flex-col items-center gap-2 h-full justify-end">
                            
                            {{-- Tooltip Popover --}}
                            <div class="absolute -top-9 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:-top-11 pointer-events-none z-20">
                                <div class="relative rounded-lg bg-gray-900 px-2.5 py-1 text-[11px] font-semibold text-white shadow-xl whitespace-nowrap">
                                    {{ $bar['value'] }} Transaksi
                                    <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 border-x-4 border-t-4 border-x-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            {{-- Numeric Badge --}}
                            <span class="text-xs font-bold transition-all duration-200 {{ $isToday ? 'text-amber-700 scale-110' : 'text-gray-400 group-hover:text-gray-800' }}">
                                {{ $bar['value'] }}
                            </span>

                            {{-- Bar Pillar --}}
                            <div class="relative flex h-full w-full items-end rounded-xl bg-gray-100/80 p-1 overflow-hidden">
                                <div
                                    class="w-full rounded-lg transition-all duration-700 ease-out group-hover:brightness-110 {{ $isToday ? 'bg-gradient-to-t from-amber-600 via-amber-500 to-amber-400 shadow-lg shadow-amber-500/30' : 'bg-gradient-to-t from-amber-300/80 to-amber-200/60 group-hover:from-amber-400 group-hover:to-amber-300' }}"
                                    style="height: {{ $heightPercent }}%;"
                                ></div>
                            </div>

                            {{-- Day Label --}}
                            <span class="text-xs transition-colors duration-200 {{ $isToday ? 'font-black text-amber-900' : 'font-medium text-gray-400 group-hover:text-gray-700' }}">
                                {{ $bar['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Transactions Table --}}
            <div class="rounded-2xl border border-black/5 bg-white p-5 sm:p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-display text-lg sm:text-xl font-bold text-gray-900">Transaksi Terbaru</h2>
                        <p class="mt-0.5 text-xs sm:text-sm text-gray-500">Aktivitas penyewaan dan pembelian busana atelier terkini</p>
                    </div>
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.transaksi.index') ? route('admin.transaksi.index') : '#' }}" class="group inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-amber-700 hover:text-amber-900 transition-colors">
                        <span>Lihat semua</span>
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-50 group-hover:bg-amber-100 transition-colors">
                            <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                </div>

                {{-- Table Responsive Wrapper --}}
                <div class="mt-5 overflow-x-auto -mx-5 px-5 sm:mx-0 sm:px-0">
                    <table class="w-full min-w-[620px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                <th class="pb-3 pr-4">Kode TRX</th>
                                <th class="pb-3 pr-4">Pelanggan</th>
                                <th class="pb-3 pr-4">Produk</th>
                                <th class="pb-3 pr-4">Total</th>
                                <th class="pb-3 pr-4">Status</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/80">
                            @forelse ($transaksiTerbaru as $t)
                                @php
                                    $items = $t->detail;
                                    $jumlahProduk = $items->count();
                                    $totalQty = (int) $items->sum('jumlah');
                                    $pertama = $items->first();
                                    $namaPelanggan = $t->nama_pelanggan;
                                    $inisial = strtoupper(mb_substr(trim($namaPelanggan) ?: '?', 0, 1));
                                    $fotoUser = $t->user->foto_profil ?? null;
                                    $fotoUserUrl = $fotoUser ? (\Illuminate\Support\Str::startsWith($fotoUser, ['http://', 'https://']) ? $fotoUser : asset('storage/' . $fotoUser)) : null;
                                    $statusColor = match ($t->status_pembayaran) {
                                        'lunas', 'dp_dibayar' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        default => 'bg-gray-100 text-gray-500 border-gray-200',
                                    };
                                @endphp
                                <tr class="group transition-colors duration-150 hover:bg-amber-50/30">
                                    {{-- Kode TRX + tanggal --}}
                                    <td class="py-3.5 pr-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $t->kode_transaksi }}</div>
                                        <div class="text-xs text-gray-500">{{ $t->tanggal->format('d M Y') }}</div>
                                    </td>
                                    {{-- Pelanggan + jenis + kanal --}}
                                    <td class="py-3.5 pr-4">
                                        <div class="flex items-center gap-3">
                                            @if ($fotoUserUrl)
                                                <img src="{{ $fotoUserUrl }}" alt="{{ $namaPelanggan }}" class="h-9 w-9 shrink-0 rounded-full object-cover border border-gray-200">
                                            @else
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-amber-100 bg-amber-50 text-sm font-black text-amber-700">{{ $inisial }}</span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-gray-900 transition-colors group-hover:text-amber-800">{{ $namaPelanggan }}</p>
                                                <div class="mt-1 flex items-center gap-1.5">
                                                    <span class="text-[11px] text-gray-500">{{ $t->jenis === 'sewa' ? 'Penyewaan' : 'Pembelian' }}</span>
                                                    @if (($t->channel ?? 'online') === 'kasir')
                                                        <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-indigo-600">Kasir</span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-gray-500">Online</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Produk --}}
                                    <td class="py-3.5 pr-4">
                                        <div class="text-xs font-medium text-gray-700">{{ $pertama?->produk->nama ?? '-' }}</div>
                                        @if ($jumlahProduk > 1 || $totalQty > 1)
                                            <div class="mt-1 text-[10px] font-semibold text-gray-400">
                                                @if ($jumlahProduk > 1)+{{ $jumlahProduk - 1 }} produk lain &middot; @endif{{ $totalQty }} barang
                                            </div>
                                        @endif
                                        @if ($t->sewa)
                                            <div class="mt-1 text-[10px] font-semibold text-amber-700">
                                                {{ \Carbon\Carbon::parse($t->sewa->tanggal_pinjam)->format('d M') }} -
                                                {{ \Carbon\Carbon::parse($t->sewa->tanggal_kembali_rencana)->format('d M') }}
                                            </div>
                                        @endif
                                    </td>
                                    {{-- Total --}}
                                    <td class="py-3.5 pr-4 text-sm font-bold tabular-nums text-gray-900">
                                        Rp {{ number_format($t->total, 0, ',', '.') }}
                                    </td>
                                    {{-- Status pembayaran --}}
                                    <td class="py-3.5 pr-4">
                                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $statusColor }}">
                                            {{ str_replace('_', ' ', $t->status_pembayaran) }}
                                        </span>
                                    </td>
                                    {{-- Aksi --}}
                                    <td class="py-3.5 text-right">
                                        <a href="{{ \Illuminate\Support\Facades\Route::has('admin.transaksi.show') ? route('admin.transaksi.show', $t->id) : '#' }}"
                                           class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-sm italic text-gray-400">Belum ada data transaksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Antrean Tindakan & Pemeliharaan Stok --}}
        <div class="space-y-6">

            {{-- Card: Perlu Tindakan --}}
            <div class="rounded-2xl border border-black/5 bg-white p-5 sm:p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-lg font-bold text-gray-900">Perlu Tindakan</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Prioritas operasional toko</p>
                    </div>
                    <span class="relative flex items-center">
                        <span class="animate-ping absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-rose-400 opacity-75"></span>
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-extrabold text-rose-700">{{ $antreanTotal }} antrean</span>
                    </span>
                </div>

                <div class="mt-5 space-y-3">
    @php
        $tindakanStyle = [
            'jaminan' => ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'tone' => 'rose'],
            'refund'  => ['icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6', 'tone' => 'amber'],
            'retur'   => ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'tone' => 'amber'],
            'fitting' => ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'tone' => 'sky'],
        ];
        $toneClass = [
            'rose'  => ['bg-rose-100 text-rose-600', 'hover:border-rose-200 hover:bg-rose-50/40', 'group-hover:text-rose-950', 'bg-rose-200/60 text-rose-800'],
            'amber' => ['bg-amber-100 text-amber-700', 'hover:border-amber-200 hover:bg-amber-50/40', 'group-hover:text-amber-950', 'bg-amber-200/60 text-amber-800'],
            'sky'   => ['bg-sky-100 text-sky-700', 'hover:border-sky-200 hover:bg-sky-50/40', 'group-hover:text-sky-950', 'bg-sky-200/60 text-sky-800'],
        ];
    @endphp

    @foreach ($actionQueue as $item)
        @php [$iconBg, $hoverBg, $hoverText, $badgeBg] = $toneClass[$tindakanStyle[$item['key']]['tone']]; @endphp
        <a href="{{ $item['route'] }}" class="group flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 transition-all duration-200 {{ $hoverBg }} hover:shadow-sm hover:-translate-x-0.5">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $iconBg }} transition-transform group-hover:scale-110">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tindakanStyle[$item['key']]['icon'] }}"/></svg>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 {{ $hoverText }}">{{ $item['title'] }}</h3>
                    <p class="text-xs text-gray-500">{{ $item['desc'] }}</p>
                </div>
            </div>
            <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $badgeBg }} text-xs font-bold">{{ $item['count'] }}</span>
        </a>
    @endforeach
                </div>
            </div>

            {{-- Card: Stok Perlu Perhatian --}}
            <div class="rounded-2xl border border-black/5 bg-white p-5 sm:p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-lg font-bold text-gray-900">Stok Perlu Perhatian</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Perawatan, stok habis &amp; hampir habis</p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-extrabold text-amber-800">{{ $stockAlertTotal }} busana</span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($stockAlerts as $alert)
                        @php
                            $isRusak = in_array($alert['status'], ['rusak', 'habis'], true);
                            $border = $isRusak ? 'border-rose-500' : 'border-amber-500';
                            $grad = $isRusak ? 'from-rose-50/80' : 'from-amber-50/80';
                            $badge = $isRusak ? 'bg-rose-200/60 text-rose-900' : 'bg-amber-200/60 text-amber-900';
                            $linkColor = $isRusak ? 'text-rose-700 hover:text-rose-900' : 'text-amber-700 hover:text-amber-900';
                            $titleHover = $isRusak ? 'group-hover:text-rose-900' : 'group-hover:text-amber-900';
                        @endphp
                        <div class="group relative overflow-hidden rounded-xl border-l-4 {{ $border }} bg-gradient-to-r {{ $grad }} to-white p-4 transition-all hover:shadow-md">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-xs sm:text-sm font-bold text-gray-900 {{ $titleHover }} transition-colors">{{ $alert['nama'] }}</h3>
                                <span class="rounded-md {{ $badge }} px-2 py-0.5 text-[10px] font-bold shrink-0">{{ $alert['label'] }}</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-600">SKU: {{ $alert['sku'] }}</p>
                            <a href="{{ \Illuminate\Support\Facades\Route::has('admin.produk.index') ? route('admin.produk.index') : '#' }}" class="mt-3 inline-flex items-center gap-1 text-xs font-extrabold {{ $linkColor }} transition-colors">
                                <span>Lihat di Kelola Produk</span>
                                <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Tidak ada busana yang perlu perhatian saat ini.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

@endsection