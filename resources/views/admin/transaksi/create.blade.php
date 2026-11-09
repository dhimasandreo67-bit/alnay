@extends('admin.layouts.admin')
@section('title', 'Kasir - Buat Transaksi Baru')


@section('content')
@php
    // ---------- Data untuk JS (disiapkan di sini supaya @json tetap sederhana) ----------
    $fotoUrl = function ($p) {
        $f = $p->foto_produk;
        if (is_string($f)) {
            $dec = json_decode($f, true);
            $f = is_array($dec) ? $dec : [$f];
        }
        $f = is_array($f) ? collect($f)->filter()->first() : null;
        if (! $f) {
            return null;
        }
        if (preg_match('#^https?://#', $f)) {
            return $f;
        }
        return asset('storage/' . ltrim($f, '/'));
    };

    // Satu set stok (ukuran + warna + stok) dalam bentuk yang dibaca JS kasir
    $bangunSet = function (array $ukuranRaw, array $warnaRaw, int $stok) {
        $ukuran = collect($ukuranRaw)->map(function ($v) {
            return [
                'ukuran' => (string) ($v['ukuran'] ?? ''),
                'stok'   => (int) ($v['stok'] ?? 0),
                'warna'  => collect($v['warna'] ?? [])->map(fn ($w) => [
                    'nama' => (string) ($w['nama'] ?? ''),
                    'stok' => (int) ($w['stok'] ?? 0),
                ])->values(),
            ];
        })->values();

        $warna = collect($warnaRaw)
            ->filter(fn ($w) => is_scalar($w) && $w !== '')
            ->map(fn ($w) => (string) $w)
            ->values();

        return ['ukuran' => $ukuran, 'warna' => $warna, 'stok' => $stok];
    };

    $produkJs = $produks->map(function ($p) use ($fotoUrl, $bangunSet) {
        // Produk dijual & disewa punya stok terpisah: set 'beli' dan set 'sewa'.
        // Produk satu jenis: kedua set sama (memakai kolom lama).
        $setBeli = $bangunSet($p->ukuranUntuk('jual'), $p->warnaUntuk('jual'), $p->stokUntuk('jual'));
        $setSewa = $bangunSet($p->ukuranUntuk('sewa'), $p->warnaUntuk('sewa'), $p->stokUntuk('sewa'));

        return [
            'id'            => $p->id,
            'sku'           => $p->sku,
            'nama'          => $p->nama,
            'foto'          => $fotoUrl($p),
            'kategori_id'   => $p->kategori_id,
            'kategori'      => $p->kategori->nama_kategori ?? null,
            'bahan'         => $p->bahan,
            'motif'         => $p->motif,
            'berat'         => $p->berat !== null ? (float) $p->berat : null,
            'warna'         => $setBeli['warna'],
            'ukuran'        => $setBeli['ukuran'],
            'set'           => ['beli' => $setBeli, 'sewa' => $setSewa],
            'harga_jual'    => (float) $p->harga_jual,
            'harga_sewa'    => (float) $p->harga_sewa,
            'dp'            => (float) $p->dp,
            'stok'          => $setBeli['stok'],
            'status'        => $p->status,
            'bisa_disewa'   => (bool) $p->bisa_disewa,
            'dapat_dijual'  => (bool) $p->dapat_dijual,
            'diskon_persen' => (float) $p->diskon_persen,
            'diskon_sewa'   => (float) ($p->diskon_sewa_persen ?? 0),
            'diskon_jual'   => (float) ($p->diskon_jual_persen ?? 0),
            'denda'         => (float) $p->denda_per_hari_telat,
        ];
    })->values();

    $kategoriJs = $kategoris->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama_kategori])->values();

    $usersJs = $users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'no_hp' => $u->no_hp])->values();

    $oldItems = collect(old('items', []))->map(function ($i) {
        return [
            'produk_id' => (int) ($i['produk_id'] ?? 0),
            'ukuran'    => (string) ($i['ukuran'] ?? ''),
            'warna'     => (string) ($i['warna'] ?? ''),
            'qty'       => max(1, (int) ($i['qty'] ?? 1)),
        ];
    })->values();

    $oldJenis    = old('jenis', 'beli');
    $oldCustomer = old('customer_type', 'terdaftar');
    $oldStatus   = old('status_pembayaran', 'lunas');
@endphp

