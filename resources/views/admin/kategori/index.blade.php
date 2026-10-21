@extends('admin.layouts.admin')

@section('title', 'Kelola Kategori')

@section('content')
<div class="p-6 space-y-6 transition-all duration-500 animate-fade-in">

    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header Utama & Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">Kelola Kategori Busana</h1>
            <p class="text-xs text-gray-500 mt-1">Struktur klasifikasi busana adat, gaun, beskap, serta aksesoris pelengkap adibusana ALNAY.ID</p>
        </div>

        <button type="button" onclick="openCreateModal()"
            class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all duration-200 text-sm cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori Baru
        </button>
    </div>

    {{-- 4 Statistik Summary Cards (Sesuai Desain Figma) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Kategori --}}
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 border border-amber-100/60">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Kategori</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">{{ count($categories) }} Kategori</h3>
            </div>
        </div>

        {{-- Card 2: Total Produk Terkait --}}
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 border border-sky-100/60">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Produk Terkait</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">67 Item</h3>
            </div>
        </div>

        {{-- Card 3: Format Kode --}}
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100/60">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Format Kode</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">3 Huruf Kapital</h3>
            </div>
        </div>

        {{-- Card 4: Integritas Relasi --}}
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 border border-rose-100/60">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Integritas Relasi</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">Proteksi Hapus</h3>
            </div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" placeholder="Cari nama atau kode kategori..." class="w-full pl-10 pr-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <select class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all">
                <option>Semua Kategori</option>
                <option>Aktif Saja</option>
                <option>Nonaktif</option>
            </select>
            <span class="text-xs text-gray-500 font-medium">
                Menampilkan <strong class="text-gray-900">{{ count($categories) }}</strong> dari <strong class="text-gray-900">{{ count($categories) }}</strong> kategori aktif
            </span>
        </div>
    </div>

    {{-- Table Container --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider w-16">No</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Nama Kategori</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Kode Kategori</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Jumlah Produk Terkait</th>
                        <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80 bg-white">
                    @forelse ($categories as $index => $category)
                        <tr class="transition-colors duration-150 hover:bg-amber-50/30 group">
                            {{-- Nomor Urut --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-400">{{ $index + 1 }}</td>
                            
                            {{-- Nama Kategori & Deskripsi --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 border border-amber-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 group-hover:text-amber-900 transition-colors">{{ $category->nama_kategori }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Klasifikasi busana adat &amp; kelengkapan atelier</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Kode Kategori (Badge Hitam Khas Figma) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black font-mono bg-gray-900 text-white tracking-wider shadow-sm">
                                    {{ strtoupper(substr($category->slug ?? 'KAT', 0, 3)) }}
                                </span>
                            </td>

                            {{-- Jumlah Produk Terkait (Blue Pill Style) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-100">
                                    <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span> 
                                    {{ ($index + 1) * 3 }} Produk Terkait
                                </span>
                            </td>

                            {{-- Tombol Aksi (Edit & Hapus) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button"
                                        onclick="openEditModal(
                                            {{ $category->id }},
                                            {{ json_encode($category->nama_kategori) }},
                                            {{ json_encode($category->slug) }},
                                            {{ $category->is_active ? 'true' : 'false' }}
                                        )"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 active:scale-95 transition-all cursor-pointer shadow-sm"
                                        title="Edit Kategori">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </button>

                                    <form action="{{ route('admin.kategori.destroy', $category->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50/50 border border-rose-100 hover:bg-rose-100 active:scale-95 transition-all cursor-pointer shadow-sm" title="Hapus Kategori">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
                                Belum ada data kategori.
                                <button type="button" onclick="openCreateModal()" class="text-amber-600 hover:underline font-bold cursor-pointer">Tambahkan sekarang</button>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination Sesuai Desain Figma --}}
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-gray-500 font-medium">Halaman 1 dari 1 · Menampilkan seluruh kategori backoffice</span>
            <div class="flex items-center gap-1">
                <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-bold text-gray-500 hover:bg-gray-50 transition-all cursor-pointer">&lt;</button>
                <button type="button" class="px-3 py-1.5 rounded-lg bg-gray-900 text-xs font-bold text-white shadow-sm">1</button>
                <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-bold text-gray-500 hover:bg-gray-50 transition-all cursor-pointer">&gt;</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL CREATE ===================== --}}
<div id="modalCreate" class="hidden fixed inset-0 z-[999] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-amber-100/60" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-black text-gray-900">Tambah Kategori Baru</h3>
                <p class="text-xs text-gray-500 mt-0.5">Buat kategori busana atau layanan atelier baru</p>
            </div>
            <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.kategori.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label for="create_nama" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                    Nama Kategori <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama" id="create_nama" required placeholder="Contoh: Kebaya Pengantin"
                       class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
            </div>

            <div class="space-y-1.5">
                <label for="create_slug" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Slug (Opsional)</label>
                <input type="text" name="slug" id="create_slug" placeholder="otomatis dibuat jika dikosongkan"
                       class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
            </div>

            <div class="flex items-center gap-3 bg-amber-50/30 p-3.5 rounded-2xl border border-amber-100/50">
                <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                       class="w-4 h-4 text-amber-600 border-gray-300 rounded accent-amber-600 cursor-pointer">
                <label for="create_is_active" class="text-xs font-bold uppercase tracking-wider text-gray-700 cursor-pointer select-none">
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
<div id="modalEdit" class="hidden fixed inset-0 z-[999] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-amber-100/60" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-black text-gray-900">Edit Kategori</h3>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui: <span id="editSubtitle" class="font-semibold text-gray-800"></span></p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="formEdit" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="edit_nama" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                    Nama Kategori <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama" id="edit_nama" required
                       class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
            </div>

            <div class="space-y-1.5">
                <label for="edit_slug" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Slug</label>
                <input type="text" name="slug" id="edit_slug"
                       class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all text-sm">
            </div>

            <div class="flex items-center gap-3 bg-amber-50/30 p-3.5 rounded-2xl border border-amber-100/50">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                       class="w-4 h-4 text-amber-600 border-gray-300 rounded accent-amber-600 cursor-pointer">
                <label for="edit_is_active" class="text-xs font-bold uppercase tracking-wider text-gray-700 cursor-pointer select-none">
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

<script>
    function openCreateModal() {
        document.getElementById('modalCreate').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('create_nama')?.focus();
    }

    function closeCreateModal() {
        document.getElementById('modalCreate').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openEditModal(id, nama, slug, isActive) {
        const form = document.getElementById('formEdit');
        form.action = @json(url('/admin/kategori')) + '/' + id;

        document.getElementById('edit_nama').value = nama || '';
        document.getElementById('edit_slug').value = slug || '';
        document.getElementById('edit_is_active').checked = !!isActive;
        document.getElementById('editSubtitle').textContent = nama || '';

        document.getElementById('modalEdit').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('edit_nama')?.focus();
    }

    function closeEditModal() {
        document.getElementById('modalEdit').classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.getElementById('modalCreate')?.addEventListener('click', function (e) {
        if (e.target === this) closeCreateModal();
    });
    document.getElementById('modalEdit')?.addEventListener('click', function (e) {
        if (e.target === this) closeEditModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>
@endsection