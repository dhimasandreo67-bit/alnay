@extends('layouts.public')

@section('title', 'Koleksi Produk — ' . config('alnay.brand.nama'))

@section('content')

{{-- ============================================
     HEADER KOLEKSI
     ============================================ --}}
<section class="bg-cream-dim/50 border-b border-line">
    <div class="max-w-7xl mx-auto px-4 py-8 md:py-12">
        <h1 class="font-display text-3xl md:text-4xl text-ink leading-tight tracking-tight mb-2">
            Koleksi Produk
        </h1>
        <p class="text-sm text-muted">
            {{ $produkList->total() }} busana adat siap sewa maupun beli — cari berdasarkan kategori, warna, atau harga.
        </p>
    </div>
</section>

{{-- ============================================
     KONTEN UTAMA — GRID + SIDEBAR
     ============================================ --}}
<div class="max-w-7xl mx-auto px-4 py-8 md:py-12">

    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6 md:gap-8">

        {{-- ============================================
             SIDEBAR FILTER (STICKY)
             ============================================ --}}
        <aside class="lg:sticky lg:top-24 h-fit">
            <form method="GET" action="{{ route('koleksi.index') }}"
                class="bg-panel border border-line rounded-xl p-5 space-y-5 animate-fade-up">

                {{-- Jenis Transaksi --}}
                <div>
                    <p class="text-[11px] font-bold text-ink mb-2.5">Jenis Transaksi</p>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="jenis" value="sewa"
                                {{ request('jenis') === 'sewa' ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-line text-gold focus:ring-gold focus:ring-offset-0">
                            <span class="text-[13px] text-ink">Bisa Disewa</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="jenis" value="beli"
                                {{ request('jenis') === 'beli' ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-line text-gold focus:ring-gold focus:ring-offset-0">
                            <span class="text-[13px] text-ink">Bisa Beli/Disewa</span>
                        </label>
                    </div>
                </div>

                <hr class="border-line">

                {{-- Kategori --}}
                <div>
                    <p class="text-[11px] font-bold text-ink mb-2.5">Kategori</p>
                    <div class="space-y-2">
                        @foreach ($kategoriList as $k)
                            <label class="flex items-center justify-between gap-2 cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" name="kategori[]" value="{{ $k->kode_kategori }}"
                                        {{ in_array($k->kode_kategori, (array) request('kategori', [])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-line text-gold focus:ring-gold focus:ring-offset-0">
                                    <span class="text-[13px] text-ink">{{ $k->nama_kategori }}</span>
                                </span>
                                <span class="text-[11px] text-muted">{{ $k->produk_count ?? '' }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <hr class="border-line">

                {{-- Rentang Harga --}}
                <div>
                    <p class="text-[11px] font-bold text-ink mb-2.5">Rentang Harga</p>
                    <div class="space-y-2">
                        @php
                            $hargaOpsi = [
                                '0-1000000' => 'Di bawah Rp 1.000.000',
                                '1000000-3000000' => 'Rp 1.000.000 – 3.000.000',
                                '3000000-5000000' => 'Rp 3.000.000 – 5.000.000',
                                '5000000+' => 'Di atas Rp 5.000.000',
                            ];
                        @endphp
                        @foreach ($hargaOpsi as $val => $label)
                            <label class="block cursor-pointer">
                                <input type="radio" name="harga" value="{{ $val }}" class="peer sr-only"
                                    {{ request('harga') === $val ? 'checked' : '' }}>
                                <div class="px-3 py-2 rounded-md border border-line text-[12px] text-ink transition-all
                                            peer-checked:border-gold peer-checked:bg-gold/5 peer-checked:text-gold peer-checked:font-medium
                                            hover:border-gold/50">
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <hr class="border-line">

                {{-- Warna --}}
                <div>
                    <p class="text-[11px] font-bold text-ink mb-2.5">Warna</p>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $warnaOpsi = [
                                'Hitam' => 'bg-[#1a1a1a]',
                                'Maroon' => 'bg-[#6b1c2a]',
                                'Gold' => 'bg-[#c99a32]',
                                'Cream' => 'bg-[#f5e6c8]',
                                'Hijau' => 'bg-[#2a5c3f]',
                                'Coklat' => 'bg-[#5c3a21]',
                            ];
                        @endphp
                        @foreach ($warnaOpsi as $w => $class)
                            <label class="cursor-pointer">
                                <input type="radio" name="warna" value="{{ $w }}" class="sr-only"
                                    {{ request('warna') === $w ? 'checked' : '' }}>
                                <span class="block w-6 h-6 rounded-full {{ $class }} ring-2 ring-offset-1 ring-transparent transition-all
                                            peer-checked:ring-gold
                                            {{ request('warna') === $w ? 'ring-gold' : '' }}"
                                    title="{{ $w }}"></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Tombol Reset --}}
                <a href="{{ route('koleksi.index') }}"
                class="block text-center py-2 rounded-md bg-cream text-[11px] font-semibold text-ink hover:bg-cream-dim transition-colors">
                    Reset Semua Filter
                </a>
            </form>
        </aside>

        {{-- ============================================
             AREA PRODUK
             ============================================ --}}
        <div>

            {{-- Toolbar: Info + Sort --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 animate-fade-up" style="animation-delay: 0.1s;">
                <p class="text-sm text-muted">
                    Menampilkan <b class="text-ink">{{ $produkList->firstItem() ?? 0 }}–{{ $produkList->lastItem() ?? 0 }}</b>
                    dari <b class="text-ink">{{ $produkList->total() }}</b> produk
                </p>

                <form method="GET" action="{{ route('koleksi.index') }}" class="flex items-center gap-2">
                    @foreach (request()->except('sort') as $key => $val)
                        @if (is_array($val))
                            @foreach ($val as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <label for="sort" class="text-xs text-muted">Urutkan:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()"
                            class="px-3 py-1.5 rounded-md border border-line bg-panel text-sm text-ink focus:outline-none focus:border-gold cursor-pointer">
                        <option value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="populer" {{ request('sort') === 'populer' ? 'selected' : '' }}>Terpopuler</option>
                        <option value="termurah" {{ request('sort') === 'termurah' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="termahal" {{ request('sort') === 'termahal' ? 'selected' : '' }}>Harga Tertinggi</option>
                    </select>
                </form>
            </div>

            {{-- Grid Produk --}}
            @if ($produkList->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4 items-stretch">
                    @foreach ($produkList as $p)
                        <x-product-card :produk="$p" />
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-10">
                    {{ $produkList->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-20 border-2 border-dashed border-line rounded-2xl animate-fade-up">
                    <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center text-gold mx-auto mb-4">
                        <x-icon name="search" :size="28" />
                    </div>
                    <h3 class="font-display text-xl text-ink mb-2">Tidak ada produk ditemukan</h3>
                    <p class="text-sm text-muted max-w-md mx-auto mb-6">
                        Coba ubah filter atau reset untuk melihat semua koleksi.
                    </p>
                    <a href="{{ route('koleksi.index') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold/90 transition-colors min-h-[44px]">
                        Reset Filter
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('aside form input[type="radio"], aside form input[type="checkbox"]').forEach(el => {
        el.addEventListener('change', () => el.closest('form').submit());
    });
</script>
@endpush

@endsection