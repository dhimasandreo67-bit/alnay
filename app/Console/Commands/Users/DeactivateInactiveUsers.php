<?php

namespace App\Console\Commands\Users;

use App\Models\User;
use Illuminate\Console\Command;

class DeactivateInactiveUsers extends Command
{
    protected $signature = 'users:deactivate-inactive
                            {--days=360 : Batas hari tidak aktif}
                            {--dry-run : Hanya tampilkan, tidak mengubah data}';

    protected $description = 'Nonaktifkan akun yang tidak aktif ≥ N hari (default 360)';

    public function handle(): int
    {
        $batas = now()->subDays((int) $this->option('days'));
        $dry   = (bool) $this->option('dry-run');

        $query = User::query()
            ->where('is_active', true)
            ->where(function ($q) use ($batas) {
                $q->where('last_active_at', '<=', $batas)
                  ->orWhere(function ($q) use ($batas) {
                      // fallback: belum pernah login
                      $q->whereNull('last_active_at')
                        ->where('created_at', '<=', $batas);
                  });
            });

        $jumlah = (clone $query)->count();

        if ($dry) {
            $this->info("[dry-run] {$jumlah} akun akan dinonaktifkan.");
            return self::SUCCESS;
        }

        $query->update([
            'is_active'        => false,
            'deactivated_at'   => now(),
            'deactivated_by'   => null,
            'deactivated_note' => 'Auto: tidak aktif ≥'.$this->option('days').' hari',
        ]);

        $this->info("{$jumlah} akun dinonaktifkan otomatis.");
        return self::SUCCESS;
    }
}