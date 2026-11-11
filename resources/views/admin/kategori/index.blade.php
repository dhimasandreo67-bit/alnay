@extends('admin.layouts.admin')

@section('title', 'Kelola Kategori')

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

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header Utama & Tombol Tambah --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Kelola Kategori Busana</h1>
                <p class="text-xs text-gray-500 mt-1">Struktur klasifikasi busana adat, gaun, beskap, serta aksesoris
                    pelengkap adibusana ALNAY.ID</p>
            </div>

            <button type="button" onclick="openCreateModal()"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all duration-200 text-sm cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kategori Baru
            </button>
        </div>

        {{-- 4 Statistik Summary Cards (Sesuai Desain Dashboard) --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Card 1: Total Kategori (Tema Amber/Gold) --}}
            <div
                class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Total Kategori</span>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-amber-950">{{ $categories->count() }}</span>
                    <span class="text-sm font-bold text-amber-800/60">Kategori</span>
                </div>
                <p class="mt-2 text-xs font-medium text-amber-700/80">Tersedia di sistem</p>
            </div>

            {{-- Card 2: Total Produk Terkait (Tema Sky/Blue) --}}
            <div
                class="group group-shine relative overflow-hidden rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50/80 via-white to-sky-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-800/80">Total Produk Terkait</span>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-blue-500 text-white shadow-md shadow-sky-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-sky-950">{{ $totalProduk }}</span>
                    <span class="text-sm font-bold text-sky-800/60">Item</span>
                </div>
                <div
                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-sky-700 bg-sky-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-sky-500 animate-pulse"></span>
                    <span>Terkait dengan kategori</span>
                </div>
            </div>

            {{-- Card 3: Format Kode (Tema Emerald/Green) --}}
            <div
                class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Format Kode</span>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <span class="text-2xl font-extrabold tracking-tight text-emerald-950">3 Huruf Kapital</span>
                </div>
                <p class="mt-2 text-xs text-emerald-800/60">Standar penamaan SKU</p>
            </div>

            {{-- Card 4: Integritas Relasi (Tema Rose/Pink) --}}
            <div
                class="group group-shine relative overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-br from-rose-50/80 via-white to-rose-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-rose-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-800/80">Integritas Relasi</span>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-extrabold tracking-tight text-rose-950">Proteksi Hapus</span>
                </div>
                <p class="mt-2 text-xs text-rose-600 font-medium">Data aman dari penghapusan paksa</p>
            </div>

        </div>

        {{-- Filter & Search Toolbar --}}
        <form method="GET" action="{{ route('admin.kategori.index') }}" onsubmit="event.preventDefault()"
            class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">

            {{-- Pencarian --}}
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="search" id="cariKategori" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama, kode, atau slug..." autocomplete="off" oninput="terapkanFilterKategori()"
                    class="w-full pl-10 pr-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
            </div>

            {{-- Filter & Tombol --}}
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">

                {{-- Dropdown Status --}}
                <select id="filterStatusKategori" name="status" onchange="terapkanFilterKategori()"
                    class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer">
                    <option value="">Semua Kategori</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif Saja</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                {{-- Info Jumlah --}}
                <span class="text-xs text-gray-500 font-medium">
                    Menampilkan <strong id="infoJumlah" class="text-gray-900">{{ count($categories) }}</strong> kategori
                </span>
            </div>
        </form>

        {{-- Peringatan: semua kategori punya produk, jadi tidak ada yang bisa dihapus --}}
        @if ($categories->count() > 0 && $categories->every(fn($c) => $c->produks_count > 0))
            <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-2xl shadow-sm"
                role="alert">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold">Semua kategori tidak dapat dihapus</p>
                    <p class="text-xs text-amber-800/80 mt-0.5">Setiap kategori masih memiliki produk terkait. Pindahkan
                        atau hapus produknya terlebih dahulu agar kategori bisa dipilih untuk dihapus.</p>
                </div>
            </div>
        @endif

        {{-- Table Container --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Bar hapus massal: muncul saat ada yang dicentang --}}
            <form id="formBulkDelete" method="POST" action="{{ route('admin.kategori.bulk-destroy') }}">
                @csrf
                @method('DELETE')
            </form>
            <div id="bulkBar"
                class="hidden items-center justify-between gap-3 px-6 py-3 bg-rose-50 border-b border-rose-100">
                <span class="text-xs font-bold text-rose-700"><span id="bulkCount">0</span> kategori dipilih</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="clearBulk()"
                        class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="button" onclick="submitBulkDelete()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-95 transition-all cursor-pointer shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus Terpilih
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox" id="checkAll" onchange="toggleSelectAll(this)"
                                    @disabled($categories->count() === 0 || $categories->every(fn($c) => $c->produks_count > 0))
                                    class="w-4 h-4 rounded border-gray-300 accent-amber-600 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                    title="Pilih semua">
                            </th>
                            <th
                                class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider w-16">
                                No</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                Nama
                                Kategori</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                Kode
                                Kategori</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                Jumlah
                                Produk Terkait</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/80 bg-white">
                        @forelse ($categories as $index => $category)
                            @php $kode = $category->kode_kategori ?: strtoupper(substr($category->slug ?? 'KAT', 0, 3)); @endphp
                            <tr data-cari="{{ \Illuminate\Support\Str::lower($category->nama_kategori . ' ' . $category->slug . ' ' . $kode) }}"
                                data-status="{{ $category->is_active ? '1' : '0' }}"
                                class="transition-colors duration-150 hover:bg-amber-50/30 group">
                                {{-- Checklist --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="ids[]" value="{{ $category->id }}"
                                        form="formBulkDelete" data-nama="{{ $category->nama_kategori }}" onchange="updateBulkBar()"
                                        @disabled($category->produks_count > 0)
                                        class="bulk-check w-4 h-4 rounded border-gray-300 accent-amber-600 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                        title="{{ $category->produks_count > 0 ? 'Tidak bisa dipilih, masih ada ' . $category->produks_count . ' produk terkait' : 'Pilih untuk dihapus' }}">
                                </td>

                                {{-- Nomor Urut --}}
                                <td class="no-urut px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-400">
                                    {{ $index + 1 }}
                                </td>

                                {{-- Nama Kategori & Deskripsi --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($category->foto)
                                            <img src="{{ asset('storage/' . $category->foto) }}"
                                                alt="{{ $category->nama_kategori }}" loading="lazy"
                                                class="h-12 w-12 shrink-0 rounded-2xl object-cover border border-amber-100 shadow-sm">
                                        @else
                                            <div
                                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 border border-amber-100">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="text-sm font-bold text-gray-900 group-hover:text-amber-900 transition-colors">
                                                    {{ $category->nama_kategori }}</div>

                                                {{-- Badge Status --}}
                                                @if ($category->is_active)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">Aktif</span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-gray-100 text-gray-500 border border-gray-200">Nonaktif</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">Klasifikasi busana adat &amp;
                                                kelengkapan
                                                atelier</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kode Kategori (Badge Hitam Khas Figma) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black font-mono bg-gray-900 text-white tracking-wider shadow-sm">
                                        {{ $kode }}
                                    </span>
                                </td>

                                {{-- Jumlah Produk Terkait (Blue Pill Style, data asli dari relasi) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('admin.produk.index', ['kategori_id' => $category->id]) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-100 hover:bg-sky-100 transition-colors cursor-pointer">
                                        <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                        {{ $category->produks_count }} Produk Terkait
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
                                    Belum ada data kategori.
                                    <button type="button" onclick="openCreateModal()"
                                        class="text-amber-600 hover:underline font-bold cursor-pointer">Tambahkan
                                        sekarang</button>.
                                </td>
                            </tr>
                        @endforelse

                        {{-- Muncul saat pencarian tidak menemukan apa pun --}}
                        <tr id="barisKosongCari" class="hidden">
                            <td colspan="6" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
                                Tidak ada kategori yang cocok dengan pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination Sesuai Desain Figma --}}
            <div
                class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-gray-500 font-medium">Halaman 1 dari 1 · Menampilkan seluruh kategori
                    backoffice</span>
                <div class="flex items-center gap-1">
                    <button type="button"
                        class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-bold text-gray-500 hover:bg-gray-50 transition-all cursor-pointer">&lt;</button>
                    <button type="button"
                        class="px-3 py-1.5 rounded-lg bg-gray-900 text-xs font-bold text-white shadow-sm">1</button>
                    <button type="button"
                        class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-bold text-gray-500 hover:bg-gray-50 transition-all cursor-pointer">&gt;</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL CREATE ===================== --}}
    <div id="modalCreate"
        class="hidden fixed inset-0 z-[999] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-amber-100/60"
            onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Tambah Kategori Baru</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Buat kategori busana atau layanan atelier baru</p>
                </div>
                <button type="button" onclick="closeCreateModal()"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.kategori.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label for="create_nama" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_kategori" id="create_nama" required autocomplete="off"
                        placeholder="Contoh: Kebaya Pengantin" oninput="rekomendasiKategori()"
                        class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
                </div>

                <div class="space-y-1.5">
                    <label for="create_kode" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Kode Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kode_kategori" id="create_kode" required autocomplete="off"
                        placeholder="Contoh: JSB (3 huruf kapital)" maxlength="10" oninput="ubahManual('kode')"
                        class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm uppercase font-mono tracking-wider">
                    <p class="text-[10px] text-gray-400">Rekomendasi otomatis dari nama, tetap bisa diubah.</p>
                </div>

                <div class="space-y-1.5">
                    <label for="create_slug" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Slug
                        (Opsional)</label>
                    <input type="text" name="slug" id="create_slug" autocomplete="off"
                        placeholder="otomatis dibuat dari nama kategori" oninput="ubahManual('slug')"
                        class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
                    <p class="text-[10px] text-gray-400">Kosongkan kolom untuk kembali ke rekomendasi otomatis.</p>
                </div>

                <div class="space-y-1.5">
                    <label for="create_foto" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Foto
                        Kategori</label>
                    <input type="file" name="foto" id="create_foto" accept="image/*"
                        class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
                </div>

                <div class="flex items-center gap-3 bg-amber-50/30 p-3.5 rounded-2xl border border-amber-100/50">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                        class="w-4 h-4 text-amber-600 border-gray-300 rounded accent-amber-600 cursor-pointer">
                    <label for="create_is_active"
                        class="text-xs font-bold uppercase tracking-wider text-gray-700 cursor-pointer select-none">
                        Aktifkan kategori ini untuk sistem
                    </label>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit"
                        class="flex-1 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-amber-500/25 active:scale-95 transition-all text-sm cursor-pointer">
                        Simpan Kategori
                    </button>
                    <button type="button" onclick="closeCreateModal()"
                        class="px-6 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold text-sm hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL EDIT ===================== --}}
    <div id="modalEdit"
        class="hidden fixed inset-0 z-[999] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-amber-100/60"
            onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Edit Kategori</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Perbarui: <span id="editSubtitle"
                            class="font-semibold text-gray-800"></span></p>
                </div>
                <button type="button" onclick="closeEditModal()"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="formEdit" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="space-y-1.5">
                    <label for="edit_nama" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_kategori" id="edit_nama" required
                        class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
                </div>

                <div class="space-y-1.5">
                    <label for="edit_kode" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Kode Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kode_kategori" id="edit_kode" required
                        class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
                </div>

                <div class="space-y-1.5">
                    <label for="edit_slug"
                        class="block text-xs font-bold uppercase tracking-wider text-gray-700">Slug</label>
                    <input type="text" name="slug" id="edit_slug"
                        class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
                </div>

                <div class="space-y-1.5">
                    <label for="edit_foto" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Foto
                        Kategori</label>
                    <input type="file" name="foto" id="edit_foto" accept="image/*"
                        class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
                    <div id="edit_foto_existing" class="mt-2 hidden">
                        <img src="" alt="Foto saat ini" class="w-16 h-16 rounded-lg object-cover border">
                        <p class="text-[10px] text-gray-400 mt-1">Foto saat ini. Unggah baru untuk mengganti.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-amber-50/30 p-3.5 rounded-2xl border border-amber-100/50">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                        class="w-4 h-4 text-amber-600 border-gray-300 rounded accent-amber-600 cursor-pointer">
                    <label for="edit_is_active"
                        class="text-xs font-bold uppercase tracking-wider text-gray-700 cursor-pointer select-none">
                        Aktifkan kategori ini untuk sistem
                    </label>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit"
                        class="flex-1 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-amber-500/25 active:scale-95 transition-all text-sm cursor-pointer">
                        Perbarui Kategori
                    </button>
                    <button type="button" onclick="closeEditModal()"
                        class="px-6 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold text-sm hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL KONFIRMASI HAPUS MASSAL ===================== --}}
    <div id="modalBulkDelete"
        class="hidden fixed inset-0 z-[1000] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div id="modalBulkDeleteBox"
            class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-rose-100 transform transition-all duration-200 scale-95 opacity-0"
            onclick="event.stopPropagation()">
            <div class="flex flex-col items-center text-center">
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 ring-8 ring-rose-50/60 text-rose-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-black text-gray-900">Hapus <span id="bulkConfirmCount">0</span> Kategori?</h3>
                <p class="mt-1.5 text-xs text-gray-500 leading-relaxed">
                    Kategori berikut beserta fotonya akan dihapus permanen dan <strong class="text-rose-600">tidak
                        dapat dikembalikan</strong>.
                </p>
            </div>

            <ul id="bulkConfirmList"
                class="mt-5 max-h-40 overflow-y-auto space-y-1.5 rounded-2xl border border-gray-100 bg-gray-50/70 p-3 text-left">
            </ul>

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
        // ===== Rekomendasi otomatis kode & slug (modal Tambah Kategori) =====
        // Daftar kode/slug yang sudah dipakai (dari seluruh kategori, bukan hanya hasil filter)
        const KODE_ADA = new Set(@json($kodeAda ?? []).map(k => String(k).toUpperCase()));
        const SLUG_ADA = new Set(@json($slugAda ?? []).map(s => String(s).toLowerCase()));
        const KATA_SAMBUNG = ['DAN', 'DI', 'DARI', 'UNTUK', 'YANG', 'DENGAN'];

        // true = pengguna sudah mengetik sendiri, jadi rekomendasi tidak menimpanya
        const isiManual = { kode: false, slug: false };

        function bersihkanTeks(teks) {
            return String(teks || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        function buatSlug(nama) {
            return bersihkanTeks(nama).toLowerCase()
                .replace(/&/g, ' dan ')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function slugUnik(dasar) {
            if (!dasar) return '';
            let slug = dasar, i = 2;
            while (SLUG_ADA.has(slug)) slug = dasar + '-' + i++;
            return slug;
        }

        // Kode 3 huruf: inisial tiap kata (maks 3), kekurangannya diambil dari huruf berikutnya.
        // "Kebaya Pengantin" -> KPE, "Beskap" -> BES, "Jas Songket Batik" -> JSB
        function kodeUnik(nama) {
            let kata = bersihkanTeks(nama).toUpperCase().split(/[^A-Z0-9]+/).filter(Boolean);
            const penting = kata.filter(k => !KATA_SAMBUNG.includes(k));
            if (penting.length) kata = penting;
            if (!kata.length) return '';

            const inisial = kata.slice(0, 3).map(k => k[0]).join('');
            const sisa = kata[0].slice(1) + kata.slice(1).map(k => k.slice(1)).join('');
            const dasar = (inisial + sisa).slice(0, 3);

            if (!KODE_ADA.has(dasar)) return dasar;

            // Bentrok: ganti huruf ke-3 dengan huruf lain dari nama, lalu pakai angka
            const dua = dasar.slice(0, 2);
            for (const huruf of sisa) {
                if (!KODE_ADA.has(dua + huruf)) return dua + huruf;
            }
            for (let n = 1; n <= 99; n++) {
                const calon = dua + n;
                if (!KODE_ADA.has(calon)) return calon;
            }
            return dasar;
        }

        function rekomendasiKategori() {
            const nama = document.getElementById('create_nama').value;
            const kode = document.getElementById('create_kode');
            const slug = document.getElementById('create_slug');

            if (!isiManual.kode) kode.value = kodeUnik(nama);
            if (!isiManual.slug) slug.value = slugUnik(buatSlug(nama));
        }

        function ubahManual(kolom) {
            const el = document.getElementById(kolom === 'kode' ? 'create_kode' : 'create_slug');
            if (kolom === 'kode') el.value = el.value.toUpperCase();

            // Kolom dikosongkan -> kembali ke rekomendasi otomatis
            isiManual[kolom] = el.value.trim() !== '';
            if (!isiManual[kolom]) rekomendasiKategori();
        }

        function openCreateModal() {
            isiManual.kode = false;
            isiManual.slug = false;
            document.getElementById('modalCreate').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            rekomendasiKategori();
            document.getElementById('create_nama')?.focus();
        }

        function closeCreateModal() {
            document.getElementById('modalCreate').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openEditModal(id, nama, slug, isActive, foto, kodeKategori) {
            const form = document.getElementById('formEdit');
            form.action = @json(url('/admin/kategori')) + '/' + id;

            document.getElementById('edit_nama').value = nama || '';
            document.getElementById('edit_slug').value = slug || '';
            document.getElementById('edit_kode').value = kodeKategori || '';
            document.getElementById('edit_is_active').checked = !!isActive;
            document.getElementById('editSubtitle').textContent = nama || '';

            // Tampilkan foto lama jika ada
            const fotoExisting = document.getElementById('edit_foto_existing');
            if (foto) {
                fotoExisting.classList.remove('hidden');
                fotoExisting.querySelector('img').src = "{{ asset('storage') }}/" + foto;
            } else {
                fotoExisting.classList.add('hidden');
            }

            document.getElementById('modalEdit').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('edit_nama')?.focus();
        }

        function closeEditModal() {
            document.getElementById('modalEdit').classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.getElementById('modalCreate')?.addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });
        document.getElementById('modalEdit')?.addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateModal();
                closeEditModal();
                closeBulkConfirm();
            }
        });


        // ===== Pencarian langsung (tanpa reload), seperti katalog kasir =====
        function terapkanFilterKategori() {
            const q = (document.getElementById('cariKategori').value || '').trim().toLowerCase();
            const st = document.getElementById('filterStatusKategori').value;
            const baris = document.querySelectorAll('tr[data-cari]');
            let n = 0;

            baris.forEach(tr => {
                const cocok = (!q || tr.dataset.cari.includes(q)) && (st === '' || tr.dataset.status === st);
                tr.classList.toggle('hidden', !cocok);

                if (cocok) {
                    n++;
                    const no = tr.querySelector('.no-urut');
                    if (no) no.textContent = n;
                } else {
                    // Baris yang tersembunyi tidak boleh ikut terhapus
                    const cb = tr.querySelector('.bulk-check');
                    if (cb) cb.checked = false;
                }
            });

            document.getElementById('infoJumlah').textContent = n;
            document.getElementById('barisKosongCari')
                .classList.toggle('hidden', !(baris.length > 0 && n === 0));
            updateBulkBar();
        }

        document.addEventListener('DOMContentLoaded', terapkanFilterKategori);

        // ===== Hapus massal (checklist) =====
        function bulkChecks() {
            // Hanya baris yang sedang tampil (hasil pencarian) yang bisa dipilih
            return [...document.querySelectorAll('.bulk-check:not(:disabled)')]
                .filter(c => !c.closest('tr').classList.contains('hidden'));
        }

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
                nama.textContent = c.dataset.nama || ('Kategori #' + c.value);
                li.append(dot, nama);
                list.appendChild(li);
            });

            const modal = document.getElementById('modalBulkDelete');
            const box = document.getElementById('modalBulkDeleteBox');
            modal.classList.remove('hidden');
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
            sessionStorage.setItem('kategori_scroll_pos', window.scrollY);
            document.getElementById('formBulkDelete').submit();
        }

        document.getElementById('modalBulkDelete')?.addEventListener('click', function(e) {
            if (e.target === this) closeBulkConfirm();
        });

        document.addEventListener("DOMContentLoaded", function() {
            // 1. Cek apakah ada posisi scroll yang tersimpan
            let scrollpos = sessionStorage.getItem('kategori_scroll_pos');
            if (scrollpos) {
                // Kembalikan ke posisi semula
                window.scrollTo(0, parseInt(scrollpos));
                // Hapus memori setelah dipakai agar tidak nyangkut
                sessionStorage.removeItem('kategori_scroll_pos');
            }

            // 2. Simpan posisi scroll sebelum form disubmit (Tambah/Edit/Hapus)
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    sessionStorage.setItem('kategori_scroll_pos', window.scrollY);
                });
            });
        });
    </script>
@endsection