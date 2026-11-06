@extends('admin.layouts.admin')
@section('title', 'Reservasi Fitting')

@section('content')
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
            <p class="text-xs text-gray-500 mt-1">Daftar jadwal kedatangan pelanggan untuk pengukuran busana di toko ALNAY.</p>
        </div>
    </div>

    {{-- Filter & Search --}}
    <form method="GET" action="{{ route('admin.fitting.index') }}" class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode booking atau nama..."
                   class="w-full pl-10 pr-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                   class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all cursor-pointer">

            <button type="submit"
                    class="bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-amber-500/20 active:scale-95 transition-all duration-200 cursor-pointer">Cari</button>
            <a href="{{ route('admin.fitting.index') }}" class="text-xs text-gray-500 hover:text-amber-600 font-bold ml-2 transition-colors">Reset</a>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pelanggan</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Jadwal</th>
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
                                ? (\Illuminate\Support\Str::startsWith($fotoUser, ['http://', 'https://']) ? $fotoUser : asset('storage/' . $fotoUser)) 
                                : null;
                        @endphp
                        <tr id="baris-{{ $r->id }}" class="hover:bg-amber-50/30 transition-colors">
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
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($r->slot->tanggal)->format('d M Y') }}</div>
                                <div class="text-[11px] text-amber-600 font-bold">{{ $r->slot->jam_mulai ?? '-' }} WIB</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black font-mono bg-gray-900 text-white tracking-wider shadow-sm">
                                    {{ $r->kode_booking }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badge = match(strtolower($r->status)) {
                                        'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'hadir' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'batal' => 'bg-rose-100 text-rose-700 border-rose-200',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $badge }}">
                                    {{ $r->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form id="form-status-{{ $r->id }}" action="{{ route('admin.fitting.update-status', $r->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="simpanPosisiScroll(this.closest('tr')); this.form.submit()" aria-label="Ubah status reservasi"
                                            class="px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-[11px] font-bold cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all hover:border-amber-300">
                                        <option value="pending" {{ $r->status == 'pending' ? 'selected' : '' }}>Tandai Pending</option>
                                        <option value="hadir" {{ $r->status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                        <option value="batal" {{ $r->status == 'batal' ? 'selected' : '' }}>Batal</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">Belum ada jadwal reservasi fitting.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $reservasis->links() }}
        </div>
    </div>
</div>

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