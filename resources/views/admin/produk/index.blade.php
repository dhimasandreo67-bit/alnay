@extends('admin.layouts.admin')

@section('title', 'Kelola Produk')

@section('content')

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

    <div class="p-4 lg:p-6 space-y-8 animate-fade-up">

        @if(session('success'))
            <div class="flex items-center gap-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm animate-fade-up" role="alert">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center gap-4 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl shadow-sm animate-fade-up" role="alert">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p class="text-sm font-semibold">{{ session('error') }}</p>
            </div>
        @endif

        @php
            // Ambang batas "stok hampir habis". Belum ada kolom khusus di tabel produk,
            // jadi dipakai angka tetap dulu — gampang diubah di sini kalau mau disesuaikan.
            $batasStokMenipis = 3;
            // Produk dijual & disewa punya dua stok terpisah -> pakai yang paling sedikit.
            // Stok sewa yang dihitung = unit yang TERSEDIA hari ini (total unit dikurangi yang sedang disewa).
            $sewaTerpakai = $sewaTerpakai ?? [];
            $stokTersedia = function ($p) use ($sewaTerpakai) {
                $nilai = [];
                if ($p->bisa_disewa) {
                    $nilai[] = max(0, $p->stokUntuk('sewa') - ($sewaTerpakai[$p->id] ?? 0));
                }
                if ($p->dapat_dijual) {
                    $nilai[] = $p->stokUntuk('jual');
                }

                return $nilai ? min($nilai) : (int) $p->stok;
            };
            $produkStokHabis = $produkList->filter(fn ($p) => $stokTersedia($p) <= 0);
            $produkStokMenipis = $produkList->filter(fn ($p) => $stokTersedia($p) >= 1 && $stokTersedia($p) <= $batasStokMenipis);
            $produkPerluRestock = $produkStokHabis->merge($produkStokMenipis);
        @endphp

        @if($produkPerluRestock->isNotEmpty())
            <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-2xl shadow-sm animate-fade-up" role="alert">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="text-sm">
                    <p class="font-bold">Perhatian stok produk</p>
                    <p class="mt-0.5">
                        @if($produkStokHabis->isNotEmpty())
                            <strong class="text-rose-700">{{ $produkStokHabis->count() }} produk stok habis</strong>
                        @endif
                        @if($produkStokHabis->isNotEmpty() && $produkStokMenipis->isNotEmpty()) &middot; @endif
                        @if($produkStokMenipis->isNotEmpty())
                            <strong class="text-amber-700">{{ $produkStokMenipis->count() }} produk hampir habis</strong> (≤ {{ $batasStokMenipis }} unit)
                        @endif
                    </p>
                    <p class="mt-1 text-xs text-amber-800/70">
                        {{ $produkPerluRestock->pluck('nama')->take(5)->implode(', ') }}@if($produkPerluRestock->count() > 5), dan {{ $produkPerluRestock->count() - 5 }} lainnya @endif
                    </p>
                </div>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <nav class="text-xs text-muted font-bold tracking-wider uppercase mb-1.5 flex items-center gap-2">
                    <span>Katalog Busana</span> <span class="text-gold">/</span> <span class="text-ink">Manajemen Inventaris</span>
                </nav>
                <h1 class="text-3xl font-display font-bold tracking-tight text-ink">Kelola Produk</h1>
                <p class="text-sm text-muted mt-1">Katalog busana & aksesoris etnik, inventaris sewa, dan penjualan couture AL-NAY.ID</p>
            </div>

            <div class="flex items-center gap-3">
                <a id="linkExport" href="{{ route('admin.produk.export', array_filter($filters)) }}" title="Unduh data produk (mengikuti filter yang aktif) dalam format Excel" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold text-sm hover:bg-gray-50 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                    Unduh Excel
                </a>
                <a href="{{ route('admin.produk.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all duration-200 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Tambah Produk
                </a>
            </div>
        </div>

        {{-- Stat Cards Kustom dengan Gradasi Luxe & Glow --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            
            {{-- Card 1: Total Koleksi --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Total Koleksi</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-amber-950">{{ $totalUnit }}</span>
                    <span class="text-sm font-bold text-amber-800/60">Unit</span>
                </div>
                <p class="mt-1 text-xs font-semibold text-amber-700/80">{{ $totalKoleksi }} Model &middot; {{ $totalKategori }} Kategori</p>
            </div>

            {{-- Card 2: Siap Sewa / Jual --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Siap Sewa / Jual</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-emerald-950">{{ $siapCount }}</span>
                    <span class="text-sm font-bold text-emerald-800/60">Unit</span>
                </div>
                <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Tersedia di atelier</span>
                </div>
            </div>

            {{-- Card 3: Sedang Disewa --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50/80 via-white to-sky-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-800/80">Sedang Disewa</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-blue-500 text-white shadow-md shadow-sky-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-sky-950">{{ $disewaCount }}</span>
                    <span class="text-sm font-bold text-sky-800/60">Unit</span>
                </div>
                <p class="mt-2 text-xs font-medium text-sky-700/80">Sedang keluar hari ini</p>
            </div>

            {{-- Card 4: Perawatan / Reparasi --}}
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-br from-rose-50/80 via-white to-rose-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-rose-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-800/80">Perawatan / Reparasi</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-rose-950">{{ $perawatanCount }}</span>
                    <span class="text-sm font-bold text-rose-800/60">Unit</span>
                </div>
                <p class="mt-2 text-xs font-semibold text-rose-600">Dry clean & perbaikan</p>
            </div>

        </div>

        {{-- Filter & Search Toolbar --}}
        <form method="GET" action="{{ route('admin.produk.index') }}" onsubmit="event.preventDefault()"
              class="bg-panel p-3 rounded-2xl border border-line shadow-sm flex flex-col md:flex-row items-center gap-3">
            <div class="relative w-full md:w-80 group">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-muted group-focus-within:text-gold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="search" id="cariProduk" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama, SKU, motif, bahan..."
                       autocomplete="off" oninput="terapkanFilterProduk()"
                       class="w-full pl-11 pr-4 py-2.5 bg-cream/50 border border-transparent rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-gold/20 focus:border-gold transition-all duration-300">
            </div>

            <select id="filterKategoriProduk" name="kategori_id" onchange="terapkanFilterProduk()"
                    class="w-full md:w-auto px-4 py-2.5 bg-cream/50 border border-transparent rounded-xl text-sm font-semibold text-ink focus:outline-none focus:ring-2 focus:ring-gold/20 focus:border-gold transition-all duration-300 cursor-pointer">
                <option value="">Semua Kategori</option>
                @foreach($categories as $kategori)
                    <option value="{{ $kategori->id }}" @selected(($filters['kategori_id'] ?? null) == $kategori->id)>{{ $kategori->nama_kategori }}</option>
                @endforeach
            </select>

            <select id="filterStatusProduk" name="status" onchange="terapkanFilterProduk()"
                    class="w-full md:w-auto px-4 py-2.5 bg-cream/50 border border-transparent rounded-xl text-sm font-semibold text-ink focus:outline-none focus:ring-2 focus:ring-gold/20 focus:border-gold transition-all duration-300 cursor-pointer">
                <option value="">Semua Status</option>
                @foreach($opsiStatus as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="flex-1 flex items-center justify-end gap-3 w-full md:w-auto mt-2 md:mt-0 pr-2">
                <span class="text-xs text-muted font-medium whitespace-nowrap">
                    Menampilkan <strong id="infoJumlah" class="text-ink">{{ $produkList->count() }}</strong> dari <strong class="text-ink">{{ $totalKoleksi }}</strong> busana
                </span>
                <button type="button" id="btnReset" onclick="resetFilterProduk()"
                        class="hidden text-xs font-bold text-rose-500 hover:text-rose-700 hover:underline transition-all whitespace-nowrap cursor-pointer">
                    ⟲ Reset
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="bg-panel rounded-3xl shadow-sm border border-line overflow-hidden">
            {{-- Bar hapus massal: muncul saat ada yang dicentang --}}
            <form id="formBulkDelete" method="POST" action="{{ route('admin.produk.bulk-destroy') }}">
                @csrf
                @method('DELETE')
            </form>
            <div id="bulkBar" class="hidden items-center justify-between gap-3 px-6 py-3 bg-rose-50 border-b border-rose-100">
                <span class="text-xs font-bold text-rose-700"><span id="bulkCount">0</span> produk dipilih</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="clearBulk()"
                        class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="button" onclick="submitBulkDelete()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-95 transition-all cursor-pointer shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus Terpilih
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px] divide-y divide-line">
                    <thead class="bg-cream/80">
                        <tr>
                            <th class="px-3 py-3 w-8">
                                <input type="checkbox" id="checkAll" onchange="toggleSelectAll(this)" @disabled($produkList->count() === 0)
                                    class="w-4 h-4 rounded border-gray-300 accent-amber-600 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                    title="Pilih semua">
                            </th>
                            <th class="whitespace-nowrap px-3 py-3 text-left text-[10px] font-bold text-ink/60 uppercase tracking-wide">Foto & Siluet</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left text-[10px] font-bold text-ink/60 uppercase tracking-wide">Nama & Motif</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left text-[10px] font-bold text-ink/60 uppercase tracking-wide">Kode SKU</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left text-[10px] font-bold text-ink/60 uppercase tracking-wide">Kategori</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left text-[10px] font-bold text-ink/60 uppercase tracking-wide">Tarif Sewa & Jual</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left text-[10px] font-bold text-ink/60 uppercase tracking-wide">Stok</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left text-[10px] font-bold text-ink/60 uppercase tracking-wide">Status</th>
                            <th class="whitespace-nowrap px-3 py-3 text-right text-[10px] font-bold text-ink/60 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line/50 bg-panel">
                        @forelse($produkList as $produk)
                            @php
                                $sampul = $produk->sampulUrl();
                                $statusClass = match($produk->status) {
                                    'tersedia' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'disewa'   => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'dicuci'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'rusak'    => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default    => 'bg-gray-50 text-gray-500 border-gray-200',
                                };
                                $stokHabis = $stokTersedia($produk) <= 0;
                                $stokMenipis = !$stokHabis && $stokTersedia($produk) <= $batasStokMenipis;
                                $sewaTotal = $produk->bisa_disewa ? $produk->stokUntuk('sewa') : 0;
                                $sewaOut = $sewaTerpakai[$produk->id] ?? 0;
                                $sewaSisa = max(0, $sewaTotal - $sewaOut);
                                $rowStokClass = $stokHabis ? 'bg-rose-50/50 hover:bg-rose-50/80' : ($stokMenipis ? 'bg-amber-50/40 hover:bg-amber-50/70' : 'hover:bg-cream/40');
                            @endphp
                            <tr data-cari="{{ \Illuminate\Support\Str::lower(implode(' ', [$produk->nama, $produk->sku, $produk->motif, $produk->bahan, $produk->kategori->nama_kategori ?? ''])) }}"
                                data-kategori="{{ $produk->kategori_id }}"
                                data-status="{{ $produk->status }}"
                                class="transition-colors duration-200 group {{ $rowStokClass }}">
                                <td class="px-3 py-3">
                                    <input type="checkbox" name="ids[]" value="{{ $produk->id }}" form="formBulkDelete"
                                        data-nama="{{ $produk->nama }}" onchange="updateBulkBar()"
                                        class="bulk-check w-4 h-4 rounded border-gray-300 accent-amber-600 cursor-pointer"
                                        title="Pilih untuk dihapus">
                                </td>
                                <td class="px-3 py-3">
                                    <div class="relative aspect-square w-12 h-12 overflow-hidden rounded-xl bg-cream border border-line shadow-sm">
                                        @if($sampul)
                                            <img src="{{ $sampul }}" alt="{{ $produk->nama }}" loading="lazy"
                                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center text-muted">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 min-w-[140px]">
                                    <p class="text-sm font-bold text-ink group-hover:text-gold transition-colors duration-300">{{ $produk->nama }}</p>
                                    @if($produk->motif)
                                        <p class="text-xs text-muted mt-0.5">{{ $produk->motif }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-block whitespace-nowrap bg-cream border border-line rounded-lg px-2 py-0.5 text-[10px] font-mono font-bold text-ink/80">{{ $produk->sku }}</span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-block whitespace-nowrap bg-ink/5 text-ink rounded-full px-2.5 py-0.5 text-[11px] font-semibold">{{ $produk->kategori->nama_kategori ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-3 text-[11px] whitespace-nowrap">
                                    @php
                                        // Diskon sewa dan jual dipisah. Rumus sama dengan preview di form:
                                        // harga * (1 - diskon/100).
                                        $diskonSewa = (float) ($produk->diskon_sewa_persen ?? 0);
                                        $diskonJual = (float) ($produk->diskon_jual_persen ?? 0);
                                        $sewaFinal = $produk->harga_sewa ? $produk->harga_sewa * (1 - $diskonSewa / 100) : null;
                                        $jualFinal = $produk->harga_jual ? $produk->harga_jual * (1 - $diskonJual / 100) : null;
                                        $fmtPersen = fn ($d) => rtrim(rtrim(number_format($d, 1, ',', '.'), '0'), ',');
                                    @endphp
                                    @if($produk->bisa_disewa && $produk->harga_sewa)
                                        <p class="font-bold text-gold">
                                            Sewa:
                                            @if($diskonSewa > 0)
                                                <span class="line-through text-muted font-normal">Rp {{ number_format($produk->harga_sewa, 0, ',', '.') }}</span>
                                                Rp {{ number_format($sewaFinal, 0, ',', '.') }}
                                            @else
                                                Rp {{ number_format($produk->harga_sewa, 0, ',', '.') }}
                                            @endif
                                            <span class="text-muted font-normal">/3hr</span>
                                            @if($diskonSewa > 0)
                                                <span class="inline-flex items-center ml-1 rounded-full bg-rose-100 px-1.5 py-0.5 text-[9px] font-bold text-rose-700 border border-rose-200">-{{ $fmtPersen($diskonSewa) }}%</span>
                                            @endif
                                        </p>
                                    @endif
                                    @if($produk->dapat_dijual)
                                        <p class="text-ink/70 mt-1 font-medium">
                                            Jual:
                                            @if($produk->harga_jual)
                                                @if($diskonJual > 0)
                                                    <span class="line-through text-muted font-normal">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</span>
                                                    <span class="font-bold text-gold">Rp {{ number_format($jualFinal, 0, ',', '.') }}</span>
                                                    <span class="inline-flex items-center ml-1 rounded-full bg-rose-100 px-1.5 py-0.5 text-[9px] font-bold text-rose-700 border border-rose-200">-{{ $fmtPersen($diskonJual) }}%</span>
                                                @else
                                                    Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </p>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-col items-start gap-1 whitespace-nowrap">
                                        @if($produk->pisahStok())
                                            <span class="text-xs font-black text-ink">
                                                <span class="text-[10px] font-bold uppercase text-emerald-600">Jual</span> {{ $produk->stokUntuk('jual') }}
                                                <span class="text-xs font-normal text-muted">unit</span>
                                            </span>
                                            <span class="text-xs font-black text-ink">
                                                <span class="text-[10px] font-bold uppercase text-sky-600">Sewa</span> {{ $sewaSisa }}
                                                <span class="text-xs font-normal text-muted">/ {{ $sewaTotal }} unit</span>
                                            </span>
                                        @elseif($produk->bisa_disewa)
                                            <span class="text-xs font-black text-ink">{{ $sewaSisa }} <span class="text-xs font-normal text-muted">/ {{ $sewaTotal }} unit</span></span>
                                        @else
                                            <span class="text-xs font-black text-ink">{{ $produk->stokUntuk('jual') }} <span class="text-xs font-normal text-muted">unit</span></span>
                                        @endif
                                        @if($produk->bisa_disewa && $sewaOut > 0)
                                            <span class="text-[10px] font-semibold text-sky-600">{{ $sewaOut }} sedang disewa</span>
                                        @endif
                                        @if($stokHabis)
                                            <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-rose-100 px-2 py-0.5 text-[9px] font-bold text-rose-700 border border-rose-200 animate-pulse" title="Stok produk ini kosong">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Habis
                                            </span>
                                        @elseif($stokMenipis)
                                            <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-bold text-amber-700 border border-amber-200" title="Sisa stok {{ $batasStokMenipis }} unit atau kurang">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Hampir Habis
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap border rounded-lg px-2 py-0.5 text-[10px] font-bold {{ $statusClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                        {{ $opsiStatus[$produk->status] ?? $produk->status }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center justify-end gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="{{ route('admin.produk.edit', $produk->id) }}"
                                           class="p-1.5 rounded-xl text-ink/50 hover:text-gold hover:bg-gold/10 active:scale-90 transition-all duration-200" title="Edit Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-muted">
                                        <svg class="w-12 h-12 mb-3 text-line" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="text-sm font-medium">Belum ada produk di katalog.</p>
                                        <a href="{{ route('admin.produk.create') }}" class="mt-3 text-sm text-gold font-bold hover:underline">+ Tambah Produk Pertama</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                        {{-- Muncul saat pencarian tidak menemukan apa pun --}}
                        <tr id="barisKosongCari" class="hidden">
                            <td colspan="9" class="px-6 py-16 text-center text-sm font-medium text-muted">
                                Tidak ada produk yang cocok dengan pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL KONFIRMASI HAPUS MASSAL ===================== --}}
    <div id="modalBulkDelete"
        class="hidden fixed inset-0 z-[1000] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div id="modalBulkDeleteBox"
            class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-rose-100 transform transition-all duration-200 scale-95 opacity-0"
            onclick="event.stopPropagation()">
            <div class="flex flex-col items-center text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 ring-8 ring-rose-50/60 text-rose-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-black text-gray-900">Hapus <span id="bulkConfirmCount">0</span> Produk?</h3>
                <p class="mt-1.5 text-xs text-gray-500 leading-relaxed">
                    Produk berikut akan dihapus permanen dan <strong class="text-rose-600">tidak dapat dikembalikan</strong>.
                </p>
            </div>

            <ul id="bulkConfirmList"
                class="mt-5 max-h-40 overflow-y-auto space-y-1.5 rounded-2xl border border-gray-100 bg-gray-50/70 p-3 text-left"></ul>

            <div class="mt-6 flex items-center gap-3">
                <button type="button" onclick="closeBulkConfirm()"
                    class="flex-1 px-5 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold text-sm hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                    Batal
                </button>
                <button type="button" onclick="confirmBulkDelete()"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-700 hover:to-rose-600 text-white font-bold text-sm shadow-lg shadow-rose-500/25 active:scale-95 transition-all cursor-pointer">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <script>
        function bulkChecks() {
            // Hanya baris yang sedang tampil (hasil pencarian) yang bisa dipilih
            return [...document.querySelectorAll('.bulk-check:not(:disabled)')]
                .filter(c => !c.closest('tr').classList.contains('hidden'));
        }

        // ===== Pencarian langsung (tanpa reload), seperti katalog kasir =====
        const EXPORT_URL = @json(route('admin.produk.export'));
        const JENIS_AKTIF = @json($filters['jenis'] ?? '');

        function terapkanFilterProduk() {
            const q = (document.getElementById('cariProduk').value || '').trim().toLowerCase();
            const kat = document.getElementById('filterKategoriProduk').value;
            const st = document.getElementById('filterStatusProduk').value;
            const baris = document.querySelectorAll('tr[data-cari]');
            let n = 0;

            baris.forEach(tr => {
                const cocok = (!q || tr.dataset.cari.includes(q))
                    && (kat === '' || tr.dataset.kategori === kat)
                    && (st === '' || tr.dataset.status === st);
                tr.classList.toggle('hidden', !cocok);

                if (cocok) {
                    n++;
                } else {
                    // Baris yang tersembunyi tidak boleh ikut terhapus
                    const cb = tr.querySelector('.bulk-check');
                    if (cb) cb.checked = false;
                }
            });

            document.getElementById('infoJumlah').textContent = n;
            document.getElementById('barisKosongCari')
                .classList.toggle('hidden', !(baris.length > 0 && n === 0));
            document.getElementById('btnReset')
                .classList.toggle('hidden', !(q || kat || st));

            // Unduhan Excel mengikuti filter yang sedang tampil
            const params = new URLSearchParams();
            if (q) params.set('q', document.getElementById('cariProduk').value.trim());
            if (kat) params.set('kategori_id', kat);
            if (st) params.set('status', st);
            if (JENIS_AKTIF) params.set('jenis', JENIS_AKTIF);
            const qs = params.toString();
            document.getElementById('linkExport').href = EXPORT_URL + (qs ? '?' + qs : '');

            updateBulkBar();
        }

        function resetFilterProduk() {
            document.getElementById('cariProduk').value = '';
            document.getElementById('filterKategoriProduk').value = '';
            document.getElementById('filterStatusProduk').value = '';
            terapkanFilterProduk();
        }

        document.addEventListener('DOMContentLoaded', terapkanFilterProduk);

        function updateBulkBar() {
            const checks = bulkChecks();
            const n = document.querySelectorAll('.bulk-check:checked').length;
            const bar = document.getElementById('bulkBar');
            document.getElementById('bulkCount').textContent = n;
            bar.classList.toggle('hidden', n === 0);
            bar.classList.toggle('flex', n > 0);

            const all = document.getElementById('checkAll');
            if (all) {
                all.checked = checks.length > 0 && n === checks.length;
                all.indeterminate = n > 0 && n < checks.length;
            }
        }

        function toggleSelectAll(src) {
            bulkChecks().forEach(c => c.checked = src.checked);
            updateBulkBar();
        }

        function clearBulk() {
            bulkChecks().forEach(c => c.checked = false);
            updateBulkBar();
        }

        function submitBulkDelete() {
            const checked = document.querySelectorAll('.bulk-check:checked');
            if (!checked.length) return;

            document.getElementById('bulkConfirmCount').textContent = checked.length;

            const list = document.getElementById('bulkConfirmList');
            list.innerHTML = '';
            checked.forEach(c => {
                const li = document.createElement('li');
                li.className = 'flex items-center gap-2 text-xs font-semibold text-gray-700';
                const dot = document.createElement('span');
                dot.className = 'h-1.5 w-1.5 shrink-0 rounded-full bg-rose-500';
                const nama = document.createElement('span');
                nama.className = 'truncate';
                nama.textContent = c.dataset.nama || ('Produk #' + c.value);
                li.append(dot, nama);
                list.appendChild(li);
            });

            const box = document.getElementById('modalBulkDeleteBox');
            document.getElementById('modalBulkDelete').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            });
        }

        function closeBulkConfirm() {
            const box = document.getElementById('modalBulkDeleteBox');
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
            document.getElementById('modalBulkDelete').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function confirmBulkDelete() {
            document.getElementById('formBulkDelete').submit();
        }

        document.getElementById('modalBulkDelete')?.addEventListener('click', function(e) {
            if (e.target === this) closeBulkConfirm();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeBulkConfirm();
        });
    </script>
@endsection