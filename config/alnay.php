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
        'wa_cs'         => env('ALNAY_WA_CS', '6288242480648'),
        'wa_cs_display' => env('ALNAY_WA_CS_DISPLAY', '+62 8824-2480-648'),
        'email'         => env('ALNAY_EMAIL', 'placeholder@alnay.id'),
    ],

    'toko' => [
        'nama'     => 'Toko ALNAY',
        'jam_buka' => env('ALNAY_JAM_BUKA', 'Senin - Sabtu, 09.00 - 18.00 WIB'),
        'alamat'   => env('ALNAY_ALAMAT', 'Alamat Toko ALNAY (Belum diatur)'),
    ],

    'event' => [
        'url' => env('ALNAY_EVENT_URL', 'https://event.alnay.id'),
    ],

    'dp_sewa' => env('ALNAY_DP_SEWA', 1000000),
    'deposit_ktp' => env('ALNAY_DEPOSIT_KTP', 200000),

];