@extends('layouts.public')

@section('title', $produk->nama . ' — ' . config('alnay.brand.nama'))

@section('content')

    <script src="https://cdn.lordicon.com/lordicon.js" defer></script>
    {{-- Aktifkan mode "animasi" lebih awal supaya elemen scroll-reveal tidak berkedip --}}
    <script>document.documentElement.classList.add('pd-js');</script>

    <style>
 
        [x-cloak] { display: none !important; }

        @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        @keyframes pd-swap { from { opacity: 0; transform: translateY(8px); }  to { opacity: 1; transform: none; } }
        @keyframes pd-pop  { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }
        @keyframes pd-grow { from { width: 0; } to { width: 3rem; } }
        @keyframes pd-shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
        @keyframes pd-glow { 0%, 100% { box-shadow: 0 0 0 0 rgba(184, 134, 43, .45); } 50% { box-shadow: 0 0 0 9px rgba(184, 134, 43, 0); } }
        @keyframes pd-wiggle { 0%, 100% { transform: rotate(0); } 25% { transform: rotate(-12deg); } 75% { transform: rotate(12deg); } }
        @keyframes pd-spin { to { transform: rotate(360deg); } }
        @keyframes pd-float { 0%, 100% { transform: translate3d(0, 0, 0); } 50% { transform: translate3d(0, -14px, 0); } }
        @keyframes pd-tick { 0% { transform: scale(1); } 40% { transform: scale(1.06); } 100% { transform: scale(1); } }

        .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
        .pd-swap  { animation: pd-swap .45s ease-out both; }
        .pd-pop   { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }
        .pd-line  { animation: pd-grow .8s .55s ease-out both; }
        .pd-shake { animation: pd-shake .45s ease; }
        .pd-ready { animation: pd-glow 2s ease-in-out infinite; }
        .pd-spin  { animation: pd-spin .7s linear infinite; }
        .pd-float { animation: pd-float 9s ease-in-out infinite; }
        .pd-tick  { animation: pd-tick .4s ease; }

        /* Muncul saat di-scroll (hanya aktif bila JS jalan) */
        .pd-reveal { transition: opacity .7s ease, transform .7s cubic-bezier(.22, .8, .3, 1); transition-delay: var(--d, 0s); }
        .pd-js .pd-reveal:not(.is-in) { opacity: 0; transform: translateY(20px); }

        /* Kilau yang menyapu tombol saat di-hover */
        .pd-shine { position: relative; overflow: hidden; }
        .pd-shine::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%);
            transform: translateX(-120%); transition: transform .8s ease; pointer-events: none;
        }
        .pd-shine:hover::after { transform: translateX(120%); }

        .group:hover .pd-wa-icon { animation: pd-wiggle .6s ease; }

        /* Zoom gambar mengikuti kursor (hanya perangkat dengan mouse) */
        .pd-zoom-inner { transform-origin: var(--ox, 50%) var(--oy, 50%); transition: transform .6s cubic-bezier(.22, .8, .3, 1), transform-origin .15s linear; }
        @media (hover: hover) and (pointer: fine) {
            .pd-zoom { cursor: zoom-in; }
            .pd-zoom:hover .pd-zoom-inner { transform: scale(1.5); }
        }

        /* Scroll horizontal tanpa scrollbar (produk serupa di mobile) */
        .pd-noscroll { scrollbar-width: none; -ms-overflow-style: none; }
        .pd-noscroll::-webkit-scrollbar { display: none; }

        @media (prefers-reduced-motion: reduce) {
            .pd-up, .pd-swap, .pd-pop, .pd-line, .pd-shake, .pd-ready, .pd-float, .pd-tick, .group:hover .pd-wa-icon { animation: none !important; }
            .pd-js .pd-reveal:not(.is-in) { opacity: 1; transform: none; }
            .pd-reveal, .pd-shine::after, .pd-zoom-inner { transition: none !important; }
            .pd-zoom:hover .pd-zoom-inner { transform: none !important; }
        }

        /* Sembunyikan tombol back to top di HP karena sudah ada bar bayar */
    </style>

    @php
        $statusLabel = \App\Support\ProdukStatus::label($produk->status, $produk->dapat_dijual);


        // Data stok dibaca lewat model, persis seperti halaman admin kelola produk:
        //  - produk dijual & disewa : set jual = kolom utama, set sewa = kolom *_sewa
        //  - produk satu jenis      : hanya kolom utama (warna, ukuran, stok)
        $paletWarna = \App\Models\Produk::paletWarna();

        // Cadangan untuk warna kustom yang tidak ada di palet admin (hex warna kustom tidak disimpan)
        $petaWarna = [
            'merah muda' => '#f4a6c0', 'rose gold' => '#d9a58f', 'burgundy' => '#7a1f33', 'marun' => '#6b1c2a', 'maroon' => '#6b1c2a',
            'navy' => '#1f2f55', 'tosca' => '#2bb3a3', 'teal' => '#2a8c8c', 'lavender' => '#b9a3e3', 'magenta' => '#c2185b',
            'champagne' => '#e6d3b3', 'mustard' => '#d1a02b', 'olive' => '#6b7a3a', 'mocca' => '#a47c5a', 'peach' => '#f6b99a',
            'salem' => '#f1a38a', 'fuchsia' => '#d81b8c', 'emas' => '#c99a32', 'gold' => '#c99a32', 'ungu' => '#6b3fa0',
            'purple' => '#6b3fa0', 'violet' => '#7c4dbd', 'pink' => '#ec6fa4', 'merah' => '#c0262d', 'red' => '#c0262d',
            'biru' => '#2f5fb3', 'blue' => '#2f5fb3', 'hijau' => '#2f8f5b', 'green' => '#2f8f5b', 'kuning' => '#e6c229',
            'yellow' => '#e6c229', 'oranye' => '#e8873a', 'orange' => '#e8873a', 'jingga' => '#e8873a', 'coklat' => '#7a5230',
            'cokelat' => '#7a5230', 'brown' => '#7a5230', 'hitam' => '#1a1a1a', 'black' => '#1a1a1a', 'putih' => '#f5f2ec',
            'white' => '#f5f2ec', 'abu' => '#9a9a9a', 'grey' => '#9a9a9a', 'gray' => '#9a9a9a', 'silver' => '#c0c0c0',
            'krem' => '#efe2c4', 'cream' => '#efe2c4',
        ];
        uksort($petaWarna, fn ($a, $b) => strlen($b) <=> strlen($a));

        $hexWarna = function (string $label) use ($paletWarna, $petaWarna) {
            if (isset($paletWarna[$label])) {
                return $paletWarna[$label];
            }
            if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $label)) {
                return $label;
            }
            $rendah = mb_strtolower($label);
            foreach ($petaWarna as $kata => $kode) {
                if (strpos($rendah, $kata) !== false) {
                    return $kode;
                }
            }
            return '#cfc8bb';
        };

        // Satu set stok: ukuran [{label, stok, warna:[{label, stok, hex}]}], warna set, total stok
        $bangunSet = function (string $jenis) use ($produk, $hexWarna) {
            $ukuran = [];
            foreach ($produk->ukuranUntuk($jenis) as $row) {
                // Data lama bisa berupa teks biasa; form admin menampilkannya dengan stok 0
                $label = trim((string) (is_array($row) ? ($row['ukuran'] ?? '') : $row));
                if ($label === '') {
                    continue;
                }
                $warna = [];
                foreach ((array) (is_array($row) ? ($row['warna'] ?? []) : []) as $w) {
                    $nama = trim((string) ($w['nama'] ?? ''));
                    if ($nama === '') {
                        continue;
                    }
                    $warna[] = ['label' => $nama, 'stok' => max(0, (int) ($w['stok'] ?? 0)), 'hex' => $hexWarna($nama)];
                }
                $ukuran[] = [
                    'label' => $label,
                    'stok'  => $warna ? array_sum(array_column($warna, 'stok')) : max(0, (int) (is_array($row) ? ($row['stok'] ?? 0) : 0)),
                    'warna' => $warna,
                ];
            }

            $warnaSet = [];
            foreach ($produk->warnaUntuk($jenis) as $nama) {
                $nama = trim((string) $nama);
                if ($nama !== '') {
                    $warnaSet[] = ['label' => $nama, 'hex' => $hexWarna($nama)];
                }
            }

            return [
                'ukuran' => $ukuran,
                'warna'  => $warnaSet,
                'stok'   => $ukuran ? array_sum(array_column($ukuran, 'stok')) : max(0, $produk->stokUntuk($jenis)),
            ];
        };

        $sets = [];
        if ($produk->bisa_disewa) {
            $sets['sewa'] = $bangunSet('sewa');
        }
        if ($produk->dapat_dijual) {
            $sets['jual'] = $bangunSet('jual');
        }
        $stokJual = $produk->dapat_dijual ? $sets['jual']['stok'] : 0;

        // Ringkasan untuk tabel spesifikasi (gabungan semua set)
        $semuaUkuran = collect($sets)->flatMap(fn ($s) => array_column($s['ukuran'], 'label'))->unique()->values()->all();
        $semuaWarna = collect($sets)->flatMap(fn ($s) => array_merge(
            array_column($s['warna'], 'label'),
            collect($s['ukuran'])->flatMap(fn ($u) => array_column($u['warna'], 'label'))->all()
        ))->unique()->values()->all();
        $beratTeks = (float) $produk->berat > 0 ? number_format((float) $produk->berat, 0, ',', '.') . ' gram' : '-';

        $diskonSewa = $produk->diskonSewa();
        $diskonJual = $produk->diskonJual();
        $fmtDiskon = fn ($d) => rtrim(rtrim(number_format($d, 2, '.', ''), '0'), '.');


        $paragrafDeskripsi = preg_split('/\R{2,}/', trim((string) $produk->deskripsi)) ?: [];
        $jumlahDisewa = $produk->getAttributes()['jumlah_disewa'] ?? null;

        $thumbGradien = [
            'linear-gradient(135deg,#3a2a12,#1b130a)',
            'linear-gradient(135deg,#32230f,#1a1209)',
            'linear-gradient(135deg,#7a1a2a,#4a0f1a)',
            'linear-gradient(135deg,#1b3a33,#0f2420)',
        ];

        // Semua foto produk (bisa string tunggal atau array). Jika kosong, pakai placeholder gradien.
        $fotoList = collect(\Illuminate\Support\Arr::flatten((array) $produk->foto_produk))
            ->filter(fn ($f) => is_string($f) && trim($f) !== '')
            ->values()
            ->all();
        $punyaFoto = count($fotoList) > 0;
        $jumlahFoto = $punyaFoto ? count($fotoList) : count($thumbGradien);

        $modeAwal = (request('mode') === 'beli' && $produk->dapat_dijual) || ! $produk->bisa_disewa ? 'beli' : 'sewa';

        $config = [
            'mode' => $modeAwal,
            // Set stok per jenis (sewa / jual), dibaca dari kolom yang sama dengan form admin
            'sets' => (object) $sets,
            // Harga sudah dipotong diskon, sama seperti di halaman admin
            'hargaSewa' => $produk->hargaSewaNet(),
            'hargaJual' => $produk->hargaJualNet(),
            'tarifTambahan' => $produk->tarifTambahanSewa(),
            // Per kombinasi ukuran/warna: { "M|Merah Marun": { kapasitas: 3, hitung: { "2026-11-10": 1 } }, "|": ... }
            'ketersediaan' => (object) ($ketersediaan ?? []),
            // true = bila SEMUA unit pilihan ini sedang disewa hari ini, kalender dikunci (tidak bisa pilih tanggal).
            // Ubah lewat config('alnay.kunci_kalender_bila_disewa_semua') bila ingin tetap boleh booking tanggal mendatang.
            'kunciBilaDisewaSemua' => (bool) config('alnay.kunci_kalender_bila_disewa_semua', true),
            'jumlahFoto' => $jumlahFoto,
            'fotoUrls' => collect($fotoList)->map(fn ($f) => asset('storage/' . $f))->values()->all(),
        ];

        // Ubah nilai apa pun (termasuk array bersarang) jadi teks aman untuk ditampilkan.
        $toTeks = function ($nilai) use (&$toTeks) {
            if (is_array($nilai)) {
                return implode(', ', array_map($toTeks, $nilai));
            }
            return $nilai === null || $nilai === '' ? '-' : $nilai;
        };
    @endphp

    <div class="relative overflow-x-clip bg-[#FBFAF7] min-h-screen py-6 md:py-10 pb-28 lg:pb-14 font-sans"
        x-data="produkDetail(@js($config))">

        {{-- Hiasan latar halus --}}
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-x-0 top-0 h-[520px] bg-[radial-gradient(60%_60%_at_85%_0%,rgba(184,134,43,.11),transparent_70%)]"></div>
        <div aria-hidden="true"
            class="pd-float pointer-events-none absolute -left-24 top-72 w-72 h-72 rounded-full bg-gold/10 blur-3xl hidden md:block"></div>

        <div class="relative max-w-[1180px] mx-auto px-4 md:px-6">

            {{-- Breadcrumb --}}
            <nav class="pd-up flex items-center gap-1.5 mb-5 md:mb-7 text-xs text-ink/50 overflow-x-auto whitespace-nowrap pd-noscroll"
                aria-label="Breadcrumb">
                <a href="{{ route('koleksi.index') }}" class="hover:text-gold transition-colors">Koleksi</a>
                <span aria-hidden="true">›</span>
                <a href="{{ route('koleksi.index', ['kategori' => [$produk->kategori->slug]]) }}"
                    class="hover:text-gold transition-colors">{{ $produk->kategori->nama_kategori }}</a>
                <span aria-hidden="true">›</span>
                <span class="text-ink/80 font-medium truncate">{{ $produk->nama }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-[400px_minmax(0,1fr)] gap-6 lg:gap-12">

                {{-- ==================== KOLOM KIRI: GALERI ==================== --}}
                <div class="lg:self-start w-full max-w-[340px] sm:max-w-[380px] lg:max-w-[400px] mx-auto lg:mx-0">

                    {{-- Gambar utama --}}
                    <div class="pd-up group relative aspect-[4/5] rounded-2xl overflow-hidden border border-line/40 shadow-sm lg:hover:shadow-2xl transition-shadow duration-500 touch-pan-y select-none"
                        style="background:#1b130a;"
                        @touchstart.passive="sentuhMulai($event)" @touchend.passive="sentuhSelesai($event)">

                        <div class="pd-zoom absolute inset-0" @click="buka()" @mousemove="zoomGerak($event)" @mouseleave="zoomReset($event)">
                            <div class="pd-zoom-inner absolute inset-0">
                                @if ($punyaFoto)
                                    @foreach ($fotoList as $i => $foto)
                                        <img src="{{ asset('storage/' . $foto) }}"
                                            alt="{{ $produk->nama }} — foto {{ $i + 1 }}"
                                            @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif
                                            draggable="false"
                                            class="absolute inset-0 w-full h-full object-cover transition-all duration-700 ease-out {{ $i === 0 ? '' : 'opacity-0' }}"
                                            :class="fotoAktif === {{ $i }} ? 'opacity-100 scale-100' : 'opacity-0 scale-105 pointer-events-none'">
                                    @endforeach
                                @else
                                    @foreach ($thumbGradien as $i => $gradien)
                                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-8 transition-all duration-700 ease-out {{ $i === 0 ? '' : 'opacity-0' }}"
                                            :class="fotoAktif === {{ $i }} ? 'opacity-100 scale-100' : 'opacity-0 scale-105'"
                                            style="background: {{ $gradien }};">
                                            <p class="font-display text-xl md:text-2xl text-[#e3b64f] leading-snug">{{ $produk->nama }}</p>
                                            <p class="mt-2 text-sm text-white/80">{{ $produk->kategori->nama_kategori }}</p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- Gradasi bawah supaya penanda terbaca --}}
                        <div aria-hidden="true"
                            class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/35 to-transparent"></div>

                        <span
                            class="absolute top-3 left-3 px-3 py-1.5 rounded-md bg-white/95 backdrop-blur text-ink/80 text-xs font-medium tracking-wide shadow-sm">
                            {{ $statusLabel }}
                        </span>

                        @if ($jumlahFoto > 1)
                            @if ($punyaFoto)
                                <button type="button" @click="buka()" aria-label="Lihat semua foto"
                                    class="absolute top-3 right-3 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-black/45 hover:bg-black/65 backdrop-blur text-white text-[11px] font-medium shadow-sm active:scale-95 transition-all duration-300">
                                    <svg viewBox="0 0 24 24" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                    {{ $jumlahFoto }} foto
                                </button>
                            @endif

                            {{-- Panah (muncul saat hover di desktop) --}}
                            <button type="button" @click="fotoSebelum()" aria-label="Foto sebelumnya"
                                class="hidden md:flex absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full bg-white/90 text-ink/70 shadow-md opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 hover:bg-white hover:text-gold active:scale-90 transition-all duration-300">
                                <x-icon name="chevron-right" :size="14" class="rotate-180" />
                            </button>
                            <button type="button" @click="fotoBerikut()" aria-label="Foto berikutnya"
                                class="hidden md:flex absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full bg-white/90 text-ink/70 shadow-md opacity-0 translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 hover:bg-white hover:text-gold active:scale-90 transition-all duration-300">
                                <x-icon name="chevron-right" :size="14" />
                            </button>

                            {{-- Titik indikator (mobile) --}}
                            <div class="md:hidden absolute bottom-3 inset-x-0 flex justify-center gap-1.5" aria-hidden="true">
                                @for ($i = 0; $i < $jumlahFoto; $i++)
                                    <span class="h-1.5 rounded-full bg-white transition-all duration-300 {{ $i === 0 ? 'w-5' : 'w-1.5 opacity-60' }}"
                                        :class="fotoAktif === {{ $i }} ? 'w-5 opacity-100' : 'w-1.5 opacity-60'"></span>
                                @endfor
                            </div>
                        @endif
                    </div>

                    {{-- Thumbnail --}}
                    @if ($jumlahFoto > 1)
                        <div class="flex gap-2.5 mt-3 pd-up overflow-x-auto pd-noscroll pb-1" style="--d: .1s;">
                            @if ($punyaFoto)
                                @foreach ($fotoList as $i => $foto)
                                    <button type="button" @click="fotoAktif = {{ $i }}" aria-label="Foto {{ $i + 1 }}"
                                        class="shrink-0 w-14 h-14 sm:w-[68px] sm:h-[68px] rounded-lg border-2 overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md active:scale-95"
                                        :class="fotoAktif === {{ $i }} ? 'border-gold shadow-md' : 'border-transparent opacity-70 hover:opacity-100'">
                                        <img src="{{ asset('storage/' . $foto) }}" alt="" loading="lazy"
                                            class="w-full h-full object-cover" draggable="false">
                                    </button>
                                @endforeach
                            @else
                                @foreach ($thumbGradien as $i => $gradien)
                                    <button type="button" @click="fotoAktif = {{ $i }}" aria-label="Foto {{ $i + 1 }}"
                                        class="shrink-0 w-14 h-14 sm:w-[68px] sm:h-[68px] rounded-lg border-2 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md active:scale-95"
                                        :class="fotoAktif === {{ $i }} ? 'border-gold shadow-md' : 'border-transparent opacity-70 hover:opacity-100'"
                                        style="background: {{ $gradien }};"></button>
                                @endforeach
                            @endif
                        </div>
                    @endif

                    {{-- Deskripsi produk (di bawah gambar) --}}
                    <div class="mt-5 rounded-2xl border border-line/40 bg-white/80 p-4 shadow-sm">
                        <h2 class="font-display text-base font-semibold text-ink/90 mb-1">Deskripsi Produk</h2>
                        <span class="block h-[2px] w-8 bg-gold/70 rounded-full mb-3"></span>

                        <div class="space-y-2.5 text-[13px] text-ink/65 leading-relaxed">
                                                        @foreach ($paragrafDeskripsi as $paragraf)
                                @if (trim($paragraf) !== '')
                                    <p>{{ preg_replace('/\s*\R\s*/', ' ', trim($paragraf)) }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Spesifikasi (di bawah deskripsi) --}}
                    <div class="mt-4 rounded-2xl border border-line/40 bg-white/80 p-4 shadow-sm">
                        <h3 class="font-display text-base font-semibold text-ink/90 mb-1">Spesifikasi</h3>
                        @foreach (array_filter([
                            ['Kode Produk', $produk->sku ?: '-'],
                            ['Kategori', $produk->kategori->nama_kategori ?? '-'],
                            ['Material', $toTeks($produk->bahan ?? '-')],
                            ['Motif', $toTeks($produk->motif ?? '-')],
                            ['Berat', $beratTeks],
                            ['Ukuran Tersedia', $semuaUkuran ? implode(', ', $semuaUkuran) : 'All Size'],
                            ['Warna', $semuaWarna ? implode(', ', $semuaWarna) : '-'],
                        ], fn ($baris) => $baris[1] !== '-') as $baris)
                            <div class="flex justify-between gap-4 py-2.5 text-[13px] border-b border-line/40 last:border-b-0">
                                <span class="text-ink/55 shrink-0">{{ $baris[0] }}</span>
                                <span class="text-ink/85 font-medium text-right">{{ $baris[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ==================== KOLOM KANAN: INFO & AKSI ==================== --}}
                <div class="min-w-0">

                    <div class="pd-up flex flex-wrap items-center gap-2" style="--d: .05s;">
                        <span class="inline-block px-2.5 py-1 rounded-md bg-ink/90 text-gold text-[11px] font-medium tracking-wider">
                            {{ $produk->bisa_disewa && $produk->dapat_dijual ? 'Sewa & Beli' : ($produk->bisa_disewa ? 'Khusus Sewa' : 'Khusus Jual') }}
                        </span>

                        <a href="{{ route('koleksi.index', ['kategori' => [$produk->kategori->slug]]) }}"
                            class="inline-block px-2.5 py-1 rounded-md bg-gold/10 text-gold border border-gold/30 hover:bg-gold hover:text-white transition-colors text-[11px] font-bold tracking-wider">
                            {{ $produk->kategori->nama_kategori }}
                        </a>
                    </div>

                    <h1 class="pd-up font-display text-2xl min-[375px]:text-[28px] sm:text-3xl lg:text-[34px] font-semibold text-ink/90 leading-tight mt-3" style="--d: .1s;">{{ $produk->nama }}</h1>
                    <span class="pd-line block h-[2px] bg-gold/70 rounded-full mt-3 mb-3"></span>

                    {{-- Rating --}}
                    <div class="pd-up flex flex-wrap items-center gap-x-3 gap-y-1 mb-5 text-xs text-ink/60" style="--d: .15s;">
                        <div class="flex items-center gap-2">
                            <div class="flex text-gold">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="pd-pop inline-flex" style="--d: {{ 0.3 + $i * 0.07 }}s;">
                                        <x-icon name="{{ $i <= round($produk->rating_rata_rata) ? 'star' : 'star-outline' }}"
                                            :size="13" />
                                    </span>
                                @endfor
                            </div>
                            <span class="font-semibold text-ink/85">{{ number_format($produk->rating_rata_rata, 1) }}</span>
                            <span>({{ $produk->jumlah_rating }} ulasan)</span>
                        </div>
                        @if ($jumlahDisewa)
                            <span class="hidden sm:block w-px h-3.5 bg-line"></span>
                            <span>{{ number_format($jumlahDisewa, 0, ',', '.') }}+ kali disewa</span>
                        @endif
                    </div>

                    {{-- Switch Sewa / Beli (indikator bergeser) --}}
                    @if ($produk->bisa_disewa && $produk->dapat_dijual)
                        <div class="pd-up relative grid grid-cols-2 border border-line/40 rounded-xl overflow-hidden bg-white/80 mb-5 p-1"
                            style="--d: .2s;">
                            <span aria-hidden="true"
                                class="absolute top-1 bottom-1 left-1 w-[calc(50%-4px)] rounded-lg bg-ink/90 shadow-md transition-transform duration-300 ease-[cubic-bezier(.22,.8,.3,1)] {{ $config['mode'] === 'beli' ? 'translate-x-full' : '' }}"
                                :class="{ 'translate-x-full': mode === 'beli' }"></span>
                            <button type="button" @click="mode = 'sewa'"
                                :class="mode === 'sewa' ? 'text-gold' : 'text-ink/55 hover:text-ink'"
                                class="relative z-10 h-[42px] text-xs font-semibold tracking-wider transition-colors duration-300">Sewa
                                Busana</button>
                            <button type="button" @click="mode = 'beli'"
                                :class="mode === 'beli' ? 'text-gold' : 'text-ink/55 hover:text-ink'"
                                class="relative z-10 h-[42px] text-xs font-semibold tracking-wider transition-colors duration-300">Beli
                                Baru</button>
                        </div>
                    @endif

                    {{-- ===== MODE SEWA: HARGA ===== --}}
                    <div x-show="mode === 'sewa'" x-cloak class="pd-swap">
                        <div class="bg-cream/70 rounded-2xl p-5 mb-6 border border-line/30 hover:border-gold/40 hover:shadow-md transition-all duration-300">
                            @if ($diskonSewa > 0)
                                <p class="mb-1.5 flex items-center gap-2 text-xs text-ink/50">
                                    <span class="line-through tabular-nums">Rp {{ number_format($produk->harga_sewa, 0, ',', '.') }}</span>
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 border border-rose-200">-{{ $fmtDiskon($diskonSewa) }}%</span>
                                </p>
                            @endif
                            <p class="font-display text-2xl sm:text-[28px] font-medium text-ink leading-none lining-nums tabular-nums">
                                <span x-text="formatRupiah(hargaTampil)"></span>
                                <span class="font-sans text-xs font-normal text-ink/50">/ <span
                                        x-text="durasi > 0 ? durasi + ' hari' : 'sewa'"></span></span>
                            </p>

                            {{-- Stok sewa (terpisah dari stok jual bila produk dijual & disewa) --}}
                            <p class="mt-2 text-xs text-ink/60 flex items-center gap-1.5">
                                <span class="inline-block w-1.5 h-1.5 rounded-full" :class="sewaTanpaTanggal ? (kapasitasSewa <= 0 ? 'bg-rose-500' : (sisaHariIniDipilih <= 0 ? 'bg-amber-500' : 'bg-emerald-500')) : (stok > 0 ? 'bg-emerald-500' : 'bg-rose-500')"></span>
                                <span x-text="sewaTanpaTanggal ? (kapasitasSewa <= 0 ? 'Stok sewa kosong' : (sisaHariIniDipilih <= 0 ? 'Semua unit sedang disewa hari ini' : 'Tersedia ' + sisaHariIniDipilih + ' dari ' + kapasitasSewa + ' unit hari ini')) : ((stok <= 0 ? 'Tidak ada unit tersedia' : (stok <= 3 ? 'Tersisa ' + stok + ' unit' : 'Tersedia ' + stok + ' unit')) + (mode === 'sewa' ? ' pada tanggal terpilih' : ''))"></span>
                            </p>
                            <div x-show="sewaTanpaTanggal && kapasitasSewa > 0 && sisaHariIniDipilih <= 0" x-cloak
                                 class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] leading-snug text-amber-800">
                                Semua unit pilihan ini sedang disewa hari ini.
                                <span x-show="tanggalKosongBerikut">Tersedia kembali mulai <b x-text="formatTanggal(tanggalKosongBerikut)"></b>.</span>
                                Pilih tanggal di kalender untuk menyewa di hari lain.
                            </div>
                            <div x-show="sewaTanpaTanggal && kapasitasSewa > 0 && sisaHariIniDipilih > 0 && sisaHariIniDipilih < kapasitasSewa" x-cloak
                                 class="mt-2 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-[12px] leading-snug text-sky-800">
                                <b x-text="kapasitasSewa - sisaHariIniDipilih"></b> unit sedang disewa hari ini, masih ada
                                <b x-text="sisaHariIniDipilih"></b> unit yang bisa disewa.
                            </div>
                            @if ($produk->dp > 0)
                                <p class="mt-1 text-xs text-ink/60">DP / uang muka: <strong class="text-ink/80 font-semibold">Rp {{ number_format($produk->dp, 0, ',', '.') }}</strong></p>
                            @endif

                            {{-- Alur 3 tahap --}}
                            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                @foreach ([['Ambil', 'Hari 1'], ['Pakai', 'Hari acara'], ['Kembali', 'Hari terakhir']] as $i => $tahap)
                                    <div class="relative rounded-lg bg-white/70 border border-line/30 px-2 py-2.5">
                                        <span class="mx-auto mb-1 flex w-5 h-5 items-center justify-center rounded-full bg-gold/15 text-gold text-[10px] font-bold">{{ $i + 1 }}</span>
                                        <p class="text-[12px] font-semibold text-ink/80">{{ $tahap[0] }}</p>
                                        <p class="text-[10px] text-ink/45 leading-tight">{{ $tahap[1] }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-3 text-[13px] text-ink/60 leading-relaxed">
                                <p class="font-semibold text-ink/75 mb-1">Jadwal Penyewaan:</p>
                                <p>Penyewaan memiliki 3 tahap utama: pengambilan, pemakaian, dan pengembalian. Contoh normal: diambil di hari pertama, dipakai untuk acara, dan dikembalikan di hari ketiga.
                            </div>

                            <div
                                class="mt-3 p-3 rounded-lg border border-dashed border-gold/50 text-xs text-ink/60 leading-relaxed bg-gold/[0.04]">
                                Ingin pemakaian lebih dari 1 hari? Tidak perlu bayar harga sewa penuh lagi — cukup tambahan
                                <strong
                                    class="text-gold font-semibold">Rp{{ number_format($produk->tarifTambahanSewa(), 0, ',', '.') }}
                                    per hari</strong>.
                                @if ($produk->denda_per_hari_telat > 0)
                                    Keterlambatan pengembalian dikenakan denda Rp{{ number_format($produk->denda_per_hari_telat, 0, ',', '.') }} per hari.
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ===== MODE BELI: HARGA ===== --}}
                    <div x-show="mode === 'beli'" x-cloak class="pd-swap">
                        <div class="bg-cream/70 rounded-2xl p-5 mb-6 border border-line/30 hover:border-gold/40 hover:shadow-md transition-all duration-300">
                            <p class="text-xs text-ink/50 mb-1">Harga Beli</p>
                            @if ($diskonJual > 0)
                                <p class="mb-1.5 flex items-center gap-2 text-xs text-ink/50">
                                    <span class="line-through tabular-nums">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</span>
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 border border-rose-200">-{{ $fmtDiskon($diskonJual) }}%</span>
                                </p>
                            @endif
                            <p class="font-display text-2xl sm:text-[28px] font-semibold text-ink/90 leading-none lining-nums tabular-nums">Rp {{ number_format($produk->hargaJualNet(), 0, ',', '.') }}</p>
                            <p class="mt-2 text-xs text-ink/60 flex items-center gap-1.5">
                                <span class="inline-block w-1.5 h-1.5 rounded-full" :class="stok > 0 ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                <span x-text="stok > 0 ? 'Tersedia untuk dibeli' : 'Stok habis'"></span>
                            </p>
                            <p class="mt-2 text-[11px] text-ink/50">Metode pengambilan &amp; ongkir dipilih saat checkout.</p>
                        </div>
                    </div>

                                        

                    {{-- Varian Warna & Motif --}}
