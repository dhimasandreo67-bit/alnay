{{--
    Panel QRIS bersama (Sewa & Beli).
    Butuh: $transaksi (dengan relasi pembayaran), $nominal (int, opsional).
--}}
@php
    $tagihanQ = $transaksi->pembayaran->sortByDesc('id')->first();
    $nominalQ = (int) ($nominal ?? ($tagihanQ->jumlah ?? $transaksi->total));
    $isSewa = $transaksi->jenis === 'sewa';
    $cfgQris = [
        'chargeUrl'  => route('qris.charge', $transaksi->id),
        'statusUrl'  => route('qris.status', $transaksi->id),
        'csrf'       => csrf_token(),
        'sudahBayar' => in_array($transaksi->status_pembayaran, ['dp_dibayar', 'lunas'], true),
    ];
@endphp

<div x-data="qrisPanel(@js($cfgQris))">

    {{-- Hitung mundur --}}
    <div x-show="state === 'ready' || state === 'expired'"
        class="bg-gold/5 border border-gold/20 rounded-lg px-4 py-3 flex justify-between items-center mb-6 transition-colors"
        :class="{ 'border-rose-300 bg-rose-50': state === 'expired' }">
        <span class="text-[11px] text-ink/60" x-text="state === 'expired' ? 'Waktu pembayaran habis' : 'Selesaikan pembayaran dalam'"></span>
        <span class="font-display text-lg font-medium tabular-nums" :class="state === 'expired' ? 'text-rose-600' : 'text-gold'" x-text="waktu"></span>
    </div>

    <div class="flex flex-col items-center mb-6">
        <div class="w-64 h-64 bg-white border border-line/40 rounded-lg flex items-center justify-center p-2 relative shadow-sm">

            <p x-show="state === 'loading'" class="text-[10px] text-ink/40 uppercase tracking-wider animate-pulse">Memuat QR Code...</p>

            <img x-show="state === 'ready'" x-cloak :src="qrUrl" alt="QRIS" class="w-full h-full object-contain">

            {{-- Tampilan Jika Sudah Lunas --}}
            <div x-show="state === 'paid'" x-cloak class="text-center px-4">
                <div class="w-14 h-14 mx-auto rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-sm font-semibold text-emerald-700">Pembayaran diterima</p>
                <p class="text-[10px] text-ink/50 mt-1 mb-4">Terima kasih, transaksi Anda sedang diproses.</p>
                
                <div class="flex flex-col gap-2">
                    @if($isSewa)
                        <a href="{{ route('fitting.create') }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-all">Booking Fitting</a>
                    @endif
                    <a href="{{ route('akun.dashboard') }}#penyewaan" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg bg-gold text-white text-xs font-bold hover:bg-gold/90 transition-all">Lihat Riwayat Pesanan</a>
                    <a href="{{ route('beranda') }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg border border-line text-ink text-xs font-bold hover:bg-cream transition-all">Kembali ke Beranda</a>
                </div>
            </div>

            {{-- Tampilan Jika Kedaluwarsa --}}
            <div x-show="state === 'expired'" x-cloak class="text-center px-4">
                <p class="text-sm font-semibold text-rose-600 mb-1">QR kedaluwarsa</p>
                <p class="text-[10px] text-ink/50 mb-3">Buat QR baru untuk melanjutkan pembayaran.</p>
                <button type="button" @click="buat(true)" class="h-9 px-4 rounded-lg bg-gold text-white text-[11px] font-semibold tracking-wider hover:bg-gold/90 transition-colors">Buat QR Baru</button>
            </div>

            {{-- Tampilan Jika Error --}}
            <div x-show="state === 'error'" x-cloak class="text-center px-3">
                <p class="text-sm font-semibold text-rose-600 mb-1">QR belum bisa dimuat</p>
                <p class="text-[10px] text-ink/55 mb-3 break-words" x-text="error"></p>
                <button type="button" @click="buat(false)" class="h-9 px-4 rounded-lg bg-gold text-white text-[11px] font-semibold tracking-wider hover:bg-gold/90 transition-colors">Coba Lagi</button>
            </div>
        </div>

        <p class="font-sans text-3xl font-semibold text-ink/85 mt-4 tabular-nums">Rp{{ number_format($nominalQ, 0, ',', '.') }}</p>
        <p class="text-[10px] text-ink/40 mt-1">AL-NAY BUSANA ADAT</p>
    </div>

    <div x-show="state === 'ready'" class="mb-6">
        <button type="button" @click="periksa(true)" :disabled="memeriksa"
                class="w-full h-[42px] rounded-lg bg-gold text-white text-[11px] font-semibold tracking-wider hover:bg-gold/90 transition-colors disabled:opacity-60 shadow-md">
            <span x-text="memeriksa ? 'Memeriksa...' : 'Cek Status Pembayaran'"></span>
        </button>
        
        {{-- PERUBAHAN UI: Peringatan yang Lebih Mencolok --}}
        <div x-show="info" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-4 flex items-start gap-2.5 p-3.5 rounded-xl bg-amber-50 border border-amber-200 shadow-sm">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-[13px] font-semibold text-amber-900 leading-relaxed" x-text="info"></p>
        </div>
    </div>
    
    <hr class="border-line/30 my-6">

    <div class="space-y-4">
        @foreach (['Buka aplikasi pembayaran (m-banking, GoPay, OVO, ShopeePay, Dana, dsb).', 'Pilih menu Scan QRIS, lalu arahkan kamera ke kode di atas.', 'Periksa nama merchant dan nominal — pastikan sesuai.', 'Selesaikan pembayaran; halaman ini akan berubah otomatis setelah pembayaran diterima.'] as $i => $langkah)
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-cream text-ink/60 text-[10px] font-semibold flex items-center justify-center shrink-0 border border-line/50">{{ $i + 1 }}</span>
                <p class="text-[11px] text-ink/60 leading-relaxed">{{ $langkah }}</p>
            </div>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('qrisPanel', (cfg) => ({
            state: cfg.sudahBayar ? 'paid' : 'loading',
            qrUrl: null,
            error: '',
            info: '',
            sisa: 0,
            memeriksa: false,
            _tick: null,
            _poll: null,

            init() {
                if (this.state !== 'paid') this.buat(false);
            },

            get waktu() {
                const m = Math.floor(Math.max(0, this.sisa) / 60);
                const s = Math.max(0, this.sisa) % 60;
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            },

            headers() {
                return {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': cfg.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                };
            },

            stop() {
                clearInterval(this._tick);
                clearInterval(this._poll);
            },

            gagal(pesan) {
                this.stop();
                this.error = pesan;
                this.state = 'error';
            },

            berhasil() {
                this.stop();
                this.state = 'paid';
                setTimeout(() => window.location.reload(), 1800);
            },

            kedaluwarsa() {
                this.stop();
                this.state = 'expired';
                this.sisa = 0;
            },

            async buat(baru) {
                this.stop();
                this.state = 'loading';
                this.error = '';
                this.info = '';
                try {
                    const res = await fetch(cfg.chargeUrl, {
                        method: 'POST',
                        headers: this.headers(),
                        credentials: 'same-origin',
                        body: JSON.stringify({ baru: baru }),
                    });
                    if (res.status === 401 || res.status === 419) {
                        return this.gagal('Sesi Anda berakhir. Muat ulang halaman lalu masuk kembali.');
                    }
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        return this.gagal(data.message || ('Server menjawab ' + res.status + '.'));
                    }
                    if (data.paid) return this.berhasil();
                    if (!data.qr_url) {
                        return this.gagal('Respons server tidak dikenali. Muat ulang halaman.');
                    }
                    this.qrUrl = data.qr_url;
                    this.sisa = data.expires_in;
                    this.state = 'ready';
                    this.mulai();
                } catch (e) {
                    this.gagal('Tidak dapat terhubung ke server.');
                }
            },

            mulai() {
                this._tick = setInterval(() => {
                    this.sisa--;
                    if (this.sisa <= 0) {
                        this.periksa(false).then(() => {
                            if (this.state === 'ready') this.kedaluwarsa();
                        });
                    }
                }, 1000);
                this._poll = setInterval(() => this.periksa(false), 4000);
            },

            async periksa(manual) {
                if (this.memeriksa) return;
                this.memeriksa = manual;
                this.info = ''; // Reset info setiap kali memeriksa
                try {
                    const res = await fetch(cfg.statusUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) throw new Error('status ' + res.status);
                    const data = await res.json();
                    
                    if (data.status === 'paid') return this.berhasil();
                    if (data.status === 'expired') return this.kedaluwarsa();
                    
                    if (manual) {
                        this.info = 'Pembayaran belum masuk. Jika Anda baru saja membayar, tunggu 1-2 menit lalu cek lagi.';
                    }
                } catch (e) {
                    if (manual) this.info = 'Gagal memeriksa status. Periksa koneksi internet Anda lalu coba lagi.';
                } finally {
                    this.memeriksa = false;
                }
            },
        }));
    });
</script>