{{--
    Gaya + komponen Alpine 'fittingForm' yang dipakai BERSAMA oleh halaman Booking Fitting (buat)
    dan Ubah Jadwal Fitting, supaya keduanya selalu menampilkan informasi yang sama.
    cfg.mode = 'ubah' -> memakai tanggalDefault / jamDefault dan tidak memeriksa nama & WhatsApp.
--}}
    <style>
        [x-cloak] { display: none !important; }

        @keyframes pd-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        @keyframes pd-pop { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }
        @keyframes pd-shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
        @keyframes pd-spin { to { transform: rotate(360deg); } }
        @keyframes pd-pulse { 0%, 100% { opacity: .4; } 50% { opacity: 1; } }

        .pd-up { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
        .pd-pop { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }
        .pd-shake { animation: pd-shake .45s ease; }
        .pd-spin { animation: pd-spin .8s linear infinite; }
        .pd-pulse { animation: pd-pulse 2s ease-in-out infinite; }

        .pd-shine { position: relative; overflow: hidden; }
        .pd-shine::after { content: ''; position: absolute; inset: 0; background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%); transform: translateX(-120%); transition: transform .8s ease; pointer-events: none; }
        .pd-shine:hover::after { transform: translateX(120%); }

        @media (prefers-reduced-motion: reduce) {
            .pd-up, .pd-pop, .pd-shake, .pd-spin, .pd-pulse { animation: none !important; }
            .pd-shine::after { transition: none !important; }
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fittingForm', (cfg) => {
                const pad = (n) => String(n).padStart(2, '0');
                const iso = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
                const now = new Date();
                const reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

                // Mode 'ubah' (halaman ubah jadwal) memakai komponen yang sama dengan halaman booking baru.
                const modeUbah = cfg.mode === 'ubah';
                let awalBulan = now.getMonth();
                let awalTahun = now.getFullYear();
                if (cfg.tanggalDefault) {
                    const [y, m] = cfg.tanggalDefault.split('-').map(Number);
                    awalTahun = y;
                    awalBulan = m - 1;
                }

                return {
                    tanggalPenuh: cfg.tanggalPenuh,
                    slotJam: cfg.slotJam,
                    slotTerisi: cfg.slotTerisi,
                    kapasitas: cfg.kapasitas,
                    slotTutup: cfg.slotTutup,
                    tanggalTutup: cfg.tanggalTutup,
                    tanggalMaksimal: cfg.tanggalMaksimal,
                    tanggalDipilih: cfg.tanggalDefault || null,
                    jamDipilih: cfg.jamDefault || null,
                    nama: cfg.nama,
                    noWa: cfg.noWa,
                    produk: cfg.produkDefault,
                    catatan: '',
                    tahun: awalTahun,
                    bulan: awalBulan,
                    hariIni: iso(now.getFullYear(), now.getMonth(), now.getDate()),
                    namaHari: ['S', 'S', 'R', 'K', 'J', 'S', 'M'],
                    gantiBulan: false,
                    pesanTanggal: '',
                    errorMetode: '',
                    getar: false,
                    sedangKirim: false,

                    get namaBulan() {
                        return new Date(Date.UTC(this.tahun, this.bulan, 1)).toLocaleDateString('id-ID', { month: 'long', year: 'numeric', timeZone: 'UTC' });
                    },
                    get bisaMundur() {
                        return this.tahun > now.getFullYear() || (this.tahun === now.getFullYear() && this.bulan > now.getMonth());
                    },
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

                    transisiBulan(fn) {
                        if (reduce) { fn(); return; }
                        this.gantiBulan = true;
                        setTimeout(() => { fn(); this.gantiBulan = false; }, 150);
                    },
                    bulanSebelumnya() {
                        if (!this.bisaMundur) return;
                        this.transisiBulan(() => {
                            if (this.bulan === 0) { this.bulan = 11; this.tahun--; } else { this.bulan--; }
                        });
                    },
                    bulanBerikutnya() {
                        this.transisiBulan(() => {
                            if (this.bulan === 11) { this.bulan = 0; this.tahun++; } else { this.bulan++; }
                        });
                    },

                    lalu(t) { return t < this.hariIni; },
                    penuh(t) { return this.tanggalPenuh.includes(t) || this.tanggalTutup.includes(t); },
                    nonaktif(t) {
                        if (this.tanggalMaksimal && t > this.tanggalMaksimal) return true;
                        return this.lalu(t) || this.penuh(t);
                    },

                    kelasTanggal(c) {
                        if (this.tanggalDipilih === c.t) return 'bg-ink/90 text-gold font-semibold scale-105 shadow-sm';
                        if (c.luar || this.lalu(c.t)) return 'text-ink/20 cursor-default';
                        if (this.tanggalMaksimal && c.t > this.tanggalMaksimal) return 'bg-ink/5 text-ink/30 cursor-not-allowed';
                        if (c.t === this.hariIni) return 'ring-1 ring-gold/60 text-ink/80 hover:bg-cream hover:scale-110 active:scale-95';
                        return 'text-ink/60 hover:bg-cream hover:text-ink hover:scale-110 active:scale-95';
                    },

                    pilihTanggal(t) {
                        if (this.nonaktif(t)) return;
                        this.tanggalDipilih = t;
                        this.jamDipilih = null;
                        this.pesanTanggal = '';
                        this.errorMetode = '';
                    },

                    terisi(jam) {
                        const hari = this.slotTerisi[this.tanggalDipilih];
                        return hari ? (hari[jam] || 0) : 0;
                    },
                    sisa(jam) { return Math.max(0, this.kapasitas - this.terisi(jam)); },
                    jamTerisi(jam) { return !!this.tanggalDipilih && this.terisi(jam) >= this.kapasitas; },
                    jamLewat(jam) {
                        if (this.tanggalDipilih !== this.hariIni) return false;
                        const [h, m] = jam.split(':').map(Number);
                        const skrg = new Date();
                        return (h * 60 + m) <= (skrg.getHours() * 60 + skrg.getMinutes());
                    },
                    jamTutup(jam) {
                        const hari = this.slotTutup[this.tanggalDipilih];
                        return !!(hari && hari[jam]);
                    },
                    bisaJam(jam) { return !this.jamTutup(jam) && !this.jamTerisi(jam) && !this.jamLewat(jam); },
                    get semuaTidakTersedia() {
                        return !!this.tanggalDipilih && this.slotJam.every((j) => !this.bisaJam(j));
                    },

                    labelJam(jam) {
                        if (!this.tanggalDipilih) return '—';
                        if (this.jamTutup(jam)) return 'Ditutup';
                        if (this.jamLewat(jam)) return 'Sudah lewat';
                        if (this.jamTerisi(jam)) return 'Penuh';
                        if (this.terisi(jam) === 0) return 'Kosong';
                        return 'Sisa ' + this.sisa(jam) + ' tamu';
                    },

                    kelasJam(jam) {
                        if (this.jamDipilih === jam) return 'bg-ink/90 text-gold border-ink/90';
                        if (!this.tanggalDipilih || this.jamTutup(jam) || this.jamLewat(jam)) return 'bg-cream/60 text-ink/30 border-line/30 cursor-not-allowed';
                        if (this.jamTerisi(jam)) return 'bg-rose-50 text-rose-400 border-rose-100 cursor-not-allowed';
                        if (this.sisa(jam) < this.kapasitas) return 'bg-amber-50 text-amber-800 border-amber-200 hover:border-gold/60';
                        return 'bg-white text-ink/70 border-line/50 hover:border-gold/50 hover:text-ink/90';
                    },

                    pilihJam(jam) {
                        if (!this.tanggalDipilih || !this.bisaJam(jam)) return;
                        this.jamDipilih = jam;
                        this.errorMetode = '';
                    },

                    formatTanggal(t) {
                        if (!t) return '—';
                        return new Date(t).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
                    },

                    tampilError(pesan) {
                        this.errorMetode = pesan;
                        this.getar = false;
                        this.$nextTick(() => {
                            this.getar = true;
                            setTimeout(() => { this.getar = false; }, 500);
                        });
                        return false;
                    },

                    kirim() {
                        if (this.sedangKirim) return;
                        if (!this.tanggalDipilih || !this.jamDipilih) {
                            this.pesanTanggal = 'Pilih tanggal dan jam terlebih dahulu.';
                            this.tampilError('Lengkapi tanggal & jam sebelum konfirmasi.');
                            return;
                        }
                        if (modeUbah) {
                            if (this.tanggalDipilih === cfg.tanggalDefault && this.jamDipilih === cfg.jamDefault) {
                                return this.tampilError('Jadwal yang dipilih sama dengan jadwal Anda saat ini.');
                            }
                        } else {
                            if (!this.nama.trim()) return this.tampilError('Nama lengkap wajib diisi.');
                            if (!this.noWa.trim()) return this.tampilError('Nomor WhatsApp wajib diisi.');
                        }

                        this.pesanTanggal = '';
                        this.errorMetode = '';
                        this.sedangKirim = true;

                        this.$refs.inputTanggal.value = this.tanggalDipilih;
                        this.$refs.inputJam.value = this.jamDipilih;
                        if (this.$refs.inputNama) this.$refs.inputNama.value = this.nama;
                        if (this.$refs.inputNoWa) this.$refs.inputNoWa.value = this.noWa;
                        if (this.$refs.inputProduk) this.$refs.inputProduk.value = this.produk;
                        if (this.$refs.inputCatatan) this.$refs.inputCatatan.value = this.catatan;

                        this.$refs.formFitting.submit();
                    },
                };
            });
        });
    </script>