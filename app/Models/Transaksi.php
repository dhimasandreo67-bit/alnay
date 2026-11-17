<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

        protected $fillable = [
        'kode_transaksi',
        'user_id',
        'jenis',
        'kanal',
        'tanggal',
        'total',
        'status_pembayaran',
        'catatan',
        'reservasi_fitting_id',
        'channel',
        'nama_tamu',
        'no_hp_tamu',
    ];

    protected function casts(): array   
    {
        return [
            'tanggal' => 'date',
            'total' => 'decimal:2',
            'stok_ditahan' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Data pelanggan (satu sumber untuk Transaksi, Sewa, dan Jaminan)
    |--------------------------------------------------------------------------
    | Pelanggan terdaftar -> dari relasi user
    | Pelanggan tamu      -> dari kolom nama_tamu / no_hp_tamu
    */
    public function getIsTamuAttribute(): bool
    {
        return $this->user === null;
    }

    public function getNamaPelangganAttribute(): string
    {
        return $this->user?->name ?: ($this->nama_tamu ?: '-');
    }

    public function getNoHpPelangganAttribute(): ?string
    {
        return $this->user?->no_hp ?: ($this->no_hp_tamu ?: null);
    }

    /**
     * Apakah stok produk sudah terpotong oleh transaksi ini?
     * - Sewa (kasir maupun online): TIDAK pernah memotong stok. Ketersediaan dihitung per tanggal
     *   (lihat Support\KetersediaanSewa), jadi tidak ada stok yang perlu dikembalikan.
     * - Beli kasir: terpotong saat transaksi dibuat.
     * - Beli online: terpotong (ditahan) saat checkout, dan dikembalikan bila pesanan batal/kedaluwarsa.
     *   Penandanya kolom stok_ditahan.
     */
    public function stokSudahDipotong(): bool
    {
        if ($this->jenis === 'sewa') {
            return false;
        }

        if (($this->channel ?? null) === 'kasir') {
            return true;
        }

        return $this->jenis === 'beli' && (bool) $this->stok_ditahan;
    }

    /**
     * Apakah pembayaran sudah dianggap lunas?
     */
    public function sudahLunas(): bool
    {
        return in_array(strtolower((string) $this->status_pembayaran), ['lunas', 'settlement', 'success'], true);
    }
    public function tandaiLunas(): bool
    {
        if ($this->sudahLunas()) {
            return false;
        }

        $this->update(['status_pembayaran' => 'lunas']);

        // Ubah status di tabel pembayaran (jika ada tagihan DP/Deposit yang pending) menjadi success
        $this->pembayaran()->where('status_midtrans', 'pending')->update(['status_midtrans' => 'success']);

        // Cek apakah ada sisa tagihan yang belum dibayar (misal pelunasan tunai)
        $totalDibayar = $this->pembayaran()->whereIn('status_midtrans', ['success', 'settlement', 'lunas'])->sum('jumlah');
        $sisaTagihan = (int) $this->total - $totalDibayar;

        // Jika ada sisa tagihan, otomatis catat sebagai "Pelunasan (Tunai)"
        if ($sisaTagihan > 0) {
            Pembayaran::create([
                'transaksi_id' => $this->id,
                'jenis_bayar' => 'pelunasan',
                'jumlah' => $sisaTagihan,
                'metode' => 'tunai',
                'status_midtrans' => 'success',
                'tanggal_bayar' => now(),
            ]);
        }

        // Pembayaran tunai: tanggal bayar = saat admin menerima uang di toko
        $this->pembayaran()->where('metode', 'tunai')->update(['tanggal_bayar' => now()]);

        // Stok pembelian online sudah ditahan saat checkout, jadi ini tidak memotong lagi.
        $this->tahanStok();

        return true;
    }

    /** Pembelian online (bukan sewa, bukan kasir): satu-satunya jenis yang stoknya ditahan di sini. */
    protected function menahanStokOnline(): bool
    {
        return $this->jenis === 'beli' && ($this->channel ?? null) !== 'kasir';
    }

    /** Apakah stok semua item masih cukup (dipakai sebelum menghidupkan lagi pesanan yang sudah kedaluwarsa). */
    public function stokCukup(): bool
    {
        foreach ($this->detail()->get() as $d) {
            $produk = Produk::find($d->produk_id);

            if (! $produk || $produk->stokTersedia('jual', $d->ukuran, $d->warna) < (int) $d->jumlah) {
                return false;
            }
        }

        return true;
    }

    /**
     * TAHAN stok: potong stok jual (per ukuran & warna) untuk pembelian ONLINE.
     * Dipanggil saat checkout, sehingga pelanggan lain langsung melihat stok yang sudah berkurang.
     * Aman dipanggil berulang: hanya berjalan sekali per pesanan (dijaga kolom stok_ditahan + kunci baris).
     *
     * @return bool true bila stok benar-benar dipotong sekarang
     */
    public function tahanStok(): bool
    {
        if (! $this->menahanStokOnline()) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () {
            $kunci = static::whereKey($this->id)->lockForUpdate()->first();

            if (! $kunci || $kunci->stok_ditahan) {
                return false;
            }

            foreach ($kunci->detail()->get() as $detail) {
                $produk = Produk::whereKey($detail->produk_id)->lockForUpdate()->first();

                if ($produk) {
                    $produk->kurangiStok('jual', $detail->ukuran, $detail->warna, (int) $detail->jumlah);
                }
            }

            $kunci->forceFill(['stok_ditahan' => true])->save();
            $this->stok_ditahan = true;

            return true;
        });
    }

    /**
     * KEMBALIKAN stok yang ditahan (pesanan kedaluwarsa / gagal / dibatalkan).
     * Hanya berjalan bila stok memang sedang ditahan, jadi tidak pernah mengembalikan dua kali.
     *
     * @return bool true bila stok benar-benar dikembalikan sekarang
     */
    public function lepasStok(): bool
    {
        if (! $this->menahanStokOnline()) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () {
            $kunci = static::whereKey($this->id)->lockForUpdate()->first();

            if (! $kunci || ! $kunci->stok_ditahan) {
                return false;
            }

            foreach ($kunci->detail()->get() as $detail) {
                $produk = Produk::whereKey($detail->produk_id)->lockForUpdate()->first();

                if ($produk) {
                    $produk->tambahStok('jual', $detail->ukuran, $detail->warna, (int) $detail->jumlah);
                }
            }

            $kunci->forceFill(['stok_ditahan' => false])->save();
            $this->stok_ditahan = false;

            return true;
        });
    }

    /** Nama lama, dipertahankan agar pemanggil lama (MidtransQris::terapkan) tetap bekerja. */
    public function potongStokPembelianOnline(): void
    {
        $this->tahanStok();
    }

    /*
    |--------------------------------------------------------------------------
    | Alur sewa -> fitting
    |--------------------------------------------------------------------------
    */

    /** DP (atau pelunasan) sudah diterima. */
    public function dpSudahDibayar(): bool
    {
        // status_pembayaran 'dp' dipakai saat pesanan dibuat (belum bayar), jadi tidak cukup dijadikan patokan.
        // Patokan paling andal: ada pembayaran yang sudah sukses di tabel pembayaran.
        return $this->sudahLunas()
            || strtolower((string) $this->status_pembayaran) === 'dp_dibayar'
            || $this->pembayaran->contains(
                fn ($p) => in_array(strtolower((string) $p->status_midtrans), ['success', 'settlement', 'capture', 'lunas'], true)
            );
    }

    /**
     * Sewa online yang diambil sendiri di toko wajib fitting.
     * "Ambil di toko" = tidak ada baris pengiriman (checkout hanya membuat Pengiriman untuk metode kirim).
     */
    public function perluFitting(): bool
    {
        return $this->jenis === 'sewa'
            && ($this->channel ?? 'online') !== 'kasir';
    }

    /** Reservasi fitting yang masih berjalan / sudah selesai untuk pesanan ini (bukan yang dibatalkan). */
    public function fittingAktif(): ?ReservasiFitting
    {
        return $this->reservasiFitting
            ->first(fn ($r) => in_array(strtolower((string) $r->status), ['pending', 'disetujui', 'hadir'], true));
    }

    public function reservasiFitting()
    {
        return $this->hasMany(ReservasiFitting::class, 'transaksi_id');
    }

    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class, 'transaksi_id');
    }

    public function sewa()
    {
        return $this->hasOne(Sewa::class);
    }

    public function refund()
    {
        return $this->hasMany(Refund::class);
    }

    /** Refund yang sedang berjalan untuk transaksi ini (hanya boleh satu pada satu waktu). */
    public function refundAktif(): ?Refund
    {
        return $this->refund->first(fn ($r) => in_array($r->status, Refund::AKTIF, true));
    }

    /**
     * Aturan pengajuan refund oleh pelanggan.
     *
     * @return array{0: bool, 1: ?string} [boleh, alasan bila tidak boleh]
     */
    public function cekBisaRefund(): array
    {
        if ($this->jenis !== 'beli') {
            return [false, 'Refund hanya berlaku untuk transaksi pembelian.'];
        }

        if (! $this->sudahLunas()) {
            return [false, 'Refund hanya bisa diajukan untuk pesanan yang sudah lunas.'];
        }

        $this->loadMissing('refund', 'pengiriman');

        if ($this->refund->contains(fn ($r) => in_array($r->status, [...Refund::AKTIF, 'selesai'], true))) {
            return [false, 'Pesanan ini sudah memiliki pengajuan refund.'];
        }

        // Titik awal batas waktu: saat barang diterima. Pesanan ambil di toko tidak punya data
        // pengiriman, jadi memakai tanggal transaksi.
        if ($this->pengiriman) {
            if ($this->pengiriman->status_kirim !== 'diterima') {
                return [false, 'Refund bisa diajukan setelah pesanan diterima. Konfirmasi "Pesanan Diterima" lebih dulu.'];
            }

            $diterima = $this->pengiriman->tanggal_diterima ?? $this->pengiriman->updated_at;
        } else {
            $diterima = $this->tanggal;
        }

        $batas = \Carbon\Carbon::parse($diterima)->startOfDay()->addDays(Refund::BATAS_HARI)->endOfDay();

        if (now()->gt($batas)) {
            return [false, 'Batas pengajuan refund (' . Refund::BATAS_HARI . ' hari setelah barang diterima) sudah terlewati.'];
        }

        return [true, null];
    }

    /** Nilai barang saja (tanpa ongkir), dipakai sebagai usulan nominal refund. */
    public function nilaiBarang(): int
    {
        return (int) round($this->detail->sum(fn ($d) => $d->jumlah * (float) $d->harga_satuan));
    }

    public function pengembalian()
    {
        return $this->hasMany(Pengembalian::class);
    }

        public function rating()
    {
        return $this->hasManyThrough(
            Rating::class,
            DetailTransaksi::class,
            'transaksi_id', // Foreign key on detail_transaksi table
            'detail_transaksi_id', // Foreign key on rating table
            'id', // Local key on transaksi table
            'id' // Local key on detail_transaksi table
        );
    }
}