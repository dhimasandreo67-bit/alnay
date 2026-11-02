@extends('admin.layouts.admin')

@section('title', 'Detail Sewa Busana')

@section('content')
    <div class="space-y-6" x-data="{ tab: '{{ session('active_tab', 'ringkasan') }}', modeJaminan: '{{ ($sewa->jaminan->metode_jaminan ?? 'ktp_tatap_muka') === 'ktp_tatap_muka' ? 'fisik' : 'digital' }}' }">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <nav class="text-xs text-gray-500 mb-1">
                    <a href="{{ route('admin.sewa.index') }}" class="hover:text-gold">Data Sewa</a> /
                    <span>Detail Sewa</span>
                </nav>
                <h1 class="text-2xl font-bold text-ink flex items-center gap-3">
                    Detail Sewa Busana <span
                        class="text-gray-400">#SEWA-{{ str_pad($sewa->id, 3, '0', STR_PAD_LEFT) }}</span>
                </h1>

                @php
                    $isTerlambat =
                        \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->isPast() && $sewa->status !== 'selesai';
                @endphp
                <span
                    class="inline-block mt-2 px-3 py-1 rounded-md text-xs font-bold {{ $isTerlambat ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $isTerlambat ? 'TERLAMBAT & MENUNGGU QC PELUNASAN' : 'SEDANG DISEWA' }}
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.sewa.index') }}"
                    class="px-4 py-2 bg-white border border-line rounded-lg text-sm font-semibold hover:bg-gray-50">&larr;
                    Kembali</a>
                <a href="https://wa.me/{{ $sewa->no_wa }}" target="_blank"
                    class="px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-semibold hover:bg-emerald-100">Hubungi
                    Pelanggan via WA</a>
                <button class="px-4 py-2 bg-ink text-white rounded-lg text-sm font-semibold hover:bg-gray-800">Cetak Berita
                    Acara</button>

                {{-- TOMBOL TANDAI SELESAI --}}
                @if ($sewa->status === 'aktif')
                    <form action="{{ route('admin.sewa.ubah-status', $sewa->id) }}" method="POST" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="selesai">
                        <button type="submit"
                            class="px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-semibold hover:bg-rose-700"
                            onclick="return confirm('Tandai sewa ini sebagai selesai/dikembalikan?')">
                            Tandai Selesai
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Notifikasi Success --}}
        @if (session('success'))
            <div
                class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Kartu Ringkasan Atas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-line shadow-sm">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Transaksi Induk</p>
                <h3 class="text-lg font-bold text-ink">{{ $sewa->transaksi->kode_transaksi ?? '-' }}</h3>
                <p class="text-xs text-gray-500 mt-1">Staff: Admin</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-line shadow-sm">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Pelanggan</p>
                <h3 class="text-lg font-bold text-ink">{{ $sewa->nama_pelanggan }}@if ($sewa->is_tamu) <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-bold uppercase text-gray-500">Tamu</span>@endif</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $sewa->no_hp_pelanggan ?: '-' }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-line shadow-sm {{ $isTerlambat ? 'border-rose-200' : '' }}">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Jadwal Pengembalian</p>
                <h3 class="text-lg font-bold {{ $isTerlambat ? 'text-rose-600' : 'text-ink' }}">
                    {{ \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->format('d M Y') }}
                </h3>
                @if ($isTerlambat)
                    <span
                        class="inline-block mt-1 text-[10px] font-bold text-rose-500 bg-rose-50 px-2 py-0.5 rounded">(Terlambat)</span>
                @endif
            </div>
            <div class="bg-white p-5 rounded-2xl border border-line shadow-sm">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Total Denda Ditagih</p>
                <h3 class="text-lg font-bold text-rose-600">Rp {{ number_format($totalDenda ?? 0, 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $sewa->denda->where('status', 'belum_lunas')->count() }}
                    tagihan aktif</p>
            </div>
        </div>

        {{-- Navigasi Tab --}}
        <div class="flex items-center gap-8 border-b border-line px-2">
            <button @click="tab = 'ringkasan'"
                :class="tab === 'ringkasan' ? 'border-gold text-ink' : 'border-transparent text-gray-500 hover:text-ink'"
                class="pb-3 text-sm font-bold border-b-2 flex items-center gap-2">
                <i class="fas fa-box"></i> Ringkasan Busana & Alur
            </button>
            <button @click="tab = 'jaminan'"
                :class="tab === 'jaminan' ? 'border-gold text-ink' : 'border-transparent text-gray-500 hover:text-ink'"
                class="pb-3 text-sm font-bold border-b-2 flex items-center gap-2">
                <i class="fas fa-shield-alt"></i> Verifikasi Jaminan Sewa
            </button>
            <button @click="tab = 'qc'"
                :class="tab === 'qc' ? 'border-gold text-ink' : 'border-transparent text-gray-500 hover:text-ink'"
                class="pb-3 text-sm font-bold border-b-2 flex items-center gap-2">
                <i class="fas fa-clipboard-check"></i> Pengecekan Barang (QC)
            </button>
            <button @click="tab = 'denda'"
                :class="tab === 'denda' ? 'border-gold text-ink' : 'border-transparent text-gray-500 hover:text-ink'"
                class="pb-3 text-sm font-bold border-b-2 flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar"></i> Kelola Denda & Settlement
            </button>
        </div>

        {{-- Konten Tab --}}
        <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">

            {{-- TAB 1: RINGKASAN --}}
            <div x-show="tab === 'ringkasan'" x-transition.opacity>
                <h2 class="text-lg font-bold text-ink mb-4">Ringkasan Busana & Alur</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Detail Produk --}}
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex gap-4 p-4 border border-line rounded-xl bg-cream/20">
                            @php
                                $produk = $sewa->transaksi->detail->first()->produk ?? null;
                                $foto = $produk->foto_produk ?? null;
                                if (is_array($foto)) {
                                    $foto = $foto[0] ?? null;
                                }

                                $namaKategori = $produk->kategori->nama_kategori ?? 'Busana Adat';
                                // Normalisasi ukuran: bisa berupa string, array biasa, atau array of array
                                // (mis. [{"ukuran":"M","stok":2}]) -> jadikan daftar teks sederhana.
                                $ukuranRaw = $produk->ukuran ?? [];
                                if (is_string($ukuranRaw)) {
                                    $decoded = json_decode($ukuranRaw, true);
                                    $ukuranRaw = is_array($decoded) ? $decoded : array_map('trim', explode(',', $ukuranRaw));
                                }
                                $ukuranList = collect(is_array($ukuranRaw) ? $ukuranRaw : [])
                                    ->map(function ($u) {
                                        if (is_array($u)) {
                                            return $u['ukuran'] ?? $u['size'] ?? $u['nama'] ?? $u['label']
                                                ?? collect($u)->filter(fn($v) => is_scalar($v))->first();
                                        }
                                        return is_scalar($u) ? $u : null;
                                    })
                                    ->filter(fn($u) => $u !== null && $u !== '')
                                    ->unique()
                                    ->values()
                                    ->all();
                                $hargaJual = $produk->harga_jual ?? 0;
                            @endphp
                            <div class="w-32 h-40 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                                @if ($foto)
                                    <img src="{{ asset('storage/' . $foto) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">No
                                        Image</div>
                                @endif
                            </div>
                            <div>
                                <span
                                    class="bg-gold text-white text-[10px] px-2 py-1 rounded font-bold mb-2 inline-block uppercase">
                                    {{ $namaKategori }}
                                </span>
                                <h3 class="text-xl font-bold text-ink">{{ $produk->nama ?? 'Nama Produk' }}</h3>
                                <p class="text-sm text-gray-600 mt-2 line-clamp-3">
                                    {{ $produk->deskripsi ?? 'Deskripsi produk...' }}</p>

                                <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-line">
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase">Ukuran Tersedia</p>
                                        <p class="text-sm font-semibold">
                                            {{ count($ukuranList) > 0 ? implode(', ', $ukuranList) : 'All Size' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase">SKU</p>
                                        <p class="text-sm font-semibold">{{ $produk->sku ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase">Nilai Aset (Harga Jual)</p>
                                        <p class="text-sm font-semibold">Rp {{ number_format($hargaJual, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kelengkapan Item Busana (Dummy) --}}
                        <div class="border border-line rounded-xl p-4">
                            <h4 class="font-bold text-ink mb-3 text-sm flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-gold"></span> Kelengkapan Item Busana
                            </h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center gap-2 text-sm p-2 bg-gray-50 rounded border border-gray-100">
                                    <i class="fas fa-check-circle text-emerald-500"></i> Busana Utama
                                </div>
                                <div class="flex items-center gap-2 text-sm p-2 bg-gray-50 rounded border border-gray-100">
                                    <i class="fas fa-check-circle text-emerald-500"></i> Aksesoris Pendukung
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="bg-cream/30 p-5 rounded-xl border border-line">
                        <h4 class="font-bold text-ink mb-4 text-sm">Audit Alur Penyewaan</h4>
                        <div class="relative border-l-2 border-gold/30 ml-2 space-y-6">
                            @foreach ($timeline as $item)
                                <div class="relative pl-4">
                                    <div
                                        class="absolute -left-[5px] top-1 w-2 h-2 rounded-full {{ $item['color'] }} ring-2 ring-white">
                                    </div>
                                    <p class="text-xs font-bold text-ink">{{ $item['title'] }}</p>
                                    <p class="text-[10px] text-gray-500">
                                        {{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d M Y • H:i') }} WIB</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $item['desc'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: JAMINAN --}}
            <div x-show="tab === 'jaminan'" x-cloak x-transition.opacity>
                {{-- Mode Simulasi --}}
                <div
                    class="mb-6 p-4 bg-gray-50 rounded-xl border border-line flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-sm text-ink">MODE SIMULASI DATA METODE JAMINAN</h3>
                        <p class="text-xs text-gray-500">Pilih kondisi metode jaminan untuk menampilkan antarmuka
                            kondisional.</p>
                    </div>
                    <div class="flex bg-white rounded-lg border border-line p-1">
                        <button @click="modeJaminan = 'fisik'"
                            :class="modeJaminan === 'fisik' ? 'bg-ink text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50'"
                            class="px-4 py-2 text-xs font-bold rounded-md transition-all">METODE: FISIK (STUDIO)</button>
                        <button @click="modeJaminan = 'digital'"
                            :class="modeJaminan === 'digital' ? 'bg-ink text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50'"
                            class="px-4 py-2 text-xs font-bold rounded-md transition-all">METODE: NON-FISIK
                            (DIGITAL)</button>
                    </div>
                </div>

                {{-- KONTEN: FISIK --}}
                <div x-show="modeJaminan === 'fisik'" x-transition.opacity class="border border-line rounded-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-ink flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-gold"></span> Verifikasi Jaminan Fisik di Studio
                        </h2>
                        @if (($sewa->jaminan->status_verifikasi ?? \'\') === \'verified\')<span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md text-xs font-bold">Terverifikasi</span>@else<span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-md text-xs font-bold">Menunggu Verifikasi Bankras</span>@endif
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Form Verifikasi --}}
                        <form action="{{ route('admin.sewa.verifikasi-jaminan', $sewa->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex gap-3">
                                <input type="checkbox" checked
                                    class="mt-1 w-4 h-4 text-gold rounded border-gray-300 focus:ring-gold">
                                <div>
                                    <p class="text-sm font-bold text-ink">KTP asli penyewa sudah dicocokkan tatap muka
                                        langsung di kasir studio</p>
                                    <p class="text-xs text-gray-500 mt-1">Admin kasir telah memastikan kesesuaian fisik
                                        pemegang KTP asli dengan pemilik transaksi rental.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">NOMOR NIK KTP</label>
                                    <input type="text" name="no_ktp" value="{{ $sewa->jaminan->nomor_ktp ?? '' }}"
                                        class="w-full px-3 py-2 border border-line rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold outline-none"
                                        placeholder="3273..." required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">NAMA SESUAI KTP</label>
                                    <input type="text" name="nama_sesuai_ktp"
                                        value="{{ $sewa->jaminan->nama_ktp ?? '' }}"
                                        class="w-full px-3 py-2 border border-line rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold outline-none"
                                        placeholder="Nama Lengkap" required>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-3 bg-ink text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> Simpan & Verifikasi Jaminan Fisik
                            </button>
                        </form>

                        {{-- Protap Penyimpanan --}}
                        <div class="bg-amber-50/50 border border-amber-100 rounded-lg p-5">
                            <h4 class="text-xs font-bold text-amber-800 uppercase mb-3">Protap Penyimpanan Jaminan</h4>
                            <p class="text-xs text-amber-700 leading-relaxed mb-4">KTP fisik hanya disimpan selama masa
                                penyewaan berlangsung dan wajib disimpan dalam amplop berkode identitas transaksi di Slot
                                B-04.</p>
                            <ul class="space-y-2 text-xs text-amber-700">
                                <li class="flex items-start gap-2"><i class="fas fa-check mt-0.5"></i> Dicek keaslian
                                    hologram e-KTP</li>
                                <li class="flex items-start gap-2"><i class="fas fa-check mt-0.5"></i> Bebas denda sebelum
                                    KTP dilepas</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- KONTEN: DIGITAL --}}
                <div x-show="modeJaminan === 'digital'" x-transition.opacity class="border border-line rounded-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-ink flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-gold"></span> Verifikasi Jaminan Digital / Non-Fisik
                        </h2>
                        @if (($sewa->jaminan->status_verifikasi ?? \'\') === \'verified\')<span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md text-xs font-bold">Terverifikasi</span>@else<span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-md text-xs font-bold">Menunggu Verifikasi Admin</span>@endif
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Foto KTP --}}
                        <div class="lg:col-span-2 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-gray-600">FOTO KTP UPLOAD PELANGGAN</label>
                                <button class="text-xs font-semibold text-gold hover:underline">Perbesar / Zoom Foto
                                    KTP</button>
                            </div>
                            <div
                                class="w-full h-64 bg-gray-100 rounded-lg border border-line flex items-center justify-center overflow-hidden">
                                @if ($sewa->jaminan && $sewa->jaminan->foto_ktp)
                                    <img src="{{ asset('storage/' . $sewa->jaminan->foto_ktp) }}"
                                        class="w-full h-full object-contain">
                                @else
                                    <div class="text-gray-400 text-sm flex flex-col items-center">
                                        <i class="fas fa-id-card text-4xl mb-2 text-gray-300"></i>
                                        Belum ada foto KTP
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Form Input NIK & Nama --}}
                        <div class="space-y-4">
                            <div class="bg-amber-50 rounded-xl p-5 border border-amber-100 text-center">
                                <p class="text-xs font-bold text-amber-800 uppercase mb-1">Jumlah Deposit Yang Sudah
                                    Dibayar</p>
                                <h3 class="text-2xl font-bold text-ink">Rp
                                    {{ number_format($sewa->jaminan->jumlah_deposit ?? 0, 0, ',', '.') }}</h3>
                                <p class="text-xs text-amber-700 mt-1 flex items-center justify-center gap-1"><i
                                        class="fas fa-shield-alt"></i> via Midtrans - Status: Tertahan</p>
                            </div>

                            <form action="{{ route('admin.sewa.verifikasi-jaminan', $sewa->id) }}" method="POST"
                                class="space-y-4 bg-gray-50 p-4 rounded-xl border border-line">
                                @csrf
                                <p class="text-xs font-bold text-gray-600 mb-2">INPUT DATA KTP (Cocokkan dengan foto)</p>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">NOMOR NIK KTP</label>
                                    <input type="text" name="no_ktp" value="{{ $sewa->jaminan->nomor_ktp ?? '' }}"
                                        class="w-full px-3 py-2 border border-line rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold outline-none"
                                        placeholder="3273..." required>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">NAMA SESUAI KTP</label>
                                    <input type="text" name="nama_sesuai_ktp"
                                        value="{{ $sewa->jaminan->nama_ktp ?? '' }}"
                                        class="w-full px-3 py-2 border border-line rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold outline-none"
                                        placeholder="Nama Lengkap" required>
                                </div>

                                <button type="submit"
                                    class="w-full py-3 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle"></i> Terima & Verifikasi KTP
                                </button>
                            </form>

                            {{-- Tombol Tolak --}}
                            <form action="#" method="POST" onsubmit="return confirm('Tolak KTP ini?')">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2.5 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-xs font-bold hover:bg-rose-100 transition">
                                    Tolak KTP (Minta Upload Ulang)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: QC --}}
            <div x-show="tab === 'qc'" x-cloak x-transition.opacity>
                <div class="flex items-center justify-between mb-6 border-b border-line pb-4">
                    <h2 class="text-lg font-bold text-ink">Pengecekan Barang (QC)</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Form Input QC --}}
                    <div class="space-y-5">
                        <form action="{{ route('admin.sewa.simpan-qc', $sewa->id) }}" method="POST"
                            enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <div class="bg-gray-50 p-4 rounded-xl border border-line mb-4">
                                <h3 class="font-bold text-sm text-ink">FORMULIR QC</h3>
                                <p class="text-xs text-gray-500">Dokumentasikan detail fisik gaun, payet, dan aksesoris.
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">PILIHAN TAHAP QC</label>
                                <select name="tahap"
                                    class="w-full px-3 py-2.5 border border-line rounded-lg text-sm bg-white focus:border-gold focus:ring-1 focus:ring-gold outline-none"
                                    required>
                                    <option value="sebelum_cuci">Sebelum Cuci / Baru Diterima dari Pelanggan</option>
                                    <option value="setelah_cuci">Setelah Cuci / Siap Disewakan Kembali</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">CATATAN KONDISI</label>
                                <textarea name="catatan" rows="4"
                                    class="w-full px-3 py-2 border border-line rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold outline-none resize-none"
                                    placeholder="Contoh: 3 butir payet lepas di lengan kanan..." required></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">FOTO BUKTI (Opsional)</label>
                                <input type="file" name="foto_bukti" accept="image/*"
                                    class="w-full px-3 py-2 border border-line rounded-lg text-sm focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-gold/10 file:text-gold hover:file:bg-gold/20">
                            </div>

                            <button type="submit"
                                class="w-full py-3 bg-ink text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-plus"></i> Simpan Catatan QC
                            </button>
                        </form>
                    </div>

                    {{-- Timeline Riwayat QC --}}
                    <div class="bg-cream/30 p-5 rounded-xl border border-line h-fit">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-sm text-ink">RIWAYAT PENGECEKAN</h4>
                            <span
                                class="text-[10px] bg-white border border-line px-2 py-1 rounded text-gray-500 font-bold">{{ $sewa->pengecekan->count() }}
                                Entri</span>
                        </div>

                        <div class="relative border-l-2 border-gold/30 ml-2 space-y-8">
                            @forelse($sewa->pengecekan->sortByDesc('created_at') as $qc)
                                <div class="relative pl-6" x-data="{ editMode: false }">
                                    {{-- Dot Timeline --}}
                                    <div
                                        class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-white border-2 {{ $qc->tahap === 'sebelum_cuci' ? 'border-rose-400' : 'border-emerald-400' }}">
                                    </div>

                                    {{-- Header Timeline --}}
                                    <div class="flex items-center justify-between mb-1">
                                        <span
                                            class="text-[10px] font-bold {{ $qc->tahap === 'sebelum_cuci' ? 'text-rose-600 bg-rose-50' : 'text-emerald-600 bg-emerald-50' }} px-2 py-0.5 rounded uppercase">
                                            {{ str_replace('_', ' ', $qc->tahap) }}
                                        </span>

                                        {{-- Tombol Aksi --}}
                                        <div class="flex gap-1">
                                            <button @click="editMode = !editMode"
                                                class="text-gray-400 hover:text-gold transition-colors" title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('admin.sewa.hapus-qc', $qc->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus catatan QC ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-gray-400 hover:text-rose-600 transition-colors"
                                                    title="Hapus">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <p class="text-[10px] text-gray-500 mb-2">
                                        {{ $qc->created_at->translatedFormat('d M Y, H:i') }} WIB</p>

                                    {{-- Mode Tampilan --}}
                                    <div x-show="!editMode" class="bg-white p-3 rounded-lg border border-line text-xs text-gray-600 italic">
                                        "{{ $qc->catatan }}"
                                    </div>

                                    {{-- Baris Bawah: Petugas & Link Foto --}}
                                    <div x-show="!editMode" class="flex items-center justify-between mt-2">
                                        <p class="text-[10px] text-gray-500 flex items-center gap-1">
                                            <i class="fas fa-user-check"></i> Petugas: {{ $qc->petugas }}
                                        </p>
                                        @if ($qc->foto_bukti)
                                            <a href="{{ asset('storage/' . $qc->foto_bukti) }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-[10px] font-bold text-gold hover:underline">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Lihat Foto
                                            </a>
                                        @endif
                                    </div>

                                    {{-- Mode Edit --}}
                                    <div x-show="editMode" x-cloak
                                        class="bg-gray-50 p-3 rounded-lg border border-line">
                                        <form action="{{ route('admin.sewa.update-qc', $qc->id) }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf @method('PUT')
                                            <select name="tahap"
                                                class="w-full mb-2 px-2 py-1 border border-line rounded text-xs">
                                                <option value="sebelum_cuci"
                                                    {{ $qc->tahap === 'sebelum_cuci' ? 'selected' : '' }}>Sebelum Cuci
                                                </option>
                                                <option value="setelah_cuci"
                                                    {{ $qc->tahap === 'setelah_cuci' ? 'selected' : '' }}>Setelah Cuci
                                                </option>
                                            </select>
                                            <textarea name="catatan" rows="2" class="w-full mb-2 px-2 py-1 border border-line rounded text-xs" required>{{ $qc->catatan }}</textarea>
                                            <input type="file" name="foto_bukti" accept="image/*"
                                                class="w-full mb-2 text-xs">
                                            <div class="flex gap-2">
                                                <button type="submit"
                                                    class="px-2 py-1 bg-gold text-white text-[10px] font-bold rounded">Simpan</button>
                                                <button type="button" @click="editMode = false"
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 text-[10px] font-bold rounded">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-500 italic pl-4">Belum ada riwayat pengecekan.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: DENDA --}}
            <div x-show="tab === 'denda'" x-cloak x-transition.opacity>
                <div class="flex items-center justify-between mb-6 border-b border-line pb-4">
                    <div>
                        <h2 class="text-lg font-bold text-ink">Rincian Denda & Penyelesaian</h2>
                        <p class="text-xs text-gray-500 mt-1">Kelola penalti keterlambatan, kerusakan busana, serta
                            otorisasi pengembalian sisa deposit.</p>
                    </div>
                    <button onclick="document.getElementById('form-denda').classList.toggle('hidden')"
                        class="px-4 py-2 bg-ink text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Denda Baru
                    </button>
                </div>

                {{-- Form Tambah Denda --}}
                <form id="form-denda" action="{{ route('admin.sewa.tambah-denda', $sewa->id) }}" method="POST"
                    class="hidden mb-6 p-4 bg-gray-50 border border-line rounded-xl grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">JENIS DENDA</label>
                        <select name="jenis" class="w-full px-3 py-2 border border-line rounded-lg text-sm" required>
                            <option value="Keterlambatan">Keterlambatan</option>
                            <option value="Kerusakan">Kerusakan Busana</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">JUMLAH (Rp)</label>
                        <input type="number" name="jumlah"
                            class="w-full px-3 py-2 border border-line rounded-lg text-sm" placeholder="150000"
                            required>
                    </div>
                    <button type="submit" class="py-2 bg-gold text-white rounded-lg text-sm font-bold">Simpan
                        Denda</button>
                </form>

                {{-- Tabel Denda --}}
                <div class="bg-white border border-line rounded-xl overflow-hidden mb-6">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-line text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-4">Jenis Denda</th>
                                <th class="px-6 py-4 text-right">Jumlah (Rp)</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse($sewa->denda as $denda)
                                <tr>
                                    <td class="px-6 py-4 font-bold text-ink">{{ $denda->jenis }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-rose-600">Rp
                                        {{ number_format($denda->jumlah, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($denda->status === 'lunas')
                                            <span
                                                class="px-2 py-1 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded text-[10px] font-bold">LUNAS</span>
                                        @else
                                            <span
                                                class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded text-[10px] font-bold">BELUM
                                                LUNAS</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if ($denda->status !== 'lunas')
                                            <form action="{{ route('admin.sewa.lunasi-denda', $denda->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="px-3 py-1.5 border border-line rounded text-xs font-semibold hover:bg-gray-50">Tandai
                                                    Lunas</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada denda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pengembalian Deposit --}}
                <div
                    class="bg-emerald-50/30 border border-emerald-100 rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span
                                class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded">PENCAIRAN
                                DEPOSIT JAMINAN</span>
                        </div>
                        <h3 class="text-xl font-bold text-ink mb-2">Pengembalian Penuh 100% Deposit Sewa</h3>
                        <p class="text-sm text-gray-600 leading-relaxed max-w-3xl">
                            Deposit selalu dikembalikan utuh 100% (Rp
                            {{ number_format($sewa->jaminan->jumlah_deposit ?? 0, 0, ',', '.') }}) tanpa memotong denda.
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs text-gray-500 mb-1">STATUS PENCAIRAN:</p>
                        <p class="text-sm font-bold text-ink mb-3">
                            {{ ($sewa->jaminan->status_pengembalian_deposit ?? '') === 'dikembalikan' ? 'Selesai Dikembalikan' : 'Menunggu Otorisasi QC Selesai' }}
                        </p>

                        <form action="{{ route('admin.sewa.kembalikan-deposit', $sewa->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition flex items-center gap-2 shadow-sm"
                                {{ ($sewa->jaminan->status_pengembalian_deposit ?? '') === 'dikembalikan' ? 'disabled' : '' }}>
                                <i class="fas fa-money-bill-wave"></i> Kembalikan Deposit Penuh
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection