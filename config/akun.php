<?php

return [

    /*
    | Akun dianggap "dinonaktifkan karena tidak aktif" bila selisih antara aktivitas
    | terakhir (last_active_at, atau tanggal daftar) dan waktu dinonaktifkan >= batas ini.
    | Di bawah batas ini, pelanggan hanya melihat "dinonaktifkan oleh admin".
    */
    'batas_hari_tidak_aktif' => 365,

    /*
    | Kontak admin yang tampil di halaman Akun Dinonaktifkan.
    | Isi lewat .env:
    |   ADMIN_WHATSAPP=6281234567890      (boleh 0812..., otomatis diubah ke 62...)
    |   ADMIN_EMAIL=admin@alnay.id
    |   ADMIN_JAM_LAYANAN="Setiap hari, 09.00 - 17.00 WIB"   (opsional)
    | Item yang kosong tidak ditampilkan.
    */
    'kontak_admin' => [
        'whatsapp' => env('ADMIN_WHATSAPP'),
        'email'    => env('ADMIN_EMAIL'),
        'jam'      => env('ADMIN_JAM_LAYANAN'),
        'alamat'   => env('ADMIN_ALAMAT', 'Jl. Terusan Ragom Gawi III, Perumahan Taman Cinta A7, Kelurahan Kedaung, Kecamatan Kemiling, Kota Bandar Lampung, Lampung 35158'),
    ],

];