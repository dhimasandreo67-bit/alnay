<?php

namespace App\Console\Commands;

use App\Models\Pengiriman;
use App\Models\Rating;
use App\Models\Refund;
use App\Models\ReservasiFitting;
use App\Models\Transaksi;
use App\Models\User;
use App\Notifications\AdminNotifikasi;
use App\Providers\NotifikasiServiceProvider;
use App\Support\NotifikasiAdmin;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

/**
 * Memeriksa apakah sistem notifikasi admin sudah terpasang dengan benar.
 *
 *   php artisan notifikasi:cek            -> periksa saja
 *   php artisan notifikasi:cek --kirim    -> periksa + kirim 1 notifikasi uji ke semua owner/admin
 */
class CekNotifikasi extends Command
{
    protected $signature = 'notifikasi:cek {--kirim : Kirim notifikasi uji ke semua penerima}';

    protected $description = 'Periksa pemasangan sistem notifikasi admin (provider, tabel, penerima) dan bila perlu kirim notifikasi uji.';

    private int $masalah = 0;

    public function handle(): int
    {
        $this->info('Pemeriksaan sistem notifikasi admin');
        $this->newLine();

        // 1. Provider
        $this->line('<options=bold>1. Provider</>');
        $terdaftar = app()->getProvider(NotifikasiServiceProvider::class) !== null;
        $this->hasil(
            $terdaftar,
            'NotifikasiServiceProvider terdaftar',
            'Tambahkan App\\Providers\\NotifikasiServiceProvider::class di bootstrap/providers.php, lalu php artisan optimize:clear'
        );

        // 2. Pendengar event model
        $this->line('<options=bold>2. Pendengar event model</>');
        $dispatcher = Model::getEventDispatcher();
        $pendengar = [
            Transaksi::class . '|created'       => 'Pesanan baru (Transaksi created)',
            Transaksi::class . '|updated'       => 'Pembayaran masuk (Transaksi updated)',
            ReservasiFitting::class . '|created' => 'Booking fitting baru',
            Refund::class . '|created'          => 'Pengajuan refund baru',
            Pengiriman::class . '|updated'      => 'Pesanan diterima pelanggan',
            User::class . '|created'            => 'Pelanggan baru',
            Rating::class . '|created'          => 'Ulasan baru',
        ];
        foreach ($pendengar as $kunci => $label) {
            [$kelas, $event] = explode('|', $kunci);
            $this->hasil(
                $dispatcher->hasListeners("eloquent.{$event}: {$kelas}"),
                $label,
                $terdaftar ? 'Pendengar tidak terpasang. Jalankan php artisan optimize:clear' : 'Karena provider belum terdaftar (langkah 1)'
            );
        }

        // 3. Tabel
        $this->line('<options=bold>3. Tabel database</>');
        $adaTabel = Schema::hasTable('notifications');
        $this->hasil($adaTabel, 'Tabel notifications ada', 'Jalankan php artisan migrate');
        $this->hasil(Schema::hasTable('pengingat_admin'), 'Tabel pengingat_admin ada', 'Jalankan php artisan migrate');

        // 4. Penerima
        $this->line('<options=bold>4. Penerima notifikasi</>');
        $menu = ['transaksi', 'sewa', 'pengiriman', 'fitting', 'refund', 'pengguna', 'rating'];
        $baris = [];
        foreach ($menu as $m) {
            $p = NotifikasiAdmin::penerima($m);
            $baris[] = [$m, $p->count(), $p->pluck('email')->implode(', ') ?: '-'];
        }
        $this->table(['Menu', 'Jumlah penerima', 'Penerima'], $baris);
        $this->hasil(
            NotifikasiAdmin::penerima('transaksi')->isNotEmpty(),
            'Ada penerima untuk menu "transaksi"',
            'Tidak ada user role owner/admin yang aktif dan punya akses menu ini. Periksa kolom role, status_aktif, dan admin_permissions'
        );

        // 5. Isi tabel notifikasi
        if ($adaTabel) {
            $this->line('<options=bold>5. Notifikasi tersimpan</>');
            $total = \Illuminate\Notifications\DatabaseNotification::count();
            $this->line("   Total di tabel: {$total}");
            $terbaru = \Illuminate\Notifications\DatabaseNotification::latest()->limit(3)->get();
            foreach ($terbaru as $n) {
                $this->line('   - [' . $n->created_at?->format('d M H:i') . '] ' . ($n->data['judul'] ?? '?') . ' → user #' . $n->notifiable_id);
            }
            if ($total === 0) {
                $this->warn('   Belum ada notifikasi. Pesanan yang dibuat sebelum sistem aktif tidak ikut dicatat; buat pesanan baru untuk mencoba.');
            }
        }

        // 6. Kirim uji
        if ($this->option('kirim')) {
            $this->newLine();
            $this->line('<options=bold>6. Kirim notifikasi uji</>');

            if (! $adaTabel) {
                $this->error('   Dilewati: tabel notifications belum ada.');
            } else {
                $penerima = User::whereIn('role', ['owner', 'admin'])->get();
                try {
                    Notification::send($penerima, new AdminNotifikasi([
                        'kategori'  => 'pesanan',
                        'ikon'      => 'bell',
                        'nada'      => 'amber',
                        'prioritas' => 'normal',
                        'judul'     => 'Tes notifikasi',
                        'pesan'     => 'Jika Anda melihat ini di lonceng, sistem notifikasi bekerja.',
                        'url'       => route('admin.notifikasi.index', [], false),
                        'menu'      => 'transaksi',
                    ]));
                    $this->info('   Terkirim ke ' . $penerima->count() . ' akun. Buka panel admin: lonceng akan terisi dalam ±20 detik (atau muat ulang halaman).');
                } catch (\Throwable $e) {
                    $this->masalah++;
                    $this->error('   GAGAL: ' . $e->getMessage());
                }
            }
        }

        $this->newLine();
        if ($this->masalah === 0) {
            $this->info('Semua pemeriksaan lolos.');

            return self::SUCCESS;
        }

        $this->error("{$this->masalah} masalah ditemukan. Perbaiki sesuai petunjuk di atas, lalu jalankan perintah ini lagi.");

        return self::FAILURE;
    }

    private function hasil(bool $ok, string $label, string $petunjuk): void
    {
        if ($ok) {
            $this->line("   <fg=green>✔</> {$label}");

            return;
        }

        $this->masalah++;
        $this->line("   <fg=red>✘</> {$label}");
        $this->line("     <fg=yellow>→ {$petunjuk}</>");
    }
}