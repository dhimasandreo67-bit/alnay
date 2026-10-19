<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Owner
        User::create([
            'name' => 'Raden Aditya',
            'email' => 'owner@alnay.id',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'no_hp' => '081200000001',
            'jenis_kelamin' => 'L',
            'status_aktif' => true,
        ]);

        // Admin
        User::create([
            'name' => 'Siti Adminah',
            'email' => 'admin@alnay.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'no_hp' => '081200000002',
            'jenis_kelamin' => 'P',
            'status_aktif' => true,
        ]);

        // Pelanggan (3 orang, untuk testimoni)
        $pelanggan = [
            ['nama' => 'Raditya A.', 'email' => 'raditya@example.com', 'jk' => 'L'],
            ['nama' => 'Fatiha M.', 'email' => 'fatiha@example.com', 'jk' => 'P'],
            ['nama' => 'Dian S.', 'email' => 'dian@example.com', 'jk' => 'P'],
        ];

        foreach ($pelanggan as $i => $p) {
            User::create([
                'name' => $p['nama'],
                'email' => $p['email'],
                'password' => Hash::make('password'),
                'role' => 'pelanggan',
                'no_hp' => '0812000000' . ($i + 10),
                'jenis_kelamin' => $p['jk'],
                'status_aktif' => true,
            ]);
        }
    }
}
