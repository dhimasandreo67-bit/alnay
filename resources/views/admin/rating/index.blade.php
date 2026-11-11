@extends('admin.layouts.admin')
@section('title', 'Rating & Ulasan')

@section('content')
    @php
        $jumlahFilter =
            collect(['q', 'produk_id', 'komentar', 'dari', 'sampai', 'bintang'])
                ->filter(fn($k) => request()->filled($k))
                ->count() + (request('urut', 'terbaru') !== 'terbaru' ? 1 : 0);
    @endphp
    <div class="p-6 space-y-6 max-w-6xl mx-auto transition-all duration-500 animate-fade-in">

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
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm"
                role="alert">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-sm font-bold">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm"
                role="alert">
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
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Rating & Ulasan</h1>
            <p class="text-xs text-gray-500 mt-1">Pantau kepuasan pelanggan terhadap busana dan layanan ALNAY.ID.</p>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

            {{-- Card 1: Rata-rata Rating (Amber) --}}
            <div
                class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Rata-rata Rating</span>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span
                        class="text-3xl font-extrabold tracking-tight text-amber-950 tabular-nums">{{ number_format($rataRata, 1) }}</span>
                    <span class="text-sm font-bold text-amber-800/60">/ 5.0</span>
                </div>
                <p class="mt-2 text-xs font-medium text-amber-700/80">
                    {{ $jumlahFilter > 0 ? 'Sesuai filter' : 'Kepuasan pelanggan' }}</p>
            </div>

            {{-- Card 2: Total Ulasan (Sky) --}}
            <div
                class="group group-shine relative overflow-hidden rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50/80 via-white to-sky-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-800/80">Total Ulasan</span>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-blue-500 text-white shadow-md shadow-sky-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span
                        class="text-3xl font-extrabold tracking-tight text-sky-950 tabular-nums">{{ $totalUlasan }}</span>
                    <span class="text-sm font-bold text-sky-800/60">Ulasan</span>
                </div>
                <div
                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-sky-700 bg-sky-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-sky-500 animate-pulse"></span>
                    <span>Ulasan pelanggan</span>
                </div>
            </div>

            {{-- Card 3: Bintang 5 (Emerald) --}}
            <div
                class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Bintang 5</span>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span
                        class="text-3xl font-extrabold tracking-tight text-emerald-950 tabular-nums">{{ $bintang5 }}</span>
                    <span class="text-sm font-bold text-emerald-800/60">Ulasan</span>
                </div>
                <div
                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Rating sempurna</span>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        @php
            $urutAktif = request('urut', 'terbaru');
            $kelasInput =
                'w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all';
        @endphp
        <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input type="hidden" name="bintang" value="{{ request('bintang') }}">

                <div class="sm:col-span-2">
                    <label for="f-q"
                        class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Cari</label>
                    <input type="search" id="f-q" name="q" value="{{ request('q') }}"
                        placeholder="Isi ulasan, nama pelanggan, atau produk" class="{{ $kelasInput }}">
                </div>

                <div>
                    <label for="f-produk"
                        class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Produk</label>
                    <select id="f-produk" name="produk_id"
                        class="{{ $kelasInput }} cursor-pointer">
                        <option value="">Semua Produk</option>
                        @foreach ($produkList as $p)
                            <option value="{{ $p->id }}"
                                {{ (string) request('produk_id') === (string) $p->id ? 'selected' : '' }}>
                                {{ $p->nama }}{{ $p->sku ? ' (' . $p->sku . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="f-urut"
                        class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Urutkan</label>
                    <select id="f-urut" name="urut"
                        class="{{ $kelasInput }} cursor-pointer">
                        <option value="terbaru" {{ $urutAktif === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="terlama" {{ $urutAktif === 'terlama' ? 'selected' : '' }}>Terlama</option>
                        <option value="tertinggi" {{ $urutAktif === 'tertinggi' ? 'selected' : '' }}>Rating tertinggi
                        </option>
                        <option value="terendah" {{ $urutAktif === 'terendah' ? 'selected' : '' }}>Rating terendah
                        </option>
                    </select>
                </div>

                <div>
                    <label for="f-komentar"
                        class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Komentar</label>
                    <select id="f-komentar" name="komentar"
                        class="{{ $kelasInput }} cursor-pointer">
                        <option value="">Semua</option>
                        <option value="ada" {{ request('komentar') === 'ada' ? 'selected' : '' }}>Ada komentar</option>
                        <option value="tanpa" {{ request('komentar') === 'tanpa' ? 'selected' : '' }}>Tanpa komentar
                        </option>
                    </select>
                </div>

                <div>
                    <label for="f-dari"
                        class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Dari
                        tanggal</label>
                    <input type="date" id="f-dari" name="dari" value="{{ request('dari') }}"
                        class="{{ $kelasInput }}">
                </div>

                <div>
                    <label for="f-sampai"
                        class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Sampai
                        tanggal</label>
                    <input type="date" id="f-sampai" name="sampai" value="{{ request('sampai') }}"
                        class="{{ $kelasInput }}">
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit"
                        class="inline-flex items-center rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 px-4 py-2 text-xs font-bold text-white shadow-md shadow-amber-500/20 transition-all hover:from-amber-700 hover:to-amber-600 active:scale-95">Terapkan</button>
                    @if ($jumlahFilter > 0)
                        <a href="{{ route('admin.rating.index') }}"
                            class="text-xs font-bold text-gray-500 hover:text-amber-600">Reset ({{ $jumlahFilter }})</a>
                    @endif
                </div>
            </form>

            {{-- Chip bintang: jumlah mengikuti filter lain --}}
            <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3" role="group"
                aria-label="Filter bintang">
                @php $bintangAktif = (string) request('bintang'); @endphp
                <a href="{{ request()->fullUrlWithQuery(['bintang' => null, 'page' => null]) }}"
                    class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-bold transition-colors {{ $bintangAktif === '' ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-md shadow-amber-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
                    Semua
                    <span
                        class="rounded-full px-2 py-0.5 text-[10px] tabular-nums {{ $bintangAktif === '' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $totalUlasan }}</span>
                </a>
                @foreach ([5, 4, 3, 2, 1] as $n)
                    @php $aktif = $bintangAktif === (string) $n; @endphp
                    <a href="{{ request()->fullUrlWithQuery(['bintang' => $n, 'page' => null]) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-bold transition-colors {{ $aktif ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-md shadow-amber-500/20' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
                        <svg class="h-3.5 w-3.5 {{ $aktif ? 'text-white' : 'text-amber-400' }}" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        {{ $n }}
                        <span
                            class="rounded-full px-2 py-0.5 text-[10px] tabular-nums {{ $aktif ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $perBintang[$n] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- List Ulasan (Dibungkus untuk AJAX) --}}
        <div id="area-ulasan" class="transition-opacity duration-300 relative">

            {{-- Indikator Loading Halus --}}
            <div id="loading-ulasan"
                class="hidden absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-start justify-center pt-20">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($ratings as $r)
                    <div
                        class="group group-shine relative overflow-hidden bg-white rounded-2xl border border-gray-100 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                {{-- Foto Profil Dinamis --}}
                                @if ($r->user && $r->user->foto_profil)
                                    <img src="{{ asset('storage/' . $r->user->foto_profil) }}"
                                        alt="{{ $r->user->name }}"
                                        class="w-10 h-10 rounded-2xl object-cover border border-amber-100">
                                @else
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-400 flex items-center justify-center text-white font-black shadow-md shadow-amber-500/20">
                                        {{ substr($r->user->name ?? '?', 0, 1) }}
                                    </div>
                                @endif

                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $r->user->name ?? 'User' }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $r->created_at->format('d M Y') }}</p>
                                </div>
                            </div>

                            {{-- Bintang SVG Dinamis --}}
                            <div class="flex gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $r->rating ? 'text-amber-400' : 'text-gray-300' }}"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <p class="text-sm text-gray-700 italic mb-3">"{{ $r->ulasan ?? 'Tanpa komentar.' }}"</p>
                        @if ($r->produk_id)
                            <a href="{{ request()->fullUrlWithQuery(['produk_id' => $r->produk_id, 'page' => null]) }}"
                                title="Lihat semua ulasan produk ini"
                                class="inline-flex items-center gap-1.5 rounded-full border border-amber-100 bg-amber-50/60 px-2.5 py-1 text-[11px] text-amber-800 transition-colors hover:border-amber-300 hover:bg-amber-100">
                                Produk: <strong class="font-bold text-amber-900">{{ $r->produk->nama ?? '-' }}</strong>
                            </a>
                        @else
                            <div
                                class="inline-flex items-center gap-1.5 rounded-full border border-amber-100 bg-amber-50/60 px-2.5 py-1 text-[11px] text-amber-800">
                                Produk: <strong class="font-bold text-amber-900">-</strong>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-2 text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-sm">
                        <div
                            class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-500 border border-amber-100">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-400">
                            {{ $jumlahFilter > 0 ? 'Tidak ada ulasan yang cocok dengan filter ini.' : 'Belum ada ulasan.' }}
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $ratings->links() }}</div>
        </div>

        <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[method="GET"]');
        const areaUlasan = document.getElementById('area-ulasan');

        if (!form || !areaUlasan) return;

        async function muatData(url) {
            // Tampilkan efek loading halus
            areaUlasan.style.opacity = '0.5';
            areaUlasan.style.pointerEvents = 'none';

            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('Gagal memuat');

                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const areaBaru = doc.getElementById('area-ulasan');

                if (areaBaru) {
                    // 1. Ganti isi ulasan
                    areaUlasan.innerHTML = areaBaru.innerHTML;
                    
                    // 2. Update URL di browser tanpa reload
                    history.pushState({}, '', url);

                    // 3. SINKRONISASI FORM: Agar filter bintang/produk tidak hilang saat ganti urutan
                    const urlParams = new URLSearchParams(url.split('?')[1] || '');
                    form.querySelectorAll('input, select').forEach(input => {
                        const paramValue = urlParams.get(input.name);
                        if (paramValue !== null) {
                            input.value = paramValue;
                        } else if (input.type !== 'hidden') {
                            // Reset ke default jika tidak ada di URL
                            if (input.tagName === 'SELECT') input.selectedIndex = 0;
                            else input.value = '';
                        }
                    });
                }
            } catch (error) {
                console.error(error);
                window.location.href = url; // Fallback jika AJAX gagal
            } finally {
                areaUlasan.style.opacity = '1';
                areaUlasan.style.pointerEvents = 'auto';
            }
        }

        // 1. Tangkap Form Submit (Tombol Terapkan)
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const params = new URLSearchParams(new FormData(form));
            for (const [key, value] of params.entries()) {
                if (value === '') params.delete(key);
            }
            muatData(form.action.split('?')[0] + '?' + params.toString());
        });

        // 2. Tangkap Perubahan Dropdown & Tanggal (Otomatis AJAX)
        form.querySelectorAll('select, input[type="date"]').forEach(el => {
            el.addEventListener('change', () => {
                form.dispatchEvent(new Event('submit'));
            });
        });

        // 3. Tangkap Klik Chip Bintang, Pagination, dan Link Produk
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link || link.getAttribute('href').startsWith('#')) return;

            const url = new URL(link.href);
            // Cek jika link mengandung parameter filter (bintang, page, produk_id)
            if (url.searchParams.has('bintang') || url.searchParams.has('page') || url.searchParams.has('produk_id')) {
                e.preventDefault();
                muatData(link.href);
            }
        });

        // 4. Handle tombol Back/Forward browser
        window.addEventListener('popstate', function() {
            muatData(window.location.href);
        });
    });
</script>
    @endsection