<div x-show="adaWarna" x-cloak class="pd-up mb-6" style="--d: .33s;">
    <div class="flex items-baseline justify-between gap-3 mb-2.5">
        <p class="text-sm font-semibold text-ink/85">Varian Warna &amp; Motif</p>
        <p x-show="varianWarna" x-cloak class="min-w-0 truncate text-xs text-ink/55">
            Dipilih: <b class="font-semibold text-ink/85" x-text="varianWarna"></b>
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5" role="radiogroup" aria-label="Warna">
        <template x-for="w in warnaTampil" :key="jenis + '-' + w.label">
            <label class="relative block" :class="statusWarna(w) === 'habis' ? 'cursor-not-allowed' : 'cursor-pointer'">
                <input type="radio" name="pilih_warna" :value="w.label" class="peer sr-only"
                    :checked="varianWarna === w.label"
                    :disabled="statusWarna(w) === 'habis'"
                    @change="pilihWarna(w.label)">

                {{-- Kartu warna.
                     ok    = tersedia            -> tampil NORMAL (tidak pudar)
                     lain  = ada di ukuran lain  -> tetap NORMAL, hanya diberi petunjuk
                     habis = stok kosong         -> satu-satunya yang dipudarkan --}}
                <div class="flex items-center gap-3 rounded-xl border px-3 py-3 transition-all duration-200 peer-focus-visible:ring-2 peer-focus-visible:ring-gold/50"
                    :class="statusWarna(w) === 'habis'
                        ? 'border-dashed border-line/60 bg-cream/40'
                        : 'border-line/70 bg-white hover:border-gold/70 hover:-translate-y-0.5 hover:shadow-sm active:scale-[0.98] peer-checked:border-gold peer-checked:bg-[#fdf8ec] peer-checked:shadow-sm'">

                    <span class="relative w-9 h-9 shrink-0 overflow-hidden rounded-lg border border-black/10 shadow-[inset_0_0_0_2px_rgba(255,255,255,.35)]"
                        :class="statusWarna(w) === 'habis' ? 'grayscale opacity-45' : ''"
                        :style="'background: ' + w.hex">
                        <span x-show="statusWarna(w) === 'habis'" x-cloak aria-hidden="true"
                            class="absolute inset-0 flex items-center justify-center">
                            <span class="block h-px w-[140%] rotate-45 bg-ink/50"></span>
                        </span>
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[13px] font-medium"
                            :class="statusWarna(w) === 'habis' ? 'text-ink/40 line-through decoration-ink/30' : 'text-ink/90'"
                            x-text="w.label"></span>
                        <span x-show="keteranganWarna(w)" x-cloak class="block text-[10px] font-medium leading-snug"
                            :class="statusWarna(w) === 'habis' ? 'text-rose-500' : 'text-amber-600'"
                            x-text="keteranganWarna(w)"></span>
                    </span>
                </div>

                {{-- Centang pada warna terpilih --}}
                <span aria-hidden="true"
                    class="pointer-events-none absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-gold text-white shadow opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-200">
                    <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                </span>
            </label>
        </template>
    </div>

    <p x-show="warnaTampil.some((w) => statusWarna(w) === 'habis')" x-cloak class="mt-2 text-[11px] text-ink/50">
        Warna yang tampak pudar &amp; dicoret sedang tidak tersedia.
    </p>
