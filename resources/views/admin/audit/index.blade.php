@extends('admin.layouts.admin')
@section('title', 'Audit Log')

@section('content')
@php
    // @json() Blade memecah isinya di tiap koma, jadi array harus disiapkan di sini, bukan ditulis langsung di dalam @json().
    $filterJs = request()->only(['search', 'aksi', 'modul', 'peran', 'dari', 'sampai']);

    $kartu = [
        ['id' => 'stat-total', 'label' => 'Aktivitas Hari Ini', 'nilai' => $ringkasan['total'], 'catatan' => 'Semua aksi tercatat',
            'kelas' => 'border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30',
            'teks' => 'text-amber-950', 'label_kelas' => 'text-amber-800/80',
            'hover_shadow' => 'hover:shadow-amber-500/10',
            'ikon_kelas' => 'bg-gradient-to-tr from-amber-500 to-amber-400 shadow-md shadow-amber-500/20 group-hover:rotate-6',
            'ikon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'],

        ['id' => 'stat-pelaku', 'label' => 'Pelaku Aktif', 'nilai' => $ringkasan['pelaku'], 'catatan' => 'Akun berbeda hari ini',
            'kelas' => 'border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30',
            'teks' => 'text-emerald-950', 'label_kelas' => 'text-emerald-800/80',
            'hover_shadow' => 'hover:shadow-emerald-500/10',
            'ikon_kelas' => 'bg-gradient-to-tr from-emerald-500 to-teal-500 shadow-md shadow-emerald-500/20 group-hover:rotate-6',
            'ikon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'],

        ['id' => 'stat-dihapus', 'label' => 'Data Dihapus', 'nilai' => $ringkasan['dihapus'], 'catatan' => 'Penghapusan hari ini',
            'kelas' => 'border-slate-200/60 bg-gradient-to-br from-slate-50/80 via-white to-slate-100/30',
            'teks' => 'text-slate-900', 'label_kelas' => 'text-slate-700/80',
            'hover_shadow' => 'hover:shadow-slate-500/10',
            'ikon_kelas' => 'bg-gradient-to-tr from-slate-500 to-slate-400 shadow-md shadow-slate-500/20 group-hover:-rotate-6',
            'ikon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'],

        ['id' => 'stat-gagal', 'label' => 'Gagal Masuk', 'nilai' => $ringkasan['gagal'], 'catatan' => 'Percobaan login ditolak',
            'kelas' => 'border-rose-200/60 bg-gradient-to-br from-rose-50/80 via-white to-rose-100/30',
            'teks' => 'text-rose-950', 'label_kelas' => 'text-rose-800/80',
            'hover_shadow' => 'hover:shadow-rose-500/10',
            'ikon_kelas' => 'bg-gradient-to-tr from-rose-500 to-pink-500 shadow-md shadow-rose-500/20 group-hover:-rotate-6',
            'ikon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'],
    ];
@endphp

