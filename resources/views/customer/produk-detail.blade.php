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
    </style>

    @php
        $statusLabel = \App\Support\ProdukStatus::label($produk->status, $produk->dapat_dijual);

        $kunciUkuran = ['ukuran', 'size'];
        $kunciWarna  = ['warna', 'color', 'colour', 'varian'];
        $kunciNama   = ['nama', 'name', 'label', 'value', 'pilihan', 'opsi', 'nama_warna'];
        $kunciStok   = ['stok', 'stock', 'qty', 'quantity', 'jumlah'];
        $kunciHex    = ['hex', 'kode', 'kode_warna', 'kode_hex', 'warna_hex', 'hex_code', 'color_hex'];
        $daftarUkuran = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '2XL', '3XL', '4XL', '5XL', 'ALL SIZE', 'ALLSIZE', 'ALL', 'FREE SIZE', 'FREESIZE', 'ONE SIZE', 'CUSTOM', 'STANDAR', 'STANDARD', 'JUMBO'];

        // Data ukuran bisa berisi ukuran, warna, dan stok sekaligus (daftar datar, pasangan, atau record).
        // Semuanya dikumpulkan dulu sebagai entri [label, stok, tipe, hex], lalu dipisah.
        $entries = [];
        $kumpulkan = function ($nilai) use (&$kumpulkan, &$entries, $kunciUkuran, $kunciWarna, $kunciNama, $kunciStok, $kunciHex) {
            if (is_array($nilai)) {
                $assoc = $nilai !== [] && array_keys($nilai) !== range(0, count($nilai) - 1);
                if (!$assoc) {
                    foreach ($nilai as $v) {
                        $kumpulkan($v);
                    }
                    return;
                }
                $low = array_change_key_case($nilai, CASE_LOWER);
                $kenal = array_merge($kunciUkuran, $kunciWarna, $kunciNama, $kunciStok);
                if (!array_intersect(array_keys($low), $kenal)) {
                    // Bentuk peta: ['M' => 2, 'L' => 1]
                    foreach ($nilai as $k => $v) {
                        if (!is_array($v) && is_numeric($v) && !is_numeric($k)) {
                            $entries[] = ['label' => trim((string) $k), 'stok' => (int) $v, 'tipe' => null, 'hex' => null];
                        } else {
                            $kumpulkan($v);
                        }
                    }
                    return;
                }
                $label = null;
                $tipe = null;
                $stok = null;
                $hex = null;
                foreach ($kunciStok as $k) {
                    if (isset($low[$k]) && is_numeric($low[$k])) {
                        $stok = (int) $low[$k];
                        break;
                    }
                }
                foreach ([[$kunciUkuran, 'ukuran'], [$kunciWarna, 'warna'], [$kunciNama, null]] as $grup) {
                    foreach ($grup[0] as $k) {
                        if ($label === null && isset($low[$k]) && is_scalar($low[$k]) && trim((string) $low[$k]) !== '') {
                            $label = trim((string) $low[$k]);
                            $tipe = $grup[1];
                        }
                    }
                }
                foreach ($kunciHex as $k) {
                    if (!empty($low[$k]) && is_string($low[$k])) {
                        $hex = $low[$k];
                        break;
                    }
                }
                if ($label === null) {
                    foreach ($nilai as $v) {
                        $kumpulkan($v);
                    }
                    return;
                }
                $entries[] = ['label' => $label, 'stok' => $stok, 'tipe' => $tipe, 'hex' => $hex];
                return;
            }

            if ($nilai === null || is_bool($nilai)) {
                return;
            }
            $teks = trim((string) $nilai);
            if ($teks === '') {
                return;
            }
            if (is_numeric($teks)) {
                $n = count($entries);
                if ($n > 0 && $entries[$n - 1]['stok'] === null && !is_numeric($entries[$n - 1]['label'])) {
                    $entries[$n - 1]['stok'] = (int) $teks; // angka setelah label = stok label tsb
                    return;
                }
            }
            if (preg_match('/^(.+?)\s*[:=]\s*(\d+)$/u', $teks, $m)) {
                $entries[] = ['label' => trim($m[1]), 'stok' => (int) $m[2], 'tipe' => null, 'hex' => null];
                return;
            }
            $entries[] = ['label' => $teks, 'stok' => null, 'tipe' => null, 'hex' => null];
        };

        // Ubah nilai kolom/relasi (string, JSON, array, Collection/Model) menjadi array biasa
        $keArray = function ($nilai) {
            if (is_object($nilai) && method_exists($nilai, 'toArray')) {
                $nilai = $nilai->toArray();
            }
            if (is_string($nilai)) {
                $t = trim($nilai);
                if ($t !== '' && ($t[0] === '[' || $t[0] === '{')) {
                    $j = json_decode($t, true);
                    if (is_array($j)) {
                        return $j;
                    }
                }
                return array_map('trim', explode(',', $t));
            }
            return $nilai;
        };

        $kumpulkan($keArray($produk->ukuran));
        $entriUkuran = $entries;
        $entries = [];

        // Warna yang diisi di halaman admin: cari kolom/relasi bernama warna/color pada produk
        $namaKolom = method_exists($produk, 'getAttributes') ? array_keys($produk->getAttributes()) : array_keys(get_object_vars($produk));
        $sumberWarna = null;
        foreach (['warna', 'warna_tersedia', 'varian_warna', 'warna_produk', 'pilihan_warna', 'colors', 'color'] as $k) {
            if (in_array($k, $namaKolom, true)) {
                $sumberWarna = $k;
                break;
            }
        }
        if ($sumberWarna === null) {
            foreach ($namaKolom as $k) {
                if (preg_match('/warna|colou?r/i', (string) $k)) {
                    $sumberWarna = $k;
                    break;
                }
            }
        }
        if ($sumberWarna === null) {
            foreach (['warna', 'warnas', 'varianWarna', 'warnaProduk'] as $k) {
                if (method_exists($produk, $k)) {
                    $sumberWarna = $k;
                    break;
                }
            }
        }
        if ($sumberWarna !== null) {
            $kumpulkan($keArray($produk->{$sumberWarna}));
        }
        $entriWarnaAdmin = $entries;
        foreach ($entriWarnaAdmin as $i => $e) {
            $entriWarnaAdmin[$i]['tipe'] = 'warna';
        }
        $adaWarnaAdmin = count($entriWarnaAdmin) > 0;
        // Bila warna admin ada, warna yang "terselip" di kolom ukuran tidak dipakai
        $entries = array_merge($entriUkuran, $entriWarnaAdmin);
        $jumlahEntriUkuran = count($entriUkuran);

        // Nama warna -> kode warna untuk bulatan contoh (yang lebih spesifik ditaruh lebih dulu)
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
        uksort($petaWarna, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        $ukuranOpsi = [];
        $warnaOpsi = [];
        $terlihat = [];
        foreach ($entries as $idx => $e) {
            $label = $e['label'];
            $tipe = $e['tipe'];
            if ($tipe === null) {
                $tipe = (in_array(strtoupper($label), $daftarUkuran, true) || preg_match('/^\d{1,3}$/', $label)) ? 'ukuran' : 'warna';
            }
            $unik = $tipe . '|' . mb_strtolower($label);
            if (isset($terlihat[$unik])) {
                continue;
            }
            $terlihat[$unik] = true;

            if ($tipe === 'ukuran') {
                $ukuranOpsi[] = ['label' => $label, 'stok' => $e['stok']];
                continue;
            }
            if ($adaWarnaAdmin && $idx < $jumlahEntriUkuran) {
                continue; // warna dari kolom ukuran diabaikan karena sudah ada data warna dari admin
            }

            $hex = (is_string($e['hex']) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $e['hex'])) ? $e['hex'] : null;
            if ($hex === null && preg_match('/^#[0-9a-fA-F]{3,8}$/', $label)) {
                $hex = $label; // label berupa kode warna
            }
            if ($hex === null) {
                $rendah = mb_strtolower($label);
                foreach ($petaWarna as $kata => $kode) {
                    if (strpos($rendah, $kata) !== false) {
                        $hex = $kode;
                        break;
                    }
                }
            }
            $warnaOpsi[] = ['label' => $label, 'stok' => $e['stok'], 'hex' => $hex ?? '#cfc8bb'];
        }

        if (empty($ukuranOpsi)) {
            foreach (['S', 'M', 'L', 'XL', 'Custom'] as $u) {
                $ukuranOpsi[] = ['label' => $u, 'stok' => null];
            }
        }

        $ukuranList = array_column($ukuranOpsi, 'label');
        $adaWarna = count($warnaOpsi) > 0;
        $stokUkuran = array_column($ukuranOpsi, 'stok', 'label');
        $stokWarna = array_column($warnaOpsi, 'stok', 'label');

        $tersedia = function ($o) {
            return $o['stok'] === null || $o['stok'] > 0;
        };
        $ukuranDefault = null;
        foreach ($ukuranOpsi as $o) {
            if ($tersedia($o) && strtoupper($o['label']) === 'M') {
                $ukuranDefault = $o['label'];
                break;
            }
        }
        if ($ukuranDefault === null) {
            foreach ($ukuranOpsi as $o) {
                if ($tersedia($o)) {
                    $ukuranDefault = $o['label'];
                    break;
                }
            }
        }
        if ($ukuranDefault === null) {
            $ukuranDefault = $ukuranOpsi[0]['label'];
        }
        $warnaDefault = '';
        foreach ($warnaOpsi as $o) {
            if ($tersedia($o)) {
                $warnaDefault = $o['label'];
                break;
            }
        }
        if ($warnaDefault === '' && $adaWarna) {
            $warnaDefault = $warnaOpsi[0]['label'];
        }

        // Nomor langkah menyesuaikan ada/tidaknya pilihan warna
        $noTanggal = $adaWarna ? 3 : 2;
        $noJumlah = $adaWarna ? 3 : 2;
        $noKirim = $adaWarna ? 4 : 3;

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

        $config = [
            'mode' => (request('mode') === 'beli' && $produk->dapat_dijual) || !$produk->bisa_disewa ? 'beli' : 'sewa',
            'hargaSewa' => (float) ($produk->harga_sewa ?? 0),
            'dendaPerHari' => (float) ($produk->denda_per_hari_telat ?? 0),
            'hargaJual' => (float) ($produk->harga_jual ?? 0),
            'stok' => (int) ($produk->stok ?? 0),
            'ukuran' => $ukuranList,
            'ukuranDefault' => $ukuranDefault,
            'warnaDefault' => $warnaDefault,
            'stokUkuran' => (object) $stokUkuran,
            'stokWarna' => (object) $stokWarna,
            'dibooking' => array_values($dibooking ?? []),
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
                <div class="lg:sticky lg:top-24 lg:self-start w-full max-w-[300px] sm:max-w-[380px] lg:max-w-[400px] mx-auto lg:mx-0">

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

                    <h1 class="pd-up font-display text-[26px] sm:text-3xl lg:text-[34px] font-semibold text-ink/90 leading-tight mt-3"
                        style="--d: .1s;">{{ $produk->nama }}</h1>
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
                            <p class="font-display text-[28px] font-medium text-ink leading-none lining-nums tabular-nums">
                                <span x-text="formatRupiah(hargaTampil)"></span>
                                <span class="font-sans text-xs font-normal text-ink/50">/ <span
                                        x-text="durasi > 0 ? durasi + ' hari' : 'sewa'"></span></span>
                            </p>

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
                                <p>Penyewaan memiliki 3 tahap utama: pengambilan, pemakaian, dan pengembalian. Contoh
                                    normal: diambil hari Senin, dipakai untuk acara hari Selasa, dikembalikan hari Rabu.</p>
                            </div>

                            <div
                                class="mt-3 p-3 rounded-lg border border-dashed border-gold/50 text-xs text-ink/60 leading-relaxed bg-gold/[0.04]">
                                Ingin pemakaian lebih dari 1 hari? Tidak perlu bayar harga sewa penuh lagi — cukup tambahan
                                <strong
                                    class="text-gold font-semibold">Rp{{ number_format($produk->denda_per_hari_telat, 0, ',', '.') }}
                                    per hari</strong>.
                            </div>
                        </div>
                    </div>

                    {{-- ===== MODE BELI: HARGA ===== --}}
                    <div x-show="mode === 'beli'" x-cloak class="pd-swap">
                        <div class="bg-cream/70 rounded-2xl p-5 mb-6 border border-line/30 hover:border-gold/40 hover:shadow-md transition-all duration-300">
                            <p class="text-xs text-ink/50 mb-1">Harga Beli</p>
                            <p
                                class="font-display text-[28px] font-semibold text-ink/90 leading-none lining-nums tabular-nums">
                                Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                            <p class="mt-2 text-xs text-ink/60 flex items-center gap-1.5">
                                <span class="inline-block w-1.5 h-1.5 rounded-full {{ $produk->stok > 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ $produk->stok > 0 ? 'Tersedia untuk dibeli' : 'Stok habis' }}
                            </p>
                        </div>
                    </div>

                    {{-- Pilihan 1: Ukuran --}}
                    <div class="pd-up mb-5 rounded-2xl border border-line/40 bg-white/80 p-4 sm:p-5 shadow-sm" style="--d: .3s;">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <p class="flex items-center gap-2 text-sm font-semibold text-ink/85">
                                <span class="flex w-5 h-5 items-center justify-center rounded-full bg-ink/90 text-gold text-[11px] font-bold">1</span>
                                Pilih Ukuran
                            </p>
                            <p class="text-xs text-ink/50">Terpilih: <span class="font-semibold text-gold" x-text="ukuran"></span></p>
                        </div>
                        <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Ukuran">
                            @foreach ($ukuranOpsi as $o)
                                @php
                                    $u = $o['label'];
                                    $habisU = $o['stok'] !== null && $o['stok'] <= 0;
                                @endphp
                                <button type="button" role="radio" :aria-checked="ukuran === @js($u)" @click="ukuran = @js($u)"
                                    @if ($habisU) :disabled="mode === 'beli'" @endif
                                    :class="[ukuran === @js($u) ? 'bg-ink/90 text-gold border-ink/90 shadow-md' : 'bg-white text-ink/65 border-line/60 hover:border-gold hover:text-ink hover:-translate-y-0.5', @if ($habisU) (mode === 'beli' ? 'opacity-40 line-through cursor-not-allowed' : '') @else '' @endif]"
                                    class="relative min-w-[56px] min-h-[44px] px-4 py-1.5 rounded-xl border text-[13px] font-semibold active:scale-95 transition-all duration-200">
                                    <span class="block leading-tight">{{ $u }}</span>
                                    @if ($o['stok'] !== null)
                                        <span x-show="mode === 'beli'" x-cloak
                                            class="block text-[10px] font-normal leading-tight {{ $habisU ? 'text-rose-500' : 'opacity-60' }}">{{ $habisU ? 'habis' : 'stok ' . $o['stok'] }}</span>
                                    @endif
                                    <span x-show="ukuran === @js($u)" x-cloak
                                        class="pd-pop absolute -top-1.5 -right-1.5 flex w-4 h-4 items-center justify-center rounded-full bg-gold text-white text-[9px] leading-none">✓</span>
                                </button>
                            @endforeach
                        </div>
                        <p class="mt-3 text-[11px] text-ink/45">Ragu soal ukuran? Tanyakan lewat WhatsApp di bawah, kami bantu pilihkan.</p>
                    </div>

                    @if ($adaWarna)
                        {{-- Pilihan 2: Warna --}}
                        <div class="pd-up mb-5 rounded-2xl border border-line/40 bg-white/80 p-4 sm:p-5 shadow-sm" style="--d: .33s;">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <p class="flex items-center gap-2 text-sm font-semibold text-ink/85">
                                    <span class="flex w-5 h-5 items-center justify-center rounded-full bg-ink/90 text-gold text-[11px] font-bold">2</span>
                                    Pilih Warna
                                </p>
                                <p class="text-xs text-ink/50 text-right">Terpilih: <span class="font-semibold text-gold" x-text="varianWarna || '-'"></span></p>
                            </div>
                            <div class="flex flex-wrap gap-x-3 gap-y-4" role="radiogroup" aria-label="Warna">
                                @foreach ($warnaOpsi as $w)
                                    @php $habisW = $w['stok'] !== null && $w['stok'] <= 0; @endphp
                                    <label class="w-[76px] flex flex-col items-center gap-1.5 text-center {{ $habisW ? '' : 'cursor-pointer' }}"
                                        @if ($habisW) :class="mode === 'beli' ? 'cursor-not-allowed' : 'cursor-pointer'" @endif>
                                        <input type="radio" name="pilih_warna" value="{{ $w['label'] }}" class="peer sr-only"
                                            x-model="varianWarna" @if ($habisW) :disabled="mode === 'beli'" @endif>
                                        <span class="relative block w-12 h-12 rounded-full border border-black/10 ring-2 ring-offset-2 ring-offset-white ring-transparent shadow-inner hover:scale-110 peer-checked:ring-gold peer-checked:scale-110 transition-all duration-300"
                                            @if ($habisW) :class="mode === 'beli' ? 'opacity-40' : ''" @endif
                                            style="background: {{ $w['hex'] }};">
                                            <span x-show="varianWarna === @js($w['label'])" x-cloak
                                                class="pd-pop absolute -top-1 -right-1 flex w-4 h-4 items-center justify-center rounded-full bg-gold text-white text-[9px] leading-none">✓</span>
                                            @if ($habisW)
                                                <span x-show="mode === 'beli'" x-cloak aria-hidden="true" class="absolute inset-0 flex items-center justify-center overflow-hidden rounded-full">
                                                    <span class="block w-[130%] h-[2px] bg-rose-500/80 -rotate-45"></span>
                                                </span>
                                            @endif
                                        </span>
                                        <span class="text-[11px] leading-tight text-ink/75 peer-checked:text-gold peer-checked:font-semibold">{{ $w['label'] }}</span>
                                        @if ($w['stok'] !== null)
                                            <span x-show="mode === 'beli'" x-cloak class="text-[10px] -mt-1 {{ $habisW ? 'font-medium text-rose-500' : 'text-ink/45' }}">{{ $habisW ? 'Habis' : 'stok ' . $w['stok'] }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ===== MODE SEWA: KALENDER ===== --}}
                    <div x-show="mode === 'sewa'" x-cloak class="pd-swap">
                        <div class="mb-6" id="kalender-sewa">
                            <p class="flex items-center gap-2 text-sm font-semibold text-ink/85 mb-3">
                                <span class="flex w-5 h-5 items-center justify-center rounded-full bg-ink/90 text-gold text-[11px] font-bold">{{ $noTanggal }}</span>
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

                                <div class="grid grid-cols-7 gap-y-1 text-center transition-all duration-150 ease-out"
                                    :class="gantiBulan ? (arah > 0 ? 'opacity-0 -translate-x-3' : 'opacity-0 translate-x-3') : 'opacity-100 translate-x-0'">
                                    <template x-for="(h, idx) in namaHari" :key="idx">
                                        <div class="text-[11px] text-ink/50 py-1.5 font-medium" x-text="h"></div>
                                    </template>

                                    <template x-for="c in sel" :key="c.t">
                                        <button type="button" @click="pilihTanggal(c.t)" :disabled="c.luar || nonaktif(c.t)"
                                            :class="kelas(c)"
                                            class="h-10 sm:h-10 mx-px sm:mx-0.5 rounded-lg text-xs font-medium transition-all duration-200 ease-out">
                                            <span x-text="c.hari"></span>
                                        </button>
                                    </template>
                                </div>

                                {{-- Legend --}}
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 pt-3 border-t border-line/30 text-[11px] text-ink/55">
                                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-ink/80"></span>
                                        Dipilih</div>
                                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gold/30"></span>
                                        Rentang sewa</div>
                                    <div class="flex items-center gap-1.5"><span
                                            class="w-2.5 h-2.5 rounded-full bg-rose-200"></span> Sudah dibooking</div>
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
                                    <span x-text="'Tambahan ' + (durasi - 1) + ' hari × ' + formatRupiah(dendaPerHari)"></span>
                                    <span class="tabular-nums text-ink/85" x-text="formatRupiah((durasi - 1) * dendaPerHari)"></span>
                                </div>
                                <div class="flex justify-between pt-1.5 border-t border-line/30 text-[13px] font-semibold text-ink/90">
                                    <span>Total sewa</span>
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
                                    <span class="flex w-5 h-5 items-center justify-center rounded-full bg-ink/90 text-gold text-[11px] font-bold">{{ $noJumlah }}</span>
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

                        {{-- Pilihan 4: Pengiriman --}}
                        <div class="mb-5 rounded-2xl border border-line/40 bg-white/80 p-4 sm:p-5 shadow-sm">
                            <p class="flex items-center gap-2 text-sm font-semibold text-ink/85 mb-3">
                                <span class="flex w-5 h-5 items-center justify-center rounded-full bg-ink/90 text-gold text-[11px] font-bold">{{ $noKirim }}</span>
                                Metode Pengiriman
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label class="block cursor-pointer">
                                    <input type="radio" name="metode" value="ambil" class="peer sr-only" x-model="metodeKirim">
                                    <div class="h-full flex items-start gap-2 p-3 rounded-xl border border-line/50 hover:border-gold/60 peer-checked:border-gold peer-checked:bg-gold/5 peer-checked:shadow-sm transition-all duration-300">
                                        <div class="flex-1">
                                            <p class="text-[13px] font-semibold text-ink/85">Ambil di Toko</p>
                                            <p class="text-xs text-ink/55">Gratis, tanpa ongkos kirim.</p>
                                        </div>
                                    </div>
                                </label>
                                <label class="block cursor-pointer">
                                    <input type="radio" name="metode" value="kirim" class="peer sr-only" x-model="metodeKirim">
                                    <div class="h-full flex items-start gap-2 p-3 rounded-xl border border-line/50 hover:border-gold/60 peer-checked:border-gold peer-checked:bg-gold/5 peer-checked:shadow-sm transition-all duration-300">
                                        <div class="flex-1">
                                            <p class="text-[13px] font-semibold text-ink/85">Dikirim ke Alamat</p>
                                            <p class="text-xs text-ink/55">Ongkir dihitung otomatis saat checkout.</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Ringkasan pilihan --}}
                    <div class="pd-up mb-3 flex flex-wrap items-center gap-x-2 gap-y-1.5 rounded-xl bg-ink/[0.04] border border-line/30 px-3.5 py-2.5 text-xs text-ink/70" style="--d: .35s;">
                        <span class="font-semibold text-ink/85">Pilihanmu:</span>
                        <span class="px-2 py-0.5 rounded-md bg-white border border-line/40">Ukuran <b class="text-ink/90" x-text="ukuran"></b></span>
                        <span x-show="varianWarna" x-cloak class="px-2 py-0.5 rounded-md bg-white border border-line/40">Warna <b class="text-ink/90" x-text="varianWarna"></b></span>
                        <span x-show="mode === 'beli'" x-cloak class="px-2 py-0.5 rounded-md bg-white border border-line/40">Jumlah <b class="text-ink/90" x-text="jumlah"></b></span>
                        <span x-show="mode === 'sewa'" x-cloak class="px-2 py-0.5 rounded-md border"
                            :class="durasi > 0 ? 'bg-white border-line/40' : 'bg-amber-50 border-amber-300 text-amber-700'">
                            <b class="font-semibold" x-text="durasi > 0 ? formatTanggal(tanggalAmbil) + ' – ' + formatTanggal(tanggalKembali) + ' (' + durasi + ' hari)' : 'Tanggal belum dipilih'"></b>
                        </span>
                    </div>

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
                                    <input type="hidden" name="ukuran" :value="ukuran">
                                    <input type="hidden" name="warna" :value="varianWarna">
                                    <input type="hidden" name="durasi" :value="durasi">
                                    <input type="hidden" name="total_sewa" :value="totalSewa">
                                    <button type="submit" @click.prevent="kirimSewa()" :disabled="memuat"
                                        :class="{ 'pd-ready': tanggalAmbil && tanggalKembali && !memuat }"
                                        class="pd-shine w-full h-12 rounded-xl bg-gold text-white text-[13px] font-semibold tracking-wide hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait transition-all duration-300">
                                        <span x-show="!memuat">Sewa Sekarang</span>
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
                                    @if ($produk->stok > 0)
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
                            <input type="hidden" name="ukuran" :value="ukuran">
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

                            <button type="submit" @click.prevent="tambahKeKeranjang($event)" :disabled="memuat"
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

            {{-- ==================== DESKRIPSI & SPESIFIKASI ==================== --}}
            <div class="mt-12 md:mt-16 grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-12">
                <div class="lg:col-span-3">
                    <h2 class="pd-reveal font-display text-xl md:text-2xl font-semibold text-ink/90 mb-1">Deskripsi Produk</h2>
                    <span class="pd-reveal block h-[2px] w-10 bg-gold/70 rounded-full mb-4"></span>

                    <div class="space-y-3 text-sm md:text-[15px] text-ink/65 leading-relaxed">
                        @foreach ($paragrafDeskripsi as $i => $paragraf)
                            @if (trim($paragraf) !== '')
                                <p class="pd-reveal" style="--d: {{ $i * 0.08 }}s;">{{ preg_replace('/\s*\R\s*/', ' ', trim($paragraf)) }}</p>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="pd-reveal rounded-2xl border border-line/40 bg-white/80 p-4 md:p-5 shadow-sm">
                        <h3 class="font-display text-base font-semibold text-ink/90 mb-2">Spesifikasi</h3>
                        @foreach ([
                            ['Kategori', $produk->kategori->nama_kategori ?? '-'],
                            ['Material', $toTeks($produk->bahan ?? '-')],
                            ['Ukuran Tersedia', $ukuranList ? implode(', ', $ukuranList) : '-'],
                            ...($adaWarna ? [['Warna', implode(', ', array_column($warnaOpsi, 'label'))]] : [])
                        ] as $baris)
                            <div class="flex justify-between gap-4 py-3 px-2 -mx-2 rounded-md text-[13px] border-b border-line/40 last:border-b-0 hover:bg-cream/60 transition-colors duration-300">
                                <span class="text-ink/55 shrink-0">{{ $baris[0] }}</span>
                                <span class="text-ink/85 font-medium text-right">{{ $baris[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Produk Serupa --}}
            @if ($serupa->count() > 0)
                <div class="mt-14 md:mt-20">
                    <div class="pd-reveal">
                        <x-section-heading title="Produk Serupa"
                            subtitle="Pilihan lain dari kategori {{ $produk->kategori->nama_kategori }}" />
                    </div>
                    <div class="pd-reveal flex md:grid md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4 overflow-x-auto md:overflow-visible snap-x snap-mandatory pd-noscroll -mx-4 px-4 md:mx-0 md:px-0 pb-2">
                        @foreach ($serupa as $i => $s)
                            <div class="h-full shrink-0 w-[62%] sm:w-[40%] md:w-auto snap-start">
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
            class="lg:hidden fixed inset-x-0 bottom-0 z-40 border-t border-line/40 bg-white/90 backdrop-blur-md px-4 pt-3 pb-[calc(.75rem+env(safe-area-inset-bottom))] shadow-[0_-10px_30px_rgba(0,0,0,.08)]">
            <div class="flex items-center gap-3 max-w-[1180px] mx-auto">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] text-ink/50 truncate"
                        x-text="mode === 'sewa' ? (durasi > 0 ? 'Total sewa · ' + durasi + ' hari' : 'Harga sewa') : 'Total beli'"></p>
                    <p class="font-display text-lg font-semibold text-ink leading-tight tabular-nums truncate"
                        x-text="formatRupiah(mode === 'sewa' ? hargaTampil : totalBeli)"></p>
                </div>
                <button type="button" @click="aksiUtama()" :disabled="memuat || (mode === 'beli' && stok <= 0)"
                    class="pd-shine shrink-0 h-12 px-6 rounded-xl bg-gold text-white text-[13px] font-semibold tracking-wide active:scale-95 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed transition-all duration-200"
                    x-text="mode === 'sewa' ? 'Sewa Sekarang' : (stok > 0 ? 'Beli Sekarang' : 'Stok Habis')"></button>
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
                hargaSewaPerHari: cfg.hargaSewa,
                dendaPerHari: cfg.dendaPerHari,
                hargaJual: cfg.hargaJual,
                stokProduk: cfg.stok,
                stokUkuran: cfg.stokUkuran || {},
                stokWarna: cfg.stokWarna || {},
                dibooking: cfg.dibooking,
                ukuran: cfg.ukuranDefault,
                tanggalAmbil: null,
                tanggalKembali: null,
                varianWarna: cfg.warnaDefault || '',
                jumlah: 1,
                metodeKirim: 'ambil',
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
                getar: false,
                memuat: false,
                barTampil: false,
                hargaTampil: cfg.hargaSewa,
                _anim: null,
                _tx: 0,
                _ty: 0,

                // Alpine memanggil init() otomatis
                init() {
                    this.$watch('totalSewa', (v) => this.tweenHarga(v || this.hargaSewaPerHari));
                    this.$watch('tanggalKembali', (v) => { if (v) this.pesanTanggal = ''; });
                    this.$watch('lightbox', (v) => { document.body.style.overflow = v ? 'hidden' : ''; });
                    this.$watch('stok', (v) => { if (v > 0 && this.jumlah > v) this.jumlah = v; });

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
                tambahJumlah() { if (this.jumlah < this.stok) this.jumlah++; },
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
                    if (!this.tanggalAmbil || !this.tanggalKembali) {
                        this.peringatiTanggal();
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

                // Stok yang berlaku = stok terkecil dari ukuran & warna yang dipilih
                get stok() {
                    const kandidat = [];
                    const su = this.stokUkuran[this.ukuran];
                    const sw = this.stokWarna[this.varianWarna];
                    if (typeof su === 'number') kandidat.push(su);
                    if (typeof sw === 'number') kandidat.push(sw);
                    if (!kandidat.length) return this.stokProduk;
                    return Math.max(0, Math.min(...kandidat));
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
                penuh(t) {
                    return this.dibooking.includes(t);
                },
                nonaktif(t) {
                    return this.lalu(t) || this.penuh(t);
                },
                kelas(c) {
                    if (this.isSelected(c.t)) return 'bg-ink/90 text-gold font-semibold scale-105 shadow-md relative z-10';
                    if (this.isInRange(c.t)) return 'bg-gold/15 text-ink/85 font-medium';
                    if (c.luar || this.lalu(c.t)) return 'text-ink/25 cursor-default';
                    if (this.penuh(c.t)) return 'bg-rose-50 text-rose-400 cursor-not-allowed line-through decoration-rose-300';
                    const cincin = c.t === this.hariIni ? ' ring-1 ring-gold/60 text-ink' : '';
                    return 'text-ink/70 hover:bg-cream hover:text-ink hover:scale-110 active:scale-95' + cincin;
                },
                pilihTanggal(t) {
                    if (this.nonaktif(t)) return;
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
                get totalSewa() {
                    if (this.durasi === 0) return 0;
                    return this.hargaSewaPerHari + (Math.max(0, this.durasi - 1) * this.dendaPerHari);
                },
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