</div>

                    {{-- Ukuran --}}
<div x-show="adaUkuran" x-cloak class="pd-up mb-6" style="--d: .3s;">
    <div class="flex items-center justify-between gap-3 mb-2.5">
        <p class="text-sm font-semibold text-ink/85">Ukuran
            <span x-show="ukuran" x-cloak class="ml-1 text-xs font-normal text-ink/55">· dipilih: <b class="font-semibold text-ink/85" x-text="ukuran"></b></span>
        </p>
        <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya butuh panduan ukur untuk ' . $produk->nama) }}"
            target="_blank" rel="noopener"
            class="text-xs font-semibold text-gold hover:underline">Panduan Ukur Mandiri</a>
    </div>
    <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Ukuran">
        <template x-for="o in ukuranTampil" :key="jenis + '-' + o.label">
            <button type="button" role="radio" :aria-checked="ukuran === o.label"
                @click="pilihUkuran(o.label)"
                :disabled="ukuranNonaktif(o)"
                :title="!o.ada ? 'Tidak tersedia untuk warna ' + varianWarna : (sisaUkuran(o) <= 0 ? 'Stok habis' : (mode === 'sewa' ? 'Tersedia ' + sisaUkuran(o) + ' unit' : 'Stok ' + sisaUkuran(o)))"
                :class="[
                    ukuran === o.label ? 'bg-ink/90 text-gold border-ink/90 shadow-md' : 'bg-white text-ink/75 border-line/60 hover:border-gold hover:text-ink',
                    !o.ada ? 'opacity-40 border-dashed line-through cursor-not-allowed' : (sisaUkuran(o) <= 0 ? 'opacity-40 line-through cursor-not-allowed bg-cream/40' : '')
                ]"
                class="w-16 h-11 flex items-center justify-center rounded-lg border text-[13px] font-semibold active:scale-95 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold/50"
                x-text="o.label"></button>
        </template>
        <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya ingin pesan ukuran custom untuk ' . $produk->nama) }}"
            target="_blank" rel="noopener"
            class="w-16 h-11 flex items-center justify-center rounded-lg border border-line/60 bg-white text-[12px] font-semibold text-ink/75 hover:border-gold hover:text-ink transition-all duration-200">Custom</a>
    </div>
    <p x-show="varianWarna && ukuranTampil.some((o) => !o.ada)" x-cloak class="mt-2 text-[11px] text-ink/50">
        Ukuran yang dicoret tidak tersedia untuk warna <b class="text-ink/70" x-text="varianWarna"></b>.
    </p>
