@extends('admin.layouts.admin')

@section('title', 'Edit Produk')

@php
    $ukuranVariasiAwal = collect($produk->ukuran ?? [])->map(function ($item) {
        return is_array($item)
            ? ['ukuran' => $item['ukuran'] ?? '', 'stok' => (int) ($item['stok'] ?? 0)]
            : ['ukuran' => $item, 'stok' => 0];
    })->values()->all();
@endphp

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

    @if(session('success'))
        <div class="flex items-center gap-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm animate-fade-up" role="alert">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-sm font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-2">
        <div>
            <nav class="text-xs text-muted font-bold tracking-wider uppercase mb-1.5 flex items-center gap-2">
                <a href="{{ route('admin.produk.index') }}" class="hover:text-gold transition-colors">Produk</a>
                <span class="text-gold">/</span>
                <span class="text-ink">Edit Data</span>
            </nav>
            <h1 class="text-3xl font-display font-bold tracking-tight text-ink">Edit Produk</h1>
            <p class="text-sm text-muted mt-1">Mengubah data busana <strong class="text-ink">{{ $produk->nama }}</strong></p>
        </div>

        <form method="POST" action="{{ route('admin.produk.destroy', $produk->id) }}"
              onsubmit="return confirm('Yakin ingin menghapus produk &quot;{{ $produk->nama }}&quot;? Tindakan ini tidak bisa dibatalkan.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-rose-200 bg-panel text-rose-600 font-bold text-sm hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 active:scale-95 transition-all duration-300 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Produk
            </button>
        </form>
    </div>

        @include('admin.produk._form', [
        'actionUrl'   => route('admin.produk.update', $produk->id),
        'method'      => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
        'formData'    => [
            'sku'             => old('sku', $produk->sku),
            'kategoriId'      => old('kategori_id', (string) $produk->kategori_id),
            'bisaDisewa'      => (bool) old('bisa_disewa', $produk->bisa_disewa),
            'dapatDijual'     => (bool) old('dapat_dijual', $produk->dapat_dijual),
            'status'          => old('status', $produk->status),
            'nama'            => old('nama', $produk->nama),
            'deskripsi'       => old('deskripsi', $produk->deskripsi),
            'bahan'           => old('bahan', $produk->bahan),
            'motif'           => old('motif', $produk->motif),
            'berat'           => old('berat', $produk->berat),
            'warna'           => old('warna', $produk->warna ?? []),
            'ukuranVariasi'   => old('ukuran', $ukuranVariasiAwal),
            
            // TAMBAHKAN (int) DI BAWAH INI:
            'hargaSewa'       => (int) old('harga_sewa', $produk->harga_sewa),
            'dp'              => (int) old('dp', $produk->dp),
            'hargaJual'       => (int) old('harga_jual', $produk->harga_jual),
            'stok'            => old('stok', $produk->stok),
            'dendaPerHari'    => (int) old('denda_per_hari_telat', $produk->denda_per_hari_telat),
            'diskon'          => old('diskon_persen', $produk->diskon_persen),
            
            'existingFoto'    => array_pad(array_slice((array) ($produk->foto_produk ?? []), 0, 4), 4, null),
            'previewFoto'     => [
                                    $produk->fotoUrl(0),
                                    $produk->fotoUrl(1),
                                    $produk->fotoUrl(2),
                                    $produk->fotoUrl(3),
                                ],
            'ratingRataRata'  => $produk->rating_rata_rata ?? 0,
            'jumlahRating'    => $produk->jumlah_rating ?? 0,
        ],
    ])
</div>
@endsection