<div class="p-6 space-y-6 transition-all duration-500 animate-fade-in">

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

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Audit Log</h1>
            <p class="text-xs text-gray-500 mt-1">Jejak siapa melakukan apa di sistem. Baris baru muncul otomatis tanpa perlu memuat ulang halaman.</p>
        </div>
        <div class="flex items-center gap-3">
            <span id="live-waktu" class="text-[11px] tabular-nums text-gray-400"></span>
            <button type="button" id="btn-live"
                    class="group-shine relative inline-flex items-center gap-2 overflow-hidden rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white px-4 py-2 text-xs font-bold text-amber-800 shadow-sm transition-all hover:from-amber-100 hover:to-amber-50 hover:shadow-md hover:shadow-amber-500/10 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60">
                <span id="live-dot" class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span id="live-label">Live</span>
            </button>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach ($kartu as $k)
            <div class="group group-shine relative overflow-hidden rounded-2xl border p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ $k['hover_shadow'] }} {{ $k['kelas'] }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider {{ $k['label_kelas'] }}">{{ $k['label'] }}</span>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white transition-transform duration-300 group-hover:scale-110 {{ $k['ikon_kelas'] }}">
                        {!! $k['ikon'] !!}
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span id="{{ $k['id'] }}" class="text-3xl font-extrabold tabular-nums tracking-tight {{ $k['teks'] }}">{{ $k['nilai'] }}</span>
                </div>
                <p class="mt-2 text-[11px] font-medium text-gray-500">{{ $k['catatan'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.audit.index') }}" class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm space-y-3">
        <div class="flex flex-col lg:flex-row items-center gap-3">
            <div class="relative w-full lg:flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aktivitas, pelaku, data, atau alamat IP..."
                       class="w-full pl-10 pr-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <select name="aksi" onchange="this.form.submit()" aria-label="Jenis aksi" class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer">
                    <option value="">Semua Aksi</option>
                    @foreach (['created' => 'Dibuat', 'updated' => 'Diubah', 'deleted' => 'Dihapus', 'login' => 'Masuk', 'logout' => 'Keluar', 'login_gagal' => 'Gagal masuk', 'lainnya' => 'Aksi lain'] as $nilai => $nama)
                        <option value="{{ $nilai }}" {{ request('aksi') === $nilai ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>

                <select name="modul" onchange="this.form.submit()" aria-label="Modul" class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer">
                    <option value="">Semua Modul</option>
                    @foreach ($modulList as $m)
                        <option value="{{ $m }}" {{ request('modul') === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>

                <select name="peran" onchange="this.form.submit()" aria-label="Pelaku" class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer">
                    <option value="">Semua Pelaku</option>
                    @foreach (['owner' => 'Owner', 'admin' => 'Admin', 'pelanggan' => 'Pelanggan', 'tamu' => 'Tamu'] as $nilai => $nama)
                        <option value="{{ $nilai }}" {{ request('peran') === $nilai ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <label class="flex items-center gap-2 text-[11px] font-bold text-gray-500">
                Dari
                <input type="date" name="dari" value="{{ request('dari') }}" class="px-3 py-1.5 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
            </label>
            <label class="flex items-center gap-2 text-[11px] font-bold text-gray-500">
                Sampai
                <input type="date" name="sampai" value="{{ request('sampai') }}" class="px-3 py-1.5 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
            </label>
            <button type="submit"
                    class="bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-amber-500/20 active:scale-95 transition-all duration-200">
                Terapkan
            </button>
            @if ($adaFilter)
                <a href="{{ route('admin.audit.index') }}" class="text-xs font-bold text-gray-500 hover:text-amber-600 transition-colors">Reset</a>
            @endif
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Waktu</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pelaku</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Aksi</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Modul</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Aktivitas</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Asal</th>
                    </tr>
                </thead>
                <tbody id="tbody-audit" class="divide-y divide-gray-100/80">
                    @forelse ($logs as $log)
                        @include('admin.audit._baris', ['log' => $log])
                    @empty
                        <tr id="baris-kosong">
                            <td colspan="6" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
                                {{ $adaFilter ? 'Tidak ada aktivitas yang cocok dengan filter ini.' : 'Belum ada aktivitas tercatat.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<script>
    (function () {
        var FEED = @json(route('admin.audit.feed'));
        var FILTER = @json($filterJs);
        var HALAMAN = {{ (int) $logs->currentPage() }};
        var INTERVAL = 4000;
        var MAKS_BARIS = 100;

        var $ = function (id) { return document.getElementById(id); };
        var tbody = $('tbody-audit');
        var btn = $('btn-live');
        var dot = $('live-dot');
        var label = $('live-label');
        var waktu = $('live-waktu');

        var terbaru = {{ (int) $idTerbaru }};
        var aktif = HALAMAN === 1; // live hanya masuk akal di halaman pertama
        var sedang = false;

        function tampil() {
            dot.className = 'h-2 w-2 rounded-full ' + (aktif ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400');
            if (HALAMAN !== 1) {
                label.textContent = 'Live dijeda (hanya di halaman 1)';
            } else {
                label.textContent = aktif ? 'Live' : 'Dijeda';
            }
        }

        function query() {
            var p = new URLSearchParams();
            Object.keys(FILTER).forEach(function (k) { if (FILTER[k]) p.set(k, FILTER[k]); });
            p.set('after', terbaru);
            return p.toString();
        }

        function isiRingkasan(r) {
            [['stat-total', 'total'], ['stat-pelaku', 'pelaku'], ['stat-dihapus', 'dihapus'], ['stat-gagal', 'gagal']].forEach(function (x) {
                var el = $(x[0]);
                if (el && r[x[1]] !== undefined) el.textContent = r[x[1]];
            });
        }

        function sisip(html) {
            var kosong = $('baris-kosong');
            if (kosong) kosong.remove();

            var tpl = document.createElement('template');
            tpl.innerHTML = html.trim();
            var baris = Array.prototype.slice.call(tpl.content.querySelectorAll('tr'));

            baris.forEach(function (tr) { tr.classList.add('bg-amber-100'); });
            tbody.insertBefore(tpl.content, tbody.firstChild);
            setTimeout(function () {
                baris.forEach(function (tr) { tr.classList.remove('bg-amber-100'); });
            }, 2500);

            while (tbody.rows.length > MAKS_BARIS) tbody.deleteRow(-1);
        }

        function tarik() {
            if (!aktif || document.hidden || sedang) return;
            sedang = true;

            fetch(FEED + '?' + query(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            }).then(function (d) {
                if (d.terbaru) terbaru = Math.max(terbaru, d.terbaru);
                if (d.html) sisip(d.html);
                if (d.ringkasan) isiRingkasan(d.ringkasan);
                waktu.textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');
            }).catch(function () {
                // Sesi habis / koneksi putus: diam saja, coba lagi di putaran berikutnya.
            }).finally(function () {
                sedang = false;
            });
        }

        if (HALAMAN !== 1) btn.disabled = true;

        btn.addEventListener('click', function () {
            aktif = !aktif;
            tampil();
            if (aktif) tarik();
        });

        document.addEventListener('visibilitychange', function () { if (!document.hidden) tarik(); });

        tampil();
        setInterval(tarik, INTERVAL);
    })();
</script>
@endsection