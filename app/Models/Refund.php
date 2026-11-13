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

    public const LABEL = [
        'diajukan'        => 'Menunggu tinjauan',
        'disetujui'       => 'Disetujui — kirim barang',
        'barang_dikirim'  => 'Barang dalam pengiriman',
        'barang_diterima' => 'Barang diterima — dana diproses',
        'selesai'         => 'Selesai — dana dikembalikan',
        'ditolak'         => 'Ditolak',
        'dibatalkan'      => 'Dibatalkan',
    ];

    /** Perpindahan status yang diizinkan. Status di luar peta ini = final. */
    public const ALUR = [
        'diajukan'        => ['disetujui', 'ditolak', 'dibatalkan'],
        'disetujui'       => ['barang_dikirim', 'barang_diterima'],
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

    /** @return string[] URL foto bukti dari pelanggan. */
    public function fotoBuktiUrl(): array
    {
        return collect($this->bukti_foto ?? [])
            ->map(fn ($p) => Storage::disk('public')->url($p))
            ->all();
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