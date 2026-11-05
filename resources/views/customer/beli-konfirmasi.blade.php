@extends('layouts.public')

@section('title', 'Konfirmasi Pembayaran — ' . config('alnay.brand.nama'))

@section('content')

    <style>
        [x-cloak] { display: none !important; }

        @keyframes kb-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        @keyframes kb-pop { 0% { opacity: 0; transform: scale(.6); } 60% { transform: scale(1.08); } 100% { opacity: 1; transform: scale(1); } }
        @keyframes kb-draw { to { stroke-dashoffset: 0; } }
        @keyframes kb-ring { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, .45); } 100% { box-shadow: 0 0 0 26px rgba(16, 185, 129, 0); } }
        @keyframes kb-pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.7); opacity: .35; } }
        @keyframes kb-shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
        @keyframes kb-sweep { 0% { transform: translateX(-100%); } 100% { transform: translateX(250%); } }
        @keyframes kb-spin { to { transform: rotate(360deg); } }

        .kb-up { animation: kb-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
        .kb-pop { animation: kb-pop .6s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
        .kb-shake { animation: kb-shake .5s ease; }
        .kb-spin { animation: kb-spin .8s linear infinite; }

        .kb-ring { animation: kb-ring 1.6s ease-out 2; }

        .kb-check path {
            stroke-dasharray: 1;
            stroke-dashoffset: 1;
            animation: kb-draw .6s ease-out .35s forwards;
        }

        .kb-dot { position: relative; }
        .kb-dot::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background: currentColor;
            animation: kb-pulse 1.6s ease-in-out infinite;
        }

        .kb-bar { position: relative; overflow: hidden; }
        .kb-bar::after {
            content: '';
            position: absolute;
            inset: 0;
            width: 40%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .75), transparent);
            animation: kb-sweep 1.8s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .kb-up, .kb-pop, .kb-shake, .kb-ring, .kb-dot::after, .kb-bar::after, .kb-spin { animation: none !important; }
            .kb-check path { animation: none !important; stroke-dashoffset: 0; }
            .kb-up, .kb-pop { opacity: 1; }
        }
    </style>

    @php
        $lunas  = in_array($transaksi->status_pembayaran, ['lunas', 'dp_dibayar']);
        $alamat = $transaksi->pengiriman->alamat_tujuan ?? null;
        $isSewa = $transaksi->jenis === 'sewa';

        $cfg = [
            'lunas'     => $lunas,
            'urlStatus' => route('qris.status', $transaksi->id),
        ];
    @endphp

    <div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans" x-data="konfirmasiBayar(@js($cfg))">
        <div class="max-w-[1180px] mx-auto px-4 md:px-6">

            <nav class="kb-up text-[11px] text-ink/50 mb-5" style="--d: .05s;">
                <a href="{{ route('koleksi.index') }}" class="hover:text-gold transition-colors inline-flex items-center gap-1">
                    <x-icon name="chevron-right" :size="12" class="rotate-180" />
                    Kembali ke Katalog
                </a>
            </nav>

            @if (session('success') && ! $lunas)
                <div class="kb-up p-3 mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-xs text-emerald-700" style="--d: .08s;">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ============ BANNER STATUS (besar, selalu terlihat) ============ --}}
            <div class="kb-up mb-6" style="--d: .1s;">

                {{-- Menunggu --}}
                <div x-show="status === 'menunggu'" x-cloak
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 shadow-sm">
                    <div class="flex items-start gap-3.5">
                        <span class="kb-dot mt-1.5 w-3 h-3 rounded-full bg-amber-500 text-amber-500 shrink-0"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-[15px] md:text-base font-semibold text-amber-900">Menunggu pembayaran</p>
                            <p class="text-[13px] text-amber-800/80 mt-0.5 leading-relaxed">
                                Pesanan belum diproses sampai pembayaran kami terima. Halaman ini memeriksa status secara otomatis.
                            </p>
                        </div>
                    </div>
                    <div class="kb-bar mt-3.5 h-1.5 rounded-full bg-amber-200/70"></div>
                </div>

                {{-- Lunas --}}
                <div x-show="status === 'lunas'" x-cloak
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-4 shadow-sm">
                    <div class="flex items-center gap-3.5">
                        <span class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div>
                            <p class="text-[15px] md:text-base font-semibold text-emerald-900">Pembayaran diterima</p>
                            <p class="text-[13px] text-emerald-800/80 mt-0.5">Terima kasih, pesanan Anda sudah kami catat.</p>
                        </div>
                    </div>
                </div>

                {{-- Gagal / kedaluwarsa --}}
                <div x-show="status === 'gagal'" x-cloak
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="rounded-xl border border-rose-300 bg-rose-50 px-5 py-4 shadow-sm">
                    <div class="flex items-start gap-3.5">
                        <span class="w-9 h-9 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
                        </span>
                        <div>
                            <p class="text-[15px] md:text-base font-semibold text-rose-900">Pembayaran tidak berhasil</p>
                            <p class="text-[13px] text-rose-800/80 mt-0.5 leading-relaxed">
                                Kode pembayaran sudah kedaluwarsa atau dibatalkan. Buat pesanan baru dari katalog, atau muat ulang halaman untuk meminta kode baru.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-6 lg:gap-8">

                {{-- ============ KIRI ============ --}}
                <div class="kb-up bg-panel border border-line/40 rounded-xl p-6 md:p-8" style="--d: .15s;">

                    {{-- Tampilan LUNAS (hanya satu, menggantikan panel QRIS) --}}
                    <div x-show="status === 'lunas'" x-cloak
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="text-center py-6 md:py-10">
                            <div class="kb-ring kb-pop w-24 h-24 mx-auto rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center mb-5">
                                <svg class="kb-check w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path pathLength="1" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>

                            <h1 class="kb-up font-display text-2xl md:text-3xl font-medium text-ink/85 mb-2" style="--d: .25s;">Pembayaran Berhasil</h1>
                            <p class="kb-up text-sm text-ink/55 max-w-md mx-auto mb-7 leading-relaxed" style="--d: .35s;">
                                {{ $isSewa
                                    ? 'Terima kasih. Silakan datang ke studio untuk fitting sesuai jadwal.'
                                    : 'Terima kasih. Pesanan Anda akan segera kami siapkan dan kirim. Nomor resi dikirim lewat WhatsApp.' }}
                            </p>

                            <div class="kb-up flex flex-col sm:flex-row gap-3 justify-center" style="--d: .45s;">
                                @if ($isSewa)
                                    <a href="{{ route('fitting.create') }}"
                                        class="px-6 py-3 rounded-xl bg-gold text-white text-sm font-bold hover:bg-gold/90 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 transition-all duration-300">Booking Fitting</a>
                                @endif
                                <a href="{{ route('akun.pesanan.index') }}"
                                    class="px-6 py-3 rounded-xl border border-line/60 bg-white text-ink/75 text-sm font-bold hover:bg-cream/50 hover:-translate-y-0.5 transition-all duration-300">Lihat Riwayat Pesanan</a>
                            </div>
                        </div>
                    </div>

                    {{-- Tampilan BELUM LUNAS: judul + panel QRIS + tombol cek status --}}
                    @unless ($lunas)
                        <div x-show="status !== 'lunas'" x-cloak>
                            <h1 class="font-display text-2xl md:text-3xl font-medium text-ink/85 mb-2">Bayar dengan QRIS</h1>
                            <p class="text-[13px] text-ink/55 mb-6 leading-relaxed">
                                Pindai kode di bawah menggunakan aplikasi m-banking, dompet digital, atau QRIS scanner favorit Anda.
                            </p>

                            @include('customer.partials.qris-panel', ['transaksi' => $transaksi, 'nominal' => (int) $total])

                            {{-- Cek status manual --}}
                            <div class="mt-6 pt-6 border-t border-line/40">
                                <button type="button" @click="cekManual()" :disabled="mengecek"
                                    class="w-full h-12 rounded-xl border border-gold/60 bg-white text-gold text-[13px] font-semibold hover:bg-gold hover:text-white disabled:opacity-70 disabled:cursor-wait transition-all duration-300 inline-flex items-center justify-center gap-2">
                                    <svg x-show="mengecek" x-cloak class="kb-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 3a9 9 0 1 0 9 9"/></svg>
                                    <span x-text="mengecek ? 'Memeriksa pembayaran…' : 'Saya Sudah Bayar, Cek Status'"></span>
                                </button>

                                {{-- Hasil cek: peringatan jelas --}}
                                <div x-show="hasilCek === 'belum'" x-cloak
                                    :class="{ 'kb-shake': getar }"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-4">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L2.4 18a2 2 0 001.7 3h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
                                        <div>
                                            <p class="text-sm font-semibold text-amber-900">Pembayaran belum kami terima</p>
                                            <p class="text-[13px] text-amber-800/85 mt-1 leading-relaxed">
                                                Pastikan Anda sudah menyelesaikan pembayaran di aplikasi. Jika sudah, tunggu sekitar satu menit lalu periksa lagi. Halaman ini juga diperbarui otomatis.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="hasilCek === 'error'" x-cloak
                                    class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-[13px] text-rose-700">
                                    Gagal memeriksa status. Periksa koneksi internet Anda lalu coba lagi.
                                </div>
                            </div>
                        </div>
                    @endunless
                </div>

                {{-- ============ KANAN: Ringkasan ============ --}}
                <div class="lg:sticky lg:top-24 h-fit">
                    <div class="kb-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .2s;">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-[10px] font-medium uppercase tracking-wider text-ink/50">Ringkasan Pesanan</h2>

                            <span x-show="status === 'menunggu'" x-cloak
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold uppercase tracking-wide">
                                <span class="kb-dot w-1.5 h-1.5 rounded-full bg-amber-500 text-amber-500"></span> Menunggu
                            </span>
                            <span x-show="status === 'lunas'" x-cloak
                                class="kb-pop inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wide">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Lunas
                            </span>
                            <span x-show="status === 'gagal'" x-cloak
                                class="inline-flex items-center px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold uppercase tracking-wide">Gagal</span>
                        </div>

                                                <div class="space-y-3 mb-4 max-h-60 overflow-y-auto pr-2">
                            @foreach ($transaksi->detail as $item)
                                @php
                                    // Ambil data foto produk
                                    $foto = $item->produk->foto_produk ?? null;
                                    
                                    // Antisipasi jika foto disimpan dalam bentuk array/JSON
                                    if (is_string($foto) && str_starts_with($foto, '[')) {
                                        $decoded = json_decode($foto, true);
                                        $foto = is_array($decoded) ? ($decoded[0] ?? null) : $foto;
                                    } elseif (is_array($foto)) {
                                        $foto = $foto[0] ?? null;
                                    }
                                @endphp

                                <div class="flex gap-3 items-center border-b border-line/30 pb-3">
                                    {{-- Tampilkan Foto Produk --}}
                                    <div class="w-12 h-12 rounded-lg bg-cream-dim border border-line/40 flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($foto)
                                            <img src="{{ asset('storage/' . $foto) }}" alt="{{ $item->produk->nama ?? 'Produk' }}" class="w-full h-full object-cover">
                                        @else
                                            {{-- Placeholder jika tidak ada foto --}}
                                            <x-icon name="sparkle" :size="16" class="text-ink/30" />
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[12px] font-medium text-ink/80 truncate">{{ $item->produk->nama ?? 'Produk' }}</p>
                                        @if (! empty($spesifikasi[$loop->index]) && $spesifikasi[$loop->index] !== '-')
                                            <p class="text-[10px] text-ink/50 truncate">{{ $spesifikasi[$loop->index] }}</p>
                                        @endif
                                        <p class="text-[10px] text-ink/50">{{ $item->jumlah }}x Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                                    </div>
                                    <span class="text-[11px] font-bold text-ink/80 shrink-0">Rp{{ number_format($item->jumlah * $item->harga_satuan, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-2 text-[11px] mb-4">
                            <div class="flex justify-between">
                                <span class="text-ink/50">Subtotal</span>
                                <span class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($transaksi->total - ($transaksi->pengiriman->biaya_kirim ?? 0), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink/50">Ongkos kirim</span>
                                <span class="font-medium text-ink/80 tabular-nums">Rp{{ number_format($transaksi->pengiriman->biaya_kirim ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center border-t border-line/40 pt-4 mb-4">
                            <span class="text-[11px] font-medium text-ink/80">Total</span>
                            <span class="font-sans text-xl font-semibold text-gold tabular-nums">Rp{{ number_format($transaksi->total, 0, ',', '.') }}</span>
                        </div>

                        @if ($alamat)
                            <div class="bg-cream/60 rounded-md px-3 py-2 mb-3">
                                <p class="text-[9px] text-ink/40 mb-0.5">Dikirim ke</p>
                                <p class="text-[10px] text-ink/75 leading-relaxed">{{ $alamat }}</p>
                            </div>
                        @endif

                        <div class="bg-cream/60 rounded-md px-3 py-2 flex justify-between items-center">
                            <span class="text-[9px] text-ink/40">ID Transaksi</span>
                            <span class="text-[10px] font-medium text-ink/80">{{ $transaksi->kode_transaksi }}</span>
                        </div>

                        <p class="text-[10px] text-ink/40 leading-relaxed text-center mt-4">
                            Status pembayaran diperbarui otomatis.<br>Halaman ini bisa ditutup setelah Anda membayar.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('konfirmasiBayar', (cfg) => {
                const SUDAH = ['lunas', 'dp_dibayar', 'settlement', 'capture', 'paid', 'success'];
                const GAGAL = ['expire', 'expired', 'cancel', 'canceled', 'deny', 'failure', 'gagal'];

                const bacaStatus = (json) => {
                    if (json && json.paid === true) return 'lunas';
                    const s = String(json?.status ?? '').toLowerCase();
                    if (SUDAH.includes(s)) return 'lunas';
                    if (GAGAL.includes(s)) return 'gagal';
                    return 'menunggu';
                };

                return {
                    status: cfg.lunas ? 'lunas' : 'menunggu',
                    mengecek: false,
                    hasilCek: '',
                    getar: false,
                    timer: null,

                    init() {
                        if (this.status !== 'menunggu') return;

                        this.timer = setInterval(() => {
                            if (!document.hidden) this.periksa(false);
                        }, 4000);

                        document.addEventListener('visibilitychange', () => {
                            if (!document.hidden && this.status === 'menunggu') this.periksa(false);
                        });
                    },

                    async periksa(manual) {
                        try {
                            const res = await fetch(cfg.urlStatus, { headers: { 'Accept': 'application/json' } });
                            if (!res.ok) throw new Error('HTTP ' + res.status);
                            const baru = bacaStatus(await res.json());

                            if (baru !== this.status) this.ubah(baru);
                            return baru;
                        } catch (e) {
                            if (manual) this.hasilCek = 'error';
                            return null;
                        }
                    },

                    ubah(baru) {
                        this.status = baru;
                        if (baru !== 'menunggu') {
                            clearInterval(this.timer);
                            this.hasilCek = '';
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            if (baru === 'lunas') document.title = 'Pembayaran Berhasil — ' + document.title;
                        }
                    },

                    async cekManual() {
                        if (this.mengecek) return;
                        this.mengecek = true;
                        this.hasilCek = '';

                        // Jeda singkat supaya pengguna melihat proses pemeriksaan.
                        const [hasil] = await Promise.all([
                            this.periksa(true),
                            new Promise(r => setTimeout(r, 900)),
                        ]);

                        this.mengecek = false;

                        if (hasil === 'menunggu') {
                            this.hasilCek = 'belum';
                            this.getar = false;
                            this.$nextTick(() => {
                                this.getar = true;
                                setTimeout(() => this.getar = false, 600);
                            });
                        }
                    },
                };
            });
        });
    </script>

@endsection