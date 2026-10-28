@extends('layouts.public')

@section('title', 'Checkout Sewa — ' . config('alnay.brand.nama'))

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
    .pd-shine::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%);
        transform: translateX(-120%); transition: transform .8s ease; pointer-events: none;
    }
    .pd-shine:hover::after { transform: translateX(120%); }

    /* Titik radio: isi saat dipilih (tidak bisa lewat peer-checked karena berada di dalam kartu) */
    .opsi-radio:checked + .opsi-card .radio-dot {
        background: var(--color-gold, #ba8320);
        border-color: var(--color-gold, #ba8320);
        box-shadow: inset 0 0 0 2px #fff;
    }
    .opsi-radio:focus-visible + .opsi-card { outline: 2px solid var(--color-gold, #ba8320); outline-offset: 2px; }
    .opsi-radio:disabled + .opsi-card { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

    @media (prefers-reduced-motion: reduce) {
        .pd-up, .pd-swap, .pd-shake { animation: none !important; }
        .pd-shine::after { transition: none !important; }
    }
</style>

@php
    $tglAmbil = \Carbon\Carbon::parse($sewaData['tanggal_ambil'])->locale('id');
    $tglKembali = \Carbon\Carbon::parse($sewaData['tanggal_kembali'])->locale('id');
    $rentang = $tglAmbil->isSameMonth($tglKembali)
        ? $tglAmbil->format('d') . '–' . $tglKembali->translatedFormat('d M Y')
        : $tglAmbil->translatedFormat('d M') . ' – ' . $tglKembali->translatedFormat('d M Y');

    $tambahanHari = max(0, ($sewaData['durasi'] ?? 1) - 1);
    $biayaTambahan = $tambahanHari * (float) ($produk->denda_per_hari_telat ?? 0);

    $user = auth()->user();

    $cfg = [
        'penerimaan' => old('metode_penerimaan', 'ambil'),
        'ktp'        => old('metode_ktp', 'fisik'),
        'bayar'      => old('metode_bayar', 'qris'),
        'nama'       => old('nama_penerima', $user->name ?? ''),
        'wa'         => old('no_wa', $user->no_hp ?? ''),
        'alamat'     => old('alamat', $user->alamat ?? ''),
        'catatan'    => old('catatan', ''),
        'setuju'     => (bool) old('setuju'),
        'dp'         => (float) ($dp ?? 0),
        'deposit'    => (float) ($deposit ?? 0),
        'maksKtpMb'  => 2,
    ];
@endphp

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 pb-28 lg:pb-12 font-sans"
     x-data="checkoutSewa(@js($cfg))"
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

        <form method="POST" action="{{ route('sewa.store') }}" enctype="multipart/form-data" novalidate
              x-ref="formCheckout" @submit.prevent="kirim()">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6 lg:gap-8">

                {{-- ==================== KOLOM KIRI ==================== --}}
                <div class="space-y-4">

                    {{-- 1. Ringkasan Sewa --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5 hover:shadow-md transition-shadow duration-300" style="--d: .05s;">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <h2 class="font-display text-xl font-semibold text-ink/90 mb-1">Ringkasan Sewa</h2>
                                <p class="text-xs text-ink/55">Detail produk dan tanggal yang dipilih di halaman produk</p>
                            </div>
                            <a href="{{ route('produk.detail', ['sku' => $produk->sku, 'mode' => 'sewa']) }}"
                               class="shrink-0 text-xs font-semibold text-gold hover:underline underline-offset-2">Ubah</a>
                        </div>

                        <div class="flex gap-3 items-center border-b border-line/40 pb-4 mb-4">
                            <div class="w-16 h-16 rounded-lg bg-cream-dim border border-line/50 overflow-hidden flex items-center justify-center text-muted text-[10px] uppercase tracking-wider shrink-0">
                                @php
                                    $fotoSewa = $produk->foto_produk;
                                    if (is_array($fotoSewa)) {
                                        $fotoSewa = $fotoSewa[0] ?? null;
                                    }
                                @endphp

                                @if($fotoSewa)
                                    <img src="{{ asset('storage/' . $fotoSewa) }}" alt="{{ $produk->nama }}" class="w-full h-full object-cover">
                                @else
                                    Foto
                                @endif
                            </div>
                            <div>
                                <p class="text-[15px] font-semibold text-ink/85">{{ $produk->nama }}</p>
                                <p class="text-xs text-ink/55 mt-0.5">Ukuran {{ $sewaData['ukuran'] }} · Warna sesuai foto</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-[13px]">
                            <div>
                                <p class="text-ink/50 text-[11px] uppercase tracking-wider mb-1">Tanggal Ambil</p>
                                <p class="font-semibold text-ink/85">{{ $tglAmbil->translatedFormat('d M Y') }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-ink/50 text-[11px] uppercase tracking-wider mb-1">Sewa</p>
                                <p class="font-semibold text-ink/85">{{ $rentang }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-ink/50 text-[11px] uppercase tracking-wider mb-1">Kembali</p>
                                <p class="font-semibold text-ink/85">{{ $tglKembali->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>

                        @if ($tambahanHari > 0)
                            <div class="mt-4 p-3 rounded-md bg-gold/5 border border-gold/20 flex justify-between gap-3 text-xs">
                                <span class="text-ink/65">Tambahan hari pemakaian: {{ $tambahanHari }} hari</span>
                                <span class="font-semibold text-gold shrink-0">+Rp{{ number_format($biayaTambahan, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- 2. Verifikasi KTP --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .1s;">
                        <h2 class="font-display text-xl font-semibold text-ink/90 mb-1">Verifikasi Identitas</h2>
                        <p class="text-xs text-ink/55 mb-4">Sebagai jaminan sewa — Anda diharapkan datang langsung ke studio untuk verifikasi KTP asli.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            {{-- KTP Fisik --}}
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_ktp" value="fisik" class="opsi-radio peer sr-only" x-model="metodeKtp">
                                <div class="opsi-card h-full p-4 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 hover:-translate-y-px hover:shadow-sm transition-all duration-300">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="radio-dot w-3.5 h-3.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <p class="text-[13px] font-semibold text-ink/85">KTP Fisik (Asli)</p>
                                    </div>
                                    <p class="text-xs text-ink/60 leading-relaxed mb-2">Datang langsung ke studio, tunjukkan KTP asli. <strong class="text-ink/85">Disarankan</strong>.</p>
                                    <p class="text-xs text-green-700 flex items-center gap-1"><x-icon name="check" :size="12" /> Tidak ada deposit tambahan</p>
                                </div>
                            </label>

                            {{-- KTP Digital --}}
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_ktp" value="digital" class="opsi-radio peer sr-only" x-model="metodeKtp">
                                <div class="opsi-card h-full p-4 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 hover:-translate-y-px hover:shadow-sm transition-all duration-300">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="radio-dot w-3.5 h-3.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <p class="text-[13px] font-semibold text-ink/85">KTP Digital (Online)</p>
                                    </div>
                                    <p class="text-xs text-ink/60 leading-relaxed mb-2">Hanya jika <strong class="text-ink/85">benar-benar tidak bisa datang</strong>. Upload foto KTP.</p>
                                    <p class="text-xs font-semibold text-gold">+ Deposit jaminan Rp{{ number_format($deposit ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </label>
                        </div>

                        {{-- Upload foto KTP (hanya KTP Digital) --}}
                        <div x-show="metodeKtp === 'digital'" x-cloak class="pd-swap">
                            <div class="border border-dashed rounded-lg p-5 text-center transition-colors duration-300"
                                 :class="namaFileKtp ? 'border-gold/60 bg-gold/5' : 'border-line/60'">
                                <x-icon name="user" :size="26" class="text-ink/35 mx-auto mb-2" />
                                <p class="text-[13px] font-medium text-ink/75 mb-1" x-text="namaFileKtp || 'Upload Foto KTP'"></p>
                                <p class="text-[11px] text-ink/50 mb-3">JPG/PNG, maks {{ $cfg['maksKtpMb'] }}MB</p>
                                <input type="file" name="foto_ktp" id="upload-ktp" accept="image/png,image/jpeg" class="hidden"
                                       x-ref="fileKtp" @change="pilihKtp($event)">
                                <label for="upload-ktp"
                                       class="inline-block px-4 py-2 rounded-md border border-line/60 bg-white text-xs font-semibold text-ink/70 hover:border-gold hover:text-gold cursor-pointer active:scale-95 transition-all"
                                       x-text="namaFileKtp ? 'Ganti File' : 'Pilih File'"></label>
                            </div>
                            @error('foto_ktp') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- 3. Metode Penerimaan --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .15s;">
                        <h2 class="font-display text-xl font-semibold text-ink/90 mb-1">Metode Penerimaan</h2>
                        <p class="text-xs text-ink/55 mb-4">Jika memilih KTP Digital, busana wajib dikirim ke alamat Anda — pilihan otomatis berpindah.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label :class="metodeKtp === 'digital' ? 'cursor-not-allowed' : 'cursor-pointer'">
                                <input type="radio" name="metode_penerimaan" value="ambil" class="opsi-radio peer sr-only"
                                       x-model="metodePenerimaan" :disabled="metodeKtp === 'digital'">
                                <div class="opsi-card h-full p-4 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 hover:-translate-y-px hover:shadow-sm transition-all duration-300">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="radio-dot w-3.5 h-3.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <p class="text-[13px] font-semibold text-ink/85">Ambil di Studio</p>
                                    </div>
                                    <p class="text-xs text-ink/60 leading-relaxed">Tidak tersedia untuk KTP Digital.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="metode_penerimaan" value="kirim" class="opsi-radio peer sr-only" x-model="metodePenerimaan">
                                <div class="opsi-card h-full p-4 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 hover:-translate-y-px hover:shadow-sm transition-all duration-300">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="radio-dot w-3.5 h-3.5 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                        <p class="text-[13px] font-semibold text-ink/85">Dikirim ke Alamat</p>
                                    </div>
                                    <p class="text-xs text-ink/60 leading-relaxed">Diantar oleh kurir ke alamat Anda.</p>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded bg-gold/10 text-gold text-[10px] font-bold uppercase">Wajib untuk KTP Digital</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 4. Alamat Pengiriman --}}
                    <div class="bg-panel border border-line/40 rounded-xl p-5"
                        x-show="metodePenerimaan === 'kirim'" x-cloak
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2">
                                            <h2 class="font-display text-xl font-semibold text-ink/90 mb-1">Alamat Pengiriman</h2>
                        <p class="text-xs text-green-700 mb-4 flex items-center gap-1">
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

                    {{-- 5. Metode Pembayaran --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .25s;">
                        <h2 class="font-display text-xl font-semibold text-ink/90 mb-1">Metode Pembayaran
                            <span class="ml-2 align-middle px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase">Instan & Terverifikasi</span>
                        </h2>
                        <p class="text-xs text-ink/55 mb-4">DP dibayar online agar tanggal sewa langsung terkunci</p>

                        <div class="space-y-2">
                            <label class="cursor-pointer block">
                                <input type="radio" name="metode_bayar" value="qris" class="opsi-radio peer sr-only" x-model="metodeBayar">
                                <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 hover:-translate-y-px transition-all duration-300">
                                    <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                    <div class="flex-1">
                                        <p class="text-[13px] font-semibold text-ink/85">QRIS (Semua Bank & Dompet Digital)</p>
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
                                        <p class="text-[13px] font-semibold text-ink/85">Virtual Account Bank</p>
                                        <p class="text-xs text-ink/55">BCA, Mandiri, BNI, BRI, Permata</p>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer block">
                                <input type="radio" name="metode_bayar" value="ewallet" class="opsi-radio peer sr-only" x-model="metodeBayar">
                                <div class="opsi-card flex items-center gap-3 p-3.5 rounded-lg border border-line/60 peer-checked:border-gold peer-checked:bg-gold/5 hover:border-gold/60 hover:-translate-y-px transition-all duration-300">
                                    <span class="radio-dot w-4 h-4 rounded-full border-2 border-line shrink-0 transition-all"></span>
                                    <div class="flex-1">
                                        <p class="text-[13px] font-semibold text-ink/85">E-Wallet Direct</p>
                                        <p class="text-xs text-ink/55">GoPay, ShopeePay, OVO</p>
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- Pemberitahuan DP & persetujuan --}}
                        <div class="mt-5 flex gap-2.5 p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900 leading-relaxed">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <p><strong>Penting:</strong> DP hangus bila pesanan sewa dibatalkan, dan sewa busana tidak dapat di-refund. Pastikan tanggal dan ukuran sudah benar sebelum membayar.</p>
                        </div>

                        <label class="mt-4 flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="setuju" value="1" x-model="setuju"
                                   class="mt-0.5 w-4 h-4 rounded border-line text-gold focus:ring-gold focus:ring-offset-0">
                            <span class="text-[13px] text-ink/75 leading-relaxed">
                                Saya sudah membaca dan menyetujui
                                {{-- TODO: ganti "#" dengan link halaman Syarat & Ketentuan Sewa --}}
                                <a href="#" class="text-gold font-semibold underline underline-offset-2">syarat & ketentuan sewa</a>.
                            </span>
                        </label>
                        @error('setuju') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- ==================== KOLOM KANAN — RINGKASAN ==================== --}}
                <div class="lg:sticky lg:top-24 h-fit">
                    {{-- pd-up di pembungkus, pd-shake di kartu: dua animasi tidak boleh di elemen yang sama --}}
                    <div class="pd-up" style="--d: .3s;">
                    <div class="bg-panel border border-line/40 rounded-xl p-5 transition-colors duration-300"
                         :class="{ 'pd-shake border-rose-300': getar }">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-ink/65 mb-4">Rincian Biaya Sewa</h2>

                        <div class="space-y-2 text-[13px] mb-4">
                            <div class="flex justify-between gap-3">
                                <span class="text-ink/60">Harga sewa ({{ $sewaData['durasi'] }} hari)</span>
                                <span class="font-semibold text-ink/85">Rp{{ number_format($produk->harga_sewa, 0, ',', '.') }}</span>
                            </div>
                            @if ($tambahanHari > 0)
                                <div class="flex justify-between gap-3">
                                    <span class="text-ink/60">Tambahan hari ({{ $tambahanHari }} hari)</span>
                                    <span class="font-semibold text-ink/85">Rp{{ number_format($biayaTambahan, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-line/40 pt-3 mb-4 space-y-2 text-[13px]">
                            <div class="flex justify-between gap-3">
                                <span class="font-semibold text-ink/85">Total Biaya Sewa</span>
                                <span class="font-semibold text-ink/85">Rp{{ number_format($totalSewa, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span class="text-ink/60">DP dibayar sekarang</span>
                                <span class="font-semibold text-ink/85">Rp{{ number_format($dp, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between gap-3" x-show="metodeKtp === 'digital'" x-cloak
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0">
                                <span class="text-ink/60">Deposit jaminan KTP Digital</span>
                                <span class="font-semibold text-ink/85">Rp{{ number_format($deposit ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-ink text-cream rounded-lg p-3.5 mb-4">
                            <div class="flex justify-between items-center gap-3">
                                <span class="text-xs text-cream/75">Total Dibayar Sekarang</span>
                                <span class="font-sans text-xl font-semibold tabular-nums" x-text="formatRupiah(totalTampil)"></span>
                            </div>
                        </div>

                        <div x-show="metodeKtp === 'digital'" x-cloak
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="p-3 rounded-md bg-cream/60 border border-line/30 mb-4">
                            <p class="text-xs font-semibold text-ink/75 mb-1">Deposit dikembalikan penuh</p>
                            <p class="text-[11px] text-ink/55 leading-relaxed">Deposit jaminan KTP Digital dikembalikan setelah busana diterima kembali.</p>
                        </div>

                        <p x-ref="errorBox" x-show="errorMetode" x-cloak x-transition x-text="errorMetode"
                           class="mb-3 p-2.5 rounded-md bg-rose-50 border border-rose-200 text-xs text-rose-700"></p>

                        <button type="submit" :disabled="sedangKirim"
                                class="pd-shine w-full h-[46px] rounded-lg bg-gold text-white text-[13px] font-semibold tracking-wide hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait disabled:hover:translate-y-0 transition-all duration-300">
                            <span x-show="!sedangKirim">Lanjutkan Pembayaran</span>
                            <span x-show="sedangKirim" x-cloak class="inline-flex items-center justify-center gap-2">
                                <svg class="pd-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 3a9 9 0 1 0 9 9" /></svg>
                                Memproses…
                            </span>
                        </button>
                    </div>
                    </div>
                </div>
            </div>

            {{-- Bilah bayar menempel di bawah (HP) --}}
            <div class="lg:hidden fixed inset-x-0 bottom-0 z-30 bg-white/95 backdrop-blur border-t border-line/50 px-4 py-3 flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] text-ink/55">Total dibayar sekarang</p>
                    <p class="font-display text-lg font-semibold text-ink tabular-nums truncate" x-text="formatRupiah(totalTampil)"></p>
                </div>
                <button type="submit" :disabled="sedangKirim"
                        class="shrink-0 h-11 px-5 rounded-lg bg-gold text-white text-[13px] font-semibold active:scale-[0.98] disabled:opacity-70 transition-all">
                    <span x-text="sedangKirim ? 'Memproses…' : 'Bayar'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutSewa', (cfg) => {
            const reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

            return {
                metodePenerimaan: cfg.penerimaan,
                metodeKtp: cfg.ktp,
                metodeBayar: cfg.bayar,
                namaPenerima: cfg.nama,
                noWa: cfg.wa,
                alamat: cfg.alamat,
                catatan: cfg.catatan,
                setuju: cfg.setuju,
                dp: cfg.dp,
                deposit: cfg.deposit,
                maksKtpMb: cfg.maksKtpMb,

                namaFileKtp: '',
                errorMetode: '',
                getar: false,
                sedangKirim: false,
                totalTampil: 0,
                _anim: null,

                get totalBayar() {
                    return this.dp + (this.metodeKtp === 'digital' ? this.deposit : 0);
                },

                init() {
                    this.totalTampil = this.totalBayar;
                    if (this.metodeKtp === 'digital') this.metodePenerimaan = 'kirim';

                    this.$watch('metodeKtp', (v) => {
                        if (v === 'digital') {
                            this.metodePenerimaan = 'kirim';
                        } else if (this.$refs.fileKtp) {
                            this.$refs.fileKtp.value = '';
                            this.namaFileKtp = '';
                        }
                        this.tweenTotal(this.totalBayar);
                    });
                },

                tweenTotal(target) {
                    const dari = this.totalTampil;
                    if (reduce || dari === target) {
                        this.totalTampil = target;
                        return;
                    }
                    const mulai = performance.now();
                    const dur = 400;
                    cancelAnimationFrame(this._anim);
                    const langkah = (t) => {
                        const p = Math.min(1, (t - mulai) / dur);
                        const e = 1 - Math.pow(1 - p, 3);
                        this.totalTampil = Math.round(dari + (target - dari) * e);
                        if (p < 1) this._anim = requestAnimationFrame(langkah);
                    };
                    this._anim = requestAnimationFrame(langkah);
                },

                pilihKtp(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) { this.namaFileKtp = ''; return; }

                    if (!['image/jpeg', 'image/png'].includes(file.type)) {
                        e.target.value = '';
                        this.namaFileKtp = '';
                        return this.tampilError('Foto KTP harus berformat JPG atau PNG.');
                    }
                    if (file.size > this.maksKtpMb * 1024 * 1024) {
                        e.target.value = '';
                        this.namaFileKtp = '';
                        return this.tampilError('Ukuran foto KTP maksimal ' + this.maksKtpMb + 'MB.');
                    }
                    this.namaFileKtp = file.name;
                    this.errorMetode = '';
                },

                tampilError(pesan) {
                    this.errorMetode = pesan;
                    this.getar = false;
                    this.$nextTick(() => {
                        this.getar = true;
                        setTimeout(() => { this.getar = false; }, 500);
                        if (this.$refs.errorBox) {
                            this.$refs.errorBox.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
                        }
                    });
                    return false;
                },

                validasi() {
                    if (!this.metodePenerimaan) return this.tampilError('Pilih metode penerimaan.');
                    if (!this.metodeKtp) return this.tampilError('Pilih metode verifikasi KTP.');
                    if (!this.metodeBayar) return this.tampilError('Pilih metode pembayaran.');
                    if (this.metodeKtp === 'digital' && this.metodePenerimaan !== 'kirim') {
                        return this.tampilError('KTP Digital hanya tersedia untuk pengiriman ke alamat.');
                    }
                    if (this.metodeKtp === 'digital' && !this.namaFileKtp) {
                        return this.tampilError('Upload foto KTP terlebih dahulu.');
                    }
                    if (this.metodePenerimaan === 'kirim') {
                        if (!this.namaPenerima.trim() || !this.noWa.trim() || !this.alamat.trim()) {
                            return this.tampilError('Lengkapi nama penerima, no. WhatsApp, dan alamat pengiriman.');
                        }
                    }
                    if (!this.setuju) return this.tampilError('Centang persetujuan syarat & ketentuan sewa.');

                    this.errorMetode = '';
                    return true;
                },

                kirim() {
                    if (this.sedangKirim) return;
                    if (!this.validasi()) return;
                    this.sedangKirim = true;
                    this.$refs.formCheckout.submit();
                },

                formatRupiah(n) {
                    return 'Rp' + new Intl.NumberFormat('id-ID').format(n);
                },
            };
        });
    });
</script>

@endsection