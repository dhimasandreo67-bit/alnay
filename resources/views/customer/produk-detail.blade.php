@extends('layouts.public')

@section('title', $produk->nama . ' — ' . config('alnay.brand.nama'))

@section('content')

<script src="https://cdn.lordicon.com/lordicon.js"></script>

@php
    $statusLabel = \App\Support\ProdukStatus::label($produk->status, $produk->dapat_dijual);

    $ukuranList = array_values(array_filter(array_map('trim', explode(',', (string) $produk->ukuran))));
    if (empty($ukuranList)) {
        $ukuranList = ['S', 'M', 'L', 'XL', 'Custom'];
    }

    $paragrafDeskripsi = preg_split('/\R{2,}/', trim((string) $produk->deskripsi)) ?: [];
    $jumlahDisewa = $produk->getAttributes()['jumlah_disewa'] ?? null;

    $config = [
        'mode'          => (request('mode') === 'beli' && $produk->dapat_dijual) ? 'beli' : 'sewa',
        'hargaSewa'     => (float) ($produk->harga_sewa ?? 0),
        'dendaPerHari'  => (float) ($produk->denda_per_hari_telat ?? 0),
        'hargaJual'     => (float) ($produk->harga_jual ?? 0),
        'ukuran'        => $ukuranList,
        'ukuranDefault' => in_array('M', $ukuranList, true) ? 'M' : $ukuranList[0],
        'dibooking'     => array_values($dibooking ?? []),
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
            <div class="animate-fade-up">

                {{-- Gambar utama --}}
                <div class="relative aspect-[4/5] rounded-2xl overflow-hidden border border-line/40 shadow-sm"
                     style="background: linear-gradient(135deg,#3a2a12,#1b130a);">
                    @if ($produk->foto_produk)
                        <img src="{{ asset('storage/' . $produk->foto_produk) }}" alt="{{ $produk->nama }}" class="w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-8">
                            <h2 class="font-display text-xl md:text-2xl text-[#e3b64f] leading-snug">{{ $produk->nama }}</h2>
                            <p class="mt-2 text-xs text-white/80">{{ $produk->kategori->nama_kategori }}</p>
                        </div>
                    @endif

                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-white/95 text-ink/80 text-[10px] font-medium tracking-wide">
                        {{ $statusLabel }}
                    </span>
                </div>

                {{-- Thumbnail --}}
                <div class="flex gap-2 mt-3">
                    @foreach ($thumbGradien as $i => $gradien)
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-lg border-2 {{ $i === 0 ? 'border-gold/60' : 'border-transparent' }} transition-all duration-300"
                             style="background: {{ $gradien }};"></div>
                    @endforeach
                </div>

                {{-- Deskripsi --}}
                <div class="mt-8 md:mt-10">
                    <h2 class="font-display text-lg font-semibold text-ink/90 mb-3">Deskripsi Produk</h2>

                    <div class="space-y-3 text-[13px] text-ink/60 leading-relaxed">
                        @foreach ($paragrafDeskripsi as $paragraf)
                            @if (trim($paragraf) !== '')
                                <p>{{ preg_replace('/\s*\R\s*/', ' ', trim($paragraf)) }}</p>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-5">
                        <div class="flex justify-between gap-4 py-3 text-[12px] border-b border-line/40">
                            <span class="text-ink/50">Kategori</span>
                            <span class="text-ink/80 font-medium text-right">{{ $produk->kategori->nama_kategori }}</span>
                        </div>
                        <div class="flex justify-between gap-4 py-3 text-[12px] border-b border-line/40">
                            <span class="text-ink/50">Material</span>
                            <span class="text-ink/80 font-medium text-right">{{ $produk->bahan }}</span>
                        </div>
                        <div class="flex justify-between gap-4 py-3 text-[12px] border-b border-line/40">
                            <span class="text-ink/50">Ukuran Tersedia</span>
                            <span class="text-ink/80 font-medium text-right">{{ $produk->ukuran }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================== KOLOM KANAN ==================== --}}
            <div class="animate-fade-up" style="animation-delay: 0.15s;">

                <span class="inline-block px-2 py-0.5 rounded-md bg-ink/90 text-gold text-[9px] font-medium tracking-wider">
                    {{ $produk->dapat_dijual ? 'Sewa & Beli' : 'Khusus Sewa' }}
                </span>

                <h1 class="font-display text-2xl lg:text-3xl font-semibold text-ink/90 leading-tight mt-3 mb-2">{{ $produk->nama }}</h1>

                {{-- Rating --}}
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-4 text-[11px] text-ink/50">
                    <div class="flex items-center gap-1.5">
                        <div class="flex text-gold/90">
                            @for ($i = 1; $i <= 5; $i++)
                                <x-icon name="{{ $i <= round($produk->rating_rata_rata) ? 'star' : 'star-outline' }}" :size="11" />
                            @endfor
                        </div>
                        <span class="font-medium text-ink/80">{{ number_format($produk->rating_rata_rata, 1) }}</span>
                        <span>({{ $produk->jumlah_rating }} ulasan)</span>
                    </div>
                    @if ($jumlahDisewa)
                        <span class="hidden sm:block w-px h-3 bg-line/50"></span>
                        <span>{{ number_format($jumlahDisewa, 0, ',', '.') }}+ kali disewa</span>
                    @endif
                </div>

                {{-- Switch Sewa / Beli --}}
                @if ($produk->dapat_dijual)
                    <div class="grid grid-cols-2 border border-line/40 rounded-lg overflow-hidden bg-white/60 mb-5">
                        <button type="button" @click="mode = 'sewa'"
                                :class="mode === 'sewa' ? 'bg-ink/90 text-gold' : 'text-ink/50 hover:text-ink/80'"
                                class="h-[38px] text-[10px] font-medium tracking-wider transition-all duration-300">Sewa Busana</button>
                        <button type="button" @click="mode = 'beli'"
                                :class="mode === 'beli' ? 'bg-ink/90 text-gold' : 'text-ink/50 hover:text-ink/80'"
                                class="h-[38px] text-[10px] font-medium tracking-wider transition-all duration-300">Beli Baru</button>
                    </div>
                @endif

                {{-- ===== MODE SEWA ===== --}}
                <div x-show="mode === 'sewa'" x-transition>

                    {{-- Harga & penjelasan --}}
                    <div class="bg-cream/70 rounded-xl p-4 mb-6 border border-line/30">
                        <p class="font-display text-[22px] font-medium text-ink leading-none lining-nums tabular-nums">
                            <span x-text="formatRupiah(totalSewa || hargaSewaPerHari)"></span>
                            <span class="font-sans text-[10px] font-normal text-ink/40">/ <span x-text="durasi > 0 ? durasi + ' hari' : 'sewa'"></span></span>
                        </p>

                        <div class="mt-3 text-[11px] text-ink/50 leading-relaxed">
                            <p class="font-medium text-ink/70 mb-1">Jadwal Penyewaan:</p>
                            <p>Penyewaan memiliki 3 tahap utama: pengambilan, pemakaian, dan pengembalian. Contoh normal: diambil hari Senin, dipakai untuk acara hari Selasa, dikembalikan hari Rabu.</p>
                        </div>

                        <div class="mt-3 p-3 rounded-md border border-dashed border-gold/40 text-[10px] text-ink/50 leading-relaxed bg-gold/[0.03]">
                            Ingin pemakaian lebih dari 1 hari? Tidak perlu bayar harga sewa penuh lagi — cukup tambahan
                            <strong class="text-gold/90 font-semibold">Rp{{ number_format($produk->denda_per_hari_telat, 0, ',', '.') }} per hari</strong>.
                        </div>
                    </div>

                    {{-- Kalender --}}
                    <div class="mb-6">
                        <p class="text-[11px] font-medium text-ink/80 mb-2">Tanggal Sewa</p>

                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-semibold text-ink/80 capitalize" x-text="namaBulan"></span>
                            <div class="flex gap-1">
                                <button type="button" @click="bulanSebelumnya()" :disabled="!bisaMundur" aria-label="Bulan sebelumnya"
                                        class="w-6 h-6 rounded-md border border-line/50 bg-white flex items-center justify-center text-ink/40 hover:border-gold/60 hover:text-gold/80 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-300">
                                    <x-icon name="chevron-right" :size="10" class="rotate-180" />
                                </button>
                                <button type="button" @click="bulanBerikutnya()" aria-label="Bulan berikutnya"
                                        class="w-6 h-6 rounded-md border border-line/50 bg-white flex items-center justify-center text-ink/40 hover:border-gold/60 hover:text-gold/80 transition-all duration-300">
                                    <x-icon name="chevron-right" :size="10" />
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-7 gap-0.5 text-center">
                            <template x-for="(h, idx) in namaHari" :key="idx">
                                <div class="text-[9px] text-ink/40 py-1 font-medium" x-text="h"></div>
                            </template>

                            <template x-for="c in sel" :key="c.t">
                                <button type="button" @click="pilihTanggal(c.t)" :disabled="c.luar || nonaktif(c.t)"
                                        :class="kelas(c)"
                                        class="h-7 sm:h-8 mx-0.5 rounded-md text-[10px] font-medium transition-all duration-300 ease-out">
                                    <span x-text="c.hari"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Legend --}}
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-3 text-[9px] text-ink/40">
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-ink/80"></span> Dipilih</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gold/30"></span> Rentang sewa</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-200/80"></span> Sudah dibooking</div>
                        </div>

                        {{-- Ringkasan tanggal --}}
                        <div class="mt-3 grid grid-cols-3 gap-2 bg-cream/60 rounded-lg px-3 py-2.5 text-[10px] border border-line/30">
                            <div>
                                <p class="text-[9px] text-ink/40">Ambil / Diantar</p>
                                <p class="font-semibold text-ink/80 mt-0.5" x-text="tanggalAmbil ? formatTanggal(tanggalAmbil) : 'Klik tanggal'"></p>
                            </div>
                            <div class="text-center">
                                <p class="text-[9px] text-ink/40">Sewa</p>
                                <p class="font-semibold text-ink/80 mt-0.5" x-text="durasi > 0 ? durasi + ' hari' : '—'"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-ink/40">Kembali</p>
                                <p class="font-semibold text-ink/80 mt-0.5" x-text="tanggalKembali ? formatTanggal(tanggalKembali) : '—'"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== MODE BELI ===== --}}
                <div x-show="mode === 'beli'" x-transition>
                    <div class="bg-cream/70 rounded-xl p-4 mb-6 border border-line/30">
                        <p class="text-[9px] text-ink/40 mb-1">Harga Beli</p>
                        <p class="font-display text-[22px] font-semibold text-ink/90 leading-none lining-nums tabular-nums">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                        <p class="mt-2 text-[10px] text-ink/50">Stok tersedia: <strong class="text-ink/80 font-medium">{{ $produk->stok }} set</strong></p>
                    </div>

                    {{-- Varian Warna --}}
                    <div class="mb-6">
                        <p class="text-[11px] font-medium text-ink/80 mb-2">Varian Warna & Motif</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            @foreach ([['Emas Klasik', 'bg-[#c99a32]', 'Tersedia'], ['Merah Marun', 'bg-[#6b1c2a]', 'Tersedia'], ['Hitam Polos', 'bg-[#1a1a1a]', 'Tersedia'], ['Motif Songket', 'bg-[#a39a8e]', 'Stok Habis'], ['Biru Navy', 'bg-[#2a3f5c]', 'Stok Habis']] as $v)
                                <label class="cursor-pointer">
                                    <input type="radio" name="warna" value="{{ $v[0] }}" class="peer sr-only" {{ $v[2] === 'Stok Habis' ? 'disabled' : '' }}>
                                    <div class="flex items-center gap-2 px-2.5 py-2 rounded-lg border border-line/50 text-[10px] {{ $v[2] === 'Stok Habis' ? 'opacity-40' : 'hover:border-gold/50' }} peer-checked:border-gold/70 peer-checked:bg-gold/5 transition-all duration-300">
                                        <span class="w-3.5 h-3.5 rounded-full {{ $v[1] }} shrink-0"></span>
                                        <div class="flex-1">
                                            <p class="font-medium text-ink/80">{{ $v[0] }}</p>
                                            <p class="text-[8px] {{ $v[2] === 'Stok Habis' ? 'text-rose-400/80' : 'text-emerald-600/70' }}">{{ $v[2] }}</p>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Ukuran --}}
                <div class="mb-6">
                    <p class="text-[11px] font-medium text-ink/80 mb-2">Ukuran</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($ukuranList as $u)
                            <button type="button" @click="ukuran = @js($u)"
                                    :class="ukuran === @js($u) ? 'bg-ink/90 text-gold border-ink/90' : 'bg-white text-ink/60 border-line/50 hover:border-gold/50 hover:text-ink/80'"
                                    class="min-w-[36px] h-[32px] px-2.5 rounded-md border text-[10px] font-medium transition-all duration-300">{{ $u }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Jumlah (mode beli) --}}
                <div x-show="mode === 'beli'" class="mb-6">
                    <p class="text-[11px] font-medium text-ink/80 mb-2">Jumlah</p>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="jumlah = Math.max(1, jumlah - 1)" class="w-8 h-8 rounded-md border border-line/50 flex items-center justify-center text-ink/50 hover:border-gold/50 hover:text-gold/80 transition-all duration-300">−</button>
                        <span class="w-8 text-center text-[12px] font-medium text-ink/80" x-text="jumlah"></span>
                        <button type="button" @click="jumlah++" class="w-8 h-8 rounded-md border border-line/50 flex items-center justify-center text-ink/50 hover:border-gold/50 hover:text-gold/80 transition-all duration-300">+</button>
                    </div>
                </div>

                {{-- Metode Pengiriman (mode beli) --}}
                <div x-show="mode === 'beli'" class="mb-6">
                    <p class="text-[11px] font-medium text-ink/80 mb-2">Metode Pengiriman</p>
                    <div class="space-y-1.5">
                        <label class="block cursor-pointer">
                            <input type="radio" name="metode" value="ambil" class="peer sr-only" x-model="metodeKirim">
                            <div class="flex items-start gap-2 p-2.5 rounded-lg border border-line/50 peer-checked:border-gold/60 peer-checked:bg-gold/5 transition-all duration-300">
                                <div class="flex-1">
                                    <p class="text-[11px] font-medium text-ink/80">Ambil di Toko</p>
                                    <p class="text-[9px] text-ink/40">Gratis, tanpa ongkos kirim.</p>
                                </div>
                            </div>
                        </label>
                        <label class="block cursor-pointer">
                            <input type="radio" name="metode" value="kirim" class="peer sr-only" x-model="metodeKirim">
                            <div class="flex items-start gap-2 p-2.5 rounded-lg border border-line/50 peer-checked:border-gold/60 peer-checked:bg-gold/5 transition-all duration-300">
                                <div class="flex-1">
                                    <p class="text-[11px] font-medium text-ink/80">Dikirim ke Alamat</p>
                                    <p class="text-[9px] text-ink/40">Ongkir dihitung otomatis saat checkout.</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Tombol aksi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {{-- Tombol Sewa --}}
                    <div x-show="mode === 'sewa'" x-transition>
                        <form method="POST" action="{{ route('sewa.simpan') }}" x-ref="formSewa">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <input type="hidden" name="tanggal_ambil" :value="tanggalAmbil">
                            <input type="hidden" name="tanggal_kembali" :value="tanggalKembali">
                            <input type="hidden" name="ukuran" :value="ukuran">
                            <input type="hidden" name="durasi" :value="durasi">
                            <input type="hidden" name="total_sewa" :value="totalSewa">
                            <button type="submit"
                                    @click.prevent="if(!tanggalAmbil || !tanggalKembali) { alert('Pilih tanggal ambil dan kembali.'); return; } $refs.formSewa.submit();"
                                    class="w-full h-[42px] rounded-lg bg-gold/95 text-white text-[10px] font-medium tracking-wider hover:bg-gold transition-all duration-300">
                                Sewa Sekarang
                            </button>
                        </form>
                    </div>

                    {{-- Tombol Beli --}}
                    <div x-show="mode === 'beli'" x-transition>
                        <button type="button"
                                class="w-full h-[42px] rounded-lg bg-gold/95 text-white text-[10px] font-medium tracking-wider hover:bg-gold transition-all duration-300">
                            Beli Sekarang (<span x-text="formatRupiah(totalBeli)"></span>)
                        </button>
                    </div>

                    {{-- Tombol Keranjang --}}
                    <button type="button"
                            class="w-full h-[42px] rounded-lg border border-ink/80 bg-white text-ink/80 text-[10px] font-medium tracking-wider hover:bg-ink/90 hover:text-cream transition-all duration-300">
                        Tambah ke Keranjang
                    </button>
                </div>
                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya tertarik dengan produk ' . $produk->nama) }}"
                target="_blank" rel="noopener"
                class="mt-4 flex items-center justify-center gap-2 w-full py-3 px-3 rounded-lg border border-dashed border-line/50 text-[10px] font-medium text-emerald-700/80 hover:bg-cream/50 hover:border-emerald-600/30 transition-all duration-300 whitespace-nowrap">

                    <img src="{{ asset('images/wired-outline-2543-logo-whatsapp.svg') }}"
                        alt="WhatsApp"
                        class="w-4 h-4 shrink-0">

                    Konsultasi Produk Ini via WhatsApp
                </a>
            </div>
        </div>

        {{-- Produk Serupa --}}
        @if ($serupa->count() > 0)
            <div class="mt-12">
                <x-section-heading title="Produk Serupa" subtitle="Pilihan lain dari kategori {{ $produk->kategori->nama_kategori }}" />
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                    @foreach ($serupa as $s)
                        <x-product-card :produk="$s" />
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
        get namaBulan() { return new Date(Date.UTC(this.tahun, this.bulan, 1)).toLocaleDateString('id-ID', { month: 'long', year: 'numeric', timeZone: 'UTC' }); },
        get bisaMundur() { return this.tahun > now.getFullYear() || (this.tahun === now.getFullYear() && this.bulan > now.getMonth()); },
        get sel() {
            const pertama = new Date(Date.UTC(this.tahun, this.bulan, 1));
            const offset = (pertama.getUTCDay() + 6) % 7;
            const cells = [];
            for (let i = 0; i < 42; i++) {
                const d = new Date(Date.UTC(this.tahun, this.bulan, 1 - offset + i));
                cells.push({ t: iso(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()), hari: d.getUTCDate(), luar: d.getUTCMonth() !== this.bulan });
            }
            return cells.slice(35).every(c => c.luar) ? cells.slice(0, 35) : cells;
        },
        bulanSebelumnya() { if (!this.bisaMundur) return; if (this.bulan === 0) { this.bulan = 11; this.tahun--; } else { this.bulan--; } },
        bulanBerikutnya() { if (this.bulan === 11) { this.bulan = 0; this.tahun++; } else { this.bulan++; } },
        lalu(t) { return t < this.hariIni; },
        penuh(t) { return this.dibooking.includes(t); },
        nonaktif(t) { return this.lalu(t) || this.penuh(t); },
        kelas(c) {
            if (this.isSelected(c.t)) return 'bg-ink/90 text-gold font-medium';
            if (this.isInRange(c.t)) return 'bg-gold/10 text-ink/80 font-medium';
            if (c.luar || this.lalu(c.t)) return 'text-ink/20 cursor-default';
            if (this.penuh(c.t)) return 'bg-rose-50 text-rose-300/80 cursor-not-allowed';
            return 'text-ink/60 hover:bg-cream/60';
        },
        pilihTanggal(t) {
            if (this.nonaktif(t)) return;
            if (!this.tanggalAmbil || this.tanggalKembali || t <= this.tanggalAmbil) { this.tanggalAmbil = t; this.tanggalKembali = null; return; }
            if (this.dibooking.some(b => b > this.tanggalAmbil && b < t)) { this.tanggalAmbil = t; this.tanggalKembali = null; return; }
            this.tanggalKembali = t;
        },
        isSelected(t) { return this.tanggalAmbil === t || this.tanggalKembali === t; },
        isInRange(t) { if (!this.tanggalAmbil || !this.tanggalKembali) return false; return t > this.tanggalAmbil && t < this.tanggalKembali; },
        get durasi() { if (!this.tanggalAmbil || !this.tanggalKembali) return 0; const a = new Date(this.tanggalAmbil); const b = new Date(this.tanggalKembali); return Math.max(0, Math.ceil((b - a) / (1000 * 60 * 60 * 24))); },
        get totalSewa() { if (this.durasi === 0) return 0; return this.hargaSewaPerHari + (Math.max(0, this.durasi - 1) * this.dendaPerHari); },
        get totalBeli() { return this.hargaJual * this.jumlah; },
        formatRupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); },
        formatTanggal(t) { if (!t) return '—'; return new Date(t).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' }); },
    };
}
</script>

@endsection