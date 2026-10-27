@extends('layouts.public')

@section('title', 'Koleksi Produk — ' . config('alnay.brand.nama'))

@section('content')

    <div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans">

        {{-- ============================================
         HEADER KOLEKSI
         ============================================ --}}
        <section class="bg-cream-dim/40 border-b border-line/40">
            <div class="max-w-[1280px] mx-auto px-4 md:px-6 py-8 md:py-12">
                <h1 class="pd-up font-display text-3xl md:text-4xl text-ink/90 leading-tight tracking-tight mb-2"
                    style="--d: .05s;">
                    Koleksi Produk
                </h1>
                <p class="pd-up text-sm text-ink/55" style="--d: .15s;">
                    {{ $produkList->total() }} busana adat siap sewa maupun beli — cari berdasarkan kategori, warna, atau
                    harga.
                </p>
            </div>
        </section>

        {{-- ============================================
         KONTEN UTAMA — GRID + SIDEBAR
         ============================================ --}}
        <div class="max-w-[1280px] mx-auto px-4 md:px-6 py-8 md:py-12">
            <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6 md:gap-8">

                {{-- ============================================
                 SIDEBAR FILTER
                 ============================================ --}}
                <aside class="lg:sticky lg:top-24 h-fit pd-up" style="--d: .2s;">
                    <form method="GET" action="{{ route('koleksi.index') }}"
                        class="bg-panel border border-line/40 rounded-xl p-5 space-y-5">

                        {{-- Jenis Transaksi --}}
                        <div>
                            <p class="text-[11px] font-semibold text-ink/80 mb-2.5">Jenis Transaksi</p>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" name="jenis" value="sewa"
                                        {{ request('jenis') === 'sewa' ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-line/60 text-gold focus:ring-gold focus:ring-offset-0 transition-all">
                                    <span class="text-[13px] text-ink/70 group-hover:text-gold transition-colors">Bisa
                                        Disewa</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" name="jenis" value="beli"
                                        {{ request('jenis') === 'beli' ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-line/60 text-gold focus:ring-gold focus:ring-offset-0 transition-all">
                                    <span class="text-[13px] text-ink/70 group-hover:text-gold transition-colors">Bisa
                                        Beli/Disewa</span>
                                </label>
                            </div>
                        </div>

                        <hr class="border-line/30">

                        {{-- Kategori --}}
                        <div>
                            <p class="text-[11px] font-semibold text-ink/80 mb-2.5">Kategori</p>
                            <div class="space-y-2">
                                @foreach ($kategoriList as $k)
                                    <label class="flex items-center justify-between gap-2 cursor-pointer group">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="kategori[]" value="{{ $k->slug }}"
                                                {{ in_array($k->slug, (array) request('kategori', [])) ? 'checked' : '' }}
                                                class="w-4 h-4 rounded border-line/60 text-gold focus:ring-gold focus:ring-offset-0 transition-all">
                                            <span class="text-[13px] text-ink/70 group-hover:text-gold transition-colors">{{ $k->nama_kategori }}</span>
                                        </span>
                                        <span class="text-[11px] text-ink/40">{{ $k->produk_count ?? '' }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <hr class="border-line/30">

                        {{-- Rentang Harga --}}
                        <div>
                            <p class="text-[11px] font-semibold text-ink/80 mb-2.5">Rentang Harga</p>
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
                                        <input type="radio" name="harga" value="{{ $val }}"
                                            class="peer sr-only" {{ request('harga') === $val ? 'checked' : '' }}>
                                        <div
                                            class="px-3 py-2 rounded-md border border-line/50 text-[12px] text-ink/70 transition-all
                                                peer-checked:border-gold peer-checked:bg-gold/5 peer-checked:text-gold peer-checked:font-medium
                                                hover:border-gold/60 hover:-translate-y-px hover:shadow-sm">
                                            {{ $label }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <hr class="border-line/30">

                        {{-- Warna --}}
                        <div>
                            <p class="text-[11px] font-semibold text-ink/80 mb-2.5">Warna</p>
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
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="warna" value="{{ $w }}"
                                            class="peer sr-only" {{ request('warna') === $w ? 'checked' : '' }}>
                                        <span
                                            class="block w-7 h-7 rounded-full {{ $class }} ring-2 ring-offset-2 ring-transparent transition-all duration-200
                                                 peer-checked:ring-gold group-hover:scale-110 group-hover:ring-gold/40
                                                 {{ request('warna') === $w ? 'ring-gold scale-110' : '' }}"
                                            title="{{ $w }}"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Tombol Reset --}}
                        <a href="{{ route('koleksi.index') }}"
                            class="pd-shine block text-center py-2.5 rounded-md bg-cream text-[11px] font-semibold text-ink/70 hover:bg-cream-dim hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                            Reset Semua Filter
                        </a>
                    </form>
                </aside>

                {{-- ============================================
                 AREA PRODUK
                 ============================================ --}}
                <div>

                    {{-- Toolbar --}}
                    <div class="pd-up flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6"
                        style="--d: .25s;">
                        <p class="text-sm text-ink/55">
                            Menampilkan <b
                                class="text-ink/85">{{ $produkList->firstItem() ?? 0 }}–{{ $produkList->lastItem() ?? 0 }}</b>
                            dari <b class="text-ink/85">{{ $produkList->total() }}</b> produk
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
                            <label for="sort" class="text-xs text-ink/50">Urutkan:</label>
                            <select name="sort" id="sort" onchange="this.form.submit()"
                                class="px-3 py-1.5 rounded-md border border-line/50 bg-panel text-sm text-ink/70 focus:outline-none focus:border-gold hover:border-gold/60 cursor-pointer transition-all">
                                <option value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>
                                    Terbaru</option>
                                <option value="populer" {{ request('sort') === 'populer' ? 'selected' : '' }}>Terpopuler
                                </option>
                                <option value="termurah" {{ request('sort') === 'termurah' ? 'selected' : '' }}>Harga
                                    Terendah</option>
                                <option value="termahal" {{ request('sort') === 'termahal' ? 'selected' : '' }}>Harga
                                    Tertinggi</option>
                            </select>
                        </form>
                    </div>

                    {{-- Grid Produk --}}
                @if ($produkList->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4 items-stretch">
                        @foreach ($produkList as $index => $p)
                            <x-product-card :produk="$p" :delay="min(0.1 + ($index * 0.08), 0.8)" />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="pd-reveal mt-10">
                        {{ $produkList->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                        <div class="pd-up text-center py-20 border-2 border-dashed border-line/50 rounded-2xl">
                            <div class="pd-pop w-16 h-16 rounded-full bg-cream flex items-center justify-center text-gold mx-auto mb-4">
                                <x-icon name="search" :size="28" />
                            </div>
                            <h3 class="font-display text-xl text-ink/80 mb-2">Tidak ada produk ditemukan</h3>
                            <p class="text-sm text-ink/50 max-w-md mx-auto mb-6">
                                Coba ubah filter atau reset untuk melihat semua koleksi.
                            </p>
                            <a href="{{ route('koleksi.index') }}"
                            class="pd-shine inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold/90 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 min-h-[44px]">
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </div>
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
