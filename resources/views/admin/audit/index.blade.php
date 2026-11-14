@extends('admin.layouts.admin')
@section('title', 'Audit Log')

@section('content')
@php
    // @json() Blade memecah isinya di tiap koma, jadi array harus disiapkan di sini, bukan ditulis langsung di dalam @json().
    $filterJs = request()->only(['search', 'aksi', 'modul', 'peran', 'dari', 'sampai']);

    // Dipakai untuk menandai pemisah tanggal saat tabel dirender pertama kali.
    $tanggalBerjalan = null;

    // Label ramah-baca untuk chip filter aktif.
    $aksiLabelMap = ['created' => 'Dibuat', 'updated' => 'Diubah', 'deleted' => 'Dihapus', 'login' => 'Masuk', 'logout' => 'Keluar', 'login_gagal' => 'Gagal masuk', 'lainnya' => 'Aksi lain'];
    $peranLabelMap = ['owner' => 'Owner', 'admin' => 'Admin', 'pelanggan' => 'Pelanggan', 'tamu' => 'Tamu'];

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

    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 shadow-sm" role="alert">
            <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error') || $errors->any())
        <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800 shadow-sm" role="alert">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="space-y-0.5">
                @if (session('error')) <p>{{ session('error') }}</p> @endif
                @foreach ($errors->all() as $pesan) <p>{{ $pesan }}</p> @endforeach
            </div>
        </div>
    @endif

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

        /*
         * Area tabel scroll sendiri, header tetap di atas.
         * Background WAJIB solid: kalau transparan, baris yang ter-scroll tembus ke bawah header.
         * --tinggi-header diisi lewat JS (tinggi asli thead) supaya pemisah tanggal
         * menempel tepat di bawah header, tidak menimpanya.
         */
        #scroll-audit {
            max-height: 70vh;
            --tinggi-header: 49px;
        }

        #scroll-audit thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #f9fafb;
            box-shadow: inset 0 -1px 0 #e5e7eb;
        }

        #tbody-audit tr.tanggal-pemisah td {
            position: sticky;
            top: var(--tinggi-header);
            z-index: 10;
            background-color: #f3f4f6;
            box-shadow: inset 0 -1px 0 #e5e7eb;
        }

        /* Mode tampilan rapat: kurangi jarak vertikal tiap baris. */
        #tabel-audit.tampilan-rapat td {
            padding-top: .5rem !important;
            padding-bottom: .5rem !important;
        }

        #tabel-audit.tampilan-rapat tr.tanggal-pemisah td {
            padding-top: .25rem !important;
            padding-bottom: .25rem !important;
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
            <button type="button" id="btn-densitas"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-500 shadow-sm transition-all hover:border-amber-200 hover:text-amber-700 active:scale-95">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span id="densitas-label">Nyaman</span>
            </button>
            <button type="button" id="btn-bersihkan"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-600 shadow-sm transition-all hover:border-rose-300 hover:bg-rose-50 active:scale-95">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Bersihkan Log
            </button>
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

    {{-- Ringkasan hasil & chip filter aktif --}}
    <div class="flex flex-wrap items-center justify-between gap-2 px-1">
        <p class="text-[11px] font-semibold text-gray-400">
            @if ($logs->total())
                Menampilkan <span class="text-gray-600">{{ $logs->firstItem() }}–{{ $logs->lastItem() }}</span> dari <span class="text-gray-600">{{ number_format($logs->total(), 0, ',', '.') }}</span> aktivitas
            @else
                Tidak ada aktivitas untuk ditampilkan
            @endif
        </p>

        @if ($adaFilter)
            <div class="flex flex-wrap items-center gap-1.5">
                @if (request('search'))
                    <a href="{{ route('admin.audit.index', request()->except('search')) }}"
                       class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 hover:bg-amber-100 transition-colors">
                        Cari: "{{ \Illuminate\Support\Str::limit(request('search'), 20) }}" <span aria-hidden="true">&times;</span>
                    </a>
                @endif
                @if (request('aksi'))
                    <a href="{{ route('admin.audit.index', request()->except('aksi')) }}"
                       class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 hover:bg-amber-100 transition-colors">
                        Aksi: {{ $aksiLabelMap[request('aksi')] ?? request('aksi') }} <span aria-hidden="true">&times;</span>
                    </a>
                @endif
                @if (request('modul'))
                    <a href="{{ route('admin.audit.index', request()->except('modul')) }}"
                       class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 hover:bg-amber-100 transition-colors">
                        Modul: {{ request('modul') }} <span aria-hidden="true">&times;</span>
                    </a>
                @endif
                @if (request('peran'))
                    <a href="{{ route('admin.audit.index', request()->except('peran')) }}"
                       class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 hover:bg-amber-100 transition-colors">
                        Pelaku: {{ $peranLabelMap[request('peran')] ?? request('peran') }} <span aria-hidden="true">&times;</span>
                    </a>
                @endif
                @if (request('dari'))
                    <a href="{{ route('admin.audit.index', request()->except('dari')) }}"
                       class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 hover:bg-amber-100 transition-colors">
                        Dari: {{ request('dari') }} <span aria-hidden="true">&times;</span>
                    </a>
                @endif
                @if (request('sampai'))
                    <a href="{{ route('admin.audit.index', request()->except('sampai')) }}"
                       class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 hover:bg-amber-100 transition-colors">
                        Sampai: {{ request('sampai') }} <span aria-hidden="true">&times;</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Form hapus terpilih (id log diisi lewat JS saat dikirim) --}}
        <form id="form-hapus-terpilih" method="POST" action="{{ route('admin.audit.hapus-terpilih') }}">
            @csrf
            @method('DELETE')
            @foreach ($filterJs as $k => $v)
                @if (filled($v)) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
            @endforeach
        </form>
        <div id="bulk-bar" class="hidden items-center justify-between gap-3 border-b border-rose-100 bg-rose-50 px-6 py-3">
            <span class="text-xs font-bold text-rose-700"><span id="bulk-jumlah">0</span> log dipilih</span>
            <div class="flex items-center gap-2">
                <button type="button" id="bulk-batal"
                        class="rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 transition-all hover:bg-gray-50 active:scale-95">Batal</button>
                <button type="button" id="bulk-hapus"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-rose-700 active:scale-95">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Terpilih
                </button>
            </div>
        </div>

        <div id="scroll-audit" class="overflow-auto">
            <table id="tabel-audit" class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="w-10 px-4 py-4">
                            <input type="checkbox" id="cek-semua" aria-label="Pilih semua log di halaman ini"
                                   class="h-4 w-4 cursor-pointer rounded border-gray-300 accent-amber-600">
                        </th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Waktu</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pelaku</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Aksi</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Modul</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Aktivitas</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Asal</th>
                    </tr>
                </thead>
                <tbody id="tbody-audit" class="divide-y divide-gray-100/80">
                    @forelse ($logs as $log)
                        @php
                            $tanggalLog = optional($log->created_at)->isoFormat('dddd, D MMMM Y');
                        @endphp
                        @if ($tanggalLog && $tanggalLog !== $tanggalBerjalan)
                            <tr class="tanggal-pemisah bg-gray-50/90">
                                <td colspan="7" class="px-6 py-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    {{ $tanggalLog }}
                                </td>
                            </tr>
                            @php($tanggalBerjalan = $tanggalLog)
                        @endif
                        @include('admin.audit._baris', ['log' => $log])
                    @empty
                        <tr id="baris-kosong">
                            <td colspan="7" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
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

