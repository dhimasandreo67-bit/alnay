<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Bisnis ALNAY.ID
|--------------------------------------------------------------------------
|
| Semua nilai di bawah ini bisa diatur melalui file .env
|
*/

return [

    'brand' => [
        'nama'    => 'ALNAY.ID',
        'tagline' => 'Fashion Adat Nusantara',
    ],

    'promo_bar' => [
        'teks' => env('ALNAY_PROMO_TEKS', 'Selamat datang di ALNAY.ID'),
    ],

    'kontak' => [
        'wa_cs'         => env('ALNAY_WA_CS', '0882-4248-0648'),
        'wa_cs_display' => env('ALNAY_WA_CS_DISPLAY', '0882-4248-0648'),
        'email'         => env('ALNAY_EMAIL', 'storealnay.id@gmail.com'),
    ],

    'toko' => [
        'nama'     => 'Toko ALNAY',
        'jam_buka' => env('ALNAY_JAM_BUKA', 'Senin - Sabtu, 08.00 - 19.00 WIB'),
        'alamat'   => env('ALNAY_ALAMAT', 'Jl. Terusan Ragom Gawi III, Perumahan Taman Cinta A7, Kelurahan Kedaung, Kecamatan Kemiling, Kota Bandar Lampung, Lampung 35158'),
    ],

    'event' => [
        'url' => env('ALNAY_EVENT_URL', 'https://event.alnay.id'),
    ],

    'dp_sewa' => env('ALNAY_DP_SEWA', 100000),
    'deposit_ktp' => env('ALNAY_DEPOSIT_KTP', 200000),

];