<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\OtpCode;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Throwable;

/**
 * Perekam jejak aktivitas sistem (audit log).
 *
 * Otomatis merekam:
 *  - semua data yang dibuat / diubah / dihapus lewat Eloquent (semua model sekaligus,
 *    tanpa perlu menyentuh model satu per satu),
 *  - masuk, keluar, dan percobaan masuk yang gagal.
 *
 * Untuk aksi yang tidak lewat Eloquent (mis. DB::table()->update()), panggil manual:
 *   Audit::catat('status', 'menonaktifkan akun "Budi"', $user);
 *
 * Perekaman TIDAK PERNAH boleh merusak request utama: semua error ditelan & dilaporkan saja.
 */
class Audit
{
    /** Model yang tidak dicatat (log itu sendiri, kode OTP, dan riwayat status yang sudah terwakili log Transaksi). */
    protected const MODEL_DIABAIKAN = [AuditLog::class, OtpCode::class, RiwayatStatus::class];

    /** Kolom yang kalau jadi satu-satunya perubahan dianggap noise. */
    protected const KOLOM_DIABAIKAN = ['created_at', 'updated_at', 'deleted_at', 'last_active_at', 'remember_token'];

    /** Nilai kolom dengan nama seperti ini disamarkan di log. */
    protected const KOLOM_RAHASIA = '/password|token|secret|otp|api_key/i';

    /** Urutan kolom yang dipakai sebagai nama/label sebuah data. */
    protected const KOLOM_LABEL = ['name', 'nama', 'nama_produk', 'nama_kategori', 'judul', 'kode_transaksi', 'kode', 'email'];

    /** Penanda supaya listener tidak terpasang dua kali. */
    protected static bool $terdaftar = false;

    /**
     * Pasang listener. Dipanggil sekali dari AppServiceProvider::boot().
     */
    public static function daftarkan(): void
    {
        if (self::$terdaftar) {
            return;
        }
        self::$terdaftar = true;

        Event::listen(
            ['eloquent.created: *', 'eloquent.updated: *', 'eloquent.deleted: *'],
            function (string $event, array $data) {
                try {
                    $model = $data[0] ?? null;

                    if ($model instanceof Model) {
                        self::catatModel(Str::before(Str::after($event, 'eloquent.'), ':'), $model);
                    }
                } catch (Throwable $e) {
                    report($e);
                }
            }
        );

        Event::listen(Login::class, function (Login $e) {
            try {
                self::catatAuth('login', $e->user, $e->guard);
            } catch (Throwable $ex) {
                report($ex);
            }
        });

        Event::listen(Logout::class, function (Logout $e) {
            try {
                self::catatAuth('logout', $e->user, $e->guard);
            } catch (Throwable $ex) {
                report($ex);
            }
        });

        Event::listen(Failed::class, function (Failed $e) {
            try {
                $kredensial = is_array($e->credentials) ? $e->credentials : [];
                self::catatAuth('login_gagal', null, $e->guard, $kredensial['email'] ?? $kredensial['no_hp'] ?? null);
            } catch (Throwable $ex) {
                report($ex);
            }
        });
    }

    /**
     * Catat aksi manual. $teks tanpa nama pelaku (otomatis diawali nama pelaku).
     *
     * @param  array<int, array{field: string, lama?: ?string, baru?: ?string}>|null  $perubahan
     */
    public static function catat(string $aksi, string $teks, ?Model $subjek = null, ?array $perubahan = null): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $pelaku = self::pelaku();

