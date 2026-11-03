<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sewa extends Model
{
    protected $table = 'sewa';

    protected $fillable = [
        'transaksi_id',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali_rencana' => 'date',
            'tanggal_kembali_aktual' => 'date',
        ];
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function jaminan()
    {
        return $this->hasOne(JaminanSewa::class);
    }

    public function pengecekan()
    {
        return $this->hasMany(PengecekanBarang::class);
    }

    public function denda()
    {
        return $this->hasMany(Denda::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Data pelanggan: selalu diambil dari Transaksi (lihat Transaksi::getNamaPelangganAttribute)
    | supaya halaman Transaksi, Sewa, dan Jaminan menampilkan data yang sama.
    */

    public function getIsTamuAttribute(): bool
    {
        return (bool) $this->transaksi?->is_tamu;
    }

    public function getNamaPelangganAttribute(): string
    {
        return $this->transaksi?->nama_pelanggan ?? '-';
    }

    public function getNoHpPelangganAttribute(): ?string
    {
        return $this->transaksi?->no_hp_pelanggan;
    }

    /** Nomor untuk link wa.me (08xx -> 628xx). */
    public function getNoWaAttribute(): string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->no_hp_pelanggan);

        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }
        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    /*
    |--------------------------------------------------------------------------
    | Buat Sewa + Jaminan dari transaksi kasir
    |--------------------------------------------------------------------------
    | Aman dipanggil berulang (idempotent): kalau sewa/jaminan untuk transaksi
    | ini sudah ada, tidak dibuat dobel.
    */
    public static function buatDariTransaksi(Transaksi $transaksi, array $data = []): self
    {
        return DB::transaction(function () use ($transaksi, $data) {
            $sewa = static::firstOrCreate(
                ['transaksi_id' => $transaksi->id],
                [
                    'tanggal_pinjam'          => $data['tanggal_pinjam'] ?? now()->toDateString(),
                    'tanggal_kembali_rencana' => $data['tanggal_kembali_rencana'] ?? now()->addDay()->toDateString(),
                    'status'                  => 'aktif',
                ]
            );

            // nomor_ktp tidak lagi diisi dari kasir (cukup centang kecocokan),
            // jadi hanya ikut disimpan kalau memang dikirim.
            $atribut = [
                'metode_jaminan'              => $data['metode_jaminan'] ?? 'ktp_tatap_muka',
                'foto_ktp'                    => $data['foto_ktp'] ?? null,
                'nama_ktp'                    => $data['nama_ktp'] ?? null,
                'jumlah_deposit'              => $data['jumlah_deposit'] ?? 0,
                'status_verifikasi'           => ! empty($data['terverifikasi_oleh']) ? 'verified' : 'pending',
                'diverifikasi_oleh'           => $data['terverifikasi_oleh'] ?? null,
                'tanggal_verifikasi'          => ! empty($data['terverifikasi_oleh']) ? now() : null,
                'status_pengembalian_deposit' => 'belum',
            ];

            if (! empty($data['nomor_ktp'])) {
                $atribut['nomor_ktp'] = $data['nomor_ktp'];
            }

            JaminanSewa::firstOrCreate(['sewa_id' => $sewa->id], $atribut);

            return $sewa;
        });
    }
}