@extends('admin.layouts.admin')

@section('title', 'Tambah Produk Baru')

@section('content')
<div class="p-8 space-y-6 transition-all duration-500 animate-fade-up">

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-5 rounded-2xl text-sm shadow-sm flex items-start gap-3 animate-fade-up">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <ul class="list-disc list-inside space-y-1 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-2">
        <div>
            <nav class="text-xs text-muted font-bold tracking-wider uppercase mb-1.5 flex items-center gap-2">
                <a href="{{ route('admin.produk.index') }}" class="hover:text-gold transition-colors">Produk</a>
                <span class="text-gold">/</span>
                <span class="text-ink">Tambah Baru</span>
            </nav>
            <h1 class="text-3xl font-display font-bold tracking-tight text-ink">Tambah Produk Baru</h1>
            <p class="text-sm text-muted mt-1">Tambahkan busana atau aksesoris etnik baru ke dalam katalog atelier.</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 bg-cream text-gold border border-gold/20 px-4 py-2 rounded-xl text-xs font-bold shadow-sm">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-gold"></span>
                </span>
                Status: Draf Baru
            </span>
        </div>
    </div>

    @include('admin.produk._form', [
        'actionUrl'   => route('admin.produk.store'),
        'method'      => 'POST',
        'submitLabel' => 'Simpan Produk',
        'formData'    => [
            'sku'             => old('sku', ''),
            'kategoriId'      => old('kategori_id', ''),
            'bisaDisewa'      => (bool) old('bisa_disewa', true),
            'dapatDijual'     => (bool) old('dapat_dijual', true),
            'status'          => old('status', 'tersedia'),
            'nama'            => old('nama', ''),
            'deskripsi'       => old('deskripsi', ''),
            'bahan'           => old('bahan', ''),
            'motif'           => old('motif', ''),
            'berat'           => old('berat'),
            'warna'           => old('warna', []),
            'ukuranVariasi'   => old('ukuran', []),
            'hargaSewa'       => old('harga_sewa'),
            'hargaJual'       => old('harga_jual'),
            'stok'            => old('stok', 1),
            'dendaPerHari'    => old('denda_per_hari_telat', 50000),
            'diskon'          => old('diskon_persen', 0),
            'existingFoto'    => [null, null, null, null],
            'ratingRataRata'  => 0,
            'jumlahRating'    => 0,
        ],
    ])
</div>
@endsection