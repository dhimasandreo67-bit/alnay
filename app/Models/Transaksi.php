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
     * - Beli online: terpotong saat pembayaran lunas.
     */
    public function stokSudahDipotong(): bool
    {
        if ($this->jenis === 'sewa') {
            return false;
        }

        if (($this->channel ?? null) === 'kasir') {
            return true;
        }

        return $this->jenis === 'beli' && $this->status_pembayaran === 'lunas';
    }

    /**
     * Apakah pembayaran sudah dianggap lunas?
     */
    public function sudahLunas(): bool
    {
        return in_array(strtolower((string) $this->status_pembayaran), ['lunas', 'settlement', 'success'], true);
    }

    /**
     * Tandai transaksi lunas (dipakai halaman Transaksi maupun Sewa).
     * Return true kalau status memang berubah, false kalau sebelumnya sudah lunas
     * (aman dipanggil berulang).
     */
    public function tandaiLunas(): bool
    {
        if ($this->sudahLunas()) {
            return false;
        }

        $this->update(['status_pembayaran' => 'lunas']);

        // Ubah status di tabel pembayaran (jika ada tagihan DP/Pending)
        $this->pembayaran()->update(['status_midtrans' => 'success']);

        // Pembayaran tunai: tanggal bayar = saat admin menerima uang di toko
        $this->pembayaran()->where('metode', 'tunai')->update(['tanggal_bayar' => now()]);

        return true;
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
            && ($this->channel ?? 'online') !== 'kasir'
            && $this->pengiriman === null;
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