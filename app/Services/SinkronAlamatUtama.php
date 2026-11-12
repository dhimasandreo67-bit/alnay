<?php

namespace App\Services;

use App\Models\AlamatPengiriman;
use App\Models\User;

class SinkronAlamatUtama
{
    public static function untuk(int $userId, bool $kosongkanBilaTidakAda = false): void
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        $alamat = AlamatPengiriman::where('user_id', $userId)
            ->orderByDesc('is_utama')
            ->orderByDesc('updated_at')
            ->first();

        if (! $alamat) {
            if ($kosongkanBilaTidakAda) {
                // no_hp sengaja tidak dikosongkan: itu nomor akun, bukan hanya alamat
                $user->forceFill([
                    'alamat'         => null,
                    'provinsi'       => null,
                    'provinsi_nama'  => null,
                    'kota_kabupaten' => null,
                    'kota_nama'      => null,
                    'kecamatan'      => null,
                    'kode_pos'       => null,
                ])->saveQuietly();
            }
            return;
        }

        $user->forceFill([
            'alamat'         => $alamat->alamat,
            'provinsi'       => $alamat->provinsi,
            'provinsi_nama'  => $alamat->provinsi,
            'kota_kabupaten' => $alamat->kota_kabupaten,
            'kota_nama'      => $alamat->kota_kabupaten,
            'kecamatan'      => $alamat->kecamatan,
            'kode_pos'       => $alamat->kode_pos,
            // Nomor HP user mengikuti alamat utama; kalau nomor alamat kosong, pakai nomor lama
            'no_hp'          => $alamat->no_hp ?: $user->no_hp,
        ])->saveQuietly();
    }
}