{{-- ===================== MODAL BERSIHKAN LOG ===================== --}}
<div id="modal-bersihkan" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div id="modal-bersihkan-box" class="w-full max-w-lg scale-95 transform rounded-3xl border border-rose-100 bg-white p-6 opacity-0 shadow-2xl transition-all duration-200 sm:p-8">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 ring-4 ring-rose-50/60">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-gray-900">Bersihkan Audit Log</h3>
                <p class="mt-1 text-xs leading-relaxed text-gray-500">Log yang dihapus <strong class="text-rose-600">tidak dapat dikembalikan</strong>. Pilih cakupan yang ingin dibersihkan.</p>
            </div>
        </div>

        <form id="form-bersihkan" method="POST" action="{{ route('admin.audit.bersihkan') }}" class="mt-6 space-y-3">
            @csrf
            @method('DELETE')
            @foreach ($filterJs as $k => $v)
                @if (filled($v)) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
            @endforeach

            {{-- Berdasarkan umur --}}
            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-gray-200 p-4 transition-colors hover:border-amber-300 has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/40">
                <input type="radio" name="mode" value="umur" checked class="mt-0.5 h-4 w-4 accent-amber-600">
                <div class="flex-1">
                    <p class="text-xs font-bold text-gray-800">Log yang sudah lama</p>
                    <p class="mt-0.5 text-[11px] text-gray-500">Disarankan untuk perawatan rutin. Log terbaru tetap aman.</p>
                    <select name="hari" id="bersihkan-hari" class="mt-2 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                        @foreach ($batasHari as $h => $namaHari)
                            <option value="{{ $h }}" data-jumlah="{{ $hitungUmur[$h] ?? 0 }}" {{ $h === 90 ? 'selected' : '' }}>Lebih lama dari {{ $namaHari }}</option>
                        @endforeach
                    </select>
                </div>
            </label>

            {{-- Sesuai filter --}}
            <label class="flex items-start gap-3 rounded-2xl border border-gray-200 p-4 transition-colors has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/40 {{ $adaFilter ? 'cursor-pointer hover:border-amber-300' : 'cursor-not-allowed opacity-50' }}">
                <input type="radio" name="mode" value="filter" {{ $adaFilter ? '' : 'disabled' }} class="mt-0.5 h-4 w-4 accent-amber-600">
                <div>
                    <p class="text-xs font-bold text-gray-800">Semua yang cocok dengan filter saat ini
                        @if ($adaFilter)<span class="font-semibold text-gray-400">({{ number_format($logs->total(), 0, ',', '.') }} log)</span>@endif
                    </p>
                    <p class="mt-0.5 text-[11px] text-gray-500">{{ $adaFilter ? 'Misalnya hapus semua log "Gagal masuk" atau satu modul saja.' : 'Terapkan filter di atas dulu untuk memakai opsi ini.' }}</p>
                </div>
            </label>

            {{-- Semua --}}
            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-gray-200 p-4 transition-colors hover:border-rose-300 has-[:checked]:border-rose-400 has-[:checked]:bg-rose-50/50">
                <input type="radio" name="mode" value="semua" class="mt-0.5 h-4 w-4 accent-rose-600">
                <div class="flex-1">
                    <p class="text-xs font-bold text-gray-800">Seluruh log <span class="font-semibold text-gray-400">({{ number_format($totalSemua, 0, ',', '.') }} log)</span></p>
                    <p class="mt-0.5 text-[11px] text-gray-500">Mengosongkan seluruh riwayat aktivitas.</p>
                    <div id="bersihkan-konfirmasi" class="mt-2 hidden">
                        <label class="text-[11px] font-bold text-rose-600">Ketik <span class="rounded bg-rose-100 px-1">HAPUS</span> untuk melanjutkan</label>
                        <input type="text" name="konfirmasi" id="bersihkan-konfirmasi-input" autocomplete="off"
                               class="mt-1 w-full rounded-xl border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                </div>
            </label>

            <p id="bersihkan-ringkas" class="px-1 pt-1 text-xs font-bold text-rose-600"></p>

            <div class="flex items-center gap-3 pt-2">
                <button type="button" id="bersihkan-batal"
                        class="flex-1 rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 transition-all hover:bg-gray-50 active:scale-95">Batal</button>
                <button type="submit" id="bersihkan-ya"
                        class="flex-1 rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/25 transition-all hover:from-rose-700 hover:to-rose-600 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
                    Ya, Hapus
                </button>
            </div>
        </form>
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
        var tabel = $('tabel-audit');
        var btn = $('btn-live');
        var dot = $('live-dot');
        var label = $('live-label');
        var waktu = $('live-waktu');
        var btnDensitas = $('btn-densitas');
        var labelDensitas = $('densitas-label');

        var JUMLAH_FILTER = {{ (int) $logs->total() }};
        var JUMLAH_SEMUA = {{ (int) $totalSemua }};

        var scrollEl = $('scroll-audit');
        var cekSemua = $('cek-semua');
        var bulkBar = $('bulk-bar');
        var bulkJumlah = $('bulk-jumlah');

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

        // Pemisah tanggal harus menempel tepat di bawah header, jadi tinggi header diukur dari DOM.
        function ukurHeader() {
            var th = tabel.tHead;
            if (th && th.offsetHeight) scrollEl.style.setProperty('--tinggi-header', th.offsetHeight + 'px');
        }

        // ----- Pilih & hapus banyak -----
        function daftarCek() {
            return Array.prototype.slice.call(tbody.querySelectorAll('.cek-log'));
        }

        function perbaruiBulk() {
            var list = daftarCek();
            var n = list.filter(function (c) { return c.checked; }).length;
            bulkJumlah.textContent = n;
            bulkBar.classList.toggle('hidden', n === 0);
            bulkBar.classList.toggle('flex', n > 0);
            cekSemua.checked = list.length > 0 && n === list.length;
            cekSemua.indeterminate = n > 0 && n < list.length;
        }

        tbody.addEventListener('change', function (e) {
            if (e.target.classList && e.target.classList.contains('cek-log')) perbaruiBulk();
        });

        cekSemua.addEventListener('change', function () {
            daftarCek().forEach(function (c) { c.checked = cekSemua.checked; });
            perbaruiBulk();
        });

        $('bulk-batal').addEventListener('click', function () {
            daftarCek().forEach(function (c) { c.checked = false; });
            perbaruiBulk();
        });

        $('bulk-hapus').addEventListener('click', async function () {
            var ids = daftarCek().filter(function (c) { return c.checked; }).map(function (c) { return c.value; });
            if (!ids.length) return;
            if (!(await konfirmasi('Hapus ' + ids.length + ' log terpilih? Tindakan ini tidak bisa dibatalkan.'))) return;

            var f = $('form-hapus-terpilih');
            Array.prototype.slice.call(f.querySelectorAll('input[name="ids[]"]')).forEach(function (i) { i.remove(); });
            ids.forEach(function (id) {
                var i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'ids[]';
                i.value = id;
                f.appendChild(i);
            });
            f.submit();
        });

        // ----- Modal Bersihkan Log -----
        var modal = $('modal-bersihkan');
        var modalBox = $('modal-bersihkan-box');
        var selectHari = $('bersihkan-hari');
        var boxKonfirmasi = $('bersihkan-konfirmasi');
        var inputKonfirmasi = $('bersihkan-konfirmasi-input');
        var ringkas = $('bersihkan-ringkas');
        var btnYa = $('bersihkan-ya');

        function modeTerpilih() {
            var r = document.querySelector('#form-bersihkan input[name="mode"]:checked');
            return r ? r.value : 'umur';
        }

        function segarkanModal() {
            var mode = modeTerpilih();
            var jumlah = 0;

            if (mode === 'umur') {
                jumlah = parseInt(selectHari.options[selectHari.selectedIndex].getAttribute('data-jumlah'), 10) || 0;
            } else if (mode === 'filter') {
                jumlah = JUMLAH_FILTER;
            } else {
                jumlah = JUMLAH_SEMUA;
            }

            boxKonfirmasi.classList.toggle('hidden', mode !== 'semua');
            ringkas.textContent = jumlah > 0
                ? jumlah.toLocaleString('id-ID') + ' log akan dihapus permanen.'
                : 'Tidak ada log yang cocok untuk dihapus.';

            var konfirmasiOk = mode !== 'semua' || inputKonfirmasi.value.trim().toUpperCase() === 'HAPUS';
            btnYa.disabled = !(jumlah > 0 && konfirmasiOk);
        }

        function bukaModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            segarkanModal();
            requestAnimationFrame(function () {
                modalBox.classList.remove('scale-95', 'opacity-0');
                modalBox.classList.add('scale-100', 'opacity-100');
            });
        }

        function tutupModal() {
            modalBox.classList.remove('scale-100', 'opacity-100');
            modalBox.classList.add('scale-95', 'opacity-0');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        $('btn-bersihkan').addEventListener('click', bukaModal);
        $('bersihkan-batal').addEventListener('click', tutupModal);
        modal.addEventListener('click', function (e) { if (e.target === modal) tutupModal(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutupModal(); });
        $('form-bersihkan').addEventListener('change', segarkanModal);
        inputKonfirmasi.addEventListener('input', segarkanModal);

        function tanggalPalingAtas() {
            var tr = tbody.querySelector('tr[data-tanggal]');
            return tr ? tr.getAttribute('data-tanggal') : null;
        }

        function buatPemisahTanggal(tanggal) {
            var tr = document.createElement('tr');
            tr.className = 'tanggal-pemisah bg-gray-50/90';
            var td = document.createElement('td');
            td.colSpan = 7;
            td.className = 'px-6 py-2 text-[11px] font-bold uppercase tracking-wider text-gray-400';
            td.textContent = tanggal;
            tr.appendChild(td);
            return tr;
        }

        function sisip(html) {
            var kosong = $('baris-kosong');
            if (kosong) kosong.remove();

            var tpl = document.createElement('template');
            tpl.innerHTML = html.trim();
            var baris = Array.prototype.slice.call(tpl.content.querySelectorAll('tr[data-log-id]'));
            if (!baris.length) return;

            baris.forEach(function (tr) { tr.classList.add('bg-amber-100'); });

            // Kalau baris baru ini dari tanggal yang belum ada pemisahnya di paling atas, sisipkan dulu.
            var tanggalBaru = baris[0].getAttribute('data-tanggal');
            if (tanggalBaru && tanggalBaru !== tanggalPalingAtas()) {
                tbody.insertBefore(buatPemisahTanggal(tanggalBaru), tbody.firstChild);
            }

            tbody.insertBefore(tpl.content, tbody.firstChild);
            setTimeout(function () {
                baris.forEach(function (tr) { tr.classList.remove('bg-amber-100'); });
            }, 2500);

            while (tbody.rows.length > MAKS_BARIS) tbody.deleteRow(-1);

            // Jangan sampai ada pemisah tanggal "yatim" tertinggal sendirian di baris paling bawah.
            var akhir = tbody.rows[tbody.rows.length - 1];
            if (akhir && akhir.classList.contains('tanggal-pemisah')) tbody.deleteRow(tbody.rows.length - 1);

            perbaruiBulk();
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

        function terapkanDensitas(rapat) {
            tabel.classList.toggle('tampilan-rapat', rapat);
            labelDensitas.textContent = rapat ? 'Rapat' : 'Nyaman';
        }

        try {
            terapkanDensitas(localStorage.getItem('audit_densitas_rapat') === '1');
        } catch (e) {
            terapkanDensitas(false);
        }

        btnDensitas.addEventListener('click', function () {
            var rapat = !tabel.classList.contains('tampilan-rapat');
            terapkanDensitas(rapat);
            try { localStorage.setItem('audit_densitas_rapat', rapat ? '1' : '0'); } catch (e) {}
        });

        if (HALAMAN !== 1) btn.disabled = true;

        btn.addEventListener('click', function () {
            aktif = !aktif;
            tampil();
            if (aktif) tarik();
        });

        document.addEventListener('visibilitychange', function () { if (!document.hidden) tarik(); });

        tampil();
        ukurHeader();
        window.addEventListener('load', ukurHeader);
        window.addEventListener('resize', ukurHeader);
        setInterval(tarik, INTERVAL);
    })();
</script>
@endsection