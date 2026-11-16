<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Refund PEMBELIAN. (Transaksi sewa tidak punya refund: DP hangus, barang dikembalikan lewat alur pengembalian sewa.)
 *
 * Alur status:
 *   diajukan ──► disetujui ──► barang_dikirim ──► barang_diterima ──► selesai
 *      │             │ └────────────────────────────►┘
 *      ├─► ditolak   (admin boleh langsung ke barang_diterima, mis. barang diantar sendiri)
 *      └─► dibatalkan (oleh pelanggan, selama masih "diajukan")
 */
class Refund extends Model
{
    protected $table = 'refund';

    /** Pelanggan hanya boleh mengajukan refund maksimal sekian hari setelah barang diterima. */
    public const BATAS_HARI = 7;

    /** Setelah disetujui, pelanggan punya sekian hari untuk mengirim/mengantar barang. Lewat itu refund dibatalkan otomatis. */
    public const BATAS_KIRIM_HARI = 7;

    public const METODE = [
        'dijemput'      => 'Dijemput kurir (gratis untuk Anda)',
        'kirim_sendiri' => 'Antar ke agen kurir (ongkir diganti toko)',
    ];

    public const LABEL = [
        'diajukan'        => 'Menunggu tinjauan',
        'disetujui'       => 'Disetujui',
        'barang_dikirim'  => 'Barang dalam pengiriman',
        'barang_diterima' => 'Barang diterima',
        'selesai'         => 'Selesai',
        'ditolak'         => 'Ditolak',
        'dibatalkan'      => 'Dibatalkan',
    ];

    /** Perpindahan status yang diizinkan. Status di luar peta ini = final. */
    public const ALUR = [
        'diajukan'        => ['disetujui', 'ditolak', 'dibatalkan'],
        'disetujui'       => ['barang_dikirim', 'barang_diterima', 'dibatalkan'],
        'barang_dikirim'  => ['barang_diterima'],
        'barang_diterima' => ['selesai'],
    ];

    /** Status yang dianggap masih berjalan (menghalangi pengajuan refund baru untuk transaksi yang sama). */
    public const AKTIF = ['diajukan', 'disetujui', 'barang_dikirim', 'barang_diterima'];

    protected $fillable = [
        'transaksi_id',
        'diajukan_oleh',
        'diproses_oleh',
        'alasan',
        'alasan_penolakan',
        'bukti_foto',
        'bank_tujuan',
        'no_rekening',
        'atas_nama',
        'catatan_negosiasi',
        'no_resi_penjemputan',
        'metode_pengembalian',
        'kurir_retur',
        'no_resi_retur',
        'bukti_pengiriman',
        'tanggal_dikirim',
        'batas_kirim',
        'biaya_jemput',
        'biaya_ongkir_retur',
        'ganti_ongkir',
        'jumlah',
        'bukti_transfer',
        'stok_dikembalikan',
        'tanggal_barang_diterima',
        'tanggal_selesai',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'                  => 'decimal:2',
            'bukti_foto'              => 'array',
            'stok_dikembalikan'       => 'boolean',
            'tanggal_barang_diterima' => 'datetime',
            'tanggal_selesai'         => 'datetime',
            'tanggal_dikirim'         => 'datetime',
            'batas_kirim'             => 'datetime',
            'biaya_jemput'            => 'decimal:2',
            'biaya_ongkir_retur'      => 'decimal:2',
            'ganti_ongkir'            => 'decimal:2',
        ];
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function pemroses()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    /** Timeline perubahan status (tabel riwayat_status). */
    public function riwayat()
    {
        return RiwayatStatus::where('tabel_terkait', 'refund')
            ->where('record_id', $this->id)
            ->orderBy('id');
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', self::AKTIF);
    }

    public function getLabelStatusAttribute(): string
    {
        return self::LABEL[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function bolehKe(string $status): bool
    {
        return in_array($status, self::ALUR[$this->status] ?? [], true);
    }

    public function sudahFinal(): bool
    {
        return ! isset(self::ALUR[$this->status]);
    }

    /**
     * URL foto bukti dari pelanggan.
     * Memakai asset() (host & port sesuai request), bukan Storage::url() yang bergantung pada APP_URL,
     * sehingga gambar tetap tampil walau APP_URL berbeda dari alamat yang dibuka (mis. 127.0.0.1:8001).
     *
     * @return string[]
     */
    public function fotoBuktiUrl(): array
    {
        $daftar = $this->bukti_foto ?? [];
        if (is_string($daftar)) {
            $dec = json_decode($daftar, true);
            $daftar = is_array($dec) ? $dec : [$daftar];
        }

        return collect($daftar)
            ->filter()
            ->map(fn ($p) => preg_match('#^https?://#', $p) ? $p : asset('storage/' . ltrim($p, '/')))
            ->values()
            ->all();
    }

    /** Label metode pengembalian (null untuk refund lama yang dibuat sebelum fitur metode ada). */
    public function getLabelMetodeAttribute(): ?string
    {
        return self::METODE[$this->metode_pengembalian] ?? null;
    }

    /** Total yang ditransfer ke pelanggan: nominal refund + ongkir retur yang diganti toko. */
    public function totalTransfer(): float
    {
        return (float) $this->jumlah + (float) ($this->ganti_ongkir ?? 0);
    }

    /** URL foto serah-terima ke kurir dari pelanggan (null bila tidak ada). */
    public function buktiPengirimanUrl(): ?string
    {
        return $this->bukti_pengiriman ? asset('storage/' . ltrim($this->bukti_pengiriman, '/')) : null;
    }

    /** Alamat tujuan retur dari config/refund.php (null bila belum diisi). */
    public static function alamatRetur(): ?array
    {
        $a = config('refund.alamat_retur');

        return is_array($a) && filled($a['alamat'] ?? null) ? $a : null;
    }

    /** URL bukti transfer dari admin (null bila belum ada). */
    public function buktiTransferUrl(): ?string
    {
        return $this->bukti_transfer ? asset('storage/' . ltrim($this->bukti_transfer, '/')) : null;
    }

    /**
     * Pindahkan ke status baru + catat ke riwayat_status, dalam satu transaksi.
     * Baris refund dikunci supaya klik ganda / dua admin sekaligus tidak memproses dua kali.
     *
     * @param  array<string,mixed>  $atribut  kolom tambahan yang ikut diperbarui
     * @param  int|null             $oleh     id user/admin pelaku
     * @return bool false bila perpindahan tidak diizinkan dari status saat ini
     */
    public function pindahKe(string $status, array $atribut = [], ?int $oleh = null): bool
    {
        return DB::transaction(function () use ($status, $atribut, $oleh) {
            $baru = static::lockForUpdate()->find($this->id);

            if (! $baru || ! $baru->bolehKe($status)) {
                return false;
            }

            $lama = $baru->status;
            $baru->forceFill(array_merge($atribut, ['status' => $status]))->save();

            RiwayatStatus::create([
                'tabel_terkait' => 'refund',
                'record_id'     => $baru->id,
                'status_lama'   => $lama,
                'status_baru'   => $status,
                'diubah_oleh'   => $oleh,
                'waktu_ubah'    => now(),
            ]);

            $this->setRawAttributes($baru->getAttributes(), true);

            return true;
        });
    }
}