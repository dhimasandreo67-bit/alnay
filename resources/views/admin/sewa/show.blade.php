@extends('admin.layouts.admin')

@section('title', 'Detail Sewa Busana')

@section('content')
    @php
        // Denda telat otomatis
        $produkSewaList = $sewa->transaksi->detail->map(fn ($d) => $d->produk)->filter();
        $tarifTelat = (float) $produkSewaList->sum('denda_per_hari_telat');
        $dendaTelatOtomatis = $sewa->denda->where('otomatis', true)->first();
        $hariTelatLive = 0;
        if ($sewa->status !== 'selesai') {
            $batasKembali = \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->startOfDay();
            $hariTelatLive = max(0, (int) $batasKembali->diffInDays(now()->startOfDay(), false));
        }
        $estimasiDendaTelat = $hariTelatLive * $tarifTelat;

        // --- KALKULASI DEPOSIT & DENDA BARU ---
        $depositAwal = $sewa->jaminan->jumlah_deposit ?? 0;
        $totalDendaKotor = $sewa->denda->sum('jumlah');
        $potonganDenda = min($totalDendaKotor, $depositAwal);
        $sisaDeposit = max(0, $depositAwal - $totalDendaKotor);
        $kekuranganDenda = max(0, $totalDendaKotor - $depositAwal);
    @endphp
    
    <div class="space-y-6" x-data="{ 
        tab: '{{ session('active_tab', 'ringkasan') }}',
        hapusDenda: false,
        urlHapus: ''
    }">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <nav class="text-xs text-gray-500 mb-1">
                    <a href="{{ route('admin.sewa.index') }}" class="hover:text-gold">Data Sewa</a> /
                    <span>Detail Sewa</span>
                </nav>
                <h1 class="text-2xl font-bold text-ink flex items-center gap-3">
                    Detail Sewa Busana <span class="text-gray-400">#SEWA-{{ str_pad($sewa->id, 3, '0', STR_PAD_LEFT) }}</span>
                </h1>

                @php
                    $isTerlambat = \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->isPast() && $sewa->status !== 'selesai';
                @endphp
                <span class="inline-block mt-2 px-3 py-1 rounded-md text-xs font-bold {{ $isTerlambat ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $isTerlambat ? 'TERLAMBAT — BELUM DIKEMBALIKAN' : 'SEDANG DISEWA' }}
                </span>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.sewa.index') }}" class="px-4 py-2 bg-white border border-line rounded-lg text-sm font-semibold hover:bg-gray-50">&larr; Kembali</a>
                <a href="https://wa.me/{{ $sewa->no_wa }}" target="_blank" class="px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-semibold hover:bg-emerald-100">Hubungi Pelanggan via WA</a>
                <button class="px-4 py-2 bg-ink text-white rounded-lg text-sm font-semibold hover:bg-gray-800">Cetak Berita Acara</button>
            </div>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Alur Pengembalian Barang --}}
        @php
            $flowDiterima = $sewa->status === 'selesai';
            $jumlahDendaBelumLunas = $sewa->denda->where('status', 'belum_lunas')->count();
            $flowDenda = $flowDiterima && $jumlahDendaBelumLunas === 0;
            $flowDeposit = ($sewa->jaminan->status_pengembalian_deposit ?? null) === 'dikembalikan';

            $flowSteps = [
                1 => ['label' => 'Sewa Berjalan', 'desc' => 'Busana di tangan pelanggan.', 'done' => true],
                2 => ['label' => 'Barang Kembali & QC', 'desc' => 'Terima busana, cek kondisi.', 'done' => $flowDiterima],
                3 => ['label' => 'Denda Diselesaikan', 'desc' => 'Denda lunas/dipotong deposit.', 'done' => $flowDenda],
                4 => ['label' => 'Deposit Dikembalikan', 'desc' => 'Sisa deposit cair ke pelanggan.', 'done' => $flowDeposit],
            ];
            $flowCurrent = collect($flowSteps)->first(fn ($s) => !$s['done'], end($flowSteps));
            $flowCurrentNum = array_search($flowCurrent, $flowSteps, true);
            $flowSelesaiSemua = $flowDiterima && $flowDenda && $flowDeposit;
        @endphp
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-bold text-ink flex items-center gap-2">
                    <i class="fas fa-route text-gold"></i> Alur Pengembalian Barang
                </h2>
                @unless ($flowSelesaiSemua)
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tahap {{ $flowCurrentNum }} dari 4</span>
                @else
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Tuntas</span>
                @endunless
            </div>

            <div class="flex items-start overflow-x-auto pb-1">
                @foreach ($flowSteps as $num => $step)
                    <div class="flex items-center {{ $num < 4 ? 'flex-1 min-w-[150px]' : 'min-w-[150px]' }}">
                        <div class="flex flex-col items-center text-center w-full px-1">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold border-2
                                        {{ $step['done']
                                            ? 'bg-emerald-500 border-emerald-500 text-white'
                                            : ($num === $flowCurrentNum ? 'bg-amber-50 border-amber-500 text-amber-600' : 'bg-gray-50 border-line text-gray-400') }}">
                                @if ($step['done'])
                                    <i class="fas fa-check text-[10px]"></i>
                                @else
                                    {{ $num }}
                                @endif
                            </div>
                            <p class="mt-2 text-xs font-bold whitespace-nowrap {{ $step['done'] ? 'text-emerald-700' : ($num === $flowCurrentNum ? 'text-amber-700' : 'text-gray-400') }}">
                                {{ $step['label'] }}
                            </p>
                            <p class="mt-0.5 text-[10px] text-gray-400 max-w-[140px]">{{ $step['desc'] }}</p>
                        </div>
                    </div>
                    @if ($num < 4)
                        <div class="mt-4 h-0.5 flex-1 min-w-[24px] {{ $step['done'] ? 'bg-emerald-400' : 'bg-line' }}"></div>
                    @endif
                @endforeach
            </div>

            <div class="mt-5 pt-4 border-t border-line">
                @if (!$flowDiterima)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <p class="text-xs text-gray-500">Busana masih di tangan pelanggan. Tandai sudah kembali begitu diterima di toko.</p>
                        <form action="{{ route('admin.sewa.ubah-status', $sewa->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="selesai">
                            <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-bold hover:bg-rose-700 whitespace-nowrap" onclick="return confirm('Tandai barang sudah diterima kembali dari pelanggan?')">
                                <i class="fas fa-box-open mr-1"></i> Tandai Barang Diterima Kembali
                            </button>
                        </form>
                    </div>
                @elseif (!$flowDenda)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <p class="text-xs text-amber-700">
                            <i class="fas fa-triangle-exclamation mr-1"></i>
                            Masih ada {{ $jumlahDendaBelumLunas }} denda yang belum lunas. Lunasi dulu sebelum deposit bisa dikembalikan.
                        </p>
                        <button type="button" @click="tab = 'denda'" class="px-4 py-2 bg-ink text-white rounded-lg text-sm font-bold hover:bg-gray-800 whitespace-nowrap">
                            Urus di Tab Denda &rarr;
                        </button>
                    </div>
                @elseif (!$flowDeposit)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <p class="text-xs text-gray-500">Barang sudah kembali dan denda sudah beres. Deposit siap dikembalikan ke pelanggan.</p>
                        <button type="button" @click="tab = 'denda'" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 whitespace-nowrap">
                            <i class="fas fa-money-bill-wave mr-1"></i> Kembalikan Deposit &rarr;
                        </button>
                    </div>
                @else
                    <p class="text-xs text-emerald-700 font-semibold flex items-center gap-2">
                        <i class="fas fa-circle-check"></i> Seluruh proses pengembalian untuk sewa ini sudah tuntas.
                    </p>
                @endif
            </div>
        </div>

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
                    <span class="inline-block mt-1 text-[10px] font-bold text-rose-500 bg-rose-50 px-2 py-0.5 rounded">(Terlambat)</span>
                @endif
            </div>
            <div class="bg-white p-5 rounded-2xl border border-line shadow-sm">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Total Denda Ditagih</p>
                <h3 class="text-lg font-bold text-rose-600">Rp {{ number_format($totalDendaKotor ?? 0, 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $sewa->denda->where('status', 'belum_lunas')->count() }} tagihan aktif</p>
            </div>
        </div>

        {{-- Navigasi Tab --}}
        <div class="flex items-center gap-5 sm:gap-8 border-b border-line px-2 overflow-x-auto no-scrollbar">
            <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'border-gold text-ink' : 'border-transparent text-gray-500 hover:text-ink'" class="pb-3 text-sm font-bold border-b-2 flex items-center gap-2 whitespace-nowrap shrink-0">
                <i class="fas fa-box"></i> Ringkasan Busana & Alur
            </button>
            <button @click="tab = 'jaminan'" :class="tab === 'jaminan' ? 'border-gold text-ink' : 'border-transparent text-gray-500 hover:text-ink'" class="pb-3 text-sm font-bold border-b-2 flex items-center gap-2 whitespace-nowrap shrink-0">
                <i class="fas fa-shield-alt"></i> Verifikasi Jaminan Sewa
            </button>
            <button @click="tab = 'qc'" :class="tab === 'qc' ? 'border-gold text-ink' : 'border-transparent text-gray-500 hover:text-ink'" class="pb-3 text-sm font-bold border-b-2 flex items-center gap-2 whitespace-nowrap shrink-0">
                <i class="fas fa-clipboard-check"></i> Pengecekan Barang (QC)
                @if ($flowDiterima && !$flowSelesaiSemua)
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                @endif
            </button>
            <button @click="tab = 'denda'" :class="tab === 'denda' ? 'border-gold text-ink' : 'border-transparent text-gray-500 hover:text-ink'" class="pb-3 text-sm font-bold border-b-2 flex items-center gap-2 whitespace-nowrap shrink-0">
                <i class="fas fa-file-invoice-dollar"></i> Kelola Denda & Settlement
                @if ($flowDiterima && !$flowDenda)
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                @endif
            </button>
        </div>

        {{-- Konten Tab --}}
        <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">

            {{-- TAB 1: RINGKASAN (LENGKAP) --}}
            <div x-show="tab === 'ringkasan'" x-transition.opacity>
                <h2 class="text-lg font-bold text-ink mb-4">Ringkasan Busana & Alur</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex gap-4 p-4 border border-line rounded-xl bg-cream/20">
                            @php
                                $produk = $sewa->transaksi->detail->first()->produk ?? null;
                                $foto = $produk->foto_produk ?? null;
                                if (is_array($foto)) { $foto = $foto[0] ?? null; }
                                $namaKategori = $produk->kategori->nama_kategori ?? 'Busana Adat';
                                $ukuranRaw = $produk->ukuran ?? [];
                                if (is_string($ukuranRaw)) {
                                    $decoded = json_decode($ukuranRaw, true);
                                    $ukuranRaw = is_array($decoded) ? $decoded : array_map('trim', explode(',', $ukuranRaw));
                                }
                                $ukuranList = collect(is_array($ukuranRaw) ? $ukuranRaw : [])
                                    ->map(function ($u) {
                                        if (is_array($u)) {
                                            return $u['ukuran'] ?? $u['size'] ?? $u['nama'] ?? $u['label'] ?? collect($u)->filter(fn($v) => is_scalar($v))->first();
                                        }
                                        return is_scalar($u) ? $u : null;
                                    })->filter(fn($u) => $u !== null && $u !== '')->unique()->values()->all();
                                $hargaJual = $produk->harga_jual ?? 0;
                            @endphp
                            <div class="w-32 h-40 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                                @if ($foto)
                                    <img src="{{ asset('storage/' . $foto) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">No Image</div>
                                @endif
                            </div>
                            <div>
                                <span class="bg-gold text-white text-[10px] px-2 py-1 rounded font-bold mb-2 inline-block uppercase">{{ $namaKategori }}</span>
                                <h3 class="text-xl font-bold text-ink">{{ $produk->nama ?? 'Nama Produk' }}</h3>
                                <p class="text-sm text-gray-600 mt-2 line-clamp-3">{{ $produk->deskripsi ?? 'Deskripsi produk...' }}</p>
                                <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-line">
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase">Ukuran Tersedia</p>
                                        <p class="text-sm font-semibold">{{ count($ukuranList) > 0 ? implode(', ', $ukuranList) : 'All Size' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase">SKU</p>
                                        <p class="text-sm font-semibold">{{ $produk->sku ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase">Nilai Aset (Harga Jual)</p>
                                        <p class="text-sm font-semibold">Rp {{ number_format($hargaJual, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border border-line rounded-xl p-4">
                            <h4 class="font-bold text-ink mb-3 text-sm flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gold"></span> Kelengkapan Item Busana</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center gap-2 text-sm p-2 bg-gray-50 rounded border border-gray-100"><i class="fas fa-check-circle text-emerald-500"></i> Busana Utama</div>
                                <div class="flex items-center gap-2 text-sm p-2 bg-gray-50 rounded border border-gray-100"><i class="fas fa-check-circle text-emerald-500"></i> Aksesoris Pendukung</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-cream/30 p-5 rounded-xl border border-line">
                        <h4 class="font-bold text-ink mb-4 text-sm">Audit Alur Penyewaan</h4>
                        <div class="relative border-l-2 border-gold/30 ml-2 space-y-6">
                            @foreach ($timeline as $item)
                                <div class="relative pl-4">
                                    <div class="absolute -left-[5px] top-1 w-2 h-2 rounded-full {{ $item['color'] }} ring-2 ring-white"></div>
                                    <p class="text-xs font-bold text-ink">{{ $item['title'] }}</p>
                                    <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d M Y • H:i') }} WIB</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $item['desc'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: JAMINAN (LENGKAP DENGAN KTP) --}}
            <div x-show="tab === 'jaminan'" x-cloak x-transition.opacity>
                @php
                    $jaminan = $sewa->jaminan;
                    $isFisik = ($jaminan->metode_jaminan ?? 'ktp_tatap_muka') === 'ktp_tatap_muka';
                    $verified = ($jaminan->status_verifikasi ?? null) === 'verified';
                    $nikJaminan = (string) ($jaminan->nomor_ktp ?? '');
                    $nikMask = $nikJaminan !== '' ? substr($nikJaminan, 0, 4) . str_repeat('•', max(0, strlen($nikJaminan) - 8)) . substr($nikJaminan, -4) : '-';
                    $fmtTanggalBayar = fn ($v) => $v ? \Carbon\Carbon::parse($v)->translatedFormat('d M Y, H:i') . ' WIB' : '-';
                    $semuaBayar = $sewa->transaksi->pembayaran ?? collect();
                    $bayarBerhasil = $semuaBayar->filter(fn ($p) => in_array(strtolower((string) $p->status_midtrans), ['success', 'settlement', 'capture'], true));
                    $dpList = $bayarBerhasil->where('jenis_bayar', 'dp');
                    $dpDibayar = (float) $dpList->sum('jumlah');
                    $dpPertama = $dpList->sortBy('tanggal_bayar')->first();
                    $totalBayarJaminan = (float) $bayarBerhasil->sum('jumlah');
                    $totalTrxJaminan = (float) ($sewa->transaksi->total ?? 0);
                    $sisaTagihanJaminan = max(0, $totalTrxJaminan - $totalBayarJaminan);
                    $persenDp = $totalTrxJaminan > 0 ? round($dpDibayar / $totalTrxJaminan * 100) : 0;
                @endphp

                <div class="space-y-6" x-data="{ zoom: false, lihatNik: false, nik: @js($nikJaminan !== '' ? $nikJaminan : '-'), masked: @js($nikMask) }" x-init="$watch('tab', () => zoom = false)" @keydown.escape.window="zoom = false">
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-line">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($verified)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-emerald-100 text-emerald-700 border-emerald-200">Terverifikasi</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-amber-100 text-amber-700 border-amber-200">Menunggu Verifikasi</span>
                            @endif
                            @if ($isFisik)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-sky-100 text-sky-700 border-sky-200">Fisik (KTP Tatap Muka)</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-violet-100 text-violet-700 border-violet-200">Digital (Upload)</span>
                            @endif
                        </div>
                        @if ($jaminan && $jaminan->foto_ktp)
                            <button type="button" @click="zoom = true" class="text-xs font-bold text-gold hover:underline">Perbesar Foto KTP</button>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                        <div class="lg:col-span-2 space-y-6">
                            @php $adaFoto = $jaminan && $jaminan->foto_ktp; @endphp
                            @if ($verified)
                                <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                    <i class="fas fa-circle-check mt-0.5 text-emerald-600"></i>
                                    <div>
                                        <p class="text-sm font-bold text-emerald-800">Jaminan sudah terverifikasi</p>
                                        <p class="mt-0.5 text-xs text-emerald-700">Dicek oleh {{ $jaminan->verifikator->name ?? 'admin' }} &middot; {{ $fmtTanggalBayar($jaminan->tanggal_verifikasi) }}.</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                    <i class="fas fa-hourglass-half mt-0.5 text-amber-600"></i>
                                    <div>
                                        <p class="text-sm font-bold text-amber-800">Menunggu verifikasi</p>
                                        <p class="mt-0.5 text-xs text-amber-700">Cocokkan KTP dengan penyewa, lalu centang konfirmasi di bawah.</p>
                                    </div>
                                </div>
                            @endif
                            <ol class="grid grid-cols-3 gap-2 text-center text-[11px] font-bold">
                                <li class="rounded-lg border px-2 py-2 {{ $adaFoto ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-gray-200 bg-gray-50 text-gray-400' }}">1. Foto KTP {{ $adaFoto ? 'diterima' : 'belum ada' }}</li>
                                <li class="rounded-lg border px-2 py-2 {{ $verified ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">2. Data dicocokkan</li>
                                <li class="rounded-lg border px-2 py-2 {{ $verified ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-gray-200 bg-gray-50 text-gray-400' }}">3. Terverifikasi</li>
                            </ol>
                            <div class="border border-line rounded-xl p-6">
                                <h3 class="font-bold text-ink mb-4 text-sm flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gold"></span> KTP Penyewa</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="w-full h-56 bg-gray-50 rounded-xl border border-line flex items-center justify-center overflow-hidden">
                                        @if ($adaFoto)
                                            <img src="{{ asset('storage/' . $jaminan->foto_ktp) }}" alt="Foto KTP" class="w-full h-full object-contain cursor-zoom-in" @click="zoom = true">
                                        @else
                                            <div class="text-center px-4 text-gray-400">
                                                <i class="fas fa-id-card text-4xl mb-2 text-gray-300"></i>
                                                <p class="text-sm font-semibold">{{ $isFisik ? 'KTP fisik disimpan di toko' : 'Belum ada foto KTP' }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <dl class="space-y-4 text-sm">
                                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Nama Penyewa</dt><dd class="mt-1 font-bold text-ink">{{ $sewa->nama_pelanggan }}</dd></div>
                                        @if ($nikJaminan !== '')
                                            <div>
                                                <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Nomor NIK</dt>
                                                <dd class="mt-1 flex items-center gap-2">
                                                    <span class="font-mono font-bold text-ink tracking-wide" x-text="lihatNik ? nik : masked"></span>
                                                    <button type="button" @click="lihatNik = !lihatNik" class="text-[11px] font-bold text-gold hover:underline" x-text="lihatNik ? 'Sembunyikan' : 'Tampilkan'"></button>
                                                </dd>
                                            </div>
                                        @endif
                                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Metode Jaminan</dt><dd class="mt-1 font-semibold text-ink">{{ $isFisik ? 'KTP tatap muka' : 'Upload digital' }}</dd></div>
                                    </dl>
                                </div>
                            </div>
                            @unless ($verified)
                                <form action="{{ route('admin.sewa.verifikasi-jaminan', $sewa->id) }}" method="POST" class="border border-line rounded-xl p-6 space-y-4" x-data="{ ok: false }">
                                    @csrf
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                                        <input type="checkbox" name="konfirmasi_sesuai" value="1" x-model="ok" class="mt-1 w-4 h-4 text-gold rounded border-gray-300 focus:ring-gold">
                                        <span>
                                            <span class="block text-sm font-bold text-ink">Nama & NIK di KTP sudah sesuai dengan {{ $sewa->nama_pelanggan }}</span>
                                        </span>
                                    </label>
                                    @error('konfirmasi_sesuai')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                                    <button type="submit" :disabled="!ok" class="w-full py-3 bg-ink text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors flex items-center justify-center gap-2 disabled:cursor-not-allowed disabled:opacity-40">
                                        <i class="fas fa-check-circle"></i> Verifikasi Jaminan
                                    </button>
                                </form>
                            @endunless
                        </div>
                        <div class="space-y-6">
                            <div class="bg-white rounded-xl border border-line p-6">
                                <h3 class="text-sm font-bold text-ink flex items-center gap-2 mb-4"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> DP di Awal Sewa</h3>
                                @if ($dpDibayar > 0)
                                    <p class="text-3xl font-extrabold tracking-tight text-ink tabular-nums">Rp {{ number_format($dpDibayar, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $persenDp }}% dari total transaksi</p>
                                    <div class="mt-3 h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-blue-500" style="width: {{ min(100, $persenDp) }}%"></div></div>
                                    <dl class="mt-4 space-y-2 text-xs">
                                        <div class="flex justify-between gap-3"><dt class="text-gray-400">Metode</dt><dd class="font-semibold text-gray-800 uppercase">{{ $dpPertama->metode ?? '-' }}</dd></div>
                                        <div class="flex justify-between gap-3"><dt class="text-gray-400">Dibayar</dt><dd class="font-semibold text-gray-800 text-right">{{ $fmtTanggalBayar($dpPertama->tanggal_bayar ?? null) }}</dd></div>
                                    </dl>
                                @else
                                    <p class="text-sm font-semibold text-gray-600">Tidak ada DP.</p>
                                @endif
                                <div class="mt-4 pt-4 border-t border-line space-y-2 text-xs">
                                    <div class="flex justify-between"><span class="text-gray-400">Total dibayar</span><span class="font-bold text-emerald-700 tabular-nums">Rp {{ number_format($totalBayarJaminan, 0, ',', '.') }}</span></div>
                                    <div class="flex justify-between"><span class="text-gray-400">Sisa tagihan</span><span class="font-bold {{ $sisaTagihanJaminan > 0 ? 'text-rose-600' : 'text-gray-700' }} tabular-nums">Rp {{ number_format($sisaTagihanJaminan, 0, ',', '.') }}</span></div>
                                </div>
                            </div>
                            <div class="bg-sky-50/60 rounded-xl border border-sky-100 p-6">
                                <h3 class="text-sm font-bold text-sky-900 flex items-center gap-2 mb-3"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> Deposit Jaminan</h3>
                                <p class="text-2xl font-extrabold tracking-tight text-sky-950 tabular-nums">Rp {{ number_format((float) ($jaminan->jumlah_deposit ?? 0), 0, ',', '.') }}</p>
                                <p class="mt-2 text-xs font-semibold {{ ($jaminan->status_pengembalian_deposit ?? '') === 'dikembalikan' ? 'text-emerald-700' : 'text-sky-700' }}">{{ ($jaminan->status_pengembalian_deposit ?? '') === 'dikembalikan' ? 'Sudah dikembalikan' : 'Masih tertahan' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-line overflow-hidden">
                        <div class="px-6 py-4 border-b border-line"><h3 class="text-sm font-bold text-ink">Riwayat Pembayaran Transaksi</h3></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm divide-y divide-line">
                                <thead class="bg-gray-50/80">
                                    <tr>
                                        <th class="px-6 py-3 text-[11px] font-bold text-gray-400 uppercase">Jenis</th>
                                        <th class="px-6 py-3 text-[11px] font-bold text-gray-400 uppercase">Metode</th>
                                        <th class="px-6 py-3 text-[11px] font-bold text-gray-400 uppercase">Tanggal</th>
                                        <th class="px-6 py-3 text-right text-[11px] font-bold text-gray-400 uppercase">Jumlah</th>
                                        <th class="px-6 py-3 text-center text-[11px] font-bold text-gray-400 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line/80">
                                    @forelse ($semuaBayar->sortBy('tanggal_bayar') as $p)
                                        @php $bayarOk = in_array(strtolower((string) $p->status_midtrans), ['success', 'settlement', 'capture'], true); @endphp
                                        <tr>
                                            <td class="px-6 py-3 font-bold text-ink uppercase">{{ $p->jenis_bayar }}</td>
                                            <td class="px-6 py-3 text-gray-600 uppercase">{{ $p->metode ?? '-' }}</td>
                                            <td class="px-6 py-3 text-gray-600">{{ $fmtTanggalBayar($p->tanggal_bayar) }}</td>
                                            <td class="px-6 py-3 text-right font-bold tabular-nums text-ink">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                                            <td class="px-6 py-3 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $bayarOk ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200' }}">{{ $p->status_midtrans ?? '-' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada riwayat pembayaran.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($jaminan && $jaminan->foto_ktp)
                        <div x-show="zoom" x-cloak x-transition.opacity x-effect="document.body.style.overflow = zoom ? 'hidden' : ''" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6" @click="zoom = false" role="dialog">
                            <img src="{{ asset('storage/' . $jaminan->foto_ktp) }}" alt="Foto KTP" class="max-h-[90vh] max-w-[90vw] object-contain rounded-xl shadow-2xl" @click.stop>
                            <button type="button" @click="zoom = false" class="absolute right-6 top-6 h-9 w-9 rounded-full bg-white/90 text-gray-700 text-xl font-bold hover:bg-white" aria-label="Tutup">&times;</button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TAB 3: QC (LENGKAP DENGAN EDIT MODE) --}}
            <div x-show="tab === 'qc'" id="tab-qc" class="scroll-mt-24" x-cloak x-transition.opacity>
                <div class="flex items-center justify-between mb-6 border-b border-line pb-4"><h2 class="text-lg font-bold text-ink">Pengecekan Barang (QC)</h2></div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-5">
                        <form action="{{ route('admin.sewa.simpan-qc', $sewa->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <div class="bg-gray-50 p-4 rounded-xl border border-line mb-4">
                                <h3 class="font-bold text-sm text-ink">FORMULIR PENGECEKAN (QC)</h3>
                                <p class="text-xs text-gray-500">Catat kondisi fisik busana secara bebas. Jika ada kerusakan, tulis detailnya di catatan dan unggah foto.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">PILIHAN TAHAP QC</label>
                                <select name="tahap" class="w-full px-3 py-2.5 border border-line rounded-lg text-sm bg-white" required>
                                    <option value="sebelum_cuci">Sebelum Cuci / Baru Diterima dari Pelanggan</option>
                                    <option value="setelah_cuci">Setelah Cuci / Siap Disewakan Kembali</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">CATATAN KONDISI</label>
                                <textarea name="catatan" rows="4" class="w-full px-3 py-2 border border-line rounded-lg text-sm" placeholder="Contoh: Ada noda kuning di lengan kanan..." required></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">FOTO BUKTI (Opsional)</label>
                                <input type="file" name="foto_bukti" accept="image/*" class="w-full text-sm">
                            </div>
                            <button type="submit" class="w-full py-3 bg-ink text-white rounded-lg text-sm font-bold hover:bg-gray-800">Simpan Catatan QC</button>
                        </form>
                    </div>
                    <div class="bg-cream/30 p-5 rounded-xl border border-line h-fit">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-sm text-ink">RIWAYAT PENGECEKAN</h4>
                            <span class="text-[10px] bg-white border border-line px-2 py-1 rounded text-gray-500 font-bold">{{ $sewa->pengecekan->count() }} Entri</span>
                        </div>
                        <div class="relative border-l-2 border-gold/30 ml-2 space-y-8">
                            @forelse($sewa->pengecekan->sortByDesc('created_at') as $qc)
                                <div class="relative pl-6" x-data="{ editMode: false }">
                                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-white border-2 {{ $qc->tahap === 'sebelum_cuci' ? 'border-rose-400' : 'border-emerald-400' }}"></div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-bold {{ $qc->tahap === 'sebelum_cuci' ? 'text-rose-600 bg-rose-50' : 'text-emerald-600 bg-emerald-50' }} px-2 py-0.5 rounded uppercase">{{ str_replace('_', ' ', $qc->tahap) }}</span>
                                        <div class="flex gap-1">
                                            <button @click="editMode = !editMode" class="text-gray-400 hover:text-gold transition-colors" title="Edit"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                            <form action="{{ route('admin.sewa.hapus-qc', $qc->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus catatan QC ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-rose-600 transition-colors" title="Hapus"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                            </form>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-500 mb-2">{{ $qc->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                                    <div x-show="!editMode" class="bg-white p-3 rounded-lg border border-line text-xs text-gray-600 italic">"{{ $qc->catatan }}"</div>
                                    <div x-show="!editMode" class="flex items-center justify-between mt-2">
                                        <p class="text-[10px] text-gray-500 flex items-center gap-1"><i class="fas fa-user-check"></i> Petugas: {{ $qc->petugas }}</p>
                                        @if ($qc->foto_bukti)
                                            <a href="{{ asset('storage/' . $qc->foto_bukti) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-gold hover:underline"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>Lihat Foto</a>
                                        @endif
                                    </div>
                                    <div x-show="editMode" x-cloak class="bg-gray-50 p-3 rounded-lg border border-line">
                                        <form action="{{ route('admin.sewa.update-qc', $qc->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf @method('PUT')
                                            <select name="tahap" class="w-full mb-2 px-2 py-1 border border-line rounded text-xs">
                                                <option value="sebelum_cuci" {{ $qc->tahap === 'sebelum_cuci' ? 'selected' : '' }}>Sebelum Cuci</option>
                                                <option value="setelah_cuci" {{ $qc->tahap === 'setelah_cuci' ? 'selected' : '' }}>Setelah Cuci</option>
                                            </select>
                                            <textarea name="catatan" rows="2" class="w-full mb-2 px-2 py-1 border border-line rounded text-xs" required>{{ $qc->catatan }}</textarea>
                                            <input type="file" name="foto_bukti" accept="image/*" class="w-full mb-2 text-xs">
                                            <div class="flex gap-2">
                                                <button type="submit" class="px-2 py-1 bg-gold text-white text-[10px] font-bold rounded">Simpan</button>
                                                <button type="button" @click="editMode = false" class="px-2 py-1 bg-gray-200 text-gray-700 text-[10px] font-bold rounded">Batal</button>
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

            {{-- TAB 4: DENDA (LENGKAP DENGAN SETTLEMENT KALKULASI & MODAL) --}}
            <div x-show="tab === 'denda'" id="tab-denda" class="scroll-mt-24" x-cloak x-transition.opacity>
                <div class="flex items-center justify-between mb-6 border-b border-line pb-4">
                    <div>
                        <h2 class="text-lg font-bold text-ink">Rincian Denda & Penyelesaian</h2>
                        <p class="text-xs text-gray-500 mt-1">Kelola penalti keterlambatan, kerusakan, dan pengembalian deposit.</p>
                    </div>
                    <button onclick="document.getElementById('form-denda').classList.toggle('hidden')" class="px-4 py-2 bg-ink text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Denda
                    </button>
                </div>

                <form id="form-denda" action="{{ route('admin.sewa.tambah-denda', $sewa->id) }}" method="POST" class="hidden mb-6 p-4 bg-gray-50 border border-line rounded-xl grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">KETERANGAN DENDA</label>
                        <input type="text" name="jenis" class="w-full px-3 py-2 border border-line rounded-lg text-sm" placeholder="Cth: Noda Oli / Kancing Lepas" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">NOMINAL (Rp)</label>
                        <input type="text" name="jumlah" inputmode="numeric" oninput="formatRupiah(this)" class="w-full px-3 py-2 border border-line rounded-lg text-sm" placeholder="50.000" required>
                    </div>
                    <button type="submit" class="py-2 bg-gold text-white rounded-lg text-sm font-bold">Simpan Denda</button>
                </form>

                <div class="mb-6 rounded-xl border {{ $dendaTelatOtomatis || $hariTelatLive > 0 ? 'border-rose-200 bg-rose-50/50' : 'border-line bg-gray-50' }} p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="text-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Denda Keterlambatan Otomatis</p>
                        @if ($tarifTelat <= 0)
                            <p class="font-semibold text-gray-700">Tarif denda belum diatur.</p>
                        @elseif ($sewa->status !== 'selesai' && $hariTelatLive > 0)
                            <p class="font-bold text-rose-700">Terlambat {{ $hariTelatLive }} hari = Rp {{ number_format($estimasiDendaTelat, 0, ',', '.') }}</p>
                        @elseif ($dendaTelatOtomatis)
                            <p class="font-bold text-rose-700">Terlambat {{ $dendaTelatOtomatis->jumlah_hari }} hari = Rp {{ number_format($dendaTelatOtomatis->jumlah, 0, ',', '.') }}</p>
                        @else
                            <p class="font-semibold text-emerald-700">Tidak ada keterlambatan.</p>
                        @endif
                    </div>
                    @if ($sewa->status !== 'selesai' && $tarifTelat > 0)
                        <form action="{{ route('admin.sewa.sinkron-denda-telat', $sewa->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-white border border-line rounded-lg text-xs font-bold hover:bg-gray-50"><i class="fas fa-rotate mr-1"></i> Hitung Ulang</button>
                        </form>
                    @endif
                </div>

                <div class="bg-white border border-line rounded-xl overflow-hidden mb-6">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-line text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-4">Keterangan</th>
                                <th class="px-6 py-4 text-right">Nominal</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse($sewa->denda as $denda)
                                <tr>
                                    <td class="px-6 py-4 font-bold text-ink">{{ $denda->jenis }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-rose-600">Rp {{ number_format($denda->jumlah, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($denda->status === 'lunas')
                                            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded text-[10px] font-bold">LUNAS</span>
                                        @else
                                            <span class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded text-[10px] font-bold">BELUM LUNAS</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                                        @if ($denda->status !== 'lunas')
                                            <form action="{{ route('admin.sewa.lunasi-denda', $denda->id) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="px-3 py-1.5 border border-line rounded text-xs font-semibold hover:bg-gray-50">Lunas</button>
                                            </form>
                                        @endif
                                        
                                        <button type="button" @click="hapusDenda = true; urlHapus = '{{ route('admin.sewa.hapus-denda', $denda->id) }}'" class="px-3 py-1.5 border border-rose-200 text-rose-600 rounded text-xs font-semibold hover:bg-rose-50">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada denda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-emerald-50/30 border border-emerald-100 rounded-xl p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded">SETTLEMENT DEPOSIT</span>
                            </div>
                            <h3 class="text-xl font-bold text-ink mb-3">Rincian Pengembalian Deposit</h3>
                            <div class="max-w-md bg-white rounded-lg p-4 border border-emerald-100 space-y-2 text-sm shadow-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Deposit Awal:</span>
                                    <span class="font-bold">Rp {{ number_format($depositAwal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Denda:</span>
                                    <span class="font-bold text-rose-600">- Rp {{ number_format($totalDendaKotor, 0, ',', '.') }}</span>
                                </div>
                                <div class="border-t border-dashed border-gray-200 my-1"></div>
                                <div class="flex justify-between text-base">
                                    <span class="font-bold text-gray-700">Sisa Dikembalikan:</span>
                                    <span class="font-black text-emerald-600">Rp {{ number_format($sisaDeposit, 0, ',', '.') }}</span>
                                </div>
                                @if($kekuranganDenda > 0)
                                <div class="flex justify-between text-xs mt-1 text-rose-600 font-bold">
                                    <span>Kekurangan Denda:</span>
                                    <span>Rp {{ number_format($kekuranganDenda, 0, ',', '.') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-right shrink-0 w-full md:w-auto">
                            <p class="text-xs text-gray-500 mb-1">STATUS:</p>
                            <p class="text-sm font-bold mb-3 {{ $flowDeposit ? 'text-emerald-700' : 'text-ink' }}">
                                @if ($flowDeposit) Selesai Dikembalikan
                                @elseif (!$flowDiterima) Menunggu Barang Kembali
                                @elseif ($kekuranganDenda > 0) Menunggu Pelunasan Kekurangan
                                @else Siap Dikembalikan
                                @endif
                            </p>
                            <form action="{{ route('admin.sewa.kembalikan-deposit', $sewa->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full px-5 py-3 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition shadow-sm disabled:opacity-40 disabled:cursor-not-allowed" {{ !($flowDiterima && $kekuranganDenda <= 0 && !$flowDeposit) ? 'disabled' : '' }}>
                                    <i class="fas fa-money-bill-wave mr-1"></i> Proses Settlement & Kembalikan Sisa Deposit
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL KONFIRMASI HAPUS DENDA --}}
        <div x-show="hapusDenda" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="hapusDenda = false" x-transition.opacity></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100">
                        <i class="fas fa-trash-alt text-2xl"></i>
                    </div>
                    <h3 class="font-display text-xl font-bold text-gray-900 mb-2">Hapus Denda?</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Apakah Anda yakin ingin menghapus data denda ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-3">
                    <button type="button" @click="hapusDenda = false" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-100 transition-colors">Batal</button>
                    <form :action="urlHapus" method="POST" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold transition-colors shadow-md shadow-rose-500/20">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function formatRupiah(input) {
        let value = input.value.replace(/\D/g, '');
        if (value !== '') {
            value = parseInt(value, 10).toLocaleString('id-ID');
        }
        input.value = value;
    }
    </script>
@endsection