<div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto transition-all duration-500 animate-fade-in">

    <style>
        .group-shine {
            position: relative;
            overflow: hidden;
        }

        .group-shine::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 20%;
            height: 200%;
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(30deg);
            transition: all 0.6s ease;
        }

        .group-shine:hover::after {
            left: 130%;
        }
    </style>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Kasir - Transaksi Baru</h1>
            <p class="text-xs text-gray-500 mt-1">Catat transaksi offline. Setelah disimpan, data langsung muncul di Semua Transaksi dengan label <strong>Kasir</strong>.</p>
        </div>
        <a href="{{ route('admin.transaksi.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-amber-600 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Semua Transaksi
        </a>
    </div>

    @if ($errors->any())
        <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-bold mb-1 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Periksa lagi isian form:
            </p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.transaksi.store') }}" id="form-kasir" class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- ================= KOLOM KIRI ================= --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- 1. Jenis transaksi --}}
            <section class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-sm shadow-amber-500/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Jenis Transaksi</p>
                </div>
                <div class="flex gap-3">
                    <label class="flex-1">
                        <input type="radio" name="jenis" value="beli" class="peer sr-only" id="radio-beli" @checked($oldJenis === 'beli')>
                        <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-400 transition-all">
                            Pembelian
                        </div>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="jenis" value="sewa" class="peer sr-only" id="radio-sewa" @checked($oldJenis === 'sewa')>
                        <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-400 transition-all">
                            Penyewaan
                        </div>
                    </label>
                </div>

                <div id="box-tanggal-sewa" class="hidden mt-4">
                    {{-- Kalender rentang sewa (sama dengan kalender di halaman detail produk).
                         Nilai tersimpan di dua input tersembunyi supaya form & validasi lama tetap jalan. --}}
                    <input type="hidden" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam') }}">
                    <input type="hidden" id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" value="{{ old('tanggal_kembali_rencana') }}">

                    <p class="text-xs font-bold text-gray-500 mb-2">Tanggal Sewa <span class="text-rose-600">*</span></p>
                    <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                        <div class="mb-2 flex items-center justify-between">
                            <span id="kal-judul" class="text-sm font-bold capitalize text-gray-800"></span>
                            <div class="flex gap-1.5">
                                <button type="button" id="kal-mundur" aria-label="Bulan sebelumnya"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition-all hover:border-amber-400 hover:text-amber-600 active:scale-90 disabled:cursor-not-allowed disabled:opacity-40">&#8249;</button>
                                <button type="button" id="kal-maju" aria-label="Bulan berikutnya"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition-all hover:border-amber-400 hover:text-amber-600 active:scale-90">&#8250;</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 gap-y-1 text-center">
                            <div class="py-1.5 text-[11px] font-medium text-gray-400">Sen</div>
                            <div class="py-1.5 text-[11px] font-medium text-gray-400">Sel</div>
                            <div class="py-1.5 text-[11px] font-medium text-gray-400">Rab</div>
                            <div class="py-1.5 text-[11px] font-medium text-gray-400">Kam</div>
                            <div class="py-1.5 text-[11px] font-medium text-gray-400">Jum</div>
                            <div class="py-1.5 text-[11px] font-medium text-gray-400">Sab</div>
                            <div class="py-1.5 text-[11px] font-medium text-gray-400">Min</div>
                        </div>
                        <div id="kal-grid" class="grid grid-cols-7 gap-y-1 text-center"></div>
                        {{-- Jadwal sewa: rentang ambil - dipakai - kembali (diisi oleh renderKalender) --}}
                        <div id="kal-jadwal" class="mt-3 space-y-1.5 border-t border-gray-100 pt-3 text-[11px] text-gray-600"></div>

                        <div class="mt-3 border-t border-gray-100 pt-3">
                            <p class="mb-2 text-[11px] font-semibold text-gray-700">Keterangan warna</p>
                            <div class="grid grid-cols-1 gap-x-4 gap-y-1.5 text-[11px] text-gray-500 sm:grid-cols-2">
                                <span class="flex items-center gap-2"><span class="h-4 w-4 rounded-md bg-white ring-1 ring-gray-200"></span> Tersedia, bisa dipilih</span>
                                <span class="flex items-center gap-2"><span class="h-4 w-4 rounded-md bg-white ring-1 ring-amber-400"></span> Hari ini</span>
                                <span class="flex items-center gap-2"><span class="h-4 w-4 rounded-md bg-amber-600"></span> Tanggal ambil / kembali yang dipilih</span>
                                <span class="flex items-center gap-2"><span class="h-4 w-4 rounded-md bg-amber-100"></span> Rentang sewa yang dipilih</span>
                                <span class="flex items-center gap-2"><span class="h-4 w-4 rounded-md bg-sky-50 ring-1 ring-sky-300"></span> Sebagian unit disewa, masih bisa dipilih</span>
                                <span class="flex items-center gap-2"><span class="h-4 w-4 rounded-md bg-rose-200"></span> Merah: semua unit disewa, tidak bisa dipilih</span>
                                <span class="flex items-center gap-2"><span class="flex h-4 w-4 items-center justify-center rounded-md bg-gray-50 text-[9px] text-gray-300">12</span> Abu-abu: sudah lewat</span>
                                <span class="flex items-center gap-2 sm:col-span-2"><span class="w-4 text-center text-[8px] font-semibold uppercase text-gray-500">aa</span> Tulisan kecil di tanggal: AMBIL (barang diambil), DIPAKAI, KEMBALI (barang dikembalikan)</span>
                            </div>
                        </div>
                    </div>
                    <p id="kal-hint" class="mt-2 text-[11px] text-gray-500">Tambahkan produk ke keranjang dulu agar tanggal yang sudah dibooking (online &amp; kasir) ditandai merah. Klik tanggal yang sama dua kali untuk sewa 1 hari.</p>
                    <div class="mt-3 grid grid-cols-3 gap-2 rounded-xl bg-amber-50/60 px-4 py-3 text-xs ring-1 ring-amber-100">
                        <div><p class="text-[10px] text-gray-400">Ambil</p><p id="kal-ambil" class="font-bold text-gray-800">Klik tanggal</p></div>
                        <div><p class="text-[10px] text-gray-400">Kembali</p><p id="kal-kembali" class="font-bold text-gray-800">-</p></div>
                        <div><p class="text-[10px] text-gray-400">Durasi</p><p id="kal-durasi" class="font-bold text-amber-700">-</p></div>
                    </div>
                    <p id="info-durasi" class="mt-2 text-xs font-semibold text-amber-700"></p>
                    <div id="info-bentrok" class="hidden mt-2 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800" role="alert"></div>

                    {{-- Upload KTP penyewa (jaminan) --}}
                    <div class="mt-4 rounded-2xl border border-dashed border-amber-300 bg-amber-50/40 p-4">
                        <label for="foto_ktp" class="text-xs font-bold text-gray-700">Foto KTP Penyewa <span class="text-rose-600">*</span></label>
                        <p class="mt-0.5 text-[11px] text-gray-500">Wajib untuk penyewaan. Format JPG, PNG, atau WEBP, maksimal 2 MB.</p>
                        <input type="file" id="foto_ktp" name="foto_ktp" accept="image/jpeg,image/png,image/webp"
                               class="mt-2 block w-full text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-amber-700 hover:file:bg-amber-200">
                        <div id="preview-ktp" class="hidden mt-3 items-center gap-3">
                            <img id="preview-ktp-img" src="" alt="Pratinjau KTP" class="h-20 w-32 rounded-lg border border-gray-200 object-cover bg-white">
                            <button type="button" id="hapus-ktp" class="text-xs font-bold text-rose-600 hover:text-rose-700">Hapus foto</button>
                        </div>
                        <p id="error-ktp" class="hidden mt-2 text-[11px] font-semibold text-rose-600"></p>
                        {{-- Cukup centang: NIK & nama di KTP sudah dicocokkan kasir dengan pelanggan --}}
                        <label for="ktp_sesuai" class="mt-3 flex cursor-pointer items-start gap-3 rounded-xl border border-amber-200 bg-white p-3">
                            <input type="checkbox" id="ktp_sesuai" name="ktp_sesuai" value="1" @checked(old('ktp_sesuai')) class="mt-0.5 h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <span>
                                <span class="block text-xs font-bold text-gray-800">NIK & nama di KTP sudah sesuai dengan penyewa <span class="text-rose-600">*</span></span>
                                <span class="mt-0.5 block text-[11px] text-gray-500">Cocokkan KTP asli dengan pelanggan. Jaminan otomatis tercatat <b>terverifikasi</b> di Kelola Sewa.</span>
                            </span>
                        </label>
                        <p id="error-ktp-sesuai" class="hidden mt-2 text-[11px] font-semibold text-rose-600"></p>

                        {{-- Deposit jaminan (satu paket dengan KTP) --}}
                        <div class="mt-4 pt-4 border-t border-amber-200">
                            <label for="jumlah_deposit" class="text-xs font-bold text-gray-700">Jumlah Deposit Jaminan (Rp) <span class="text-rose-600">*</span></label>
                            <p class="mt-0.5 text-[11px] text-gray-500">Deposit dititipkan pelanggan bersama KTP, dan dikembalikan 100% saat barang sudah kembali &amp; tidak ada denda tertunda.</p>
                            <input type="number" id="jumlah_deposit" name="jumlah_deposit" min="1" step="1000"
                                   value="{{ old('jumlah_deposit') }}" placeholder="Contoh: 200000"
                                   class="mt-2 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <p id="error-deposit" class="hidden mt-2 text-[11px] font-semibold text-rose-600"></p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 2. Data pelanggan --}}
            <section class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-sm shadow-amber-500/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Data Pelanggan</p>
                </div>
                <div class="flex gap-3 mb-4">
                    <label class="flex-1">
                        <input type="radio" name="customer_type" value="terdaftar" class="peer sr-only" @checked($oldCustomer === 'terdaftar')>
                        <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-400 transition-all">
                            Pelanggan Terdaftar
                        </div>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="customer_type" value="tamu" class="peer sr-only" @checked($oldCustomer === 'tamu')>
                        <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-400 transition-all">
                            Tamu (Walk-in)
                        </div>
                    </label>
                </div>

                <div id="box-user-terdaftar" class="space-y-2">
                    <label for="cari-user" class="text-xs font-bold text-gray-500">Cari Pelanggan</label>
                    <input type="search" id="cari-user" placeholder="Ketik nama atau nomor HP" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all" autocomplete="off">
                    <select name="user_id" id="select-user" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all"></select>
                    <div id="kartu-user" class="hidden items-center gap-3 rounded-xl bg-amber-50/60 ring-1 ring-amber-100 px-4 py-3">
                        <div id="user-inisial" class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-sm font-black text-white shadow-sm shadow-amber-500/20"></div>
                        <div class="min-w-0">
                            <p id="user-nama" class="truncate text-sm font-bold text-gray-900"></p>
                            <p id="user-hp" class="text-xs text-gray-500"></p>
                        </div>
                    </div>
                </div>

                <div id="box-user-tamu" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_tamu" class="text-xs font-bold text-gray-500">Nama Tamu</label>
                        <input type="text" id="nama_tamu" name="nama_tamu" value="{{ old('nama_tamu') }}" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all" placeholder="Nama pembeli">
                    </div>
                    <div>
                        <label for="no_hp_tamu" class="text-xs font-bold text-gray-500">No. WhatsApp</label>
                        <input type="text" id="no_hp_tamu" name="no_hp_tamu" value="{{ old('no_hp_tamu') }}" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
            </section>

            {{-- 3. Katalog produk --}}
            <section class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div class="flex items-start gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-sm shadow-amber-500/20 shrink-0">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Pilih Produk</p>
                            <p id="katalog-info" class="text-xs text-gray-500 mt-0.5"></p>
                        </div>
                    </div>
                    <input type="search" id="cari-produk" placeholder="Cari nama, SKU, motif, bahan" class="w-full sm:w-72 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all" autocomplete="off">
                </div>

                <div id="chip-kategori" class="flex flex-wrap gap-2 mb-4"></div>

                <div id="katalog" class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-[640px] overflow-y-auto pr-1"></div>
            </section>

            {{-- 4. Pembayaran --}}
            <section class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-sm shadow-amber-500/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Pembayaran</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="status_pembayaran" class="text-xs font-bold text-gray-500">Status</label>
                        <select name="status_pembayaran" id="status_pembayaran" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="lunas" @selected($oldStatus === 'lunas')>Lunas (Bayar Penuh)</option>
                            <option value="dp" @selected($oldStatus === 'dp')>DP (Bayar Sebagian)</option>
                        </select>
                    </div>
                    <div id="box-jumlah-dp" class="hidden">
                        <label for="jumlah_dp" class="text-xs font-bold text-gray-500">Jumlah DP (Rp) <span id="ket-dp-otomatis" class="hidden font-normal text-gray-400">- otomatis</span></label>
                        <input type="number" id="jumlah_dp" name="jumlah_dp" min="1" value="{{ old('jumlah_dp') }}" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>
                    <div>
                        <label for="metode_bayar" class="text-xs font-bold text-gray-500">Metode Bayar</label>
                        <select name="metode_bayar" id="metode_bayar" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="tunai" @selected(old('metode_bayar', 'tunai') === 'tunai')>Tunai</option>
                            <option value="qris" @selected(old('metode_bayar') === 'qris')>QRIS</option>
                            <option value="debit" @selected(old('metode_bayar') === 'debit')>Kartu Debit</option>
                            <option value="transfer" @selected(old('metode_bayar') === 'transfer')>Transfer Bank</option>
                        </select>
                    </div>
                </div>
                <p id="info-dp-sewa" class="hidden mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 ring-1 ring-amber-100">
                    Penyewaan dibayar dengan DP terlebih dahulu. Sisa pembayaran dilunasi saat pelanggan mengembalikan barang sewa.
                </p>
                <div class="mt-4">
                    <label for="catatan" class="text-xs font-bold text-gray-500">Catatan (opsional)</label>
                    <textarea name="catatan" id="catatan" rows="2" maxlength="255" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all" placeholder="Contoh: pelanggan minta penyesuaian ukuran, ambil jam 3 sore">{{ old('catatan') }}</textarea>
                </div>
            </section>
        </div>

        {{-- ================= KOLOM KANAN: KERANJANG ================= --}}
        <aside class="xl:sticky xl:top-6 space-y-4">
            <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between gap-2 px-5 pt-5 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-sm shadow-amber-500/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Keranjang Kasir</p>
                    </div>
                    <span id="badge-item" class="rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-700">0 item</span>
                </div>

                <div id="cart" class="px-5 pb-2 space-y-3 max-h-[52vh] overflow-y-auto"></div>

                <div id="cart-kosong" class="mx-5 mb-5 rounded-2xl border border-dashed border-gray-200 py-8 text-center">
                    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-500 border border-amber-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-500">Keranjang masih kosong</p>
                    <p class="mt-1 text-xs text-gray-400">Pilih produk dari katalog untuk menambahkannya.</p>
                </div>

                <div class="border-t border-gray-100 bg-amber-50/40 px-5 py-4 space-y-2">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Jenis</span><span id="sum-jenis" class="font-bold text-gray-800">-</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Jumlah barang</span><span id="sum-qty" class="font-bold text-gray-800">0</span>
                    </div>
                    <div class="flex items-end justify-between pt-2 border-t border-amber-200/60">
                        <span id="label-total" class="text-[10px] uppercase tracking-wider text-gray-400">Total</span>
                        <span id="total-display" class="text-2xl font-black tabular-nums text-gray-900">Rp 0</span>
                    </div>
                    <div id="sum-bayar" class="hidden space-y-1 pt-1">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span id="label-dibayar">Dibayar sekarang</span><span id="sum-dibayar" class="font-bold text-emerald-700 tabular-nums">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span id="label-sisa">Sisa tagihan</span><span id="sum-sisa" class="font-bold text-rose-600 tabular-nums">Rp 0</span>
                        </div>
                    </div>
                </div>
            </section>

            <div id="pesan-error" role="alert" class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700"></div>

            <button type="submit"
                    class="group-shine relative w-full overflow-hidden bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white px-6 py-3.5 rounded-xl text-sm font-bold shadow-lg shadow-amber-500/20 active:scale-95 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2">
                Simpan Transaksi
            </button>
        </aside>
    </form>