</div>

                    {{-- ===== MODE SEWA: KALENDER ===== --}}
                    <div x-show="mode === 'sewa'" x-cloak class="pd-swap">
                        <div class="mb-6" id="kalender-sewa">
                            {{-- Jumlah unit sewa --}}
                            <div class="mb-5 rounded-2xl border border-line/40 bg-white/80 p-4 sm:p-5 shadow-sm">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <p class="text-sm font-semibold text-ink/85">Jumlah Unit</p>
                                    <p class="text-xs font-medium"
                                        :class="maksJumlah <= 0 ? 'text-rose-500' : (maksJumlah <= 3 ? 'text-amber-600' : 'text-emerald-600')"
                                        x-text="maksJumlah <= 0 ? 'Tidak ada unit tersedia' : (tanggalAmbil ? 'Maks. ' + maksJumlah + ' unit di tanggal terpilih' : 'Maks. ' + maksJumlah + ' unit')"></p>
                                </div>
                                <div class="flex items-center gap-4 flex-wrap">
                                    <div class="inline-flex items-center gap-1 rounded-xl border border-line/50 bg-white p-1">
                                        <button type="button" @click="kurangiJumlah()" :disabled="jumlah <= 1 || maksJumlah <= 0" aria-label="Kurangi jumlah"
                                            class="w-10 h-10 rounded-lg flex items-center justify-center text-ink/60 hover:bg-cream hover:text-gold active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-ink/60 transition-all duration-200">−</button>
                                        <span class="w-10 text-center text-sm font-semibold text-ink/85 tabular-nums" x-text="jumlah"></span>
                                        <button type="button" @click="tambahJumlah()" :disabled="jumlah >= maksJumlah" aria-label="Tambah jumlah"
                                            class="w-10 h-10 rounded-lg flex items-center justify-center text-ink/60 hover:bg-cream hover:text-gold active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-ink/60 transition-all duration-200">+</button>
                                    </div>
                                    <p class="text-xs text-ink/55" x-show="jumlah > 1" x-cloak>
                                        Harga sewa dihitung per unit × <b class="text-ink/85" x-text="jumlah"></b>
                                    </p>
                                </div>
                                <p x-show="maksJumlah > 0 && jumlah >= maksJumlah" x-cloak x-transition class="mt-2 text-[11px] text-amber-600">
                                    Jumlah sudah mencapai unit maksimal yang tersedia.
                                </p>
                            </div>
                            <p class="flex items-center gap-2 text-sm font-semibold text-ink/85 mb-3">
                                <span class="flex w-5 h-5 items-center justify-center rounded-full bg-ink/90 text-gold text-[11px] font-bold"><span x-text="noTanggal"></span></span>
                                Pilih Tanggal Sewa
                            </p>

                            <div class="rounded-2xl border border-line/40 bg-white/80 p-3 sm:p-4 shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-ink/85 capitalize" x-text="namaBulan"></span>
                                    <div class="flex gap-1.5">
                                        <button type="button" @click="bulanSebelumnya()" :disabled="!bisaMundur"
                                            aria-label="Bulan sebelumnya"
                                            class="w-9 h-9 sm:w-8 sm:h-8 rounded-lg border border-line/60 bg-white flex items-center justify-center text-ink/50 hover:border-gold hover:text-gold hover:-translate-x-0.5 active:scale-90 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:translate-x-0 disabled:hover:border-line/60 disabled:hover:text-ink/50 transition-all duration-200">
                                            <x-icon name="chevron-right" :size="11" class="rotate-180" />
                                        </button>
                                        <button type="button" @click="bulanBerikutnya()" aria-label="Bulan berikutnya"
                                            class="w-9 h-9 sm:w-8 sm:h-8 rounded-lg border border-line/60 bg-white flex items-center justify-center text-ink/50 hover:border-gold hover:text-gold hover:translate-x-0.5 active:scale-90 transition-all duration-200">
                                            <x-icon name="chevron-right" :size="11" />
                                        </button>
                                    </div>
                                </div>

                                <div x-show="terkunci" x-cloak class="mb-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-[12px] leading-snug text-rose-700">
                                    <b>Produk sedang disewa semua.</b> Stok sewa untuk pilihan ini habis, jadi tanggal belum bisa dipilih.
                                    <span x-show="tanggalKosongBerikut">Perkiraan tersedia kembali <b x-text="formatTanggal(tanggalKosongBerikut)"></b>.</span>
                                    Coba ukuran atau warna lain.
                                </div>

                                <div class="grid grid-cols-7 gap-y-1 text-center transition-all duration-150 ease-out"
                                    :class="gantiBulan ? (arah > 0 ? 'opacity-0 -translate-x-3' : 'opacity-0 translate-x-3') : 'opacity-100 translate-x-0'">
                                    <template x-for="(h, idx) in namaHari" :key="idx">
                                        <div class="text-[11px] text-ink/50 py-1.5 font-medium" x-text="h"></div>
                                    </template>

                                    <template x-for="c in sel" :key="c.t">
                                        <button type="button" @click="pilihTanggal(c.t)" :disabled="c.luar || lalu(c.t) || terkunci" :aria-disabled="penuh(c.t) ? 'true' : null"
                                            :class="kelas(c)"
                                            :title="judulTanggal(c)"
                                            class="h-11 mx-0.5 rounded-lg text-[13px] sm:text-xs font-medium transition-all duration-200 ease-out flex flex-col items-center justify-center leading-none">
                                            <span x-text="c.hari"></span>
                                            <span x-show="labelPeran(c)" x-cloak class="mt-0.5 text-[8px] font-semibold uppercase tracking-tight opacity-80" x-text="labelPeran(c)"></span>
                                        </button>
                                    </template>
                                </div>

                                {{-- Jadwal sewa: rentang ambil - dipakai - kembali --}}
                                <div class="mt-3 pt-3 border-t border-line/30 text-[11px] text-ink/65 space-y-1.5">
                                    <p class="text-[11px] font-semibold text-ink/70">Jadwal sewa</p>
                                    <p x-show="tanggalAmbil && tanggalKembali" x-cloak class="rounded-lg bg-gold/10 px-2.5 py-1.5 text-ink/80">
                                        <b>Pilihanmu:</b> <span x-text="rincianRentang(tanggalAmbil, tanggalKembali, false, null)"></span>
                                    </p>
                                    <template x-for="(r, i) in rentangKal" :key="'rg-' + i">
                                        <p class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-rose-700">
                                            <b x-text="formatTanggal(r.mulai) + ' – ' + formatTanggal(r.selesai)"></b>
                                            <span x-text="' · ' + r.jumlah + ' unit disewa'"></span><br>
                                            <span x-text="rincianRentang(r.mulai, r.selesai, r.terlambat, r.rencana)"></span>
                                        </p>
                                    </template>
                                    <p x-show="rentangKal.length === 0" x-cloak>Belum ada jadwal sewa lain untuk pilihan ini.</p>
                                </div>

                                {{-- Keterangan warna kalender --}}
                                <div class="mt-3 pt-3 border-t border-line/30">
                                    <p class="text-[11px] font-semibold text-ink/70 mb-2">Keterangan warna</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5 text-[11px] text-ink/60">
                                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-md bg-white ring-1 ring-line"></span> Tersedia, bisa dipilih</div>
                                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-md bg-white ring-1 ring-gold/70"></span> Hari ini</div>
                                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-md bg-ink/90"></span> Tanggal ambil / kembali yang kamu pilih</div>
                                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-md bg-gold/20"></span> Rentang sewa kamu</div>
                                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-md bg-sky-50 ring-1 ring-sky-300"></span> Sebagian unit disewa, masih bisa dipilih</div>
                                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-md bg-rose-200"></span> Merah: semua unit disewa, tidak bisa dipilih</div>
                                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-md bg-cream/60 text-ink/25 text-[9px] flex items-center justify-center">12</span> Abu-abu: sudah lewat</div>
                                        <div class="flex items-center gap-2 sm:col-span-2"><span class="text-[8px] font-semibold uppercase text-ink/60 w-4 text-center">aa</span> Tulisan kecil di tanggal: AMBIL (barang diambil), DIPAKAI, KEMBALI (barang dikembalikan)</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Ringkasan tanggal --}}
                            <div class="mt-3 grid grid-cols-3 gap-2 bg-cream/60 rounded-xl px-4 py-3 text-xs border border-line/30 transition-colors duration-300"
                                :class="{ 'pd-shake border-rose-300': getar, 'border-gold/40': tanggalAmbil && tanggalKembali && !getar }">
                                <div>
                                    <p class="text-[11px] text-ink/50">Ambil / Diantar</p>
                                    <p class="font-semibold text-ink/85 mt-0.5 text-[13px]"
                                        x-text="tanggalAmbil ? formatTanggal(tanggalAmbil) : 'Klik tanggal'"></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[11px] text-ink/50">Sewa</p>
                                    <p class="font-semibold text-ink/85 mt-0.5 text-[13px]"
                                        x-text="durasi > 0 ? durasi + ' hari' : '—'"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[11px] text-ink/50">Kembali</p>
                                    <p class="font-semibold text-ink/85 mt-0.5 text-[13px]"
                                        x-text="tanggalKembali ? formatTanggal(tanggalKembali) : '—'"></p>
                                </div>
                            </div>

                            {{-- Rincian biaya --}}
                            <div x-show="durasi > 0" x-cloak
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="mt-3 rounded-xl border border-line/30 bg-white/80 px-4 py-3 text-xs text-ink/65 space-y-1.5">
                                <div class="flex justify-between">
                                    <span>Harga sewa (hari pertama)</span>
                                    <span class="tabular-nums text-ink/85" x-text="formatRupiah(hargaSewaPerHari)"></span>
                                </div>
                                <div class="flex justify-between" x-show="durasi > 1" x-cloak>
                                    <span x-text="'Tambahan ' + (durasi - 1) + ' hari × ' + formatRupiah(tarifTambahan)"></span>
                                    <span class="tabular-nums text-ink/85" x-text="formatRupiah((durasi - 1) * tarifTambahan)"></span>
                                </div>
                                <div class="flex justify-between" x-show="jumlah > 1" x-cloak>
                                    <span>Subtotal per unit</span>
                                    <span class="tabular-nums text-ink/85" x-text="formatRupiah(totalSewaSatuan)"></span>
                                </div>
                                <div class="flex justify-between pt-1.5 border-t border-line/30 text-[13px] font-semibold text-ink/90">
                                    <span x-text="jumlah > 1 ? 'Total sewa (' + jumlah + ' unit)' : 'Total sewa'"></span>
                                    <span class="tabular-nums" x-text="formatRupiah(totalSewa)"></span>
                                </div>
                            </div>

                            <p x-show="pesanTanggal" x-cloak x-transition x-text="pesanTanggal"
                                class="mt-2 text-xs text-rose-600"></p>
                        </div>
                    </div>

                    {{-- ===== MODE BELI: WARNA, JUMLAH, PENGIRIMAN ===== --}}
                    <div x-show="mode === 'beli'" x-cloak class="pd-swap">

                        {{-- Pilihan 3: Jumlah & stok --}}
                        <div class="mb-5 rounded-2xl border border-line/40 bg-white/80 p-4 sm:p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <p class="flex items-center gap-2 text-sm font-semibold text-ink/85">
                                    <span class="flex w-5 h-5 items-center justify-center rounded-full bg-ink/90 text-gold text-[11px] font-bold"><span x-text="noJumlah"></span></span>
                                    Jumlah
                                </p>
                                <p class="text-xs font-medium"
                                    :class="stok <= 0 ? 'text-rose-500' : (stok <= 3 ? 'text-amber-600' : 'text-emerald-600')"
                                    x-text="stok <= 0 ? 'Stok habis' : (stok <= 3 ? 'Tinggal ' + stok + ' set!' : 'Stok: ' + stok + ' set')"></p>
                            </div>
                            <div class="flex items-center gap-4 flex-wrap">
                                <div class="inline-flex items-center gap-1 rounded-xl border border-line/50 bg-white p-1">
                                    <button type="button" @click="kurangiJumlah()" :disabled="jumlah <= 1 || stok <= 0" aria-label="Kurangi jumlah"
                                        class="w-10 h-10 rounded-lg flex items-center justify-center text-ink/60 hover:bg-cream hover:text-gold active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-ink/60 transition-all duration-200">−</button>
                                    <span class="w-10 text-center text-sm font-semibold text-ink/85 tabular-nums" x-text="jumlah"></span>
                                    <button type="button" @click="tambahJumlah()" :disabled="jumlah >= stok" aria-label="Tambah jumlah"
                                        class="w-10 h-10 rounded-lg flex items-center justify-center text-ink/60 hover:bg-cream hover:text-gold active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-ink/60 transition-all duration-200">+</button>
                                </div>
                                <p class="text-xs text-ink/55" x-show="stok > 0" x-cloak>
                                    Subtotal: <span class="font-semibold text-ink/85 tabular-nums" x-text="formatRupiah(totalBeli)"></span>
                                </p>
                            </div>
                            <p x-show="stok > 0 && jumlah >= stok" x-cloak x-transition class="mt-2 text-[11px] text-amber-600">
                                Jumlah sudah mencapai stok maksimal yang tersedia.
                            </p>
                        </div>
                    </div>

                    {{-- Ringkasan pilihan --}}
                    <div class="pd-up mb-3 flex flex-wrap items-center gap-x-2 gap-y-1.5 rounded-xl bg-ink/[0.04] border border-line/30 px-3.5 py-2.5 text-xs text-ink/70" style="--d: .35s;">
                        <span class="font-semibold text-ink/85">Pilihanmu:</span>
                        <span x-show="adaUkuran" x-cloak class="px-2 py-0.5 rounded-md bg-white border border-line/40">Ukuran <b class="text-ink/90" x-text="ukuran"></b></span>
                        <span x-show="varianWarna" x-cloak class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-white border border-line/40">
                            <span class="inline-block h-2.5 w-2.5 rounded-full ring-1 ring-black/15" :style="'background: ' + hexTerpilih"></span>
                            Warna <b class="text-ink/90" x-text="varianWarna"></b>
                        </span>
                        <span class="px-2 py-0.5 rounded-md bg-white border border-line/40">Jumlah <b class="text-ink/90" x-text="jumlah"></b></span>
                        <span x-show="mode === 'sewa'" x-cloak class="px-2 py-0.5 rounded-md border"
                            :class="durasi > 0 ? 'bg-white border-line/40' : 'bg-amber-50 border-amber-300 text-amber-700'">
                            <b class="font-semibold" x-text="durasi > 0 ? formatTanggal(tanggalAmbil) + ' – ' + formatTanggal(tanggalKembali) + ' (' + durasi + ' hari)' : 'Tanggal belum dipilih'"></b>
                        </span>
                    </div>

                    <p x-show="catatan" x-cloak x-transition
    class="mb-4 -mt-1 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] leading-snug text-amber-800">
    <svg viewBox="0 0 24 24" class="mt-px h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
    <span x-text="catatan"></span>
