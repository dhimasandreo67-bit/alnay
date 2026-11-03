@extends('layouts.public')

@section('title', 'Koleksi Produk — ' . config('alnay.brand.nama'))

@section('content')

    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes sheet-up {
            from {
                transform: translateY(100%);
            }

            to {
                transform: none;
            }
        }

        .sheet-in {
            animation: sheet-up .3s cubic-bezier(.22, .8, .3, 1);
        }

        @media (min-width: 1024px) {
            .sheet-in {
                animation: none;
            }
        }

                /* Custom scrollbar untuk filter warna */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(201, 154, 50, 0.3);
            border-radius: 10px;
        }
    </style>

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
            <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6 md:gap-8" x-data="{ filterOpen: false }"
                @keydown.escape.window="filterOpen = false"
                x-effect="document.body.style.overflow = (filterOpen && window.innerWidth < 1024) ? 'hidden' : ''">

                {{-- ============================================
                 SIDEBAR FILTER (Responsive Modal di HP)
                 ============================================ --}}
                <aside class="fixed inset-0 z-50 bg-black/50 lg:bg-transparent lg:static lg:z-auto lg:!block"
                    x-show="filterOpen" x-transition.opacity x-cloak @click.self="filterOpen = false">
                    <div
                        class="sheet-in absolute bottom-0 inset-x-0 bg-panel rounded-t-2xl max-h-[85vh] overflow-y-auto p-5 lg:sticky lg:top-24 lg:h-fit lg:rounded-xl lg:max-h-none lg:overflow-visible lg:border lg:border-line/40">

                        {{-- Header Filter (Hanya muncul di HP) --}}
                        <div class="flex items-center justify-between mb-4 lg:hidden">
                            <h3 class="font-bold text-lg text-ink">Filter Produk</h3>
                            <button @click="filterOpen = false" class="p-2 bg-cream rounded-full text-ink/70">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- ISI FORM FILTER --}}
                        <form method="GET" action="{{ route('koleksi.index') }}" class="space-y-5">

                            <input type="hidden" name="search" value="{{ request('search') }}">

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
                                                <span
                                                    class="text-[13px] text-ink/70 group-hover:text-gold transition-colors">{{ $k->nama_kategori }}</span>
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
                                
                                {{-- Tampilan Horizontal (Berjejer ke samping) --}}
                                <div class="flex flex-wrap gap-2.5 max-h-[70px] overflow-y-auto custom-scrollbar pr-1 pb-1 pt-1">
                                    
                                    @forelse ($warnaList as $w)
                                        @php
                                            // Menerjemahkan nama warna dari Admin menjadi kode HEX CSS
                                            $wLower = strtolower(trim($w));
                                            $hex = '#d1d5db'; // Warna default abu-abu jika tidak dikenali

                                            if (str_contains($wLower, 'hitam')) $hex = '#1a1a1a';
                                            elseif (str_contains($wLower, 'maroon') || str_contains($wLower, 'merah')) $hex = '#6b1c2a';
                                            elseif (str_contains($wLower, 'gold') || str_contains($wLower, 'emas') || str_contains($wLower, 'kuning')) $hex = '#c99a32';
                                            elseif (str_contains($wLower, 'cream') || str_contains($wLower, 'krem') || str_contains($wLower, 'putih')) $hex = '#f5e6c8';
                                            elseif (str_contains($wLower, 'hijau') || str_contains($wLower, 'green')) $hex = '#2a5c3f';
                                            elseif (str_contains($wLower, 'coklat') || str_contains($wLower, 'brown')) $hex = '#5c3a21';
                                            elseif (str_contains($wLower, 'biru') || str_contains($wLower, 'blue') || str_contains($wLower, 'navy')) $hex = '#1e3a8a';
                                            elseif (str_contains($wLower, 'pink') || str_contains($wLower, 'rose')) $hex = '#f472b6';
                                            elseif (str_contains($wLower, 'ungu') || str_contains($wLower, 'purple')) $hex = '#7e22ce';
                                            elseif (str_contains($wLower, 'abu') || str_contains($wLower, 'grey') || str_contains($wLower, 'silver')) $hex = '#9ca3af';
                                        @endphp

                                        <label class="cursor-pointer group relative">
                                            <input type="radio" name="warna" value="{{ $w }}" class="peer sr-only" {{ request('warna') === $w ? 'checked' : '' }}>
                                            
                                            {{-- Bulatan Warna --}}
                                            <span class="block w-7 h-7 rounded-full border border-black/10 shadow-sm transition-all duration-200
                                                         peer-checked:ring-2 peer-checked:ring-gold peer-checked:ring-offset-2 peer-checked:scale-110
                                                         group-hover:scale-110 group-hover:shadow-md"
                                                style="background-color: {{ $hex }};">
                                            </span>
                                            
                                            {{-- Tooltip Teks (Hanya muncul saat di-hover) --}}
                                            <span class="absolute -top-7 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] font-medium px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50">
                                                {{ $w }}
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-ink/40">Belum ada variasi warna.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Tombol Reset & Terapkan --}}
                            <div class="flex gap-2 pt-2">
                                <a href="{{ route('koleksi.index') }}"
                                    class="flex-1 text-center py-2.5 rounded-md bg-cream text-[11px] font-semibold text-ink/70 hover:bg-cream-dim transition-all">
                                    Reset
                                </a>
                                <button type="submit" @click="filterOpen = false"
                                    class="flex-1 text-center py-2.5 rounded-md bg-gold text-[11px] font-semibold text-white hover:bg-gold/90 transition-all">
                                    Terapkan
                                </button>
                            </div>
                        </form>
                    </div>
                </aside>

                {{-- ============================================
                 AREA PRODUK
                 ============================================ --}}
                <div class="min-w-0">

                    {{-- Toolbar --}}
                    <div class="pd-up relative z-30 flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6"
                        style="--d: .25s;">

                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm text-ink/55">
                                Menampilkan <b
                                    class="text-ink/85">{{ $produkList->firstItem() ?? 0 }}–{{ $produkList->lastItem() ?? 0 }}</b>
                                dari <b class="text-ink/85">{{ $produkList->total() }}</b> produk
                            </p>

                            {{-- Tombol Filter Khusus HP --}}
                            <button @click="filterOpen = true"
                                class="lg:hidden flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-line/50 bg-panel text-xs font-semibold text-ink/70">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filter
                            </button>
                        </div>

                        {{-- Dropdown Custom Sortir (satu blok saja) --}}
                        <div class="flex items-center gap-2" x-data="{
                            open: false,
                            selected: '{{ request('sort', 'terbaru') }}',
                            options: {
                                'terbaru': 'Terbaru',
                                'populer': 'Terpopuler',
                                'termurah': 'Harga Terendah',
                                'termahal': 'Harga Tertinggi'
                            },
                            updateSort(val) {
                                this.selected = val;
                                this.open = false;
                                document.getElementById('sort-input').value = val;
                                document.getElementById('sort-form').submit();
                            }
                        }">
                            {{-- Form tersembunyi: membawa filter yang sedang aktif --}}
                            <form id="sort-form" method="GET" action="{{ route('koleksi.index') }}">
                                @foreach (request()->except('sort', 'page') as $key => $val)
                                    @if (is_array($val))
                                        @foreach ($val as $v)
                                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                    @endif
                                @endforeach
                                <input type="hidden" name="sort" id="sort-input" value="{{ request('sort', 'terbaru') }}">
                            </form>

                            <span class="text-xs text-ink/50">Urutkan:</span>

                            <div class="relative" @click.outside="open = false" @keydown.escape="open = false">
                                <button type="button" @click="open = !open" :aria-expanded="open"
                                    class="flex items-center justify-between gap-2 min-w-[130px] px-3 py-1.5 rounded-md border border-line/50 bg-panel text-[13px] text-ink/70 hover:border-gold/60 transition-all focus:outline-none">
                                    <span x-text="options[selected]"></span>
                                    <svg class="w-4 h-4 text-ink/40 transition-transform duration-200"
                                        :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                                    class="absolute right-0 top-full mt-1 w-44 bg-panel border border-line/40 rounded-lg shadow-lg overflow-hidden z-50">
                                    <template x-for="(label, key) in options" :key="key">
                                        <button type="button" @click="updateSort(key)"
                                            class="w-full text-left px-4 py-2 text-[13px] transition-colors"
                                            :class="selected === key ? 'bg-gold/10 text-gold font-semibold' : 'text-ink/70 hover:bg-cream'">
                                            <span x-text="label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /toolbar --}}

                    {{-- Grid Produk --}}
                    @if ($produkList->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4 items-stretch">
                            @foreach ($produkList as $index => $p)
                                <x-product-card :produk="$p" :delay="min(0.1 + $index * 0.08, 0.8)" />
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="pd-reveal mt-10">
                            @if ($produkList->hasPages())
                                <nav class="flex items-center justify-center gap-1">
                                    {{-- Previous --}}
                                    @if ($produkList->onFirstPage())
                                        <span
                                            class="px-3 py-1.5 rounded-md border border-line/50 text-xs text-ink/30">«</span>
                                    @else
                                        <a href="{{ $produkList->previousPageUrl() }}"
                                            class="px-3 py-1.5 rounded-md border border-line/50 text-xs text-ink/70 hover:bg-cream hover:border-gold transition-all">«</a>
                                    @endif

                                    {{-- Page Numbers --}}
                                    @foreach ($produkList->getUrlRange(max(1, $produkList->currentPage() - 2), min($produkList->lastPage(), $produkList->currentPage() + 2)) as $page => $url)
                                        @if ($page == $produkList->currentPage())
                                            <span
                                                class="px-3 py-1.5 rounded-md bg-ink text-gold text-xs font-bold">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}"
                                                class="px-3 py-1.5 rounded-md border border-line/50 text-xs text-ink/70 hover:bg-cream hover:border-gold transition-all">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    {{-- Next --}}
                                    @if ($produkList->hasMorePages())
                                        <a href="{{ $produkList->nextPageUrl() }}"
                                            class="px-3 py-1.5 rounded-md border border-line/50 text-xs text-ink/70 hover:bg-cream hover:border-gold transition-all">»</a>
                                    @else
                                        <span
                                            class="px-3 py-1.5 rounded-md border border-line/50 text-xs text-ink/30">»</span>
                                    @endif
                                </nav>
                            @endif
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="pd-up text-center py-20 border-2 border-dashed border-line/50 rounded-2xl">
                            <div
                                class="pd-pop w-16 h-16 rounded-full bg-cream flex items-center justify-center text-gold mx-auto mb-4">
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

@endsection