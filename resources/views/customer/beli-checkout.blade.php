@extends('layouts.public')

@section('title', 'Checkout Pembelian — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans" 
     x-data="{ metodeBayar: 'qris' }">
    <div class="max-w-[1180px] mx-auto px-4 md:px-6">

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="p-3 mb-4 rounded-md bg-red-50 border border-red-200 text-xs text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('beli.store') }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 lg:gap-8">

                {{-- ==================== KOLOM KIRI ==================== --}}
                <div class="space-y-4">

                    {{-- 1. Ringkasan Pembelian --}}
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <h2 class="font-display text-lg font-semibold text-ink/90 mb-1">Ringkasan Pembelian</h2>
                        <p class="text-[11px] text-ink/50 mb-4">Barang menjadi milik Anda sepenuhnya — tidak perlu dikembalikan.</p>

                        <div class="flex gap-3 items-center border-b border-line/40 pb-4 mb-4">
                            <div class="w-14 h-14 rounded-lg bg-gold/20 border border-gold/30 flex items-center justify-center shrink-0">
                                <x-icon name="sparkle" :size="20" class="text-gold/60" />
                            </div>
                            <div class="flex-1">
                                <p class="text-[13px] font-semibold text-ink/80">{{ $produk->nama }}</p>
                                <p class="text-[10px] text-ink/50 mt-0.5">Varian {{ $varian }} · Ukuran {{ $beliData['ukuran'] ?? 'M' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded bg-cream/60 text-[10px] font-semibold text-ink/60">Qty: {{ $qty }}</span>
                        </div>

                        <div class="flex items-start gap-2 text-[10px] text-ink/50 bg-cream/40 rounded-md p-3">
                            <x-icon name="check" :size="12" class="text-emerald-500 mt-0.5 shrink-0" />
                            <p class="leading-relaxed">Pembelian tidak memerlukan sesi fitting. Pastikan ukuran sudah sesuai lewat <a href="#" class="text-gold hover:underline">Panduan Ukur Mandiri</a> sebelum melanjutkan.</p>
                        </div>
                    </div>

                    {{-- 2. Alamat Pengiriman --}}
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <h2 class="font-display text-lg font-semibold text-ink/90 mb-1">Alamat Pengiriman</h2>
                        <p class="text-[11px] text-green-600 mb-4 flex items-center gap-1">
                            <x-icon name="check" :size="12" /> Diisi otomatis dari profil Anda — semua kolom bisa diubah khusus untuk pesanan ini.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-[10px] font-medium text-ink/60 mb-1">Nama Penerima</label>
                                <input type="text" name="nama_penerima" value="{{ old('nama_penerima', auth()->user()->name) }}" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 focus:outline-none focus:border-gold/60">
                            </div>
                            <div>
                                <label class="block text-[10px] font-medium text-ink/60 mb-1">No. WhatsApp Aktif</label>
                                <input type="text" name="no_wa" value="{{ old('no_wa', auth()->user()->no_hp) }}" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 focus:outline-none focus:border-gold/60">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-[10px] font-medium text-ink/60 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" rows="2" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 focus:outline-none focus:border-gold/60 resize-none">{{ old('alamat', auth()->user()->alamat) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="block text-[10px] font-medium text-ink/60 mb-1">Catatan untuk Kurir (opsional)</label>
                            <input type="text" name="catatan" placeholder="Contoh: patokan dekat minimarket, titip satpam, dll." class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 placeholder:text-ink/30 focus:outline-none focus:border-gold/60">
                        </div>

                        <a href="{{ route('akun.profil.edit') }}" class="inline-flex items-center gap-1 text-[10px] text-gold hover:underline">
                            Kelola alamat tersimpan lainnya di halaman Profil
                            <x-icon name="chevron-right" :size="10" />
                        </a>
                    </div>

                    {{-- 3. Metode Pembayaran --}}
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <h2 class="font-display text-lg font-semibold text-ink/90 mb-1">Metode Pembayaran <span class="ml-2 px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[8px] font-bold uppercase">Instan & Terverifikasi</span></h2>
                        <p class="text-[11px] text-ink/50 mb-4">Dibayar online agar pesanan langsung diproses untuk dikirim</p>

                        <div class="space-y-2">
                            <label class="cursor-pointer block">
                                <input type="radio" name="metode_bayar" value="qris" class="peer sr-only" x-model="metodeBayar" checked>
                                <div class="flex items-center gap-3 p-3 rounded-lg border border-line/50 peer-checked:border-gold/70 peer-checked:bg-gold/5 transition-all duration-300">
                                    <span class="w-4 h-4 rounded-full border-2 border-line/60 shrink-0"></span>
                                    <div class="flex-1">
                                        <p class="text-[12px] font-semibold text-ink/80">QRIS (Semua Bank & Dompet Digital)</p>
                                        <p class="text-[10px] text-ink/50">BCA, Mandiri, OVO, GoPay, ShopeePay, AstraPay, Dana</p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[8px] font-bold">Otomatis</span>
                                </div>
                            </label>

                            <label class="cursor-pointer block">
                                <input type="radio" name="metode_bayar" value="va" class="peer sr-only" x-model="metodeBayar">
                                <div class="flex items-center gap-3 p-3 rounded-lg border border-line/50 peer-checked:border-gold/70 peer-checked:bg-gold/5 transition-all duration-300">
                                    <span class="w-4 h-4 rounded-full border-2 border-line/60 shrink-0"></span>
                                    <div class="flex-1">
                                        <p class="text-[12px] font-semibold text-ink/80">Virtual Account Bank</p>
                                        <p class="text-[10px] text-ink/50">BCA, Mandiri, BNI, BRI, Permata</p>
                                    </div>
                                    <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                                </div>
                            </label>

                            <label class="cursor-pointer block">
                                <input type="radio" name="metode_bayar" value="ewallet" class="peer sr-only" x-model="metodeBayar">
                                <div class="flex items-center gap-3 p-3 rounded-lg border border-line/50 peer-checked:border-gold/70 peer-checked:bg-gold/5 transition-all duration-300">
                                    <span class="w-4 h-4 rounded-full border-2 border-line/60 shrink-0"></span>
                                    <div class="flex-1">
                                        <p class="text-[12px] font-semibold text-ink/80">E-Wallet Direct</p>
                                        <p class="text-[10px] text-ink/50">GoPay, ShopeePay, OVO</p>
                                    </div>
                                    <x-icon name="chevron-right" :size="14" class="text-ink/30" />
                                </div>
                            </label>
                        </div>

                        <p class="mt-3 text-[9px] text-ink/40 leading-relaxed">Begitu pembayaran terverifikasi, pesanan langsung dikemas dan diserahkan ke kurir — tidak perlu bayar apa pun lagi di tempat.</p>
                    </div>
                </div>

                {{-- ==================== KOLOM KANAN — RINGKASAN ==================== --}}
                <div class="lg:sticky lg:top-24 h-fit">
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <h2 class="text-[11px] font-bold uppercase tracking-wider text-ink/60 mb-4">Rincian Harga</h2>

                        <div class="space-y-2 text-[11px] mb-4">
                            <div class="flex justify-between">
                                <span class="text-ink/50">{{ $produk->nama }} ×{{ $qty }}</span>
                                <span class="font-semibold text-ink/80">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink/50">Ongkos Kirim (JNE YES · Bandung)</span>
                                <span class="font-semibold text-ink/80">Rp{{ number_format($ongkir, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center border-t border-line/40 pt-3 mb-4 text-[11px]">
                            <span class="font-semibold text-ink/80">Total Harga</span>
                            <span class="font-semibold text-ink/80">Rp{{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        <div class="bg-ink text-cream rounded-md p-3 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] text-cream/70">Total Dibayar Sekarang</span>
                                <span class="font-display text-xl font-semibold">Rp{{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <p class="text-[9px] text-ink/40 leading-relaxed mb-4">Pesanan diproses otomatis begitu pembayaran terverifikasi. Anda akan menerima nomor resi lewat WhatsApp begitu barang dikirim.</p>

                        <button type="submit" class="w-full h-[42px] rounded-lg bg-gold/95 text-white text-[10px] font-semibold tracking-wider hover:bg-gold transition-all duration-300 mb-2">
                            Bayar Sekarang & Kirim Pesanan
                        </button>

                        <a href="{{ route('koleksi.index') }}" class="block w-full h-[38px] rounded-lg border border-line/50 bg-white text-ink/70 text-[10px] font-medium hover:bg-cream/50 transition-all duration-300 text-center leading-[38px]">
                            Simpan & Lanjutkan Nanti
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection