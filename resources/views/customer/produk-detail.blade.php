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

        .pd-up   { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
        .pd-swap { animation: pd-swap .45s ease-out both; }
        .pd-pop  { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }
        .pd-line { animation: pd-grow .8s .55s ease-out both; }
        .pd-shake { animation: pd-shake .45s ease; }
        .pd-ready { animation: pd-glow 2s ease-in-out infinite; }

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

        @media (prefers-reduced-motion: reduce) {
            .pd-up, .pd-swap, .pd-pop, .pd-line, .pd-shake, .pd-ready, .group:hover .pd-wa-icon { animation: none !important; }
            .pd-js .pd-reveal:not(.is-in) { opacity: 1; transform: none; }
            .pd-reveal, .pd-shine::after { transition: none !important; }
        }
    </style>

    @php
        $statusLabel = \App\Support\ProdukStatus::label($produk->status, $produk->dapat_dijual);

        $ukuranList = array_values(array_filter(array_map('trim', explode(',', (string) $produk->ukuran))));
        if (empty($ukuranList)) {
            $ukuranList = ['S', 'M', 'L', 'XL', 'Custom'];
        }

        $paragrafDeskripsi = preg_split('/\R{2,}/', trim((string) $produk->deskripsi)) ?: [];
        $jumlahDisewa = $produk->getAttributes()['jumlah_disewa'] ?? null;

        $config = [
            'mode' => request('mode') === 'beli' && $produk->dapat_dijual ? 'beli' : 'sewa',
            'hargaSewa' => (float) ($produk->harga_sewa ?? 0),
            'dendaPerHari' => (float) ($produk->denda_per_hari_telat ?? 0),
            'hargaJual' => (float) ($produk->harga_jual ?? 0),
            'ukuran' => $ukuranList,
            'ukuranDefault' => in_array('M', $ukuranList, true) ? 'M' : $ukuranList[0],
            'dibooking' => array_values($dibooking ?? []),
        ];

        $thumbGradien = [
            'linear-gradient(135deg,#3a2a12,#1b130a)',
            'linear-gradient(135deg,#32230f,#1a1209)',
            'linear-gradient(135deg,#7a1a2a,#4a0f1a)',
            'linear-gradient(135deg,#1b3a33,#0f2420)',
        ];
    @endphp

    <div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans" x-data="produkDetail(@js($config))">
        <div class="max-w-[1180px] mx-auto px-4 md:px-6">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

                {{-- ==================== KOLOM KIRI ==================== --}}
                <div>

                    {{-- Gambar utama --}}
                    <div class="pd-up group relative aspect-[4/5] rounded-2xl overflow-hidden border border-line/40 shadow-sm hover:shadow-xl transition-shadow duration-500"
                        style="background:#1b130a;">
                        @if ($produk->foto_produk)
                            <img src="{{ asset('storage/' . $produk->foto_produk) }}" alt="{{ $produk->nama }}"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-[1200ms] ease-out group-hover:scale-105">
                        @else
                            @foreach ($thumbGradien as $i => $gradien)
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-8 transition-all duration-700 ease-out group-hover:scale-105 {{ $i === 0 ? '' : 'opacity-0' }}"
                                    :class="{ 'opacity-0': fotoAktif !== {{ $i }} }"
                                    style="background: {{ $gradien }};">
                                    <p class="font-display text-xl md:text-2xl text-[#e3b64f] leading-snug">{{ $produk->nama }}</p>
                                    <p class="mt-2 text-sm text-white/80">{{ $produk->kategori->nama_kategori }}</p>
                                </div>
                            @endforeach
                        @endif

                        <span
                            class="absolute top-3 left-3 px-3 py-1.5 rounded-md bg-white/95 text-ink/80 text-xs font-medium tracking-wide shadow-sm">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Thumbnail --}}
                    <div class="flex gap-2.5 mt-3 pd-up" style="--d: .1s;">
                        @foreach ($thumbGradien as $i => $gradien)
                            <button type="button" @click="fotoAktif = {{ $i }}" aria-label="Foto {{ $i + 1 }}"
                                class="w-14 h-14 sm:w-16 sm:h-16 rounded-lg border-2 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md active:scale-95 {{ $i === 0 ? 'border-gold' : 'border-transparent' }}"
                                :class="{ 'border-gold': fotoAktif === {{ $i }}, 'border-transparent': fotoAktif !== {{ $i }} }"
                                style="background: {{ $gradien }};"></button>
                        @endforeach
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mt-8 md:mt-10">
                        <h2 class="pd-reveal font-display text-xl font-semibold text-ink/90 mb-3">Deskripsi Produk</h2>

                        <div class="space-y-3 text-sm text-ink/65 leading-relaxed">
                            @foreach ($paragrafDeskripsi as $i => $paragraf)
                                @if (trim($paragraf) !== '')
                                    <p class="pd-reveal" style="--d: {{ $i * 0.08 }}s;">{{ preg_replace('/\s*\R\s*/', ' ', trim($paragraf)) }}</p>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-5">
                            @foreach ([['Kategori', $produk->kategori->nama_kategori], ['Material', $produk->bahan], ['Ukuran Tersedia', $produk->ukuran]] as $i => $baris)
                                <div class="pd-reveal flex justify-between gap-4 py-3 px-2 -mx-2 rounded-md text-[13px] border-b border-line/40 hover:bg-cream/60 transition-colors duration-300"
                                    style="--d: {{ $i * 0.1 }}s;">
                                    <span class="text-ink/55">{{ $baris[0] }}</span>
                                    <span class="text-ink/85 font-medium text-right">{{ $baris[1] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ==================== KOLOM KANAN ==================== --}}
                <div>

                                        <div class="pd-up flex items-center gap-2" style="--d: .05s;">
                        <span class="inline-block px-2.5 py-1 rounded-md bg-ink/90 text-gold text-[11px] font-medium tracking-wider">
                            {{ $produk->dapat_dijual ? 'Sewa & Beli' : 'Khusus Sewa' }}
                        </span>
                        
                        {{-- BADGE KATEGORI BARU --}}
                        <a href="{{ route('koleksi.index', ['kategori' => [$produk->kategori->slug]]) }}" 
                        class="inline-block px-2.5 py-1 rounded-md bg-gold/10 text-gold border border-gold/30 hover:bg-gold hover:text-white transition-colors text-[11px] font-bold tracking-wider">
                            {{ $produk->kategori->nama_kategori }}
                        </a>
                    </div>

                    <h1 class="pd-up font-display text-2xl lg:text-[32px] font-semibold text-ink/90 leading-tight mt-3"
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
                    @if ($produk->dapat_dijual)
                        <div class="pd-up relative grid grid-cols-2 border border-line/40 rounded-lg overflow-hidden bg-white/70 mb-5"
                            style="--d: .2s;">
                            <span aria-hidden="true"
                                class="absolute inset-y-0 left-0 w-1/2 bg-ink/90 transition-transform duration-300 ease-out {{ $config['mode'] === 'beli' ? 'translate-x-full' : '' }}"
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

                    {{-- ===== MODE SEWA ===== --}}
                    <div x-show="mode === 'sewa'" x-cloak class="pd-swap">

                        {{-- Harga & penjelasan --}}
                        <div class="bg-cream/70 rounded-xl p-5 mb-6 border border-line/30 hover:border-gold/40 hover:shadow-md transition-all duration-300">
                            <p class="font-display text-[26px] font-medium text-ink leading-none lining-nums tabular-nums">
                                <span x-text="formatRupiah(hargaTampil)"></span>
                                <span class="font-sans text-xs font-normal text-ink/50">/ <span
                                        x-text="durasi > 0 ? durasi + ' hari' : 'sewa'"></span></span>
                            </p>

                            <div class="mt-3 text-[13px] text-ink/60 leading-relaxed">
                                <p class="font-semibold text-ink/75 mb-1">Jadwal Penyewaan:</p>
                                <p>Penyewaan memiliki 3 tahap utama: pengambilan, pemakaian, dan pengembalian. Contoh
                                    normal: diambil hari Senin, dipakai untuk acara hari Selasa, dikembalikan hari Rabu.</p>
                            </div>

                            <div
                                class="mt-3 p-3 rounded-md border border-dashed border-gold/50 text-xs text-ink/60 leading-relaxed bg-gold/[0.04]">
                                Ingin pemakaian lebih dari 1 hari? Tidak perlu bayar harga sewa penuh lagi — cukup tambahan
                                <strong
                                    class="text-gold font-semibold">Rp{{ number_format($produk->denda_per_hari_telat, 0, ',', '.') }}
                                    per hari</strong>.
                            </div>
                        </div>

                        {{-- Kalender --}}
                        <div class="mb-6" id="kalender-sewa">
                            <p class="text-sm font-semibold text-ink/85 mb-3">Tanggal Sewa</p>

                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-ink/85 capitalize" x-text="namaBulan"></span>
                                <div class="flex gap-1.5">
                                    <button type="button" @click="bulanSebelumnya()" :disabled="!bisaMundur"
                                        aria-label="Bulan sebelumnya"
                                        class="w-7 h-7 rounded-md border border-line/60 bg-white flex items-center justify-center text-ink/50 hover:border-gold hover:text-gold hover:-translate-x-0.5 active:scale-90 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:translate-x-0 disabled:hover:border-line/60 disabled:hover:text-ink/50 transition-all duration-200">
                                        <x-icon name="chevron-right" :size="11" class="rotate-180" />
                                    </button>
                                    <button type="button" @click="bulanBerikutnya()" aria-label="Bulan berikutnya"
                                        class="w-7 h-7 rounded-md border border-line/60 bg-white flex items-center justify-center text-ink/50 hover:border-gold hover:text-gold hover:translate-x-0.5 active:scale-90 transition-all duration-200">
                                        <x-icon name="chevron-right" :size="11" />
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-7 gap-y-0.5 text-center transition-all duration-150"
                                :class="gantiBulan ? 'opacity-0 translate-y-1' : 'opacity-100'">
                                <template x-for="(h, idx) in namaHari" :key="idx">
                                    <div class="text-[11px] text-ink/50 py-1.5 font-medium" x-text="h"></div>
                                </template>

                                <template x-for="c in sel" :key="c.t">
                                    <button type="button" @click="pilihTanggal(c.t)" :disabled="c.luar || nonaktif(c.t)"
                                        :class="kelas(c)"
                                        class="h-9 sm:h-10 mx-0.5 rounded-md text-xs font-medium transition-all duration-200 ease-out">
                                        <span x-text="c.hari"></span>
                                    </button>
                                </template>
                            </div>

                            {{-- Legend --}}
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 text-[11px] text-ink/55">
                                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-ink/80"></span>
                                    Dipilih</div>
                                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gold/30"></span>
                                    Rentang sewa</div>
                                <div class="flex items-center gap-1.5"><span
                                        class="w-2.5 h-2.5 rounded-full bg-rose-200"></span> Sudah dibooking</div>
                            </div>

                            {{-- Ringkasan tanggal --}}
                            <div class="mt-3 grid grid-cols-3 gap-2 bg-cream/60 rounded-lg px-4 py-3 text-xs border border-line/30 transition-colors duration-300"
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

                            <p x-show="pesanTanggal" x-cloak x-transition x-text="pesanTanggal"
                                class="mt-2 text-xs text-rose-600"></p>
                        </div>
                    </div>

                    {{-- ===== MODE BELI ===== --}}
                    <div x-show="mode === 'beli'" x-cloak class="pd-swap">
                        <div class="bg-cream/70 rounded-xl p-5 mb-6 border border-line/30 hover:border-gold/40 hover:shadow-md transition-all duration-300">
                            <p class="text-xs text-ink/50 mb-1">Harga Beli</p>
                            <p
                                class="font-display text-[26px] font-semibold text-ink/90 leading-none lining-nums tabular-nums">
                                Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                            <p class="mt-2 text-xs text-ink/60">Stok tersedia: <strong
                                    class="text-ink/85 font-semibold">{{ $produk->stok }} set</strong></p>
                        </div>

                        {{-- Varian Warna --}}
                        <div class="mb-6">
                            <p class="text-sm font-semibold text-ink/85 mb-2">Varian Warna & Motif</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ([['Emas Klasik', 'bg-[#c99a32]', 'Tersedia'], ['Merah Marun', 'bg-[#6b1c2a]', 'Tersedia'], ['Hitam Polos', 'bg-[#1a1a1a]', 'Tersedia'], ['Motif Songket', 'bg-[#a39a8e]', 'Stok Habis'], ['Biru Navy', 'bg-[#2a3f5c]', 'Stok Habis']] as $v)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="warna" value="{{ $v[0] }}"
                                            class="peer sr-only" {{ $v[2] === 'Stok Habis' ? 'disabled' : '' }}>
                                        <div
                                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg border border-line/50 text-xs {{ $v[2] === 'Stok Habis' ? 'opacity-40' : 'hover:border-gold/60 hover:-translate-y-px hover:shadow-sm' }} peer-checked:border-gold peer-checked:bg-gold/5 transition-all duration-300">
                                            <span class="w-4 h-4 rounded-full {{ $v[1] }} shrink-0"></span>
                                            <div class="flex-1">
                                                <p class="font-medium text-ink/85">{{ $v[0] }}</p>
                                                <p
                                                    class="text-[11px] {{ $v[2] === 'Stok Habis' ? 'text-rose-500' : 'text-emerald-600' }}">
                                                    {{ $v[2] }}</p>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Ukuran --}}
                    <div class="pd-up mb-6" style="--d: .3s;">
                        <p class="text-sm font-semibold text-ink/85 mb-2">Ukuran</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($ukuranList as $u)
                                <button type="button" @click="ukuran = @js($u)"
                                    :class="ukuran === @js($u) ? 'bg-ink/90 text-gold border-ink/90 scale-105 shadow-sm' :
                                        'bg-white text-ink/65 border-line/60 hover:border-gold hover:text-ink hover:-translate-y-0.5'"
                                    class="min-w-[44px] h-[40px] px-3 rounded-md border text-xs font-semibold active:scale-95 transition-all duration-200">{{ $u }}</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Jumlah (mode beli) --}}
                    <div x-show="mode === 'beli'" x-cloak class="pd-swap mb-6">
                        <p class="text-sm font-semibold text-ink/85 mb-2">Jumlah</p>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="jumlah = Math.max(1, jumlah - 1)"
                                class="w-9 h-9 rounded-md border border-line/60 flex items-center justify-center text-ink/60 hover:border-gold hover:text-gold active:scale-90 transition-all duration-200">−</button>
                            <span class="w-9 text-center text-sm font-semibold text-ink/85" x-text="jumlah"></span>
                            <button type="button" @click="jumlah++"
                                class="w-9 h-9 rounded-md border border-line/60 flex items-center justify-center text-ink/60 hover:border-gold hover:text-gold active:scale-90 transition-all duration-200">+</button>
                        </div>
                    </div>

                    {{-- Metode Pengiriman (mode beli) --}}
                    <div x-show="mode === 'beli'" x-cloak class="pd-swap mb-6">
                        <p class="text-sm font-semibold text-ink/85 mb-2">Metode Pengiriman</p>
                        <div class="space-y-2">
                            <label class="block cursor-pointer">
                                <input type="radio" name="metode" value="ambil" class="peer sr-only"
                                    x-model="metodeKirim">
                                <div
                                    class="flex items-start gap-2 p-3 rounded-lg border border-line/50 hover:border-gold/60 peer-checked:border-gold peer-checked:bg-gold/5 transition-all duration-300">
                                    <div class="flex-1">
                                        <p class="text-[13px] font-semibold text-ink/85">Ambil di Toko</p>
                                        <p class="text-xs text-ink/55">Gratis, tanpa ongkos kirim.</p>
                                    </div>
                                </div>
                            </label>
                            <label class="block cursor-pointer">
                                <input type="radio" name="metode" value="kirim" class="peer sr-only"
                                    x-model="metodeKirim">
                                <div
                                    class="flex items-start gap-2 p-3 rounded-lg border border-line/50 hover:border-gold/60 peer-checked:border-gold peer-checked:bg-gold/5 transition-all duration-300">
                                    <div class="flex-1">
                                        <p class="text-[13px] font-semibold text-ink/85">Dikirim ke Alamat</p>
                                        <p class="text-xs text-ink/55">Ongkir dihitung otomatis saat checkout.</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Tombol aksi --}}
                    <div class="pd-up grid grid-cols-1 sm:grid-cols-2 gap-2.5" style="--d: .4s;">
                        {{-- Tombol Sewa --}}
                        <div x-show="mode === 'sewa'" x-cloak>
                            <form method="POST" action="{{ route('sewa.simpan') }}" x-ref="formSewa">
                                @csrf
                                <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                <input type="hidden" name="tanggal_ambil" :value="tanggalAmbil">
                                <input type="hidden" name="tanggal_kembali" :value="tanggalKembali">
                                <input type="hidden" name="ukuran" :value="ukuran">
                                <input type="hidden" name="durasi" :value="durasi">
                                <input type="hidden" name="total_sewa" :value="totalSewa">
                                <button type="submit" @click.prevent="kirimSewa()"
                                    :class="{ 'pd-ready': tanggalAmbil && tanggalKembali }"
                                    class="pd-shine w-full h-[46px] rounded-lg bg-gold text-white text-[13px] font-semibold tracking-wide hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[0.98] transition-all duration-300">
                                    Sewa Sekarang
                                </button>
                            </form>
                        </div>

                        {{-- Tombol Beli --}}
                        <div x-show="mode === 'beli'" x-cloak>
                            <form method="POST" action="{{ route('beli.simpan') }}" x-ref="formBeli">
                                @csrf
                                <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                <input type="hidden" name="qty" :value="jumlah">
                                <input type="hidden" name="varian" value="Emas Klasik">
                                <button type="submit"
                                    class="pd-shine w-full h-[46px] rounded-lg bg-gold text-white text-[13px] font-semibold tracking-wide hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[0.98] transition-all duration-300">
                                    Beli Sekarang (<span x-text="formatRupiah(totalBeli)"></span>)
                                </button>
                            </form>
                        </div>

                        {{-- Tombol Keranjang --}}
                        <button type="button"
                            class="w-full h-[46px] rounded-lg border border-ink/80 bg-white text-ink/85 text-[13px] font-semibold tracking-wide hover:bg-ink/90 hover:text-cream hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 active:scale-[0.98] transition-all duration-300">
                            Tambah ke Keranjang
                        </button>
                    </div>

                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya tertarik dengan produk ' . $produk->nama) }}"
                        target="_blank" rel="noopener"
                        class="pd-up group mt-4 flex items-center justify-center gap-2 w-full py-3 px-3 rounded-lg border border-dashed border-emerald-600/40 text-xs font-semibold text-emerald-800 text-center hover:bg-emerald-50 hover:border-emerald-600/70 hover:-translate-y-0.5 transition-all duration-300"
                        style="--d: .45s;">
                        <img src="{{ asset('images/wired-outline-2543-logo-whatsapp.svg') }}" alt="WhatsApp"
                            class="pd-wa-icon w-5 h-5 shrink-0">
                        <span>Konsultasi Produk Ini via WhatsApp</span>
                    </a>
                </div>
            </div>

            {{-- Produk Serupa --}}
            @if ($serupa->count() > 0)
                <div class="mt-14">
                    <div class="pd-reveal">
                        <x-section-heading title="Produk Serupa"
                            subtitle="Pilihan lain dari kategori {{ $produk->kategori->nama_kategori }}" />
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                        @foreach ($serupa as $i => $s)
                            <div class="pd-reveal h-full" style="--d: {{ $i * 0.08 }}s;">
                                <x-product-card :produk="$s" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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
                dibooking: cfg.dibooking,
                ukuran: cfg.ukuranDefault,
                tanggalAmbil: null,
                tanggalKembali: null,
                varianWarna: 'Emas Klasik',
                jumlah: 1,
                metodeKirim: 'ambil',
                tahun: now.getFullYear(),
                bulan: now.getMonth(),
                hariIni: iso(now.getFullYear(), now.getMonth(), now.getDate()),
                namaHari: ['S', 'S', 'R', 'K', 'J', 'S', 'M'],

                // --- state animasi / UI ---
                fotoAktif: 0,
                gantiBulan: false,
                pesanTanggal: '',
                getar: false,
                hargaTampil: cfg.hargaSewa,
                _anim: null,

                // Alpine memanggil init() otomatis
                init() {
                    this.$watch('totalSewa', (v) => this.tweenHarga(v || this.hargaSewaPerHari));
                    this.$watch('tanggalKembali', (v) => { if (v) this.pesanTanggal = ''; });
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

                // Kirim form sewa, dengan pesan inline (bukan alert)
                kirimSewa() {
                    if (!this.tanggalAmbil || !this.tanggalKembali) {
                        this.pesanTanggal = 'Pilih tanggal ambil dan tanggal kembali dulu ya.';
                        this.getar = false;
                        this.$nextTick(() => {
                            this.getar = true;
                            setTimeout(() => { this.getar = false; }, 500);
                        });
                        const el = document.getElementById('kalender-sewa');
                        if (el) el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
                        return;
                    }
                    this.pesanTanggal = '';
                    this.$refs.formSewa.submit();
                },

                // Ganti bulan dengan fade singkat
                transisiBulan(fn) {
                    if (reduce) { fn(); return; }
                    this.gantiBulan = true;
                    setTimeout(() => { fn(); this.gantiBulan = false; }, 150);
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
                    });
                },
                bulanBerikutnya() {
                    this.transisiBulan(() => {
                        if (this.bulan === 11) {
                            this.bulan = 0;
                            this.tahun++;
                        } else {
                            this.bulan++;
                        }
                    });
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
                    if (this.isSelected(c.t)) return 'bg-ink/90 text-gold font-semibold scale-105 shadow-sm';
                    if (this.isInRange(c.t)) return 'bg-gold/15 text-ink/85 font-medium';
                    if (c.luar || this.lalu(c.t)) return 'text-ink/25 cursor-default';
                    if (this.penuh(c.t)) return 'bg-rose-50 text-rose-400 cursor-not-allowed';
                    return 'text-ink/70 hover:bg-cream hover:text-ink hover:scale-110 active:scale-95';
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