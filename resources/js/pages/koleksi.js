// Logika halaman Koleksi (filter AJAX). Dimuat hanya di halaman yang memakainya (lihat app.js).
// `kat` (kategori) adalah satu-satunya sumber kebenaran: checkbox sidebar hanya cerminannya.
export default function koleksiFilter(awal = {}) {
    return {
        filterOpen: false,
        habisBuka: false,
        loading: false,
        scrolled: false,
        sheetY: 0,
        dragY0: null,
        seq: 0,
        timer: null,
        urlTerakhir: '',
        cari: awal.cari || '',
        hargaMin: awal.hargaMin || '',
        hargaMax: awal.hargaMax || '',
        kat: awal.kat || [],
        jenis: awal.jenis || '',
        warna: awal.warna || '',
        total: awal.total || 0,
        aktif: awal.aktif || 0,
        preset: [
            { l: 'Di bawah 1 jt', min: '', max: '1000000' },
            { l: '1 – 3 jt', min: '1000000', max: '3000000' },
            { l: '3 – 5 jt', min: '3000000', max: '5000000' },
            { l: 'Di atas 5 jt', min: '5000000', max: '' },
        ],

        init() {
            this.urlTerakhir = location.href;
            // Tombol back/forward browser: sinkronkan form lalu muat ulang konten tanpa reload
            window.addEventListener('popstate', () => {
                this.sinkronForm();
                this.muat(location.href, { push: false });
            });
        },

        // Geser lembar filter ke bawah (HP) untuk menutupnya
        mulaiTarik(e) {
            if (window.innerWidth >= 1024) return;
            this.dragY0 = e.touches[0].clientY;
        },
        tarik(e) {
            if (this.dragY0 === null) return;
            this.sheetY = Math.max(0, e.touches[0].clientY - this.dragY0);
        },
        lepasTarik() {
            if (this.dragY0 === null) return;
            if (this.sheetY > 90) this.filterOpen = false;
            this.sheetY = 0;
            this.dragY0 = null;
        },

        // Di HP hasil berada jauh di bawah hero: geser ke daftar produk
        keHasil() {
            this.gulirKe('area-produk');
        },

        // Buka/tutup bagian "Produk Habis" di bawah daftar (tanpa pindah halaman)
        togelHabis() {
            this.habisBuka = !this.habisBuka;
        },

        // Geser ke bagian "Produk Habis"
        keHabis() {
            this.gulirKe('produk-habis');
        },

        // Baca posisi (read) dan gulir (write) dibungkus rAF supaya tidak memicu forced reflow
        gulirKe(id) {
            const el = document.getElementById(id);
            if (!el) return;
            requestAnimationFrame(() => {
                const top = el.getBoundingClientRect().top + window.scrollY - 96;
                window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
            });
        },

        fmt(v) {
            return v === '' ? '' : Number(v).toLocaleString('id-ID');
        },

        // Input harga: hanya angka, tampil dengan titik ribuan, diterapkan otomatis setelah berhenti mengetik
        setHarga(kolom, e) {
            const v = e.target.value.replace(/\D/g, '').slice(0, 10);
            this[kolom] = v;
            e.target.value = this.fmt(v);
            this.tunda();
        },

        tunda() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.kirim(), 500);
        },

        pilihPreset(p) {
            if (this.hargaMin === p.min && this.hargaMax === p.max) {
                this.hargaMin = '';
                this.hargaMax = '';
            } else {
                this.hargaMin = p.min;
                this.hargaMax = p.max;
            }
            this.kirim();
        },

        // ---------- Kategori (state `kat` = sumber kebenaran) ----------

        // Baca kategori dari URL: mendukung kategori[] dan kategori[0] (format paginasi Laravel)
        katDariUrl(p) {
            const hasil = [];
            p.forEach((v, k) => {
                if (/^kategori(\[\d*\])?$/.test(k) && v !== '') hasil.push(v);
            });
            return hasil;
        },

        // Samakan checkbox sidebar dengan state `kat`
        syncKatDom() {
            document.querySelectorAll('#form-filter input[name="kategori[]"]').forEach(c => {
                c.checked = this.kat.includes(c.value);
            });
        },

        // Tombol kategori di hero
        toggleKategori(slug) {
            if (!slug) return;
            this.kat = this.kat.includes(slug)
                ? this.kat.filter(s => s !== slug)
                : [...this.kat, slug];
            this.syncKatDom();
            this.kirim({ hero: true });
        },

        semuaKategori() {
            this.kat = [];
            this.syncKatDom();
            this.kirim({ hero: true });
        },

        // ---------- Jenis & warna ----------

        setJenis(v) {
            document.querySelectorAll('#form-filter input[name="jenis"]').forEach(r => { r.checked = (r.value === v); });
            this.jenis = v;
        },

        // Tab Semua / Sewa / Beli di atas daftar produk
        pilihJenis(v) {
            if (this.jenis === v) return;
            this.setJenis(v);
            this.kirim();
        },

        hapusWarna() {
            document.querySelectorAll('#form-filter input[name="warna"]').forEach(r => { r.checked = false; r.dataset.was = ''; });
            this.kirim();
        },

        // Kumpulkan semua filter (state + form) -> URL -> muat via AJAX
        kirim(opt = {}) {
            clearTimeout(this.timer);
            const f = document.getElementById('form-filter');
            const fd = new FormData(f);
            this.jenis = fd.get('jenis') || '';
            this.warna = fd.get('warna') || '';

            const p = new URLSearchParams();
            fd.forEach((v, k) => {
                if (v !== '' && k !== 'sort' && k !== 'search' && k !== 'kategori[]') p.append(k, v);
            });
            // Kategori diambil dari state, bukan dari checkbox
            this.kat.forEach(s => p.append('kategori[]', s));

            if (this.cari.trim() !== '') p.set('search', this.cari.trim());
            // Jika terbalik (min > max), tukar otomatis supaya hasil tidak kosong
            if (this.hargaMin !== '' && this.hargaMax !== '' && Number(this.hargaMin) > Number(this.hargaMax)) {
                [this.hargaMin, this.hargaMax] = [this.hargaMax, this.hargaMin];
            }
            if (this.hargaMin !== '') p.set('harga_min', this.hargaMin);
            if (this.hargaMax !== '') p.set('harga_max', this.hargaMax);
            const sort = new URLSearchParams(location.search).get('sort');
            if (sort) p.set('sort', sort);

            const qs = p.toString();
            const url = f.action + (qs ? '?' + qs : '');
            if (url === this.urlTerakhir) {
                if (opt.hero && window.innerWidth < 1024) this.keHasil();
                return;
            }
            this.muat(url, { naik: !!opt.hero && window.innerWidth < 1024 });
        },

        // Setiap input berubah -> langsung terapkan. 'Jenis' dibuat saling meniadakan.
        ubah(e, f) {
            const t = e.target;
            if (t.type === 'text') return; // input harga ditangani setHarga (debounce)
            if (t.name === 'kategori[]') {
                this.kat = Array.from(f.querySelectorAll('input[name="kategori[]"]:checked')).map(c => c.value);
            }
            if (t.name === 'jenis' && t.checked) {
                f.querySelectorAll('input[name=jenis]').forEach(c => { if (c !== t) c.checked = false; });
            }
            this.kirim();
        },

        // Radio (warna) bisa dibatalkan dengan klik ulang pada pilihan yang sama
        klik(e, f) {
            const t = e.target;
            if (t.type !== 'radio' || t.name !== 'warna') return;
            if (t.dataset.was === '1') {
                t.checked = false;
                t.dataset.was = '';
                this.kirim();
                return;
            }
            f.querySelectorAll('input[name=' + t.name + ']').forEach(r => r.dataset.was = '');
            t.dataset.was = '1';
        },

        // Reset semua filter
        reset(url) {
            const f = document.getElementById('form-filter');
            f.querySelectorAll('input[type=checkbox], input[type=radio]').forEach(i => {
                i.checked = false;
                i.dataset.was = '';
            });
            this.cari = '';
            this.hargaMin = '';
            this.hargaMax = '';
            this.kat = [];
            this.syncKatDom();
            this.setJenis('');
            this.warna = '';
            this.muat(url);
        },

        // Klik pagination / chip filter / tombol reset di area produk -> AJAX juga
        halaman(e) {
            const a = e.target.closest('nav a, a[data-ajax], a[data-chip]');
            if (!a || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey) return;
            e.preventDefault();
            if (a.dataset.ajax !== undefined) return this.reset(a.href);
            if (a.dataset.chip !== undefined) {
                this.muat(a.href).then(() => this.sinkronForm());
                return;
            }
            // Pagination di dalam bagian "Produk Habis": tetap di bagian itu
            if (a.closest('#produk-habis')) {
                this.muat(a.href).then(() => this.$nextTick(() => this.keHabis()));
                return;
            }
            this.muat(a.href, { naik: true });
        },

        // Samakan form dengan URL (untuk tombol back/forward & hapus chip)
        sinkronForm() {
            const p = new URLSearchParams(location.search);
            document.getElementById('form-filter')
                .querySelectorAll('input[type=radio]').forEach(i => {
                    i.checked = p.getAll(i.name).includes(i.value);
                    i.dataset.was = i.checked ? '1' : '';
                });
            this.kat = this.katDariUrl(p);
            this.syncKatDom();
            this.cari = p.get('search') || '';
            this.hargaMin = p.get('harga_min') || '';
            this.hargaMax = p.get('harga_max') || '';
            this.setJenis(p.get('jenis') || '');
            this.warna = p.get('warna') || '';
        },

        // Inti: ambil halaman, ganti hanya area produk, posisi scroll tidak berubah
        async muat(url, opt = {}) {
            const { push = true, naik = false } = opt;
            const id = ++this.seq;
            this.loading = true;
            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                });
                if (!res.ok) throw new Error('gagal memuat');
                const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                if (id !== this.seq) return; // ada permintaan yang lebih baru
                const baru = doc.getElementById('area-produk');
                if (!baru) throw new Error('area produk tidak ditemukan');

                document.getElementById('area-produk').innerHTML = baru.innerHTML;
                // Baru dianggap "URL terakhir" setelah benar-benar berhasil dimuat
                this.urlTerakhir = url;
                this.total = parseInt(baru.dataset.total || '0', 10);
                this.aktif = parseInt(baru.dataset.aktif || '0', 10);
                if (push) history.pushState({}, '', url);

                // Hanya saat pindah halaman (pagination) -> geser ke awal daftar produk
                if (naik) this.keHasil();
            } catch (err) {
                // Fallback: kalau AJAX gagal, pakai navigasi biasa
                if (id === this.seq) window.location.href = url;
            } finally {
                if (id === this.seq) this.loading = false;
            }
        }
    };
}