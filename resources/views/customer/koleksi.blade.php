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
            animation: sheet-up .32s cubic-bezier(.22, .8, .3, 1);
        }

        @media (min-width: 1024px) {
            .sheet-in {
                animation: none;
            }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(201, 154, 50, 0.35);
            border-radius: 10px;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            scrollbar-width: none;
        }

        @keyframes bar-slide {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }
    </style>

    <script>
        function koleksiFilter(awal = {}) {
            return {
                filterOpen: false,
                loading: false,
                scrolled: false,
                sheetY: 0,
                dragY0: null,
                seq: 0,
                timer: null,
                urlTerakhir: '',
                cari: awal.cari || '',
                hargaMin: awal.hargaMin || '',
                hargaMax: awal.hargaMax || '',
                kat: awal.kat || [],
                total: awal.total || 0,
                aktif: awal.aktif || 0,
                preset: [
                    { l: '< 1 jt', min: '', max: '1000000' },
                    { l: '1 – 3 jt', min: '1000000', max: '3000000' },
                    { l: '3 – 5 jt', min: '3000000', max: '5000000' },
                    { l: '> 5 jt', min: '5000000', max: '' },
                ],

                init() {
                    this.urlTerakhir = location.href;
                    // Tombol back/forward browser: sinkronkan form lalu muat ulang konten tanpa reload
                    window.addEventListener('popstate', () => {
                        this.sinkronForm();
                        this.muat(location.href, { push: false });
                    });
                },

                // Geser lembar filter ke bawah (HP) untuk menutupnya
                mulaiTarik(e) {
                    if (window.innerWidth >= 1024) return;
                    this.dragY0 = e.touches[0].clientY;
                },
                tarik(e) {
                    if (this.dragY0 === null) return;
                    this.sheetY = Math.max(0, e.touches[0].clientY - this.dragY0);
                },
                lepasTarik() {
                    if (this.dragY0 === null) return;
                    if (this.sheetY > 90) this.filterOpen = false;
                    this.sheetY = 0;
                    this.dragY0 = null;
                },

                // Di HP hasil berada jauh di bawah hero: geser ke daftar produk
                keHasil() {
                    const el = document.getElementById('area-produk');
                    if (!el) return;
                    const top = el.getBoundingClientRect().top + window.scrollY - 96;
                    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
                },

                fmt(v) {
                    return v === '' ? '' : Number(v).toLocaleString('id-ID');
                },

                // Input harga: hanya angka, tampil dengan titik ribuan, diterapkan otomatis setelah berhenti mengetik
                setHarga(kolom, e) {
                    const v = e.target.value.replace(/\D/g, '').slice(0, 10);
                    this[kolom] = v;
                    e.target.value = this.fmt(v);
                    this.tunda();
                },

                tunda() {
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => this.kirim(), 500);
                },

                pilihPreset(p) {
                    if (this.hargaMin === p.min && this.hargaMax === p.max) {
                        this.hargaMin = '';
                        this.hargaMax = '';
                    } else {
                        this.hargaMin = p.min;
                        this.hargaMax = p.max;
                    }
                    this.kirim();
                },

                // Chip kategori di bagian atas: centang/lepas checkbox kategori di sidebar
                toggleKategori(slug) {
                    const cb = document.querySelector('#form-filter input[name="kategori[]"][value="' + slug + '"]');
                    if (!cb) return;
                    cb.checked = !cb.checked;
                    this.kirim({ hero: true });
                },

                semuaKategori() {
                    document.querySelectorAll('#form-filter input[name="kategori[]"]').forEach(c => c.checked = false);
                    this.kirim({ hero: true });
                },

                // Kumpulkan semua filter (form + search + harga + sort) -> URL -> muat via AJAX
                kirim(opt = {}) {
                    clearTimeout(this.timer);
                    const f = document.getElementById('form-filter');
                    const fd = new FormData(f);
                    this.kat = fd.getAll('kategori[]');

                    const p = new URLSearchParams();
                    fd.forEach((v, k) => {
                        if (v !== '' && k !== 'sort' && k !== 'search') p.append(k, v);
                    });
                    if (this.cari.trim() !== '') p.set('search', this.cari.trim());
                    if (this.hargaMin !== '') p.set('harga_min', this.hargaMin);
                    if (this.hargaMax !== '') p.set('harga_max', this.hargaMax);
                    const sort = new URLSearchParams(location.search).get('sort');
                    if (sort) p.set('sort', sort);

                    const qs = p.toString();
                    const url = f.action + (qs ? '?' + qs : '');
                    if (url === this.urlTerakhir) {
                        if (opt.hero && window.innerWidth < 1024) this.keHasil();
                        return;
                    }
                    this.muat(url, { naik: !!opt.hero && window.innerWidth < 1024 });
                },

                // Setiap input berubah -> langsung terapkan. 'Jenis' dibuat saling meniadakan.
                ubah(e, f) {
                    const t = e.target;
                    if (t.type === 'text') return; // input harga ditangani setHarga (debounce)
                    if (t.name === 'jenis' && t.checked) {
                        f.querySelectorAll('input[name=jenis]').forEach(c => { if (c !== t) c.checked = false; });
                    }
                    this.kirim();
                },

                // Radio (warna) bisa dibatalkan dengan klik ulang pada pilihan yang sama
                klik(e, f) {
                    const t = e.target;
                    if (t.type !== 'radio') return;
                    if (t.dataset.was === '1') {
                        t.checked = false;
                        t.dataset.was = '';
                        this.kirim();
                        return;
                    }
                    f.querySelectorAll('input[name=' + t.name + ']').forEach(r => r.dataset.was = '');
                    t.dataset.was = '1';
                },

                // Reset semua filter
                reset(url) {
                    const f = document.getElementById('form-filter');
                    f.querySelectorAll('input[type=checkbox], input[type=radio]').forEach(i => {
                        i.checked = false;
                        i.dataset.was = '';
                    });
                    this.cari = '';
                    this.hargaMin = '';
                    this.hargaMax = '';
                    this.kat = [];
                    this.muat(url);
                },

                // Klik pagination / chip filter / tombol reset di area produk -> AJAX juga
                halaman(e) {
                    const a = e.target.closest('nav a, a[data-ajax], a[data-chip]');
                    if (!a || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey) return;
                    e.preventDefault();
                    if (a.dataset.ajax !== undefined) return this.reset(a.href);
                    if (a.dataset.chip !== undefined) {
                        this.muat(a.href).then(() => this.sinkronForm());
                        return;
                    }
                    this.muat(a.href, { naik: true });
                },

                // Samakan form dengan URL (untuk tombol back/forward & hapus chip)
                sinkronForm() {
                    const p = new URLSearchParams(location.search);
                    document.getElementById('form-filter')
                        .querySelectorAll('input[type=checkbox], input[type=radio]').forEach(i => {
                            i.checked = p.getAll(i.name).includes(i.value);
                            if (i.type === 'radio') i.dataset.was = i.checked ? '1' : '';
                        });
                    this.cari = p.get('search') || '';
                    this.hargaMin = p.get('harga_min') || '';
                    this.hargaMax = p.get('harga_max') || '';
                    this.kat = p.getAll('kategori[]');
                },

                // Inti: ambil halaman, ganti hanya area produk, posisi scroll tidak berubah
                async muat(url, opt = {}) {
                    const { push = true, naik = false } = opt;
                    const id = ++this.seq;
                    this.urlTerakhir = url;
                    this.loading = true;
                    try {
                        const res = await fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                        });
                        if (!res.ok) throw new Error('gagal memuat');
                        const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                        if (id !== this.seq) return; // ada permintaan yang lebih baru
                        const baru = doc.getElementById('area-produk');
                        if (!baru) throw new Error('area produk tidak ditemukan');

                        document.getElementById('area-produk').innerHTML = baru.innerHTML;
                        this.total = parseInt(baru.dataset.total || '0', 10);
                        this.aktif = parseInt(baru.dataset.aktif || '0', 10);
                        if (push) history.pushState({}, '', url);

                        // Hanya saat pindah halaman (pagination) -> geser ke awal daftar produk
                        if (naik) this.keHasil();
                    } catch (err) {
                        // Fallback: kalau AJAX gagal, pakai navigasi biasa
                        if (id === this.seq) window.location.href = url;
                    } finally {
                        if (id === this.seq) this.loading = false;
                    }
                }
            };
        }
    </script>

    @php
        $katAktif = array_values(array_filter((array) request('kategori', []), 'is_string'));
        $hMin = preg_replace('/\D/', '', (string) request('harga_min', ''));
        $hMax = preg_replace('/\D/', '', (string) request('harga_max', ''));
        $jumlahAktif =
            count($katAktif) +
            (request('jenis') ? 1 : 0) +
            (request('warna') ? 1 : 0) +
            ($hMin !== '' || $hMax !== '' ? 1 : 0);

        $awalFilter = [
            'cari' => (string) request('search', ''),
            'hargaMin' => $hMin,
            'hargaMax' => $hMax,
            'kat' => $katAktif,
            'total' => $produkList->total(),
            'aktif' => $jumlahAktif,
        ];
    @endphp

    <div class="bg-[#FBFAF7] min-h-screen font-sans" x-data="koleksiFilter(@js($awalFilter))"
        @muat="muat($event.detail.url)" @keydown.escape.window="filterOpen = false"
        @resize.window.debounce.150ms="if (window.innerWidth >= 1024) filterOpen = false"
        @scroll.window.throttle.100ms="scrolled = window.scrollY > 520"
        x-effect="document.body.style.overflow = (filterOpen && window.innerWidth < 1024) ? 'hidden' : ''">

        {{-- Indikator loading tipis di atas layar --}}
        <div x-show="loading" x-cloak class="fixed top-0 inset-x-0 h-0.5 z-[60] overflow-hidden bg-gold/20">
            <div class="h-full w-1/2 bg-gold" style="animation: bar-slide 1s ease-in-out infinite;"></div>
        </div>

        {{-- ============================================
         HERO + PENCARIAN + KATEGORI CEPAT
         (di luar #area-produk supaya fokus ketikan tidak hilang saat hasil diganti)
         ============================================ --}}
        <section class="relative overflow-hidden bg-ink">
            <div class="absolute inset-0 opacity-[.08] pointer-events-none"
                style="background-image: repeating-linear-gradient(45deg, #c99a32 0 1px, transparent 1px 16px), repeating-linear-gradient(-45deg, #c99a32 0 1px, transparent 1px 16px);">
            </div>
            <div class="absolute -top-28 -right-24 w-96 h-96 rounded-full bg-gold/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -left-20 w-80 h-80 rounded-full bg-gold/10 blur-3xl pointer-events-none"></div>

            <div class="relative max-w-[1280px] mx-auto px-4 md:px-6 pt-9 pb-7 md:pt-14 md:pb-10">
                <p class="pd-up text-[11px] md:text-xs font-semibold tracking-[.22em] uppercase text-gold/90 mb-3"
                    style="--d: .02s;">
                    Sewa &amp; Beli
                </p>
                <h1 class="pd-up font-display text-3xl md:text-5xl text-cream leading-[1.1] tracking-tight mb-3"
                    style="--d: .08s;">
                    Koleksi Busana Adat
                </h1>
                <p class="pd-up text-sm md:text-base text-cream/65 max-w-xl leading-relaxed" style="--d: .14s;">
                    <span class="text-gold font-semibold" x-text="total">{{ $produkList->total() }}</span> busana adat
                    siap sewa maupun beli — cari berdasarkan nama, kategori, warna, atau harga.
                </p>

                {{-- Pencarian --}}
                <div class="pd-up relative mt-6 max-w-2xl" style="--d: .2s;">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-ink/40 pointer-events-none"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                    </svg>
                    <input type="search" x-model="cari" @input.debounce.400ms="kirim()" @keydown.enter.prevent="kirim({ hero: true }); $el.blur()"
                        autocomplete="off" enterkeyhint="search" aria-label="Cari produk"
                        placeholder="Cari kebaya, siger, motif…"
                        class="w-full h-12 md:h-14 pl-12 pr-12 rounded-full bg-white text-base md:text-[15px] text-ink placeholder:text-ink/40 shadow-lg shadow-black/20 border-0 ring-1 ring-white/10 focus:ring-2 focus:ring-gold outline-none transition-all [&::-webkit-search-cancel-button]:hidden">
                    <button type="button" x-show="cari" x-cloak @click="cari = ''; kirim()" aria-label="Hapus pencarian"
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-full text-ink/45 hover:text-ink hover:bg-cream transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Kategori cepat (scroll horizontal di HP) --}}
                @if ($kategoriList->count())
                    <div class="pd-up -mx-4 px-4 md:mx-0 md:px-0 mt-5 flex gap-2 overflow-x-auto no-scrollbar md:flex-wrap"
                        style="--d: .26s;">
                        <button type="button" @click="semuaKategori()"
                            class="shrink-0 px-4 py-2 rounded-full border text-[12px] md:text-[13px] font-medium transition-all active:scale-95"
                            :class="kat.length === 0 ? 'bg-gold border-gold text-ink' :
                                'border-white/20 text-cream/80 hover:border-gold/70 hover:text-gold'">
                            Semua
                        </button>
                        @foreach ($kategoriList as $k)
                            <button type="button" @click="toggleKategori('{{ $k->slug }}')"
                                class="shrink-0 px-4 py-2 rounded-full border text-[12px] md:text-[13px] font-medium transition-all active:scale-95"
                                :class="kat.includes('{{ $k->slug }}') ? 'bg-gold border-gold text-ink' :
                                    'border-white/20 text-cream/80 hover:border-gold/70 hover:text-gold'">
                                {{ $k->nama_kategori }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- ============================================
         KONTEN UTAMA — SIDEBAR + GRID
         ============================================ --}}
        <div class="max-w-[1280px] mx-auto px-4 md:px-6 py-6 md:py-10">
            <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6 lg:gap-8">

                {{-- ============================================
                 SIDEBAR FILTER (bottom-sheet di HP)
                 ============================================ --}}
                <aside
                    class="fixed inset-0 z-50 bg-black/55 backdrop-blur-[2px] lg:backdrop-blur-none lg:bg-transparent lg:static lg:z-auto lg:!block"
                    x-show="filterOpen" x-transition.opacity x-cloak @click.self="filterOpen = false">
                    <div :style="sheetY ? 'transform: translateY(' + sheetY + 'px); transition: none; animation: none;' : ''"
                        class="sheet-in absolute bottom-0 inset-x-0 flex flex-col max-h-[88dvh] bg-panel rounded-t-3xl shadow-2xl lg:sticky lg:top-24 lg:max-h-[calc(100vh-8rem)] lg:rounded-2xl lg:shadow-sm lg:border lg:border-line/40">

                        {{-- Header panel --}}
                        <div class="shrink-0 px-5 pt-3 pb-3 lg:pt-5 border-b border-line/30" style="touch-action: pan-y;"
                            @touchstart.passive="mulaiTarik($event)" @touchmove.passive="tarik($event)" @touchend="lepasTarik()">
                            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-ink/15 lg:hidden"></div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-display text-lg text-ink">Filter</h3>
                                    <span x-show="aktif > 0" x-cloak x-text="aktif"
                                        class="min-w-[20px] h-5 px-1.5 rounded-full bg-gold text-ink text-[11px] font-bold flex items-center justify-center"></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('koleksi.index') }}" @click.prevent="reset($el.href)"
                                        x-show="aktif > 0 || cari" x-cloak
                                        class="text-[12px] font-semibold text-gold hover:underline px-2 py-1">Reset</a>
                                    <button type="button" @click="filterOpen = false" aria-label="Tutup filter"
                                        class="lg:hidden p-2 bg-cream rounded-full text-ink/70">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ISI FORM FILTER --}}
                        <form id="form-filter" method="GET" action="{{ route('koleksi.index') }}"
                            class="flex-1 min-h-0 overflow-y-auto overscroll-contain custom-scrollbar px-5" @submit.prevent="kirim()"
                            @change="ubah($event, $el)" @click="klik($event, $el)">

                            <input type="hidden" name="search" value="{{ request('search') }}">
                            @if (request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif

                            {{-- Jenis Transaksi --}}
                            <details open class="group border-b border-line/30">
                                <summary
                                    class="flex items-center justify-between py-4 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">
                                    <span class="text-[12px] font-semibold tracking-wide uppercase text-ink/75">Jenis
                                        Transaksi</span>
                                    <svg class="w-4 h-4 text-ink/40 transition-transform group-open:rotate-180"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <div class="pb-4 grid grid-cols-2 gap-2">
                                    @foreach ([['sewa', 'Bisa Disewa'], ['beli', 'Bisa Beli/Disewa']] as [$v, $l])
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="jenis" value="{{ $v }}"
                                                class="peer sr-only" {{ request('jenis') === $v ? 'checked' : '' }}>
                                            <span
                                                class="flex items-center justify-center text-center min-h-[44px] px-2 rounded-xl border border-line/50 text-[12px] leading-tight text-ink/70 transition-all hover:border-gold/60 peer-checked:bg-ink peer-checked:text-gold peer-checked:border-ink peer-checked:font-semibold peer-focus-visible:ring-2 peer-focus-visible:ring-gold/50">{{ $l }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </details>

                            {{-- Kategori --}}
                            <details open class="group border-b border-line/30">
                                <summary
                                    class="flex items-center justify-between py-4 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">
                                    <span
                                        class="text-[12px] font-semibold tracking-wide uppercase text-ink/75">Kategori</span>
                                    <svg class="w-4 h-4 text-ink/40 transition-transform group-open:rotate-180"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <div class="pb-4 space-y-0.5">
                                    @foreach ($kategoriList as $k)
                                        <label
                                            class="flex items-center justify-between gap-2 cursor-pointer group/row -mx-2 px-2 py-2.5 rounded-lg hover:bg-cream/70 transition-colors">
                                            <span class="flex items-center gap-2.5">
                                                <input type="checkbox" name="kategori[]" value="{{ $k->slug }}"
                                                    {{ in_array($k->slug, (array) request('kategori', [])) ? 'checked' : '' }}
                                                    class="w-5 h-5 rounded border-line/60 text-gold focus:ring-gold focus:ring-offset-0 transition-all">
                                                <span
                                                    class="text-[13px] text-ink/75 group-hover/row:text-ink transition-colors">{{ $k->nama_kategori }}</span>
                                            </span>
                                            <span
                                                class="text-[11px] text-ink/45 bg-cream rounded-full px-2 py-0.5">{{ $k->produks_count ?? '' }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </details>

                            {{-- Rentang Harga (bebas diisi pelanggan) --}}
                            <details open class="group border-b border-line/30">
                                <summary
                                    class="flex items-center justify-between py-4 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">
                                    <span class="text-[12px] font-semibold tracking-wide uppercase text-ink/75">Rentang
                                        Harga</span>
                                    <svg class="w-4 h-4 text-ink/40 transition-transform group-open:rotate-180"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <div class="pb-4">
                                    <div class="flex items-center gap-2">
                                        <div class="relative flex-1 min-w-0">
                                            <span
                                                class="absolute left-3 top-1/2 -translate-y-1/2 text-[11px] text-ink/40 pointer-events-none">Rp</span>
                                            <input type="text" inputmode="numeric" autocomplete="off" placeholder="Min"
                                                aria-label="Harga minimum" :value="fmt(hargaMin)"
                                                @input="setHarga('hargaMin', $event)" @keydown.enter.prevent="kirim()"
                                                class="w-full h-12 sm:h-11 pl-9 pr-2 rounded-xl border border-line/50 bg-white text-base sm:text-[13px] text-ink/85 placeholder:text-ink/30 focus:border-gold focus:ring-2 focus:ring-gold/25 outline-none transition-all">
                                        </div>
                                        <span class="text-ink/35 text-xs">–</span>
                                        <div class="relative flex-1 min-w-0">
                                            <span
                                                class="absolute left-3 top-1/2 -translate-y-1/2 text-[11px] text-ink/40 pointer-events-none">Rp</span>
                                            <input type="text" inputmode="numeric" autocomplete="off" placeholder="Max"
                                                aria-label="Harga maksimum" :value="fmt(hargaMax)"
                                                @input="setHarga('hargaMax', $event)" @keydown.enter.prevent="kirim()"
                                                class="w-full h-12 sm:h-11 pl-9 pr-2 rounded-xl border border-line/50 bg-white text-base sm:text-[13px] text-ink/85 placeholder:text-ink/30 focus:border-gold focus:ring-2 focus:ring-gold/25 outline-none transition-all">
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-1.5 mt-3">
                                        <template x-for="p in preset" :key="p.l">
                                            <button type="button" @click="pilihPreset(p)"
                                                class="px-3.5 min-h-[40px] rounded-full border text-[12px] transition-all active:scale-95"
                                                :class="(hargaMin === p.min && hargaMax === p.max) ?
                                                'border-gold bg-gold/10 text-ink font-semibold' :
                                                'border-line/50 text-ink/65 hover:border-gold/60'"
                                                x-text="p.l"></button>
                                        </template>
                                    </div>
                                </div>
                            </details>

                            {{-- Warna --}}
                            <details open class="group">
                                <summary
                                    class="flex items-center justify-between py-4 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">
                                    <span
                                        class="text-[12px] font-semibold tracking-wide uppercase text-ink/75">Warna</span>
                                    <svg class="w-4 h-4 text-ink/40 transition-transform group-open:rotate-180"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <div class="pb-5 flex flex-wrap gap-2 max-h-56 overflow-y-auto custom-scrollbar">
                                    @forelse ($warnaList as $w)
                                        @php
                                            // Pakai palet resmi dari model; jika tidak ada, tebak dari kata kunci
                                            $hex = \App\Models\Produk::paletWarna()[$w] ?? null;

                                            if (!$hex) {
                                                $wLower = strtolower(trim($w));
                                                $hex = '#d1d5db';

                                                if (str_contains($wLower, 'hitam')) $hex = '#1a1a1a';
                                                elseif (str_contains($wLower, 'maroon') || str_contains($wLower, 'marun') || str_contains($wLower, 'merah')) $hex = '#6b1c2a';
                                                elseif (str_contains($wLower, 'gold') || str_contains($wLower, 'emas') || str_contains($wLower, 'kuning')) $hex = '#c99a32';
                                                elseif (str_contains($wLower, 'cream') || str_contains($wLower, 'krem') || str_contains($wLower, 'putih')) $hex = '#f5e6c8';
                                                elseif (str_contains($wLower, 'hijau') || str_contains($wLower, 'green')) $hex = '#2a5c3f';
                                                elseif (str_contains($wLower, 'coklat') || str_contains($wLower, 'cokelat') || str_contains($wLower, 'brown')) $hex = '#5c3a21';
                                                elseif (str_contains($wLower, 'biru') || str_contains($wLower, 'blue') || str_contains($wLower, 'navy')) $hex = '#1e3a8a';
                                                elseif (str_contains($wLower, 'pink') || str_contains($wLower, 'rose')) $hex = '#f472b6';
                                                elseif (str_contains($wLower, 'ungu') || str_contains($wLower, 'purple')) $hex = '#7e22ce';
                                                elseif (str_contains($wLower, 'abu') || str_contains($wLower, 'grey') || str_contains($wLower, 'silver')) $hex = '#9ca3af';
                                            }
                                        @endphp

                                        <label class="cursor-pointer">
                                            <input type="radio" name="warna" value="{{ $w }}"
                                                data-was="{{ request('warna') === $w ? '1' : '' }}"
                                                class="peer sr-only" {{ request('warna') === $w ? 'checked' : '' }}>
                                            <span
                                                class="inline-flex items-center gap-2 min-h-[36px] pl-1.5 pr-3 rounded-full border border-line/50 text-[12px] text-ink/70 transition-all hover:border-gold/60 peer-checked:border-gold peer-checked:bg-gold/10 peer-checked:text-ink peer-checked:font-semibold peer-focus-visible:ring-2 peer-focus-visible:ring-gold/50">
                                                <span class="w-6 h-6 rounded-full border border-black/10 shadow-inner"
                                                    style="background-color: {{ $hex }};"></span>
                                                {{ $w }}
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-ink/40">Belum ada variasi warna.</p>
                                    @endforelse
                                </div>
                            </details>
                        </form>

                        {{-- Footer sheet (hanya HP): terapkan & tutup --}}
                        <div class="lg:hidden shrink-0 flex items-center gap-3 px-4 pt-3 border-t border-line/30 bg-panel"
                            style="padding-bottom: max(.75rem, env(safe-area-inset-bottom));">
                            <a href="{{ route('koleksi.index') }}" @click.prevent="reset($el.href)"
                                class="px-5 h-12 flex items-center rounded-full bg-cream text-[13px] font-semibold text-ink/70 active:scale-95 transition">
                                Reset
                            </a>
                            <button type="button" @click="filterOpen = false"
                                class="flex-1 h-12 rounded-full bg-ink text-gold text-[14px] font-semibold active:scale-[.98] transition">
                                Lihat <span x-text="total"></span> produk
                            </button>
                        </div>
                    </div>
                </aside>

                {{-- ============================================
                 AREA PRODUK (diganti lewat AJAX)
                 ============================================ --}}
                <div id="area-produk" class="min-w-0 transition-opacity duration-200"
                    :class="loading ? 'opacity-50 pointer-events-none' : ''" @click="halaman($event)"
                    data-total="{{ $produkList->total() }}" data-aktif="{{ $jumlahAktif }}">

                    @php
                        // ---------- Chip filter aktif (bisa dihapus satu per satu) ----------
                        $bangunUrl = function (array $q) {
                            $pairs = [];
                            foreach ($q as $key => $val) {
                                if (is_array($val)) {
                                    foreach ($val as $x) {
                                        $pairs[] = rawurlencode($key) . '%5B%5D=' . rawurlencode($x);
                                    }
                                } elseif ($val !== null && $val !== '') {
                                    $pairs[] = rawurlencode($key) . '=' . rawurlencode($val);
                                }
                            }
                            return route('koleksi.index') . ($pairs ? '?' . implode('&', $pairs) : '');
                        };
                        $tanpa = fn(array $kunci) => $bangunUrl(request()->except(array_merge($kunci, ['page'])));
                        $rp = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

                        $chips = [];

                        if (filled(request('search'))) {
                            $chips[] = ['label' => '“' . request('search') . '”', 'url' => $tanpa(['search'])];
                        }
                        if (request('jenis')) {
                            $chips[] = [
                                'label' => request('jenis') === 'sewa' ? 'Bisa Disewa' : 'Bisa Beli/Disewa',
                                'url' => $tanpa(['jenis']),
                            ];
                        }
                        foreach ($katAktif as $slug) {
                            $sisa = array_values(array_diff($katAktif, [$slug]));
                            $q = request()->except(['kategori', 'page']);
                            if ($sisa) {
                                $q['kategori'] = $sisa;
                            }
                            $chips[] = [
                                'label' => optional($kategoriList->firstWhere('slug', $slug))->nama_kategori ?? $slug,
                                'url' => $bangunUrl($q),
                            ];
                        }
                        if (request('warna')) {
                            $chips[] = ['label' => 'Warna: ' . request('warna'), 'url' => $tanpa(['warna'])];
                        }
                        if ($hMin !== '' || $hMax !== '') {
                            $labelHarga =
                                $hMin !== '' && $hMax !== ''
                                    ? $rp($hMin) . ' – ' . $rp($hMax)
                                    : ($hMin !== ''
                                        ? '≥ ' . $rp($hMin)
                                        : '≤ ' . $rp($hMax));
                            $chips[] = ['label' => $labelHarga, 'url' => $tanpa(['harga_min', 'harga_max'])];
                        }
                    @endphp

                    {{-- Toolbar --}}
                    <div class="relative z-30 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                        <p class="text-[13px] text-ink/55 min-w-0">
                            <span class="hidden sm:inline">Menampilkan </span><b
                                class="text-ink/85">{{ $produkList->firstItem() ?? 0 }}–{{ $produkList->lastItem() ?? 0 }}</b>
                            dari <b class="text-ink/85">{{ $produkList->total() }}</b> produk
                        </p>

                        <div class="flex items-center gap-2 w-full sm:w-auto sm:shrink-0">
                            {{-- Tombol Filter khusus HP --}}
                            <button type="button" @click="filterOpen = true"
                                class="lg:hidden inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 h-11 px-4 rounded-full border border-line/60 bg-panel text-[13px] font-semibold text-ink/80 active:scale-95 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filter
                                @if ($jumlahAktif > 0)
                                    <span
                                        class="min-w-[18px] h-[18px] px-1 rounded-full bg-gold text-ink text-[10px] font-bold flex items-center justify-center">{{ $jumlahAktif }}</span>
                                @endif
                            </button>

                            {{-- Dropdown Sortir --}}
                            <div class="flex flex-1 sm:flex-none min-w-0 items-center gap-2" x-data="{
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
                                    const p = new URLSearchParams(location.search);
                                    p.set('sort', val);
                                    p.delete('page');
                                    this.$dispatch('muat', { url: '{{ route('koleksi.index') }}?' + p.toString() });
                                }
                            }">
                                {{-- Form cadangan (tanpa JS): membawa filter yang sedang aktif --}}
                                <form id="sort-form" method="GET" action="{{ route('koleksi.index') }}">
                                    @foreach (request()->except('sort', 'page') as $key => $val)
                                        @if (is_array($val))
                                            @foreach ($val as $v)
                                                <input type="hidden" name="{{ $key }}[]"
                                                    value="{{ $v }}">
                                            @endforeach
                                        @else
                                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                        @endif
                                    @endforeach
                                    <input type="hidden" name="sort" id="sort-input"
                                        value="{{ request('sort', 'terbaru') }}">
                                </form>

                                <span class="hidden md:inline text-xs text-ink/50">Urutkan:</span>

                                <div class="relative flex-1 sm:flex-none" @click.outside="open = false" @keydown.escape="open = false">
                                    <button type="button" @click="open = !open" :aria-expanded="open"
                                        class="flex w-full items-center justify-between gap-2 h-11 sm:h-10 sm:min-w-[128px] px-4 rounded-full border border-line/60 bg-panel text-[13px] text-ink/80 hover:border-gold/60 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-gold/50">
                                        <span x-text="options[selected]"></span>
                                        <svg class="w-4 h-4 text-ink/40 transition-transform duration-200"
                                            :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                                        class="absolute right-0 top-full mt-2 w-48 bg-panel border border-line/40 rounded-xl shadow-xl overflow-hidden z-50 py-1">
                                        <template x-for="(label, key) in options" :key="key">
                                            <button type="button" @click="updateSort(key)"
                                                class="w-full text-left px-4 py-2.5 text-[13px] transition-colors"
                                                :class="selected === key ? 'bg-gold/10 text-gold font-semibold' :
                                                    'text-ink/70 hover:bg-cream'">
                                                <span x-text="label"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>{{-- /toolbar --}}

                    {{-- Chip filter aktif --}}
                    @if (count($chips))
                        <div class="flex items-center gap-2 mb-5 -mx-4 px-4 overflow-x-auto no-scrollbar sm:mx-0 sm:px-0 sm:flex-wrap sm:overflow-visible">
                            @foreach ($chips as $c)
                                <a href="{{ $c['url'] }}" data-chip
                                    class="group inline-flex shrink-0 items-center gap-1.5 pl-3.5 pr-2 py-2 rounded-full bg-gold/10 border border-gold/30 text-[12px] font-medium text-ink/80 hover:bg-gold/20 active:scale-95 transition">
                                    <span class="max-w-[220px] truncate">{{ $c['label'] }}</span>
                                    <svg class="w-3.5 h-3.5 text-ink/45 group-hover:text-ink" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endforeach
                            <a href="{{ route('koleksi.index') }}" data-ajax
                                class="shrink-0 whitespace-nowrap text-[12px] font-semibold text-gold hover:underline px-1.5 py-1">Hapus semua</a>
                        </div>
                    @endif

                    {{-- Grid Produk --}}
                    @if ($produkList->count() > 0)
                        <div
                            class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-5 items-stretch">
                            @foreach ($produkList as $index => $p)
                                <x-product-card :produk="$p" :delay="min(0.1 + $index * 0.08, 0.8)" />
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if ($produkList->hasPages())
                            <nav class="mt-10 md:mt-12 flex items-center justify-center gap-1.5"
                                aria-label="Navigasi halaman">
                                {{-- Previous --}}
                                @if ($produkList->onFirstPage())
                                    <span
                                        class="w-11 h-11 flex items-center justify-center rounded-full border border-line/40 text-ink/25">‹</span>
                                @else
                                    <a href="{{ $produkList->previousPageUrl() }}" aria-label="Halaman sebelumnya"
                                        class="w-11 h-11 flex items-center justify-center rounded-full border border-line/60 text-ink/70 hover:bg-cream hover:border-gold active:scale-95 transition-all">‹</a>
                                @endif

                                {{-- Nomor halaman (tablet/desktop) --}}
                                <div class="hidden sm:flex items-center gap-1.5">
                                    @foreach ($produkList->getUrlRange(max(1, $produkList->currentPage() - 2), min($produkList->lastPage(), $produkList->currentPage() + 2)) as $page => $url)
                                        @if ($page == $produkList->currentPage())
                                            <span
                                                class="w-11 h-11 flex items-center justify-center rounded-full bg-ink text-gold text-[13px] font-bold">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}"
                                                class="w-11 h-11 flex items-center justify-center rounded-full border border-line/60 text-[13px] text-ink/70 hover:bg-cream hover:border-gold transition-all">{{ $page }}</a>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Ringkas (HP) --}}
                                <span class="sm:hidden px-4 text-[13px] text-ink/60">
                                    Hal. <b class="text-ink">{{ $produkList->currentPage() }}</b> / {{ $produkList->lastPage() }}
                                </span>

                                {{-- Next --}}
                                @if ($produkList->hasMorePages())
                                    <a href="{{ $produkList->nextPageUrl() }}" aria-label="Halaman berikutnya"
                                        class="w-11 h-11 flex items-center justify-center rounded-full border border-line/60 text-ink/70 hover:bg-cream hover:border-gold active:scale-95 transition-all">›</a>
                                @else
                                    <span
                                        class="w-11 h-11 flex items-center justify-center rounded-full border border-line/40 text-ink/25">›</span>
                                @endif
                            </nav>
                        @endif
                    @else
                        {{-- Empty State --}}
                        <div class="pd-up text-center py-16 md:py-24 px-6 border-2 border-dashed border-line/50 rounded-3xl bg-panel/50">
                            <div
                                class="pd-pop w-16 h-16 rounded-full bg-cream flex items-center justify-center text-gold mx-auto mb-4">
                                <x-icon name="search" :size="28" />
                            </div>
                            <h3 class="font-display text-xl md:text-2xl text-ink/80 mb-2">Produk tidak ditemukan</h3>
                            <p class="text-sm text-ink/50 max-w-md mx-auto mb-6 leading-relaxed">
                                Coba kurangi filter, perlebar rentang harga, atau gunakan kata kunci yang lebih umum.
                            </p>
                            <a href="{{ route('koleksi.index') }}" data-ajax
                                class="pd-shine inline-flex items-center gap-2 px-7 py-3 rounded-full bg-ink text-gold text-sm font-semibold hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 min-h-[44px]">
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </div>{{-- /area-produk --}}
            </div>
        </div>

        {{-- Tombol Filter mengambang (HP) — muncul setelah menggulir --}}
        <button type="button" x-show="scrolled && !filterOpen" x-cloak x-transition.opacity @click="filterOpen = true"
            class="lg:hidden fixed left-1/2 -translate-x-1/2 z-40 inline-flex items-center gap-2 h-12 pl-5 pr-5 rounded-full bg-ink text-gold text-[14px] font-semibold shadow-xl shadow-black/25 active:scale-95 transition-transform"
            style="bottom: calc(1.25rem + env(safe-area-inset-bottom));">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filter
            <span x-show="aktif > 0" x-text="aktif"
                class="min-w-[20px] h-5 px-1.5 rounded-full bg-gold text-ink text-[11px] font-bold flex items-center justify-center"></span>
        </button>
    </div>

@endsection