</p>

{{-- Tombol aksi --}}
                    <div x-ref="aksi" class="pd-up grid grid-cols-1 sm:grid-cols-2 gap-2.5 items-stretch" style="--d: .4s;">
                        {{-- Tombol Sewa --}}
                        @if ($produk->bisa_disewa)
                            <div x-show="mode === 'sewa'" x-cloak class="h-full">
                                <form method="POST" action="{{ route('sewa.simpan') }}" x-ref="formSewa">
                                    @csrf
                                    <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                    <input type="hidden" name="tanggal_ambil" :value="tanggalAmbil">
                                    <input type="hidden" name="tanggal_kembali" :value="tanggalKembali">
                                    <input type="hidden" name="ukuran" :value="ukuranKirim">
                                    <input type="hidden" name="warna" :value="varianWarna">
                                    <input type="hidden" name="qty" :value="jumlah">
                                    <input type="hidden" name="durasi" :value="durasi">
                                    <input type="hidden" name="total_sewa" :value="totalSewa">
                                    <button type="submit" @click.prevent="kirimSewa()" :disabled="memuat || stok <= 0"
                                        :class="{ 'pd-ready': tanggalAmbil && tanggalKembali && !memuat }"
                                        class="pd-shine w-full h-12 rounded-xl bg-gold text-white text-[13px] font-semibold tracking-wide hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait transition-all duration-300">
                                        <span x-show="!memuat" x-text="stok > 0 ? 'Sewa Sekarang' : 'Stok Habis'"></span>
                                        <span x-show="memuat" x-cloak class="inline-flex items-center gap-2">
                                            <span class="pd-spin inline-block w-4 h-4 rounded-full border-2 border-white/40 border-t-white"></span>
                                            Memproses…
                                        </span>
                                    </button>
                                </form>
                            </div>
                        @endif

                        {{-- Tombol Beli --}}
                        @if ($produk->dapat_dijual)
                            <div x-show="mode === 'beli'" x-cloak>
                                <form method="POST" action="{{ route('beli.simpan') }}" x-ref="formBeli" @submit="memuat = true">
                                    @csrf
                                    <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                    <input type="hidden" name="qty" :value="jumlah">
                                    <input type="hidden" name="varian" :value="varianWarna">
                                    <input type="hidden" name="ukuran" :value="ukuranKirim">
                                    @if ($stokJual > 0)
                                        <button type="submit" :disabled="memuat || stok <= 0" class="pd-shine w-full h-12 rounded-xl bg-gold text-white text-[13px] font-semibold tracking-wide hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait transition-all duration-300">
                                            <span x-show="!memuat">Beli Sekarang (<span x-text="formatRupiah(totalBeli)"></span>)</span>
                                            <span x-show="memuat" x-cloak class="inline-flex items-center gap-2">
                                                <span class="pd-spin inline-block w-4 h-4 rounded-full border-2 border-white/40 border-t-white"></span>
                                                Memproses…
                                            </span>
                                        </button>
                                    @else
                                        <button type="button" disabled class="w-full h-12 rounded-xl bg-gray-200 text-gray-400 text-[13px] font-semibold cursor-not-allowed">
                                            Stok Habis
                                        </button>
                                    @endif
                                </form>
                            </div>
                        @endif

                        {{-- Tombol Keranjang (Cerdas: Sewa / Beli) --}}
                        <form :action="mode === 'sewa' ? '{{ route('sewa.keranjang.tambah') }}' : '{{ route('keranjang.add') }}'" method="POST" class="w-full h-full">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <input type="hidden" name="mode" :value="mode">
                            <input type="hidden" name="ukuran" :value="ukuranKirim">
                            <input type="hidden" name="warna" :value="varianWarna">
                            <input type="hidden" name="qty" :value="jumlah">

                            {{-- Jika mode sewa, kirim data tanggal --}}
                            <template x-if="mode === 'sewa'">
                                <div>
                                    <input type="hidden" name="tanggal_ambil" :value="tanggalAmbil">
                                    <input type="hidden" name="tanggal_kembali" :value="tanggalKembali">
                                    <input type="hidden" name="durasi" :value="durasi">
                                    <input type="hidden" name="total_sewa" :value="totalSewa">
                                </div>
                            </template>

                            <button type="submit" @click.prevent="tambahKeKeranjang($event)" :disabled="memuat || stok <= 0"
                                    class="w-full h-12 rounded-xl border border-ink/80 bg-white text-ink/85 text-[13px] font-semibold tracking-wide hover:bg-ink/90 hover:text-cream hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait transition-all duration-300">
                                <span x-show="!memuat">+ Tambah ke Keranjang</span>
                                <span x-show="memuat" x-cloak>Menambahkan…</span>
                            </button>
                        </form>
                    </div>

                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya tertarik dengan produk ' . $produk->nama) }}"
                        target="_blank" rel="noopener"
                        class="pd-up group mt-4 flex items-center justify-center gap-2 w-full py-3.5 px-3 rounded-xl border border-dashed border-emerald-600/40 text-xs font-semibold text-emerald-800 text-center hover:bg-emerald-50 hover:border-emerald-600/70 hover:-translate-y-0.5 transition-all duration-300"
                        style="--d: .45s;">
                        <img src="{{ asset('images/wired-outline-2543-logo-whatsapp.svg') }}" alt="WhatsApp"
                            class="pd-wa-icon w-5 h-5 shrink-0">
                        <span>Konsultasi Produk Ini via WhatsApp</span>
                    </a>
                </div>
            </div>


            {{-- Produk Serupa --}}
            @if ($serupa->count() > 0)
                <div class="mt-14 md:mt-20">
                    <div class="pd-reveal">
                        <x-section-heading title="Produk Serupa"
                            subtitle="Pilihan lain dari kategori {{ $produk->kategori->nama_kategori }}" />
                    </div>
                    <div class="pd-reveal grid grid-flow-col auto-cols-[calc(50%-0.375rem)] sm:auto-cols-[40%] md:grid-flow-row md:grid-cols-3 lg:grid-cols-4 md:auto-cols-auto gap-3 md:gap-4 overflow-x-auto md:overflow-visible snap-x snap-mandatory pd-noscroll -mx-4 px-4 md:mx-0 md:px-0 pb-2 items-stretch">
                        @foreach ($serupa as $i => $s)
                            <div class="h-full snap-start">
                                <x-product-card :produk="$s" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ==================== PREVIEW FOTO (LIGHTBOX) ==================== --}}
        @if ($punyaFoto && $jumlahFoto > 1)
            <div x-show="lightbox" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @keydown.escape.window="tutup()"
                @keydown.arrow-right.window="lightbox && fotoBerikut()"
                @keydown.arrow-left.window="lightbox && fotoSebelum()"
                @click.self="tutup()"
                role="dialog" aria-modal="true" aria-label="Pratinjau foto produk"
                class="fixed inset-0 z-[70] flex flex-col bg-black/90 backdrop-blur-sm">

                {{-- Bar atas --}}
                <div class="flex items-center justify-between gap-3 px-4 py-3 text-white/90" @click.self="tutup()">
                    <p class="text-sm tabular-nums"><span x-text="fotoAktif + 1"></span> / <span x-text="jumlahFoto"></span></p>
                    <p class="hidden sm:block flex-1 text-center text-xs text-white/60 truncate">{{ $produk->nama }}</p>
                    <button type="button" @click="tutup()" aria-label="Tutup pratinjau"
                        class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 active:scale-90 flex items-center justify-center transition-all duration-200">✕</button>
                </div>

                {{-- Foto besar --}}
                <div class="relative flex-1 min-h-0 w-full max-w-5xl mx-auto"
                    @click.self="tutup()"
                    @touchstart.passive="sentuhMulai($event)" @touchend.passive="sentuhSelesai($event)">
                    <div class="absolute inset-y-0 inset-x-4 sm:inset-x-20 pointer-events-none">
                        <template x-for="(u, i) in fotoUrls" :key="i">
                            <img :src="u" alt="" draggable="false"
                                class="absolute inset-0 w-full h-full object-contain select-none transition-all duration-500 ease-out"
                                :class="fotoAktif === i ? 'opacity-100 scale-100' : 'opacity-0 scale-95'">
                        </template>
                    </div>

                    <button type="button" @click="fotoSebelum()" aria-label="Foto sebelumnya"
                        class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center active:scale-90 transition-all duration-200">
                        <x-icon name="chevron-right" :size="14" class="rotate-180" />
                    </button>
                    <button type="button" @click="fotoBerikut()" aria-label="Foto berikutnya"
                        class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center active:scale-90 transition-all duration-200">
                        <x-icon name="chevron-right" :size="14" />
                    </button>
                </div>

                {{-- Thumbnail --}}
                <div class="flex justify-center gap-2 px-4 py-4 overflow-x-auto pd-noscroll" @click.self="tutup()">
                    <template x-for="(u, i) in fotoUrls" :key="'t' + i">
                        <button type="button" @click="fotoAktif = i" :aria-label="'Foto ' + (i + 1)"
                            class="shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden border-2 transition-all duration-300 active:scale-95"
                            :class="fotoAktif === i ? 'border-gold scale-105' : 'border-transparent opacity-50 hover:opacity-100'">
                            <img :src="u" alt="" class="w-full h-full object-cover" draggable="false">
                        </button>
                    </template>
                </div>
            </div>
        @endif

        {{-- ==================== BAR AKSI MENEMPEL (MOBILE) ==================== --}}
        <div x-show="barTampil" x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-full opacity-0"
            class="lg:hidden fixed inset-x-0 bottom-0 z-[60] border-t border-line/40 bg-white/90 backdrop-blur-md px-4 pt-3 pb-[calc(.75rem+env(safe-area-inset-bottom))] shadow-[0_-10px_30px_rgba(0,0,0,.08)]">
            <div class="flex items-center gap-3 max-w-[1180px] mx-auto">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] text-ink/50 truncate"
                        x-text="mode === 'sewa' ? (durasi > 0 ? 'Total sewa · ' + durasi + ' hari' : 'Harga sewa') : 'Total beli'"></p>
                    <p class="font-display text-lg font-semibold text-ink leading-tight tabular-nums truncate"
                        x-text="formatRupiah(mode === 'sewa' ? hargaTampil : totalBeli)"></p>
                </div>
                <button type="button" @click="aksiUtama()" :disabled="memuat || stok <= 0"
                    class="pd-shine shrink-0 h-12 px-6 rounded-xl bg-gold text-white text-[13px] font-semibold tracking-wide active:scale-95 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed transition-all duration-200"
                    x-text="stok <= 0 ? 'Stok Habis' : (mode === 'sewa' ? 'Sewa Sekarang' : 'Beli Sekarang')"></button>
            </div>
        </div>
    </div>

    <script>
        function produkDetail(cfg) {
            const pad = (n) => String(n).padStart(2, '0');
            const iso = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
            const now = new Date();
            const reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

            return {
                mode: cfg.mode,
                sets: cfg.sets || {},
                hargaSewaPerHari: cfg.hargaSewa,
                tarifTambahan: cfg.tarifTambahan,
                hargaJual: cfg.hargaJual,
                ketersediaan: cfg.ketersediaan || {},
                kunciOpsi: cfg.kunciBilaDisewaSemua !== false,
                ukuran: '',
                tanggalAmbil: null,
                tanggalKembali: null,
                varianWarna: '',
                jumlah: 1,
                tahun: now.getFullYear(),
                bulan: now.getMonth(),
                hariIni: iso(now.getFullYear(), now.getMonth(), now.getDate()),
                namaHari: ['S', 'S', 'R', 'K', 'J', 'S', 'M'],

                // --- state animasi / UI ---
                fotoAktif: 0,
                jumlahFoto: cfg.jumlahFoto,
                fotoUrls: cfg.fotoUrls || [],
                lightbox: false,
                _geser: false,
                gantiBulan: false,
                arah: 1,
                pesanTanggal: '',
                catatan: '',
                getar: false,
                memuat: false,
                barTampil: false,
                hargaTampil: cfg.hargaSewa,
                _anim: null,
                _tx: 0,
                _ty: 0,

                // Alpine memanggil init() otomatis
                init() {
                    this.pilihDefault();
                    this.$watch('mode', () => { this.jumlah = 1; this.pilihDefault(); });
                    this.$watch('kunciKetersediaan', () => this.bersihkanTanggal());
                    this.$watch('terkunci', (v) => { if (v) { this.tanggalAmbil = null; this.tanggalKembali = null; } });
                    this.$watch('totalSewa', (v) => this.tweenHarga(v || this.hargaSewaPerHari));
                    this.$watch('tanggalKembali', (v) => { if (v) this.pesanTanggal = ''; });
                    this.$watch('lightbox', (v) => { document.body.style.overflow = v ? 'hidden' : ''; });
                    this.$watch('maksJumlah', (v) => { if (v > 0 && this.jumlah > v) this.jumlah = v; });
this.$watch('jumlah', () => {
    const adaTanggal = !!this.tanggalAmbil;
    this.bersihkanTanggal();
    if (adaTanggal && !this.tanggalAmbil) {
        this.pesanTanggal = 'Tanggal direset karena sisa unit tidak cukup untuk ' + this.jumlah + ' unit. Pilih tanggal lagi.';
    }
});

                    // Bar aksi mobile muncul hanya saat tombol aksi utama keluar dari layar
                    const aksi = this.$refs.aksi;
                    if (aksi && 'IntersectionObserver' in window) {
                        new IntersectionObserver(([en]) => { this.barTampil = !en.isIntersecting; }).observe(aksi);
                    }

                    // Reset status loading saat kembali via tombol back (bfcache)
                    window.addEventListener('pageshow', () => { this.memuat = false; });
                },

                // --- Galeri ---
                fotoBerikut() { this.fotoAktif = (this.fotoAktif + 1) % this.jumlahFoto; },
                fotoSebelum() { this.fotoAktif = (this.fotoAktif - 1 + this.jumlahFoto) % this.jumlahFoto; },
                buka() {
                    if (this._geser) { this._geser = false; return; }
                    if (this.fotoUrls.length > 1) this.lightbox = true;
                },
                tutup() { this.lightbox = false; },
                tambahJumlah() { if (this.jumlah < this.maksJumlah) this.jumlah++; },
                kurangiJumlah() { if (this.jumlah > 1) this.jumlah--; },
                sentuhMulai(e) {
                    this._tx = e.changedTouches[0].clientX;
                    this._ty = e.changedTouches[0].clientY;
                },
                sentuhSelesai(e) {
                    if (this.jumlahFoto < 2) return;
                    const dx = e.changedTouches[0].clientX - this._tx;
                    const dy = e.changedTouches[0].clientY - this._ty;
                    if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
                        this._geser = true;
                        setTimeout(() => { this._geser = false; }, 400);
                        dx < 0 ? this.fotoBerikut() : this.fotoSebelum();
                    }
                },
                zoomGerak(e) {
                    const r = e.currentTarget.getBoundingClientRect();
                    const x = ((e.clientX - r.left) / r.width) * 100;
                    const y = ((e.clientY - r.top) / r.height) * 100;
                    e.currentTarget.style.setProperty('--ox', x + '%');
                    e.currentTarget.style.setProperty('--oy', y + '%');
                },
                zoomReset(e) {
                    e.currentTarget.style.removeProperty('--ox');
                    e.currentTarget.style.removeProperty('--oy');
                },

                // Angka harga bergerak halus ke nilai baru
                tweenHarga(target) {
                    const dari = this.hargaTampil;
                    if (reduce || dari === target) {
                        this.hargaTampil = target;
                        return;
                    }
                    const mulai = performance.now();
                    const dur = 450;
                    cancelAnimationFrame(this._anim);
                    const langkah = (t) => {
                        const p = Math.min(1, (t - mulai) / dur);
                        const e = 1 - Math.pow(1 - p, 3);
                        this.hargaTampil = Math.round(dari + (target - dari) * e);
                        if (p < 1) this._anim = requestAnimationFrame(langkah);
                    };
                    this._anim = requestAnimationFrame(langkah);
                },

                // Tampilkan pesan validasi tanggal + getar + gulir ke kalender
                peringatiTanggal() {
                    this.pesanTanggal = 'Pilih tanggal ambil dan tanggal kembali dulu ya.';
                    this.getar = false;
                    this.$nextTick(() => {
                        this.getar = true;
                        setTimeout(() => { this.getar = false; }, 500);
                    });
                    const el = document.getElementById('kalender-sewa');
                    if (el) el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
                },

                // Kirim form sewa, dengan pesan inline (bukan alert)
                kirimSewa() {
                    if (this.stok <= 0) return;
                    if (!this.tanggalAmbil || !this.tanggalKembali) {
                        this.peringatiTanggal();
                        return;
                    }
                    if (this.rentangBentrok()) {
                        this.pesanTanggal = 'Ada tanggal yang sudah penuh dibooking di rentang ini. Pilih tanggal lain.';
                        this.tanggalAmbil = null;
                        this.tanggalKembali = null;
                        return;
                    }
                    if (this.jumlah > this.maksSewa) {
                        this.pesanTanggal = 'Jumlah melebihi unit yang tersedia di tanggal ini.';
                        return;
                    }
                    if (!this.$refs.formSewa) return;
                    this.pesanTanggal = '';
                    this.memuat = true;
                    this.$refs.formSewa.submit();
                },

                // Aksi tombol utama di bar mobile
                aksiUtama() {
                    if (this.mode === 'sewa') {
                        this.kirimSewa();
                        return;
                    }
                    if (this.stok > 0 && this.$refs.formBeli) {
                        this.memuat = true;
                        this.$refs.formBeli.submit();
                    }
                },

                tambahKeKeranjang(event) {
                    // Jika mode sewa tapi tanggal belum dipilih, cegah submit
                    if (this.mode === 'sewa' && (!this.tanggalAmbil || !this.tanggalKembali)) {
                        this.peringatiTanggal();
                        return;
                    }
                    if (this.mode === 'sewa' && this.rentangBentrok()) {
                        this.pesanTanggal = 'Ada tanggal yang sudah penuh dibooking di rentang ini. Pilih tanggal lain.';
                        this.tanggalAmbil = null;
                        this.tanggalKembali = null;
                        return;
                    }
                    if (this.mode === 'sewa' && this.jumlah > this.maksSewa) {
                        this.pesanTanggal = 'Jumlah melebihi unit yang tersedia di tanggal ini.';
                        return;
                    }
                    this.memuat = true;
                    event.currentTarget.form.submit();
                },

                // Ganti bulan dengan fade + geser singkat
                transisiBulan(fn, arah) {
                    if (reduce) { fn(); return; }
                    this.arah = arah;
                    this.gantiBulan = true;
                    setTimeout(() => { fn(); this.gantiBulan = false; }, 150);
                },

                // --- Set stok sesuai mode: sewa memakai stok sewa, beli memakai stok jual ---
                get jenis() { return this.mode === 'sewa' ? 'sewa' : 'jual'; },
                get set() { return this.sets[this.jenis] || { ukuran: [], warna: [], stok: 0 }; },
                // Kunci kombinasi untuk daftar tanggal penuh (sama dengan Support\KetersediaanSewa::kunci)
                get kunciKetersediaan() {
                    if (!this.adaUkuran) return '|';
                    const b = this.barisUkuran;
                    return b && b.warna.length ? this.ukuran + '|' + this.varianWarna : this.ukuran + '|';
                },
                // Kapasitas & unit terbooking per tanggal untuk ukuran/warna yang dipilih
                get dataKal() { return this.ketersediaan[this.kunciKetersediaan] || { kapasitas: 0, hitung: {} }; },
                // --- Info ketersediaan sewa HARI INI (saat belum ada tanggal dipilih) ---
                // Semua unit pilihan ini sedang keluar hari ini: produk dianggap habis, kalender tidak bisa dipakai
                get terkunci() { return this.kunciOpsi && this.mode === 'sewa' && this.kapasitasSewa > 0 && this.sisaHariIniDipilih <= 0; },
                get sewaTanpaTanggal() { return this.mode === 'sewa' && !this.tanggalAmbil; },
                get kapasitasSewa() { return this.dataKal.kapasitas || 0; },
                sisaHariIni(kunci) {
                    const d = this.ketersediaan[kunci];
                    if (!d) return 0;
                    return Math.max(0, d.kapasitas - ((d.hitung || {})[this.hariIni] || 0));
                },
                get sisaHariIniDipilih() { return this.sisaHariIni(this.kunciKetersediaan); },
                // Tanggal pertama (mulai hari ini) yang masih punya unit kosong untuk pilihan ini
                get tanggalKosongBerikut() {
                    const d = this.dataKal;
                    if (!d.kapasitas) return null;
                    const h = d.hitung || {};
                    const mulai = new Date(this.hariIni + 'T00:00:00Z');
                    for (let i = 0; i < 400; i++) {
                        const x = new Date(mulai.getTime() + i * 86400000);
                        const t = iso(x.getUTCFullYear(), x.getUTCMonth(), x.getUTCDate());
                        if ((h[t] || 0) < d.kapasitas) return t;
                    }
                    return null;
                },
                // Teks di bawah chip ukuran/warna pada mode sewa
                labelSewa(kunci) {
                    const d = this.ketersediaan[kunci];
                    if (!d || d.kapasitas <= 0) return 'habis';
                    if (this.tanggalAmbil) return 'tersedia ' + this.sisaSewa(kunci);
                    const n = this.sisaHariIni(kunci);
                    return n <= 0 ? 'disewa semua' : 'tersedia ' + n + ' hari ini';
                },
                // --- Rentang sewa: ambil -> dipakai -> kembali ---
                get rentangKal() { return this.dataKal.rentang || []; },
                tambahHari(t, n) {
                    const d = new Date(t + 'T00:00:00Z');
                    d.setUTCDate(d.getUTCDate() + n);
                    return iso(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate());
                },
                pendek(t) {
                    return new Date(t + 'T00:00:00Z').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', timeZone: 'UTC' });
                },
                // Contoh: "Diambil 1 Nov · dipakai 2 Nov · dikembalikan 3 Nov"
                rincianRentang(mulai, selesai, terlambat, rencana) {
                    if (terlambat) return 'Diambil ' + this.pendek(mulai) + ' · belum dikembalikan (jadwal kembali ' + this.pendek(rencana) + ')';
                    if (mulai === selesai) return 'Diambil & dikembalikan ' + this.pendek(mulai);
                    const a = this.tambahHari(mulai, 1), b = this.tambahHari(selesai, -1);
                    const pakai = a > b ? '' : (a === b ? ' · dipakai ' + this.pendek(a) : ' · dipakai ' + this.pendek(a) + '–' + this.pendek(b));
                    return 'Diambil ' + this.pendek(mulai) + pakai + ' · dikembalikan ' + this.pendek(selesai);
                },
                // Peran tanggal t di setiap booking yang sudah ada untuk pilihan ini
                perananBooking(t) {
                    const hasil = [];
                    for (const x of this.rentangKal) {
                        if (t < x.mulai || t > x.selesai) continue;
                        let peran;
                        if (x.terlambat && t === x.selesai) peran = 'belum kembali';
                        else if (x.mulai === x.selesai) peran = 'ambil & kembali';
                        else if (t === x.mulai) peran = 'ambil';
                        else if (t === x.selesai) peran = 'kembali';
                        else peran = 'dipakai';
                        hasil.push({ peran, x });
                    }
                    return hasil;
                },
                // Tulisan kecil di bawah angka tanggal
                labelPeran(c) {
                    if (c.luar || this.lalu(c.t)) return '';
                    if (this.tanggalAmbil === c.t) return 'ambil';
                    if (this.tanggalKembali === c.t) return 'kembali';
                    if (this.isInRange(c.t)) return 'dipakai';
                    const p = this.perananBooking(c.t);
                    if (p.length === 0) return '';
                    if (p.length > 1) return p.length + ' jadwal';
                    return { 'ambil': 'ambil', 'kembali': 'kembali', 'dipakai': 'dipakai', 'belum kembali': 'telat', 'ambil & kembali': '1 hari' }[p[0].peran];
                },
                judulTanggal(c) {
                    const t = c.t;
                    const baris = [];
                    if (this.penuh(t)) baris.push('Sedang disewa, tidak tersedia');
                    else if (this.sebagian(t)) baris.push(this.terpakai(t) + ' dari ' + this.dataKal.kapasitas + ' unit sudah dibooking');
                    for (const { peran, x } of this.perananBooking(t)) {
                        baris.push(x.jumlah + ' unit: ' + peran + ' (' + this.pendek(x.mulai) + ' – ' + this.pendek(x.selesai) + ')');
                    }
                    return baris.length ? baris.join('\n') : null;
                },
                terpakai(t) { return (this.dataKal.hitung || {})[t] || 0; },
                sebagian(t) { return this.terpakai(t) > 0 && !this.penuh(t); },
                // Tanggal yang sudah penuh (semua unit terbooking)
                get dibooking() {
                    const d = this.dataKal, h = d.hitung || {};
                    const perlu = Math.max(1, this.jumlah);
                    return Object.keys(h).filter((t) => h[t] > 0 && (d.kapasitas - h[t]) < perlu).sort();
                },
                rentangBentrok() {
                    if (!this.tanggalAmbil || !this.tanggalKembali) return false;
                    return this.dibooking.some((b) => b >= this.tanggalAmbil && b <= this.tanggalKembali);
                },
                bersihkanTanggal() {
                    if (!this.tanggalAmbil) return;
                    const penuh = this.dibooking;
                    const bentrok = penuh.includes(this.tanggalAmbil) ||
                        (this.tanggalKembali && (penuh.includes(this.tanggalKembali) ||
                            penuh.some((b) => b > this.tanggalAmbil && b < this.tanggalKembali)));
                    if (bentrok) { this.tanggalAmbil = null; this.tanggalKembali = null; }
                },
                get adaUkuran() { return this.set.ukuran.length > 0; },
                get barisUkuran() { return this.set.ukuran.find((u) => u.label === this.ukuran) || null; },
                
                get adaWarna() { return this.warnaTampil.length > 0; },

// Stok satu kombinasi ukuran + warna (sewa: dari ketersediaan tanggal, jual: dari data produk)
stokKombinasi(u, w) {
    if (this.mode === 'sewa') return this.sisaSewa(u + '|' + w);
    const row = this.set.ukuran.find((x) => x.label === u);
    const c = row ? row.warna.find((x) => x.label === w) : null;
    return c ? c.stok : 0;
},

// Stok satu ukuran (sewa: jumlah dari semua warnanya)
sisaUkuran(o) {
    if (this.mode !== 'sewa') return o.stok;
    if (!o.warna.length) return this.sisaSewa(o.label + '|');
    return o.warna.reduce((n, w) => n + this.stokKombinasi(o.label, w.label), 0);
},

// Apakah produk punya stok warna yang berbeda per ukuran
get warnaPerUkuran() {
    return this.set.ukuran.some((u) => u.warna.length > 0);
},

// Daftar warna gabungan dari semua ukuran.
// ada   = warna ini punya rincian di ukuran yang sedang dipilih
// total = stok warna ini di semua ukuran (null bila warna berlaku umum)
get warnaTampil() {
    if (!this.warnaPerUkuran) {
        return this.set.warna.map((w) => ({ label: w.label, hex: w.hex, ada: true, stok: null, total: null }));
    }
    const row = this.barisUkuran;
    const gabungan = new Map();
    this.set.ukuran.forEach((u) => u.warna.forEach((w) => {
        if (!gabungan.has(w.label)) gabungan.set(w.label, w.hex);
    }));
    return [...gabungan].map(([label, hex]) => {
        const ada = !!row && row.warna.some((w) => w.label === label);
        return {
            label,
            hex,
            ada,
            stok: ada ? this.stokKombinasi(this.ukuran, label) : null,
            total: this.set.ukuran.reduce((n, u) => n + this.stokKombinasi(u.label, label), 0),
        };
    });
},

// Daftar ukuran. ada = ukuran ini tersedia untuk warna yang sedang dipilih.
get ukuranTampil() {
    return this.set.ukuran.map((u) => ({
        ...u,
        ada: !this.warnaPerUkuran
            || !this.varianWarna
            || u.warna.length === 0
            || this.stokKombinasi(u.label, this.varianWarna) > 0,
    }));
},

ukuranNonaktif(o) {
    return !o.ada || this.sisaUkuran(o) <= 0;
},

// Status kartu warna:
//  'habis' = stok warna ini kosong di semua ukuran  -> satu-satunya yang dipudarkan
//  'lain'  = tidak ada / kosong di ukuran aktif, tapi ada di ukuran lain -> tampil normal
//  'ok'    = tersedia
statusWarna(w) {
    if (w.total !== null && w.total <= 0) return 'habis';
    if (w.total !== null && (!w.ada || this.habis(w.stok))) return 'lain';
    return 'ok';
},

// Daftar ukuran lain (selain yang aktif) yang masih punya stok untuk warna ini
ukuranLain(w) {
    return this.set.ukuran
        .filter((u) => u.label !== this.ukuran && this.stokKombinasi(u.label, w.label) > 0)
        .map((u) => u.label);
},

keteranganWarna(w) {
    const st = this.statusWarna(w);
    if (st === 'habis') return 'Stok habis';
    if (st === 'lain') {
        const l = this.ukuranLain(w);
        return (l.length ? 'Tersedia di ukuran ' + l.join(', ') : 'Tersedia di ukuran lain') + ' · klik untuk pindah';
    }
    if (w.stok !== null && w.stok > 0 && w.stok <= 3) return 'Sisa ' + w.stok + ' unit';
    return '';
},

get hexTerpilih() {
    const w = this.warnaTampil.find((x) => x.label === this.varianWarna);
    return w ? w.hex : 'transparent';
},

// Klik ukuran: hanya bisa dipilih bila tersedia untuk warna terpilih
pilihUkuran(label) {
    const o = this.ukuranTampil.find((x) => x.label === label);
    if (!o || this.ukuranNonaktif(o)) return;
    this.catatan = '';
    this.ukuran = label;
},

// Klik warna: bila warna tidak ada / habis di ukuran aktif, ukuran pindah ke yang punya stok
pilihWarna(label) {
    this.catatan = '';
    const row = this.barisUkuran;
    if (this.warnaPerUkuran) {
        const adaDiSini = !!row && row.warna.some((w) => w.label === label);
        const stokDiSini = adaDiSini ? this.stokKombinasi(row.label, label) : 0;
        if (!adaDiSini || stokDiSini <= 0) {
            const cari = (u) => u.warna.some((w) => w.label === label);
            const tujuan = this.set.ukuran.find((u) => cari(u) && this.stokKombinasi(u.label, label) > 0)
                || this.set.ukuran.find(cari);
            if (!tujuan) return;
            if (tujuan.label !== this.ukuran) {
                this.catatan = 'Ukuran disesuaikan ke ' + tujuan.label + ' karena warna ' + label
                    + ' ' + (adaDiSini ? 'habis' : 'tidak tersedia') + ' di ukuran ' + this.ukuran + '.';
                this.ukuran = tujuan.label;
            }
        }
    }
    this.varianWarna = label;
},

// Pilih warna default yang masih ada stoknya di ukuran aktif
pilihWarnaDefault() {
    const tersedia = this.warnaTampil.filter((w) => w.ada && !this.habis(w.stok));
    if (tersedia.some((w) => w.label === this.varianWarna)) return;
    const pertama = tersedia[0] || this.warnaTampil.find((w) => w.ada);
    this.varianWarna = pertama ? pertama.label : '';
},
                get ukuranKirim() { return this.adaUkuran ? this.ukuran : 'All Size'; },
                get noWarna() { return this.adaUkuran ? 2 : 1; },
                get noTanggal() { return (this.adaUkuran ? 1 : 0) + (this.adaWarna ? 1 : 0) + 1; },
                get noJumlah() { return this.noTanggal; },
                habis(stok) { return stok !== null && stok !== undefined && stok <= 0; },

                pilihDefault() {
                    const u = this.set.ukuran.find((x) => this.sisaUkuran(x) > 0) || this.set.ukuran[0];
                    this.ukuran = u ? u.label : '';
                    this.varianWarna = '';
                    this.pilihWarnaDefault();
                },
                

                // Stok yang berlaku: warna terpilih (bila ada rincian) > ukuran terpilih > total set
                // Sewa: unit TERSEDIA = kapasitas dikurangi unit terbooking terbanyak pada rentang terpilih
                // (atau hari ini bila tanggal belum dipilih). Stok jual: dari data produk.
                sisaSewa(kunci) {
                    const d = this.ketersediaan[kunci];
                    if (!d) return 0;
                    // Belum ada tanggal dipilih: tampilkan jumlah unit yang dimiliki (kapasitas), BUKAN sisa hari ini.
                    // Kalau hari ini penuh, ukuran/warna tetap bisa dipilih karena tanggal lain mungkin masih kosong.
                    if (!this.tanggalAmbil) return d.kapasitas;
                    // Tanggal sudah dipilih: stok tetap = kapasitas dikurangi unit yang belum dikembalikan,
                    // tidak ikut naik/turun mengikuti tanggal. Tanggal yang bentrok sudah ditangani kalender.
                    const keluar = (d.rentang || []).reduce((n, r) => n + (Number(r.jumlah) || 0), 0);
                    return Math.max(0, d.kapasitas - keluar);
                },
            
                // Unit maksimal yang bisa disewa pada rentang terpilih
                // (kapasitas dikurangi unit terbooking TERBANYAK di salah satu hari dalam rentang)
                get maksSewa() {
                    const d = this.dataKal;
                    const kap = d.kapasitas || 0;
                    if (!this.tanggalAmbil) return kap;
                    const h = d.hitung || {};
                    const akhir = this.tanggalKembali || this.tanggalAmbil;
                    let terbanyak = 0, t = this.tanggalAmbil;
                    while (t <= akhir) {
                        terbanyak = Math.max(terbanyak, h[t] || 0);
                        t = this.tambahHari(t, 1);
                    }
                    return Math.max(0, kap - terbanyak);
                },
                get maksJumlah() {
                    return this.mode === 'sewa' ? (this.terkunci ? 0 : this.maksSewa) : this.stok;
                },
                get stok() {
                    if (this.mode === 'sewa') return this.terkunci ? 0 : this.maksSewa;
                    const b = this.barisUkuran;
                    if (!b) return this.set.stok;
                    if (b.warna.length && this.varianWarna) {
                        const w = b.warna.find((x) => x.label === this.varianWarna);
                        return w ? w.stok : 0;
                    }
                    return b.stok;
                },
                get namaBulan() {
                    return new Date(Date.UTC(this.tahun, this.bulan, 1)).toLocaleDateString('id-ID', {
                        month: 'long',
                        year: 'numeric',
                        timeZone: 'UTC'
                    });
                },
                get bisaMundur() {
                    return this.tahun > now.getFullYear() || (this.tahun === now.getFullYear() && this.bulan > now
                        .getMonth());
                },
                get sel() {
                    const pertama = new Date(Date.UTC(this.tahun, this.bulan, 1));
                    const offset = (pertama.getUTCDay() + 6) % 7;
                    const cells = [];
                    for (let i = 0; i < 42; i++) {
                        const d = new Date(Date.UTC(this.tahun, this.bulan, 1 - offset + i));
                        cells.push({
                            t: iso(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()),
                            hari: d.getUTCDate(),
                            luar: d.getUTCMonth() !== this.bulan
                        });
                    }
                    return cells.slice(35).every(c => c.luar) ? cells.slice(0, 35) : cells;
                },
                bulanSebelumnya() {
                    if (!this.bisaMundur) return;
                    this.transisiBulan(() => {
                        if (this.bulan === 0) {
                            this.bulan = 11;
                            this.tahun--;
                        } else {
                            this.bulan--;
                        }
                    }, -1);
                },
                bulanBerikutnya() {
                    this.transisiBulan(() => {
                        if (this.bulan === 11) {
                            this.bulan = 0;
                            this.tahun++;
                        } else {
                            this.bulan++;
                        }
                    }, 1);
                },
                lalu(t) {
                    return t < this.hariIni;
                },
                // "Penuh" = sisa unit di tanggal itu kurang dari jumlah yang diminta
                penuh(t) {
                    const n = this.terpakai(t);
                    return n > 0 && (this.dataKal.kapasitas - n) < Math.max(1, this.jumlah);
                },
                nonaktif(t) {
                    return this.lalu(t) || this.penuh(t);
                },
                kelas(c) {
                    if (this.terkunci && !c.luar && !this.lalu(c.t)) return 'text-ink/25 bg-cream/40 cursor-not-allowed';
                    if (this.isSelected(c.t)) return 'bg-ink/90 text-gold font-semibold scale-105 shadow-md relative z-10';
                    if (this.isInRange(c.t)) return 'bg-gold/15 text-ink/85 font-medium';
                    if (c.luar || this.lalu(c.t)) return 'text-ink/25 cursor-default';
                    if (this.penuh(c.t)) return 'bg-rose-100 text-rose-500 cursor-not-allowed line-through decoration-rose-400';
                    if (this.sebagian(c.t)) return 'bg-sky-50 text-sky-700 ring-1 ring-sky-200 hover:bg-sky-100 hover:scale-110 active:scale-95';
                    const cincin = c.t === this.hariIni ? ' ring-1 ring-gold/60 text-ink' : '';
                    return 'text-ink/70 hover:bg-cream hover:text-ink hover:scale-110 active:scale-95' + cincin;
                },
                pilihTanggal(t) {
                    if (this.lalu(t) || this.terkunci) return;
                    if (this.penuh(t)) {
                        // Tanggal merah: tidak bisa dipilih, beri tahu alasannya
                        this.pesanTanggal = this.formatTanggal(t) + ' tidak punya cukup unit untuk ' + this.jumlah + ' unit. Kurangi jumlah atau pilih tanggal lain.';
                        return;
                    }
                    this.pesanTanggal = '';
                    if (!this.tanggalAmbil || this.tanggalKembali || t <= this.tanggalAmbil) {
                        this.tanggalAmbil = t;
                        this.tanggalKembali = null;
                        return;
                    }
                    if (this.dibooking.some(b => b > this.tanggalAmbil && b < t)) {
                        this.tanggalAmbil = t;
                        this.tanggalKembali = null;
                        return;
                    }
                    this.tanggalKembali = t;
                },
                isSelected(t) {
                    return this.tanggalAmbil === t || this.tanggalKembali === t;
                },
                isInRange(t) {
                    if (!this.tanggalAmbil || !this.tanggalKembali) return false;
                    return t > this.tanggalAmbil && t < this.tanggalKembali;
                },
                get durasi() {
                    if (!this.tanggalAmbil || !this.tanggalKembali) return 0;
                    const a = new Date(this.tanggalAmbil);
                    const b = new Date(this.tanggalKembali);
                    return Math.max(0, Math.ceil((b - a) / (1000 * 60 * 60 * 24)));
                },
                get totalSewaSatuan() {
                    if (this.durasi === 0) return 0;
                    return this.hargaSewaPerHari + (Math.max(0, this.durasi - 1) * this.tarifTambahan);
                },
                get totalSewa() { return this.totalSewaSatuan * this.jumlah; },
                get totalBeli() {
                    return this.hargaJual * this.jumlah;
                },
                formatRupiah(n) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
                },
                formatTanggal(t) {
                    if (!t) return '—';
                    return new Date(t).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric',
                        timeZone: 'UTC'
                    });
                },
            };
        }

        // Elemen .pd-reveal muncul halus saat masuk layar
        (function () {
            const jalankan = () => {
                const els = document.querySelectorAll('.pd-reveal');
                if (!('IntersectionObserver' in window)) {
                    els.forEach((el) => el.classList.add('is-in'));
                    return;
                }
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((en) => {
                        if (en.isIntersecting) {
                            en.target.classList.add('is-in');
                            io.unobserve(en.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
                els.forEach((el) => io.observe(el));
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', jalankan);
            } else {
                jalankan();
            }
        })();
    </script>

@endsection