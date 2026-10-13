<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Bisnis ALNAY.ID
|--------------------------------------------------------------------------
|
| ⚠️ SEMUA NILAI DI BAWAH INI ADALAH PLACEHOLDER.
| Ganti dengan nilai final sebelum produksi.
| JANGAN hardcode nilai ini di Blade — selalu baca lewat config().
|
*/

return [

    'brand' => [
        'nama'    => 'ALNAY.ID',
        'tagline' => 'Fashion Adat Nusantara',
    ],

    'promo_bar' => [
        'teks' => env('ALNAY_PROMO_TEKS', 'PLACEHOLDER: teks promo beranda'),
    ],

    'kontak' => [
        'wa_cs'         => env('ALNAY_WA_CS', '6280000000000'),
        'wa_cs_display' => env('ALNAY_WA_CS_DISPLAY', '+62 800-0000-000'),
        'email'         => env('ALNAY_EMAIL', 'placeholder@alnay.id'),
    ],

    'studio' => [
        'jam_buka' => env('ALNAY_JAM_BUKA', 'PLACEHOLDER: jam buka studio'),
        'alamat'   => env('ALNAY_ALAMAT', 'PLACEHOLDER: alamat atelier'),
        // ⚠️ Figma inkonsisten (Bandung vs Lampung). Jangan diisi sampai final.
        // ⚠️ Aturan bisnis: lokasi fitting HANYA SATU.
    ],

    'event' => [
        'url' => env('ALNAY_EVENT_URL', 'https://event.alnay.id'),
        // Sistem event TERPISAH TOTAL. Dari web utama, ini hanya tautan keluar.
        // JANGAN buat query/model/migrasi ke tabel event di project ini.
    ],

];
