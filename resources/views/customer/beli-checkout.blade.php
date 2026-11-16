@extends('layouts.public')

@section('title', 'Checkout Pembelian — ' . config('alnay.brand.nama'))

@section('content')

    <style>
        [x-cloak] { display: none !important; }
        @keyframes pd-up { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @keyframes pd-swap { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        @keyframes pd-shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
        @keyframes pd-spin { to { transform: rotate(360deg); } }

        .pd-up { animation: pd-up .6s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
        .pd-swap { animation: pd-swap .4s ease-out both; }
        .pd-shake { animation: pd-shake .45s ease; }
        .pd-spin { animation: pd-spin .8s linear infinite; }

        .pd-shine { position: relative; overflow: hidden; }
        .pd-shine::after { content: ''; position: absolute; inset: 0; background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%); transform: translateX(-120%); transition: transform .8s ease; pointer-events: none; }
        .pd-shine:hover::after { transform: translateX(120%); }

        /* Custom Radio Card */
        .opsi-radio:checked + .opsi-card {
            border-color: var(--color-gold, #ba8320);
            background-color: rgba(186, 131, 32, 0.05);
            box-shadow: 0 4px 14px -4px rgba(186, 131, 32, 0.16);
        }
        .opsi-radio:checked + .opsi-card .radio-dot {
            background: var(--color-gold, #ba8320);
            border-color: var(--color-gold, #ba8320);
            box-shadow: inset 0 0 0 2.5px #fff;
        }
        .opsi-radio:focus-visible + .opsi-card { outline: 2px solid var(--color-gold, #ba8320); outline-offset: 2px; }
        .opsi-radio:disabled + .opsi-card { opacity: .45; cursor: not-allowed; transform: none; box-shadow: none; }

        /* ===== Alur Checkout (Stepper) ===== */
        .ck-step .ck-dot {
            background: #fff;
            border: 2px solid rgba(26, 23, 20, 0.16);
            color: rgba(26, 23, 20, 0.4);
            transition: all .3s ease;
        }
        .ck-step .ck-label {
            color: rgba(26, 23, 20, 0.45);
            transition: color .3s ease;
        }
        .ck-step .ck-num { display: inline; }
        .ck-step .ck-check { display: none; }

        .ck-step[data-state="active"] .ck-dot {
            border-color: var(--color-gold, #ba8320);
            background: #fffdf8;
            color: var(--color-gold, #ba8320);
            box-shadow: 0 0 0 4px rgba(186, 131, 32, 0.15);
        }
        .ck-step[data-state="active"] .ck-label {
            color: var(--color-ink, #1a1714);
            font-weight: 700;
        }

        .ck-step[data-state="done"] .ck-dot {
            background: var(--color-gold, #ba8320);
            border-color: var(--color-gold, #ba8320);
            color: #fff;
        }
        .ck-step[data-state="done"] .ck-label {
            color: var(--color-ink, #1a1714);
        }
        .ck-step[data-state="done"] .ck-num { display: none; }
        .ck-step[data-state="done"] .ck-check { display: inline; }

        .ck-line {
            background: rgba(26, 23, 20, 0.12);
            position: relative;
            overflow: hidden;
        }
        .ck-line::after {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--color-gold, #ba8320);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .45s ease;
        }
        .ck-line[data-full="1"]::after { transform: scaleX(1); }

        .ck-sec { scroll-margin-top: 6.5rem; transition: border-color .3s ease; }
        .ck-sec:focus-within { border-color: rgba(186, 131, 32, 0.55); }

        @media (prefers-reduced-motion: reduce) {
            .pd-up, .pd-swap, .pd-shake { animation: none !important; }
            .pd-shine::after, .ck-step .ck-dot, .ck-line::after { transition: none !important; }
        }
    </style>

    @php
        $user = auth()->user();
        $fotoCheckout = $produk->foto_produk;
        if (is_array($fotoCheckout)) { $fotoCheckout = $fotoCheckout[0] ?? null; }

        $ukuranCheckout = $beliData['ukuran'] ?? null;
        if (is_array($ukuranCheckout)) { $ukuranCheckout = implode(', ', $ukuranCheckout); }

        $spesifikasiCheckout = collect([$ukuranCheckout ? 'Ukuran ' . $ukuranCheckout : null, $varian ? 'Warna ' . $varian : null])->filter()->implode(' · ');
        $diskonJualCheckout = $produk->diskonJual();

        $metodeAwal = old('metode_penerimaan', 'kirim');
        $alamatToko = config('alnay.toko.alamat', 'Jl. Terusan Rajabasa No.11, Bandar Lampung');

        $cfg = [
            'metode' => $metodeAwal,
            'bayar' => old('metode_bayar', 'qris'),
            'nama' => old('nama_penerima', $alamatUtama->nama_penerima ?? $user->name ?? ''),
            'wa' => old('no_wa', $alamatUtama->no_hp ?? $user->no_hp ?? ''),
            'alamat' => old('alamat', $alamatUtama->alamat ?? $user->alamat ?? ''),
            'catatan' => old('catatan', ''),
            'setuju' => (bool) old('setuju'),
            'total' => (float) $total,
        ];
    @endphp

    <div class="bg-[#FBFAF7] min-h-screen py-8 md:py-11 pb-32 lg:pb-12 font-sans" x-data="checkoutBeli(@js($cfg))" @pageshow.window="sedangKirim = false">
        <div class="max-w-[1180px] mx-auto px-4 md:px-6">

            {{-- Header Halaman & Breadcrumb --}}
            <div class="pd-up mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <a href="{{ route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli']) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-medium text-ink/55 hover:text-ink transition-colors mb-2">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Kembali ke Detail Produk
                    </a>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="font-display text-2xl sm:text-3xl font-semibold text-ink">Checkout Pembelian</h1>
                        <span class="px-2.5 py-0.5 rounded-full bg-gold/15 text-gold text-[11px] font-bold tracking-wide uppercase">Hak Milik</span>
                    </div>
                    <p class="text-xs sm:text-[13px] text-ink/60 mt-1">Ikuti 4 langkah singkat berikut untuk menyelesaikan pembelian Anda.</p>
                </div>

                <div class="inline-flex items-center gap-2 self-start sm:self-auto rounded-full bg-panel border border-line/50 px-3.5 py-1.5 text-xs text-ink/70">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>
                    </svg>
                    <span>Pembayaran Terverifikasi Otomatis</span>
                </div>
            </div>

            {{-- ALUR CHECKOUT (4 LANGKAH) --}}
            <nav aria-label="Alur checkout" class="pd-up mb-7 bg-panel border border-line/50 rounded-2xl px-3 sm:px-8 py-4 shadow-2xs" style="--d: .03s;">
                <ol class="flex items-start justify-between">
                    <li class="ck-step flex flex-col items-center text-center w-16 sm:w-28 shrink-0" :data-state="stepState(1)">
                        <a href="#sec-item-terima" class="flex flex-col items-center gap-1.5">
                            <span class="ck-dot w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                <span class="ck-num">1</span>
                                <svg class="ck-check w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="ck-label text-[11px] sm:text-xs font-semibold leading-tight">Penerimaan</span>
                        </a>
                    </li>

                    <li class="ck-line flex-1 h-0.5 mt-4 sm:mt-[18px] rounded-full" :data-full="stepAktif > 1 ? '1' : '0'" aria-hidden="true"></li>

                    <li class="ck-step flex flex-col items-center text-center w-16 sm:w-28 shrink-0" :data-state="stepState(2)">
                        <a href="#sec-data-kirim" class="flex flex-col items-center gap-1.5">
                            <span class="ck-dot w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                <span class="ck-num">2</span>
                                <svg class="ck-check w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="ck-label text-[11px] sm:text-xs font-semibold leading-tight" x-text="kirim ? 'Alamat & Kurir' : 'Data Pengambil'">Alamat &amp; Kurir</span>
                        </a>
                    </li>

                    <li class="ck-line flex-1 h-0.5 mt-4 sm:mt-[18px] rounded-full" :data-full="stepAktif > 2 ? '1' : '0'" aria-hidden="true"></li>

                    <li class="ck-step flex flex-col items-center text-center w-16 sm:w-28 shrink-0" :data-state="stepState(3)">
                        <a href="#sec-pembayaran" class="flex flex-col items-center gap-1.5">
                            <span class="ck-dot w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                <span class="ck-num">3</span>
                                <svg class="ck-check w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="ck-label text-[11px] sm:text-xs font-semibold leading-tight">Pembayaran</span>
                        </a>
                    </li>

                    <li class="ck-line flex-1 h-0.5 mt-4 sm:mt-[18px] rounded-full" :data-full="stepAktif > 3 ? '1' : '0'" aria-hidden="true"></li>

                    <li class="ck-step flex flex-col items-center text-center w-16 sm:w-28 shrink-0" :data-state="stepState(4)">
                        <a href="#sec-konfirmasi" class="flex flex-col items-center gap-1.5">
                            <span class="ck-dot w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                <span class="ck-num">4</span>
                                <svg class="ck-check w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="ck-label text-[11px] sm:text-xs font-semibold leading-tight">Konfirmasi</span>
                        </a>
                    </li>
                </ol>
            </nav>

            @if ($errors->any())
                <div class="pd-up p-4 mb-6 rounded-xl bg-rose-50 border border-rose-200 text-[13px] text-rose-700 space-y-1">
                    <p class="font-semibold">Mohon periksa kembali data pesanan Anda:</p>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('beli.store') }}" novalidate x-ref="formCheckout" @submit.prevent="kirimForm()">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 lg:gap-8 items-start">

                    {{-- ==========================================================
                         KOLOM KIRI: LANGKAH 1 - 3
                    ========================================================== --}}
                    <div class="space-y-5">

                        {{-- LANGKAH 1: PRODUK DIBELI & METODE PENERIMAAN --}}
                        <div id="sec-item-terima" class="ck-sec pd-up bg-panel border border-line/50 rounded-2xl p-5 sm:p-6 shadow-2xs" style="--d: .06s;">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-full bg-ink text-cream flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</span>
                                    <div>
                                        <h2 class="font-display text-lg font-medium text-ink">Produk &amp; Metode Penerimaan</h2>
                                        <p class="text-xs text-ink/55 mt-0.5">Pastikan spesifikasi produk sesuai, lalu pilih cara penerimaan.</p>
                                    </div>
                                </div>
                                <span class="hidden sm:inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">✓ Dipilih</span>
                            </div>

                            {{-- Kartu Produk --}}
                            <div class="flex gap-3.5 items-center rounded-xl bg-[#FBFAF7] border border-line/40 p-3.5 mb-5">
                                <div class="w-16 h-16 rounded-lg bg-gold/15 border border-gold/25 overflow-hidden flex items-center justify-center shrink-0">
                                    @if ($fotoCheckout)
                                        <img src="{{ asset('storage/' . $fotoCheckout) }}" alt="{{ $produk->nama }}" class="w-full h-full object-cover">
                                    @else
                                        <x-icon name="sparkle" :size="20" class="text-gold/60" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-[15px] font-semibold text-ink/90 leading-snug truncate">{{ $produk->nama }}</p>
                                        <a href="{{ route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli']) }}" class="shrink-0 text-xs font-semibold text-gold hover:underline underline-offset-2">Ubah</a>
                                    </div>
                                    @if ($spesifikasiCheckout) <p class="text-xs text-ink/60 mt-0.5">{{ $spesifikasiCheckout }}</p> @endif
                                    <div class="flex items-center justify-between gap-2 mt-1.5">
                                        <p class="text-xs text-ink/65">
                                            @if ($diskonJualCheckout > 0) <span class="line-through text-ink/40">Rp{{ number_format($produk->harga_jual, 0, ',', '.') }}</span> @endif
                                            <span class="font-medium text-ink/85">Rp{{ number_format($produk->hargaJualNet(), 0, ',', '.') }}</span> / unit
                                        </p>
                                        <span class="px-2.5 py-0.5 rounded-md bg-cream border border-line/40 text-[11px] font-bold text-ink/75">Qty: {{ $qty }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Pilihan Metode Penerimaan (Grid 2 Kolom) --}}
                            <p class="text-xs font-semibold text-ink/75 mb-2.5">Pilih Cara Penerimaan Pesanan:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="radiogroup" aria-label="Metode penerimaan">
                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_penerimaan" value="kirim" class="opsi-radio peer sr-only" x-model="metode" @checked($metodeAwal === 'kirim')>
                                    <div class="opsi-card h-full flex items-start gap-3 p-3.5 rounded-xl border border-line/60 hover:border-gold/60 transition-all duration-200">
                                        <span class="radio-dot w-4 h-4 mt-0.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1.5">
                                                <p class="text-[13px] font-semibold text-ink/90">Kirim Ekspedisi</p>
                                                <span class="px-1.5 py-0.5 rounded bg-ink/5 text-ink/65 text-[10px] font-semibold shrink-0">Kurir</span>
                                            </div>
                                            <p class="text-xs text-ink/55 mt-1 leading-relaxed">Diantar ke alamat Anda. Ongkir dihitung otomatis.</p>
                                        </div>
                                    </div>
                                </label>

                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_penerimaan" value="ambil" class="opsi-radio peer sr-only" x-model="metode" @checked($metodeAwal === 'ambil')>
                                    <div class="opsi-card h-full flex items-start gap-3 p-3.5 rounded-xl border border-line/60 hover:border-gold/60 transition-all duration-200">
                                        <span class="radio-dot w-4 h-4 mt-0.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1.5">
                                                <p class="text-[13px] font-semibold text-ink/90">Ambil di Toko</p>
                                                <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold shrink-0">Gratis Ongkir</span>
                                            </div>
                                            <p class="text-xs text-ink/55 mt-1 leading-relaxed">{{ $alamatToko }}</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('metode_penerimaan') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror

                            <div class="mt-4 pt-3.5 border-t border-line/35 flex items-center justify-between">
                                <span class="text-[11px] text-ink/50">Barang menjadi hak milik sepenuhnya.</span>
                                <a href="#sec-data-kirim" class="inline-flex items-center gap-1 text-xs font-bold text-gold hover:underline underline-offset-2">
                                    <span x-text="kirim ? 'Lanjut ke Alamat & Kurir' : 'Lanjut ke Data Pengambil'">Lanjut</span>
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>

                        {{-- LANGKAH 2: DATA PENERIMA / PENGAMBIL & ONGKIR --}}
                        <div id="sec-data-kirim" class="ck-sec pd-up bg-panel border border-line/50 rounded-2xl p-5 sm:p-6 shadow-2xs" style="--d: .1s;">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-full bg-ink text-cream flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</span>
                                    <div>
                                        <h2 class="font-display text-lg font-medium text-ink" x-text="kirim ? 'Alamat Tujuan & Kurir' : 'Data Pengambil Pesanan'">Alamat Tujuan &amp; Kurir</h2>
                                        <p class="text-xs text-ink/55 mt-0.5" x-text="kirim ? 'Lengkapi kontak penerima, alamat lengkap, serta layanan kurir.' : 'Isi nama & nomor WhatsApp yang akan mengambil pesanan di toko.'">Lengkapi kontak penerima dan alamat pengiriman.</p>
                                    </div>
                                </div>
                                <span x-show="step2Lengkap" x-cloak class="hidden sm:inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">✓ Lengkap</span>
                            </div>

                            {{-- Daftar Alamat Tersimpan (khusus kirim) --}}
                            @if(isset($alamatList) && count($alamatList) > 0)
                                <div class="mb-5 pb-5 border-b border-line/40" x-show="kirim" x-cloak>
                                    <div class="flex items-center justify-between mb-2.5">
                                        <label class="text-xs font-bold uppercase tracking-wider text-ink/60">Pilih Alamat Tersimpan</label>
                                        <a href="{{ route('akun.dashboard') }}#alamat" class="text-xs font-semibold text-gold hover:underline">Kelola Alamat</a>
                                    </div>
                                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                        @foreach($alamatList as $a)
                                            <label class="cursor-pointer block">
                                                <input type="radio" name="pilih_alamat" value="{{ $a->id }}" class="opsi-radio peer sr-only"
                                                       onchange="pilihAlamatTersimpan(this)"
                                                       data-alamat="{{ json_encode($a) }}"
                                                       @if($alamatUtama && $alamatUtama->id === $a->id) checked @endif>
                                                <div class="opsi-card flex items-start gap-3 p-3.5 rounded-xl border border-line/60 hover:border-gold/60 transition-all duration-200">
                                                    <span class="radio-dot w-4 h-4 mt-0.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <p class="text-[13px] font-bold text-ink/85">{{ $a->label }} — {{ $a->nama_penerima }}</p>
                                                            @if($a->is_utama) <span class="text-[10px] font-bold px-2 py-0.5 bg-gold/15 text-gold rounded-full">Utama</span> @endif
                                                        </div>
                                                        <p class="text-xs text-ink/65 mt-0.5">{{ $a->no_hp }}</p>
                                                        <p class="text-xs text-ink/55 mt-0.5 leading-relaxed">{{ $a->alamat }}, {{ $a->kecamatan }}, {{ $a->kota_kabupaten }}, {{ $a->provinsi }} {{ $a->kode_pos }}</p>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="text-[11px] text-ink/50 mt-2">Atau sesuaikan detail penerima &amp; alamat secara manual di bawah ini.</p>
                                </div>
                            @endif

                            {{-- Identitas Kontak (Penerima / Pengambil) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mb-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-ink/75 mb-1.5">
                                        <span x-text="kirim ? 'Nama Penerima' : 'Nama Pengambil'">Nama Penerima</span> <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="nama_penerima" x-model="namaPenerima" placeholder="Masukkan nama lengkap"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-line/60 bg-white text-[13px] text-ink/90 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/15 transition-all">
                                    @error('nama_penerima') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink/75 mb-1.5">
                                        No. WhatsApp Aktif <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="no_wa" x-model="noWa" inputmode="tel" placeholder="Contoh: 081234567890"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-line/60 bg-white text-[13px] text-ink/90 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/15 transition-all">
                                    @error('no_wa') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Alamat Lengkap (khusus kirim) --}}
                            <div class="mb-3.5" x-show="kirim" x-cloak>
                                <label class="block text-xs font-semibold text-ink/75 mb-1.5">
                                    Alamat Lengkap <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="alamat" rows="2" x-model="alamat" placeholder="Nama jalan, nomor rumah, RT/RW, patokan lokasi"
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-line/60 bg-white text-[13px] text-ink/90 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/15 transition-all resize-none"></textarea>
                                @error('alamat') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Catatan Opsional --}}
                            <div>
                                <label class="block text-xs font-semibold text-ink/75 mb-1.5" x-text="kirim ? 'Catatan untuk Kurir (Opsional)' : 'Catatan / Estimasi Jam Ambil (Opsional)'">Catatan (Opsional)</label>
                                <input type="text" name="catatan" x-model="catatan"
                                       :placeholder="kirim ? 'Contoh: Titip di pos satpam jika rumah kosong' : 'Contoh: Diambil hari ini jam 16.00 WIB'"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-line/60 bg-white text-[13px] text-ink/90 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/15 transition-all">
                            </div>

                            {{-- Info Box: Ambil di Toko --}}
                            <div x-show="!kirim" x-cloak class="pd-swap mt-4 flex gap-3 rounded-xl border border-gold/30 bg-gold/5 p-4">
                                <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0 text-gold" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                <div>
                                    <p class="text-[13px] font-semibold text-ink/90">Lokasi Pengambilan Toko ALNAY.ID</p>
                                    <p class="text-xs text-ink/65 mt-0.5">{{ $alamatToko }}</p>
                                    <p class="text-[11px] text-ink/50 mt-1">Gratis ongkos kirim — Anda tidak perlu memilih kurir pengiriman.</p>
                                </div>
                            </div>

                            {{-- Pencarian Tujuan & Layanan Kurir (khusus kirim) --}}
                            <div class="mt-5 border-t border-line/40 pt-5 space-y-4" x-show="kirim" x-cloak>
                                <div class="relative">
                                    <label class="block text-xs font-semibold text-ink/75 mb-1.5">
                                        Kelurahan / Kecamatan Tujuan (Cek Ongkir) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" id="q-tujuan" autocomplete="off"
                                           value="{{ old('tujuan_label', $alamatUtama->ongkir_tujuan_label ?? $alamatUtama->kecamatan ?? '') }}"
                                           placeholder="Ketik minimal 3 huruf nama kelurahan/kecamatan, lalu pilih..."
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-line/60 bg-white text-[13px] text-ink/90 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/15 transition-all">
                                    <div id="hasil-tujuan" class="hidden absolute z-30 left-0 right-0 mt-1 max-h-52 overflow-y-auto rounded-xl border border-line/60 bg-white shadow-xl"></div>
                                    <p id="info-tujuan" class="mt-1.5 text-xs text-emerald-700 font-medium"></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-ink/75 mb-2">
                                        Pilih Layanan Pengiriman <span class="text-rose-500">*</span>
                                    </label>
                                    <div id="opsi-ongkir" class="space-y-2">
                                        <div class="rounded-xl border border-dashed border-line/60 bg-[#FBFAF7] px-4 py-3 text-xs text-ink/50">
                                            Pilih kelurahan/kecamatan tujuan terlebih dahulu untuk menampilkan opsi kurir.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="tujuan_id" id="in-tujuan-id" value="{{ $alamatUtama->ongkir_tujuan_id ?? '' }}">
                            <input type="hidden" name="tujuan_label" id="in-tujuan-label" value="{{ $alamatUtama->ongkir_tujuan_label ?? '' }}">
                            <input type="hidden" name="kurir" id="inputKurir" value="">

                            <div class="mt-5 pt-3.5 border-t border-line/35 flex items-center justify-end">
                                <a href="#sec-pembayaran" class="inline-flex items-center gap-1 text-xs font-bold text-gold hover:underline underline-offset-2">
                                    Lanjut ke Pembayaran
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>

                        {{-- LANGKAH 3: METODE PEMBAYARAN & PERSETUJUAN --}}
                        <div id="sec-pembayaran" class="ck-sec pd-up bg-panel border border-line/50 rounded-2xl p-5 sm:p-6 shadow-2xs" style="--d: .14s;">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-full bg-ink text-cream flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</span>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h2 class="font-display text-lg font-medium text-ink">Metode Pembayaran</h2>
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase">Verifikasi Otomatis</span>
                                        </div>
                                        <p class="text-xs text-ink/55 mt-0.5" x-text="kirim ? 'Pesanan langsung diproses setelah pembayaran online berhasil.' : 'Bayar online sekarang, atau tunai saat mengambil barang di toko.'">Pilih metode transaksi yang paling nyaman untuk Anda.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_bayar" value="qris" class="opsi-radio peer sr-only" x-model="metodeBayar" @change="bayarDisentuh = true">
                                    <div class="opsi-card h-full flex items-start gap-3 p-3.5 rounded-xl border border-line/60 hover:border-gold/60 transition-all duration-200">
                                        <span class="radio-dot w-4 h-4 mt-0.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1.5">
                                                <p class="text-[13px] font-semibold text-ink/90">QRIS Instan</p>
                                                <span class="px-1.5 py-0.5 rounded bg-gold/15 text-gold text-[10px] font-bold">Populer</span>
                                            </div>
                                            <p class="text-xs text-ink/55 mt-1">Scan via m-Banking (BCA, Mandiri, BRI, BNI) atau e-Wallet.</p>
                                        </div>
                                    </div>
                                </label>

                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_bayar" value="va" class="opsi-radio peer sr-only" x-model="metodeBayar" @change="bayarDisentuh = true">
                                    <div class="opsi-card h-full flex items-start gap-3 p-3.5 rounded-xl border border-line/60 hover:border-gold/60 transition-all duration-200">
                                        <span class="radio-dot w-4 h-4 mt-0.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-semibold text-ink/90">Virtual Account Bank</p>
                                            <p class="text-xs text-ink/55 mt-1">BCA, Mandiri, BNI, BRI, Permata (tanpa upload bukti).</p>
                                        </div>
                                    </div>
                                </label>

                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_bayar" value="ewallet" class="opsi-radio peer sr-only" x-model="metodeBayar" @change="bayarDisentuh = true">
                                    <div class="opsi-card h-full flex items-start gap-3 p-3.5 rounded-xl border border-line/60 hover:border-gold/60 transition-all duration-200">
                                        <span class="radio-dot w-4 h-4 mt-0.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-semibold text-ink/90">E-Wallet Direct</p>
                                            <p class="text-xs text-ink/55 mt-1">GoPay, ShopeePay, OVO, dan DANA.</p>
                                        </div>
                                    </div>
                                </label>

                                {{-- Tunai: hanya untuk ambil di toko --}}
                                <label class="cursor-pointer block" x-show="!kirim" x-cloak>
                                    <input type="radio" name="metode_bayar" value="tunai" class="opsi-radio peer sr-only" x-model="metodeBayar" :disabled="kirim" @change="bayarDisentuh = true">
                                    <div class="opsi-card h-full flex items-start gap-3 p-3.5 rounded-xl border border-line/60 hover:border-gold/60 transition-all duration-200">
                                        <span class="radio-dot w-4 h-4 mt-0.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-semibold text-ink/90">Bayar Tunai di Toko</p>
                                            <p class="text-xs text-ink/55 mt-1">Bayar langsung di kasir saat mengambil pesanan.</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('metode_bayar') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror

                            {{-- Persetujuan Syarat & Ketentuan --}}
                            <div class="mt-5 pt-4 border-t border-line/40">
                                <label class="flex items-start gap-3 p-3 rounded-xl border transition-colors cursor-pointer"
                                       :class="setuju ? 'border-gold/40 bg-gold/5' : 'border-line/50 bg-[#FBFAF7] hover:border-gold/40'">
                                    <input type="checkbox" name="setuju" value="1" x-model="setuju"
                                           class="mt-0.5 w-4 h-4 rounded border-line text-gold focus:ring-gold focus:ring-offset-0 shrink-0">
                                    <span class="text-xs sm:text-[13px] text-ink/80 leading-relaxed">
                                        Saya sudah memeriksa detail pesanan dan menyetujui <a href="#" class="text-gold font-semibold underline underline-offset-2">syarat &amp; ketentuan pembelian</a> ALNAY.ID. <span class="text-rose-500">*</span>
                                    </span>
                                </label>
                                @error('setuju') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ==========================================================
                         KOLOM KANAN: LANGKAH 4 — RINGKASAN & KONFIRMASI
                    ========================================================== --}}
                    <div id="sec-konfirmasi" class="ck-sec lg:sticky lg:top-24 h-fit">
                        <div class="pd-up" style="--d: .18s;">
                            <div class="bg-panel border border-line/50 rounded-2xl p-5 sm:p-6 shadow-xs transition-colors duration-300" :class="{ 'pd-shake border-rose-300': getar }">

                                <div class="flex items-center justify-between gap-2 pb-3.5 border-b border-line/40 mb-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-6 h-6 rounded-full bg-gold text-white flex items-center justify-center text-xs font-bold shrink-0">4</span>
                                        <h2 class="font-display text-lg font-medium text-ink">Ringkasan Tagihan</h2>
                                    </div>
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-cream text-ink/70">{{ $qty }} Unit</span>
                                </div>

                                {{-- Ringkasan Pilihan Aktif --}}
                                <dl class="rounded-xl bg-[#FBFAF7] border border-line/40 p-3 mb-4 space-y-2 text-xs">
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-ink/55 shrink-0" x-text="kirim ? 'Penerima' : 'Pengambil'">Penerima</dt>
                                        <dd class="font-semibold text-ink/85 text-right truncate" x-text="namaPenerima.trim() || '-'">-</dd>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-ink/55 shrink-0">Penerimaan</dt>
                                        <dd class="font-semibold text-ink/85 text-right" x-text="kirim ? 'Kirim Ekspedisi' : 'Ambil di Toko'">Kirim Ekspedisi</dd>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-ink/55 shrink-0">Pembayaran</dt>
                                        <dd class="font-semibold text-ink/85 text-right" x-text="namaMetodeBayar">QRIS Instan</dd>
                                    </div>
                                </dl>

                                {{-- Rincian Biaya --}}
                                <div class="space-y-2.5 text-[13px] mb-4">
                                    <div class="flex justify-between gap-3">
                                        <span class="text-ink/65 truncate">{{ $produk->nama }} &times;{{ $qty }}</span>
                                        <span class="font-medium text-ink/85 tabular-nums shrink-0">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <span class="text-ink/65" id="labelNamaOngkir">Ongkos Kirim</span>
                                        <span class="font-medium text-ink/85 tabular-nums shrink-0" id="labelOngkir">Rp0</span>
                                    </div>
                                </div>

                                <div class="bg-ink text-cream rounded-xl p-4 mb-4">
                                    <div class="flex justify-between items-baseline gap-3">
                                        <span class="text-xs text-cream/75 leading-snug" x-text="bayarTunai ? 'Total Dibayar di Toko' : 'Total Dibayar Sekarang'">Total Dibayar Sekarang</span>
                                        <span class="font-sans text-xl font-bold tabular-nums whitespace-nowrap text-gold" id="labelTotalBayar">Rp{{ number_format($total, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                {{-- Indikator Kesiapan Langkah --}}
                                <div class="mb-4 rounded-xl px-3.5 py-2.5 text-xs flex items-start gap-2 border transition-colors"
                                     :class="siapKirim ? 'bg-emerald-50/90 border-emerald-200 text-emerald-800' : 'bg-amber-50/90 border-amber-200 text-amber-800'">
                                    <span class="font-bold shrink-0 mt-px" x-text="siapKirim ? '✓' : '!'">!</span>
                                    <span x-text="pesanKesiapan" class="leading-relaxed">Memeriksa kelengkapan data...</span>
                                </div>

                                <p x-ref="errorBox" x-show="errorMetode" x-cloak x-transition x-text="errorMetode"
                                   class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium"></p>

                                <button type="submit" :disabled="sedangKirim"
                                        class="pd-shine w-full h-[46px] rounded-xl bg-gold text-white text-[13px] font-semibold tracking-wide hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait disabled:hover:translate-y-0 transition-all duration-300 mb-2.5">
                                    <span x-show="!sedangKirim" x-text="labelTombol">Bayar Sekarang &amp; Kirim Pesanan</span>
                                    <span x-show="sedangKirim" x-cloak class="inline-flex items-center justify-center gap-2">
                                        <svg class="pd-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 3a9 9 0 1 0 9 9" /></svg> Memproses…
                                    </span>
                                </button>

                                <a href="{{ route('koleksi.index') }}"
                                   class="block w-full h-[38px] rounded-xl border border-line/50 bg-white text-ink/65 text-xs font-medium hover:bg-cream/50 transition-all duration-200 text-center leading-[36px]">
                                    Kembali ke Koleksi
                                </a>

                                <p class="text-[11px] text-ink/50 leading-relaxed mt-3.5 text-center"
                                   x-text="kirim ? 'Nomor resi pengiriman akan dikirim ke Pesanan Anda.' : (bayarTunai ? 'Pesanan dicatat dan dibayar tunai saat pengambilan di toko.' : 'Pesanan siap diambil di toko setelah pembayaran terverifikasi.')"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bilah Bayar Menempel di Bawah (HP) --}}
                <div class="lg:hidden fixed inset-x-0 bottom-0 z-30 bg-white/95 backdrop-blur-md border-t border-line/50 px-4 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] flex items-center gap-3 shadow-[0_-10px_25px_rgba(0,0,0,0.06)]">
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-ink/55" x-text="bayarTunai ? 'Total dibayar di toko' : 'Total dibayar sekarang'">Total dibayar sekarang</p>
                        <p class="font-sans text-lg font-bold text-ink tabular-nums truncate" id="labelTotalBayarMobile">Rp{{ number_format($total, 0, ',', '.') }}</p>
                    </div>
                    <button type="submit" :disabled="sedangKirim" class="shrink-0 h-11 px-6 rounded-xl bg-gold text-white text-[13px] font-semibold active:scale-[0.98] disabled:opacity-70 transition-all">
                        <span x-text="sedangKirim ? 'Memproses…' : (bayarTunai ? 'Buat Pesanan' : 'Bayar Sekarang')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPT 1: Logika Ongkir & Alamat Tersimpan --}}
    <script>
        (function () {
            const SUBTOTAL   = {{ (int) $subtotal }};
            const URL_TUJUAN = "{{ route('ongkir.tujuan') }}";
            const URL_HITUNG = "{{ route('beli.ongkir') }}";
            const TOKEN      = document.querySelector('input[name="_token"]').value;

            const $ = id => document.getElementById(id);
            const rupiah = n => 'Rp' + new Intl.NumberFormat('id-ID').format(n);

            let timer = null;
            const AWAL_TUJUAN = @json(['id' => $alamatUtama->ongkir_tujuan_id ?? null, 'label' => $alamatUtama->ongkir_tujuan_label ?? null]);

            let ongkirNow = 0;
            let labelNow = '';
            let kirimDiinisialisasi = false;

            const isKirim = () => {
                const r = document.querySelector('input[name="metode_penerimaan"]:checked');
                return !r || r.value === 'kirim';
            };

            function beritahuAlpine() {
                window.dispatchEvent(new CustomEvent('ongkir-updated', {
                    detail: {
                        tujuanId: $('in-tujuan-id').value,
                        kurir: $('inputKurir').value,
                    }
                }));
            }

            function render() {
                const kirim = isKirim();
                const ongkir = kirim ? ongkirNow : 0;
                const total = SUBTOTAL + ongkir;
                $('labelNamaOngkir').textContent = kirim && labelNow ? 'Ongkos Kirim (' + labelNow + ')' : 'Ongkos Kirim';
                $('labelOngkir').textContent = !kirim ? 'Gratis (ambil di toko)' : (labelNow ? rupiah(ongkir) : 'Belum dipilih');
                ['labelTotalBayar', 'labelTotalBayarMobile'].forEach(id => {
                    if ($(id)) $(id).textContent = rupiah(total);
                });
                beritahuAlpine();
            }

            function setHarga(ongkir, label) {
                ongkirNow = ongkir;
                labelNow = label;
                render();
            }

            function resetLayanan() {
                $('inputKurir').value = '';
                setHarga(0, '');
            }

            function resetTujuan() {
                $('in-tujuan-id').value = '';
                $('in-tujuan-label').value = '';
                $('info-tujuan').textContent = '';
                pesanOpsi('Pilih kelurahan/kecamatan tujuan terlebih dahulu untuk menampilkan opsi kurir.');
                resetLayanan();
            }

            function pesanOpsi(teks) {
                const div = document.createElement('div');
                div.className = 'rounded-xl border border-dashed border-line/60 bg-[#FBFAF7] px-4 py-3 text-xs text-ink/55';
                div.textContent = teks;
                $('opsi-ongkir').replaceChildren(div);
            }

            async function cariTujuan(q) {
                try {
                    const res = await fetch(URL_TUJUAN + '?q=' + encodeURIComponent(q), {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    renderHasil(Array.isArray(data) ? data : []);
                } catch (e) {
                    $('info-tujuan').textContent = 'Gagal mencari tujuan. Coba lagi.';
                }
            }

            function renderHasil(list) {
                const box = $('hasil-tujuan');
                box.replaceChildren();

                if (!list.length) {
                    const p = document.createElement('p');
                    p.className = 'px-3.5 py-2.5 text-xs text-ink/55';
                    p.textContent = 'Tujuan tidak ditemukan.';
                    box.appendChild(p);
                }

                list.forEach(t => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'block w-full text-left px-3.5 py-2.5 text-xs text-ink/85 hover:bg-gold/10 border-b border-line/30 last:border-0 transition-colors';
                    b.textContent = t.label;
                    b.addEventListener('click', () => pilihTujuan(t));
                    box.appendChild(b);
                });
                box.classList.remove('hidden');
            }

            function pilihTujuan(t) {
                $('in-tujuan-id').value = t.id;
                $('in-tujuan-label').value = t.label;
                $('q-tujuan').value = t.label;
                $('hasil-tujuan').classList.add('hidden');
                $('info-tujuan').textContent = '✓ Area tujuan dipilih.';
                hitungOngkir(t.id);
            }

            async function hitungOngkir(tujuanId) {
                resetLayanan();
                pesanOpsi('Sedang menghitung tarif pengiriman...');

                try {
                    const res = await fetch(URL_HITUNG, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': TOKEN,
                        },
                        body: JSON.stringify({ tujuan_id: tujuanId }),
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        pesanOpsi(data.message || 'Gagal menghitung ongkir.');
                        return;
                    }
                    renderOpsi(data.opsi || []);
                } catch (e) {
                    pesanOpsi('Gagal menghitung ongkir. Coba lagi.');
                }
            }

            function renderOpsi(list) {
                const wrap = $('opsi-ongkir');
                wrap.replaceChildren();

                list.forEach(o => {
                    const label = document.createElement('label');
                    label.className = 'cursor-pointer block';

                    const input = document.createElement('input');
                    input.type = 'radio';
                    input.name = 'layanan_ongkir';
                    input.value = o.layanan;
                    input.className = 'opsi-radio peer sr-only';
                    input.addEventListener('change', () => {
                        $('inputKurir').value = o.kurir;
                        setHarga(o.biaya, o.kurir.toUpperCase() + ' ' + o.layanan);
                    });

                    const card = document.createElement('div');
                    card.className = 'opsi-card flex items-center gap-3 p-3.5 rounded-xl border border-line/60 hover:border-gold/60 transition-all duration-200';

                    const dot = document.createElement('span');
                    dot.className = 'radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all';

                    const info = document.createElement('div');
                    info.className = 'flex-1 min-w-0';
                    const nama = document.createElement('p');
                    nama.className = 'text-[13px] font-semibold text-ink/90';
                    nama.textContent = o.kurir.toUpperCase() + ' - ' + o.layanan;
                    const est = document.createElement('p');
                    est.className = 'text-[11px] text-ink/55 mt-0.5';
                    est.textContent = (o.deskripsi || 'Layanan Pengiriman') + ' (est. ' + (o.etd || '-') + ')';
                    info.append(nama, est);

                    const harga = document.createElement('span');
                    harga.className = 'text-[13px] font-bold text-ink tabular-nums shrink-0';
                    harga.textContent = rupiah(o.biaya);

                    card.append(dot, info, harga);
                    label.append(input, card);
                    wrap.appendChild(label);
                });

                const pertama = wrap.querySelector('input[name="layanan_ongkir"]');
                if (pertama) {
                    pertama.checked = true;
                    pertama.dispatchEvent(new Event('change'));
                }
            }

            $('q-tujuan').addEventListener('input', function () {
                resetTujuan();
                clearTimeout(timer);
                const q = this.value.trim();
                if (q.length < 3) {
                    $('hasil-tujuan').classList.add('hidden');
                    return;
                }
                timer = setTimeout(() => cariTujuan(q), 350);
            });

            document.addEventListener('click', e => {
                if (!$('hasil-tujuan').contains(e.target) && e.target !== $('q-tujuan')) {
                    $('hasil-tujuan').classList.add('hidden');
                }
            });

            window.pilihAlamatTersimpan = function(el) {
                const data = JSON.parse(el.dataset.alamat);

                const inputNama = document.querySelector('input[name="nama_penerima"]');
                const inputWa = document.querySelector('input[name="no_wa"]');
                const inputAlamat = document.querySelector('textarea[name="alamat"]');

                if (inputNama) { inputNama.value = data.nama_penerima; inputNama.dispatchEvent(new Event('input')); }
                if (inputWa) { inputWa.value = data.no_hp; inputWa.dispatchEvent(new Event('input')); }
                if (inputAlamat) { inputAlamat.value = data.alamat; inputAlamat.dispatchEvent(new Event('input')); }

                if (data.ongkir_tujuan_id && data.ongkir_tujuan_label) {
                    pilihTujuan({ id: data.ongkir_tujuan_id, label: data.ongkir_tujuan_label });
                }
            };

            function mulaiKirim() {
                if (kirimDiinisialisasi) return;
                kirimDiinisialisasi = true;

                if (AWAL_TUJUAN.id && AWAL_TUJUAN.label) {
                    pilihTujuan({ id: AWAL_TUJUAN.id, label: AWAL_TUJUAN.label });
                    return;
                }

                const selectedAddress = document.querySelector('input[name="pilih_alamat"]:checked');
                if (selectedAddress) {
                    pilihAlamatTersimpan(selectedAddress);
                } else {
                    const awal = $('q-tujuan').value.trim();
                    if (awal.length >= 3) cariTujuan(awal);
                }
            }

            document.querySelectorAll('input[name="metode_penerimaan"]').forEach(r => {
                r.addEventListener('change', () => {
                    render();
                    if (isKirim()) mulaiKirim();
                });
            });

            document.addEventListener('DOMContentLoaded', function () {
                setHarga(0, '');
                if (isKirim()) mulaiKirim();
            });
        })();
    </script>

    {{-- SCRIPT 2: Alpine.js Stepper & Form Validation --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkoutBeli', (cfg) => ({
                metode: cfg.metode,
                metodeBayar: cfg.bayar,
                namaPenerima: cfg.nama,
                noWa: cfg.wa,
                alamat: cfg.alamat,
                catatan: cfg.catatan,
                setuju: cfg.setuju,

                tujuanId: '',
                kurirDipilih: '',
                bayarDisentuh: false,

                errorMetode: '',
                getar: false,
                sedangKirim: false,

                init() {
                    this.tujuanId = document.getElementById('in-tujuan-id')?.value || '';
                    this.kurirDipilih = document.getElementById('inputKurir')?.value || '';

                    window.addEventListener('ongkir-updated', (e) => {
                        this.tujuanId = e.detail.tujuanId || '';
                        this.kurirDipilih = e.detail.kurir || '';
                    });

                    this.$watch('metode', (v) => {
                        if (v === 'kirim' && this.metodeBayar === 'tunai') this.metodeBayar = 'qris';
                        this.errorMetode = '';
                    });
                },

                get kirim() { return this.metode === 'kirim'; },
                get bayarTunai() { return !this.kirim && this.metodeBayar === 'tunai'; },
                get namaMetodeBayar() {
                    const map = { qris: 'QRIS Instan', va: 'Virtual Account', ewallet: 'E-Wallet', tunai: 'Tunai di Toko' };
                    return map[this.metodeBayar] || 'QRIS Instan';
                },
                get labelTombol() {
                    if (this.kirim) return 'Bayar Sekarang & Kirim Pesanan';
                    return this.bayarTunai ? 'Buat Pesanan (Bayar di Toko)' : 'Bayar Sekarang';
                },

                // Status tiap langkah untuk stepper
                get step2Lengkap() {
                    const kontakOk = this.namaPenerima.trim().length > 0 && this.noWa.trim().length > 0;
                    if (!this.kirim) return kontakOk;
                    return kontakOk && this.alamat.trim().length > 0 && this.tujuanId !== '' && this.kurirDipilih !== '';
                },
                get stepAktif() {
                    if (!this.metode) return 1;
                    if (!this.step2Lengkap) return 2;
                    if (!this.setuju) return 3;
                    return 4;
                },
                stepState(n) {
                    if (n < this.stepAktif) return 'done';
                    if (n === this.stepAktif) return 'active';
                    return 'pending';
                },

                get siapKirim() {
                    return this.step2Lengkap && this.setuju;
                },
                get pesanKesiapan() {
                    if (!this.namaPenerima.trim() || !this.noWa.trim())
                        return this.kirim ? 'Lengkapi Nama & No. WhatsApp penerima di Langkah 2.' : 'Lengkapi Nama & No. WhatsApp pengambil di Langkah 2.';
                    if (this.kirim && !this.alamat.trim())
                        return 'Isi alamat lengkap pengiriman di Langkah 2.';
                    if (this.kirim && (!this.tujuanId || !this.kurirDipilih))
                        return 'Pilih kelurahan tujuan dan layanan kurir di Langkah 2.';
                    if (!this.setuju)
                        return 'Centang persetujuan syarat & ketentuan di Langkah 3.';
                    return 'Semua data sudah lengkap. Pesanan siap diproses.';
                },

                tampilError(pesan) {
                    this.errorMetode = pesan;
                    this.getar = false;
                    this.$nextTick(() => {
                        this.getar = true;
                        setTimeout(() => { this.getar = false; }, 500);
                        if (this.$refs.errorBox) {
                            this.$refs.errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                    return false;
                },

                validasi() {
                    if (!this.metode) return this.tampilError('Pilih metode penerimaan.');
                    if (!this.metodeBayar) return this.tampilError('Pilih metode pembayaran.');
                    if (this.metodeBayar === 'tunai' && this.kirim)
                        return this.tampilError('Pembayaran tunai hanya tersedia untuk ambil di toko.');

                    if (this.kirim) {
                        if (!document.getElementById('in-tujuan-id').value)
                            return this.tampilError('Pilih kelurahan/kecamatan tujuan dari daftar.');
                        if (!document.getElementById('inputKurir').value)
                            return this.tampilError('Pilih kurir dan layanan pengiriman.');
                    }

                    if (!this.namaPenerima.trim()) return this.tampilError(this.kirim ? 'Nama penerima wajib diisi.' : 'Nama pengambil wajib diisi.');
                    if (!this.noWa.trim()) return this.tampilError('No. WhatsApp wajib diisi.');
                    if (this.kirim && !this.alamat.trim()) return this.tampilError('Alamat lengkap wajib diisi.');
                    if (!this.setuju) return this.tampilError('Centang persetujuan syarat & ketentuan pembelian.');

                    this.errorMetode = '';
                    return true;
                },

                kirimForm() {
                    if (this.sedangKirim) return;
                    if (!this.validasi()) return;
                    this.sedangKirim = true;
                    this.$refs.formCheckout.submit();
                },
            }));
        });
    </script>

@endsection