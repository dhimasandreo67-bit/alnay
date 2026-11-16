@extends('admin.layouts.admin')
@section('title', 'Rating & Ulasan')

@section('content')
    @php
        $jumlahFilter =
            collect(['q', 'produk_id', 'komentar', 'dari', 'sampai', 'bintang'])
                ->filter(fn($k) => request()->filled($k))
                ->count() + (request('urut', 'terbaru') !== 'terbaru' ? 1 : 0);

        $urutAktif = request('urut', 'terbaru');
        $bintangAktif = (string) request('bintang');
        $kelasInput =
            'w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all';
        $pathBintang =
            'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z';
    @endphp

    <div class="p-6 space-y-6 max-w-6xl mx-auto transition-all duration-500 animate-fade-in">

        <style>
            .group-shine { position: relative; overflow: hidden; }
            .group-shine::after {
                content: ''; position: absolute; top: -50%; left: -60%; width: 20%; height: 200%;
                background: rgba(255, 255, 255, 0.25); transform: rotate(30deg); transition: all 0.6s ease;
            }
            .group-shine:hover::after { left: 130%; }
            #dlg-hapus-rating::backdrop { background: rgba(17, 24, 39, 0.45); backdrop-filter: blur(2px); }
        </style>

        {{-- Flash messages (server) --}}
        @if (session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm" role="alert">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                </div>
                <p class="text-sm font-bold">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm" role="alert">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <p class="text-sm font-bold">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Rating & Ulasan</h1>
            <p class="text-xs text-gray-500 mt-1">Pantau kepuasan pelanggan terhadap busana dan layanan ALNAY.ID.</p>
        </div>

        {{-- Statistik (ikut diperbarui saat filter / hapus) --}}
        <div id="area-statistik" class="grid grid-cols-1 gap-5 sm:grid-cols-3">

            {{-- Rata-rata Rating --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Rata-rata Rating</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $pathBintang }}" /></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-amber-950 tabular-nums">{{ number_format($rataRata, 1) }}</span>
                    <span class="text-sm font-bold text-amber-800/60">/ 5.0</span>
                </div>
                <div class="mt-2 flex items-center gap-0.5" aria-hidden="true">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="h-3.5 w-3.5 {{ $i <= round($rataRata) ? 'text-amber-400' : 'text-amber-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $pathBintang }}" /></svg>
                    @endfor
                    <span class="ml-2 text-xs font-medium text-amber-700/80">{{ $jumlahFilter > 0 ? 'Sesuai filter' : 'Kepuasan pelanggan' }}</span>
                </div>
            </div>

            {{-- Total Ulasan --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50/80 via-white to-sky-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-800/80">Total Ulasan</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-blue-500 text-white shadow-md shadow-sky-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-sky-950 tabular-nums">{{ $totalUlasan }}</span>
                    <span class="text-sm font-bold text-sky-800/60">Ulasan</span>
                </div>
                <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-sky-700 bg-sky-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-sky-500 animate-pulse"></span>
                    <span>Ulasan pelanggan</span>
                </div>
            </div>

            {{-- Bintang 5 --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Bintang 5</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" /></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-emerald-950 tabular-nums">{{ $bintang5 }}</span>
                    <span class="text-sm font-bold text-emerald-800/60">Ulasan</span>
                </div>
                <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Rating sempurna</span>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <form id="form-filter-rating" method="GET" action="{{ route('admin.rating.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input type="hidden" name="bintang" value="{{ request('bintang') }}">

                <div class="sm:col-span-2">
                    <label for="f-q" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Cari</label>
                    <input type="search" id="f-q" name="q" value="{{ request('q') }}" autocomplete="off"
                        placeholder="Isi ulasan, nama pelanggan, atau produk" class="{{ $kelasInput }}">
                </div>

                <div>
                    <label for="f-produk" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Produk</label>
                    <select id="f-produk" name="produk_id" class="{{ $kelasInput }} cursor-pointer">
                        <option value="">Semua Produk</option>
                        @foreach ($produkList as $p)
                            <option value="{{ $p->id }}" {{ (string) request('produk_id') === (string) $p->id ? 'selected' : '' }}>
                                {{ $p->nama }}{{ $p->sku ? ' (' . $p->sku . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="f-urut" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Urutkan</label>
                    <select id="f-urut" name="urut" class="{{ $kelasInput }} cursor-pointer">
                        <option value="terbaru" {{ $urutAktif === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="terlama" {{ $urutAktif === 'terlama' ? 'selected' : '' }}>Terlama</option>
                        <option value="tertinggi" {{ $urutAktif === 'tertinggi' ? 'selected' : '' }}>Rating tertinggi</option>
                        <option value="terendah" {{ $urutAktif === 'terendah' ? 'selected' : '' }}>Rating terendah</option>
                    </select>
                </div>

                <div>
                    <label for="f-komentar" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Komentar</label>
                    <select id="f-komentar" name="komentar" class="{{ $kelasInput }} cursor-pointer">
                        <option value="">Semua</option>
                        <option value="ada" {{ request('komentar') === 'ada' ? 'selected' : '' }}>Ada komentar</option>
                        <option value="tanpa" {{ request('komentar') === 'tanpa' ? 'selected' : '' }}>Tanpa komentar</option>
                    </select>
                </div>

                <div>
                    <label for="f-dari" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Dari tanggal</label>
                    <input type="date" id="f-dari" name="dari" value="{{ request('dari') }}" class="{{ $kelasInput }}">
                </div>

                <div>
                    <label for="f-sampai" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Sampai tanggal</label>
                    <input type="date" id="f-sampai" name="sampai" value="{{ request('sampai') }}" class="{{ $kelasInput }}">
                </div>

                {{-- Tombol Terapkan dihapus: semua filter berjalan otomatis --}}
                <div id="area-reset" class="flex items-end pb-2">
                    @if ($jumlahFilter > 0)
                        <a href="{{ route('admin.rating.index') }}" class="text-xs font-bold text-gray-500 hover:text-amber-600">Reset filter ({{ $jumlahFilter }})</a>
                    @endif
                </div>
            </form>

            {{-- Chip bintang: jumlah mengikuti filter lain --}}
            <div id="area-chip" class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3" role="group" aria-label="Filter bintang">
                <a href="{{ request()->fullUrlWithQuery(['bintang' => null, 'page' => null]) }}"
                    class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-bold transition-colors {{ $bintangAktif === '' ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-md shadow-amber-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
                    Semua
                    <span class="rounded-full px-2 py-0.5 text-[10px] tabular-nums {{ $bintangAktif === '' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $totalUlasan }}</span>
                </a>
                @foreach ([5, 4, 3, 2, 1] as $n)
                    @php $aktif = $bintangAktif === (string) $n; @endphp
                    <a href="{{ request()->fullUrlWithQuery(['bintang' => $n, 'page' => null]) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-bold transition-colors {{ $aktif ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-md shadow-amber-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
                        <svg class="h-3.5 w-3.5 {{ $aktif ? 'text-white' : 'text-amber-400' }}" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $pathBintang }}" /></svg>
                        {{ $n }}
                        <span class="rounded-full px-2 py-0.5 text-[10px] tabular-nums {{ $aktif ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $perBintang[$n] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Daftar ulasan (diganti lewat AJAX) --}}
        <div id="area-ulasan" class="transition-opacity duration-300 relative">

            <div id="loading-ulasan" class="hidden absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-start justify-center pt-20">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600"></div>
            </div>

            @php
                // Tandai ulasan yang kemungkinan duplikat (pelanggan + item pesanan sama) pada halaman ini.
                $kunciDup = $ratings->getCollection()
                    ->groupBy(fn($x) => $x->user_id . '|' . $x->detail_transaksi_id)
                    ->filter(fn($g) => $g->count() > 1)
                    ->keys()
                    ->all();
            @endphp

            @if (method_exists($ratings, 'total') && $ratings->total() > 0)
                <p class="mb-3 text-xs text-gray-500">
                    Menampilkan <b class="text-gray-700">{{ $ratings->firstItem() }}–{{ $ratings->lastItem() }}</b>
                    dari <b class="text-gray-700">{{ $ratings->total() }}</b> ulasan
                </p>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($ratings as $r)
                    @php
                        $nama = $r->user->name ?? 'Pelanggan';
                        $inisial = strtoupper(mb_substr(trim($nama) ?: '?', 0, 1));
                        // Foto bisa berupa URL penuh (akun Google) atau path di storage lokal.
                        $foto = $r->user->foto_profil ?? null;
                        $fotoUrl = $foto
                            ? (\Illuminate\Support\Str::startsWith($foto, ['http://', 'https://'])
                                ? $foto
                                : asset('storage/' . ltrim($foto, '/')))
                            : null;
                        $rendah = (int) $r->rating <= 2;
                        $duplikat = in_array($r->user_id . '|' . $r->detail_transaksi_id, $kunciDup, true);
                    @endphp

                    <article class="kartu-ulasan group group-shine relative overflow-hidden bg-white rounded-2xl border {{ $rendah ? 'border-rose-100' : 'border-gray-100' }} p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div class="flex min-w-0 items-center gap-3">
                                {{-- Avatar: inisial sebagai dasar, foto menimpa; kalau foto gagal dimuat, inisial tetap tampil --}}
                                <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-400 font-black text-white shadow-md shadow-amber-500/20">
                                    {{ $inisial }}
                                    @if ($fotoUrl)
                                        <img src="{{ $fotoUrl }}" alt="" loading="lazy" referrerpolicy="no-referrer"
                                            onerror="this.remove()"
                                            class="absolute inset-0 h-full w-full rounded-2xl border border-amber-100 bg-white object-cover">
                                    @endif
                                </span>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-900">{{ $nama }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $r->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <div class="flex gap-0.5" aria-label="Rating {{ (int) $r->rating }} dari 5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $r->rating ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $pathBintang }}" /></svg>
                                    @endfor
                                </div>
                                @if ($rendah)
                                    <span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-rose-600">Perlu perhatian</span>
                                @endif
                            </div>
                        </div>

                        @if (filled($r->ulasan))
                            <p class="mb-3 break-words text-sm italic text-gray-700">"{{ $r->ulasan }}"</p>
                        @else
                            <p class="mb-3 text-sm text-gray-400">Tanpa komentar.</p>
                        @endif

                        @if ($duplikat)
                            <p class="mb-3 inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                                Kemungkinan duplikat: pelanggan yang sama mengulas item pesanan yang sama
                            </p>
                        @endif

                        <div class="flex items-center justify-between gap-3">
                            @if ($r->produk_id)
                                <a href="{{ request()->fullUrlWithQuery(['produk_id' => $r->produk_id, 'page' => null]) }}"
                                    title="Lihat semua ulasan produk ini"
                                    class="inline-flex min-w-0 items-center gap-1.5 rounded-full border border-amber-100 bg-amber-50/60 px-2.5 py-1 text-[11px] text-amber-800 transition-colors hover:border-amber-300 hover:bg-amber-100">
                                    Produk: <strong class="truncate font-bold text-amber-900">{{ $r->produk->nama ?? '-' }}</strong>
                                </a>
                            @else
                                <div class="inline-flex items-center gap-1.5 rounded-full border border-amber-100 bg-amber-50/60 px-2.5 py-1 text-[11px] text-amber-800">
                                    Produk: <strong class="font-bold text-amber-900">-</strong>
                                </div>
                            @endif

                            <button type="button"
                                data-hapus-rating="{{ $r->id }}"
                                data-nama="{{ $nama }}"
                                data-produk="{{ $r->produk->nama ?? '-' }}"
                                data-bintang="{{ (int) $r->rating }}"
                                data-ulasan="{{ \Illuminate\Support\Str::limit((string) $r->ulasan, 120) }}"
                                aria-label="Hapus ulasan dari {{ $nama }}" title="Hapus ulasan"
                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 shadow-sm transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-sm">
                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-500 border border-amber-100">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $pathBintang }}" /></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-400">
                            {{ $jumlahFilter > 0 ? 'Tidak ada ulasan yang cocok dengan filter ini.' : 'Belum ada ulasan.' }}
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $ratings->links() }}</div>
        </div>
    </div>

    {{-- Dialog konfirmasi hapus (native <dialog>, tidak bergantung pada komponen modal) --}}
    <dialog id="dlg-hapus-rating" aria-labelledby="dlg-hapus-judul"
        class="m-auto w-full max-w-sm rounded-3xl border border-gray-100 bg-white p-0 shadow-2xl">
        <div class="space-y-4 p-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-rose-100 bg-rose-50 text-rose-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </span>
                <div>
                    <h2 id="dlg-hapus-judul" class="text-base font-bold text-gray-900">Hapus ulasan ini?</h2>
                    <p class="mt-1 text-xs text-gray-500">Ulasan dihapus permanen dan tidak bisa dikembalikan.</p>
                </div>
            </div>

            <dl class="space-y-1.5 rounded-2xl border border-amber-100/60 bg-amber-50/40 px-4 py-3 text-xs">
                <div class="flex justify-between gap-4"><dt class="text-gray-400">Pelanggan</dt><dd id="hapus-rating-nama" class="font-bold text-gray-900 text-right"></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-400">Produk</dt><dd id="hapus-rating-produk" class="font-semibold text-gray-800 text-right"></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-400">Rating</dt><dd id="hapus-rating-bintang" class="font-bold text-amber-600"></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-400">Ulasan</dt><dd id="hapus-rating-ulasan" class="max-w-[200px] break-words text-right italic text-gray-700"></dd></div>
            </dl>

            <div class="flex justify-end gap-2 pt-1">
                <button type="button" id="btn-hapus-rating-batal"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-50">Batal</button>
                <button type="button" id="btn-hapus-rating-ya"
                    class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-rose-500/20 transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60">Ya, hapus</button>
            </div>
        </div>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('form-filter-rating');
            var inputQ = document.getElementById('f-q');
            if (!form) return;

            var URL_INDEX = "{{ route('admin.rating.index') }}";
            var URL_HAPUS = "{{ route('admin.rating.destroy', 0) }}"; // diakhiri /0, diganti id saat hapus
            var CSRF = "{{ csrf_token() }}";
            var WILAYAH = ['area-statistik', 'area-chip', 'area-reset', 'area-ulasan'];
            var DEBOUNCE_MS = 350;

            var controller = null;
            var timer = null;

            // ---------- Bantuan ----------
            function bangunUrl(extra) {
                var p = new URLSearchParams();
                new FormData(form).forEach(function (v, k) {
                    v = String(v).trim();
                    if (v === '') return;
                    if (k === 'urut' && v === 'terbaru') return; // default tidak perlu di URL
                    p.set(k, v);
                });
                if (extra) Object.keys(extra).forEach(function (k) { p.set(k, extra[k]); });
                var qs = p.toString();
                return URL_INDEX + (qs ? '?' + qs : '');
            }

            // Samakan isi form dengan URL (dipakai saat klik chip / link produk / reset / tombol back)
            function sinkronForm(url) {
                var sp = new URL(url, location.origin).searchParams;
                Array.prototype.forEach.call(form.elements, function (el) {
                    if (!el.name) return;
                    var v = sp.get(el.name);
                    if (el.tagName === 'SELECT') el.value = v !== null ? v : el.options[0].value;
                    else el.value = v !== null ? v : '';
                });
            }

            // Toast melayang di pojok kanan atas (tetap terlihat walau halaman sedang di-scroll)
            function pesan(teks, tipe) {
                var ok = tipe !== 'error';
                var wrap = document.getElementById('toast-rating');
                if (!wrap) {
                    wrap = document.createElement('div');
                    wrap.id = 'toast-rating';
                    wrap.setAttribute('aria-live', 'polite');
                    wrap.className = 'fixed right-4 top-24 z-[9999] flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-2';
                    document.body.appendChild(wrap);
                }

                var el = document.createElement('div');
                el.setAttribute('role', ok ? 'status' : 'alert');
                el.className = 'flex cursor-pointer items-center gap-3 rounded-2xl border bg-white px-4 py-3 shadow-lg transition-all duration-300 translate-x-full opacity-0 ' +
                    (ok ? 'border-emerald-200' : 'border-rose-200');

                var ikon = document.createElement('span');
                ikon.className = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-xl ' +
                    (ok ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600');
                ikon.innerHTML = ok
                    ? '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>'
                    : '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>';

                var isi = document.createElement('p');
                isi.className = 'text-sm font-bold text-gray-800';
                isi.textContent = teks;

                el.appendChild(ikon);
                el.appendChild(isi);
                wrap.appendChild(el);

                // masuk dengan animasi geser
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () { el.classList.remove('translate-x-full', 'opacity-0'); });
                });

                function tutup() {
                    el.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(function () { el.remove(); }, 300);
                }
                el.addEventListener('click', tutup);
                setTimeout(tutup, 3500);
            }

            // ---------- Muat data tanpa reload ----------
            // opts.riwayat: 'push' | 'replace' | 'none'; opts.sinkron: samakan form dengan URL
            async function muat(url, opts) {
                opts = opts || {};
                if (controller) controller.abort();
                var saya = controller = new AbortController();

                var area = document.getElementById('area-ulasan');
                var spin = document.getElementById('loading-ulasan');
                area.style.opacity = '0.5';
                area.style.pointerEvents = 'none';
                if (spin) spin.classList.remove('hidden');

                try {
                    var res = await fetch(url, {
                        signal: saya.signal,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    var doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                    if (!doc.getElementById('area-ulasan')) throw new Error('Respons tidak valid');

                    WILAYAH.forEach(function (id) {
                        var baru = doc.getElementById(id);
                        var lama = document.getElementById(id);
                        if (baru && lama) lama.innerHTML = baru.innerHTML;
                    });

                    if (opts.sinkron) sinkronForm(url);
                    if (opts.riwayat === 'push') history.pushState(null, '', url);
                    else if (opts.riwayat === 'replace') history.replaceState(null, '', url);
                } catch (err) {
                    if (err.name === 'AbortError') return;
                    console.error(err);
                    window.location.href = url; // fallback: cara biasa
                } finally {
                    if (controller === saya) {
                        var a = document.getElementById('area-ulasan');
                        var s = document.getElementById('loading-ulasan');
                        a.style.opacity = '1';
                        a.style.pointerEvents = 'auto';
                        if (s) s.classList.add('hidden');
                    }
                }
            }

            // ---------- Filter & pencarian otomatis ----------
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearTimeout(timer);
                muat(bangunUrl(), { riwayat: 'push' });
            });

            if (inputQ) {
                inputQ.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(function () { muat(bangunUrl(), { riwayat: 'replace' }); }, DEBOUNCE_MS);
                });
            }

            form.querySelectorAll('select, input[type="date"]').forEach(function (el) {
                el.addEventListener('change', function () {
                    clearTimeout(timer);
                    muat(bangunUrl(), { riwayat: 'push' });
                });
            });

            // ---------- Klik: hapus, chip bintang, link produk, reset, pagination ----------
            document.addEventListener('click', function (e) {
                var hapus = e.target.closest('[data-hapus-rating]');
                if (hapus) { bukaDialog(hapus); return; }

                var link = e.target.closest('#area-chip a, #area-ulasan a, #area-reset a');
                if (!link) return;
                if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey) return; // biarkan buka di tab baru
                e.preventDefault();

                var tujuan = new URL(link.href, location.origin);
                if (link.closest('nav') && tujuan.searchParams.has('page')) {
                    // Pagination: pakai filter yang sedang aktif di form + nomor halaman
                    muat(bangunUrl({ page: tujuan.searchParams.get('page') }), { riwayat: 'push' });
                } else {
                    muat(link.href, { riwayat: 'push', sinkron: true });
                }
            });

            window.addEventListener('popstate', function () {
                muat(location.href, { riwayat: 'none', sinkron: true });
            });

            // ---------- Hapus ulasan ----------
            var dlg = document.getElementById('dlg-hapus-rating');
            var btnYa = document.getElementById('btn-hapus-rating-ya');
            var btnBatal = document.getElementById('btn-hapus-rating-batal');
            var idAktif = null;

            function bukaDialog(btn) {
                idAktif = btn.dataset.hapusRating;
                document.getElementById('hapus-rating-nama').textContent = btn.dataset.nama || '-';
                document.getElementById('hapus-rating-produk').textContent = btn.dataset.produk || '-';
                document.getElementById('hapus-rating-bintang').textContent = '★ ' + (btn.dataset.bintang || '0') + ' / 5';
                document.getElementById('hapus-rating-ulasan').textContent = btn.dataset.ulasan || 'Tanpa komentar';
                btnYa.disabled = false;
                btnYa.textContent = 'Ya, hapus';
                dlg.showModal();
            }

            btnBatal.addEventListener('click', function () { dlg.close(); });
            dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); }); // klik area gelap

            btnYa.addEventListener('click', function () {
                if (!idAktif) return;
                btnYa.disabled = true;
                btnYa.textContent = 'Menghapus...';

                var jumlahKartu = document.querySelectorAll('.kartu-ulasan').length;
                var fd = new FormData();
                fd.append('_method', 'DELETE');
                fd.append('_token', CSRF);

                fetch(URL_HAPUS.replace(/\/0$/, '/' + encodeURIComponent(idAktif)), {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(async function (res) {
                    var data = await res.json().catch(function () { return {}; });
                    if (!res.ok || data.success === false) throw new Error(data.message || 'Gagal menghapus ulasan');
                    return data;
                })
                .then(function (data) {
                    dlg.close();
                    pesan(data.message || 'Ulasan berhasil dihapus.', 'success');

                    // Kalau yang dihapus satu-satunya kartu di halaman > 1, mundur satu halaman
                    var u = new URL(location.href);
                    var hal = parseInt(u.searchParams.get('page') || '1', 10);
                    if (jumlahKartu <= 1 && hal > 1) u.searchParams.set('page', hal - 1);

                    // Muat ulang daftar + statistik + jumlah di chip
                    muat(u.toString(), { riwayat: 'replace' });
                })
                .catch(function (err) {
                    pesan(err.message || 'Gagal menghapus ulasan', 'error');
                    btnYa.disabled = false;
                    btnYa.textContent = 'Ya, hapus';
                    dlg.close();
                });
            });
        });
    </script>
@endsection