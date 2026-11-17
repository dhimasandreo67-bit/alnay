@extends('layouts.public')

@section('title', 'Cara Sewa & Beli - ALNAY.ID')
{{-- Latar footer disamakan dengan CTA gelap di atasnya (warna dasar siger) --}}
@section('footer_bg', '#0a0a0a')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

@php
    /* ------------------------------------------------------------
       Ikon (path SVG) — satu tempat, dipakai ulang di seluruh halaman
       ------------------------------------------------------------ */
    $paths = [
        'check'    => ['M5 13l4 4L19 7'],
        'calendar' => ['M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        'cart'     => ['M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        'pin'      => ['M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 'M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
        'truck'    => ['M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
        'clock'    => ['M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'box'      => ['M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        'coin'     => ['M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'sparkle'  => ['M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
        'undo'     => ['M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
        'ok'       => ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'bolt'     => ['M13 10V3L4 14h7v7l9-11h-7z'],
        'arrow'    => ['M9 5l7 7-7 7'],
        'down'     => ['M19 14l-7 7m0 0l-7-7m7 7V3'],
        'scale'    => ['M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3'],
    ];
    $ic = function (string $nama, string $kelas = 'w-5 h-5', $tebal = 2) use ($paths) {
        $d = '';
        foreach ($paths[$nama] ?? [] as $p) {
            $d .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="' . $tebal . '" d="' . $p . '"></path>';
        }
        return '<svg class="' . $kelas . '" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">' . $d . '</svg>';
    };

    $lokasi = 'Jl. Terusan Rajabasa No.11, Bandar Lampung';

    /* ------------------------------------------------------------
       Konten panduan: SEWA & BELI memakai satu template yang sama
       ------------------------------------------------------------ */
    $panels = [
        'sewa' => [
            'tema'   => 'sewa',
            'badge'  => 'Alur Sewa',
            'judul'  => 'Cara Sewa di ALNAY.ID',
            'intro'  => 'Proses sewa mudah dalam 5 langkah — gratis fitting, gratis alterasi, dan tanpa perlu cuci setelah acara.',
            'steps'  => [
                ['Pilih Produk',          'Jelajahi katalog sewa, pilih busana atau aksesoris, dan tambahkan ke keranjang sewa.'],
                ['Konsultasi & Fitting',  'Hubungi CS kami untuk konfirmasi ukuran. Fitting langsung di toko atau via video call.'],
                ['Bayar + Deposit',       'Bayar biaya sewa & deposit jaminan. Deposit dikembalikan 100% setelah barang kembali aman.'],
                ['Pengiriman / Ambil',    'Barang dikirim via kurir khusus (H-3 acara) atau diambil langsung di toko kami.'],
                ['Pengembalian Mudah',    'Kembalikan setelah acara selesai. Tidak perlu dicuci! Kami handle laundry & sterilisasi.'],
            ],
            'metode_judul' => '2 Metode Pengambilan Sewa',
            'metode' => [
                [
                    'label' => 'Metode A', 'judul' => 'Ambil di Toko', 'sub' => 'Booking jadwal fitting langsung', 'ikon' => 'pin',
                    'poin'  => [
                        ['Booking Jadwal Fitting',      'Pilih tanggal & waktu lewat reservasi online'],
                        ['Fitting Langsung di Toko',    'Coba langsung dengan penjahit profesional'],
                        ['Gratis Alterasi On-Spot',     'Penyesuaian ukuran langsung di tempat'],
                    ],
                    'catatan' => ['ikon' => 'pin', 'judul' => 'Lokasi ALNAY', 'teks' => $lokasi, 'sorot' => ['clock', 'Jam operasional fleksibel. Konfirmasi via WA.']],
                    'cta' => [route('public.fitting.create'), 'Booking Jadwal Fitting', 'calendar'],
                ],
                [
                    'label' => 'Metode B', 'judul' => 'Kirim ke Alamat', 'sub' => 'Pengiriman aman ke seluruh Indonesia', 'ikon' => 'truck',
                    'poin'  => [
                        ['Kurir Khusus & Asuransi',        'JNE YES, GoSend, GrabExpress, Paxel'],
                        ['Pengiriman H-3 Acara',           'Dikirim 3 hari sebelum acara'],
                        ['Panduan Ukuran via Video Call',  'Konsultasi sebelum pengiriman'],
                    ],
                    'catatan' => ['ikon' => 'box', 'judul' => 'Ongkos Kirim', 'teks' => 'Dihitung otomatis saat checkout sesuai alamat dan kurir yang Anda pilih', 'sorot' => ['bolt', 'Express tersedia untuk mendesak']],
                    'cta' => [route('koleksi.index'), 'Mulai Sewa Sekarang', 'cart'],
                ],
            ],
            'kebijakan_judul' => 'Kebijakan Sewa',
            'kebijakan_sub'   => 'Transparan tanpa biaya tersembunyi',
            'kebijakan' => [
                ['coin',    'Deposit 100% Kembali',     'Deposit jaminan dikembalikan penuh setelah busana diterima kembali dalam kondisi wajar.'],
                ['clock',   'Denda Keterlambatan',      'Keterlambatan pengembalian dikenakan denda proporsional per hari sesuai detail produk.'],
                ['sparkle', 'Gratis Laundry & Steril',  'Tidak perlu cuci! Kami handle dry clean, ozon sterilization, dan packaging ulang.'],
            ],
            'silang' => ['beli', 'Lebih tertarik memiliki? Lihat panduan beli'],
        ],

        'beli' => [
            'tema'   => 'beli',
            'badge'  => 'Alur Beli',
            'judul'  => 'Cara Beli di ALNAY.ID',
            'intro'  => 'Miliki busana ALNAY sebagai koleksi pribadi. Proses beli mudah, bayar via QRIS, dan ada garansi refund 7 hari.',
            'steps'  => [
                ['Pilih Produk',       'Pilih busana ready stock, tentukan ukuran dan warna, lalu tambahkan ke keranjang.'],
                ['Checkout',           'Pilih kirim ke alamat atau ambil di toko, lalu periksa total pembayaran.'],
                ['Bayar via QRIS',     'Scan QRIS dari e-wallet atau m-banking. Status pembayaran terkonfirmasi otomatis.'],
                ['Terima Barang',      'Barang dikirim lewat kurir pilihan Anda, atau diambil langsung di toko.'],
                ['Refund bila Perlu',  'Ada masalah dengan barang? Ajukan refund dalam 7 hari setelah barang diterima.'],
            ],
            'metode_judul' => '2 Metode Pembelian',
            'metode' => [
                [
                    'label' => 'Metode A', 'judul' => 'Beli & Ambil di Toko', 'sub' => 'Tanpa ongkir, ambil langsung', 'ikon' => 'cart',
                    'poin'  => [
                        ['Pilih Produk Ready Stock',   'Pilih ukuran dan warna yang tersedia di katalog'],
                        ['Checkout & Bayar via QRIS',  'Pilih opsi ambil di toko saat checkout'],
                        ['Ambil di Toko',              'Ambil di toko setelah pembayaran terkonfirmasi'],
                        ['Refund 7 Hari',              'Ajukan dalam 7 hari setelah barang diterima (syarat berlaku)'],
                    ],
                    'catatan' => ['ikon' => 'pin', 'judul' => 'Lokasi ALNAY', 'teks' => $lokasi, 'sorot' => ['clock', 'Jam operasional fleksibel. Konfirmasi via WA.']],
                    'cta' => [route('koleksi.index'), 'Belanja Sekarang', 'cart'],
                ],
                [
                    'label' => 'Metode B', 'judul' => 'Beli & Kirim ke Rumah', 'sub' => 'Untuk produk ready stock', 'ikon' => 'box',
                    'poin'  => [
                        ['Pilih dari Katalog Ready',          'Ukuran dan warna sesuai ketersediaan di katalog'],
                        ['Checkout & Pembayaran',             'QRIS, bisa dari semua e-wallet dan m-banking'],
                        ['Dikirim via Kurir Pilihan Anda',    'Estimasi tiba mengikuti kurir yang dipilih saat checkout'],
                        ['Refund 7 Hari',                     'Ajukan dalam 7 hari setelah barang diterima (syarat berlaku)'],
                    ],
                    'catatan' => ['ikon' => 'box', 'judul' => 'Pembayaran', 'teks' => 'Semua transaksi dibayar lewat QRIS, status terkonfirmasi otomatis', 'sorot' => null],
                    'cta' => [route('koleksi.index'), 'Belanja Sekarang', 'cart'],
                ],
            ],
            'kebijakan_judul' => 'Kebijakan Pembelian & Refund',
            'kebijakan_sub'   => 'Beli dengan tenang, ada garansi refund',
            'kebijakan' => [
                ['undo',  'Refund 7 Hari',         'Ajukan refund paling lama 7 hari setelah barang Anda terima. Berlaku untuk pembelian yang sudah lunas.'],
                ['clock', 'Ditinjau Admin',        'Ajukan lewat menu Akun → Refund, lengkapi alasan dan foto bukti. Admin meninjau paling lama 1x24 jam. Setelah disetujui, barang dikembalikan lewat kurir sesuai arahan admin.'],
                ['ok',    'Dana Kembali ke Anda',  'Setelah barang diterima dan dicek, dana ditransfer ke rekening atau e-wallet yang Anda isi. Bila barang bermasalah atau tidak sesuai, ongkir ikut dikembalikan. Alasan lain ditinjau kasus per kasus.'],
            ],
            'silang' => ['sewa', 'Butuh untuk satu acara saja? Lihat panduan sewa'],
        ],
    ];

    /* Kartu pilihan di bagian atas (juga berfungsi sebagai tab) */
    $pilihan = [
        'sewa' => [
            'nomor' => 'Layanan 01', 'judul' => 'Sewa Busana', 'ikon' => 'calendar',
            'desk'  => 'Pakai busana premium untuk momen istimewa — hemat, praktis, dan tanpa perlu cuci setelah acara.',
            'poin'  => ['Deposit <strong>100% kembali</strong>', 'Gratis fitting & alterasi', 'Gratis laundry & sterilisasi'],
            'cocok' => 'Pernikahan, wisuda, photoshoot, acara adat',
            'cta'   => 'Lihat Panduan Sewa',
        ],
        'beli' => [
            'nomor' => 'Layanan 02', 'judul' => 'Beli Busana', 'ikon' => 'cart',
            'desk'  => 'Miliki busana adat sebagai koleksi pribadi — bisa dipakai kapan saja, tanpa batas waktu.',
            'poin'  => ['Milik <strong>permanen</strong>', 'Refund 7 hari (jika ada masalah)', 'Bayar praktis via QRIS'],
            'cocok' => 'Koleksi pribadi, hadiah, seragam acara keluarga',
            'cta'   => 'Lihat Panduan Beli',
        ],
    ];

    /* Tabel perbandingan */
    $banding = [
        ['Tujuan',               'Pakai sekali untuk acara spesial',              'Miliki permanen untuk koleksi pribadi'],
        ['Biaya',                'Lebih hemat (biaya sewa + deposit)',            'Bayar sekali, tanpa deposit'],
        ['Deposit Jaminan',      'Ya, dikembalikan 100%',                         'Tidak ada'],
        ['Fitting & Alterasi',   'Gratis, termasuk penyesuaian ukuran',           'Tidak termasuk fitting'],
        ['Perawatan',            'Gratis laundry & sterilisasi setelah kembali',  'Tanggung jawab pembeli'],
        ['Pengembalian Barang',  'Wajib dikembalikan setelah acara',              'Tidak perlu dikembalikan (milik Anda)'],
        ['Denda',                'Denda keterlambatan per hari',                  'Tidak ada denda'],
        ['Garansi',              'Deposit kembali 100% (barang aman)',            'Refund 7 hari (jika ada masalah)'],
        ['Waktu Barang',         'Tiba H-3 acara (kurir khusus)',                 'Estimasi tiba mengikuti kurir pilihan'],
    ];
@endphp

<style>
    /* Semua aturan dibatasi ke .cs supaya header/footer tidak ikut berubah */
    .cs {
        --navy: #15233d;
        --navy-soft: #eef2f8;
        --gold: #c99a32;
        --gold-strong: #9a7119;   /* emas lebih gelap: teks & tombol tetap terbaca */
        --gold-soft: #fbf6e9;
        --muted: #5b6472;
        --line: #e5e7eb;
        font-family: 'Inter', sans-serif;
        color: var(--navy);
        overflow-x: clip;
    }
    .cs *, .cs *::before, .cs *::after { box-sizing: border-box; }
    .cs .font-display { font-family: 'Playfair Display', serif; }
    .cs a { text-decoration: none; }
    .cs :focus-visible { outline: 3px solid var(--gold); outline-offset: 3px; border-radius: .5rem; }

    /* Tema per panduan (emas = sewa, navy = beli) */
    .cs .tema-sewa { --accent: var(--gold-strong); --accent-bright: var(--gold); --soft: var(--gold-soft); --glow: rgba(201,154,50,.28); }
    .cs .tema-beli { --accent: var(--navy);        --accent-bright: var(--navy); --soft: var(--navy-soft); --glow: rgba(21,35,61,.22); }

    /* Tata letak */
    .cs .wrap { max-width: 1120px; margin: 0 auto; padding: 0 1.25rem; }
    @media (min-width: 768px) { .cs .wrap { padding: 0 2rem; } }
    .cs .sec { padding: 3.5rem 0; }
    @media (min-width: 768px) { .cs .sec { padding: 5rem 0; } }
    .cs .sec-head { text-align: center; max-width: 42rem; margin: 0 auto 2.5rem; }
    .cs .sec-head h2 { font-size: clamp(1.6rem, 3.2vw, 2.25rem); font-weight: 700; line-height: 1.2; }
    .cs .sec-head p  { margin-top: .75rem; font-size: .95rem; line-height: 1.7; color: var(--muted); }
    .cs .rule { width: 56px; height: 3px; margin: 1rem auto 0; border-radius: 9999px; background: linear-gradient(90deg, var(--accent-bright, var(--gold)), transparent); }
    .cs [id] { scroll-margin-top: 90px; }

    /* ---------- Hero ---------- */
    .cs .hero { background: #faf7f0; padding: 3.5rem 0 1rem; text-align: center; }
    @media (min-width: 768px) { .cs .hero { padding: 4.5rem 0 1.5rem; } }
    .cs .eyebrow {
        display: inline-block; padding: .35rem 1rem; border-radius: 9999px; margin-bottom: 1.25rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .28em; text-transform: uppercase;
        color: var(--gold-strong); background: rgba(201,154,50,.14); border: 1px solid rgba(201,154,50,.3);
    }
    .cs .hero h1 { font-size: clamp(2rem, 5vw, 3.25rem); font-weight: 700; line-height: 1.15; }
    .cs .hero h1 span { color: var(--gold-strong); }
    .cs .hero p.lead { max-width: 40rem; margin: 1.25rem auto 0; font-size: 1.02rem; line-height: 1.75; color: var(--muted); }

    /* ---------- Kartu pilihan (tab) ---------- */
    .cs .choose { background: linear-gradient(#faf7f0, #fff 70%); padding-bottom: 1rem; }
    .cs .choose-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    @media (min-width: 768px) { .cs .choose-grid { grid-template-columns: 1fr 1fr; gap: 1.5rem; } }
    .cs .choice {
        position: relative; display: flex; flex-direction: column; text-align: left; width: 100%;
        padding: 1.5rem; border-radius: 1.25rem; background: #fff; cursor: pointer;
        border: 2px solid var(--line); transition: border-color .25s, box-shadow .25s, transform .25s;
        font: inherit; color: inherit;
    }
    @media (min-width: 768px) { .cs .choice { padding: 1.75rem; } }
    .cs .choice:hover { transform: translateY(-3px); border-color: var(--accent-bright); box-shadow: 0 16px 32px -12px var(--glow); }
    .cs .choice[aria-selected="true"] { border-color: var(--accent-bright); box-shadow: 0 16px 36px -12px var(--glow); background: linear-gradient(180deg, var(--soft), #fff 55%); }
    .cs .choice .tick {
        position: absolute; top: 1rem; right: 1rem; width: 1.75rem; height: 1.75rem; border-radius: 9999px;
        display: none; align-items: center; justify-content: center; color: #fff; background: var(--accent);
    }
    .cs .choice[aria-selected="true"] .tick { display: flex; }
    .cs .choice .ico { width: 3.25rem; height: 3.25rem; border-radius: 1rem; background: var(--soft); color: var(--accent); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .cs .choice .no { font-size: .66rem; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; color: var(--accent); }
    .cs .choice ul { margin: 1rem 0; padding: 0; list-style: none; display: grid; gap: .5rem; }
    .cs .choice li { display: flex; align-items: center; gap: .6rem; font-size: .9rem; }
    .cs .choice li svg { color: var(--accent); flex-shrink: 0; }
    .cs .choice .cocok { margin-top: auto; padding: .7rem .9rem; border-radius: .75rem; background: var(--soft); font-size: .8rem; color: var(--muted); }
    .cs .choice .go { margin-top: 1rem; display: inline-flex; align-items: center; gap: .4rem; font-size: .88rem; font-weight: 700; color: var(--accent); }
    .cs .hint { margin-top: 1.25rem; text-align: center; font-size: .85rem; color: var(--muted); }
    .cs .hint a { color: var(--navy); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }

    /* ---------- Panel panduan ---------- */
    .cs .panel { padding: 3.5rem 0; background: var(--soft); }
    @media (min-width: 768px) { .cs .panel { padding: 5rem 0; } }
    .cs .panel[hidden] { display: none; }
    .cs.cs-js .panel:not([hidden]) { animation: cs-in .45s ease both; }
    @keyframes cs-in { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }

    .cs .badge { display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1.1rem; border-radius: 9999px; background: var(--accent); color: #fff; font-size: .68rem; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; }
    .cs .sub-head { text-align: center; margin: 4rem 0 2rem; }
    .cs .sub-head h3 { font-size: 1.6rem; font-weight: 700; }
    .cs .sub-head p { margin-top: .4rem; font-size: .9rem; color: var(--muted); }

    /* Langkah-langkah */
    .cs ol.steps { list-style: none; margin: 0; padding: 0; display: grid; gap: 0; grid-template-columns: 1fr; }
    @media (min-width: 900px) { .cs ol.steps { grid-template-columns: repeat(5, 1fr); gap: 1rem; } }
    .cs .step { position: relative; display: flex; gap: 1rem; padding-bottom: 1.75rem; }
    .cs .step:last-child { padding-bottom: 0; }
    .cs .step .num {
        position: relative; z-index: 1; flex-shrink: 0; width: 2.75rem; height: 2.75rem; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.05rem;
        color: #fff; background: var(--accent); box-shadow: 0 6px 16px -4px var(--glow); transition: transform .25s;
    }
    .cs .step:hover .num { transform: scale(1.1); }
    .cs .step h4 { font-size: 1.05rem; font-weight: 700; margin: .1rem 0 .35rem; }
    .cs .step p { font-size: .86rem; line-height: 1.65; color: var(--muted); }
    /* garis penghubung: vertikal di HP, horizontal di desktop */
    .cs .step:not(:last-child)::after { content: ''; position: absolute; left: 1.35rem; top: 2.75rem; bottom: 0; width: 2px; background: linear-gradient(var(--accent-bright), transparent); opacity: .45; }
    @media (min-width: 900px) {
        .cs .step { flex-direction: column; align-items: center; text-align: center; padding-bottom: 0; }
        .cs .step:not(:last-child)::after { left: calc(50% + 1.6rem); right: calc(-50% + 1.6rem - 1rem); top: 1.35rem; bottom: auto; width: auto; height: 2px; background: linear-gradient(90deg, var(--accent-bright), transparent); }
    }

    /* Kartu metode */
    .cs .method-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
    @media (min-width: 768px) { .cs .method-grid { grid-template-columns: 1fr 1fr; gap: 1.75rem; } }
    .cs .card {
        display: flex; flex-direction: column; background: #fff; border: 1px solid var(--line); border-radius: 1.1rem;
        padding: 1.5rem; transition: transform .3s, box-shadow .3s, border-color .3s;
    }
    @media (min-width: 768px) { .cs .card { padding: 2rem; } }
    .cs .card:hover { transform: translateY(-4px); border-color: var(--accent-bright); box-shadow: 0 18px 36px -14px var(--glow); }
    .cs .card-top { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .cs .card-top .ico { width: 3.5rem; height: 3.5rem; border-radius: 9999px; background: var(--soft); color: var(--accent); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .cs .card-top .lbl { font-size: .66rem; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; color: var(--accent); }
    .cs .card-top h4 { font-size: 1.4rem; font-weight: 700; line-height: 1.2; }
    .cs .card-top small { display: block; margin-top: .2rem; font-size: .78rem; color: var(--muted); }
    .cs .points { list-style: none; margin: 0 0 1.5rem; padding: 0; display: grid; gap: 1rem; }
    .cs .points li { display: flex; gap: .75rem; }
    .cs .points li svg { color: var(--accent); flex-shrink: 0; margin-top: .15rem; }
    .cs .points b { display: block; font-size: .9rem; font-weight: 600; }
    .cs .points span { display: block; font-size: .8rem; line-height: 1.55; color: var(--muted); }
    .cs .note { margin-top: auto; margin-bottom: 1.25rem; padding: 1rem; border-radius: .85rem; background: var(--soft); border: 1px solid var(--glow); }
    .cs .note .t { display: flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 700; }
    .cs .note .t svg { color: var(--accent); }
    .cs .note p { margin-top: .25rem; font-size: .8rem; line-height: 1.55; color: var(--muted); }
    .cs .note .hl { margin-top: .6rem; display: flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 700; color: var(--accent); }

    /* Tombol */
    .cs .btn {
        position: relative; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
        min-height: 3rem; padding: .75rem 1.5rem; border-radius: .75rem; border: 0; cursor: pointer;
        font: inherit; font-size: .82rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
        color: #fff; background: var(--accent); transition: transform .2s, box-shadow .3s;
    }
    .cs .btn:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px var(--glow); }
    .cs .btn:active { transform: scale(.98); }
    .cs .btn.block { display: flex; width: 100%; }
    .cs .btn.light { background: #fff; color: var(--navy); }
    .cs .btn.gold  { background: var(--gold-strong); }
    .cs .btn::before {
        content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; transform: skewX(-25deg);
        background: linear-gradient(to right, transparent, rgba(255,255,255,.3), transparent); transition: left .7s;
    }
    .cs .btn:hover::before { left: 150%; }

    /* Kebijakan */
    .cs .policy-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
    @media (min-width: 768px) { .cs .policy-grid { grid-template-columns: repeat(3, 1fr); } }
    .cs .policy { padding: 1.5rem; }
    .cs .policy .ico { width: 2.9rem; height: 2.9rem; border-radius: .8rem; margin-bottom: 1.1rem; background: var(--soft); color: var(--accent); display: flex; align-items: center; justify-content: center; }
    .cs .policy h4 { font-size: 1.1rem; font-weight: 700; margin-bottom: .5rem; }
    .cs .policy p { font-size: .86rem; line-height: 1.7; color: var(--muted); }

    .cs .switch { margin-top: 3rem; text-align: center; }
    .cs .switch button { background: none; border: 0; cursor: pointer; font: inherit; font-size: .9rem; font-weight: 600; color: var(--accent); display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .75rem; }
    .cs .switch button:hover { text-decoration: underline; text-underline-offset: 4px; }

    /* ---------- Tabel perbandingan ---------- */
    .cs .compare { background: #fff; }
    .cs table.cmp { width: 100%; border-collapse: separate; border-spacing: 0; border-radius: 1rem; overflow: hidden; background: #fff; box-shadow: 0 10px 30px -12px rgba(0,0,0,.15); border: 1px solid var(--line); }
    .cs .cmp th, .cs .cmp td { padding: 1rem 1.4rem; text-align: left; vertical-align: top; border-bottom: 1px solid var(--line); }
    .cs .cmp thead th { font-size: .8rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #fff; }
    .cs .cmp thead th:nth-child(1) { background: var(--navy); }
    .cs .cmp thead th:nth-child(2) { background: var(--gold-strong); }
    .cs .cmp thead th:nth-child(3) { background: var(--navy); }
    .cs .cmp tbody tr:last-child th, .cs .cmp tbody tr:last-child td { border-bottom: 0; }
    .cs .cmp tbody th { width: 22%; font-size: .88rem; font-weight: 600; background: #f9fafb; color: var(--navy); }
    .cs .cmp tbody td { font-size: .88rem; line-height: 1.55; color: #3f4756; }
    .cs .cmp tbody td:nth-child(2) { border-left: 3px solid rgba(201,154,50,.45); }
    .cs .cmp tbody td:nth-child(3) { border-left: 3px solid rgba(21,35,61,.3); }
    .cs .cmp tbody tr:hover td { background: #fcfbf7; }
    @media (max-width: 640px) {
        /* HP: tiap baris jadi kartu kecil, tanpa scroll samping */
        .cs table.cmp, .cs .cmp tbody, .cs .cmp tr, .cs .cmp th, .cs .cmp td { display: block; width: 100%; }
        .cs .cmp thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
        .cs table.cmp { border: 0; box-shadow: none; background: transparent; }
        .cs .cmp tbody tr { margin-bottom: 1rem; border: 1px solid var(--line); border-radius: .9rem; overflow: hidden; background: #fff; }
        .cs .cmp tbody th { width: 100%; padding: .8rem 1rem; background: var(--navy); color: #fff; border-bottom: 0; }
        .cs .cmp tbody td { padding: .75rem 1rem; border-left: 0 !important; display: grid; grid-template-columns: 4.5rem 1fr; gap: .75rem; }
        .cs .cmp tbody td::before { content: attr(data-label); font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .cs .cmp tbody td:nth-child(2)::before { color: var(--gold-strong); }
        .cs .cmp tbody td:nth-child(3)::before { color: var(--navy); }
        .cs .cmp tbody td:nth-child(2) { border-bottom: 1px solid var(--line); }
    }

    /* ---------- CTA penutup (gaya sama dengan halaman Tentang) ---------- */
    /* Tidak ada pita spacer polos di atas footer: ruang siger disediakan padding bawah CTA */
    #footer-spacer { display: none; }

    .cs {
        --tumpal: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='12'%3E%3Cpath d='M14 1.5 L23 6 L14 10.5 L5 6Z' fill='none' stroke='%23c99a32' stroke-width='1.2'/%3E%3Cpath d='M14 4.2 L17.4 6 L14 7.8 L10.6 6Z' fill='%23c99a32'/%3E%3C/svg%3E");
    }
    .cs .cta {
        position: relative; overflow: hidden; isolation: isolate; text-align: center; color: #fff;
        padding: 5rem 0 13rem;
        background:
            radial-gradient(circle at top left, rgba(201,154,50,.18) 0%, transparent 45%),
            radial-gradient(ellipse 55% 45% at 50% 100%, rgba(201,154,50,.16) 0%, transparent 70%),
            radial-gradient(circle at bottom right, rgba(201,154,50,.12) 0%, transparent 45%),
            #0a0a0a;
    }
    @media (min-width: 768px) { .cs .cta { padding: 8rem 0 16rem; } }
    /* pita tumpal emas di tepi atas */
    .cs .cta::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; z-index: 2; height: 14px; pointer-events: none;
        border-top: 1px solid rgba(201,154,50,.7); border-bottom: 1px solid #c99a32;
        background: var(--tumpal) repeat-x center / 28px 12px, #17140f;
    }
    .cs .cta .wrap { position: relative; z-index: 2; max-width: 56rem; }
    .cs .cta .eyebrow-c { display: inline-block; font-size: .62rem; font-weight: 700; letter-spacing: .28em; text-transform: uppercase; color: var(--gold); }
    .cs .cta h2 { font-size: clamp(1.7rem, 4.2vw, 3rem); font-weight: 700; line-height: 1.2; margin: .6rem 0 1rem; color: #fff; }
    .cs .cta h2 span { color: var(--gold); }
    .cs .cta .rule { margin: 0 auto 1.5rem; background: linear-gradient(90deg, transparent, var(--gold), transparent); }
    .cs .cta p.lead { max-width: 38rem; margin: 0 auto 2.25rem; font-size: .95rem; line-height: 1.75; font-weight: 300; color: #d1d5db; }

    /* Debu emas berkilau naik lalu memudar */
    .cs .dust-wrap { position: absolute; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }
    .cs .dust {
        position: absolute; left: var(--x); bottom: var(--y); width: var(--s); height: var(--s); border-radius: 50%;
        background: #e6c877; box-shadow: 0 0 6px 1px rgba(230,200,119,.4);
        opacity: 0; animation: cs-dust var(--t) linear infinite; animation-delay: var(--d);
    }
    @keyframes cs-dust {
        0%   { transform: translate(0, 0); opacity: 0; }
        10%  { opacity: .6; }
        90%  { opacity: .6; }
        100% { transform: translate(20px, -140px); opacity: 0; }
    }

    /* Tombol utama: emas bercahaya, berdenyut, panah bergerak */
    .cs .btn-cta {
        position: relative; display: inline-flex; align-items: center; justify-content: center; gap: .75rem;
        padding: 1.15rem 2.6rem; border-radius: 9999px; overflow: visible;
        font-size: 1.02rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
        color: var(--navy); background: linear-gradient(135deg, #f3dc94 0%, #e0b84e 45%, #c99a32 100%);
        box-shadow: 0 10px 30px -6px rgba(201,154,50,.65), inset 0 1px 0 rgba(255,255,255,.55);
        transition: transform .25s, box-shadow .25s;
        animation: cta-float 3.2s ease-in-out infinite;
    }
    .cs .btn-cta::before { display: none; }
    /* cincin denyut */
    .cs .btn-cta::after {
        content: ''; position: absolute; inset: 0; border-radius: inherit; pointer-events: none;
        box-shadow: 0 0 0 0 rgba(230,200,119,.7); animation: cta-ring 2.2s ease-out infinite;
    }
    .cs .btn-cta .shine { position: absolute; inset: 0; border-radius: inherit; overflow: hidden; pointer-events: none; }
    .cs .btn-cta .shine::before {
        content: ''; position: absolute; top: 0; left: -80%; width: 45%; height: 100%; transform: skewX(-25deg);
        background: linear-gradient(to right, transparent, rgba(255,255,255,.65), transparent);
        animation: cta-shine 3.4s ease-in-out infinite;
    }
    .cs .btn-cta .arrow {
        display: inline-flex; width: 2rem; height: 2rem; border-radius: 9999px; align-items: center; justify-content: center;
        background: var(--navy); color: #f3dc94; animation: cta-nudge 1.4s ease-in-out infinite;
    }
    .cs .btn-cta:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 16px 40px -6px rgba(230,200,119,.85), inset 0 1px 0 rgba(255,255,255,.6); animation: none; }
    .cs .btn-cta:active { transform: scale(.97); }
    .cs .cta .perk { margin-top: 1.25rem; margin-bottom: 0; font-size: .82rem; color: #aab4c9; }
    .cs .cta .perk span { color: #e6c877; padding: 0 .4rem; }
    @keyframes cta-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
    @keyframes cta-ring  { 0% { box-shadow: 0 0 0 0 rgba(230,200,119,.65); } 80%, 100% { box-shadow: 0 0 0 22px rgba(230,200,119,0); } }
    @keyframes cta-shine { 0%, 55% { left: -80%; } 100% { left: 160%; } }
    @keyframes cta-nudge { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(5px); } }
    @media (max-width: 480px) { .cs .btn-cta { width: 100%; padding: 1.1rem 1.5rem; } }

    /* ---------- Animasi muncul (hanya aktif bila JS jalan) ---------- */
    .cs.cs-js .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; transition-delay: var(--d, 0ms); }
    .cs.cs-js .reveal.on { opacity: 1; transform: none; }
    @media (prefers-reduced-motion: reduce) {
        .cs *, .cs *::before, .cs *::after { animation: none !important; transition: none !important; }
        .cs.cs-js .reveal { opacity: 1; transform: none; }
    }
</style>

<div class="cs" id="cara-sewa">

{{-- ============================================================
     1. HERO
     ============================================================ --}}
<section class="hero">
    <div class="wrap">
        <span class="eyebrow reveal">Panduan Lengkap</span>
        <h1 class="font-display reveal" style="--d:60ms">Sewa atau Beli di <span>ALNAY.ID</span>?</h1>
        <div class="rule reveal" style="--d:100ms"></div>
        <p class="lead reveal" style="--d:140ms">
            Ada dua cara menikmati koleksi ALNAY: <strong style="color:var(--gold-strong)">SEWA</strong> untuk acara spesial,
            atau <strong>BELI</strong> untuk koleksi pribadi. Pilih salah satu di bawah untuk melihat panduannya.
        </p>
    </div>
</section>


{{-- ============================================================
     2. PILIH LAYANAN (sekaligus tab)
     ============================================================ --}}
<section class="choose">
    <div class="wrap">
        <div class="choose-grid" role="tablist" aria-label="Pilih panduan">
            @foreach ($pilihan as $key => $c)
                <div role="tab" tabindex="{{ $loop->first ? 0 : -1 }}" id="tab-{{ $key }}" data-tab="{{ $key }}"
                        aria-controls="section-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        class="choice tema-{{ $key }} reveal" style="--d: {{ $loop->index * 100 }}ms">
                    <span class="tick">{!! $ic('check', 'w-4 h-4', 3) !!}</span>

                    <span style="display:flex; align-items:center; gap:1rem;">
                        <span class="ico">{!! $ic($c['ikon'], 'w-7 h-7') !!}</span>
                        <span>
                            <span class="no" style="display:block;">{{ $c['nomor'] }}</span>
                            <span class="font-display" style="display:block; font-size:1.5rem; font-weight:700;">{{ $c['judul'] }}</span>
                        </span>
                    </span>

                    <span style="display:block; margin-top:1rem; font-size:.9rem; line-height:1.65; color:var(--muted);">{{ $c['desk'] }}</span>

                    <ul>
                        @foreach ($c['poin'] as $poin)
                            <li>{!! $ic('check', 'w-4 h-4', 2.5) !!}<span>{!! $poin !!}</span></li>
                        @endforeach
                    </ul>

                    <span class="cocok"><strong style="color:var(--navy)">Cocok untuk:</strong> {{ $c['cocok'] }}</span>
                    <span class="go">{{ $c['cta'] }} {!! $ic('down', 'w-4 h-4') !!}</span>
                </div>
            @endforeach
        </div>

        <p class="hint reveal">Masih ragu? <a href="#section-compare">Bandingkan sewa dan beli</a> dalam satu tabel.</p>
    </div>
</section>


{{-- ============================================================
     3. PANDUAN (satu template untuk Sewa & Beli)
     ============================================================ --}}
@foreach ($panels as $key => $p)
<section id="section-{{ $key }}" role="tabpanel" aria-labelledby="tab-{{ $key }}"
         class="panel tema-{{ $p['tema'] }}" data-panel="{{ $key }}">
    <div class="wrap">

        {{-- Judul --}}
        <div class="sec-head" style="margin-bottom:3rem">
            <span class="badge reveal">{!! $ic($key === 'sewa' ? 'calendar' : 'cart', 'w-4 h-4') !!} {{ $p['badge'] }}</span>
            <h2 class="font-display reveal" style="margin-top:1.25rem; --d:60ms">{{ $p['judul'] }}</h2>
            <div class="rule reveal" style="--d:100ms"></div>
            <p class="reveal" style="--d:140ms">{{ $p['intro'] }}</p>
        </div>

        {{-- Langkah --}}
        <ol class="steps">
            @foreach ($p['steps'] as $i => $s)
                <li class="step reveal" style="--d: {{ $i * 80 }}ms">
                    <span class="num">{{ $i + 1 }}</span>
                    <div>
                        <h4 class="font-display">{{ $s[0] }}</h4>
                        <p>{{ $s[1] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        {{-- Metode --}}
        <div class="sub-head reveal">
            <h3 class="font-display">{{ $p['metode_judul'] }}</h3>
            <p>Pilih yang paling nyaman untuk Anda</p>
        </div>

        <div class="method-grid">
            @foreach ($p['metode'] as $m)
                <article class="card reveal" style="--d: {{ $loop->index * 120 }}ms">
                    <div class="card-top">
                        <span class="ico">{!! $ic($m['ikon'], 'w-7 h-7') !!}</span>
                        <div>
                            <p class="lbl">{{ $m['label'] }}</p>
                            <h4 class="font-display">{{ $m['judul'] }}</h4>
                            <small>{{ $m['sub'] }}</small>
                        </div>
                    </div>

                    <ul class="points">
                        @foreach ($m['poin'] as $poin)
                            <li>
                                {!! $ic('check', 'w-5 h-5') !!}
                                <div><b>{{ $poin[0] }}</b><span>{{ $poin[1] }}</span></div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="note">
                        <p class="t">{!! $ic($m['catatan']['ikon'], 'w-4 h-4') !!} {{ $m['catatan']['judul'] }}:</p>
                        <p>{{ $m['catatan']['teks'] }}</p>
                        @if ($m['catatan']['sorot'])
                            <p class="hl">{!! $ic($m['catatan']['sorot'][0], 'w-4 h-4') !!} {{ $m['catatan']['sorot'][1] }}</p>
                        @endif
                    </div>

                    <a href="{{ $m['cta'][0] }}" class="btn block">
                        {!! $ic($m['cta'][2], 'w-4 h-4') !!} {{ $m['cta'][1] }}
                    </a>
                </article>
            @endforeach
        </div>

        {{-- Kebijakan --}}
        <div class="sub-head reveal">
            <h3 class="font-display">{{ $p['kebijakan_judul'] }}</h3>
            <p>{{ $p['kebijakan_sub'] }}</p>
        </div>

        <div class="policy-grid">
            @foreach ($p['kebijakan'] as $k)
                <div class="card policy reveal" style="--d: {{ $loop->index * 100 }}ms">
                    <span class="ico">{!! $ic($k[0], 'w-6 h-6') !!}</span>
                    <h4 class="font-display">{{ $k[1] }}</h4>
                    <p>{{ $k[2] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Pindah ke panduan lainnya --}}
        <div class="switch">
            <button type="button" data-switch="{{ $p['silang'][0] }}">
                {{ $p['silang'][1] }} {!! $ic('arrow', 'w-4 h-4') !!}
            </button>
        </div>

    </div>
</section>
@endforeach


{{-- ============================================================
     4. PERBANDINGAN
     ============================================================ --}}
<section id="section-compare" class="sec compare">
    <div class="wrap">
        <div class="sec-head reveal">
            <span style="font-size:.68rem; font-weight:700; letter-spacing:.28em; text-transform:uppercase; color:var(--muted);">Sewa vs Beli</span>
            <h2 class="font-display" style="margin-top:.6rem">Perbedaan Kunci</h2>
            <div class="rule"></div>
            <p>Masih ragu mau sewa atau beli? Bandingkan layanan dalam satu tabel singkat ini.</p>
        </div>

        <div class="reveal">
            <table class="cmp">
                <thead>
                    <tr>
                        <th scope="col">Aspek</th>
                        <th scope="col">Sewa</th>
                        <th scope="col">Beli</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($banding as $b)
                        <tr>
                            <th scope="row">{{ $b[0] }}</th>
                            <td data-label="Sewa">{{ $b[1] }}</td>
                            <td data-label="Beli">{{ $b[2] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>


{{-- ============================================================
     5. CTA PENUTUP
     ============================================================ --}}
<section class="cta">
    {{-- Debu emas berkilau (CSS murni) --}}
    <div class="dust-wrap" aria-hidden="true">
        @for ($i = 0; $i < 22; $i++)
            <span class="dust" style="--x: {{ ($i * 37 + 9) % 92 + 3 }}%; --y: {{ ($i * 23 + 4) % 52 + 4 }}%; --s: {{ [4, 6, 4, 8, 5][$i % 5] }}px; --t: {{ 6 + ($i * 7) % 6 }}s; --d: {{ round((($i * 13) % 40) / 10, 1) }}s;"></span>
        @endfor
    </div>

    <div class="wrap">
        <span class="eyebrow-c reveal">Mulai Sekarang</span>
        <h2 class="font-display reveal" style="--d:60ms">Siap Tampil <span>Berbudaya &amp; Berkesan?</span></h2>
        <div class="rule reveal" style="--d:100ms"></div>

        <div class="reveal" style="--d:200ms">
            <a href="{{ route('koleksi.index') }}" class="btn-cta">
                <span class="shine"></span>
                Lihat Koleksi
                <span class="arrow">{!! $ic('arrow', 'w-4 h-4', 3) !!}</span>
            </a>
        </div>
    </div>
</section>

<script>
(function () {
    var root = document.getElementById('cara-sewa');
    if (!root) return;
    root.classList.add('cs-js');

    var tabs   = [].slice.call(root.querySelectorAll('[role="tab"]'));
    var panels = [].slice.call(root.querySelectorAll('[data-panel]'));

    // Terima hash lama (#section-sewa) maupun singkat (#sewa)
    function keyDariHash() {
        var h = (location.hash || '').replace('#', '').replace('section-', '');
        return (h === 'sewa' || h === 'beli') ? h : null;
    }

    function aktifkan(key, opsi) {
        opsi = opsi || {};
        tabs.forEach(function (t) {
            var on = t.getAttribute('data-tab') === key;
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            t.tabIndex = on ? 0 : -1;
        });
        panels.forEach(function (p) {
            p.hidden = p.getAttribute('data-panel') !== key;
        });
        if (opsi.hash !== false && history.replaceState) {
            history.replaceState(null, '', '#section-' + key);
        }
        if (opsi.gulir) {
            var el = document.getElementById('section-' + key);
            if (el) el.scrollIntoView({ behavior: opsi.instan ? 'auto' : 'smooth', block: 'start' });
        }
    }

    tabs.forEach(function (t, i) {
        t.addEventListener('click', function () { aktifkan(t.getAttribute('data-tab'), { gulir: true }); });
        t.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                aktifkan(t.getAttribute('data-tab'), { gulir: true });
                return;
            }
            // Panah kiri/kanan untuk berpindah tab
            if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
            var n = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
            n.focus();
            aktifkan(n.getAttribute('data-tab'), { hash: false });
        });
    });

    [].forEach.call(root.querySelectorAll('[data-switch]'), function (b) {
        b.addEventListener('click', function () { aktifkan(b.getAttribute('data-switch'), { gulir: true }); });
    });

    window.addEventListener('hashchange', function () {
        var k = keyDariHash();
        if (k) aktifkan(k, { hash: false, gulir: true });
    });

    // Keadaan awal: ikuti hash bila ada, kalau tidak tampilkan panduan Sewa
    var awal = keyDariHash();
    aktifkan(awal || 'sewa', { hash: false, gulir: !!awal, instan: true });

    // Animasi muncul saat discroll
    var items = [].slice.call(root.querySelectorAll('.reveal'));
    if (!('IntersectionObserver' in window)) {
        items.forEach(function (el) { el.classList.add('on'); });
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (en.isIntersecting) { en.target.classList.add('on'); io.unobserve(en.target); }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    items.forEach(function (el) { io.observe(el); });
})();
</script>
</div>

@endsection