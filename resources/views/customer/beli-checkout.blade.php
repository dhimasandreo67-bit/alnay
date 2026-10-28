@extends('layouts.public')

@section('title', 'Checkout Pembelian — ' . config('alnay.brand.nama'))

@section('content')

<style>
    [x-cloak] { display: none !important; }
    @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
    @keyframes pd-swap { from { opacity: 0; transform: translateY(8px); }  to { opacity: 1; transform: none; } }
    @keyframes pd-shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
    @keyframes pd-spin { to { transform: rotate(360deg); } }
    .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .pd-swap  { animation: pd-swap .45s ease-out both; }
    .pd-shake { animation: pd-shake .45s ease; }
    .pd-spin  { animation: pd-spin .8s linear infinite; }
    .pd-shine { position: relative; overflow: hidden; }
    .pd-shine::after { content: ''; position: absolute; inset: 0; background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%); transform: translateX(-120%); transition: transform .8s ease; pointer-events: none; }
    .pd-shine:hover::after { transform: translateX(120%); }
    .opsi-radio:checked + .opsi-card .radio-dot { background: var(--color-gold, #ba8320); border-color: var(--color-gold, #ba8320); box-shadow: inset 0 0 0 2px #fff; }
    .opsi-radio:focus-visible + .opsi-card { outline: 2px solid var(--color-gold, #ba8320); outline-offset: 2px; }
    .opsi-radio:disabled + .opsi-card { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }
    @media (prefers-reduced-motion: reduce) { .pd-up, .pd-swap, .pd-shake { animation: none !important; } .pd-shine::after { transition: none !important; } }
</style>

@php
    $user = auth()->user();
    
    // Ambil foto pertama jika array
    $fotoCheckout = $produk->foto_produk;
    if (is_array($fotoCheckout)) {
        $fotoCheckout = $fotoCheckout[0] ?? null;
    }

    // Ambil ukuran dengan aman
    $ukuranCheckout = $beliData['ukuran'] ?? 'M';
    if (is_array($ukuranCheckout)) {
        $ukuranCheckout = implode(', ', $ukuranCheckout);
    }

    $cfg = [
        'bayar'    => old('metode_bayar', 'qris'),
        'nama'     => old('nama_penerima', $user->name ?? ''),
        'wa'       => old('no_wa', $user->no_hp ?? ''),
        'alamat'   => old('alamat', $user->alamat ?? ''),
        'catatan'  => old('catatan', ''),
        'setuju'   => (bool) old('setuju'),
        'total'    => (float) $total,
    ];
@endphp

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 pb-28 lg:pb-12 font-sans"
     x-data="checkoutBeli(@js($cfg))"
     @pageshow.window="sedangKirim = false">
    <div class="max-w-[1180px] mx-auto px-4 md:px-6">

        {{-- Alert Error dari server --}}
        @if ($errors->any())
            <div class="pd-up p-4 mb-4 rounded-lg bg-red-50 border border-red-200 text-[13px] text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('beli.store') }}" novalidate
              x-ref="formCheckout" @submit.prevent="kirim()">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6 lg:gap-8">

                {{-- ==================== KOLOM KIRI ==================== --}}
                <div class="space-y-4">

                    {{-- 1. Ringkasan Pembelian --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .05s;">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <h2 class="font-display text-lg font-medium text-ink/85 mb-1">Ringkasan Pembelian</h2>
                                <p class="text-xs text-ink/55">Barang menjadi milik Anda sepenuhnya — tidak perlu dikembalikan.</p>
                            </div>
                            <a href="{{ route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli']) }}"
                               class="shrink-0 text-xs font-medium text-gold hover:underline underline-offset-2">Ubah</a>
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
                                <p class="text-xs text-ink/55 mt-0.5">Varian {{ $varian }} · Ukuran {{ $ukuranCheckout }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-md bg-cream/70 text-[11px] font-semibold text-ink/65">Qty: {{ $qty }}</span>
                        </div>

                        <div class="flex items-start gap-2 text-[11px] text-ink/55 bg-cream/40 rounded-md p-3">
                            <x-icon name="check" :size="12" class="text-emerald-500 mt-0.5 shrink-0" />
                            <p class="leading-relaxed">Pembelian tidak memerlukan sesi fitting. Pastikan ukuran sudah sesuai lewat <a href="#" class="text-gold hover:underline">Panduan Ukur Mandiri</a> sebelum melanjutkan.</p>
                        </div>
                    </div>

                    {{-- 2. Alamat Pengiriman --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .1s;">
                        <h2 class="font-display text-lg font-medium text-ink/85 mb-1">Alamat Pengiriman</h2>
                        <p class="text-[11px] text-green-700 mb-4 flex items-center gap-1">
                            <x-icon name="check" :size="12" /> Diisi otomatis dari profil Anda — bisa diubah khusus untuk pesanan ini.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-ink/70 mb-1">Nama Penerima</label>
                                <input type="text" name="nama_penerima" x-model="namaPenerima"
                                       class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 hover:border-gold/60 transition-all">
                                @error('nama_penerima') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-ink/70 mb-1">No. WhatsApp Aktif</label>
                                <input type="text" name="no_wa" x-model="noWa" inputmode="tel"
                                       class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 hover:border-gold/60 transition-all">
                                @error('no_wa') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-ink/70 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" rows="2" x-model="alamat"
                                      class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 hover:border-gold/60 transition-all resize-none"></textarea>
                            @error('alamat') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-ink/70 mb-1">Catatan untuk Kurir (opsional)</label>
                            <input type="text" name="catatan" x-model="catatan"
                                   class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 hover:border-gold/60 transition-all">
                        </div>
                    </div>

                    {{-- 3. Metode Pembayaran --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .15s;">
                        <h2 class="font-display text-lg font-medium text-ink/85 mb-1">Metode Pembayaran
                            <span class="ml-2 align-middle px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase">Instan & Terverifikasi</span>
                        </h2>
                        <p class="text-xs text-ink/55 mb-4">Dibayar online agar pesanan langsung diproses untuk dikirim</p>

                        <div class="space-y-2">
                            <label class="cursor-pointer block">
                                <input type="radio" name="metode_bayar" value="qris" class="opsi-radio peer sr-only" x-model="metodeBayar">
                                <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 hover:-translate-y-px transition-all duration-300">
                                    <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                    <div class="flex-1">
                                        <p class="text-[13px] font-medium text-ink/85">QRIS (Semua Bank & Dompet Digital)</p>
                                        <p class="text-xs text-ink/55">BCA, Mandiri, OVO, GoPay, ShopeePay, AstraPay, Dana</p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">Otomatis</span>
                                </div>
                            </label>

                            <label class="cursor-pointer block">
                                <input type="radio" name="metode_bayar" value="va" class="opsi-radio peer sr-only" x-model="metodeBayar">
                                <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 hover:-translate-y-px transition-all duration-300">
                                    <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                    <div class="flex-1">
                                        <p class="text-[13px] font-medium text-ink/85">Virtual Account Bank</p>
                                        <p class="text-xs text-ink/55">BCA, Mandiri, BNI, BRI, Permata</p>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer block">
                                <input type="radio" name="metode_bayar" value="ewallet" class="opsi-radio peer sr-only" x-model="metodeBayar">
                                <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 hover:-translate-y-px transition-all duration-300">
                                    <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                    <div class="flex-1">
                                        <p class="text-[13px] font-medium text-ink/85">E-Wallet Direct</p>
                                        <p class="text-xs text-ink/55">GoPay, ShopeePay, OVO</p>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <p class="mt-3 text-[11px] text-ink/50 leading-relaxed">Begitu pembayaran terverifikasi, pesanan langsung dikemas dan diserahkan ke kurir — tidak perlu bayar apa pun lagi di tempat.</p>

                        <label class="mt-4 flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="setuju" value="1" x-model="setuju"
                                   class="mt-0.5 w-4 h-4 rounded border-line text-gold focus:ring-gold focus:ring-offset-0">
                            <span class="text-[13px] text-ink/75 leading-relaxed">
                                Saya sudah membaca dan menyetujui
                                <a href="#" class="text-gold font-medium underline underline-offset-2">syarat & ketentuan pembelian</a>.
                            </span>
                        </label>
                        @error('setuju') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- ==================== KOLOM KANAN — RINGKASAN ==================== --}}
                <div class="lg:sticky lg:top-24 h-fit">
                    <div class="pd-up" style="--d: .2s;">
                        <div class="bg-panel border border-line/40 rounded-xl p-5 transition-colors duration-300"
                            :class="{ 'pd-shake border-rose-300': getar }">
                            <h2 class="text-xs font-medium uppercase tracking-wider text-ink/55 mb-4">Rincian Harga</h2>

                        <div class="space-y-2 text-[13px] mb-4">
                            <div class="flex justify-between gap-3">
                                <span class="text-ink/60">{{ $produk->nama }} ×{{ $qty }}</span>
                                <span class="font-medium text-ink/80">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span class="text-ink/60">Ongkos Kirim (JNE YES · Bandung)</span>
                                <span class="font-medium text-ink/80">Rp{{ number_format($ongkir, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center border-t border-line/40 pt-3 mb-4 text-[13px]">
                            <span class="font-medium text-ink/80">Total Harga</span>
                            <span class="font-medium text-ink/80">Rp{{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        <div class="bg-ink text-cream rounded-lg p-3.5 mb-4">
                            <div class="flex justify-between items-center gap-3">
                                <span class="text-xs text-cream/75">Total Dibayar Sekarang</span>
                                <span class="font-sans text-xl font-semibold tabular-nums">Rp{{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <p class="text-[11px] text-ink/50 leading-relaxed mb-4">Pesanan diproses otomatis begitu pembayaran terverifikasi. Anda akan menerima nomor resi lewat WhatsApp begitu barang dikirim.</p>

                        <p x-ref="errorBox" x-show="errorMetode" x-cloak x-transition x-text="errorMetode"
                           class="mb-3 p-2.5 rounded-md bg-rose-50 border border-rose-200 text-xs text-rose-700"></p>

                        <button type="submit" :disabled="sedangKirim"
                                class="pd-shine w-full h-[46px] rounded-lg bg-gold text-white text-[13px] font-medium tracking-wide hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait disabled:hover:translate-y-0 transition-all duration-300 mb-2">
                            <span x-show="!sedangKirim">Bayar Sekarang & Kirim Pesanan</span>
                            <span x-show="sedangKirim" x-cloak class="inline-flex items-center justify-center gap-2">
                                <svg class="pd-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 3a9 9 0 1 0 9 9" /></svg>
                                Memproses…
                            </span>
                        </button>

                        <a href="{{ route('koleksi.index') }}" class="block w-full h-[38px] rounded-lg border border-line/50 bg-white text-ink/65 text-[11px] font-medium hover:bg-cream/50 transition-all duration-300 text-center leading-[38px]">
                            Simpan & Lanjutkan Nanti
                        </a>
                    </div>   
                </div>      
            </div>
        </div>           

            {{-- Bilah bayar menempel di bawah (HP) --}}
            <div class="lg:hidden fixed inset-x-0 bottom-0 z-30 bg-white/95 backdrop-blur border-t border-line/50 px-4 py-3 flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] text-ink/55">Total dibayar sekarang</p>
                    <p class="font-sans text-lg font-semibold text-ink tabular-nums truncate">Rp{{ number_format($total, 0, ',', '.') }}</p>
                </div>
                <button type="submit" :disabled="sedangKirim"
                        class="shrink-0 h-11 px-5 rounded-lg bg-gold text-white text-[13px] font-medium active:scale-[0.98] disabled:opacity-70 transition-all">
                    <span x-text="sedangKirim ? 'Memproses…' : 'Bayar'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutBeli', (cfg) => {
            return {
                metodeBayar: cfg.bayar,
                namaPenerima: cfg.nama,
                noWa: cfg.wa,
                alamat: cfg.alamat,
                catatan: cfg.catatan,
                setuju: cfg.setuju,

                errorMetode: '',
                getar: false,
                sedangKirim: false,

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
                    if (!this.metodeBayar) return this.tampilError('Pilih metode pembayaran.');
                    if (!this.namaPenerima.trim()) return this.tampilError('Nama penerima wajib diisi.');
                    if (!this.noWa.trim()) return this.tampilError('No. WhatsApp wajib diisi.');
                    if (!this.alamat.trim()) return this.tampilError('Alamat lengkap wajib diisi.');
                    if (!this.setuju) return this.tampilError('Centang persetujuan syarat & ketentuan pembelian.');

                    this.errorMetode = '';
                    return true;
                },

                kirim() {
                    if (this.sedangKirim) return;
                    if (!this.validasi()) return;
                    this.sedangKirim = true;
                    this.$refs.formCheckout.submit();
                },
            };
        });
    });
</script>

@endsection