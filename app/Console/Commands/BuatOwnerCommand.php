<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BuatOwnerCommand extends Command
{
    /**
     * php artisan admin:buat-owner
     * php artisan admin:buat-owner --name="Nama Kamu" --email=kamu@contoh.com --password=rahasia123
     */
    protected $signature = 'admin:buat-owner
                            {--name= : Nama owner}
                            {--email= : Email untuk login}
                            {--password= : Password login}';

    protected $description = 'Buat atau promosikan akun jadi owner (akses penuh panel admin). Dipakai sekali di awal untuk bootstrap akun owner pertama.';

    public function handle(): int
    {
        $name     = $this->option('name') ?: $this->ask('Nama owner');
        $email    = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password (min 8 karakter)');

        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            if (!$this->confirm("Email {$email} sudah terdaftar (role saat ini: {$existing->role}). Jadikan owner?")) {
                $this->info('Dibatalkan.');
                return self::SUCCESS;
            }

            $existing->forceFill([
                'name'              => $name,
                'password'          => Hash::make($password),
                'role'              => 'owner',
                'admin_permissions' => [],
                'status_aktif'      => true,
            ])->save();

            $this->info("Akun {$email} berhasil dijadikan owner.");
            return self::SUCCESS;
        }

        $owner = new User();
        $owner->forceFill([
            'name'              => $name,
            'email'             => $email,
            'password'          => Hash::make($password),
            'role'              => 'owner',
            'admin_permissions' => [],
            'status_aktif'      => true,
        ])->save();

        $this->info("Owner baru berhasil dibuat: {$email}");
        $this->line('Sekarang kamu bisa login di /admin/login dengan akun ini.');

        return self::SUCCESS;
    }
}