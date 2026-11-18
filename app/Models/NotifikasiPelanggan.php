<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Status baca / hapus notifikasi lonceng pelanggan.
 * (Notifikasi admin ada di App\Support\NotifikasiAdmin dan tidak terkait dengan kelas ini.)
 */
class NotifikasiPelanggan extends Model
{
    protected $table = 'notifikasi_pelanggan';

    protected $fillable = ['user_id', 'transaksi_id', 'dibaca_at', 'disembunyikan_at'];

    protected function casts(): array
    {
        return [
            'dibaca_at'        => 'datetime',
            'disembunyikan_at' => 'datetime',
        ];
    }

    /** Status pembayaran yang memunculkan notifikasi (sama seperti aturan sebelumnya). */
    public const STATUS_NOTIF = ['pending', 'dp'];

    /**
     * Transaksi milik pelanggan yang layak jadi notifikasi dan BELUM dihapus olehnya.
     */
    public static function dasar(int $userId): Builder
    {
        return Transaksi::query()
            ->where('transaksi.user_id', $userId)
            ->whereIn('transaksi.status_pembayaran', self::STATUS_NOTIF)
            ->whereNotExists(function ($q) use ($userId) {
                $q->select(DB::raw(1))
                    ->from('notifikasi_pelanggan as np')
                    ->whereColumn('np.transaksi_id', 'transaksi.id')
                    ->where('np.user_id', $userId)
                    ->whereNotNull('np.disembunyikan_at');
            });
    }

    /**
     * @return array{items: \Illuminate\Support\Collection, belum_dibaca: int, total: int}
     *   items        : notifikasi terbaru (atribut tambahan dibaca_at; null = belum dibaca)
     *   belum_dibaca : jumlah SEMUA notifikasi yang belum dibuka (angka pada badge)
     *   total        : jumlah SEMUA notifikasi yang masih tampil
     */
    public static function ringkasan(int $userId, int $batas = 10): array
    {
        $dibacaAt = DB::table('notifikasi_pelanggan as np')
            ->select('np.dibaca_at')
            ->whereColumn('np.transaksi_id', 'transaksi.id')
            ->where('np.user_id', $userId)
            ->limit(1);

        $items = static::dasar($userId)
            ->select('transaksi.*')
            ->selectSub($dibacaAt, 'dibaca_at')
            ->latest('transaksi.created_at')
            ->take($batas)
            ->get();

        $belumDibaca = static::dasar($userId)
            ->whereNotExists(function ($q) use ($userId) {
                $q->select(DB::raw(1))
                    ->from('notifikasi_pelanggan as np')
                    ->whereColumn('np.transaksi_id', 'transaksi.id')
                    ->where('np.user_id', $userId)
                    ->whereNotNull('np.dibaca_at');
            })
            ->count();

        return [
            'items'        => $items,
            'belum_dibaca' => $belumDibaca,
            'total'        => static::dasar($userId)->count(),
        ];
    }

    /** Tandai satu/banyak notifikasi sebagai sudah dibaca. */
    public static function tandaiDibaca(int $userId, int|array $transaksiIds): void
    {
        $ids = array_values(array_unique(array_map('intval', (array) $transaksiIds)));
        if (! $ids) {
            return;
        }

        $now = now();

        static::upsert(
            array_map(fn ($id) => [
                'user_id'      => $userId,
                'transaksi_id' => $id,
                'dibaca_at'    => $now,
                'created_at'   => $now,
                'updated_at'   => $now,
            ], $ids),
            ['user_id', 'transaksi_id'],
            ['dibaca_at', 'updated_at']
        );
    }

    /** Hapus (sembunyikan) satu/banyak notifikasi dari lonceng. Data transaksi tidak disentuh. */
    public static function sembunyikan(int $userId, int|array $transaksiIds): void
    {
        $ids = array_values(array_unique(array_map('intval', (array) $transaksiIds)));
        if (! $ids) {
            return;
        }

        $now = now();

        static::upsert(
            array_map(fn ($id) => [
                'user_id'          => $userId,
                'transaksi_id'     => $id,
                'dibaca_at'        => $now,
                'disembunyikan_at' => $now,
                'created_at'       => $now,
                'updated_at'       => $now,
            ], $ids),
            ['user_id', 'transaksi_id'],
            ['dibaca_at', 'disembunyikan_at', 'updated_at']
        );
    }
}