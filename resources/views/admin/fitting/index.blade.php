@extends('admin.layouts.admin')
@section('title', 'Reservasi Fitting')

@section('content')
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    /**
     * URL foto produk yang tahan terhadap berbagai format penyimpanan:
     * array / JSON string / path relatif / "storage/..." / "public/..." / URL penuh.
     */
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

<div class="p-6 space-y-6 transition-all duration-500 animate-fade-in">

    {{-- ALERT --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
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
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Reservasi Fitting</h1>
            <p class="text-xs text-gray-500 mt-1">Jadwal kedatangan pelanggan untuk fitting di toko ALNAY. Booking langsung terjadwal, tugas admin hanya menandai Hadir atau Batal.</p>
        </div>
    </div>

    {{-- Filter: otomatis, tanpa tombol Cari --}}
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

    {{-- Area yang diperbarui otomatis saat mengetik / ganti tanggal --}}
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
                        · tanggal <b class="text-gray-700">{{ Carbon::parse(request('tanggal'))->format('d M Y') }}</b>
                    @endif
                </span>
                <span class="hidden sm:inline">Kuota: maks. 2 tamu per jam</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
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
                                $tgl = $r->slot ? Carbon::parse($r->slot->tanggal) : null;
                                $jam = $r->slot?->jam_mulai ? Carbon::parse($r->slot->jam_mulai)->format('H:i') : '-';

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

                                // Busana: dari pesanan (lengkap dengan ukuran & warna). Cadangan: produk di reservasi.
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
                            <tr id="baris-{{ $r->id }}" class="hover:bg-amber-50/30 transition-colors align-top">
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
                                            Ambil <b class="text-gray-700">{{ Carbon::parse($sewa->tanggal_pinjam)->format('d M Y') }}</b>
                                            · Kembali <b class="text-gray-700">{{ Carbon::parse($sewa->tanggal_kembali_rencana)->format('d M Y') }}</b>
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                    @if ($terlewat)
                                        <p class="mt-1 text-[10px] font-semibold text-amber-600">Jadwal terlewat, tandai Hadir / Batal</p>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-6 py-4 text-right">
                                    <form id="form-status-{{ $r->id }}" action="{{ route('admin.fitting.update-status', $r->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="simpanPosisiScroll(this.closest('tr')); this.form.submit()" aria-label="Ubah status reservasi"
                                                class="px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-[11px] font-bold cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all hover:border-amber-300">
                                            <option value="pending" {{ $nilaiSelect === 'pending' ? 'selected' : '' }}>Terjadwal</option>
                                            <option value="hadir" {{ $nilaiSelect === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="batal" {{ $nilaiSelect === 'batal' ? 'selected' : '' }}>Batal</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
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
            history.replaceState(null, '', url);
            tampilkanMuat(false);
        } catch (e) {
            if (e.name === 'AbortError') return;   // diganti permintaan yang lebih baru
            window.location.href = url;            // gagal: muat biasa saja
        }
    }

    // Ketik -> tunggu sebentar -> data muncul otomatis
    inputCari.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { muat(buatUrl()); }, 300);
    });

    // Enter tidak me-refresh halaman
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(timer);
        muat(buatUrl());
    });

    // Ganti tanggal -> langsung terfilter
    inputTanggal.addEventListener('change', function () { muat(buatUrl()); });

    document.getElementById('btn-reset').addEventListener('click', function () {
        inputCari.value = '';
        inputTanggal.value = '';
        muat(buatUrl());
        inputCari.focus();
    });

    // Pagination ikut diperbarui tanpa reload (filter tetap terbawa)
    area.addEventListener('click', function (e) {
        const a = e.target.closest('a[href]');
        if (!a || !a.closest('#paginasi')) return;
        e.preventDefault();
        muat(buatUrl(new URL(a.href, location.origin).searchParams.get('page')));
    });
})();
</script>

<script>
(function () {
    const KUNCI = 'fittingScrollState';

    // Cari wadah yang benar-benar bisa di-scroll secara vertikal (bukan selalu window).
    function cariWadah(el) {
        let node = el ? el.parentElement : null;
        while (node && node !== document.body) {
            const oy = getComputedStyle(node).overflowY;
            if ((oy === 'auto' || oy === 'scroll') && node.scrollHeight > node.clientHeight) return node;
            node = node.parentElement;
        }
        return document.scrollingElement || document.documentElement;
    }

    function atasWadah(wadah) {
        return (wadah === document.scrollingElement || wadah === document.documentElement)
            ? 0
            : wadah.getBoundingClientRect().top;
    }

    // Dipanggil sebelum form dikirim: catat baris yang diubah dan jaraknya dari atas layar.
    window.simpanPosisiScroll = function (baris) {
        if (!baris) return;
        const wadah = cariWadah(baris);
        sessionStorage.setItem(KUNCI, JSON.stringify({
            path: location.pathname,
            barisId: baris.id,
            offset: baris.getBoundingClientRect().top - atasWadah(wadah),
            top: wadah.scrollTop,
        }));
    };

    function pulihkan() {
        const raw = sessionStorage.getItem(KUNCI);
        if (!raw) return;

        let s;
        try { s = JSON.parse(raw); } catch (e) { sessionStorage.removeItem(KUNCI); return; }
        if (s.path !== location.pathname) { sessionStorage.removeItem(KUNCI); return; }

        const tabel = document.querySelector('table');
        if (!tabel) return;

        const terapkan = function () {
            const wadah = cariWadah(tabel);
            const baris = document.getElementById(s.barisId);

            if (baris) {
                // Jaga baris yang sama tetap di jarak yang sama dari atas,
                // walau ada alert "berhasil" yang menambah tinggi halaman.
                const selisih = (baris.getBoundingClientRect().top - atasWadah(wadah)) - s.offset;
                wadah.scrollTop += selisih;
            } else {
                wadah.scrollTop = s.top;
            }
        };

        terapkan();

        // Ulangi beberapa kali untuk mengalahkan layout shift (font/gambar telat muat).
        let n = 0;
        const timer = setInterval(function () {
            terapkan();
            if (++n >= 8) {
                clearInterval(timer);
                sessionStorage.removeItem(KUNCI);
            }
        }, 100);
    }

    pulihkan();
})();
</script>
@endsection