</div>

<script>
    // ================= DATA =================
    const PRODUK = @json($produkJs);
    // Kapasitas & unit terbooking per tanggal (online + kasir): { produk_id: { "ukuran|warna": { kapasitas, hitung: { tanggal: jumlah } } } }
    const KETERSEDIAAN = @json((object) ($ketersediaan ?? []));
    const KATEGORI = @json($kategoriJs);
    const USERS = @json($usersJs);
    const OLD_ITEMS = @json($oldItems);
    const OLD_USER_ID = @json((string) old('user_id', ''));
    const STATUS_BLOKIR = ['dicuci', 'rusak'];

    // Nama warna umum -> hex (kalau yang tersimpan sudah hex, langsung dipakai)
    const WARNA_MAP = {
        'merah': '#b91c1c', 'maroon': '#7f1d1d', 'marun': '#7f1d1d', 'hitam': '#111827', 'putih': '#f9fafb',
        'emas': '#c9a227', 'gold': '#c9a227', 'perak': '#9ca3af', 'silver': '#9ca3af', 'biru': '#1d4ed8',
        'navy': '#1e3a8a', 'hijau': '#15803d', 'kuning': '#eab308', 'oranye': '#ea580c', 'orange': '#ea580c',
        'ungu': '#7e22ce', 'pink': '#ec4899', 'merah muda': '#f472b6', 'coklat': '#78350f', 'cokelat': '#78350f',
        'abu': '#6b7280', 'krem': '#f3e9d2', 'cream': '#f3e9d2', 'tosca': '#0d9488', 'turkis': '#0d9488'
    };

    const state = { kategori: '', q: '', cart: [], dpManual: false };
    const el = (id) => document.getElementById(id);
    const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const rupiah = (n) => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
    const pById = (id) => PRODUK.find((p) => p.id === Number(id));

    // ================= HELPER HARGA / STOK =================
    const jenisAktif = () => (el('radio-sewa').checked ? 'sewa' : 'beli');
    const hargaDasar = (p, j) => (j === 'sewa' ? p.harga_sewa : p.harga_jual);
    // Diskon sewa & jual dipisah, sama seperti hargaUnit() di server (TransaksiController::store)
    const diskonPersen = (p, j) => (j === 'sewa' ? p.diskon_sewa : p.diskon_jual) || 0;
    const hargaFinal = (p, j) => { const d = hargaDasar(p, j); return d - (d * diskonPersen(p, j) / 100); };
    // Ukuran, warna, dan stok mengikuti jenis transaksi: produk dijual & disewa punya stok terpisah.
    function terapkanSet(j) {
        const kunci = j === 'sewa' ? 'sewa' : 'beli';
        PRODUK.forEach((p) => {
            const s = p.set[kunci];
            p.ukuran = s.ukuran;
            p.warna = s.warna;
            p.stok = s.stok;
        });
    }
    const punyaUkuran = (p) => p.ukuran.length > 0;
    // Penyewaan: yang ditampilkan & ditagih di kasir adalah harga DP, sisanya dilunasi saat barang kembali.
    // Kalau DP produk belum diisi (0), dipakai 50% dari harga sewa setelah diskon.
    const dpItem = (p) => {
        const penuh = hargaFinal(p, 'sewa');
        return p.dp > 0 ? Math.min(p.dp, penuh) : Math.round(penuh / 2);
    };
    const sisaItem = (p) => Math.max(0, hargaFinal(p, 'sewa') - dpItem(p));
    const hargaTampil = (p, j) => (j === 'sewa' ? dpItem(p) : hargaFinal(p, j));

    function barisUkuran(p, ukuran) { return p.ukuran.find((x) => x.ukuran === ukuran) || null; }
    function rincianWarna(p, ukuran) {
        const b = barisUkuran(p, ukuran);
        return b && b.warna && b.warna.length ? b.warna : [];
    }
    function punyaRincianWarna(p, ukuran) { return rincianWarna(p, ukuran).length > 0; }
    // Daftar warna yang bisa dipilih: rincian per ukuran kalau ada, selain itu warna produk (stok = null)
    function daftarWarna(p, ukuran) {
        const r = rincianWarna(p, ukuran);
        return r.length ? r : p.warna.map((w) => ({ nama: w, stok: null }));
    }

    // Stok SEWA = unit tersedia pada tanggal yang dipilih (atau hari ini): total unit dikurangi booking terbanyak
    // di rentang itu (online + kasir). Stok beli tetap dari data produk.
    function terbookingSewa(p, ukuran, warna) {
        if (jenisAktif() !== 'sewa') return 0;
        const peta = KETERSEDIAAN[p.id];
        const d = peta && peta[kunciTanggal(p, ukuran, warna)];
        if (!d) return 0;
        const a = el('tanggal_pinjam').value || hariIniIso();
        const b = el('tanggal_kembali_rencana').value || a;
        let maks = 0;
        Object.keys(d.hitung).forEach((t) => { if (t >= a && t <= b && d.hitung[t] > maks) maks = d.hitung[t]; });
        return maks;
    }

    function stokUntuk(p, ukuran, warna) {
        return Math.max(0, stokDasar(p, ukuran, warna) - terbookingSewa(p, ukuran, warna));
    }

    function stokDasar(p, ukuran, warna) {
        if (!punyaUkuran(p)) return p.stok;
        if (!ukuran) return p.ukuran.reduce((a, v) => a + v.stok, 0);
        const v = barisUkuran(p, ukuran);
        if (!v) return 0;
        const r = rincianWarna(p, ukuran);
        if (r.length && warna) {
            const w = r.find((x) => x.nama === warna);
            return w ? w.stok : 0;
        }
        return v.stok;
    }

    function cocokJenis(p, j) { return j === 'sewa' ? p.bisa_disewa : p.dapat_dijual; }

    // ================= STOK REAL-TIME (memperhitungkan isi keranjang) =================
    // Kunci varian: stok dilacak per-ukuran saja kalau produk tidak punya rincian warna per ukuran,
    // atau per ukuran+warna kalau rinciannya ada. Ini harus sama persis dengan logika stokUntuk().
    function kunciVarian(p, ukuran, warna) {
        if (!punyaUkuran(p)) return '';
        return punyaRincianWarna(p, ukuran) ? (ukuran + '|' + warna) : ukuran;
    }

    // Total qty varian ini yang sudah "terpakai" di baris keranjang lain (selain baris excludeIdx).
    function terpakaiDiCart(p, ukuran, warna, excludeIdx) {
        const kunci = kunciVarian(p, ukuran, warna);
        return state.cart.reduce((sum, r, i) => {
            if (i === excludeIdx) return sum;
            if (r.produk_id !== p.id) return sum;
            if (kunciVarian(p, r.ukuran, r.warna) !== kunci) return sum;
            return sum + (r.qty || 0);
        }, 0);
    }

    // Sisa stok varian ini setelah dikurangi yang sudah dipakai di baris keranjang lain.
    // excludeIdx: indeks baris keranjang yang sedang dihitung ulang (supaya tidak menghitung dirinya sendiri sebagai "terpakai").
    function sisaStok(p, ukuran, warna, excludeIdx) {
        return Math.max(0, stokUntuk(p, ukuran, warna) - terpakaiDiCart(p, ukuran, warna, excludeIdx));
    }

    // Apakah ukuran tertentu masih punya sisa stok (di salah satu warnanya, kalau ada rincian warna)?
    function sisaUkuranTersedia(p, ukuranNama, excludeIdx) {
        const r = rincianWarna(p, ukuranNama);
        if (r.length) return r.some((w) => sisaStok(p, ukuranNama, w.nama, excludeIdx) > 0);
        return sisaStok(p, ukuranNama, '', excludeIdx) > 0;
    }

    // Apakah produk ini MASIH punya minimal satu kombinasi ukuran/warna yang sisa stoknya > 0?
    // Dipakai untuk menentukan aktif/tidaknya tombol "+ Tambah" di katalog.
    function adaStokTersisa(p) {
        if (!punyaUkuran(p)) return sisaStok(p, '', '') > 0;
        return p.ukuran.some((v) => sisaUkuranTersedia(p, v.ukuran));
    }

    // Total unit yang masih bisa dipilih untuk produk ini: sudah memperhitungkan tanggal sewa (booking
    // online + kasir) dan isi keranjang, sama dengan yang dipakai pilihan ukuran/warna dan tombol "+ Tambah".
    function totalSisa(p) {
        if (!punyaUkuran(p)) return sisaStok(p, '', '');
        return p.ukuran.reduce((jml, v) => {
            const r = rincianWarna(p, v.ukuran);
            if (r.length) return jml + r.reduce((s, w) => s + sisaStok(p, v.ukuran, w.nama), 0);
            return jml + sisaStok(p, v.ukuran, '');
        }, 0);
    }

    function bisaDipilih(p) { return adaStokTersisa(p) && !STATUS_BLOKIR.includes(p.status); }

    function warnaHex(nama) {
        const n = String(nama || '').trim().toLowerCase();
        if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(n)) return n;
        if (WARNA_MAP[n]) return WARNA_MAP[n];
        const kunci = Object.keys(WARNA_MAP).find((k) => n.includes(k));
        return kunci ? WARNA_MAP[kunci] : null;
    }

    function titikWarna(nama) {
        const hex = warnaHex(nama);
        const label = /^#/.test(nama) ? nama : esc(nama);
        if (!hex) return '<span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">' + label + '</span>';
        return '<span title="' + label + '" class="inline-block h-4 w-4 rounded-full ring-1 ring-gray-300" style="background:' + hex + '"></span>';
    }

    const ikonFoto = '<svg class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';

    // ================= KATALOG =================
    function renderKategori() {
        const semua = [{ id: '', nama: 'Semua' }].concat(KATEGORI);
        el('chip-kategori').innerHTML = semua.map((k) => {
            const aktif = String(state.kategori) === String(k.id);
            const cls = aktif
                ? 'border-amber-600 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-lg shadow-amber-500/20'
                : 'bg-white text-gray-600 border-gray-200 hover:border-amber-400 hover:text-amber-700';
            return '<button type="button" data-kat="' + k.id + '" class="rounded-full border px-3 py-1 text-xs font-semibold transition-all ' + cls + '">' + esc(k.nama) + '</button>';
        }).join('');
    }

    function kartuProduk(p, j) {
        const ok = bisaDipilih(p);
        const final = hargaTampil(p, j);
        const dasar = hargaDasar(p, j);
        const diskon = diskonPersen(p, j) > 0;
        const sewa = j === 'sewa';
        const foto = p.foto
            ? '<img src="' + esc(p.foto) + '" alt="' + esc(p.nama) + '" loading="lazy" class="h-full w-full object-cover">'
            : ikonFoto;

        const ukuran = punyaUkuran(p)
            ? '<div class="flex flex-wrap gap-1">' + p.ukuran.map((v) =>
                '<span class="rounded-md border px-1.5 py-0.5 text-[10px] font-semibold ' + (sisaUkuranTersedia(p, v.ukuran) ? 'border-gray-200 text-gray-600' : 'border-gray-100 text-gray-300 line-through') + '">' + esc(v.ukuran) + '</span>'
              ).join('') + '</div>'
            : '';

        const warna = p.warna.length
            ? '<div class="flex flex-wrap items-center gap-1">' + p.warna.slice(0, 6).map(titikWarna).join('') + (p.warna.length > 6 ? '<span class="text-[10px] text-gray-400">+' + (p.warna.length - 6) + '</span>' : '') + '</div>'
            : '';

        const meta = [p.bahan, p.motif].filter(Boolean).map(esc).join(' &middot; ');
        const badgeStatus = !ok
            ? '<span class="absolute left-2 top-2 rounded-full bg-gray-900/80 px-2 py-0.5 text-[10px] font-bold text-white">' + (!adaStokTersisa(p) ? 'Stok habis' : esc(p.status)) + '</span>'
            : (diskon ? '<span class="absolute left-2 top-2 rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-bold text-white">-' + diskonPersen(p, j) + '%</span>' : '');

        return '<article class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10 ' + (ok ? '' : 'opacity-60') + '">'
            + '<div class="relative flex aspect-[4/5] items-center justify-center overflow-hidden bg-gray-50">' + foto + badgeStatus
            + '<span class="absolute bottom-2 right-2 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold text-gray-700 shadow-sm">Stok ' + totalSisa(p) + '</span></div>'
            + '<div class="flex flex-1 flex-col gap-1.5 p-3">'
            + '<div><p class="line-clamp-2 text-sm font-bold leading-snug text-gray-900">' + esc(p.nama) + '</p>'
            + '<p class="mt-0.5 text-[10px] text-gray-400">' + esc(p.sku || '-') + (p.kategori ? ' &middot; ' + esc(p.kategori) : '') + '</p></div>'
            + (meta ? '<p class="text-[11px] text-gray-500">' + meta + '</p>' : '')
            + ukuran + warna
            + '<div class="mt-auto pt-1">' + (j === 'sewa' ? '<p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">DP Sewa</p>' : '') + '<p class="text-sm font-black tabular-nums text-gray-900">' + rupiah(final) + '</p>'
            + (sewa
                ? '<p class="mt-0.5 text-[10px] tabular-nums text-gray-500">Harga penuh <span class="font-bold text-gray-700">' + rupiah(hargaFinal(p, 'sewa')) + '</span>'
                    + (diskon ? ' <span class="text-gray-400 line-through">' + rupiah(dasar) + '</span>' : '') + '</p>'
                : (diskon ? '<p class="text-[10px] tabular-nums text-gray-400 line-through">' + rupiah(dasar) + '</p>' : ''))
            + '</div>'
            + '<button type="button" data-tambah="' + p.id + '" ' + (ok ? '' : 'disabled') + ' class="mt-1 rounded-xl bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 ring-1 ring-amber-200 transition-all hover:bg-amber-100 active:scale-95 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:ring-gray-200">+ Tambah</button>'
            + '</div></article>';
    }

    function renderKatalog() {
        const j = jenisAktif();
        const q = state.q.trim().toLowerCase();
        const daftar = PRODUK.filter((p) => cocokJenis(p, j))
            .filter((p) => !state.kategori || String(p.kategori_id) === String(state.kategori))
            .filter((p) => !q || [p.nama, p.sku, p.motif, p.bahan, p.kategori].join(' ').toLowerCase().includes(q));

        el('katalog-info').textContent = daftar.length + ' produk tersedia untuk ' + (j === 'sewa' ? 'disewa' : 'dijual');
        el('katalog').innerHTML = daftar.length
            ? daftar.map((p) => kartuProduk(p, j)).join('')
            : '<p class="col-span-full rounded-2xl border border-dashed border-gray-200 py-10 text-center text-xs italic text-gray-400">Tidak ada produk yang cocok.</p>';
    }

    // ================= KERANJANG =================
    function tambahKeKeranjang(id) {
        const p = pById(id);
        if (!p || !bisaDipilih(p)) return; // tombol harusnya sudah disabled, ini jaga-jaga saja

        let ukuran = '';
        let warna = '';

        if (p.ukuran.length === 1) {
            ukuran = p.ukuran[0].ukuran;
        } else if (p.ukuran.length > 1) {
            // Pilih otomatis ukuran PERTAMA yang masih ada sisa stoknya (bukan ukuran pertama apa adanya),
            // supaya kalau ukuran lain sudah habis dipakai di keranjang, sistem lompat ke ukuran yang masih tersedia.
            const calon = p.ukuran.find((v) => sisaUkuranTersedia(p, v.ukuran));
            if (calon) ukuran = calon.ukuran;
        }

        if (ukuran) {
            const r = rincianWarna(p, ukuran);
            if (r.length === 1) {
                warna = r[0].nama;
            } else if (r.length > 1) {
                // Pilih warna pertama yang masih ada sisa stoknya
                const w = r.find((x) => sisaStok(p, ukuran, x.nama) > 0);
                if (w) warna = w.nama;
            }
        }

        const ada = state.cart.find((r) => r.produk_id === p.id && r.ukuran === ukuran && r.warna === warna);
        if (ada) {
            const idx = state.cart.indexOf(ada);
            const sisa = sisaStok(p, ada.ukuran, ada.warna, idx); // batas maksimum baris ini (sudah tidak termasuk dirinya sendiri)
            if (ada.qty < sisa) ada.qty += 1;
            // kalau ada.qty sudah >= sisa, berarti varian ini sudah penuh dipakai -> tidak ditambah lagi
        } else {
            if (sisaStok(p, ukuran, warna) <= 0) return; // varian pilihan ini sudah habis, jangan buat baris baru
            state.cart.push({ produk_id: p.id, ukuran: ukuran, warna: warna, qty: 1 });
        }
        renderCart();
    }

    function barisCart(r, idx, j) {
        const p = pById(r.produk_id);
        if (!p) return '';
        const final = hargaTampil(p, j);
        const dasar = hargaDasar(p, j);
        const tampilDiskon = j !== 'sewa' && diskonPersen(p, j) > 0;
        // Batas qty baris ini = sisa stok varian setelah dikurangi baris keranjang LAIN (bukan dirinya sendiri)
        const stokMaks = Math.max(1, sisaStok(p, r.ukuran, r.warna, idx));
        const perluUkuran = punyaUkuran(p) && !r.ukuran;
        const rinci = punyaRincianWarna(p, r.ukuran);
        const perluWarna = rinci && !r.warna;
        const daftar = daftarWarna(p, r.ukuran);

        const foto = p.foto
            ? '<img src="' + esc(p.foto) + '" alt="" class="h-full w-full object-cover">'
            : '<span class="scale-75">' + ikonFoto + '</span>';

        const selUkuran = punyaUkuran(p)
            ? '<div><label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Ukuran</label>'
              + '<select name="items[' + idx + '][ukuran]" data-f="ukuran" class="mt-0.5 w-full rounded-lg border px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all ' + (perluUkuran ? 'border-rose-300 bg-rose-50' : 'border-gray-200') + '">'
              + '<option value="">Pilih ukuran</option>'
              + p.ukuran.map((v) => {
                    // Ukuran yang sedang dipakai baris ini selalu boleh dipilih (dihitung via stokMaks di atas);
                    // ukuran lain baru dicek sisanya tanpa mengecualikan baris ini (karena baris ini belum memakainya).
                    const tersedia = v.ukuran === r.ukuran ? true : sisaUkuranTersedia(p, v.ukuran, idx);
                    return '<option value="' + esc(v.ukuran) + '" ' + (tersedia ? '' : 'disabled') + ' ' + (v.ukuran === r.ukuran ? 'selected' : '') + '>' + esc(v.ukuran) + ' (stok ' + stokUntuk(p, v.ukuran, '') + ')</option>';
                }).join('')
              + '</select></div>'
            : '<input type="hidden" name="items[' + idx + '][ukuran]" value="">';

        const selWarna = daftar.length
            ? '<div><label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Warna</label>'
              + '<select name="items[' + idx + '][warna]" data-f="warna" class="mt-0.5 w-full rounded-lg border px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all ' + (perluWarna ? 'border-rose-300 bg-rose-50' : 'border-gray-200') + '">'
              + '<option value="">' + (rinci ? 'Pilih warna' : 'Sesuai produk') + '</option>'
              + daftar.map((w) => {
                    const tersedia = w.nama === r.warna ? true : (!rinci || sisaStok(p, r.ukuran, w.nama, idx) > 0);
                    return '<option value="' + esc(w.nama) + '" ' + (tersedia ? '' : 'disabled') + ' ' + (w.nama === r.warna ? 'selected' : '') + '>'
                        + esc(w.nama) + (w.stok !== null ? ' (stok ' + stokUntuk(p, r.ukuran, w.nama) + ')' : '') + '</option>';
                }).join('')
              + '</select></div>'
            : '<input type="hidden" name="items[' + idx + '][warna]" value="">';

        const info = [];
        if (p.bahan) info.push('Bahan: ' + esc(p.bahan));
        if (p.motif) info.push('Motif: ' + esc(p.motif));
        if (p.berat) info.push(p.berat + ' kg');
        if (j === 'sewa') {
            if (p.denda > 0) info.push('Denda telat ' + rupiah(p.denda) + '/hari');
        }

        const bermasalah = perluUkuran || perluWarna;
        const sisaStokInfo = (r.ukuran || !punyaUkuran(p)) && (!rinci || r.warna)
            ? '<p class="mt-1.5 text-[11px] font-semibold text-gray-500">Sisa stok pilihan ini: <span class="tabular-nums text-gray-800">' + stokMaks + '</span></p>'
            : '';

        return '<div data-idx="' + idx + '" class="rounded-2xl border ' + (bermasalah ? 'border-rose-200' : 'border-gray-100') + ' bg-gray-50/50 p-3">'
            + '<input type="hidden" name="items[' + idx + '][produk_id]" value="' + p.id + '">'
            + '<div class="flex gap-3">'
            + '<div class="flex h-16 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-white">' + foto + '</div>'
            + '<div class="min-w-0 flex-1"><p class="truncate text-sm font-bold text-gray-900">' + esc(p.nama) + '</p>'
            + '<p class="text-[10px] text-gray-400">' + esc(p.sku || '-') + (p.kategori ? ' &middot; ' + esc(p.kategori) : '') + '</p>'
            + '<p class="mt-0.5 text-xs text-gray-600 tabular-nums">' + (j === 'sewa' ? 'DP ' : '') + rupiah(final) + (tampilDiskon ? ' <span class="text-gray-400 line-through">' + rupiah(dasar) + '</span>' : '') + '</p></div>'
            + '<button type="button" data-act="hapus" aria-label="Hapus ' + esc(p.nama) + '" class="h-7 w-7 shrink-0 rounded-lg text-gray-400 transition hover:bg-rose-50 hover:text-rose-600">&times;</button>'
            + '</div>'
            + '<div class="mt-3 grid grid-cols-2 gap-2">' + selUkuran + selWarna + '</div>'
            + (perluUkuran ? '<p class="mt-1.5 text-[11px] font-semibold text-rose-600">Pilih ukuran dulu agar stok tercatat benar.</p>' : '')
            + (!perluUkuran && perluWarna ? '<p class="mt-1.5 text-[11px] font-semibold text-rose-600">Pilih warna agar stok kombinasi ukuran dan warna tercatat benar.</p>' : '')
            + sisaStokInfo
            + (info.length ? '<p class="mt-2 text-[10px] leading-relaxed text-gray-400">' + info.join(' &middot; ') + '</p>' : '')
            + '<div class="mt-3 flex items-center justify-between">'
            + '<div class="inline-flex items-center rounded-lg border border-gray-200 bg-white">'
            + '<button type="button" data-act="kurang" aria-label="Kurangi" class="h-8 w-8 text-sm font-bold text-gray-500 hover:text-amber-600 transition-colors">&minus;</button>'
            + '<input type="number" name="items[' + idx + '][qty]" data-f="qty" value="' + r.qty + '" min="1" max="' + stokMaks + '" class="h-8 w-12 border-x border-gray-200 text-center text-xs font-bold tabular-nums focus:outline-none">'
            + '<button type="button" data-act="tambah" aria-label="Tambah" class="h-8 w-8 text-sm font-bold text-gray-500 hover:text-amber-600 transition-colors">+</button>'
            + '</div>'
            + '<div class="text-right"><p class="text-[10px] uppercase tracking-wider text-gray-400">' + (j === 'sewa' ? 'Subtotal DP' : 'Subtotal') + '</p><p class="text-sm font-black tabular-nums text-gray-900">' + rupiah(final * r.qty) + '</p></div>'
            + '</div></div>';
    }

    function hitungRingkasan() {
        const j = jenisAktif();
        // total = nilai transaksi penuh (dikirim ke server); totalDp & sisaSewa hanya bermakna untuk sewa
        let total = 0, qty = 0, totalDp = 0, sisaSewa = 0;
        state.cart.forEach((r) => {
            const p = pById(r.produk_id);
            if (!p) return;
            total += hargaFinal(p, j) * r.qty;
            qty += r.qty;
            if (j === 'sewa') {
                totalDp += dpItem(p) * r.qty;
                sisaSewa += sisaItem(p) * r.qty;
            }
        });
        return { total, qty, totalDp, sisaSewa };
    }

    function renderCart() {
        const j = jenisAktif();
        el('cart').innerHTML = state.cart.map((r, i) => barisCart(r, i, j)).join('');
        el('cart-kosong').classList.toggle('hidden', state.cart.length > 0);
        el('badge-item').textContent = state.cart.length + ' item';
        cekBentrokTanggal();
        renderKalender();
        renderKatalog(); // refresh tombol "+ Tambah": nonaktif kalau semua varian produk sudah penuh dipakai di keranjang
        updateRingkasan();
    }

    function updateRingkasan() {
        const j = jenisAktif();
        const sewa = j === 'sewa';
        const { total, qty, totalDp, sisaSewa } = hitungRingkasan();
        el('sum-jenis').textContent = sewa ? 'Penyewaan' : 'Pembelian';
        el('sum-qty').textContent = qty;

        const inputDp = el('jumlah_dp');
        let dibayar, sisa;

        if (sewa) {
            // Sewa: total yang tampil = total DP; DP otomatis dan terkunci
            el('total-display').textContent = rupiah(totalDp);
            el('label-total').textContent = 'Total DP';
            el('label-dibayar').textContent = 'DP dibayar sekarang';
            el('label-sisa').textContent = 'Sisa (dilunasi saat barang kembali)';
            inputDp.value = totalDp > 0 ? Math.round(totalDp) : '';
            dibayar = totalDp;
            sisa = sisaSewa;
            el('sum-bayar').classList.toggle('hidden', state.cart.length === 0);
        } else {
            el('total-display').textContent = rupiah(total);
            el('label-total').textContent = 'Total';
            el('label-dibayar').textContent = 'Dibayar sekarang';
            el('label-sisa').textContent = 'Sisa tagihan';
            const isDp = el('status_pembayaran').value === 'dp';
            if (isDp && !state.dpManual) {
                const saran = total > 0 ? Math.floor(total / 2) : 0;
                inputDp.value = saran > 0 ? Math.round(saran) : '';
            }
            dibayar = isDp ? (parseFloat(inputDp.value) || 0) : total;
            sisa = Math.max(0, total - dibayar);
            el('sum-bayar').classList.toggle('hidden', !isDp);
        }

        el('sum-dibayar').textContent = rupiah(dibayar);
        el('sum-sisa').textContent = rupiah(sisa);
    }

    // Update kecil tanpa re-render penuh saat qty diketik (menjaga fokus input)
    function sinkronQty(idx, nilai) {
        const r = state.cart[idx];
        const p = pById(r.produk_id);
        const maks = Math.max(1, sisaStok(p, r.ukuran, r.warna, idx));
        r.qty = Math.min(Math.max(1, parseInt(nilai, 10) || 1), maks);
    }

    el('cart').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-act]');
        if (!btn) return;
        const idx = Number(btn.closest('[data-idx]').dataset.idx);
        const r = state.cart[idx];
        if (btn.dataset.act === 'hapus') state.cart.splice(idx, 1);
        if (btn.dataset.act === 'tambah') sinkronQty(idx, r.qty + 1);
        if (btn.dataset.act === 'kurang') sinkronQty(idx, r.qty - 1);
        renderCart();
    });

    el('cart').addEventListener('change', (e) => {
        const f = e.target.dataset.f;
        if (!f) return;
        const idx = Number(e.target.closest('[data-idx]').dataset.idx);
        const r = state.cart[idx];
        const p = pById(r.produk_id);
        if (f === 'qty') sinkronQty(idx, e.target.value);
        if (f === 'ukuran') {
            r.ukuran = e.target.value;
            // Warna yang dipilih harus ada di ukuran baru, kalau tidak dikosongkan
            if (!daftarWarna(p, r.ukuran).some((w) => w.nama === r.warna)) r.warna = '';
            // Kalau ukuran baru hanya punya satu warna, pilih otomatis
            const rinci = rincianWarna(p, r.ukuran);
            if (!r.warna && rinci.length === 1) r.warna = rinci[0].nama;
            sinkronQty(idx, r.qty);
        }
        if (f === 'warna') { r.warna = e.target.value; sinkronQty(idx, r.qty); }
        renderCart();
    });

    // ================= KATALOG: EVENT =================
    el('katalog').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-tambah]');
        if (btn) tambahKeKeranjang(btn.dataset.tambah);
    });
    el('chip-kategori').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-kat]');
        if (!btn) return;
        state.kategori = btn.dataset.kat;
        renderKategori();
        renderKatalog();
    });
    el('cari-produk').addEventListener('input', (e) => { state.q = e.target.value; renderKatalog(); });

    // ================= JENIS TRANSAKSI & TANGGAL =================
    // Kunci kombinasi: sama dengan Support\KetersediaanSewa::kunci() di server
    function kunciTanggal(p, ukuran, warna) {
        const s = p.set.sewa;
        if (!s.ukuran.length) return '|';
        const b = s.ukuran.find((x) => x.ukuran === ukuran);
        if (b && b.warna && b.warna.length && warna && b.warna.some((w) => w.nama === warna)) return ukuran + '|' + warna;
        return (ukuran || '') + '|';
    }
    const tglPendek = (t) => new Date(t + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });

    // Data kalender satu item keranjang: kapasitas & unit terbooking per tanggal untuk ukuran/warna-nya
    function dataItem(r) {
        const p = pById(r.produk_id);
        const peta = p && KETERSEDIAAN[p.id];
        return peta ? (peta[kunciTanggal(p, r.ukuran, r.warna)] || null) : null;
    }
    // Tanggal tidak cukup unit: terpakai + jumlah yang diminta melebihi kapasitas
    const kurangUnit = (d, qty, t) => (d.hitung[t] || 0) + qty > d.kapasitas;

    // Peringatan bila rentang sewa menabrak tanggal yang sudah penuh dibooking (online maupun kasir).
    // (Server tetap memeriksa ulang saat disimpan.)
    function cekBentrokTanggal() {
        const box = el('info-bentrok');
        const pesan = [];
        if (jenisAktif() === 'sewa') {
            const a = el('tanggal_pinjam').value, b = el('tanggal_kembali_rencana').value;
            if (a && b && b >= a) {
                state.cart.forEach((r) => {
                    const d = dataItem(r);
                    if (!d) return;
                    const qty = Math.max(1, Number(r.qty) || 1);
                    const penuh = Object.keys(d.hitung).filter((t) => t >= a && t <= b && kurangUnit(d, qty, t)).sort();
                    if (!penuh.length) return;
                    const p = pById(r.produk_id);
                    const varian = [r.ukuran, r.warna].filter(Boolean).join(', ');
                    const daftar = penuh.slice(0, 4).map(tglPendek).join(', ') + (penuh.length > 4 ? ' +' + (penuh.length - 4) + ' lainnya' : '');
                    pesan.push('"' + p.nama + '"' + (varian ? ' (' + varian + ')' : '') + ' tidak cukup unit kosong (' + qty + ' unit) pada ' + daftar + '.');
                });
            }
        }
        box.innerHTML = pesan.length
            ? '<p class="mb-1 font-bold">Tanggal bentrok dengan booking lain. Ganti tanggal atau pilih ukuran/warna lain:</p>' + pesan.map((m) => '<p>' + esc(m) + '</p>').join('')
            : '';
        box.classList.toggle('hidden', !pesan.length);
        return pesan;
    }

    // ---------- Kalender rentang sewa (mengikuti kalender halaman detail produk) ----------
    const kal = { bulan: 0, tahun: 0 };
    const pad2 = (n) => String(n).padStart(2, '0');
    const isoTgl = (y, m, d) => y + '-' + pad2(m + 1) + '-' + pad2(d);
    const hariIniIso = () => { const t = new Date(); return isoTgl(t.getFullYear(), t.getMonth(), t.getDate()); };
    const tglPanjang = (t) => new Date(t + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

    // Per tanggal untuk semua item keranjang: 'penuh' (tidak cukup unit) atau 'sebagian' (ada yang terbooking, masih cukup)
    function statusTanggalKeranjang() {
        const penuh = new Set(), sebagian = new Map();
        if (jenisAktif() !== 'sewa') return { penuh, sebagian };
        state.cart.forEach((r) => {
            const d = dataItem(r);
            if (!d) return;
            const qty = Math.max(1, Number(r.qty) || 1);
            Object.keys(d.hitung).forEach((t) => {
                if (kurangUnit(d, qty, t)) penuh.add(t);
                else if (!sebagian.has(t)) sebagian.set(t, d.hitung[t] + ' dari ' + d.kapasitas + ' unit sudah dibooking');
            });
        });
        return { penuh, sebagian };
    }

    // ---------- Rentang sewa: ambil -> dipakai -> kembali (sama dengan kalender halaman detail produk) ----------
    const tambahHari = (t, n) => { const d = new Date(t + 'T00:00:00Z'); d.setUTCDate(d.getUTCDate() + n); return isoTgl(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()); };
    const pendek = (t) => new Date(t + 'T00:00:00Z').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', timeZone: 'UTC' });

    // Booking yang sudah ada untuk SEMUA item di keranjang (sewa), tanpa duplikat kombinasi yang sama
    function rentangKeranjang() {
        const hasil = [], terlihat = new Set();
        if (jenisAktif() !== 'sewa') return hasil;
        state.cart.forEach((r) => {
            const p = pById(r.produk_id), d = dataItem(r);
            if (!p || !d) return;
            const kunci = p.id + '|' + kunciTanggal(p, r.ukuran, r.warna);
            if (terlihat.has(kunci)) return;
            terlihat.add(kunci);
            const varian = [r.ukuran, r.warna].filter(Boolean).join(', ');
            (d.rentang || []).forEach((x) => hasil.push(Object.assign({ nama: p.nama, varian }, x)));
        });
        return hasil;
    }

    // Contoh: "Diambil 1 Nov · dipakai 2 Nov · dikembalikan 3 Nov"
    function rincianRentang(mulai, selesai, terlambat, rencana) {
        if (terlambat) return 'Diambil ' + pendek(mulai) + ' · belum dikembalikan (jadwal kembali ' + pendek(rencana) + ')';
        if (mulai === selesai) return 'Diambil & dikembalikan ' + pendek(mulai);
        const a = tambahHari(mulai, 1), b = tambahHari(selesai, -1);
        const pakai = a > b ? '' : (a === b ? ' · dipakai ' + pendek(a) : ' · dipakai ' + pendek(a) + '–' + pendek(b));
        return 'Diambil ' + pendek(mulai) + pakai + ' · dikembalikan ' + pendek(selesai);
    }

    // Peran tanggal t di tiap booking yang sudah ada
    function perananBooking(t, daftar) {
        const hasil = [];
        daftar.forEach((x) => {
            if (t < x.mulai || t > x.selesai) return;
            let peran;
            if (x.terlambat && t === x.selesai) peran = 'belum kembali';
            else if (x.mulai === x.selesai) peran = 'ambil & kembali';
            else if (t === x.mulai) peran = 'ambil';
            else if (t === x.selesai) peran = 'kembali';
            else peran = 'dipakai';
            hasil.push({ peran, x });
        });
        return hasil;
    }

    function renderJadwal(daftar, a, b) {
        const box = el('kal-jadwal');
        if (!box) return;
        let h = '<p class="text-[11px] font-semibold text-gray-700">Jadwal sewa</p>';
        if (a && b && b >= a) {
            h += '<p class="rounded-lg bg-amber-50 px-2.5 py-1.5 text-gray-700"><b>Pilihan:</b> ' + esc(rincianRentang(a, b, false, null)) + '</p>';
        }
        if (!state.cart.length) {
            h += '<p>Tambahkan produk ke keranjang untuk melihat jadwal sewanya.</p>';
        } else if (!daftar.length) {
            h += '<p>Belum ada jadwal sewa lain untuk produk di keranjang.</p>';
        } else {
            daftar.forEach((x) => {
                h += '<p class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-rose-700"><b>' + esc(x.nama) + '</b>' + (x.varian ? ' (' + esc(x.varian) + ')' : '')
                    + '<br><b>' + esc(tglPanjang(x.mulai) + ' – ' + tglPanjang(x.selesai)) + '</b> · ' + x.jumlah + ' unit disewa'
                    + '<br>' + esc(rincianRentang(x.mulai, x.selesai, x.terlambat, x.rencana)) + '</p>';
            });
        }
        box.innerHTML = h;
    }

    function renderKalender() {
        const grid = el('kal-grid');
        if (!grid || !kal.tahun) return;
        const a = el('tanggal_pinjam').value, b = el('tanggal_kembali_rencana').value;
        const { penuh, sebagian } = statusTanggalKeranjang();
        const hariIni = hariIniIso();
        const daftarRentang = rentangKeranjang();
        renderJadwal(daftarRentang, a, b);

        el('kal-judul').textContent = new Date(Date.UTC(kal.tahun, kal.bulan, 1))
            .toLocaleDateString('id-ID', { month: 'long', year: 'numeric', timeZone: 'UTC' });

        const sekarang = new Date();
        el('kal-mundur').disabled = kal.tahun < sekarang.getFullYear() || (kal.tahun === sekarang.getFullYear() && kal.bulan <= sekarang.getMonth());

        const offset = (new Date(Date.UTC(kal.tahun, kal.bulan, 1)).getUTCDay() + 6) % 7;
        let sel = [];
        for (let i = 0; i < 42; i++) {
            const d = new Date(Date.UTC(kal.tahun, kal.bulan, 1 - offset + i));
            sel.push({ t: isoTgl(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()), hari: d.getUTCDate(), luar: d.getUTCMonth() !== kal.bulan });
        }
        if (sel.slice(35).every((c) => c.luar)) sel = sel.slice(0, 35);

        grid.innerHTML = sel.map((c) => {
            const lalu = c.t < hariIni, isPenuh = penuh.has(c.t), isSebagian = sebagian.has(c.t);
            const dipilih = c.t === a || c.t === b;
            const dalam = a && b && c.t > a && c.t < b;
            let kelas = 'flex h-11 flex-col items-center justify-center leading-none mx-px rounded-lg text-xs font-medium transition-all duration-200 ';
            let mati = false, judul = '';
            if (dipilih) kelas += 'relative z-10 scale-105 bg-amber-600 font-bold text-white shadow-md';
            else if (dalam) kelas += 'bg-amber-100 font-semibold text-amber-900';
            else if (c.luar || lalu) { kelas += 'cursor-default text-gray-300'; mati = true; }
            else if (isPenuh) { kelas += 'cursor-not-allowed bg-rose-100 text-rose-500 line-through decoration-rose-400'; mati = true; judul = 'Sedang disewa, tidak tersedia'; }
            else if (isSebagian) { kelas += 'bg-sky-50 text-sky-700 ring-1 ring-sky-200 hover:bg-sky-100 hover:scale-110 active:scale-95'; judul = sebagian.get(c.t); }
            else kelas += 'text-gray-700 hover:scale-110 hover:bg-amber-50 active:scale-95' + (c.t === hariIni ? ' ring-1 ring-amber-400' : '');
            // Tulisan kecil: ambil / dipakai / kembali (pilihan sekarang lebih dulu, lalu booking yang sudah ada)
            const peran = (c.luar || lalu) ? [] : perananBooking(c.t, daftarRentang);
            let label = '';
            if (!c.luar && !lalu) {
                if (a && b && c.t === a && c.t === b) label = '1 hari';
                else if (c.t === a) label = 'ambil';
                else if (c.t === b) label = 'kembali';
                else if (dalam) label = 'dipakai';
                else if (peran.length > 1) label = peran.length + ' jadwal';
                else if (peran.length === 1) label = ({ 'ambil': 'ambil', 'kembali': 'kembali', 'dipakai': 'dipakai', 'belum kembali': 'telat', 'ambil & kembali': '1 hari' })[peran[0].peran];
            }
            const baris = [];
            if (judul) baris.push(judul);
            peran.forEach((q) => baris.push(q.x.jumlah + ' unit: ' + q.peran + ' (' + pendek(q.x.mulai) + ' – ' + pendek(q.x.selesai) + ')'));
            const judulLengkap = baris.map(esc).join('&#10;');
            return '<button type="button" data-t="' + c.t + '" class="' + kelas + '"' + (judulLengkap ? ' title="' + judulLengkap + '"' : '') + ((mati && !dipilih) || c.luar ? ' disabled' : '') + '>'
                + '<span>' + c.hari + '</span>' + (label ? '<span class="mt-0.5 text-[8px] font-semibold uppercase tracking-tight opacity-80">' + label + '</span>' : '') + '</button>';
        }).join('');

        el('kal-ambil').textContent = a ? tglPanjang(a) : 'Klik tanggal';
        el('kal-kembali').textContent = b ? tglPanjang(b) : '-';
        el('kal-durasi').textContent = a && b ? (Math.round((new Date(b) - new Date(a)) / 86400000) + 1) + ' hari' : '-';
        el('kal-hint').classList.toggle('hidden', state.cart.length > 0);
    }

    function pilihTanggalKasir(t) {
        const { penuh } = statusTanggalKeranjang();
        if (t < hariIniIso() || penuh.has(t)) return;
        const A = el('tanggal_pinjam'), B = el('tanggal_kembali_rencana');
        const a = A.value, b = B.value;

        if (!a || b || t < a) { A.value = t; B.value = ''; }                       // mulai rentang baru
        else if (t === a) { B.value = t; }                                          // tanggal yang sama = sewa 1 hari
        else if ([...penuh].some((x) => x > a && x < t)) { A.value = t; B.value = ''; } // rentang menabrak tanggal penuh
        else { B.value = t; }

        // Stok sewa bergantung pada tanggal: segarkan batas jumlah & label stok di keranjang
        state.cart.forEach((r, idx) => {
            const p = pById(r.produk_id);
            if (p) r.qty = Math.max(1, Math.min(r.qty, sisaStok(p, r.ukuran, r.warna, idx) || 1));
        });
        hitungDurasi();
        renderCart();
    }

    function hitungDurasi() {
        cekBentrokTanggal();
        renderKalender();
        const a = el('tanggal_pinjam').value, b = el('tanggal_kembali_rencana').value;
        const out = el('info-durasi');
        if (!a || !b) { out.textContent = ''; return; }
        const hari = Math.round((new Date(b) - new Date(a)) / 86400000);
        out.textContent = hari < 0 ? 'Tanggal kembali tidak boleh sebelum tanggal ambil.' : 'Lama sewa: ' + (hari + 1) + ' hari (' + hari + ' malam)';
    }

    // Sewa: status dikunci ke DP (opsi Lunas dinonaktifkan), jumlah DP otomatis dari harga DP produk
    function aturPembayaranSewa(sewa) {
        const status = el('status_pembayaran');
        const dp = el('jumlah_dp');
        status.querySelector('option[value="lunas"]').disabled = sewa;
        if (sewa) status.value = 'dp';
        dp.readOnly = sewa;
        dp.classList.toggle('bg-gray-100', sewa);
        el('ket-dp-otomatis').classList.toggle('hidden', !sewa);
        el('info-dp-sewa').classList.toggle('hidden', !sewa);
        toggleDp();
    }

    function ubahJenis() {
        const sewa = jenisAktif() === 'sewa';
        el('box-tanggal-sewa').classList.toggle('hidden', !sewa);
        aturPembayaranSewa(sewa);

        // Ganti set stok sesuai jenis (beli/sewa), lalu buang item yang tidak boleh untuk jenis ini
        terapkanSet(jenisAktif());
        state.cart = state.cart.filter((r) => { const p = pById(r.produk_id); return p && cocokJenis(p, jenisAktif()); });
        // Ukuran/warna/jumlah item bisa tidak ada di set stok yang baru -> reset yang tidak cocok
        state.cart.forEach((r, idx) => {
            const p = pById(r.produk_id);
            if (r.ukuran && !barisUkuran(p, r.ukuran)) r.ukuran = '';
            if (r.warna && !daftarWarna(p, r.ukuran).some((w) => w.nama === r.warna)) r.warna = '';
            r.qty = Math.max(1, Math.min(r.qty, sisaStok(p, r.ukuran, r.warna, idx) || 1));
        });
        state.dpManual = false;
        renderKatalog();
        renderCart();
    }

    document.querySelectorAll('input[name="jenis"]').forEach((r) => r.addEventListener('change', ubahJenis));
    el('kal-grid').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-t]');
        if (btn && !btn.disabled) pilihTanggalKasir(btn.dataset.t);
    });
    el('kal-mundur').addEventListener('click', () => {
        if (kal.bulan === 0) { kal.bulan = 11; kal.tahun--; } else { kal.bulan--; }
        renderKalender();
    });
    el('kal-maju').addEventListener('click', () => {
        if (kal.bulan === 11) { kal.bulan = 0; kal.tahun++; } else { kal.bulan++; }
        renderKalender();
    });

    // ================= PELANGGAN =================
    function renderUsers(filter, pilihId) {
        const f = (filter || '').toLowerCase();
        const sel = el('select-user');
        const aktif = pilihId !== undefined ? String(pilihId) : sel.value;
        const daftar = USERS.filter((u) => !f || (u.name + ' ' + (u.no_hp || '')).toLowerCase().includes(f));
        sel.innerHTML = '<option value="">-- Pilih Pelanggan (' + daftar.length + ') --</option>'
            + daftar.map((u) => '<option value="' + u.id + '" ' + (String(u.id) === aktif ? 'selected' : '') + '>' + esc(u.name) + (u.no_hp ? ' - ' + esc(u.no_hp) : '') + '</option>').join('');
        tampilKartuUser();
    }

    function tampilKartuUser() {
        const u = USERS.find((x) => String(x.id) === el('select-user').value);
        const kartu = el('kartu-user');
        kartu.classList.toggle('hidden', !u);
        kartu.classList.toggle('flex', !!u);
        if (!u) return;
        el('user-inisial').textContent = (u.name || '?').trim().charAt(0).toUpperCase();
        el('user-nama').textContent = u.name;
        el('user-hp').textContent = u.no_hp || 'Nomor HP belum diisi';
    }

    function toggleCustomer() {
        const terdaftar = document.querySelector('input[name="customer_type"]:checked').value === 'terdaftar';
        el('box-user-terdaftar').classList.toggle('hidden', !terdaftar);
        el('box-user-tamu').classList.toggle('hidden', terdaftar);
    }

    el('cari-user').addEventListener('input', (e) => renderUsers(e.target.value));
    el('select-user').addEventListener('change', tampilKartuUser);
    document.querySelectorAll('input[name="customer_type"]').forEach((r) => r.addEventListener('change', toggleCustomer));

    // ================= PEMBAYARAN =================
    function toggleDp() {
        const isDp = el('status_pembayaran').value === 'dp';
        el('box-jumlah-dp').classList.toggle('hidden', !isDp);
        if (!isDp) state.dpManual = false;
        updateRingkasan();
    }
    el('status_pembayaran').addEventListener('change', toggleDp);
    el('jumlah_dp').addEventListener('input', () => { state.dpManual = true; updateRingkasan(); });

    // ================= UPLOAD KTP =================
    const KTP_MAKS = 2 * 1024 * 1024;
    function resetKtp() {
        el('foto_ktp').value = '';
        el('preview-ktp').classList.add('hidden');
        el('preview-ktp').classList.remove('flex');
        el('error-ktp').classList.add('hidden');
    }
    el('foto_ktp').addEventListener('change', (e) => {
        const f = e.target.files[0];
        const err = el('error-ktp');
        if (!f) { resetKtp(); return; }
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(f.type)) {
            resetKtp(); err.textContent = 'Format harus JPG, PNG, atau WEBP.'; err.classList.remove('hidden'); return;
        }
        if (f.size > KTP_MAKS) {
            resetKtp(); err.textContent = 'Ukuran foto KTP maksimal 2 MB.'; err.classList.remove('hidden'); return;
        }
        err.classList.add('hidden');
        el('preview-ktp-img').src = URL.createObjectURL(f);
        el('preview-ktp').classList.remove('hidden');
        el('preview-ktp').classList.add('flex');
    });
    el('hapus-ktp').addEventListener('click', resetKtp);

    // ================= VALIDASI SEBELUM KIRIM =================
    el('form-kasir').addEventListener('submit', (e) => {
        const err = [];
        const { total } = hitungRingkasan();
        const sewaAktif = jenisAktif() === 'sewa';

        if (!state.cart.length) err.push('Tambahkan minimal satu produk ke keranjang.');
        state.cart.forEach((r) => {
            const p = pById(r.produk_id);
            if (p && punyaUkuran(p) && !r.ukuran) err.push('Pilih ukuran untuk "' + p.nama + '".');
            if (p && r.ukuran && punyaRincianWarna(p, r.ukuran) && !r.warna) err.push('Pilih warna untuk "' + p.nama + '" ukuran ' + r.ukuran + '.');
        });

        if (jenisAktif() === 'sewa') {
            const a = el('tanggal_pinjam').value, b = el('tanggal_kembali_rencana').value;
            if (!a || !b) err.push('Isi tanggal ambil dan tanggal kembali.');
            else if (b < a) err.push('Tanggal kembali tidak boleh sebelum tanggal ambil.');
            else cekBentrokTanggal().forEach((m) => err.push(m));
            if (!el('foto_ktp').files.length) err.push('Upload foto KTP penyewa.');
            if (!el('ktp_sesuai').checked) err.push('Centang konfirmasi bahwa NIK & nama di KTP sudah sesuai dengan penyewa.');

            const deposit = parseFloat(el('jumlah_deposit').value) || 0;
            const errDeposit = el('error-deposit');
            if (deposit <= 0) {
                err.push('Isi jumlah deposit jaminan.');
                errDeposit.textContent = 'Jumlah deposit wajib diisi.';
                errDeposit.classList.remove('hidden');
            } else {
                errDeposit.classList.add('hidden');
            }
        }

        const tipe = document.querySelector('input[name="customer_type"]:checked').value;
        if (tipe === 'terdaftar' && !el('select-user').value) err.push('Pilih pelanggan terdaftar.');
        if (tipe === 'tamu' && (!el('nama_tamu').value.trim() || !el('no_hp_tamu').value.trim())) err.push('Isi nama dan nomor WhatsApp tamu.');

        if (!sewaAktif && el('status_pembayaran').value === 'dp') {
            const dp = parseFloat(el('jumlah_dp').value) || 0;
            if (dp <= 0) err.push('Isi jumlah DP.');
            else if (dp >= total) err.push('Jumlah DP harus lebih kecil dari total. Pilih status Lunas jika dibayar penuh.');
        }

        const box = el('pesan-error');
        if (err.length) {
            e.preventDefault();
            box.innerHTML = err.map((m) => '<p>' + esc(m) + '</p>').join('');
            box.classList.remove('hidden');
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            box.classList.add('hidden');
            // Cegah klik ganda: sekali kirim = satu transaksi
            const tombol = e.target.querySelector('button[type="submit"]');
            tombol.disabled = true;
            tombol.textContent = 'Menyimpan...';
        }
    });

    // ================= INISIALISASI =================
    (function init() {
        const dpAwal = el('jumlah_dp').value; // nilai old() dari server
        // Pulihkan keranjang kalau validasi server gagal
        state.cart = OLD_ITEMS.filter((r) => pById(r.produk_id));

        if (!el('tanggal_pinjam').value) {
            const t = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            el('tanggal_pinjam').value = t.getFullYear() + '-' + pad(t.getMonth() + 1) + '-' + pad(t.getDate());
        }
        // Kalender mulai di bulan tanggal yang terpilih (atau bulan ini)
        const awal = el('tanggal_pinjam').value.split('-').map(Number);
        kal.tahun = awal[0] || new Date().getFullYear();
        kal.bulan = awal[1] ? awal[1] - 1 : new Date().getMonth();

        renderUsers('', OLD_USER_ID);
        renderKategori();
        toggleCustomer();
        ubahJenis();
        hitungDurasi();
        toggleDp();
        if (dpAwal) { el('jumlah_dp').value = dpAwal; state.dpManual = true; }
        renderCart();
    })();
</script>
@endsection