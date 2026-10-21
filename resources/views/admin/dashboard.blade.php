@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', \Illuminate\Support\Carbon::now()->translatedFormat('l, j F Y') . ' · Ringkasan performa dan operasional atelier hari ini')

@section('content')

    @php
        $stats ??= [
            'transaksi_hari_ini' => 18,
            'transaksi_trend' => '+12%',
            'sewa_aktif' => 27,
            'sewa_jatuh_tempo' => 3,
            'refund_pending' => 2,
            'refund_saldo' => 1_150_000,
            'pendapatan_bulan_ini' => 84_200_000,
        ];
        $chart ??= [
            ['label' => 'Rab', 'value' => 11],
            ['label' => 'Kam', 'value' => 14],
            ['label' => 'Jum', 'value' => 8],
            ['label' => 'Sab', 'value' => 17],
            ['label' => 'Min', 'value' => 12],
            ['label' => 'Sen', 'value' => 15],
            ['label' => 'Hari ini', 'value' => 18, 'today' => true],
        ];
        $maxChart = max(1, max(array_column($chart, 'value')));

        $transactions ??= [
            ['kode' => 'SWA-2026-0912', 'pelanggan' => 'Perrell Brown', 'inisial' => 'PB', 'detail' => 'Fitting Final · 3 item', 'jenis' => 'Sewa', 'kanal' => 'Online', 'status' => 'DP 50%', 'statusTone' => 'rose', 'total' => 4700000],
            ['kode' => 'BEL-2026-0805', 'pelanggan' => 'Raditya A.', 'inisial' => 'RA', 'detail' => 'Beskap & Aksesoris', 'jenis' => 'Beli', 'kanal' => 'Walk In', 'status' => 'Lunas', 'statusTone' => 'green', 'total' => 2500000],
            ['kode' => 'BEL-2026-0804', 'pelanggan' => 'Fathia M.', 'inisial' => 'FM', 'detail' => 'Gaun Pengantin Custom', 'jenis' => 'Beli', 'kanal' => 'Online', 'status' => 'DP 50%', 'statusTone' => 'rose', 'total' => 9545000],
            ['kode' => 'SWA-2026-0911', 'pelanggan' => 'Dian S.', 'inisial' => 'DS', 'detail' => 'Kebaya Brokat Premium', 'jenis' => 'Sewa', 'kanal' => 'Walk In', 'status' => 'Lunas', 'statusTone' => 'green', 'total' => 3200000],
        ];
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
                <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-100/80 px-2 py-0.5 text-xs font-bold text-emerald-700 animate-pulse">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
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
                    Rp {{ number_format($stats['pendapatan_bulan_ini'] / 1_000_000, 1, ',', '.') }}jt
                </span>
            </div>
            <p class="mt-2 text-xs text-emerald-800/60">Target bulanan: 85% tercapai</p>
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
                    <a href="{{ route('admin.transaksi.index') ?? '#' }}" class="group inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-amber-700 hover:text-amber-900 transition-colors">
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
                    <a href="{{ route('admin.transaksi.index') ?? '#' }}" class="group inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-amber-700 hover:text-amber-900 transition-colors">
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
                                <th class="pb-3 pr-4">Jenis</th>
                                <th class="pb-3 pr-4">Kanal</th>
                                <th class="pb-3 pr-4">Status</th>
                                <th class="pb-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/80">
                            @foreach ($transactions as $trx)
                                <tr class="group transition-colors duration-150 hover:bg-amber-50/30">
                                    {{-- Kode TRX --}}
                                    <td class="py-3.5 pr-4">
                                        <span class="inline-block rounded-md bg-gray-100 px-2 py-1 font-mono text-xs font-bold text-gray-700 group-hover:bg-amber-100 group-hover:text-amber-900 transition-colors">
                                            {{ $trx['kode'] }}
                                        </span>
                                    </td>
                                    {{-- Pelanggan --}}
                                    <td class="py-3.5 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-amber-600 font-bold text-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                                                {{ $trx['inisial'] }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900 truncate group-hover:text-amber-800 transition-colors">{{ $trx['pelanggan'] }}</p>
                                                <p class="text-xs text-gray-400 truncate">{{ $trx['detail'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Jenis --}}
                                    <td class="py-3.5 pr-4 text-xs font-semibold text-gray-600">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $trx['jenis'] === 'Sewa' ? 'bg-indigo-500' : 'bg-emerald-500' }}"></span>
                                            {{ $trx['jenis'] }}
                                        </span>
                                    </td>
                                    {{-- Kanal --}}
                                    <td class="py-3.5 pr-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $trx['kanal'] === 'Online' ? 'bg-sky-50 text-sky-700 border border-sky-200/60' : 'bg-amber-50 text-amber-700 border border-amber-200/60' }}">
                                            {{ $trx['kanal'] }}
                                        </span>
                                    </td>
                                    {{-- Status --}}
                                    <td class="py-3.5 pr-4">
                                        @if($trx['statusTone'] === 'rose')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200/60">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                {{ $trx['status'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200/60">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                {{ $trx['status'] }}
                                            </span>
                                        @endif
                                    </td>
                                    {{-- Total --}}
                                    <td class="py-3.5 text-right font-extrabold text-gray-900">
                                        Rp {{ number_format($trx['total'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
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
                        <p class="mt-0.5 text-xs text-gray-500">Prioritas operasional studio</p>
                    </div>
                    <span class="relative flex items-center">
                        <span class="animate-ping absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-rose-400 opacity-75"></span>
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-extrabold text-rose-700">10 antrean</span>
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    
                    {{-- Item 1 --}}
                    <a href="{{ route('admin.jaminan.index') ?? '#' }}" class="group flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 transition-all duration-200 hover:border-rose-200 hover:bg-rose-50/40 hover:shadow-sm hover:-translate-x-0.5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 transition-transform group-hover:scale-110">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-rose-950">Verifikasi jaminan sewa</h3>
                                <p class="text-xs text-gray-500">4 jaminan KTP / deposit pending</p>
                            </div>
                        </div>
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-rose-200/60 text-xs font-bold text-rose-800">4</span>
                    </a>

                    {{-- Item 2 --}}
                    <a href="{{ route('admin.refund.index') ?? '#' }}" class="group flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 transition-all duration-200 hover:border-amber-200 hover:bg-amber-50/40 hover:shadow-sm hover:-translate-x-0.5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 transition-transform group-hover:scale-110">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-amber-950">Refund diproses</h3>
                                <p class="text-xs text-gray-500">2 pengajuan pembatalan</p>
                            </div>
                        </div>
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-200/60 text-xs font-bold text-amber-800">2</span>
                    </a>

                    {{-- Item 3 --}}
                    <a href="{{ route('admin.refund.index') ?? '#' }}" class="group flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 transition-all duration-200 hover:border-amber-200 hover:bg-amber-50/40 hover:shadow-sm hover:-translate-x-0.5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 transition-transform group-hover:scale-110">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-amber-950">Return pengecekan</h3>
                                <p class="text-xs text-gray-500">1 gaun baru tiba di studio</p>
                            </div>
                        </div>
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-200/60 text-xs font-bold text-amber-800">1</span>
                    </a>

                    {{-- Item 4 --}}
                    <a href="{{ route('admin.fitting.index') ?? '#' }}" class="group flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 transition-all duration-200 hover:border-sky-200 hover:bg-sky-50/40 hover:shadow-sm hover:-translate-x-0.5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 transition-transform group-hover:scale-110">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-sky-950">Reservasi fitting baru</h3>
                                <p class="text-xs text-gray-500">3 jadwal butuh konfirmasi</p>
                            </div>
                        </div>
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-200/60 text-xs font-bold text-sky-800">3</span>
                    </a>

                </div>
            </div>

            {{-- Card: Stok Perlu Perhatian --}}
            <div class="rounded-2xl border border-black/5 bg-white p-5 sm:p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-lg font-bold text-gray-900">Stok Perlu Perhatian</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Pemeliharaan &amp; perbaikan busana</p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-extrabold text-amber-800">2 busana</span>
                </div>

                <div class="mt-5 space-y-3">
                    
                    {{-- Alert 1: Laundry --}}
                    <div class="group relative overflow-hidden rounded-xl border-l-4 border-amber-500 bg-gradient-to-r from-amber-50/80 to-white p-4 transition-all hover:shadow-md">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-amber-900 transition-colors">Jas Beskap Beludru Jawa</h3>
                            <span class="rounded-md bg-amber-200/60 px-2 py-0.5 text-[10px] font-bold text-amber-900 shrink-0">Dry Cleaning</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-600">Sedang di vendor laundry sejak 3 hari lalu</p>
                        <a href="#" class="mt-3 inline-flex items-center gap-1 text-xs font-extrabold text-amber-700 hover:text-amber-900 transition-colors">
                            <span>Hubungi vendor</span>
                            <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>

                    {{-- Alert 2: Repair --}}
                    <div class="group relative overflow-hidden rounded-xl border-l-4 border-rose-500 bg-gradient-to-r from-rose-50/80 to-white p-4 transition-all hover:shadow-md">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-rose-900 transition-colors">Gaun Merah Maroon Pengantin</h3>
                            <span class="rounded-md bg-rose-200/60 px-2 py-0.5 text-[10px] font-bold text-rose-900 shrink-0">Perlu Repair</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-600">Payet bordir lepas di bagian bahu kanan</p>
                        <a href="#" class="mt-3 inline-flex items-center gap-1 text-xs font-extrabold text-rose-700 hover:text-rose-900 transition-colors">
                            <span>Kirim ke penjahit atelier</span>
                            <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </div>

@endsection