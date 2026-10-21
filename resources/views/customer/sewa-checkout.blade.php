@extends('layouts.public')

@section('title', 'Checkout Sewa — ' . config('alnay.brand.nama'))

@section('content')

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans" 
     x-data="{ 
         metodePenerimaan: 'ambil', 
         metodeKtp: 'fisik', 
         metodeBayar: 'qris' 
     }">
    <div class="max-w-[1180px] mx-auto px-4 md:px-6">

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="p-3 mb-4 rounded-md bg-red-50 border border-red-200 text-xs text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Form Utama (membungkus seluruh input) --}}
        <form method="POST" action="{{ route('sewa.store') }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 lg:gap-8">

                {{-- ==================== KOLOM KIRI ==================== --}}
                <div class="space-y-4">

                    {{-- 1. Ringkasan Sewa --}}
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <h2 class="font-display text-lg font-semibold text-ink/90 mb-1">Ringkasan Sewa</h2>
                        <p class="text-[11px] text-ink/50 mb-4">Detail produk dan tanggal yang dipilih di halaman produk</p>

                        <div class="flex gap-3 items-center border-b border-line/40 pb-4 mb-4">
                            <div class="w-14 h-14 rounded-lg bg-cream-dim border border-line/50 flex items-center justify-center text-muted text-[8px] uppercase tracking-wider shrink-0">Foto</div>
                            <div>
                                <p class="text-sm font-semibold text-ink/80">{{ $produk->nama }}</p>
                                <p class="text-[11px] text-ink/50">Ukuran {{ $sewaData['ukuran'] }} · Warna: sesuai foto</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-[11px]">
                            <div>
                                <p class="text-ink/40 text-[9px] uppercase tracking-wider mb-1">Tanggal Ambil</p>
                                <p class="font-semibold text-ink/80">{{ \Carbon\Carbon::parse($sewaData['tanggal_ambil'])->format('d M Y') }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-ink/40 text-[9px] uppercase tracking-wider mb-1">Sewa</p>
                                <p class="font-semibold text-ink/80">{{ \Carbon\Carbon::parse($sewaData['tanggal_ambil'])->format('d') }}–{{ \Carbon\Carbon::parse($sewaData['tanggal_kembali'])->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-ink/40 text-[9px] uppercase tracking-wider mb-1">Kembali</p>
                                <p class="font-semibold text-ink/80">{{ \Carbon\Carbon::parse($sewaData['tanggal_kembali'])->format('d M Y') }}</p>
                            </div>
                        </div>

                        @if ($sewaData['durasi'] > 1)
                            <div class="mt-3 p-2.5 rounded-md bg-gold/5 border border-gold/20 flex justify-between text-[10px]">
                                <span class="text-ink/60">Tambahan hari pemakaian: {{ $sewaData['durasi'] - 1 }} hari</span>
                                <span class="font-semibold text-gold">+Rp{{ number_format(($sewaData['durasi'] - 1) * $produk->denda_per_hari_telat, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- 2. Verifikasi KTP (Dipindah ke atas) --}}
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <h2 class="font-display text-lg font-semibold text-ink/90 mb-1">Verifikasi Identitas</h2>
                        <p class="text-[11px] text-ink/50 mb-4">Sebagai jaminan sewa — Anda diharapkan datang langsung ke studio untuk verifikasi KTP asli.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            {{-- KTP Fisik (Default & Wajib) --}}
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_ktp" value="fisik" class="peer sr-only" x-model="metodeKtp">
                                <div class="p-4 rounded-lg border border-line/50 peer-checked:border-gold/70 peer-checked:bg-gold/5 transition-all duration-300">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-3 h-3 rounded-full border-2 border-line/60"></span>
                                        <p class="text-[12px] font-semibold text-ink/80">KTP Fisik (Asli)</p>
                                    </div>
                                    <p class="text-[10px] text-ink/50 leading-relaxed mb-2">Datang langsung ke studio, tunjukkan KTP asli. <strong class="text-ink/80">Disarankan</strong>.</p>
                                    <p class="text-[10px] text-green-600 flex items-center gap-1"><x-icon name="check" :size="10" /> Tidak ada deposit tambahan</p>
                                </div>
                            </label>

                            {{-- KTP Digital (Opsi Terakhir) --}}
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_ktp" value="digital" class="peer sr-only" x-model="metodeKtp">
                                <div class="p-4 rounded-lg border border-line/50 peer-checked:border-gold/70 peer-checked:bg-gold/5 transition-all duration-300">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-3 h-3 rounded-full border-2 border-line/60"></span>
                                        <p class="text-[12px] font-semibold text-ink/80">KTP Digital (Online)</p>
                                    </div>
                                    <p class="text-[10px] text-ink/50 leading-relaxed mb-2">Hanya jika <strong>benar-benar tidak bisa datang</strong>. Upload foto KTP.</p>
                                    <p class="text-[10px] font-semibold text-gold">+ Deposit jaminan Rp{{ number_format($deposit, 0, ',', '.') }}</p>
                                </div>
                            </label>
                        </div>

                        {{-- Upload KTP (muncul jika digital) --}}
                        <div x-show="metodeKtp === 'digital'" x-transition class="border border-dashed border-line/50 rounded-lg p-5 text-center">
                            <x-icon name="user" :size="24" class="text-ink/30 mx-auto mb-2" />
                            <p class="text-[11px] font-medium text-ink/60 mb-1">Upload Foto KTP</p>
                            <p class="text-[9px] text-ink/40 mb-3">JPG/PNG, maks 5MB</p>
                            <input type="file" name="foto_ktp" class="hidden" id="upload-ktp">
                            <label for="upload-ktp" class="inline-block px-4 py-1.5 rounded-md border border-line/50 text-[10px] font-medium text-ink/60 hover:border-gold/50 cursor-pointer transition-all">Pilih File</label>
                        </div>
                    </div>

                    {{-- 3. Metode Penerimaan --}}
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <h2 class="font-display text-lg font-semibold text-ink/90 mb-1">Metode Penerimaan</h2>
                        <p class="text-[11px] text-ink/50 mb-4">Jika memilih KTP Digital, busana wajib dikirim ke alamat Anda.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_penerimaan" value="ambil" class="peer sr-only" x-model="metodePenerimaan">
                                <div class="p-4 rounded-lg border border-line/50 peer-checked:border-gold/70 peer-checked:bg-gold/5 transition-all duration-300 h-full">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-3 h-3 rounded-full border-2 border-line/60"></span>
                                        <p class="text-[12px] font-semibold text-ink/80">Ambil di Studio</p>
                                    </div>
                                    <p class="text-[10px] text-ink/50 leading-relaxed">Tidak tersedia untuk KTP Digital.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="metode_penerimaan" value="kirim" class="peer sr-only" x-model="metodePenerimaan">
                                <div class="p-4 rounded-lg border border-line/50 peer-checked:border-gold/70 peer-checked:bg-gold/5 transition-all duration-300 h-full">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-3 h-3 rounded-full border-2 border-line/60"></span>
                                        <p class="text-[12px] font-semibold text-ink/80">Dikirim ke Alamat</p>
                                    </div>
                                    <p class="text-[10px] text-ink/50 leading-relaxed">Diantar oleh kurir ke alamat Anda.</p>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded bg-gold/10 text-gold text-[8px] font-bold uppercase">Wajib untuk KTP Digital</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 4. Alamat Pengiriman (muncul jika kirim) --}}
                    <div class="bg-panel border border-line/40 rounded-xl p-5" x-show="metodePenerimaan === 'kirim'" x-transition>
                        <h2 class="font-display text-lg font-semibold text-ink/90 mb-1">Alamat Pengiriman</h2>
                        <p class="text-[11px] text-green-600 mb-4 flex items-center gap-1">
                            <x-icon name="check" :size="12" /> Diisi otomatis dari profil Anda — bisa diubah khusus untuk pesanan ini.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-[10px] font-medium text-ink/60 mb-1">Nama Penerima</label>
                                <input type="text" name="nama_penerima" value="{{ auth()->user()->name ?? '' }}" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 focus:outline-none focus:border-gold/60">
                            </div>
                            <div>
                                <label class="block text-[10px] font-medium text-ink/60 mb-1">No. WhatsApp Aktif</label>
                                <input type="text" name="no_wa" value="{{ auth()->user()->no_hp ?? '' }}" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 focus:outline-none focus:border-gold/60">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-[10px] font-medium text-ink/60 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" rows="2" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 focus:outline-none focus:border-gold/60 resize-none">{{ auth()->user()->alamat ?? '' }}</textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-ink/60 mb-1">Catatan untuk Kurir (opsional)</label>
                            <input type="text" name="catatan" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 focus:outline-none focus:border-gold/60">
                        </div>
                    </div>

                    {{-- 5. Metode Pembayaran --}}
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <h2 class="font-display text-lg font-semibold text-ink/90 mb-1">Metode Pembayaran <span class="ml-2 px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[8px] font-bold uppercase">Instan & Terverifikasi</span></h2>
                        <p class="text-[11px] text-ink/50 mb-4">DP dibayar online agar tanggal sewa langsung terkunci</p>

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
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- ==================== KOLOM KANAN — RINGKASAN ==================== --}}
                <div class="lg:sticky lg:top-24 h-fit">
                    <div class="bg-panel border border-line/40 rounded-xl p-5">
                        <h2 class="text-[11px] font-bold uppercase tracking-wider text-ink/60 mb-4">Rincian Biaya Sewa</h2>

                        <div class="space-y-2 text-[11px] mb-4">
                            <div class="flex justify-between">
                                <span class="text-ink/50">Harga sewa ({{ $sewaData['durasi'] }} hari)</span>
                                <span class="font-semibold text-ink/80">Rp{{ number_format($produk->harga_sewa, 0, ',', '.') }}</span>
                            </div>
                            @if ($sewaData['durasi'] > 1)
                                <div class="flex justify-between">
                                    <span class="text-ink/50">Tambahan hari ({{ $sewaData['durasi'] - 1 }} hari)</span>
                                    <span class="font-semibold text-ink/80">Rp{{ number_format(($sewaData['durasi'] - 1) * $produk->denda_per_hari_telat, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-line/40 pt-3 mb-4 space-y-2 text-[11px]">
                            <div class="flex justify-between">
                                <span class="font-semibold text-ink/80">Total Biaya Sewa</span>
                                <span class="font-semibold text-ink/80">Rp{{ number_format($totalSewa, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink/50">DP dibayar sekarang</span>
                                <span class="font-semibold text-ink/80">Rp{{ number_format($dp, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-ink text-cream rounded-md p-3 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] text-cream/70">Total Dibayar Sekarang</span>
                                <span class="font-display text-xl font-semibold">Rp{{ number_format($dp + ($deposit ?? 0), 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Deposit (hanya jika KTP digital) --}}
                        <div x-show="metodeKtp === 'digital'" x-transition class="p-3 rounded-md bg-cream/60 border border-line/30 mb-4">
                            <div class="flex justify-between text-[10px] mb-1">
                                <span class="font-semibold text-ink/70">Deposit Jaminan KTP Digital</span>
                                <span class="font-semibold text-ink/80">Rp{{ number_format($deposit, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-[9px] text-ink/40 leading-relaxed">Dikembalikan penuh setelah busana diterima kembali.</p>
                        </div>

                        <button type="submit" class="w-full h-[42px] rounded-lg bg-gold/95 text-white text-[10px] font-semibold tracking-wider hover:bg-gold transition-all duration-300 mb-2">
                            Lanjutkan Pembayaran
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection