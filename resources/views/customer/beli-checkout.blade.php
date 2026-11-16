@extends('layouts.public')

@section('title', 'Checkout Pembelian — ' . config('alnay.brand.nama'))

@section('content')

    <style>
        [x-cloak] { display: none !important; }
        @keyframes pd-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        @keyframes pd-swap { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        @keyframes pd-shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
        @keyframes pd-spin { to { transform: rotate(360deg); } }
        
        .pd-up { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
        .pd-swap { animation: pd-swap .45s ease-out both; }
        .pd-shake { animation: pd-shake .45s ease; }
        .pd-spin { animation: pd-spin .8s linear infinite; }
        
        .pd-shine { position: relative; overflow: hidden; }
        .pd-shine::after { content: ''; position: absolute; inset: 0; background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%); transform: translateX(-120%); transition: transform .8s ease; pointer-events: none; }
        .pd-shine:hover::after { transform: translateX(120%); }

        .opsi-radio:checked + .opsi-card .radio-dot { background: var(--color-gold, #ba8320); border-color: var(--color-gold, #ba8320); box-shadow: inset 0 0 0 2px #fff; }
        .opsi-radio:focus-visible + .opsi-card { outline: 2px solid var(--color-gold, #ba8320); outline-offset: 2px; }
        .opsi-radio:disabled + .opsi-card { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

        @media (prefers-reduced-motion: reduce) {
            .pd-up, .pd-swap, .pd-shake { animation: none !important; }
            .pd-shine::after { transition: none !important; }
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
            // Menggunakan data dari $alamatUtama yang dikirim oleh BeliController
            'nama' => old('nama_penerima', $alamatUtama->nama_penerima ?? $user->name ?? ''),
            'wa' => old('no_wa', $alamatUtama->no_hp ?? $user->no_hp ?? ''),
            'alamat' => old('alamat', $alamatUtama->alamat ?? $user->alamat ?? ''),
            'catatan' => old('catatan', ''),
            'setuju' => (bool) old('setuju'),
            'total' => (float) $total,
        ];
    @endphp

    <div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 pb-28 lg:pb-12 font-sans" x-data="checkoutBeli(@js($cfg))" @pageshow.window="sedangKirim = false">
        <div class="max-w-[1180px] mx-auto px-4 md:px-6">

            @if ($errors->any())
                <div class="pd-up p-4 mb-4 rounded-lg bg-red-50 border border-red-200 text-[13px] text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('beli.store') }}" novalidate x-ref="formCheckout" @submit.prevent="kirimForm()">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6 lg:gap-8">

                    {{-- KOLOM KIRI --}}
                    <div class="space-y-4">

                        {{-- 1. Ringkasan Pembelian --}}
                        <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .05s;">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div>
                                    <h2 class="font-display text-lg font-medium text-ink/85 mb-1">Ringkasan Pembelian</h2>
                                    <p class="text-xs text-ink/55">Barang menjadi milik Anda sepenuhnya — tidak perlu dikembalikan.</p>
                                </div>
                                <a href="{{ route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli']) }}" class="shrink-0 text-xs font-medium text-gold hover:underline underline-offset-2">Ubah</a>
                            </div>
                            <div class="flex gap-3 items-center border-b border-line/40 pb-4 mb-4">
                                <div class="w-16 h-16 rounded-lg bg-gold/15 border border-gold/25 overflow-hidden flex items-center justify-center shrink-0">
                                    @if ($fotoCheckout)
                                        <img src="{{ asset('storage/' . $fotoCheckout) }}" alt="{{ $produk->nama }}" class="w-full h-full object-cover">
                                    @else
                                        <x-icon name="sparkle" :size="20" class="text-gold/60" />
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-[15px] font-medium text-ink/85">{{ $produk->nama }}</p>
                                    @if ($spesifikasiCheckout) <p class="text-xs text-ink/55 mt-0.5">{{ $spesifikasiCheckout }}</p> @endif
                                    <p class="text-xs text-ink/55 mt-0.5">
                                        @if ($diskonJualCheckout > 0) <span class="line-through text-ink/40">Rp{{ number_format($produk->harga_jual, 0, ',', '.') }}</span> @endif
                                        Rp{{ number_format($produk->hargaJualNet(), 0, ',', '.') }} / unit
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 rounded-md bg-cream/70 text-[11px] font-semibold text-ink/65">Qty: {{ $qty }}</span>
                            </div>
                        </div>

                        {{-- 2. Metode Penerimaan --}}
                        <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .1s;">
                            <h2 class="font-display text-lg font-medium text-ink/85 mb-1">Metode Penerimaan</h2>
                            <p class="text-xs text-ink/55 mb-4">Diantar lewat jasa ekspedisi, atau ambil sendiri di toko jika lokasi Anda dekat.</p>

                            <div class="space-y-2" role="radiogroup" aria-label="Metode penerimaan">
                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_penerimaan" value="kirim" class="opsi-radio peer sr-only" x-model="metode" @checked($metodeAwal === 'kirim')>
                                    <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 transition-all duration-300">
                                        <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1">
                                            <p class="text-[13px] font-medium text-ink/85">Kirim dengan Ekspedisi</p>
                                            <p class="text-xs text-ink/55">Ongkir dihitung otomatis sesuai tujuan dan berat pesanan.</p>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_penerimaan" value="ambil" class="opsi-radio peer sr-only" x-model="metode" @checked($metodeAwal === 'ambil')>
                                    <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 transition-all duration-300">
                                        <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-medium text-ink/85">Ambil di Toko</p>
                                            <p class="text-xs text-ink/55">{{ $alamatToko }}</p>
                                        </div>
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold shrink-0">Gratis ongkir</span>
                                    </div>
                                </label>
                            </div>
                            @error('metode_penerimaan') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- 3. Data penerima & alamat --}}
                        <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .12s;">
                            <h2 class="font-display text-lg font-medium text-ink/85 mb-4"
                                x-text="kirim ? 'Alamat Pengiriman' : 'Data Pengambil Pesanan'">Alamat Pengiriman</h2>

                            {{-- Daftar Alamat Tersimpan (hanya untuk kirim) --}}
                            @if(isset($alamatList) && count($alamatList) > 0)
                                <div class="mb-5" x-show="kirim" x-cloak>
                                    <label class="block text-xs font-medium text-ink/70 mb-2">Pilih dari Alamat Tersimpan</label>
                                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                        @foreach($alamatList as $a)
                                            <label class="cursor-pointer block">
                                                <input type="radio" name="pilih_alamat" value="{{ $a->id }}" class="opsi-radio peer sr-only"
                                                       onchange="pilihAlamatTersimpan(this)"
                                                       data-alamat="{{ json_encode($a) }}"
                                                       @if($alamatUtama && $alamatUtama->id === $a->id) checked @endif>
                                                <div class="opsi-card flex items-start gap-3 p-3 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 transition-all duration-300">
                                                    <span class="radio-dot w-4 h-4 mt-0.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[13px] font-bold text-ink/85">{{ $a->label }} - {{ $a->nama_penerima }}</p>
                                                        <p class="text-[11px] text-ink/60">{{ $a->no_hp }}</p>
                                                        <p class="text-[11px] text-ink/60 mt-1 leading-relaxed">{{ $a->alamat }}, {{ $a->kecamatan }}, {{ $a->kota_kabupaten }}, {{ $a->provinsi }} {{ $a->kode_pos }}</p>
                                                    </div>
                                                    @if($a->is_utama) <span class="text-[9px] font-bold px-2 py-0.5 bg-gold/10 text-gold rounded shrink-0">UTAMA</span> @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="text-[11px] text-ink/55 mt-2">Atau ubah alamat secara manual di bawah ini.</p>
                                </div>
                            @endif

                            {{-- Nama & WhatsApp: dibutuhkan untuk kirim maupun ambil --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-medium text-ink/70 mb-1" x-text="kirim ? 'Nama Penerima' : 'Nama Pengambil'">Nama Penerima</label>
                                    <input type="text" name="nama_penerima" x-model="namaPenerima" class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold transition-all">
                                    @error('nama_penerima') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink/70 mb-1">No. WhatsApp Aktif</label>
                                    <input type="text" name="no_wa" x-model="noWa" inputmode="tel" class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold transition-all">
                                    @error('no_wa') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="mb-3" x-show="kirim" x-cloak>
                                <label class="block text-xs font-medium text-ink/70 mb-1">Alamat Lengkap</label>
                                <textarea name="alamat" rows="2" x-model="alamat" class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold transition-all resize-none"></textarea>
                                @error('alamat') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-ink/70 mb-1" x-text="kirim ? 'Catatan untuk Kurir (opsional)' : 'Catatan untuk Toko (opsional)'">Catatan untuk Kurir (opsional)</label>
                                <input type="text" name="catatan" x-model="catatan" class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold transition-all">
                            </div>

                            {{-- Info ambil di toko --}}
                            <div x-show="!kirim" x-cloak class="pd-swap mt-4 flex gap-3 rounded-lg border border-gold/30 bg-gold/5 p-3.5">
                                <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0 text-gold" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                <div>
                                    <p class="text-[13px] font-semibold text-ink/85">Ambil di Toko</p>
                                    <p class="text-xs text-ink/60 mt-0.5">{{ $alamatToko }}</p>
                                    <p class="text-[11px] text-ink/50 mt-1">Tanpa ongkos kirim. Tidak perlu memilih tujuan maupun kurir.</p>
                                </div>
                            </div>

                            {{-- Tujuan, Kurir & Ongkir (hanya untuk kirim) --}}
                            <div class="mt-4 border-t border-line/40 pt-4" x-show="kirim" x-cloak>
                                <div class="relative mb-4">
                                    <label class="block text-xs font-medium text-ink/70 mb-1">Kelurahan / Kecamatan Tujuan</label>
                                    <input type="text" id="q-tujuan" autocomplete="off" value="{{ old('tujuan_label', $alamatUtama->ongkir_tujuan_label ?? $alamatUtama->kecamatan ?? '') }}" placeholder="Ketik minimal 3 huruf, lalu pilih dari daftar" class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold transition-all">
                                    <div id="hasil-tujuan" class="hidden absolute z-30 left-0 right-0 mt-1 max-h-48 overflow-y-auto rounded-lg border border-line/60 bg-white shadow-lg"></div>
                                    <p id="info-tujuan" class="mt-1 text-[11px] text-ink/50"></p>
                                </div>

                                <label class="block text-xs font-medium text-ink/70 mb-2">Pilih Kurir &amp; Layanan</label>
                                <div id="opsi-ongkir" class="space-y-2">
                                    <p class="text-xs text-ink/55">Pilih tujuan terlebih dahulu.</p>
                                </div>
                            </div>

                            <input type="hidden" name="tujuan_id" id="in-tujuan-id" value="{{ $alamatUtama->ongkir_tujuan_id ?? '' }}">
                            <input type="hidden" name="tujuan_label" id="in-tujuan-label" value="{{ $alamatUtama->ongkir_tujuan_label ?? '' }}">
                            <input type="hidden" name="kurir" id="inputKurir" value="">
                        </div>

                        {{-- 4. Metode Pembayaran --}}
                        <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .15s;">
                            <h2 class="font-display text-lg font-medium text-ink/85 mb-1">Metode Pembayaran <span class="ml-2 align-middle px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase">Instan & Terverifikasi</span></h2>
                            <p class="text-xs text-ink/55 mb-4" x-text="kirim ? 'Dibayar online agar pesanan langsung diproses untuk dikirim.' : 'Bayar online sekarang, atau tunai saat mengambil pesanan di toko.'">Dibayar online agar pesanan langsung diproses untuk dikirim.</p>

                            <div class="space-y-2">
                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_bayar" value="qris" class="opsi-radio peer sr-only" x-model="metodeBayar">
                                    <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 transition-all duration-300">
                                        <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1">
                                            <p class="text-[13px] font-medium text-ink/85">QRIS (Semua Bank & Dompet Digital)</p>
                                            <p class="text-xs text-ink/55">BCA, Mandiri, OVO, GoPay, ShopeePay, Dana</p>
                                        </div>
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">Otomatis</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_bayar" value="va" class="opsi-radio peer sr-only" x-model="metodeBayar">
                                    <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 transition-all duration-300">
                                        <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1">
                                            <p class="text-[13px] font-medium text-ink/85">Virtual Account Bank</p>
                                            <p class="text-xs text-ink/55">BCA, Mandiri, BNI, BRI, Permata</p>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer block">
                                    <input type="radio" name="metode_bayar" value="ewallet" class="opsi-radio peer sr-only" x-model="metodeBayar">
                                    <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 transition-all duration-300">
                                        <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1">
                                            <p class="text-[13px] font-medium text-ink/85">E-Wallet Direct</p>
                                            <p class="text-xs text-ink/55">GoPay, ShopeePay, OVO</p>
                                        </div>
                                    </div>
                                </label>

                                {{-- Tunai: hanya untuk ambil di toko --}}
                                <label class="cursor-pointer block" x-show="!kirim" x-cloak>
                                    <input type="radio" name="metode_bayar" value="tunai" class="opsi-radio peer sr-only" x-model="metodeBayar" :disabled="kirim">
                                    <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 transition-all duration-300">
                                        <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <div class="flex-1">
                                            <p class="text-[13px] font-medium text-ink/85">Bayar Tunai di Toko</p>
                                            <p class="text-xs text-ink/55">Bayar langsung saat mengambil pesanan.</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('metode_bayar') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror

                            <label class="mt-4 flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="setuju" value="1" x-model="setuju" class="mt-0.5 w-4 h-4 rounded border-line text-gold focus:ring-gold focus:ring-offset-0">
                                <span class="text-[13px] text-ink/75 leading-relaxed">Saya sudah membaca dan menyetujui <a href="#" class="text-gold font-medium underline underline-offset-2">syarat & ketentuan pembelian</a>.</span>
                            </label>
                            @error('setuju') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- KOLOM KANAN — RINGKASAN --}}
                    <div class="lg:sticky lg:top-24 h-fit">
                        <div class="pd-up" style="--d: .2s;">
                            <div class="bg-panel border border-line/40 rounded-xl p-5 transition-colors duration-300" :class="{ 'pd-shake border-rose-300': getar }">
                                <h2 class="text-xs font-medium uppercase tracking-wider text-ink/55 mb-4">Rincian Harga</h2>
                                <div class="space-y-2 text-[13px] mb-4">
                                    <div class="flex justify-between gap-3">
                                        <span class="text-ink/60">{{ $produk->nama }} ×{{ $qty }}</span>
                                        <span class="font-medium text-ink/80">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <span class="text-ink/60" id="labelNamaOngkir">Ongkos Kirim</span>
                                        <span class="font-medium text-ink/80" id="labelOngkir">Rp0</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center border-t border-line/40 pt-3 mb-4 text-[13px]">
                                    <span class="font-medium text-ink/80">Total Harga</span>
                                    <span class="font-medium text-ink/80" id="labelTotal">Rp{{ number_format($total, 0, ',', '.') }}</span>
                                </div>
                                <div class="bg-ink text-cream rounded-lg p-3.5 mb-4">
                                    <div class="flex justify-between items-center gap-3">
                                        <span class="text-xs text-cream/75 leading-snug" x-text="bayarTunai ? 'Total Dibayar di Toko' : 'Total Dibayar Sekarang'">Total Dibayar Sekarang</span>
                                        <span class="font-sans text-xl font-semibold tabular-nums whitespace-nowrap" id="labelTotalBayar">Rp{{ number_format($total, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-ink/50 leading-relaxed mb-4" x-text="kirim ? 'Pesanan diproses otomatis begitu pembayaran terverifikasi. Nomor resi dikirim lewat WhatsApp.' : (bayarTunai ? 'Pesanan tercatat dan dibayar tunai saat Anda mengambilnya di toko.' : 'Pesanan diproses begitu pembayaran terverifikasi, lalu bisa diambil di toko.')">Pesanan diproses otomatis begitu pembayaran terverifikasi. Nomor resi dikirim lewat WhatsApp.</p>
                                <p x-ref="errorBox" x-show="errorMetode" x-cloak x-transition x-text="errorMetode" class="mb-3 p-2.5 rounded-md bg-rose-50 border border-rose-200 text-xs text-rose-700"></p>
                                <button type="submit" :disabled="sedangKirim" class="pd-shine w-full h-[46px] rounded-lg bg-gold text-white text-[13px] font-medium tracking-wide hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait disabled:hover:translate-y-0 transition-all duration-300 mb-2">
                                    <span x-show="!sedangKirim" x-text="labelTombol">Bayar Sekarang &amp; Kirim Pesanan</span>
                                    <span x-show="sedangKirim" x-cloak class="inline-flex items-center justify-center gap-2">
                                        <svg class="pd-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 3a9 9 0 1 0 9 9" /></svg> Memproses…
                                    </span>
                                </button>
                                <a href="{{ route('koleksi.index') }}" class="block w-full h-[38px] rounded-lg border border-line/50 bg-white text-ink/65 text-[11px] font-medium hover:bg-cream/50 transition-all duration-300 text-center leading-[38px]">Simpan & Lanjutkan Nanti</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bilah bayar menempel di bawah (HP) --}}
                <div class="lg:hidden fixed inset-x-0 bottom-0 z-30 bg-white/95 backdrop-blur border-t border-line/50 px-4 py-3 flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-ink/55" x-text="bayarTunai ? 'Total dibayar di toko' : 'Total dibayar sekarang'">Total dibayar sekarang</p>
                        <p class="font-sans text-lg font-semibold text-ink tabular-nums truncate" id="labelTotalBayarMobile">Rp{{ number_format($total, 0, ',', '.') }}</p>
                    </div>
                    <button type="submit" :disabled="sedangKirim" class="shrink-0 h-11 px-5 rounded-lg bg-gold text-white text-[13px] font-medium active:scale-[0.98] disabled:opacity-70 transition-all">
                        <span x-text="sedangKirim ? 'Memproses…' : (bayarTunai ? 'Pesan' : 'Bayar')"></span>
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
            
            // PERUBAHAN 3: Tambahkan variabel AWAL_TUJUAN dari instruksi Claude
            const AWAL_TUJUAN = @json(['id' => $alamatUtama->ongkir_tujuan_id ?? null, 'label' => $alamatUtama->ongkir_tujuan_label ?? null]);

            let ongkirNow = 0;
            let labelNow = '';
            let kirimDiinisialisasi = false;

            const isKirim = () => {
                const r = document.querySelector('input[name="metode_penerimaan"]:checked');
                return !r || r.value === 'kirim';
            };

            // Tampilkan harga sesuai metode: ambil di toko = gratis ongkir
            function render() {
                const kirim = isKirim();
                const ongkir = kirim ? ongkirNow : 0;
                const total = SUBTOTAL + ongkir;
                $('labelNamaOngkir').textContent = kirim && labelNow ? 'Ongkos Kirim (' + labelNow + ')' : 'Ongkos Kirim';
                $('labelOngkir').textContent = !kirim ? 'Gratis (ambil di toko)' : (labelNow ? rupiah(ongkir) : 'Belum dipilih');
                ['labelTotal', 'labelTotalBayar', 'labelTotalBayarMobile'].forEach(id => {
                    if ($(id)) $(id).textContent = rupiah(total);
                });
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
                pesanOpsi('Pilih tujuan terlebih dahulu.');
                resetLayanan();
            }

            function pesanOpsi(teks) {
                const p = document.createElement('p');
                p.className = 'text-xs text-ink/55';
                p.textContent = teks;
                $('opsi-ongkir').replaceChildren(p);
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
                    p.className = 'px-3 py-2 text-xs text-ink/55';
                    p.textContent = 'Tujuan tidak ditemukan.';
                    box.appendChild(p);
                }

                list.forEach(t => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'block w-full text-left px-3 py-2 text-xs text-ink/80 hover:bg-gold/10 border-b border-line/30 last:border-0';
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
                $('info-tujuan').textContent = 'Tujuan dipilih.';
                hitungOngkir(t.id);
            }

            async function hitungOngkir(tujuanId) {
                resetLayanan();
                pesanOpsi('Menghitung ongkir...');

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
                    card.className = 'opsi-card flex items-center gap-3 p-3 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 transition-all duration-300';

                    const dot = document.createElement('span');
                    dot.className = 'radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all';

                    const info = document.createElement('div');
                    info.className = 'flex-1 min-w-0';
                    const nama = document.createElement('p');
                    nama.className = 'text-[13px] font-semibold text-ink/85';
                    nama.textContent = o.kurir.toUpperCase() + ' - ' + o.layanan;
                    const est = document.createElement('p');
                    est.className = 'text-[11px] text-ink/55';
                    est.textContent = (o.deskripsi || '') + ' (est. ' + (o.etd || '-') + ')';
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

            // Fungsi untuk memilih alamat tersimpan
            window.pilihAlamatTersimpan = function(el) {
                const data = JSON.parse(el.dataset.alamat);
                
                // 1. Update input teks (Alpine x-model)
                const inputNama = document.querySelector('input[name="nama_penerima"]');
                const inputWa = document.querySelector('input[name="no_wa"]');
                const inputAlamat = document.querySelector('textarea[name="alamat"]');
                
                if (inputNama) { inputNama.value = data.nama_penerima; inputNama.dispatchEvent(new Event('input')); }
                if (inputWa) { inputWa.value = data.no_hp; inputWa.dispatchEvent(new Event('input')); }
                if (inputAlamat) { inputAlamat.value = data.alamat; inputAlamat.dispatchEvent(new Event('input')); }

                // 2. Set Tujuan & Hitung Ongkir
                if (data.ongkir_tujuan_id && data.ongkir_tujuan_label) {
                    pilihTujuan({ id: data.ongkir_tujuan_id, label: data.ongkir_tujuan_label });
                }
            };

            // Tujuan & ongkir hanya dihitung saat metode "kirim" aktif (tidak memanggil RajaOngkir untuk ambil di toko)
            function mulaiKirim() {
                if (kirimDiinisialisasi) return;
                kirimDiinisialisasi = true;

                // Alamat utama sudah punya tujuan ongkir: langsung hitung, tanpa mencari lagi.
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

    {{-- SCRIPT 2: Alpine.js Form Validation --}}
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

                errorMetode: '',
                getar: false,
                sedangKirim: false,

                init() {
                    // Tunai hanya untuk ambil di toko: kembali ke QRIS bila pindah ke kirim
                    this.$watch('metode', (v) => {
                        if (v === 'kirim' && this.metodeBayar === 'tunai') this.metodeBayar = 'qris';
                        this.errorMetode = '';
                    });
                },

                get kirim() { return this.metode === 'kirim'; },
                get bayarTunai() { return !this.kirim && this.metodeBayar === 'tunai'; },
                get labelTombol() {
                    if (this.kirim) return 'Bayar Sekarang & Kirim Pesanan';
                    return this.bayarTunai ? 'Buat Pesanan (Bayar di Toko)' : 'Bayar Sekarang';
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