        self::simpan($pelaku, [
            'aksi' => $aksi,
            'deskripsi' => "{$pelaku['nama']} {$teks}",
            'subjek' => $subjek,
            'perubahan' => $perubahan,
        ]);
    }

    // ------------------------------------------------------------------ internal

    protected static function catatModel(string $aksi, Model $model): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        foreach (self::MODEL_DIABAIKAN as $kelas) {
            if ($model instanceof $kelas) {
                return;
            }
        }

        $perubahan = [];
        $kolomDiubah = [];

        if ($aksi === 'updated') {
            // Di dalam event "updated", getChanges() berisi nilai baru dan getOriginal() masih nilai lama.
            $diubah = Arr::except($model->getChanges(), self::KOLOM_DIABAIKAN);

            if (! $diubah) {
                return;
            }

            $kolomDiubah = array_keys($diubah);

            foreach ($diubah as $kolom => $baru) {
                $perubahan[] = [
                    'field' => Str::headline($kolom),
                    'lama' => self::nilai($kolom, $model->getOriginal($kolom)),
                    'baru' => self::nilai($kolom, $baru),
                ];
            }
        } elseif ($aksi === 'created') {
            $isi = Arr::except($model->getAttributes(), array_merge(self::KOLOM_DIABAIKAN, [$model->getKeyName()]));
            $isi = array_filter($isi, fn ($v) => $v !== null && $v !== '');

            foreach (array_slice($isi, 0, 15, true) as $kolom => $nilai) {
                $perubahan[] = ['field' => Str::headline($kolom), 'baru' => self::nilai($kolom, $nilai)];
            }
        }

        $kata = ['created' => 'menambahkan', 'updated' => 'mengubah', 'deleted' => 'menghapus'][$aksi] ?? $aksi;
        $modul = self::namaModul($model);
        $label = self::labelModel($model);

        $pelaku = self::pelaku();
        $deskripsi = "{$pelaku['nama']} {$kata} {$modul} \"{$label}\"";

        if ($kolomDiubah) {
            $nama = array_map(fn ($k) => Str::headline($k), array_slice($kolomDiubah, 0, 4));
            $deskripsi .= ' (' . implode(', ', $nama) . (count($kolomDiubah) > 4 ? ', …' : '') . ')';
        }

        self::simpan($pelaku, [
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'subjek' => $model,
            'perubahan' => $perubahan,
        ]);
    }

    protected static function catatAuth(string $aksi, ?Authenticatable $user, string $guard, ?string $email = null): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if ($aksi === 'login_gagal') {
            $target = $email ?: 'tanpa identitas';

            self::simpan(
                ['user' => null, 'guard' => $guard, 'nama' => $target, 'peran' => 'tamu'],
                [
                    'aksi' => $aksi,
                    'modul' => 'Autentikasi',
                    'label' => $target,
                    'deskripsi' => "Percobaan masuk gagal untuk akun \"{$target}\" (" . self::namaGuard($guard) . ')',
                ]
            );

            return;
        }

        if (! $user) {
            return;
        }

        $pelaku = [
            'user' => $user,
            'guard' => $guard,
            'nama' => $user->name ?? 'Tanpa nama',
            'peran' => self::peran($user),
        ];

        $teks = $aksi === 'login' ? 'masuk ke sistem' : 'keluar dari sistem';

        self::simpan($pelaku, [
            'aksi' => $aksi,
            'modul' => 'Autentikasi',
            'label' => $user->email ?? null,
            'deskripsi' => "{$pelaku['nama']} {$teks} (" . self::namaGuard($guard) . ')',
        ]);
    }

    /**
     * Siapa yang sedang beraksi. Request /admin/* mengutamakan guard admin, sisanya guard pelanggan.
     */
    protected static function pelaku(): array
    {
        $urutan = request()->is('admin', 'admin/*') ? ['admin', 'web'] : ['web', 'admin'];

        foreach ($urutan as $guard) {
            try {
                $user = auth($guard)->user();
            } catch (Throwable) {
                $user = null;
            }

            if ($user) {
                return [
                    'user' => $user,
                    'guard' => $guard,
                    'nama' => $user->name ?? 'Tanpa nama',
                    'peran' => self::peran($user),
                ];
            }
        }

        return ['user' => null, 'guard' => null, 'nama' => 'Tamu', 'peran' => 'tamu'];
    }

    protected static function peran(Authenticatable $user): string
    {
        $role = strtolower((string) ($user->role ?? ''));

        return match (true) {
            $role === 'owner' => 'owner',
            in_array($role, User::ROLE_STAF, true) => 'admin',
            default => 'pelanggan',
        };
    }

    protected static function namaGuard(string $guard): string
    {
        return $guard === 'admin' ? 'panel admin' : 'situs pelanggan';
    }

    protected static function namaModul(Model $model): string
    {
        if ($model instanceof User) {
            return in_array(strtolower((string) $model->role), User::ROLE_STAF, true) ? 'Admin' : 'Pengguna';
        }

        return Str::headline(class_basename($model));
    }

    protected static function labelModel(Model $model): string
    {
        foreach (self::KOLOM_LABEL as $kolom) {
            $nilai = $model->getAttribute($kolom);

            if (is_string($nilai) && trim($nilai) !== '') {
                return Str::limit(trim($nilai), 80);
            }
        }

        return '#' . $model->getKey();
    }

    protected static function nilai(string $kolom, mixed $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        if (preg_match(self::KOLOM_RAHASIA, $kolom)) {
            return '••••••';
        }

        if (is_bool($nilai)) {
            return $nilai ? 'Ya' : 'Tidak';
        }

        if (is_array($nilai) || is_object($nilai)) {
            $nilai = json_encode($nilai, JSON_UNESCAPED_UNICODE);
        }

        return Str::limit((string) $nilai, 120);
    }

    /**
     * Tulis satu baris log. Langsung lewat query builder (bukan model) supaya tidak memicu event lagi.
     */
    protected static function simpan(array $pelaku, array $d): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        try {
            /** @var Model|null $subjek */
            $subjek = $d['subjek'] ?? null;
            $req = request();

            DB::table('audit_logs')->insert([
                'user_id' => $pelaku['user']?->getAuthIdentifier(),
                'pelaku' => Str::limit((string) $pelaku['nama'], 250, ''),
                'peran' => $pelaku['peran'],
                'guard' => $pelaku['guard'],
                'aksi' => $d['aksi'],
                'modul' => $d['modul'] ?? ($subjek ? self::namaModul($subjek) : null),
                'subjek_type' => $subjek ? $subjek::class : null,
                'subjek_id' => $subjek ? (string) $subjek->getKey() : null,
                'subjek_label' => $d['label'] ?? ($subjek ? self::labelModel($subjek) : null),
                'deskripsi' => $d['deskripsi'],
                'perubahan' => ! empty($d['perubahan']) ? json_encode($d['perubahan'], JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $req->ip(),
                'user_agent' => Str::limit((string) $req->userAgent(), 500, ''),
                'url' => Str::limit($req->path(), 500, ''),
                'method' => $req->method(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}