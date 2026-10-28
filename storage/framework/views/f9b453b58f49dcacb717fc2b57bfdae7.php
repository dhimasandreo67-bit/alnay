
<?php $__env->startSection('title', 'Kasir - Buat Transaksi Baru'); ?>


<?php $__env->startSection('content'); ?>
<?php
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

    $produkJs = $produks->map(function ($p) use ($fotoUrl) {
        $ukuran = collect($p->ukuran ?? [])->map(function ($v) {
            return ['ukuran' => (string) ($v['ukuran'] ?? ''), 'stok' => (int) ($v['stok'] ?? 0)];
        })->values();

        $warna = collect((array) ($p->warna ?? []))
            ->filter(fn ($w) => is_scalar($w) && $w !== '')
            ->map(fn ($w) => (string) $w)
            ->values();

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
            'warna'         => $warna,
            'ukuran'        => $ukuran,
            'harga_jual'    => (float) $p->harga_jual,
            'harga_sewa'    => (float) $p->harga_sewa,
            'dp'            => (float) $p->dp,
            'stok'          => (int) $p->stok,
            'status'        => $p->status,
            'bisa_disewa'   => (bool) $p->bisa_disewa,
            'dapat_dijual'  => (bool) $p->dapat_dijual,
            'diskon_persen' => (float) $p->diskon_persen,
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
?>

<div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">Kasir - Transaksi Baru</h1>
            <p class="text-xs text-gray-500 mt-1">Catat transaksi offline. Setelah disimpan, data langsung muncul di Semua Transaksi dengan label <strong>Kasir</strong>.</p>
        </div>
        <a href="<?php echo e(route('admin.transaksi.index')); ?>" class="text-xs font-bold text-gray-500 hover:text-amber-600">
            &larr; Kembali ke Semua Transaksi
        </a>
    </div>

    <?php if($errors->any()): ?>
        <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-bold mb-1">Periksa lagi isian form:</p>
            <ul class="list-disc list-inside space-y-0.5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('admin.transaksi.store')); ?>" id="form-kasir" class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start" novalidate>
        <?php echo csrf_field(); ?>

        
        <div class="xl:col-span-2 space-y-6">

            
            <section class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-3">Jenis Transaksi</p>
                <div class="flex gap-3">
                    <label class="flex-1">
                        <input type="radio" name="jenis" value="beli" class="peer sr-only" id="radio-beli" <?php if($oldJenis === 'beli'): echo 'checked'; endif; ?>>
                        <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-400">
                            Pembelian
                        </div>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="jenis" value="sewa" class="peer sr-only" id="radio-sewa" <?php if($oldJenis === 'sewa'): echo 'checked'; endif; ?>>
                        <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-400">
                            Penyewaan
                        </div>
                    </label>
                </div>

                <div id="box-tanggal-sewa" class="hidden mt-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_pinjam" class="text-xs font-bold text-gray-500">Tanggal Ambil</label>
                            <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="<?php echo e(old('tanggal_pinjam')); ?>" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label for="tanggal_kembali_rencana" class="text-xs font-bold text-gray-500">Tanggal Kembali (Rencana)</label>
                            <input type="date" id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" value="<?php echo e(old('tanggal_kembali_rencana')); ?>" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        </div>
                    </div>
                    <p id="info-durasi" class="mt-2 text-xs font-semibold text-amber-700"></p>
                </div>
            </section>

            
            <section class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-3">Data Pelanggan</p>
                <div class="flex gap-3 mb-4">
                    <label class="flex-1">
                        <input type="radio" name="customer_type" value="terdaftar" class="peer sr-only" <?php if($oldCustomer === 'terdaftar'): echo 'checked'; endif; ?>>
                        <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-400">
                            Pelanggan Terdaftar
                        </div>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="customer_type" value="tamu" class="peer sr-only" <?php if($oldCustomer === 'tamu'): echo 'checked'; endif; ?>>
                        <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-400">
                            Tamu (Walk-in)
                        </div>
                    </label>
                </div>

                <div id="box-user-terdaftar" class="space-y-2">
                    <label for="cari-user" class="text-xs font-bold text-gray-500">Cari Pelanggan</label>
                    <input type="search" id="cari-user" placeholder="Ketik nama atau nomor HP" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm" autocomplete="off">
                    <select name="user_id" id="select-user" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm"></select>
                    <div id="kartu-user" class="hidden items-center gap-3 rounded-xl bg-amber-50/60 ring-1 ring-amber-100 px-4 py-3">
                        <div id="user-inisial" class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500 text-sm font-black text-white"></div>
                        <div class="min-w-0">
                            <p id="user-nama" class="truncate text-sm font-bold text-gray-900"></p>
                            <p id="user-hp" class="text-xs text-gray-500"></p>
                        </div>
                    </div>
                </div>

                <div id="box-user-tamu" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_tamu" class="text-xs font-bold text-gray-500">Nama Tamu</label>
                        <input type="text" id="nama_tamu" name="nama_tamu" value="<?php echo e(old('nama_tamu')); ?>" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm" placeholder="Nama pembeli">
                    </div>
                    <div>
                        <label for="no_hp_tamu" class="text-xs font-bold text-gray-500">No. WhatsApp</label>
                        <input type="text" id="no_hp_tamu" name="no_hp_tamu" value="<?php echo e(old('no_hp_tamu')); ?>" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
            </section>

            
            <section class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Pilih Produk</p>
                        <p id="katalog-info" class="text-xs text-gray-500 mt-0.5"></p>
                    </div>
                    <input type="search" id="cari-produk" placeholder="Cari nama, SKU, motif, bahan" class="w-full sm:w-72 border border-gray-200 rounded-xl px-3 py-2 text-sm" autocomplete="off">
                </div>

                <div id="chip-kategori" class="flex flex-wrap gap-2 mb-4"></div>

                <div id="katalog" class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-[640px] overflow-y-auto pr-1"></div>
            </section>

            
            <section class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-3">Pembayaran</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="status_pembayaran" class="text-xs font-bold text-gray-500">Status</label>
                        <select name="status_pembayaran" id="status_pembayaran" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                            <option value="lunas" <?php if($oldStatus === 'lunas'): echo 'selected'; endif; ?>>Lunas (Bayar Penuh)</option>
                            <option value="dp" <?php if($oldStatus === 'dp'): echo 'selected'; endif; ?>>DP (Bayar Sebagian)</option>
                        </select>
                    </div>
                    <div id="box-jumlah-dp" class="hidden">
                        <label for="jumlah_dp" class="text-xs font-bold text-gray-500">Jumlah DP (Rp)</label>
                        <input type="number" id="jumlah_dp" name="jumlah_dp" min="1" value="<?php echo e(old('jumlah_dp')); ?>" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="metode_bayar" class="text-xs font-bold text-gray-500">Metode Bayar</label>
                        <select name="metode_bayar" id="metode_bayar" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                            <option value="tunai" <?php if(old('metode_bayar', 'tunai') === 'tunai'): echo 'selected'; endif; ?>>Tunai</option>
                            <option value="qris" <?php if(old('metode_bayar') === 'qris'): echo 'selected'; endif; ?>>QRIS</option>
                            <option value="debit" <?php if(old('metode_bayar') === 'debit'): echo 'selected'; endif; ?>>Kartu Debit</option>
                            <option value="transfer" <?php if(old('metode_bayar') === 'transfer'): echo 'selected'; endif; ?>>Transfer Bank</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label for="catatan" class="text-xs font-bold text-gray-500">Catatan (opsional)</label>
                    <textarea name="catatan" id="catatan" rows="2" maxlength="255" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm" placeholder="Contoh: pelanggan minta penyesuaian ukuran, ambil jam 3 sore"><?php echo e(old('catatan')); ?></textarea>
                </div>
            </section>
        </div>

        
        <aside class="xl:sticky xl:top-6 space-y-4">
            <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 pt-5 pb-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Keranjang Kasir</p>
                    <span id="badge-item" class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold text-gray-500">0 item</span>
                </div>

                <div id="cart" class="px-5 pb-2 space-y-3 max-h-[52vh] overflow-y-auto"></div>

                <div id="cart-kosong" class="mx-5 mb-5 rounded-2xl border border-dashed border-gray-200 py-8 text-center">
                    <p class="text-sm font-semibold text-gray-500">Keranjang masih kosong</p>
                    <p class="mt-1 text-xs text-gray-400">Pilih produk dari katalog untuk menambahkannya.</p>
                </div>

                <div class="border-t border-gray-100 bg-gray-50/60 px-5 py-4 space-y-2">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Jenis</span><span id="sum-jenis" class="font-bold text-gray-800">-</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Jumlah barang</span><span id="sum-qty" class="font-bold text-gray-800">0</span>
                    </div>
                    <div class="flex items-end justify-between pt-2 border-t border-gray-200">
                        <span class="text-[10px] uppercase tracking-wider text-gray-400">Total</span>
                        <span id="total-display" class="text-2xl font-black tabular-nums text-gray-900">Rp 0</span>
                    </div>
                    <div id="sum-bayar" class="hidden space-y-1 pt-1">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Dibayar sekarang</span><span id="sum-dibayar" class="font-bold text-emerald-700 tabular-nums">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Sisa tagihan</span><span id="sum-sisa" class="font-bold text-rose-600 tabular-nums">Rp 0</span>
                        </div>
                    </div>
                </div>
            </section>

            <div id="pesan-error" role="alert" class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700"></div>

            <button type="submit" class="w-full bg-gray-900 text-white px-6 py-3.5 rounded-xl text-sm font-bold hover:bg-gray-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2">
                Simpan Transaksi
            </button>
        </aside>
    </form>
</div>

<script>
    // ================= DATA =================
    const PRODUK = <?php echo json_encode($produkJs, 15, 512) ?>;
    const KATEGORI = <?php echo json_encode($kategoriJs, 15, 512) ?>;
    const USERS = <?php echo json_encode($usersJs, 15, 512) ?>;
    const OLD_ITEMS = <?php echo json_encode($oldItems, 15, 512) ?>;
    const OLD_USER_ID = <?php echo json_encode((string) old('user_id', ''), 512) ?>;
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
    const hargaFinal = (p, j) => { const d = hargaDasar(p, j); return d - (d * p.diskon_persen / 100); };
    const punyaUkuran = (p) => p.ukuran.length > 0;

    function stokUntuk(p, ukuran) {
        if (!punyaUkuran(p)) return p.stok;
        if (!ukuran) return p.ukuran.reduce((a, v) => a + v.stok, 0);
        const v = p.ukuran.find((x) => x.ukuran === ukuran);
        return v ? v.stok : 0;
    }

    function cocokJenis(p, j) { return j === 'sewa' ? p.bisa_disewa : p.dapat_dijual; }
    function bisaDipilih(p) { return p.stok > 0 && !STATUS_BLOKIR.includes(p.status); }

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
            const cls = aktif ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-amber-400';
            return '<button type="button" data-kat="' + k.id + '" class="rounded-full border px-3 py-1 text-xs font-semibold transition-colors ' + cls + '">' + esc(k.nama) + '</button>';
        }).join('');
    }

    function kartuProduk(p, j) {
        const ok = bisaDipilih(p);
        const final = hargaFinal(p, j);
        const dasar = hargaDasar(p, j);
        const diskon = p.diskon_persen > 0;
        const foto = p.foto
            ? '<img src="' + esc(p.foto) + '" alt="' + esc(p.nama) + '" loading="lazy" class="h-full w-full object-cover">'
            : ikonFoto;

        const ukuran = punyaUkuran(p)
            ? '<div class="flex flex-wrap gap-1">' + p.ukuran.map((v) =>
                '<span class="rounded-md border px-1.5 py-0.5 text-[10px] font-semibold ' + (v.stok > 0 ? 'border-gray-200 text-gray-600' : 'border-gray-100 text-gray-300 line-through') + '">' + esc(v.ukuran) + '</span>'
              ).join('') + '</div>'
            : '';

        const warna = p.warna.length
            ? '<div class="flex flex-wrap items-center gap-1">' + p.warna.slice(0, 6).map(titikWarna).join('') + (p.warna.length > 6 ? '<span class="text-[10px] text-gray-400">+' + (p.warna.length - 6) + '</span>' : '') + '</div>'
            : '';

        const meta = [p.bahan, p.motif].filter(Boolean).map(esc).join(' &middot; ');
        const badgeStatus = !ok
            ? '<span class="absolute left-2 top-2 rounded-full bg-gray-900/80 px-2 py-0.5 text-[10px] font-bold text-white">' + (p.stok <= 0 ? 'Stok habis' : esc(p.status)) + '</span>'
            : (diskon ? '<span class="absolute left-2 top-2 rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-bold text-white">-' + p.diskon_persen + '%</span>' : '');

        return '<article class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white transition hover:border-amber-300 hover:shadow-sm ' + (ok ? '' : 'opacity-60') + '">'
            + '<div class="relative flex aspect-[4/5] items-center justify-center overflow-hidden bg-gray-50">' + foto + badgeStatus
            + '<span class="absolute bottom-2 right-2 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold text-gray-700 shadow-sm">Stok ' + p.stok + '</span></div>'
            + '<div class="flex flex-1 flex-col gap-1.5 p-3">'
            + '<div><p class="line-clamp-2 text-sm font-bold leading-snug text-gray-900">' + esc(p.nama) + '</p>'
            + '<p class="mt-0.5 text-[10px] text-gray-400">' + esc(p.sku || '-') + (p.kategori ? ' &middot; ' + esc(p.kategori) : '') + '</p></div>'
            + (meta ? '<p class="text-[11px] text-gray-500">' + meta + '</p>' : '')
            + ukuran + warna
            + '<div class="mt-auto pt-1"><p class="text-sm font-black tabular-nums text-gray-900">' + rupiah(final) + '</p>'
            + (diskon ? '<p class="text-[10px] tabular-nums text-gray-400 line-through">' + rupiah(dasar) + '</p>' : '')
            + '</div>'
            + '<button type="button" data-tambah="' + p.id + '" ' + (ok ? '' : 'disabled') + ' class="mt-1 rounded-xl bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 ring-1 ring-amber-200 transition hover:bg-amber-100 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:ring-gray-200">+ Tambah</button>'
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
        if (!p || !bisaDipilih(p)) return;

        let ukuran = '';
        if (p.ukuran.length === 1) ukuran = p.ukuran[0].ukuran;

        const ada = state.cart.find((r) => r.produk_id === p.id && r.ukuran === ukuran && r.warna === '');
        if (ada) {
            ada.qty = Math.min(ada.qty + 1, Math.max(1, stokUntuk(p, ada.ukuran)));
        } else {
            state.cart.push({ produk_id: p.id, ukuran: ukuran, warna: '', qty: 1 });
        }
        renderCart();
    }

    function barisCart(r, idx, j) {
        const p = pById(r.produk_id);
        if (!p) return '';
        const final = hargaFinal(p, j);
        const dasar = hargaDasar(p, j);
        const stokMaks = Math.max(1, stokUntuk(p, r.ukuran));
        const perluUkuran = punyaUkuran(p) && !r.ukuran;

        const foto = p.foto
            ? '<img src="' + esc(p.foto) + '" alt="" class="h-full w-full object-cover">'
            : '<span class="scale-75">' + ikonFoto + '</span>';

        const selUkuran = punyaUkuran(p)
            ? '<div><label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Ukuran</label>'
              + '<select name="items[' + idx + '][ukuran]" data-f="ukuran" class="mt-0.5 w-full rounded-lg border px-2 py-1.5 text-xs ' + (perluUkuran ? 'border-rose-300 bg-rose-50' : 'border-gray-200') + '">'
              + '<option value="">Pilih ukuran</option>'
              + p.ukuran.map((v) => '<option value="' + esc(v.ukuran) + '" ' + (v.stok <= 0 ? 'disabled' : '') + ' ' + (v.ukuran === r.ukuran ? 'selected' : '') + '>' + esc(v.ukuran) + ' (stok ' + v.stok + ')</option>').join('')
              + '</select></div>'
            : '<input type="hidden" name="items[' + idx + '][ukuran]" value="">';

        const selWarna = p.warna.length
            ? '<div><label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Warna</label>'
              + '<select name="items[' + idx + '][warna]" data-f="warna" class="mt-0.5 w-full rounded-lg border border-gray-200 px-2 py-1.5 text-xs">'
              + '<option value="">Sesuai produk</option>'
              + p.warna.map((w) => '<option value="' + esc(w) + '" ' + (w === r.warna ? 'selected' : '') + '>' + esc(w) + '</option>').join('')
              + '</select></div>'
            : '<input type="hidden" name="items[' + idx + '][warna]" value="">';

        const info = [];
        if (p.bahan) info.push('Bahan: ' + esc(p.bahan));
        if (p.motif) info.push('Motif: ' + esc(p.motif));
        if (p.berat) info.push(p.berat + ' kg');
        if (j === 'sewa') {
            if (p.dp > 0) info.push('DP ' + rupiah(p.dp));
            if (p.denda > 0) info.push('Denda telat ' + rupiah(p.denda) + '/hari');
        }

        return '<div data-idx="' + idx + '" class="rounded-2xl border ' + (perluUkuran ? 'border-rose-200' : 'border-gray-100') + ' bg-gray-50/50 p-3">'
            + '<input type="hidden" name="items[' + idx + '][produk_id]" value="' + p.id + '">'
            + '<div class="flex gap-3">'
            + '<div class="flex h-16 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-white">' + foto + '</div>'
            + '<div class="min-w-0 flex-1"><p class="truncate text-sm font-bold text-gray-900">' + esc(p.nama) + '</p>'
            + '<p class="text-[10px] text-gray-400">' + esc(p.sku || '-') + (p.kategori ? ' &middot; ' + esc(p.kategori) : '') + '</p>'
            + '<p class="mt-0.5 text-xs text-gray-600 tabular-nums">' + rupiah(final) + (p.diskon_persen > 0 ? ' <span class="text-gray-400 line-through">' + rupiah(dasar) + '</span>' : '') + '</p></div>'
            + '<button type="button" data-act="hapus" aria-label="Hapus ' + esc(p.nama) + '" class="h-7 w-7 shrink-0 rounded-lg text-gray-400 transition hover:bg-rose-50 hover:text-rose-600">&times;</button>'
            + '</div>'
            + '<div class="mt-3 grid grid-cols-2 gap-2">' + selUkuran + selWarna + '</div>'
            + (perluUkuran ? '<p class="mt-1.5 text-[11px] font-semibold text-rose-600">Pilih ukuran dulu agar stok tercatat benar.</p>' : '')
            + (info.length ? '<p class="mt-2 text-[10px] leading-relaxed text-gray-400">' + info.join(' &middot; ') + '</p>' : '')
            + '<div class="mt-3 flex items-center justify-between">'
            + '<div class="inline-flex items-center rounded-lg border border-gray-200 bg-white">'
            + '<button type="button" data-act="kurang" aria-label="Kurangi" class="h-8 w-8 text-sm font-bold text-gray-500 hover:text-amber-600">&minus;</button>'
            + '<input type="number" name="items[' + idx + '][qty]" data-f="qty" value="' + r.qty + '" min="1" max="' + stokMaks + '" class="h-8 w-12 border-x border-gray-200 text-center text-xs font-bold tabular-nums">'
            + '<button type="button" data-act="tambah" aria-label="Tambah" class="h-8 w-8 text-sm font-bold text-gray-500 hover:text-amber-600">+</button>'
            + '</div>'
            + '<div class="text-right"><p class="text-[10px] uppercase tracking-wider text-gray-400">Subtotal</p><p class="text-sm font-black tabular-nums text-gray-900">' + rupiah(final * r.qty) + '</p></div>'
            + '</div></div>';
    }

    function hitungRingkasan() {
        const j = jenisAktif();
        let total = 0, qty = 0, dpSaran = 0;
        state.cart.forEach((r) => {
            const p = pById(r.produk_id);
            if (!p) return;
            total += hargaFinal(p, j) * r.qty;
            qty += r.qty;
            dpSaran += p.dp * r.qty;
        });
        return { total, qty, dpSaran };
    }

    function renderCart() {
        const j = jenisAktif();
        el('cart').innerHTML = state.cart.map((r, i) => barisCart(r, i, j)).join('');
        el('cart-kosong').classList.toggle('hidden', state.cart.length > 0);
        el('badge-item').textContent = state.cart.length + ' item';
        updateRingkasan();
    }

    function updateRingkasan() {
        const j = jenisAktif();
        const { total, qty, dpSaran } = hitungRingkasan();
        el('sum-jenis').textContent = j === 'sewa' ? 'Penyewaan' : 'Pembelian';
        el('sum-qty').textContent = qty;
        el('total-display').textContent = rupiah(total);

        const isDp = el('status_pembayaran').value === 'dp';
        const inputDp = el('jumlah_dp');
        if (isDp && !state.dpManual) {
            let saran = j === 'sewa' ? dpSaran : 0;
            if (saran <= 0 || saran >= total) saran = total > 0 ? Math.floor(total / 2) : 0;
            inputDp.value = saran > 0 ? Math.round(saran) : '';
        }

        const dibayar = isDp ? (parseFloat(inputDp.value) || 0) : total;
        el('sum-bayar').classList.toggle('hidden', !isDp);
        el('sum-dibayar').textContent = rupiah(dibayar);
        el('sum-sisa').textContent = rupiah(Math.max(0, total - dibayar));
    }

    // Update kecil tanpa re-render penuh saat qty diketik (menjaga fokus input)
    function sinkronQty(idx, nilai) {
        const r = state.cart[idx];
        const p = pById(r.produk_id);
        const maks = Math.max(1, stokUntuk(p, r.ukuran));
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
        if (f === 'qty') sinkronQty(idx, e.target.value);
        if (f === 'ukuran') { r.ukuran = e.target.value; sinkronQty(idx, r.qty); }
        if (f === 'warna') r.warna = e.target.value;
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
    function hitungDurasi() {
        const a = el('tanggal_pinjam').value, b = el('tanggal_kembali_rencana').value;
        const out = el('info-durasi');
        if (!a || !b) { out.textContent = ''; return; }
        const hari = Math.round((new Date(b) - new Date(a)) / 86400000);
        out.textContent = hari < 0 ? 'Tanggal kembali tidak boleh sebelum tanggal ambil.' : 'Lama sewa: ' + (hari + 1) + ' hari (' + hari + ' malam)';
    }

    function ubahJenis() {
        const sewa = jenisAktif() === 'sewa';
        el('box-tanggal-sewa').classList.toggle('hidden', !sewa);

        // Buang item yang tidak boleh untuk jenis ini, lalu render ulang harga
        state.cart = state.cart.filter((r) => { const p = pById(r.produk_id); return p && cocokJenis(p, jenisAktif()); });
        state.dpManual = false;
        renderKatalog();
        renderCart();
    }

    document.querySelectorAll('input[name="jenis"]').forEach((r) => r.addEventListener('change', ubahJenis));
    el('tanggal_pinjam').addEventListener('change', () => {
        el('tanggal_kembali_rencana').min = el('tanggal_pinjam').value;
        hitungDurasi();
    });
    el('tanggal_kembali_rencana').addEventListener('change', hitungDurasi);

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

    // ================= VALIDASI SEBELUM KIRIM =================
    el('form-kasir').addEventListener('submit', (e) => {
        const err = [];
        const { total } = hitungRingkasan();

        if (!state.cart.length) err.push('Tambahkan minimal satu produk ke keranjang.');
        state.cart.forEach((r) => {
            const p = pById(r.produk_id);
            if (p && punyaUkuran(p) && !r.ukuran) err.push('Pilih ukuran untuk "' + p.nama + '".');
        });

        if (jenisAktif() === 'sewa') {
            const a = el('tanggal_pinjam').value, b = el('tanggal_kembali_rencana').value;
            if (!a || !b) err.push('Isi tanggal ambil dan tanggal kembali.');
            else if (b < a) err.push('Tanggal kembali tidak boleh sebelum tanggal ambil.');
        }

        const tipe = document.querySelector('input[name="customer_type"]:checked').value;
        if (tipe === 'terdaftar' && !el('select-user').value) err.push('Pilih pelanggan terdaftar.');
        if (tipe === 'tamu' && (!el('nama_tamu').value.trim() || !el('no_hp_tamu').value.trim())) err.push('Isi nama dan nomor WhatsApp tamu.');

        if (el('status_pembayaran').value === 'dp') {
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
        el('tanggal_kembali_rencana').min = el('tanggal_pinjam').value;

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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Project_kecil\alnay\resources\views/admin/transaksi/create.blade.php ENDPATH**/ ?>