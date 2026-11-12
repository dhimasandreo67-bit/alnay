@extends('admin.layouts.admin')
@section('title', 'Reservasi Fitting')

@section('content')
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    $urlFoto = function ($produk) {
        $foto = $produk->foto_produk ?? null;

        if (is_string($foto)) {
            $dec = json_decode($foto, true);
            if (is_array($dec)) {
                $foto = $dec;
            }
        }
        if (is_array($foto)) {
            $foto = $foto[0] ?? null;
        }
        if (! $foto) {
            return null;
        }

        $foto = (string) $foto;
        if (Str::startsWith($foto, ['http://', 'https://', 'data:'])) {
            return $foto;
        }

        $foto = ltrim(preg_replace('#^(public/|storage/)#', '', ltrim($foto, '/')), '/');

        return asset('storage/' . $foto);
    };

    $adaFilter = filled(request('search')) || filled(request('tanggal'));
@endphp

<div class="p-6 space-y-6 transition-all duration-500 animate-fade-in relative">

    {{-- CONTAINER TOAST NOTIFIKASI (Dari kode Anda) --}}
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>

    {{-- Header & Tombol Hapus Massal (Dari kode Anda) --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Reservasi Fitting</h1>
            <p class="text-xs text-gray-500 mt-1">Jadwal kedatangan pelanggan untuk fitting di toko ALNAY. Booking langsung terjadwal, tugas admin hanya menandai Hadir atau Batal.</p>
        </div>
        
        <button id="btnBulkDelete" class="hidden bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-rose-500/20 active:scale-95 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            Hapus Terpilih (<span id="countSelected">0</span>)
        </button>
    </div>

    {{-- Filter: otomatis, tanpa tombol Cari (Gabungan) --}}
    <form id="form-filter" method="GET" action="{{ route('admin.fitting.index') }}" class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-96">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input id="input-cari" type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                   placeholder="Cari kode booking, nama, atau nomor HP..."
                   class="w-full pl-10 pr-10 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
            <span id="spinner" class="hidden absolute inset-y-0 right-0 items-center pr-3.5">
                <svg class="w-4 h-4 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            </span>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <input id="input-tanggal" type="date" name="tanggal" value="{{ request('tanggal') }}"
                   class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all cursor-pointer">
            <button type="button" id="btn-reset" class="text-xs text-gray-500 hover:text-amber-600 font-bold ml-2 transition-colors cursor-pointer">Reset</button>
        </div>
    </form>

    {{-- Area yang diperbarui otomatis --}}
    <div id="area-tabel" class="transition-opacity duration-200">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between text-[11px] text-gray-500">
                <span>
                    @if (method_exists($reservasis, 'total'))
                        Menampilkan <b class="text-gray-700">{{ $reservasis->count() }}</b> dari <b class="text-gray-700">{{ $reservasis->total() }}</b> reservasi
                    @else
                        <b class="text-gray-700">{{ $reservasis->count() }}</b> reservasi
                    @endif
                    @if (filled(request('search')))
                        · kata kunci “<b class="text-gray-700">{{ request('search') }}</b>”
                    @endif
                    @if (filled(request('tanggal')))
                        · tanggal <b class="text-gray-700">{{ \Carbon\Carbon::parse(request('tanggal'))->format('d M Y') }}</b>
                    @endif
                </span>
                <span class="hidden sm:inline">Kuota: maks. 2 tamu per jam</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            {{-- CHECKBOX SELECT ALL (Dari kode Anda) --}}
                            <th class="px-6 py-4 text-left w-10">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 text-amber-600 bg-gray-100 border-gray-300 rounded focus:ring-amber-500 cursor-pointer">
                            </th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pelanggan</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Jadwal</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Busana yang Disewa</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Kode Booking</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/80 bg-white">
                        @forelse ($reservasis as $r)
                            @php
                                $namaPelanggan = $r->user->name ?? 'User Terhapus';
                                $inisial = strtoupper(mb_substr(trim($namaPelanggan) ?: '?', 0, 1));

                                $fotoUser = $r->user->foto_profil ?? null;
                                $fotoUserUrl = $fotoUser
                                    ? (Str::startsWith($fotoUser, ['http://', 'https://']) ? $fotoUser : asset('storage/' . $fotoUser))
                                    : null;

                                // Jadwal
                                $tgl = $r->slot ? \Carbon\Carbon::parse($r->slot->tanggal) : null;
                                $jam = $r->slot?->jam_mulai ? \Carbon\Carbon::parse($r->slot->jam_mulai)->format('H:i') : '-';

                                // Status: tidak ada "pending". 'pending'/'disetujui' (data lama) = Terjadwal.
                                $st = strtolower($r->status);
                                $terjadwal = ! in_array($st, ['hadir', 'batal'], true);
                                $nilaiSelect = $terjadwal ? 'pending' : $st;
                                $hariIni = $tgl && $tgl->isToday();
                                $terlewat = $terjadwal && $tgl && $tgl->copy()->endOfDay()->isPast();

                                $badge = match (true) {
                                    $st === 'hadir' => ['bg-emerald-100 text-emerald-700 border-emerald-200', 'Hadir'],
                                    $st === 'batal' => ['bg-rose-100 text-rose-700 border-rose-200', 'Batal'],
                                    default => ['bg-sky-100 text-sky-700 border-sky-200', 'Terjadwal'],
                                };

                                // Busana
                                $trx = method_exists($r, 'transaksi') ? $r->transaksi : null;
                                $busana = [];
                                if ($trx && $trx->detail->isNotEmpty()) {
                                    foreach ($trx->detail as $d) {
                                        $busana[] = [
                                            'nama' => $d->produk->nama ?? 'Busana',
                                            'foto' => $d->produk ? $urlFoto($d->produk) : null,
                                            'ukuran' => $d->ukuran,
                                            'warna' => $d->warna,
                                        ];
                                    }
                                } elseif ($r->produk) {
                                    $busana[] = [
                                        'nama' => $r->produk->nama,
                                        'foto' => $urlFoto($r->produk),
                                        'ukuran' => null,
                                        'warna' => null,
                                    ];
                                }

                                $sewa = $trx?->sewa;
                                $catatan = collect(preg_split('/\R/', (string) $r->catatan))
                                    ->reject(fn ($l) => Str::startsWith(trim($l), 'Busana dalam pesanan:'))
                                    ->filter()->implode(' ');
                            @endphp
                            <tr id="baris-{{ $r->id }}" class="hover:bg-amber-50/30 transition-colors align-top row-data" data-id="{{ $r->id }}">
                                
                                {{-- CHECKBOX PER BARIS (Dari kode Anda) --}}
                                <td class="px-6 py-4">
                                    @if($r->status !== 'hadir')
                                        <input type="checkbox" value="{{ $r->id }}" class="row-checkbox w-4 h-4 text-amber-600 bg-gray-100 border-gray-300 rounded focus:ring-amber-500 cursor-pointer">
                                    @else
                                        <input type="checkbox" disabled title="Data hadir tidak bisa dihapus" class="w-4 h-4 text-gray-300 bg-gray-100 border-gray-200 rounded cursor-not-allowed">
                                    @endif
                                </td>

                                {{-- Pelanggan --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($fotoUserUrl)
                                            <img src="{{ $fotoUserUrl }}" alt="{{ $namaPelanggan }}" class="h-10 w-10 shrink-0 rounded-2xl object-cover border border-gray-200">
                                        @else
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black border bg-amber-50 text-amber-700 border-amber-100">{{ $inisial }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $namaPelanggan }}</p>
                                            <p class="text-xs text-gray-500">{{ $r->user->no_hp ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Jadwal --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-800">{{ $tgl ? $tgl->format('d M Y') : '-' }}</div>
                                    <div class="text-[11px] text-amber-600 font-bold">{{ $jam }} WIB</div>
                                    @if ($hariIni && $terjadwal)
                                        <span class="inline-block mt-1 px-1.5 py-0.5 rounded bg-amber-500 text-white text-[9px] font-black uppercase tracking-wider">Hari ini</span>
                                    @endif
                                </td>

                                {{-- Busana --}}
                                <td class="px-6 py-4">
                                    @if (count($busana))
                                        <div class="space-y-2">
                                            @foreach (array_slice($busana, 0, 3) as $b)
                                                <div class="flex items-center gap-2.5">
                                                    <div class="relative h-11 w-11 shrink-0 rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                                                        @if ($b['foto'])
                                                            <img src="{{ $b['foto'] }}" alt="{{ $b['nama'] }}" loading="lazy" class="relative z-10 h-full w-full object-cover bg-white"
                                                                 onerror="this.remove()">
                                                        @endif
                                                        <svg class="w-4 h-4 text-gray-300 absolute inset-0 m-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4-4a3 3 0 014 0l4 4m-2-2l1-1a3 3 0 014 0l2 2M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-[13px] font-bold text-gray-800 truncate max-w-[200px]">{{ $b['nama'] }}</p>
                                                        <div class="flex flex-wrap gap-1 mt-0.5">
                                                            @if ($b['ukuran'])
                                                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-[10px] font-semibold text-gray-600">Ukuran {{ $b['ukuran'] }}</span>
                                                            @endif
                                                            @if ($b['warna'])
                                                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-[10px] font-semibold text-gray-600">{{ $b['warna'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if (count($busana) > 3)
                                                <p class="text-[10px] text-gray-400">+{{ count($busana) - 3 }} busana lainnya</p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Fitting umum (tanpa pesanan)</span>
                                    @endif

                                    @if ($sewa)
                                        <p class="text-[10px] text-gray-500 mt-2">
                                            Ambil <b class="text-gray-700">{{ \Carbon\Carbon::parse($sewa->tanggal_pinjam)->format('d M Y') }}</b>
                                            · Kembali <b class="text-gray-700">{{ \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->format('d M Y') }}</b>
                                        </p>
                                    @endif
                                    @if ($trx)
                                        <p class="text-[10px] text-gray-400 mt-0.5">Pesanan {{ $trx->kode_transaksi }}</p>
                                    @endif
                                    @if ($catatan)
                                        <p class="text-[10px] text-gray-500 italic mt-1 max-w-[260px] truncate" title="{{ $catatan }}">“{{ $catatan }}”</p>
                                    @endif
                                </td>

                                {{-- Kode --}}
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black font-mono bg-gray-900 text-white tracking-wider shadow-sm">
                                        {{ $r->kode_booking }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider status-badge {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                    @if ($terlewat)
                                        <p class="mt-1 text-[10px] font-semibold text-amber-600">Jadwal terlewat, tandai Hadir / Batal</p>
                                    @endif
                                </td>

                                {{-- Aksi (Gabungan: AJAX + Hapus) --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('admin.fitting.update-status', $r->id) }}" method="POST" class="inline-block form-status">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" data-original="{{ $nilaiSelect }}" aria-label="Ubah status reservasi"
                                                    class="px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-[11px] font-bold cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all hover:border-amber-300">
                                                <option value="pending" {{ $nilaiSelect === 'pending' ? 'selected' : '' }}>Terjadwal</option>
                                                <option value="hadir" {{ $nilaiSelect === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                <option value="batal" {{ $nilaiSelect === 'batal' ? 'selected' : '' }}>Batal</option>
                                            </select>
                                        </form>

                                        @if ($r->status !== 'hadir')
                                            <form action="{{ route('admin.fitting.destroy', $r->id) }}" method="POST" class="inline-block form-delete btn-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-rose-500 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors border border-rose-100" title="Hapus Reservasi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
                                    @if ($adaFilter)
                                        Tidak ada reservasi yang cocok dengan pencarian Anda.
                                    @else
                                        Belum ada jadwal reservasi fitting.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="paginasi" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $reservasis->links() }}
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT 1: PENCARIAN OTOMATIS DARI TEMAN ANDA --}}
<script>
(function () {
    const form = document.getElementById('form-filter');
    const inputCari = document.getElementById('input-cari');
    const inputTanggal = document.getElementById('input-tanggal');
    const area = document.getElementById('area-tabel');
    const spinner = document.getElementById('spinner');
    let timer = null;
    let kontrol = null;

    function buatUrl(halaman) {
        const p = new URLSearchParams();
        const kata = inputCari.value.trim();
        if (kata) p.set('search', kata);
        if (inputTanggal.value) p.set('tanggal', inputTanggal.value);
        if (halaman && Number(halaman) > 1) p.set('page', halaman);
        const q = p.toString();
        return form.getAttribute('action') + (q ? '?' + q : '');
    }

    function tampilkanMuat(aktif) {
        area.classList.toggle('opacity-50', aktif);
        spinner.classList.toggle('hidden', !aktif);
        spinner.classList.toggle('flex', aktif);
    }

    async function muat(url) {
        if (kontrol) kontrol.abort();
        kontrol = new AbortController();
        tampilkanMuat(true);

        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                signal: kontrol.signal,
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);

            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const baru = doc.getElementById('area-tabel');
            if (!baru) throw new Error('Area tabel tidak ditemukan');

            area.innerHTML = baru.innerHTML;
            
            // Re-inisialisasi event listener untuk tabel yang baru dimuat (Penting untuk AJAX)
            if (typeof initTableEvents === 'function') initTableEvents();

            history.replaceState(null, '', url);
            tampilkanMuat(false);
        } catch (e) {
            if (e.name === 'AbortError') return;
            window.location.href = url;
        }
    }

    inputCari.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { muat(buatUrl()); }, 300);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(timer);
        muat(buatUrl());
    });

    inputTanggal.addEventListener('change', function () { muat(buatUrl()); });

    document.getElementById('btn-reset').addEventListener('click', function () {
        inputCari.value = '';
        inputTanggal.value = '';
        muat(buatUrl());
        inputCari.focus();
    });

    area.addEventListener('click', function (e) {
        const a = e.target.closest('a[href]');
        if (!a || !a.closest('#paginasi')) return;
        e.preventDefault();
        muat(buatUrl(new URL(a.href, location.origin).searchParams.get('page')));
    });
})();
</script>

{{-- SCRIPT 2: AJAX HAPUS, UBAH STATUS, & CHECKBOX DARI ANDA --}}
<script>
    // Bungkus ke dalam fungsi agar bisa dipanggil ulang saat tabel di-reload via AJAX
    function initTableEvents() {
        // --- FUNGSI TOAST NOTIFIKASI ---
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-rose-500';
            const icon = type === 'success' 
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
            
            toast.className = `${bgColor} text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-full opacity-0`;
            toast.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">${icon}</svg>
                <span class="text-sm font-bold">${message}</span>
            `;
            
            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-x-full', 'opacity-0'), 10);
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // --- LOGIKA HAPUS SATUAN (AJAX) ---
        document.querySelectorAll('.form-delete').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!confirm('Yakin ingin menghapus reservasi ini?')) return;

                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.closest('tr').remove();
                        showToast(data.message || 'Data berhasil dihapus', 'success');
                    } else {
                        showToast(data.message || 'Gagal menghapus', 'error');
                    }
                });
            });
        });

        // --- LOGIKA UBAH STATUS (AJAX) ---
        document.querySelectorAll('.form-status select').forEach(select => {
            select.addEventListener('change', function() {
                const form = this.closest('form');
                const row = this.closest('tr');
                const badge = row.querySelector('.status-badge');
                const newStatus = this.value;
                const originalStatus = this.dataset.original;

                this.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan server');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        badge.textContent = newStatus === 'pending' ? 'Terjadwal' : (newStatus === 'hadir' ? 'Hadir' : 'Batal');
                        
                        badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider status-badge ';
                        if (newStatus === 'pending') badge.classList.add('bg-sky-100', 'text-sky-700', 'border-sky-200');
                        else if (newStatus === 'hadir') badge.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-200');
                        else if (newStatus === 'batal') badge.classList.add('bg-rose-100', 'text-rose-700', 'border-rose-200');

                        this.dataset.original = newStatus;
                        
                        const deleteBtn = row.querySelector('.btn-delete');
                        if (deleteBtn) deleteBtn.style.display = newStatus === 'hadir' ? 'none' : 'inline-block';
                        const checkbox = row.querySelector('.row-checkbox');
                        if(checkbox) checkbox.parentElement.innerHTML = newStatus === 'hadir' ? '<input type="checkbox" disabled class="w-4 h-4 text-gray-300 bg-gray-100 border-gray-200 rounded cursor-not-allowed">' : '<input type="checkbox" value="'+row.dataset.id+'" class="row-checkbox w-4 h-4 text-amber-600 bg-gray-100 border-gray-300 rounded focus:ring-amber-500 cursor-pointer">';

                        showToast(data.message, 'success');
                    }
                })
                .catch(err => {
                    this.value = originalStatus;
                    showToast(err.message || 'Gagal mengubah status', 'error');
                })
                .finally(() => {
                    this.disabled = false;
                });
            });
        });

        // --- LOGIKA CHECKBOX & BULK DELETE ---
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const countSelected = document.getElementById('countSelected');

        function updateBulkBtn() {
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            countSelected.textContent = checked;
            if (checked > 0) {
                btnBulkDelete.classList.remove('hidden');
            } else {
                btnBulkDelete.classList.add('hidden');
            }
        }

        if (selectAll) {
            // Hapus event listener lama jika ada (mencegah dobel)
            selectAll.replaceWith(selectAll.cloneNode(true));
            const newSelectAll = document.getElementById('selectAll');
            
            newSelectAll.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
                updateBulkBtn();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.replaceWith(cb.cloneNode(true)); // Reset listener
        });
        
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const total = document.querySelectorAll('.row-checkbox').length;
                const checked = document.querySelectorAll('.row-checkbox:checked').length;
                if(document.getElementById('selectAll')) document.getElementById('selectAll').checked = total === checked;
                updateBulkBtn();
            });
        });

        if (btnBulkDelete) {
            btnBulkDelete.replaceWith(btnBulkDelete.cloneNode(true));
            document.getElementById('btnBulkDelete').addEventListener('click', function() {
                const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
                if (ids.length === 0) return;
                
                if (!confirm(`Yakin ingin menghapus ${ids.length} reservasi terpilih?`)) return;

                fetch("{{ route('admin.fitting.bulk-destroy') }}", {
                    method: 'POST',
                    body: JSON.stringify({ ids: ids, _token: "{{ csrf_token() }}", _method: "DELETE" }),
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        ids.forEach(id => {
                            const row = document.querySelector(`.row-data[data-id="${id}"]`);
                            if (row) row.remove();
                        });
                        if(document.getElementById('selectAll')) document.getElementById('selectAll').checked = false;
                        updateBulkBtn();
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message, 'error');
                    }
                });
            });
        }
    }

    // Jalankan saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', initTableEvents);
</script>
@endsection