@extends('layouts.public')

@section('title', 'Checkout - ALNAY.ID')

@section('content')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #faf9f6;
        }

        .ck-rise {
            animation: ckRise .5s cubic-bezier(.22, 1, .36, 1) both;
            animation-delay: var(--d, 0s);
        }

        @keyframes ckRise {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        /* Custom Radio & Card Selection */
        .opsi-radio:checked+.opsi-card {
            border-color: #c99a32;
            background-color: rgba(201, 154, 50, 0.04);
            box-shadow: 0 4px 14px -4px rgba(201, 154, 50, 0.15);
        }

        .opsi-radio:checked+.opsi-card .radio-dot {
            background: #c99a32;
            border-color: #c99a32;
            box-shadow: inset 0 0 0 2.5px #fff;
        }

        .opsi-radio:focus-visible+.opsi-card {
            outline: 2px solid #c99a32;
            outline-offset: 2px;
        }

        .opsi-radio:disabled+.opsi-card {
            opacity: .45;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ===== Alur Checkout (Stepper) ===== */
        .ck-step .ck-dot {
            background: #fff;
            border: 2px solid #e5e7eb;
            color: #9ca3af;
            transition: all .3s ease;
        }

        .ck-step .ck-label {
            color: #9ca3af;
            transition: color .3s ease;
        }

        .ck-step .ck-num {
            display: inline;
        }

        .ck-step .ck-check {
            display: none;
        }

        .ck-step[data-state="active"] .ck-dot {
            border-color: #c99a32;
            background: #fffdf8;
            color: #c99a32;
            box-shadow: 0 0 0 4px rgba(201, 154, 50, .15);
        }

        .ck-step[data-state="active"] .ck-label {
            color: #15233d;
            font-weight: 700;
        }

        .ck-step[data-state="done"] .ck-dot {
            background: #c99a32;
            border-color: #c99a32;
            color: #fff;
        }

        .ck-step[data-state="done"] .ck-label {
            color: #15233d;
        }

        .ck-step[data-state="done"] .ck-num {
            display: none;
        }

        .ck-step[data-state="done"] .ck-check {
            display: inline;
        }

        .ck-line {
            background: #e5e7eb;
            position: relative;
            overflow: hidden;
        }

        .ck-line::after {
            content: '';
            position: absolute;
            inset: 0;
            background: #c99a32;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .45s ease;
        }

        .ck-line[data-full="1"]::after {
            transform: scaleX(1);
        }

        .ck-sec {
            scroll-margin-top: 6.5rem;
            transition: border-color .3s ease, box-shadow .3s ease;
        }

        .ck-sec:focus-within {
            border-color: rgba(201, 154, 50, 0.45);
        }

        @media (prefers-reduced-motion: reduce) {
            .ck-rise {
                animation: none;
            }

            .ck-step .ck-dot,
            .ck-line::after {
                transition: none;
            }
        }
    </style>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-8 md:py-10 pb-36 lg:pb-12">

        {{-- Header & Breadcrumb --}}
        <div class="ck-rise mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-[#15233d] transition-colors mb-2">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
                <h1 class="font-display text-3xl md:text-4xl font-bold tracking-tight" style="color: #15233d;">Checkout
                    Pesanan</h1>
                <p class="mt-1 text-sm text-gray-500">Ikuti 4 langkah mudah di bawah untuk menyelesaikan pesanan Anda.</p>
            </div>
            <div
                class="inline-flex items-center gap-2 self-start sm:self-auto rounded-full bg-white border border-gray-200/80 px-3.5 py-1.5 text-xs text-gray-600 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
                <span>Transaksi Aman &amp; Terenkripsi</span>
            </div>
        </div>

        {{-- Alur Checkout (4 Langkah) --}}
        <nav aria-label="Alur checkout"
            class="ck-rise mb-8 bg-white rounded-2xl border border-gray-200/70 shadow-2xs px-3 sm:px-8 py-4"
            style="--d: .03s;">
            <ol class="flex items-start justify-between">
                <li class="ck-step flex flex-col items-center text-center w-16 sm:w-28 shrink-0" id="stp-1"
                    data-state="active">
                    <a href="#sec-pemesan" class="group flex flex-col items-center gap-1.5">
                        <span class="ck-dot w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                            <span class="ck-num">1</span>
                            <svg class="ck-check w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="ck-label text-[11px] sm:text-xs font-semibold leading-tight">Data Pemesan</span>
                    </a>
                </li>

                <li class="ck-line flex-1 h-0.5 mt-4 sm:mt-[18px] rounded-full" id="ln-1" aria-hidden="true"></li>

                <li class="ck-step flex flex-col items-center text-center w-16 sm:w-28 shrink-0" id="stp-2"
                    data-state="pending">
                    <a href="#sec-penerimaan" class="group flex flex-col items-center gap-1.5">
                        <span class="ck-dot w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                            <span class="ck-num">2</span>
                            <svg class="ck-check w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="ck-label text-[11px] sm:text-xs font-semibold leading-tight">Penerimaan</span>
                    </a>
                </li>

                <li class="ck-line flex-1 h-0.5 mt-4 sm:mt-[18px] rounded-full" id="ln-2" aria-hidden="true"></li>

                <li class="ck-step flex flex-col items-center text-center w-16 sm:w-28 shrink-0" id="stp-3"
                    data-state="pending">
                    <a href="#sec-pembayaran" class="group flex flex-col items-center gap-1.5">
                        <span class="ck-dot w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                            <span class="ck-num">3</span>
                            <svg class="ck-check w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="ck-label text-[11px] sm:text-xs font-semibold leading-tight">Pembayaran</span>
                    </a>
                </li>

                <li class="ck-line flex-1 h-0.5 mt-4 sm:mt-[18px] rounded-full" id="ln-3" aria-hidden="true"></li>

                <li class="ck-step flex flex-col items-center text-center w-16 sm:w-28 shrink-0" id="stp-4"
                    data-state="pending">
                    <a href="#sec-konfirmasi" class="group flex flex-col items-center gap-1.5">
                        <span class="ck-dot w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                            <span class="ck-num">4</span>
                            <svg class="ck-check w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="ck-label text-[11px] sm:text-xs font-semibold leading-tight">Konfirmasi</span>
                    </a>
                </li>
            </ol>
        </nav>

        {{-- Alert Error --}}
        @if (session('error'))
            <div
                class="mb-6 flex items-start gap-3 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3.5 text-sm text-rose-700">
                <svg class="w-5 h-5 shrink-0 text-rose-500 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3.5 text-sm text-rose-700 space-y-1">
                <p class="font-semibold">Mohon periksa kembali data berikut:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="form-checkout" action="{{ route('checkout.store') }}" method="POST"
            class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf

            {{-- KOLOM KIRI (7 Kolom): Langkah 1 - 3 --}}
            <div class="lg:col-span-7 xl:col-span-8 space-y-6">

                {{-- ==========================================================
                     LANGKAH 1: DATA PEMESAN / PENYEWA
                ========================================================== --}}
                <div id="sec-pemesan"
                    class="ck-sec ck-rise bg-white rounded-2xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs"
                    style="--d: .05s;">
                    <div class="flex items-start justify-between gap-3 mb-5">
                        <div class="flex items-start gap-3.5">
                            <span
                                class="w-7 h-7 rounded-full bg-[#15233d] text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900">Data Pemesan / Penyewa</h2>
                                <p class="text-xs text-gray-500 mt-0.5">Digunakan untuk konfirmasi pesanan &amp; update
                                    status via WhatsApp.</p>
                            </div>
                        </div>
                        <span id="badge-step-1"
                            class="hidden sm:inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                            ✓ Lengkap
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="pemesan-nama" class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="pemesan-nama" name="nama_pemesan"
                                value="{{ old('nama_pemesan', auth()->user()->name ?? '') }}" required
                                placeholder="Masukkan nama lengkap Anda"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all">
                        </div>
                        <div>
                            <label for="pemesan-wa" class="block text-xs font-semibold text-gray-700 mb-1.5">
                                No. WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" id="pemesan-wa" name="no_wa_pemesan"
                                value="{{ old('no_wa_pemesan', auth()->user()->no_hp ?? '') }}" required
                                placeholder="Contoh: 081234567890"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all">
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-[11px] text-gray-400">Pastikan nomor WhatsApp aktif untuk menerima detail tagihan.</p>
                        <a href="#sec-penerimaan"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-[#c99a32] hover:text-[#b08324] transition-colors">
                            Lanjut ke Penerimaan
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- ==========================================================
                     LANGKAH 2: METODE PENERIMAAN & PENGIRIMAN
                ========================================================== --}}
                <div id="sec-penerimaan"
                    class="ck-sec ck-rise bg-white rounded-2xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs"
                    style="--d: .09s;">
                    <div class="flex items-start justify-between gap-3 mb-5">
                        <div class="flex items-start gap-3.5">
                            <span
                                class="w-7 h-7 rounded-full bg-[#15233d] text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900">Metode Penerimaan</h2>
                                <p class="text-xs text-gray-500 mt-0.5">Pilih apakah barang diambil langsung di toko atau
                                    dikirim ke alamat tujuan.</p>
                            </div>
                        </div>
                        <span id="badge-step-2"
                            class="hidden sm:inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                            ✓ Siap
                        </span>
                    </div>

                    {{-- Pilihan Ambil vs Kirim --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <label class="cursor-pointer block">
                            <input type="radio" name="metode_penerimaan" value="ambil" checked
                                class="opsi-radio peer sr-only">
                            <div
                                class="opsi-card h-full flex items-start gap-3.5 p-4 rounded-xl border border-gray-200 hover:border-[#c99a32]/60 transition-all duration-200">
                                <span
                                    class="radio-dot mt-1 w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-bold text-gray-800">Ambil di Toko</p>
                                        <span
                                            class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Gratis</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Jl. Terusan Rajabasa No.11,
                                        Bandar Lampung</p>
                                    <p class="text-[11px] text-gray-400 mt-1.5">Bisa bayar tunai di kasir atau non-tunai
                                    </p>
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer block">
                            <input type="radio" name="metode_penerimaan" value="kirim" class="opsi-radio peer sr-only">
                            <div
                                class="opsi-card h-full flex items-start gap-3.5 p-4 rounded-xl border border-gray-200 hover:border-[#c99a32]/60 transition-all duration-200">
                                <span
                                    class="radio-dot mt-1 w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-bold text-gray-800">Kirim ke Alamat</p>
                                        <span
                                            class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">Kurir</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Dikirim via ekspedisi ke seluruh
                                        Indonesia.</p>
                                    <p class="text-[11px] text-gray-400 mt-1.5">Ongkir otomatis sesuai lokasi &amp; berat
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Panel Detail Pengiriman (Muncul saat pilih Kirim ke Alamat) --}}
                    <div id="alamat-container" class="mt-6 hidden space-y-6 border-t border-gray-100 pt-6">

                        @php
                            $alamatDefault = (string) old('alamat_id', $alamatList->first()?->id ?? 'baru');
                        @endphp

                        {{-- Sub-bagian A: Pilih Alamat --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">A. Alamat Tujuan
                                    Pengiriman</p>
                                <a href="{{ route('akun.dashboard') }}#alamat"
                                    class="text-xs font-semibold hover:underline" style="color: #c99a32;">Kelola di
                                    Profil</a>
                            </div>

                            <div id="list-alamat" class="space-y-2.5">
                                @foreach ($alamatList as $a)
                                    <label class="cursor-pointer block">
                                        <input type="radio" name="alamat_id" value="{{ $a->id }}"
                                            @checked($alamatDefault === (string) $a->id) class="opsi-radio peer sr-only">
                                        <div
                                            class="opsi-card flex items-start gap-3.5 p-4 rounded-xl border border-gray-200 hover:border-[#c99a32]/60 transition-all duration-200">
                                            <span
                                                class="radio-dot mt-1 w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <p class="text-sm font-bold text-gray-800">{{ $a->label ?: 'Alamat' }}
                                                    </p>
                                                    @if ($a->is_utama)
                                                        <span
                                                            class="badge-utama rounded-full bg-[#c99a32]/15 px-2 py-0.5 text-[10px] font-bold"
                                                            style="color: #c99a32;">Utama</span>
                                                    @endif
                                                </div>
                                                <p class="text-xs font-medium text-gray-700 mt-1">{{ $a->nama_penerima }}
                                                    &middot; {{ $a->no_hp }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $a->alamat }}
                                                </p>
                                                <p class="text-[11px] text-gray-400 mt-0.5">
                                                    {{ collect([$a->kecamatan, $a->kota_kabupaten, $a->provinsi, $a->kode_pos])->filter()->implode(', ') }}
                                                </p>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <p id="info-alamat-kosong"
                                class="{{ $alamatList->isEmpty() ? '' : 'hidden' }} rounded-xl bg-amber-50 border border-amber-200/80 px-3.5 py-2.5 text-xs text-amber-800">
                                Anda belum memiliki alamat tersimpan. Lengkapi form di bawah lalu klik <b>Simpan Alamat</b>
                                agar otomatis tersimpan untuk pesanan berikutnya.
                            </p>
                            <p id="pesan-alamat-sukses"
                                class="hidden rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2.5 text-xs text-emerald-700 font-medium">
                            </p>

                            <label id="opsi-alamat-baru" class="cursor-pointer block">
                                <input type="radio" name="alamat_id" value="baru" @checked($alamatDefault === 'baru')
                                    class="opsi-radio peer sr-only">
                                <div
                                    class="opsi-card flex items-center gap-3 p-3.5 rounded-xl border border-dashed border-gray-300 hover:border-[#c99a32] transition-all duration-200">
                                    <span
                                        class="radio-dot w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                    <p class="text-xs sm:text-sm font-semibold text-gray-700">+ Gunakan atau tambah alamat
                                        baru</p>
                                </div>
                            </label>
                        </div>

                        {{-- Form Input Alamat Baru --}}
                        <div data-manual
                            class="rounded-xl border border-gray-200/90 bg-gray-50/50 p-4 sm:p-5 space-y-4">
                            <p class="text-xs font-bold text-gray-700">Detail Alamat Penerima Baru</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Penerima</label>
                                    <input type="text" id="in-nama-penerima" name="nama_penerima"
                                        value="{{ old('nama_penerima', auth()->user()->name ?? '') }}" required
                                        class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">No. WhatsApp
                                        Penerima</label>
                                    <input type="text" id="in-wa-penerima" name="no_wa"
                                        value="{{ old('no_wa', auth()->user()->no_hp ?? '') }}" required
                                        class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all">
                                </div>
                            </div>

                            <fieldset class="space-y-4 min-w-0 border-0 p-0 m-0">
                                <div>
                                    <p class="block text-xs font-semibold text-gray-600 mb-1.5">Label Alamat</p>
                                    <div class="flex gap-2.5">
                                        @foreach (['Rumah', 'Kantor', 'Kos'] as $lbl)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="label" value="{{ $lbl }}"
                                                    class="peer sr-only" @checked(old('label', 'Rumah') === $lbl)>
                                                <span
                                                    class="inline-block bg-white px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-600 transition peer-checked:border-[#c99a32] peer-checked:text-[#c99a32] peer-checked:bg-[#c99a32]/10">{{ $lbl }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Provinsi</label>
                                        <select id="ck-provinsi" name="provinsi"
                                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all disabled:bg-gray-100 disabled:text-gray-400">
                                            <option value="">Memuat provinsi...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-semibold text-gray-500 mb-1">Kota/Kabupaten</label>
                                        <select id="ck-kota" name="kota_kabupaten" disabled
                                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all disabled:bg-gray-100 disabled:text-gray-400">
                                            <option value="">Pilih Kota/Kabupaten</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Kecamatan</label>
                                        <select id="ck-kecamatan" name="kecamatan" disabled
                                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all disabled:bg-gray-100 disabled:text-gray-400">
                                            <option value="">Pilih kota terlebih dahulu</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Kode Pos</label>
                                        <input type="text" id="ck-kodepos" name="kode_pos"
                                            value="{{ old('kode_pos') }}" readonly placeholder="Otomatis terisi"
                                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none">
                                    </div>
                                </div>
                            </fieldset>

                            <div class="relative">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Kelurahan / Area Ongkir
                                    <span class="text-rose-500">*</span></label>
                                <input type="text" id="q-tujuan" autocomplete="off"
                                    class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all"
                                    placeholder="Ketik minimal 3 huruf nama kelurahan/kecamatan...">
                                <div id="hasil-tujuan"
                                    class="hidden absolute z-30 left-0 right-0 mt-1 max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl">
                                </div>
                                <p id="info-tujuan" class="mt-1 text-xs text-emerald-600 font-medium"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Lengkap <span
                                        class="text-rose-500">*</span></label>
                                <textarea name="alamat" rows="2" required
                                    class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all"
                                    placeholder="Nama jalan, nomor rumah, RT/RW, patokan gedung/warung">{{ old('alamat', auth()->user()->alamat ?? '') }}</textarea>

                                <fieldset class="mt-3 pt-3 border-t border-gray-200/70 space-y-3 min-w-0 m-0">
                                    <div id="ck-box-utama">
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                            <input type="checkbox" id="ck-utama" name="jadikan_utama" value="1"
                                                @checked(old('jadikan_utama')) class="rounded accent-[#c99a32]">
                                            Jadikan sebagai alamat utama saya
                                        </label>
                                    </div>
                                    <p id="ck-info-pertama" class="text-xs text-gray-500">Alamat pertama Anda akan otomatis
                                        dijadikan alamat utama.</p>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <button type="button" id="btn-simpan-alamat"
                                            class="px-4 py-2 rounded-xl border border-[#c99a32] bg-white text-[#c99a32] text-xs font-bold transition-all duration-200 hover:bg-[#c99a32] hover:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                                            Simpan &amp; Gunakan Alamat Ini
                                        </button>
                                        <p id="pesan-simpan" class="text-xs text-rose-600 font-medium"></p>
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        {{-- Sub-bagian B: Pilih Layanan Kurir --}}
                        <div class="border-t border-gray-100 pt-5">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">B. Pilih Layanan
                                Pengiriman</p>
                            <div id="opsi-ongkir" class="space-y-2.5">
                                <div
                                    class="rounded-xl border border-dashed border-gray-200 bg-gray-50/60 px-4 py-3 text-xs text-gray-400">
                                    Pilih alamat atau kelurahan tujuan terlebih dahulu untuk melihat daftar ongkir.
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="tujuan_id" id="in-tujuan-id" value="">
                        <input type="hidden" name="tujuan_label" id="in-tujuan-label" value="">
                        <input type="hidden" name="kurir" id="in-kurir" value="">
                        <input type="hidden" name="layanan" id="in-layanan" value="">
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-end">
                        <a href="#sec-pembayaran"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-[#c99a32] hover:text-[#b08324] transition-colors">
                            Lanjut ke Pembayaran
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- ==========================================================
                     LANGKAH 3: METODE PEMBAYARAN
                ========================================================== --}}
                <div id="sec-pembayaran"
                    class="ck-sec ck-rise bg-white rounded-2xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs"
                    style="--d: .13s;">
                    <div class="flex items-start justify-between gap-3 mb-5">
                        <div class="flex items-start gap-3.5">
                            <span
                                class="w-7 h-7 rounded-full bg-[#15233d] text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900">Metode Pembayaran</h2>
                                <p class="text-xs text-gray-500 mt-0.5">Pilih metode transaksi yang paling memudahkan Anda.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="cursor-pointer block">
                            <input type="radio" name="metode_bayar" value="qris" checked
                                class="opsi-radio peer sr-only">
                            <div
                                class="opsi-card h-full flex items-start gap-3.5 p-4 rounded-xl border border-gray-200 hover:border-[#c99a32]/60 transition-all duration-200">
                                <span
                                    class="radio-dot mt-1 w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-bold text-gray-800">QRIS Instan</p>
                                        <span
                                            class="rounded bg-[#c99a32]/15 px-1.5 py-0.5 text-[10px] font-bold text-[#c99a32]">Populer</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Scan via m-Banking (BCA, Mandiri, BRI, BNI) atau
                                        e-Wallet apa saja.</p>
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer block">
                            <input type="radio" name="metode_bayar" value="va" class="opsi-radio peer sr-only">
                            <div
                                class="opsi-card h-full flex items-start gap-3.5 p-4 rounded-xl border border-gray-200 hover:border-[#c99a32]/60 transition-all duration-200">
                                <span
                                    class="radio-dot mt-1 w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-800">Virtual Account Bank</p>
                                    <p class="text-xs text-gray-500 mt-1">Verifikasi otomatis tanpa perlu upload bukti
                                        transfer.</p>
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer block">
                            <input type="radio" name="metode_bayar" value="ewallet" class="opsi-radio peer sr-only">
                            <div
                                class="opsi-card h-full flex items-start gap-3.5 p-4 rounded-xl border border-gray-200 hover:border-[#c99a32]/60 transition-all duration-200">
                                <span
                                    class="radio-dot mt-1 w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-800">E-Wallet</p>
                                    <p class="text-xs text-gray-500 mt-1">GoPay, OVO, ShopeePay, dan DANA.</p>
                                </div>
                            </div>
                        </label>

                        {{-- Tunai: hanya untuk Ambil di Toko --}}
                        <label id="opsi-tunai" class="cursor-pointer block hidden">
                            <input type="radio" name="metode_bayar" value="tunai" disabled
                                class="opsi-radio peer sr-only">
                            <div
                                class="opsi-card h-full flex items-start gap-3.5 p-4 rounded-xl border border-gray-200 hover:border-[#c99a32]/60 transition-all duration-200">
                                <span
                                    class="radio-dot mt-1 w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-800">Tunai di Toko</p>
                                    <p class="text-xs text-gray-500 mt-1">Bayar langsung di kasir saat mengambil pesanan.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Catatan Opsional --}}
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <label for="catatan-pesanan" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Catatan Pesanan / Jadwal Pengambilan <span class="font-normal text-gray-400">(Opsional)</span>
                        </label>
                        <input type="text" id="catatan-pesanan" name="catatan" value="{{ old('catatan') }}"
                            placeholder="Contoh: Diambil besok jam 14.00 / Tolong bungkus rapi untuk kado"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all">
                    </div>

                    <div
                        class="mt-4 flex items-start gap-2.5 rounded-xl bg-amber-50/70 border border-amber-200/60 px-3.5 py-3 text-xs text-amber-900/80">
                        <svg class="w-4 h-4 shrink-0 text-[#c99a32] mt-0.5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 8h.01M11 12h1v4h1" />
                        </svg>
                        <span>Stok item langsung dikunci setelah pesanan dibuat. Harap selesaikan pembayaran sebelum batas
                            waktu berakhir agar pesanan tidak terbatalkan otomatis.</span>
                    </div>
                </div>
            </div>

            {{-- ==========================================================
                 KOLOM KANAN (5 Kolom): LANGKAH 4 - RINGKASAN & KONFIRMASI
            ========================================================== --}}
            <div class="lg:col-span-5 xl:col-span-4">
                <div id="sec-konfirmasi"
                    class="ck-sec ck-rise bg-white rounded-2xl border border-gray-200/80 p-5 sm:p-6 shadow-sm sticky top-24"
                    style="--d: .16s;">
                    <div class="flex items-center justify-between gap-2 pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-7 h-7 rounded-full bg-[#c99a32] text-white flex items-center justify-center text-xs font-bold shrink-0">4</span>
                            <h2 class="font-display text-xl font-bold" style="color: #15233d;">Ringkasan Pesanan</h2>
                        </div>
                        <span
                            class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">{{ count($cartItems) }}
                            Item</span>
                    </div>

                    {{-- Daftar Item --}}
                    <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto pr-1 my-2">
                        @foreach ($cartItems as $item)
                            <div class="py-3 flex justify-between gap-3 text-sm">
                                <div class="text-gray-600 min-w-0">
                                    <p class="font-semibold text-gray-800 leading-snug">{{ $item['nama'] }}</p>
                                    @php
                                        $specCk = collect([
                                            !empty($item['ukuran']) ? 'Ukuran ' . $item['ukuran'] : null,
                                            !empty($item['warna']) ? 'Warna ' . $item['warna'] : null,
                                        ])
                                            ->filter()
                                            ->implode(' · ');
                                    @endphp
                                    @if ($specCk)
                                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $specCk }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $item['qty'] }} &times;
                                        @if (($item['diskon'] ?? 0) > 0)
                                            <span class="line-through text-gray-400">Rp
                                                {{ number_format($item['harga_asli'], 0, ',', '.') }}</span>
                                        @endif
                                        Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <span class="font-bold text-gray-800 shrink-0 tabular-nums">Rp
                                    {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Ringkasan Pilihan Aktif --}}
                    <dl class="rounded-xl bg-[#faf9f6] border border-gray-200/70 p-3.5 my-4 space-y-2.5 text-xs">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 shrink-0">Pemesan</dt>
                            <dd id="sum-pemesan" class="font-semibold text-gray-800 text-right truncate">
                                {{ old('nama_pemesan', auth()->user()->name ?? '-') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 shrink-0">Penerimaan</dt>
                            <dd id="sum-terima" class="font-semibold text-gray-800 text-right">Ambil di Toko</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 shrink-0">Pembayaran</dt>
                            <dd id="sum-bayar" class="font-semibold text-gray-800 text-right">QRIS Instan</dd>
                        </div>
                    </dl>

                    {{-- Rincian Harga --}}
                    <div class="border-t border-gray-100 pt-4 mb-5 space-y-2.5">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Subtotal Produk</span>
                            <span class="font-semibold text-gray-800 tabular-nums">Rp
                                {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Ongkos Kirim</span>
                            <span id="row-ongkir" class="font-semibold text-emerald-600 tabular-nums">Gratis</span>
                        </div>
                        <div class="flex justify-between items-baseline pt-3 border-t border-gray-100">
                            <span class="text-sm font-bold text-gray-800">Total Tagihan</span>
                            <span id="txt-total" class="font-display text-2xl font-bold tabular-nums"
                                style="color: #c99a32;">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Banner Status Kelengkapan --}}
                    <div id="status-box"
                        class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200/80 px-3.5 py-2.5 text-xs text-emerald-800 flex items-center gap-2">
                        <span id="status-icon" class="font-bold">✓</span>
                        <span id="hint-submit">Semua data sudah lengkap. Pesanan siap dibuat.</span>
                    </div>

                    <button type="submit" id="btn-submit"
                        class="w-full py-3.5 px-5 rounded-xl text-center text-sm font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none"
                        style="background-color: #c99a32;">
                        Buat Pesanan &amp; Bayar
                    </button>
                </div>
            </div>

            {{-- Sticky Action Bar untuk Mobile --}}
            <div
                class="lg:hidden fixed inset-x-0 bottom-0 z-[60] bg-white/95 backdrop-blur-md border-t border-gray-200 px-4 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] flex items-center justify-between gap-4 shadow-[0_-10px_25px_rgba(0,0,0,0.06)]">
                <div class="flex flex-col min-w-0">
                    <span class="text-[11px] font-medium text-gray-500">Total Tagihan</span>
                    <span id="txt-total-mobile"
                        class="font-display text-lg font-bold text-[#c99a32] tabular-nums truncate">Rp
                        {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <button type="submit" id="btn-submit-mobile"
                    class="shrink-0 h-11 px-6 rounded-xl text-sm font-bold text-white transition-all duration-200 bg-[#c99a32] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                    Buat &amp; Bayar
                </button>
            </div>
        </form>
    </section>

    @php
        $alamatJs = $alamatList
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'tujuan_id' => $a->ongkir_tujuan_id,
                    'tujuan_label' => $a->ongkir_tujuan_label,
                ];
            })
            ->values();
    @endphp

    <script>
        (function() {
            const SUBTOTAL = {{ (int) $total }};
            const URL_TUJUAN = "{{ route('ongkir.tujuan') }}";
            const URL_HITUNG = "{{ route('ongkir.hitung') }}";
            const TOKEN = document.querySelector('input[name="_token"]').value;

            const $ = id => document.getElementById(id);
            const rupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

            let ongkir = 0;
            let ongkirNama = '';
            let timer = null;
            let bayarDisentuh = false;

            const NAMA_BAYAR = {
                qris: 'QRIS Instan',
                va: 'Virtual Account',
                ewallet: 'E-Wallet',
                tunai: 'Tunai di Toko',
            };

            const isKirim = () => document.querySelector('input[name="metode_penerimaan"]:checked').value === 'kirim';

            // ===== Sinkronisasi Nama & WA Pemesan ke Ringkasan & Form Alamat Baru =====
            const inPemesanNama = $('pemesan-nama');
            const inPemesanWa = $('pemesan-wa');

            function cekPemesanLengkap() {
                const namaOk = inPemesanNama.value.trim().length >= 2;
                const waOk = inPemesanWa.value.trim().length >= 8;
                return namaOk && waOk;
            }

            inPemesanNama.addEventListener('input', () => {
                $('sum-pemesan').textContent = inPemesanNama.value.trim() || '-';
                if ($('in-nama-penerima') && !$('in-nama-penerima').dataset.edited) {
                    $('in-nama-penerima').value = inPemesanNama.value;
                }
                refresh();
            });

            inPemesanWa.addEventListener('input', () => {
                if ($('in-wa-penerima') && !$('in-wa-penerima').dataset.edited) {
                    $('in-wa-penerima').value = inPemesanWa.value;
                }
                refresh();
            });

            if ($('in-nama-penerima')) {
                $('in-nama-penerima').addEventListener('input', function() {
                    this.dataset.edited = '1';
                });
            }
            if ($('in-wa-penerima')) {
                $('in-wa-penerima').addEventListener('input', function() {
                    this.dataset.edited = '1';
                });
            }

            // ===== Alur Checkout 4 Langkah =====
            function setStep(n, state) {
                $('stp-' + n).dataset.state = state;
            }

            function perbaruiAlur(step1Ok, step2Ok) {
                let aktif;
                if (!step1Ok) aktif = 1;
                else if (!step2Ok) aktif = 2;
                else if (!bayarDisentuh) aktif = 3;
                else aktif = 4;

                [1, 2, 3, 4].forEach(n => {
                    if (n < aktif) setStep(n, 'done');
                    else if (n === aktif) setStep(n, 'active');
                    else setStep(n, 'pending');
                });

                $('ln-1').dataset.full = aktif > 1 ? '1' : '0';
                $('ln-2').dataset.full = aktif > 2 ? '1' : '0';
                $('ln-3').dataset.full = aktif > 3 ? '1' : '0';

                // Badge status per kartu
                $('badge-step-1').classList.toggle('hidden', !step1Ok);
                $('badge-step-2').classList.toggle('hidden', !step2Ok);
            }

            function refresh() {
                const kirim = isKirim();
                const pilihKurir = $('in-layanan').value !== '';
                const biaya = kirim && pilihKurir ? ongkir : 0;
                const totalFinal = SUBTOTAL + biaya;

                const step1Ok = cekPemesanLengkap();
                const step2Ok = !kirim || pilihKurir;
                const tertahan = !step1Ok || !step2Ok;

                // Update Ongkir & Total
                const rowOngkir = $('row-ongkir');
                if (!kirim) {
                    rowOngkir.textContent = 'Gratis';
                    rowOngkir.className = 'font-semibold text-emerald-600 tabular-nums';
                } else if (pilihKurir) {
                    rowOngkir.textContent = rupiah(biaya);
                    rowOngkir.className = 'font-semibold text-gray-800 tabular-nums';
                } else {
                    rowOngkir.textContent = 'Pilih kurir dulu';
                    rowOngkir.className = 'text-xs font-semibold text-amber-600';
                }

                $('txt-total').textContent = rupiah(totalFinal);
                $('btn-submit').disabled = tertahan;

                // Tunai hanya tersedia untuk Ambil di Toko
                const boxTunai = $('opsi-tunai');
                const inTunai = boxTunai.querySelector('input');
                boxTunai.classList.toggle('hidden', kirim);
                inTunai.disabled = kirim;
                if (kirim && inTunai.checked) {
                    document.querySelector('input[name="metode_bayar"][value="qris"]').checked = true;
                }

                const tunai = !kirim && inTunai.checked;
                $('btn-submit').textContent = tunai ? 'Konfirmasi & Buat Pesanan' : 'Buat Pesanan & Bayar';

                // Banner Panduan Status Kesiapan di Ringkasan
                const statusBox = $('status-box');
                const statusIcon = $('status-icon');
                const hint = $('hint-submit');

                if (!step1Ok) {
                    statusBox.className =
                        'mb-4 rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-2.5 text-xs text-amber-800 flex items-center gap-2';
                    statusIcon.textContent = '!';
                    hint.textContent = 'Lengkapi Nama & No. WhatsApp Pemesan di Langkah 1.';
                } else if (!step2Ok) {
                    statusBox.className =
                        'mb-4 rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-2.5 text-xs text-amber-800 flex items-center gap-2';
                    statusIcon.textContent = '!';
                    hint.textContent = 'Pilih alamat tujuan & layanan kurir di Langkah 2.';
                } else {
                    statusBox.className =
                        'mb-4 rounded-xl bg-emerald-50 border border-emerald-200/80 px-3.5 py-2.5 text-xs text-emerald-800 flex items-center gap-2';
                    statusIcon.textContent = '✓';
                    hint.textContent = 'Data lengkap. Pesanan siap diproses.';
                }

                // Sinkronisasi Mobile Sticky Bar
                if ($('txt-total-mobile')) $('txt-total-mobile').textContent = rupiah(totalFinal);
                if ($('btn-submit-mobile')) {
                    $('btn-submit-mobile').disabled = tertahan;
                    $('btn-submit-mobile').textContent = tunai ? 'Buat Pesanan' : 'Buat & Bayar';
                }

                // Ringkasan Pilihan
                $('sum-pemesan').textContent = inPemesanNama.value.trim() || '-';
                $('sum-terima').textContent = !kirim ? 'Ambil di Toko' : (pilihKurir && ongkirNama ? 'Kirim · ' +
                    ongkirNama : 'Kirim ke Alamat');
                const bayar = document.querySelector('input[name="metode_bayar"]:checked');
                $('sum-bayar').textContent = NAMA_BAYAR[bayar ? bayar.value : 'qris'] || '-';

                perbaruiAlur(step1Ok, step2Ok);
            }

            function resetLayanan() {
                $('in-kurir').value = '';
                $('in-layanan').value = '';
                ongkir = 0;
                ongkirNama = '';
                refresh();
            }

            function resetTujuan() {
                $('in-tujuan-id').value = '';
                $('in-tujuan-label').value = '';
                $('info-tujuan').textContent = '';
                $('opsi-ongkir').innerHTML =
                    '<div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/60 px-4 py-3 text-xs text-gray-400">Pilih alamat atau kelurahan tujuan terlebih dahulu untuk melihat daftar ongkir.</div>';
                resetLayanan();
            }

            function pesanOpsi(teks) {
                const div = document.createElement('div');
                div.className =
                    'rounded-xl border border-dashed border-gray-200 bg-gray-50/60 px-4 py-3 text-xs text-gray-500';
                div.textContent = teks;
                $('opsi-ongkir').replaceChildren(div);
            }

            document.querySelectorAll('input[name="metode_bayar"]').forEach(r => r.addEventListener('change', () => {
                bayarDisentuh = true;
                refresh();
            }));

            document.querySelectorAll('input[name="metode_penerimaan"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    $('alamat-container').classList.toggle('hidden', !isKirim());
                    terapkanAlamat();
                    refresh();
                });
            });

            // ===== Pencarian Kelurahan Tujuan =====
            $('q-tujuan').addEventListener('input', function() {
                resetTujuan();
                clearTimeout(timer);

                const q = this.value.trim();
                if (q.length < 3) {
                    $('hasil-tujuan').classList.add('hidden');
                    return;
                }

                timer = setTimeout(async () => {
                    try {
                        const res = await fetch(URL_TUJUAN + '?q=' + encodeURIComponent(q), {
                            headers: {
                                'Accept': 'application/json'
                            },
                        });
                        const data = await res.json();
                        renderHasil(Array.isArray(data) ? data : []);
                    } catch (e) {
                        $('info-tujuan').textContent = 'Gagal mencari tujuan. Coba lagi.';
                    }
                }, 350);
            });

            function renderHasil(list) {
                const box = $('hasil-tujuan');
                box.replaceChildren();

                if (!list.length) {
                    const p = document.createElement('p');
                    p.className = 'px-3.5 py-2.5 text-xs text-gray-400';
                    p.textContent = 'Tujuan tidak ditemukan.';
                    box.appendChild(p);
                }

                list.forEach(t => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className =
                        'block w-full text-left px-3.5 py-2.5 text-xs text-gray-700 hover:bg-[#c99a32]/10 border-b border-gray-100 last:border-0 transition-colors';
                    b.textContent = t.label;
                    b.addEventListener('click', () => pilihTujuan(t));
                    box.appendChild(b);
                });

                box.classList.remove('hidden');
            }

            function pilihTujuan(t) {
                $('in-tujuan-id').value = t.id;
                $('in-tujuan-label').value = t.label;
                $('q-tujuan').value = t.label;
                $('hasil-tujuan').classList.add('hidden');
                $('info-tujuan').textContent = '✓ Area tujuan terpilih.';
                if (t.zip_code) $('ck-kodepos').value = t.zip_code;
                hitungOngkir(t.id);
            }

            async function hitungOngkir(tujuanId) {
                resetLayanan();
                pesanOpsi('Sedang menghitung tarif pengiriman...');

                try {
                    const res = await fetch(URL_HITUNG, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': TOKEN,
                        },
                        body: JSON.stringify({
                            tujuan_id: tujuanId
                        }),
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        pesanOpsi(data.message || 'Gagal menghitung ongkir.');
                        return;
                    }

                    renderOpsi(data.opsi || []);
                } catch (e) {
                    pesanOpsi('Gagal menghitung ongkir. Periksa koneksi lalu coba lagi.');
                }
            }

            function renderOpsi(list) {
                const wrap = $('opsi-ongkir');
                wrap.replaceChildren();

                if (!list.length) {
                    pesanOpsi('Tidak ada layanan kurir tersedia untuk tujuan ini.');
                    return;
                }

                list.forEach(o => {
                    const label = document.createElement('label');
                    label.className = 'cursor-pointer block';

                    const input = document.createElement('input');
                    input.type = 'radio';
                    input.name = 'opsi_ongkir';
                    input.className = 'opsi-radio peer sr-only';
                    input.addEventListener('change', () => {
                        $('in-kurir').value = o.kurir;
                        $('in-layanan').value = o.layanan;
                        ongkir = o.biaya;
                        ongkirNama = o.kurir.toUpperCase() + ' ' + o.layanan;
                        refresh();
                    });

                    const card = document.createElement('div');
                    card.className =
                        'opsi-card flex items-center gap-3.5 p-3.5 rounded-xl border border-gray-200 hover:border-[#c99a32]/60 transition-all duration-200';

                    const dot = document.createElement('span');
                    dot.className =
                        'radio-dot w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all';

                    const info = document.createElement('div');
                    info.className = 'flex-1 min-w-0';
                    const nama = document.createElement('p');
                    nama.className = 'text-sm font-bold text-gray-800';
                    nama.textContent = o.kurir.toUpperCase() + ' ' + o.layanan;
                    const est = document.createElement('p');
                    est.className = 'text-xs text-gray-500 truncate mt-0.5';
                    est.textContent = (o.deskripsi || 'Layanan Reguler') + ' · Estimasi ' + o.etd;
                    info.append(nama, est);

                    const harga = document.createElement('span');
                    harga.className = 'text-sm font-bold text-gray-900 shrink-0 tabular-nums';
                    harga.textContent = rupiah(o.biaya);

                    card.append(dot, info, harga);
                    label.append(input, card);
                    wrap.appendChild(label);
                });
            }

            // ===== Alamat Tersimpan =====
            const ALAMAT = @json($alamatJs);
            let alamatTerhitung = null;

            const alamatDipilih = () => {
                const r = document.querySelector('input[name="alamat_id"]:checked');
                return r ? r.value : '';
            };

            function terapkanAlamat() {
                const kirim = isKirim();
                const id = alamatDipilih();
                const pakaiTersimpan = kirim && id !== '' && id !== 'baru';
                const tampilManual = kirim && !pakaiTersimpan;

                document.querySelectorAll('[data-manual]').forEach(el => {
                    el.classList.toggle('hidden', !tampilManual);
                    const fields = (el.tagName === 'FIELDSET' || el.matches('input, textarea')) ? [el] : el
                        .querySelectorAll('input, textarea, select, fieldset');
                    fields.forEach(f => {
                        f.disabled = !tampilManual;
                    });
                });

                if (tampilManual && !wilayahDimuat) {
                    wilayahDimuat = true;
                    muatProvinsi(@json(old('provinsi', '')), @json(old('kota_kabupaten', '')),
                        @json(old('kecamatan', '')));
                }

                if (pakaiTersimpan) {
                    const a = ALAMAT.find(x => String(x.id) === String(id));
                    if (a && alamatTerhitung !== id) {
                        alamatTerhitung = id;
                        $('in-tujuan-id').value = a.tujuan_id;
                        $('in-tujuan-label').value = a.tujuan_label;
                        $('info-tujuan').textContent = '';
                        hitungOngkir(a.tujuan_id);
                    }
                } else if (kirim && alamatTerhitung !== null) {
                    alamatTerhitung = null;
                    $('q-tujuan').value = '';
                    resetTujuan();
                }
            }

            // ===== Dropdown Wilayah Bertingkat =====
            const URL_PROVINSI = "{{ url('/ongkir/provinsi') }}";
            const URL_KOTA = "{{ url('/ongkir/kota') }}";
            const URL_KECAMATAN = "{{ url('/ongkir/kecamatan') }}";
            const selProv = $('ck-provinsi');
            const selKota = $('ck-kota');
            const selKec = $('ck-kecamatan');
            let wilayahDimuat = false;

            async function ambilData(url) {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error();
                return res.json();
            }

            function isiPilihan(sel, items, placeholder, pilih) {
                sel.innerHTML = '';
                sel.add(new Option(placeholder, ''));
                items.forEach(item => {
                    const opt = new Option(item.name, item.name, false, item.name === pilih);
                    opt.dataset.id = item.id;
                    sel.add(opt);
                });
                sel.disabled = false;
            }

            function gagalPilihan(sel, pesan) {
                sel.innerHTML = '';
                sel.add(new Option(pesan, ''));
                sel.disabled = true;
            }

            async function muatProvinsi(pilihProv = '', pilihKota = '', pilihKec = '') {
                try {
                    isiPilihan(selProv, await ambilData(URL_PROVINSI), 'Pilih Provinsi', pilihProv);
                } catch (e) {
                    gagalPilihan(selProv, 'Gagal memuat provinsi');
                    return;
                }
                if (selProv.value) await muatKota(pilihKota, pilihKec);
            }

            async function muatKota(pilihKota = '', pilihKec = '') {
                const provOpt = selProv.selectedOptions[0];
                gagalPilihan(selKec, 'Pilih kota terlebih dahulu');
                if (!provOpt || !provOpt.dataset.id) {
                    gagalPilihan(selKota, 'Pilih provinsi terlebih dahulu');
                    return;
                }
                try {
                    isiPilihan(selKota, await ambilData(URL_KOTA + '/' + provOpt.dataset.id),
                        'Pilih Kota/Kabupaten', pilihKota);
                } catch (e) {
                    gagalPilihan(selKota, 'Gagal memuat kota');
                    return;
                }
                if (selKota.value) await muatKecamatan(pilihKec);
            }

            async function muatKecamatan(pilihKec = '') {
                const kotaOpt = selKota.selectedOptions[0];
                if (!kotaOpt || !kotaOpt.dataset.id) {
                    gagalPilihan(selKec, 'Pilih kota terlebih dahulu');
                    return;
                }
                try {
                    isiPilihan(selKec, await ambilData(URL_KECAMATAN + '/' + kotaOpt.dataset.id), 'Pilih Kecamatan',
                        pilihKec);
                } catch (e) {
                    gagalPilihan(selKec, 'Gagal memuat kecamatan');
                }
            }

            selProv.addEventListener('change', () => {
                $('q-tujuan').value = '';
                $('ck-kodepos').value = '';
                resetTujuan();
                muatKota();
            });
            selKota.addEventListener('change', () => {
                $('q-tujuan').value = '';
                $('ck-kodepos').value = '';
                resetTujuan();
                muatKecamatan();
            });
            selKec.addEventListener('change', function() {
                resetTujuan();
                if (this.value) {
                    $('q-tujuan').value = this.value;
                    $('q-tujuan').dispatchEvent(new Event('input'));
                } else {
                    $('q-tujuan').value = '';
                }
            });

            // ===== Simpan Alamat Baru via AJAX =====
            const URL_SIMPAN = "{{ route('checkout.alamat.simpan') }}";
            const btnSimpan = $('btn-simpan-alamat');
            const pesanSimpan = $('pesan-simpan');

            function aturOpsiUtama() {
                $('ck-box-utama').classList.toggle('hidden', ALAMAT.length === 0);
                $('ck-info-pertama').classList.toggle('hidden', ALAMAT.length !== 0);
            }
            aturOpsiUtama();

            function el(tag, className, text) {
                const e = document.createElement(tag);
                if (className) e.className = className;
                if (text !== undefined) e.textContent = text;
                return e;
            }

            function buatBadgeUtama() {
                const b = el('span',
                    'badge-utama rounded-full bg-[#c99a32]/15 px-2 py-0.5 text-[10px] font-bold', 'Utama');
                b.style.color = '#c99a32';
                return b;
            }

            function buatKartuAlamat(a) {
                const label = el('label', 'cursor-pointer block');

                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'alamat_id';
                input.value = a.id;
                input.className = 'opsi-radio peer sr-only';

                const card = el('div',
                    'opsi-card flex items-start gap-3.5 p-4 rounded-xl border border-gray-200 hover:border-[#c99a32]/60 transition-all duration-200'
                );
                const dot = el('span',
                    'radio-dot mt-1 w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all');

                const body = el('div', 'flex-1 min-w-0');
                const header = el('div', 'flex items-center gap-2 flex-wrap');
                const judul = el('p', 'text-sm font-bold text-gray-800', a.label || 'Alamat');
                header.append(judul);
                if (a.is_utama) header.append(buatBadgeUtama());

                const wilayah = [a.kecamatan, a.kota_kabupaten, a.provinsi, a.kode_pos].filter(Boolean).join(', ');
                body.append(
                    header,
                    el('p', 'text-xs font-medium text-gray-700 mt-1', a.nama_penerima + ' · ' + a.no_hp),
                    el('p', 'text-xs text-gray-500 mt-0.5 leading-relaxed', a.alamat),
                    el('p', 'text-[11px] text-gray-400 mt-0.5', wilayah)
                );

                card.append(dot, body);
                label.append(input, card);
                return {
                    label,
                    input
                };
            }

            function tampilPesan(teks) {
                pesanSimpan.textContent = teks || '';
            }

            btnSimpan.addEventListener('click', async () => {
                tampilPesan('');

                const nilai = n => {
                    const f = document.querySelector('[name="' + n + '"]');
                    return f ? f.value.trim() : '';
                };
                const labelDipilih = document.querySelector('input[name="label"]:checked');

                if (!$('in-tujuan-id').value) {
                    tampilPesan('Pilih kelurahan tujuan terlebih dahulu.');
                    return;
                }

                const payload = {
                    label: labelDipilih ? labelDipilih.value : 'Rumah',
                    nama_penerima: nilai('nama_penerima'),
                    no_wa: nilai('no_wa'),
                    provinsi: nilai('provinsi'),
                    kota_kabupaten: nilai('kota_kabupaten'),
                    kecamatan: nilai('kecamatan'),
                    kode_pos: nilai('kode_pos'),
                    alamat: nilai('alamat'),
                    tujuan_id: $('in-tujuan-id').value,
                    tujuan_label: $('in-tujuan-label').value,
                    jadikan_utama: $('ck-utama').checked ? 1 : 0,
                };

                const teksAwal = btnSimpan.textContent;
                btnSimpan.disabled = true;
                btnSimpan.textContent = 'Menyimpan...';

                try {
                    const res = await fetch(URL_SIMPAN, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': TOKEN,
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        const pertama = data.errors ? Object.values(data.errors)[0][0] : null;
                        tampilPesan(pertama || data.message || 'Gagal menyimpan alamat. Coba lagi.');
                        return;
                    }

                    const a = data.alamat;
                    const list = $('list-alamat');

                    if (a.is_utama) list.querySelectorAll('.badge-utama').forEach(b => b.remove());

                    const kartu = buatKartuAlamat(a);
                    a.is_utama ? list.prepend(kartu.label) : list.append(kartu.label);

                    ALAMAT.push({
                        id: a.id,
                        tujuan_id: a.tujuan_id,
                        tujuan_label: a.tujuan_label
                    });
                    $('info-alamat-kosong').classList.add('hidden');

                    kartu.input.checked = true;
                    alamatTerhitung = String(a.id);
                    terapkanAlamat();
                    aturOpsiUtama();

                    document.querySelector('textarea[name="alamat"]').value = '';
                    $('q-tujuan').value = '';
                    $('info-tujuan').textContent = '';
                    $('ck-kodepos').value = '';
                    $('ck-utama').checked = false;
                    gagalPilihan(selKota, 'Pilih provinsi terlebih dahulu');
                    gagalPilihan(selKec, 'Pilih kota terlebih dahulu');
                    muatProvinsi();

                    const ok = $('pesan-alamat-sukses');
                    ok.textContent = '✓ Alamat baru berhasil disimpan dan dipilih.';
                    ok.classList.remove('hidden');
                } catch (e) {
                    tampilPesan('Gagal menyimpan alamat. Periksa koneksi Anda.');
                } finally {
                    btnSimpan.disabled = false;
                    btnSimpan.textContent = teksAwal;
                }
            });

            document.addEventListener('change', e => {
                if (e.target && e.target.name === 'alamat_id') {
                    $('pesan-alamat-sukses').classList.add('hidden');
                    terapkanAlamat();
                }
            });

            document.addEventListener('click', e => {
                if (!$('hasil-tujuan').contains(e.target) && e.target !== $('q-tujuan')) {
                    $('hasil-tujuan').classList.add('hidden');
                }
            });

            terapkanAlamat();
            refresh();
        })();
    </script>
@endsection