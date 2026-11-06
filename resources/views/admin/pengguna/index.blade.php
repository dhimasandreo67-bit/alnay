@extends('admin.layouts.admin')
@section('title', 'Kelola Pengguna')

@section('content')
@php
    $tgl = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->locale('id') : null;
    $adaFilter = request()->filled('search') || $status !== '' || $inaktif > 0;
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
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Kelola Pengguna</h1>
            <p class="text-xs text-gray-500 mt-1">Pantau akun pelanggan, tinjau yang lama tidak aktif, lalu nonaktifkan atau hapus bila perlu.</p>
        </div>
        <button type="button" data-modal-open="modal-tambah-pengguna"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all duration-200 text-sm cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Tambah Akun Manual
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Card 1: Total Pengguna --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Total Pengguna</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-amber-950 tabular-nums">{{ $ringkasan['total'] }}</span>
                <span class="text-sm font-bold text-amber-800/60">Akun</span>
            </div>
            <p class="mt-2 text-xs font-medium text-amber-700/80">Terdaftar di sistem</p>
        </div>

        {{-- Card 2: Akun Aktif --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Akun Aktif</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-emerald-950 tabular-nums">{{ $ringkasan['aktif'] }}</span>
                <span class="text-sm font-bold text-emerald-800/60">Akun</span>
            </div>
            <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Bisa masuk &amp; berbelanja</span>
            </div>
        </div>

        {{-- Card 3: Dinonaktifkan --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-slate-200/60 bg-gradient-to-br from-slate-50/80 via-white to-slate-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-700/80">Dinonaktifkan</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-slate-500 to-slate-400 text-white shadow-md shadow-slate-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">{{ $ringkasan['nonaktif'] }}</span>
                <span class="text-sm font-bold text-slate-600">Akun</span>
            </div>
            <p class="mt-2 text-xs font-medium text-slate-500">Akses dibatasi sementara</p>
        </div>

        {{-- Card 4: Tidak Aktif >= X Hari (link) --}}
        <a href="{{ route('admin.pengguna.index', ['inaktif' => $batasLama, 'status' => 'aktif']) }}"
           class="group group-shine relative overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-br from-rose-50/80 via-white to-rose-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-rose-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-800/80">Tidak Aktif &ge; {{ $batasLama }} Hari</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-rose-950 tabular-nums">{{ $ringkasan['lama'] }}</span>
                <span class="text-sm font-bold text-rose-800/60">Akun</span>
            </div>
            <p class="mt-2 text-xs font-medium text-rose-600">Klik untuk meninjau</p>
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.pengguna.index') }}" class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-4">
        <div class="relative w-full lg:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau no. HP..."
                   class="w-full pl-10 pr-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-between lg:justify-end">
            <select name="status" onchange="this.form.submit()" aria-label="Status akun" class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 cursor-pointer">
                <option value="">Semua Status</option>
                <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Dinonaktifkan</option>
            </select>
            <select name="inaktif" onchange="this.form.submit()" aria-label="Lama tidak aktif" class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 cursor-pointer">
                <option value="">Semua Aktivitas</option>
                @foreach ([30, 60, 90, 180] as $hari)
                    <option value="{{ $hari }}" {{ $inaktif === $hari ? 'selected' : '' }}>Tidak aktif &ge; {{ $hari }} hari</option>
                @endforeach
            </select>
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-gray-800 transition-all">Cari</button>
            @if ($adaFilter)
                <a href="{{ route('admin.pengguna.index') }}" class="text-xs font-bold text-gray-500 hover:text-amber-600">Reset</a>
            @endif
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Bar aksi massal (muncul saat ada yang dicentang) --}}
        <div id="bar-massal" class="hidden items-center justify-between gap-3 border-b border-amber-100 bg-amber-50/70 px-6 py-3">
            <p class="text-xs font-semibold text-amber-900"><span id="jumlah-dipilih">0</span> akun dipilih</p>
            <div class="flex items-center gap-2">
                <button type="button" data-modal-open="modal-konfirmasi-pengguna" data-bulk="1" data-aksi="nonaktifkan" class="ui-btn ui-btn--ghost">Nonaktifkan</button>
                <button type="button" data-modal-open="modal-konfirmasi-pengguna" data-bulk="1" data-aksi="aktifkan" class="ui-btn ui-btn--ghost">Aktifkan</button>
                <button type="button" data-modal-open="modal-konfirmasi-pengguna" data-bulk="1" data-aksi="hapus" class="ui-btn ui-btn--danger">Hapus</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="w-10 pl-6 py-4">
                            <input type="checkbox" id="cek-semua" aria-label="Pilih semua" class="h-4 w-4 rounded border-gray-300 accent-amber-600">
                        </th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pengguna</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Kontak</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Transaksi</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Terakhir Aktif</th>
                        <th class="px-4 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80">
                    @forelse ($users as $u)
                        @php
                            $aktif = (bool) $u->is_active;
                            $s = $stat->get($u->id);
                            $terakhir = $tgl($u->last_active_at ?? $u->created_at);
                            $lama = $aktif && $terakhir && $terakhir->lt(now()->subDays($batasLama));
                            $inisial = strtoupper(mb_substr(trim($u->name) ?: '?', 0, 1));
                        @endphp
                        <tr class="hover:bg-amber-50/30 transition-colors {{ $aktif ? '' : 'bg-gray-50/60' }}">
                            <td class="pl-6 py-4">
                                <input type="checkbox" value="{{ $u->id }}" aria-label="Pilih {{ $u->name }}" class="cek-pengguna h-4 w-4 rounded border-gray-300 accent-amber-600">
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black border {{ $aktif ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-gray-100 text-gray-500 border-gray-200' }}">{{ $inisial }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold {{ $aktif ? 'text-gray-900' : 'text-gray-500' }}">{{ $u->name }}</p>
                                        <p class="truncate text-xs text-gray-500">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-700">{{ $u->no_hp ?: '-' }}</td>
                            <td class="px-4 py-4">
                                <p class="text-xs font-bold text-gray-800">{{ (int) ($s->jumlah ?? 0) }} transaksi</p>
                                <p class="text-[11px] text-gray-400 tabular-nums">Rp {{ number_format((float) ($s->belanja ?? 0), 0, ',', '.') }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-xs font-semibold text-gray-700">{{ $terakhir ? $terakhir->diffForHumans() : '-' }}</p>
                                @if ($lama)
                                    <span class="mt-1 inline-flex rounded-full bg-amber-50 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-amber-700 ring-1 ring-amber-200">Lama tidak aktif</span>
                                @elseif (! $u->last_active_at)
                                    <p class="text-[10px] text-gray-400">Aktivitas belum tercatat</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($aktif)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">

                                    {{-- Detail --}}
                                    <button type="button" data-modal-open="modal-detail-pengguna" data-id="{{ $u->id }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 active:scale-95 transition-all shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </button>

                                    {{-- Edit --}}
                                    <button type="button" data-modal-open="modal-edit-pengguna"
                                            data-id="{{ $u->id }}"
                                            data-user="{{ json_encode($dataEdit[$u->id] ?? []) }}"
                                            title="Edit akun" aria-label="Edit akun {{ $u->name }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 shadow-sm transition-all hover:border-amber-200 hover:bg-amber-50 hover:text-amber-600 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    {{-- Toggle status --}}
                                    <button type="button" data-modal-open="modal-konfirmasi-pengguna"
                                            data-aksi="{{ $aktif ? 'nonaktifkan' : 'aktifkan' }}"
                                            data-nama="{{ $u->name }}"
                                            data-url="{{ route('admin.pengguna.status', $u->id) }}"
                                            title="{{ $aktif ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                                            aria-label="{{ $aktif ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $u->name }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-gray-200 bg-white shadow-sm transition-all active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400
                                                   {{ $aktif ? 'text-gray-400 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-600' : 'text-gray-400 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600' }}">
                                        @if ($aktif)
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </button>

                                    {{-- Hapus --}}
                                    <button type="button" data-modal-open="modal-konfirmasi-pengguna"
                                            data-aksi="hapus"
                                            data-nama="{{ $u->name }}"
                                            data-url="{{ route('admin.pengguna.destroy', $u->id) }}"
                                            title="Hapus akun" aria-label="Hapus akun {{ $u->name }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 shadow-sm transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
                                {{ $adaFilter ? 'Tidak ada pengguna yang cocok dengan filter ini.' : 'Belum ada akun pengguna.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $users->links() }}
        </div>
    </div>
</div>

{{-- ================= MODAL: DETAIL ================= --}}
<x-modal id="modal-detail-pengguna" title="Detail Akun" size="lg">
    <div class="space-y-5">
        <div class="flex items-center gap-4">
            <span id="dt-inisial" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-600 to-amber-400 text-lg font-black text-white shadow-md shadow-amber-500/20"></span>
            <div class="min-w-0 flex-1">
                <p id="dt-nama" class="truncate text-base font-bold text-gray-900"></p>
                <p id="dt-email" class="truncate text-xs text-gray-500"></p>
                <p id="dt-hp" class="text-xs text-gray-500"></p>
            </div>
            <span id="dt-status" class="shrink-0 rounded-lg border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"></span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-2xl bg-amber-50/50 border border-amber-100/50 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-amber-700/70">Transaksi</p><p id="dt-transaksi" class="mt-1 text-sm font-black text-gray-900"></p></div>
            <div class="rounded-2xl bg-emerald-50/50 border border-emerald-100/50 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700/70">Total Belanja</p><p id="dt-belanja" class="mt-1 text-sm font-black tabular-nums text-gray-900"></p></div>
            <div class="rounded-2xl bg-sky-50/50 border border-sky-100/50 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-sky-700/70">Bergabung</p><p id="dt-bergabung" class="mt-1 text-xs font-bold text-gray-900"></p></div>
            <div class="rounded-2xl bg-rose-50/50 border border-rose-100/50 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-rose-700/70">Terakhir Aktif</p><p id="dt-terakhir" class="mt-1 text-xs font-bold text-gray-900"></p><p id="dt-terakhir-rel" class="text-[10px] text-gray-400"></p></div>
        </div>

        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">Semua Data Akun</p>
            <dl id="dt-semua" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></dl>
        </div>
    </div>

    <x-slot:footer>
        <a id="dt-link-transaksi" href="#" class="ui-btn ui-btn--ghost">Lihat transaksi</a>
        <button type="button" class="ui-btn ui-btn--primary" data-modal-close>Tutup</button>
    </x-slot:footer>
</x-modal>

{{-- ================= MODAL: TAMBAH AKUN ================= --}}
<x-modal id="modal-tambah-pengguna" title="Tambah Akun Manual" size="md">
    <form id="form-tambah-pengguna" method="POST" action="{{ route('admin.pengguna.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="_form" value="tambah">

        @if ($errors->any() && old('_form') === 'tambah')
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                <ul class="list-disc list-inside space-y-0.5 font-semibold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <label for="tp-name" class="text-xs font-bold text-gray-500">Nama lengkap</label>
            <input type="text" id="tp-name" name="name" value="{{ old('name') }}" required maxlength="255" data-autofocus class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="tp-email" class="text-xs font-bold text-gray-500">Email</label>
                <input type="email" id="tp-email" name="email" value="{{ old('email') }}" required maxlength="255" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>
            <div>
                <label for="tp-hp" class="text-xs font-bold text-gray-500">No. HP / WhatsApp</label>
                <input type="text" id="tp-hp" name="no_hp" value="{{ old('no_hp') }}" maxlength="30" placeholder="08xxxxxxxxxx" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label for="tp-pass" class="text-xs font-bold text-gray-500">Kata sandi (opsional)</label>
            <input type="password" id="tp-pass" name="password" minlength="8" autocomplete="new-password" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            <p class="mt-1 text-[11px] text-gray-400">Kosongkan jika pelanggan masuk lewat kode OTP atau akun Google.</p>
        </div>

        {{-- Alamat pengiriman (opsional) — tersimpan sebagai alamat utama di menu "Alamat Saya" pelanggan --}}
        @include('admin.pengguna._form-alamat', [
            'p' => 'tp',
            'catatan' => 'Opsional. Jika diisi, disimpan sebagai alamat utama pelanggan dan tampil di menu Alamat Saya.',
        ])

        <input type="hidden" name="verifikasi_email" value="0">
        <label class="flex cursor-pointer items-start gap-3 text-xs text-gray-700">
            <input type="checkbox" name="verifikasi_email" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 accent-amber-600" {{ old('verifikasi_email', '1') == '1' ? 'checked' : '' }}>
            <span><strong class="block text-gray-900">Tandai email sudah terverifikasi</strong><span class="text-gray-400">Admin yang memastikan email ini milik pelanggan.</span></span>
        </label>

        <input type="hidden" name="is_active" value="0">
        <label class="flex cursor-pointer items-start gap-3 text-xs text-gray-700">
            <input type="checkbox" name="is_active" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 accent-amber-600" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
            <span><strong class="block text-gray-900">Langsung aktifkan akun</strong><span class="text-gray-400">Matikan jika akun baru boleh dipakai nanti.</span></span>
        </label>
    </form>

    <x-slot:footer>
        <button type="button" class="ui-btn ui-btn--ghost" data-modal-close>Batal</button>
        <button type="submit" form="form-tambah-pengguna" id="btn-simpan-pengguna" class="ui-btn ui-btn--primary">Simpan Akun</button>
    </x-slot:footer>
</x-modal>

{{-- ================= MODAL: EDIT AKUN ================= --}}
<x-modal id="modal-edit-pengguna" title="Edit Data Pengguna" size="md">
    <form id="form-edit-pengguna" method="POST" action="#" class="space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="_form" value="edit">
        <input type="hidden" name="_id" id="ep-id" value="{{ old('_id') }}">

        @if ($errors->any() && old('_form') === 'edit')
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                <ul class="list-disc list-inside space-y-0.5 font-semibold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <label for="ep-name" class="text-xs font-bold text-gray-500">Nama lengkap</label>
            <input type="text" id="ep-name" name="name" required maxlength="255" data-autofocus class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="ep-email" class="text-xs font-bold text-gray-500">Email</label>
                <input type="email" id="ep-email" name="email" required maxlength="255" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>
            <div>
                <label for="ep-hp" class="text-xs font-bold text-gray-500">No. HP / WhatsApp</label>
                <input type="text" id="ep-hp" name="no_hp" maxlength="30" placeholder="08xxxxxxxxxx" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label for="ep-pass" class="text-xs font-bold text-gray-500">Kata sandi baru (opsional)</label>
            <input type="password" id="ep-pass" name="password" minlength="8" autocomplete="new-password" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            <p class="mt-1 text-[11px] text-gray-400">Kosongkan jika tidak ingin mengubah kata sandi.</p>
        </div>
        {{-- Alamat utama pelanggan (tabel alamat_pengiriman, sama dengan menu "Alamat Saya") --}}
        <input type="hidden" name="ubah_alamat" id="ep-ubah" value="0">
        @include('admin.pengguna._form-alamat', [
            'p' => 'ep',
            'catatan' => 'Perubahan di sini memperbarui alamat utama pelanggan (menu Alamat Saya).',
        ])
    </form>

    <x-slot:footer>
        <button type="button" class="ui-btn ui-btn--ghost" data-modal-close>Batal</button>
        <button type="submit" form="form-edit-pengguna" id="btn-simpan-edit" class="ui-btn ui-btn--primary">Simpan Perubahan</button>
    </x-slot:footer>
</x-modal>

{{-- ================= MODAL: KONFIRMASI (satu modal untuk semua aksi) ================= --}}
<x-modal id="modal-konfirmasi-pengguna" title="Konfirmasi" size="sm">
    <form id="form-konfirmasi-pengguna" method="POST" action="#" class="space-y-3">
        @csrf
        <div id="konfirmasi-field"></div>
        <p id="konfirmasi-pesan" class="text-sm text-gray-600"></p>
        <p id="konfirmasi-catatan" class="text-[11px] font-semibold text-rose-600"></p>
    </form>

    <x-slot:footer>
        <button type="button" class="ui-btn ui-btn--ghost" data-modal-close>Batal</button>
        <button type="submit" form="form-konfirmasi-pengguna" id="btn-konfirmasi-pengguna" class="ui-btn ui-btn--danger">Lanjutkan</button>
    </x-slot:footer>
</x-modal>

<script>
    (function () {
        var PENGGUNA = @json($detail);
        var URL_MASSAL = @json(route('admin.pengguna.massal'));
        var URL_TRANSAKSI = @json(route('admin.transaksi.index'));
        var $ = function (id) { return document.getElementById(id); };

        // ---------- Detail ----------
        $('modal-detail-pengguna').addEventListener('modal:open', function (e) {
            var b = e.detail.trigger;
            var u = b && PENGGUNA[b.dataset.id];
            if (!u) return;

            $('dt-inisial').textContent = (u.name || '?').trim().charAt(0).toUpperCase();
            $('dt-nama').textContent = u.name;
            $('dt-email').textContent = u.email || '-';
            $('dt-hp').textContent = u.no_hp || 'No. HP belum diisi';
            $('dt-transaksi').textContent = u.transaksi + ' kali';
            $('dt-belanja').textContent = u.belanja;
            $('dt-bergabung').textContent = u.bergabung;
            $('dt-terakhir').textContent = u.terakhir_aktif;
            $('dt-terakhir-rel').textContent = u.terakhir_aktif_relatif;
            $('dt-link-transaksi').href = URL_TRANSAKSI + '?search=' + encodeURIComponent(u.name);

            var st = $('dt-status');
            st.textContent = u.aktif ? 'Aktif' : 'Nonaktif';
            st.className = 'shrink-0 rounded-lg border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider ' +
                (u.aktif ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-gray-200 bg-gray-100 text-gray-500');

            var dl = $('dt-semua');
            dl.innerHTML = '';
            u.semua.forEach(function (row) {
                var wrap = document.createElement('div');
                wrap.className = 'rounded-xl bg-gray-50 px-3 py-2';
                var dt = document.createElement('dt');
                dt.className = 'text-[10px] font-bold uppercase tracking-wider text-gray-400';
                dt.textContent = row.label;
                var dd = document.createElement('dd');
                dd.className = 'mt-0.5 break-words text-xs font-medium text-gray-800';
                dd.textContent = row.nilai;
                wrap.appendChild(dt);
                wrap.appendChild(dd);
                dl.appendChild(wrap);
            });
        });

        // ---------- Edit pengguna ----------
        var URL_UPDATE = @json(route('admin.pengguna.update', '__ID__'));
        var OLD_EDIT = @json(old('_form') === 'edit' ? old() : null);

        // ---------- Alamat pengiriman (terhubung ke "Alamat Saya" pelanggan) ----------
        // Dropdown wilayah & pencarian kelurahan memakai sumber data RajaOngkir yang sama dengan
        // halaman Checkout dan Alamat Saya, supaya alamat dari admin langsung bisa dipakai ongkir.
        var URL_PROVINSI = @json(route('admin.pengguna.ongkir.provinsi'));
        var URL_KOTA = @json(route('admin.pengguna.ongkir.kota', '__ID__'));
        var URL_KECAMATAN = @json(route('admin.pengguna.ongkir.kecamatan', '__ID__'));
        var URL_TUJUAN = @json(route('admin.pengguna.ongkir.tujuan'));

        function pasangAlamat(p, saatDiubah) {
            var el = function (s) { return $(p + '-' + s); };
            var selProv = el('provinsi'), selKota = el('kota'), selKec = el('kecamatan');
            var inCari = el('tujuan-q'), kotak = el('tujuan-hasil'), info = el('tujuan-info');
            var idTujuan = el('tujuan-id'), labelTujuan = el('tujuan-label'), kodePos = el('kodepos');
            var radios = selProv.form.querySelectorAll('input[name="label"]');
            var daftarProvinsi = [], timer = null;
            var ubah = function () { if (saatDiubah) saatDiubah(); };

            function sama(a, b) { return String(a || '').toLowerCase() === String(b || '').toLowerCase(); }

            async function ambil(url) {
                var res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error(url + ' -> HTTP ' + res.status);
                var json = await res.json();
                return Array.isArray(json) ? json : (json && Array.isArray(json.data) ? json.data : []);
            }

            function isiPilihan(select, items, placeholder, terpilih) {
                select.innerHTML = '';
                select.add(new Option(placeholder, ''));
                items.forEach(function (item) {
                    var opt = new Option(item.name, item.name, false, !!terpilih && sama(item.name, terpilih));
                    opt.dataset.id = item.id;
                    select.add(opt);
                });
                select.disabled = items.length === 0;
            }

            function gagal(select, pesan) {
                select.innerHTML = '';
                select.add(new Option(pesan, ''));
                select.disabled = true;
            }

            async function muatProvinsi(terpilih) {
                try {
                    if (!daftarProvinsi.length) daftarProvinsi = await ambil(URL_PROVINSI);
                    isiPilihan(selProv, daftarProvinsi, 'Pilih Provinsi', terpilih);
                } catch (e) {
                    console.error(e);
                    gagal(selProv, 'Gagal memuat provinsi');
                }
            }

            async function muatKota(terpilih) {
                isiPilihan(selKota, [], 'Pilih Kota/Kabupaten');
                isiPilihan(selKec, [], 'Pilih Kecamatan');
                var opt = selProv.selectedOptions[0];
                if (!opt || !opt.dataset.id) return;
                try {
                    isiPilihan(selKota, await ambil(URL_KOTA.replace('__ID__', opt.dataset.id)), 'Pilih Kota/Kabupaten', terpilih);
                } catch (e) {
                    console.error(e);
                    gagal(selKota, 'Gagal memuat kota');
                }
            }

            async function muatKecamatan(terpilih) {
                isiPilihan(selKec, [], 'Pilih Kecamatan');
                var opt = selKota.selectedOptions[0];
                if (!opt || !opt.dataset.id) return;
                try {
                    isiPilihan(selKec, await ambil(URL_KECAMATAN.replace('__ID__', opt.dataset.id)), 'Pilih Kecamatan', terpilih);
                } catch (e) {
                    console.error(e);
                    gagal(selKec, 'Gagal memuat kecamatan');
                }
            }

            function resetTujuan() {
                idTujuan.value = '';
                labelTujuan.value = '';
                info.textContent = '';
            }

            function tampilHasil(list) {
                kotak.replaceChildren();
                if (!list.length) {
                    var kosong = document.createElement('p');
                    kosong.className = 'px-3 py-2 text-xs text-gray-400';
                    kosong.textContent = 'Tujuan tidak ditemukan.';
                    kotak.appendChild(kosong);
                }
                list.forEach(function (t) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'block w-full border-b border-gray-50 px-3 py-2 text-left text-xs text-gray-700 last:border-0 hover:bg-amber-50';
                    b.textContent = t.label;
                    b.addEventListener('click', function () {
                        idTujuan.value = t.id;
                        labelTujuan.value = t.label;
                        inCari.value = t.label;
                        kotak.classList.add('hidden');
                        info.textContent = 'Tujuan dipilih.';
                        if (t.zip_code) kodePos.value = t.zip_code;
                        ubah();
                    });
                    kotak.appendChild(b);
                });
                kotak.classList.remove('hidden');
            }

            // Cari kelurahan saat mengetik (debounce 350 ms), sama seperti di Checkout
            inCari.addEventListener('input', function () {
                resetTujuan();
                ubah();
                clearTimeout(timer);
                var q = inCari.value.trim();
                if (q.length < 3) { kotak.classList.add('hidden'); return; }
                timer = setTimeout(async function () {
                    try {
                        tampilHasil(await ambil(URL_TUJUAN + '?q=' + encodeURIComponent(q)));
                    } catch (e) {
                        console.error(e);
                        info.textContent = 'Gagal mencari tujuan. Coba lagi.';
                    }
                }, 350);
            });

            document.addEventListener('click', function (e) {
                if (!kotak.contains(e.target) && e.target !== inCari) kotak.classList.add('hidden');
            });

            // Ganti wilayah = tujuan ongkir harus dipilih ulang
            selProv.addEventListener('change', function () { inCari.value = ''; kodePos.value = ''; resetTujuan(); muatKota(); ubah(); });
            selKota.addEventListener('change', function () { inCari.value = ''; kodePos.value = ''; resetTujuan(); muatKecamatan(); ubah(); });
            selKec.addEventListener('change', function () {
                resetTujuan();
                ubah();
                if (selKec.value) {
                    // Isi pencarian kelurahan dengan nama kecamatan lalu cari otomatis, seperti di Checkout
                    inCari.value = selKec.value;
                    inCari.dispatchEvent(new Event('input'));
                } else {
                    inCari.value = '';
                }
            });
            el('alamat').addEventListener('input', ubah);
            kodePos.addEventListener('input', ubah);
            radios.forEach(function (r) { r.addEventListener('change', ubah); });

            return {
                // Isi form dari data: { label, alamat, provinsi, kota_kabupaten, kecamatan, kode_pos, tujuan_id, tujuan_label }
                isi: async function (d) {
                    d = d || {};
                    el('alamat').value = d.alamat || '';
                    kodePos.value = d.kode_pos || '';
                    idTujuan.value = d.tujuan_id || '';
                    labelTujuan.value = d.tujuan_label || '';
                    inCari.value = d.tujuan_label || '';
                    kotak.classList.add('hidden');
                    info.textContent = d.tujuan_label
                        ? 'Tujuan tersimpan.'
                        : (d.provinsi ? 'Kelurahan tujuan belum tersimpan. Cari ulang bila alamat ini diubah.' : '');
                    radios.forEach(function (r) { r.checked = r.value === (d.label || 'Rumah'); });

                    await muatProvinsi(d.provinsi);
                    if (d.provinsi) {
                        await muatKota(d.kota_kabupaten);
                        if (d.kota_kabupaten) await muatKecamatan(d.kecamatan);
                    } else {
                        isiPilihan(selKota, [], 'Pilih Kota/Kabupaten');
                        isiPilihan(selKec, [], 'Pilih Kecamatan');
                    }
                }
            };
        }

        // Modal edit: perubahan apa pun pada alamat menandai ubah_alamat = 1 (server baru menyimpan alamat bila ditandai)
        var alamatEdit = pasangAlamat('ep', function () { $('ep-ubah').value = '1'; });

        function isiFormEdit(id, d) {
            $('form-edit-pengguna').action = URL_UPDATE.replace('__ID__', id);
            $('ep-id').value = id;
            $('ep-name').value = d.name || '';
            $('ep-email').value = d.email || '';
            $('ep-hp').value = d.no_hp || '';
            $('ep-pass').value = '';
            $('ep-ubah').value = String(d.ubah_alamat) === '1' ? '1' : '0';

            var n = parseInt(d.jumlah_alamat || 0, 10);
            $('ep-catatan').textContent = n > 1
                ? 'Pelanggan punya ' + n + ' alamat tersimpan. Perubahan di sini hanya memperbarui alamat utama (menu Alamat Saya).'
                : (n === 1
                    ? 'Perubahan di sini memperbarui alamat utama pelanggan (menu Alamat Saya).'
                    : 'Pelanggan belum punya alamat tersimpan. Jika diisi, disimpan sebagai alamat utama di menu Alamat Saya.');

            alamatEdit.isi(d);
        }

        $('modal-edit-pengguna').addEventListener('modal:open', function (e) {
            var b = e.detail && e.detail.trigger;
            var tombolSimpan = $('btn-simpan-edit');
            tombolSimpan.disabled = false;
            tombolSimpan.textContent = 'Simpan Perubahan';

            if (b && b.dataset.user) {
                isiFormEdit(b.dataset.id, JSON.parse(b.dataset.user));
            } else if (OLD_EDIT) {
                isiFormEdit(OLD_EDIT._id, OLD_EDIT); // dibuka ulang setelah validasi gagal
            }
        });

        $('form-edit-pengguna').addEventListener('submit', function () {
            var t = $('btn-simpan-edit');
            t.disabled = true;
            t.textContent = 'Menyimpan...';
        });

        if (OLD_EDIT) {
            document.addEventListener('DOMContentLoaded', function () { window.openModal('modal-edit-pengguna'); });
        }

        // ---------- Konfirmasi (aksi tunggal & massal) ----------
        var form = $('form-konfirmasi-pengguna');
        var tombol = $('btn-konfirmasi-pengguna');
        var TEKS = {
            nonaktifkan: { judul: 'Nonaktifkan akun?', tombol: 'Ya, nonaktifkan', merah: true,
                pesan: function (n) { return n + ' tidak akan bisa masuk lagi sampai diaktifkan kembali. Sesi login yang sedang berjalan langsung diakhiri.'; },
                catatan: '' },
            aktifkan: { judul: 'Aktifkan akun?', tombol: 'Ya, aktifkan', merah: false,
                pesan: function (n) { return n + ' akan bisa masuk dan berbelanja lagi.'; },
                catatan: '' },
            hapus: { judul: 'Hapus akun permanen?', tombol: 'Ya, hapus', merah: true,
                pesan: function (n) { return n + ' akan dihapus permanen dan tidak bisa dikembalikan.'; },
                catatan: 'Akun yang punya riwayat transaksi tidak bisa dihapus. Nonaktifkan saja akun seperti itu.' }
        };

        function tambahField(wadah, nama, nilai) {
            var i = document.createElement('input');
            i.type = 'hidden';
            i.name = nama;
            i.value = nilai;
            wadah.appendChild(i);
        }

        $('modal-konfirmasi-pengguna').addEventListener('modal:open', function (e) {
            var b = e.detail.trigger;
            if (!b) return;

            var aksi = b.dataset.aksi;
            var bulk = b.dataset.bulk === '1';
            var cfg = TEKS[aksi];
            var wadah = $('konfirmasi-field');
            wadah.innerHTML = '';

            var subjek;
            if (bulk) {
                var ids = Array.prototype.map.call(document.querySelectorAll('.cek-pengguna:checked'), function (c) { return c.value; });
                subjek = ids.length + ' akun terpilih';
                form.action = URL_MASSAL;
                tambahField(wadah, 'aksi', aksi);
                ids.forEach(function (id) { tambahField(wadah, 'ids[]', id); });
            } else {
                subjek = 'Akun "' + b.dataset.nama + '"';
                form.action = b.dataset.url;
                if (aksi === 'hapus') {
                    tambahField(wadah, '_method', 'DELETE');
                } else {
                    tambahField(wadah, '_method', 'PATCH');
                    tambahField(wadah, 'aktif', aksi === 'aktifkan' ? '1' : '0');
                }
            }

            $('modal-konfirmasi-pengguna-title').textContent = cfg.judul;
            $('konfirmasi-pesan').textContent = cfg.pesan(subjek);
            $('konfirmasi-catatan').textContent = cfg.catatan;
            $('konfirmasi-catatan').classList.toggle('hidden', !cfg.catatan);

            tombol.textContent = cfg.tombol;
            tombol.disabled = false;
            tombol.classList.toggle('ui-btn--danger', cfg.merah);
            tombol.classList.toggle('ui-btn--primary', !cfg.merah);
        });

        form.addEventListener('submit', function () {
            tombol.disabled = true;
            tombol.textContent = 'Memproses...';
        });

        $('form-tambah-pengguna').addEventListener('submit', function () {
            var t = $('btn-simpan-pengguna');
            t.disabled = true;
            t.textContent = 'Menyimpan...';
        });

        // ---------- Pilih banyak ----------
        var cekSemua = $('cek-semua');
        var bar = $('bar-massal');

        function semuaCek() { return Array.prototype.slice.call(document.querySelectorAll('.cek-pengguna')); }

        function perbaruiBar() {
            var item = semuaCek();
            var n = item.filter(function (c) { return c.checked; }).length;
            $('jumlah-dipilih').textContent = n;
            bar.classList.toggle('hidden', n === 0);
            bar.classList.toggle('flex', n > 0);
            cekSemua.checked = n > 0 && n === item.length;
            cekSemua.indeterminate = n > 0 && n < item.length;
        }

        cekSemua.addEventListener('change', function () {
            semuaCek().forEach(function (c) { c.checked = cekSemua.checked; });
            perbaruiBar();
        });
        semuaCek().forEach(function (c) { c.addEventListener('change', perbaruiBar); });

        // ---------- Buka lagi modal tambah kalau validasi gagal ----------
        @if ($errors->any() && old('_form') === 'tambah')
            document.addEventListener('DOMContentLoaded', function () { window.openModal('modal-tambah-pengguna'); });
        @endif

        // ---------- Alamat di modal Tambah Akun ----------
        var alamatTambah = pasangAlamat('tp');
        var OLD_TAMBAH = @json(old('_form') === 'tambah' ? old() : null);
        var tambahDimuat = false;

        // Dimuat saat modal pertama kali dibuka (hemat request); isi ulang dari old() bila validasi gagal
        $('modal-tambah-pengguna').addEventListener('modal:open', function () {
            if (tambahDimuat) return;
            tambahDimuat = true;
            alamatTambah.isi(OLD_TAMBAH || {});
        });
    })();
</script>
@endsection