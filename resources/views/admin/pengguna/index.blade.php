@extends('admin.layouts.admin')
@section('title', 'Kelola Pengguna')

@section('content')
@php
    $tgl = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->locale('id') : null;
    $adaFilter = request()->filled('search') || $status !== '' || $inaktif > 0;
@endphp

<div class="p-6 space-y-6 transition-all duration-500 animate-fade-in">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">Kelola Pengguna</h1>
            <p class="text-xs text-gray-500 mt-1">Pantau akun pelanggan, tinjau yang lama tidak aktif, lalu nonaktifkan atau hapus bila perlu.</p>
        </div>
        <button type="button" data-modal-open="modal-tambah-pengguna"
                class="inline-flex items-center gap-2 bg-gray-900 text-white px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-gray-800 active:scale-95 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Akun Manual
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Pengguna</p>
            <h3 class="text-2xl font-black text-gray-900 mt-1 tabular-nums">{{ $ringkasan['total'] }}</h3>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Akun Aktif</p>
            <h3 class="text-2xl font-black text-emerald-600 mt-1 tabular-nums">{{ $ringkasan['aktif'] }}</h3>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Dinonaktifkan</p>
            <h3 class="text-2xl font-black text-gray-700 mt-1 tabular-nums">{{ $ringkasan['nonaktif'] }}</h3>
        </div>
        <a href="{{ route('admin.pengguna.index', ['inaktif' => $batasLama, 'status' => 'aktif']) }}"
           class="bg-white p-5 rounded-3xl border border-amber-100 shadow-sm transition hover:border-amber-300 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
            <p class="text-[11px] font-bold text-amber-700/80 uppercase tracking-wider">Tidak Aktif &ge; {{ $batasLama }} Hari</p>
            <h3 class="text-2xl font-black text-amber-600 mt-1 tabular-nums">{{ $ringkasan['lama'] }}</h3>
            <p class="text-[10px] text-gray-400 mt-0.5">Klik untuk meninjau</p>
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
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-black {{ $aktif ? 'bg-amber-100 text-amber-700' : 'bg-gray-200 text-gray-500' }}">{{ $inisial }}</span>
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
                                    <span class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" data-modal-open="modal-detail-pengguna" data-id="{{ $u->id }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 active:scale-95 transition-all shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                                        Detail
                                    </button>

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
            <span id="dt-inisial" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-600 to-amber-400 text-lg font-black text-white"></span>
            <div class="min-w-0 flex-1">
                <p id="dt-nama" class="truncate text-base font-bold text-gray-900"></p>
                <p id="dt-email" class="truncate text-xs text-gray-500"></p>
                <p id="dt-hp" class="text-xs text-gray-500"></p>
            </div>
            <span id="dt-status" class="shrink-0 rounded-lg border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"></span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-2xl bg-gray-50 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Transaksi</p><p id="dt-transaksi" class="mt-1 text-sm font-black text-gray-900"></p></div>
            <div class="rounded-2xl bg-gray-50 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Belanja</p><p id="dt-belanja" class="mt-1 text-sm font-black tabular-nums text-gray-900"></p></div>
            <div class="rounded-2xl bg-gray-50 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Bergabung</p><p id="dt-bergabung" class="mt-1 text-xs font-bold text-gray-900"></p></div>
            <div class="rounded-2xl bg-gray-50 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Terakhir Aktif</p><p id="dt-terakhir" class="mt-1 text-xs font-bold text-gray-900"></p><p id="dt-terakhir-rel" class="text-[10px] text-gray-400"></p></div>
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

        {{-- Alamat & wilayah — opsional, sama seperti field di halaman Profil pelanggan --}}
        <div>
            <label for="tp-alamat" class="text-xs font-bold text-gray-500">Alamat lengkap (opsional)</label>
            <textarea id="tp-alamat" name="alamat" rows="2" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm resize-none">{{ old('alamat') }}</textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="tp-provinsi" class="text-xs font-bold text-gray-500">Provinsi</label>
                <select id="tp-provinsi" name="provinsi" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                    <option value="">Pilih Provinsi</option>
                </select>
            </div>
            <div>
                <label for="tp-kota" class="text-xs font-bold text-gray-500">Kota/Kabupaten</label>
                <select id="tp-kota" name="kota_kabupaten" disabled class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">Pilih Kota/Kabupaten</option>
                </select>
            </div>
            <div>
                <label for="tp-kecamatan" class="text-xs font-bold text-gray-500">Kecamatan</label>
                <select id="tp-kecamatan" name="kecamatan" disabled class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">Pilih Kecamatan</option>
                </select>
            </div>
        </div>
        <div>
            <label for="tp-kodepos" class="text-xs font-bold text-gray-500">Kode pos (opsional)</label>
            <input type="text" id="tp-kodepos" name="kode_pos" value="{{ old('kode_pos') }}" maxlength="10" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
        </div>

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

        // ---------- Wilayah berjenjang (provinsi -> kota -> kecamatan) di modal Tambah Akun ----------
        // Logikanya sama seperti wilayahForm() di halaman Profil pelanggan, cuma
        // ditulis plain JS (bukan Alpine) supaya tidak nambah dependency di panel admin.
        (function () {
            var selProv = $('tp-provinsi');
            var selKota = $('tp-kota');
            var selKec = $('tp-kecamatan');
            if (!selProv || !selKota || !selKec) return;

            var provinces = [];
            var cities = [];
            var sudahDimuat = false;

            var provinsiLama = @json(old('provinsi'));
            var kotaLama = @json(old('kota_kabupaten'));
            var kecamatanLama = @json(old('kecamatan'));

            function toList(json) {
                if (Array.isArray(json)) return json;
                if (json && Array.isArray(json.data)) return json.data;
                return [];
            }
            function idOf(item) { return item.id ?? item.code ?? item.kode; }
            function sama(a, b) { return (a || '').toString().toLowerCase() === (b || '').toString().toLowerCase(); }

            function ambilDaftar(url) {
                return fetch(url, { headers: { 'Accept': 'application/json' } }).then(function (res) {
                    if (!res.ok) throw new Error(url + ' -> HTTP ' + res.status);
                    return res.json();
                }).then(toList);
            }

            function isiSelect(select, items, placeholder, namaTerpilih) {
                select.innerHTML = '';
                var kosong = document.createElement('option');
                kosong.value = '';
                kosong.textContent = placeholder;
                select.appendChild(kosong);
                items.forEach(function (item) {
                    var opt = document.createElement('option');
                    opt.value = item.name;
                    opt.textContent = item.name;
                    if (namaTerpilih && sama(item.name, namaTerpilih)) opt.selected = true;
                    select.appendChild(opt);
                });
                select.disabled = items.length === 0;
            }

            function muatKota(namaProvinsi, kotaTerpilih) {
                cities = [];
                isiSelect(selKota, [], 'Pilih Kota/Kabupaten');
                isiSelect(selKec, [], 'Pilih Kecamatan');

                var prov = provinces.filter(function (p) { return sama(p.name, namaProvinsi); })[0];
                if (!prov) return Promise.resolve();

                return ambilDaftar('/api/wilayah/cities/' + idOf(prov)).then(function (list) {
                    cities = list;
                    isiSelect(selKota, cities, 'Pilih Kota/Kabupaten', kotaTerpilih);
                }).catch(function (err) { console.error('Gagal memuat kota:', err); });
            }

            function muatKecamatan(namaKota, kecamatanTerpilih) {
                isiSelect(selKec, [], 'Pilih Kecamatan');

                var kota = cities.filter(function (c) { return sama(c.name, namaKota); })[0];
                if (!kota) return Promise.resolve();

                return ambilDaftar('/api/wilayah/districts/' + idOf(kota)).then(function (list) {
                    isiSelect(selKec, list, 'Pilih Kecamatan', kecamatanTerpilih);
                }).catch(function (err) { console.error('Gagal memuat kecamatan:', err); });
            }

            function muatProvinsi() {
                if (sudahDimuat) return;
                sudahDimuat = true;

                ambilDaftar('/api/wilayah/provinces').then(function (list) {
                    provinces = list;
                    isiSelect(selProv, provinces, 'Pilih Provinsi', provinsiLama);

                    if (provinsiLama) {
                        return muatKota(provinsiLama, kotaLama).then(function () {
                            if (kotaLama) return muatKecamatan(kotaLama, kecamatanLama);
                        });
                    }
                }).catch(function (err) { console.error('Gagal memuat provinsi:', err); });
            }

            selProv.addEventListener('change', function (e) { muatKota(e.target.value); });
            selKota.addEventListener('change', function (e) { muatKecamatan(e.target.value); });

            // Muat daftar provinsi begitu modal tambah akun dibuka (sekali saja, biar hemat request)
            $('modal-tambah-pengguna').addEventListener('modal:open', muatProvinsi);

            // Kalau validasi server gagal dan modal ini otomatis kebuka lagi, langsung muat juga
            @if ($errors->any() && old('_form') === 'tambah')
                muatProvinsi();
            @endif
        })();
    })();
</script>
@endsection