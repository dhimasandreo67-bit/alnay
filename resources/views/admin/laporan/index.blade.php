@extends('admin.layouts.admin')

@section('title', 'Laporan Pendapatan')

@section('content')

    <style>
        @media print {
            aside, header, .no-print { display: none !important; }
            body { display: block !important; background: #fff !important; }
            main { padding: 0 !important; }
            .break-inside-avoid { break-inside: avoid; }
        }
    </style>

    @php
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $pct = fn ($n) => number_format(abs($n), 1, ',', '.') . '%';

        $labelPeriode = $f['dari']->equalTo($f['sampai'])
            ? $f['dari']->translatedFormat('d M Y')
            : $f['dari']->translatedFormat('d M Y') . ' – ' . $f['sampai']->translatedFormat('d M Y');

        $tema = [
            'amber'   => ['box' => 'border-amber-200/60 from-amber-50/80 via-white to-amber-100/30 hover:shadow-amber-500/10',     'label' => 'text-amber-800/80',   'nilai' => 'text-amber-950',   'sub' => 'text-amber-700/80',   'ikon' => 'from-amber-500 to-amber-400 shadow-amber-500/20'],
            'emerald' => ['box' => 'border-emerald-200/60 from-emerald-50/80 via-white to-emerald-100/30 hover:shadow-emerald-500/10', 'label' => 'text-emerald-800/80', 'nilai' => 'text-emerald-950', 'sub' => 'text-emerald-700/80', 'ikon' => 'from-emerald-500 to-teal-500 shadow-emerald-500/20'],
            'sky'     => ['box' => 'border-sky-200/60 from-sky-50/80 via-white to-sky-100/30 hover:shadow-sky-500/10',             'label' => 'text-sky-800/80',     'nilai' => 'text-sky-950',     'sub' => 'text-sky-700/80',     'ikon' => 'from-sky-500 to-blue-500 shadow-sky-500/20'],
            'rose'    => ['box' => 'border-rose-200/60 from-rose-50/80 via-white to-rose-100/30 hover:shadow-rose-500/10',         'label' => 'text-rose-800/80',    'nilai' => 'text-rose-950',    'sub' => 'text-rose-700/80',    'ikon' => 'from-rose-500 to-pink-500 shadow-rose-500/20'],
            'orange'  => ['box' => 'border-orange-200/60 from-orange-50/80 via-white to-orange-100/30 hover:shadow-orange-500/10', 'label' => 'text-orange-800/80',  'nilai' => 'text-orange-950',  'sub' => 'text-orange-700/80',  'ikon' => 'from-orange-500 to-amber-500 shadow-orange-500/20'],
            'slate'   => ['box' => 'border-slate-200/70 from-slate-50/80 via-white to-slate-100/40 hover:shadow-slate-500/10',     'label' => 'text-slate-700/80',   'nilai' => 'text-slate-900',   'sub' => 'text-slate-600/80',   'ikon' => 'from-slate-600 to-slate-500 shadow-slate-500/20'],
        ];

        $kartu = [
            [
                'warna' => 'amber', 'judul' => 'Pendapatan Bersih',
                'nilai' => $rp($ringkas['bersih']), 'delta' => $banding['bersih'],
                'sub'   => ($f['hitung_refund_denda'] ? 'Diterima + denda − refund' : 'Uang diterima (tanpa refund & denda)'),
                'ikon'  => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'warna' => 'emerald', 'judul' => 'Uang Diterima',
                'nilai' => $rp($ringkas['diterima']), 'delta' => $banding['diterima'],
                'sub'   => 'Nilai transaksi ' . $rp($ringkas['nilai']),
                'ikon'  => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
            ],
            [
                'warna' => 'sky', 'judul' => 'Sisa Tagihan (DP)',
                'nilai' => $rp($ringkas['sisa']), 'delta' => null,
                'sub'   => $ringkas['belum_lunas'] . ' transaksi belum lunas',
                'ikon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'warna' => 'rose', 'judul' => 'Refund',
                'nilai' => $ringkas['refund'] === null ? '—' : '− ' . $rp($ringkas['refund']), 'delta' => null,
                'sub'   => $ringkas['refund'] === null ? 'Tidak dihitung saat filter metode/kategori aktif' : 'Refund yang sudah diproses',
                'ikon'  => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
            ],
            [
                'warna' => 'orange', 'judul' => 'Denda Terbayar',
                'nilai' => $ringkas['denda'] === null ? '—' : '+ ' . $rp($ringkas['denda']), 'delta' => null,
                'sub'   => $ringkas['denda'] === null ? 'Tidak dihitung saat filter metode/kategori aktif' : 'Denda sewa yang sudah lunas',
                'ikon'  => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            ],
            [
                'warna' => 'slate', 'judul' => 'Jumlah Transaksi',
                'nilai' => number_format($ringkas['jumlah'], 0, ',', '.'), 'delta' => $banding['jumlah'],
                'sub'   => 'Beli ' . $ringkas['jumlah_beli'] . ' · Sewa ' . $ringkas['jumlah_sewa'] . ' · Rata-rata ' . $rp($ringkas['rata_rata']),
                'ikon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
            ],
        ];

        $kelasSelect = 'px-4 py-2.5 bg-cream/50 border border-transparent rounded-xl text-sm font-semibold text-ink focus:outline-none focus:ring-2 focus:ring-gold/20 focus:border-gold transition-all duration-300 cursor-pointer';
        $kelasTh = 'whitespace-nowrap px-4 py-3 text-left text-[10px] font-bold text-ink/60 uppercase tracking-wide';
        $kelasThKanan = 'whitespace-nowrap px-4 py-3 text-right text-[10px] font-bold text-ink/60 uppercase tracking-wide';
    @endphp

    <div class="p-4 lg:p-6 space-y-8 animate-fade-up">

        {{-- ===================== HEADER ===================== --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <nav class="text-xs text-muted font-bold tracking-wider uppercase mb-1.5 flex items-center gap-2">
                    <span>Transaksi</span> <span class="text-gold">/</span> <span class="text-ink">Laporan Pendapatan</span>
                </nav>
                <h1 class="text-3xl font-display font-bold tracking-tight text-ink">Laporan Pendapatan</h1>
                <p class="text-sm text-muted mt-1">
                    Ringkasan pemasukan kasir &amp; online · <strong class="text-ink">{{ $labelPeriode }}</strong>
                </p>
            </div>

            <div class="no-print flex items-center gap-3">
                <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold text-sm hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak (PDF)
                </button>
            </div>
        </div>

        {{-- ===================== FILTER ===================== --}}
        <form method="GET" action="{{ route('admin.laporan.index') }}"
              class="no-print bg-panel p-3 rounded-2xl border border-line shadow-sm flex flex-wrap items-center gap-3">

            <select name="periode" onchange="gantiPeriode(this)" class="{{ $kelasSelect }}">
                @foreach (['hari_ini' => 'Hari ini', '7_hari' => '7 hari terakhir', '30_hari' => '30 hari terakhir', 'bulan_ini' => 'Bulan ini', 'bulan_lalu' => 'Bulan lalu', 'tahun_ini' => 'Tahun ini', 'kustom' => 'Rentang kustom…'] as $v => $l)
                    <option value="{{ $v }}" @selected($f['periode'] === $v)>{{ $l }}</option>
                @endforeach
            </select>

            <div id="rentangKustom" class="{{ $f['periode'] === 'kustom' ? 'flex' : 'hidden' }} items-center gap-2">
                <input type="date" name="dari" value="{{ $f['dari']->toDateString() }}" class="{{ $kelasSelect }}">
                <span class="text-xs text-muted font-bold">s/d</span>
                <input type="date" name="sampai" value="{{ $f['sampai']->toDateString() }}" class="{{ $kelasSelect }}">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-ink text-white text-sm font-bold hover:opacity-90 active:scale-95 transition-all cursor-pointer">Terapkan</button>
            </div>

            <select name="channel" onchange="this.form.submit()" class="{{ $kelasSelect }}">
                <option value="">Semua Kanal</option>
                <option value="kasir" @selected($f['channel'] === 'kasir')>Kasir (Offline)</option>
                <option value="online" @selected($f['channel'] === 'online')>Online</option>
            </select>

            <select name="jenis" onchange="this.form.submit()" class="{{ $kelasSelect }}">
                <option value="">Semua Jenis</option>
                <option value="beli" @selected($f['jenis'] === 'beli')>Penjualan</option>
                <option value="sewa" @selected($f['jenis'] === 'sewa')>Sewa</option>
            </select>

            <select name="metode" onchange="this.form.submit()" class="{{ $kelasSelect }}">
                <option value="">Semua Metode Bayar</option>
                @foreach ($opsiMetode as $m)
                    <option value="{{ $m }}" @selected($f['metode'] === $m)>{{ strtolower($m) === 'qris' ? 'QRIS' : \Illuminate\Support\Str::headline($m) }}</option>
                @endforeach
            </select>

            <select name="kategori_id" onchange="this.form.submit()" class="{{ $kelasSelect }}">
                <option value="">Semua Kategori</option>
                @foreach ($opsiKategori as $k)
                    <option value="{{ $k->id }}" @selected($f['kategori_id'] === $k->id)>{{ $k->nama_kategori }}</option>
                @endforeach
            </select>

            @if ($f['periode'] !== 'bulan_ini' || $f['channel'] || $f['jenis'] || $f['metode'] || $f['kategori_id'])
                <a href="{{ route('admin.laporan.index') }}" class="text-xs font-bold text-rose-500 hover:text-rose-700 hover:underline whitespace-nowrap">⟲ Reset</a>
            @endif
        </form>

        @if (! $f['hitung_refund_denda'])
            <div class="no-print flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded-2xl text-xs">
                <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>Filter <strong>metode bayar / kategori</strong> sedang aktif. Refund dan denda tidak ikut dihitung karena datanya tidak terhubung ke metode atau kategori, jadi pendapatan bersih di bawah sama dengan uang diterima.</p>
            </div>
        @endif

        {{-- ===================== KARTU RINGKASAN ===================== --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($kartu as $c)
                @php $t = $tema[$c['warna']]; @endphp
                <div class="break-inside-avoid group relative overflow-hidden rounded-2xl border bg-gradient-to-br {{ $t['box'] }} p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider {{ $t['label'] }}">{{ $c['judul'] }}</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr {{ $t['ikon'] }} text-white shadow-md transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c['ikon'] }}"/></svg>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <span class="text-2xl font-extrabold tracking-tight {{ $t['nilai'] }}">{{ $c['nilai'] }}</span>

                        @if (array_key_exists('delta', $c) && $c['delta'] !== null)
                            <span class="inline-flex items-center gap-1 rounded-lg px-2 py-0.5 text-[11px] font-bold {{ $c['delta'] >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}"
                                  title="Dibanding periode sebelumnya dengan lama yang sama">
                                {{ $c['delta'] >= 0 ? '▲' : '▼' }} {{ $pct($c['delta']) }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-1.5 text-xs font-medium {{ $t['sub'] }}">{{ $c['sub'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ===================== TREN ===================== --}}
        <div class="break-inside-avoid bg-panel rounded-3xl shadow-sm border border-line p-5 lg:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-base font-bold text-ink">Tren Pendapatan {{ $grafik['tren']['mode'] === 'bulanan' ? 'per Bulan' : 'per Hari' }}</h2>
                    <p class="text-xs text-muted mt-0.5">Uang diterima, dipisah kasir &amp; online, plus jumlah transaksi</p>
                </div>
            </div>
            <div class="relative h-80" id="wrapTren"><canvas id="chartTren"></canvas></div>
        </div>

        {{-- ===================== KOMPOSISI ===================== --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="break-inside-avoid bg-panel rounded-3xl shadow-sm border border-line p-5 lg:p-6">
                <h2 class="text-base font-bold text-ink">Kasir vs Online</h2>
                <p class="text-xs text-muted mt-0.5 mb-4">Porsi uang diterima per kanal</p>
                <div class="relative h-64"><canvas id="chartKanal"></canvas></div>
            </div>

            <div class="break-inside-avoid bg-panel rounded-3xl shadow-sm border border-line p-5 lg:p-6">
                <h2 class="text-base font-bold text-ink">Penjualan vs Sewa</h2>
                <p class="text-xs text-muted mt-0.5 mb-4">Porsi uang diterima per jenis transaksi</p>
                <div class="relative h-64"><canvas id="chartJenis"></canvas></div>
            </div>

            <div class="break-inside-avoid bg-panel rounded-3xl shadow-sm border border-line p-5 lg:p-6">
                <h2 class="text-base font-bold text-ink">Metode Pembayaran</h2>
                <p class="text-xs text-muted mt-0.5 mb-4">Uang masuk berdasarkan metode bayar</p>
                <div class="relative h-64"><canvas id="chartMetode"></canvas></div>
            </div>
        </div>

        {{-- ===================== PRODUK & KATEGORI ===================== --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

            {{-- Produk terlaris --}}
            <div class="break-inside-avoid xl:col-span-2 bg-panel rounded-3xl shadow-sm border border-line overflow-hidden">
                <div class="px-5 lg:px-6 pt-5 pb-3">
                    <h2 class="text-base font-bold text-ink">Produk Terlaris</h2>
                    <p class="text-xs text-muted mt-0.5">10 produk dengan omzet tertinggi (harga satuan × jumlah)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px] divide-y divide-line">
                        <thead class="bg-cream/80">
                            <tr>
                                <th class="{{ $kelasTh }} w-10">#</th>
                                <th class="{{ $kelasTh }}">Produk</th>
                                <th class="{{ $kelasTh }}">Kategori</th>
                                <th class="{{ $kelasThKanan }}">Terjual/Disewa</th>
                                <th class="{{ $kelasThKanan }}">Omzet</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line/50 bg-panel">
                            @forelse ($produkTerlaris as $i => $p)
                                <tr class="hover:bg-cream/40 transition-colors">
                                    <td class="px-4 py-3 text-xs font-bold text-muted">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-ink">{{ $p->nama }}</p>
                                        <p class="text-[11px] font-mono text-muted">{{ $p->sku }}</p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-block whitespace-nowrap bg-ink/5 text-ink rounded-full px-2.5 py-0.5 text-[11px] font-semibold">{{ $p->nama_kategori ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap font-bold text-ink">{{ number_format($p->qty, 0, ',', '.') }} <span class="text-xs font-normal text-muted">unit</span></td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap font-bold text-gold">{{ $rp($p->omzet) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-12 text-center text-sm font-medium text-muted">Belum ada penjualan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Kategori --}}
            <div class="break-inside-avoid bg-panel rounded-3xl shadow-sm border border-line p-5 lg:p-6">
                <h2 class="text-base font-bold text-ink">Omzet per Kategori</h2>
                <p class="text-xs text-muted mt-0.5 mb-4">Kontribusi tiap kategori terhadap omzet</p>

                <div class="space-y-4">
                    @forelse ($kategoriTerlaris as $k)
                        @php $porsi = $totalOmzet > 0 ? ($k['omzet'] / $totalOmzet) * 100 : 0; @endphp
                        <div>
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="text-sm font-semibold text-ink truncate">{{ $k['nama'] }}</span>
                                <span class="text-xs font-bold text-ink whitespace-nowrap">{{ $rp($k['omzet']) }}</span>
                            </div>
                            <div class="mt-1.5 h-2 w-full rounded-full bg-cream overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-amber-400" style="width: {{ number_format($porsi, 1, '.', '') }}%"></div>
                            </div>
                            <p class="mt-1 text-[11px] text-muted">{{ number_format($porsi, 1, ',', '.') }}% · {{ number_format($k['qty'], 0, ',', '.') }} unit</p>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm font-medium text-muted">Belum ada data kategori.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ===================== RINCIAN TRANSAKSI ===================== --}}
        <div class="bg-panel rounded-3xl shadow-sm border border-line overflow-hidden">
            <div class="px-5 lg:px-6 pt-5 pb-3 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-ink">Rincian Transaksi</h2>
                    <p class="text-xs text-muted mt-0.5">{{ number_format($rincian->total(), 0, ',', '.') }} transaksi sesuai filter</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px] divide-y divide-line">
                    <thead class="bg-cream/80">
                        <tr>
                            <th class="{{ $kelasTh }}">Kode</th>
                            <th class="{{ $kelasTh }}">Tanggal</th>
                            <th class="{{ $kelasTh }}">Pelanggan</th>
                            <th class="{{ $kelasTh }}">Kanal</th>
                            <th class="{{ $kelasTh }}">Jenis</th>
                            <th class="{{ $kelasTh }}">Status</th>
                            <th class="{{ $kelasThKanan }}">Total</th>
                            <th class="{{ $kelasThKanan }}">Diterima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line/50 bg-panel">
                        @forelse ($rincian as $t)
                            <tr class="hover:bg-cream/40 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('admin.transaksi.show', $t->id) }}" class="font-mono text-[11px] font-bold text-ink hover:text-gold transition-colors">{{ $t->kode_transaksi }}</a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-muted">{{ $t->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 font-semibold text-ink">{{ $t->user->name ?? $t->nama_tamu ?? 'Tamu' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-block whitespace-nowrap rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $t->channel === 'kasir' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-sky-700' }}">{{ $t->channel === 'kasir' ? 'Kasir' : 'Online' }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-ink/80">{{ $t->jenis === 'sewa' ? 'Sewa' : 'Beli' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap border rounded-lg px-2 py-0.5 text-[10px] font-bold {{ $t->status_pembayaran === 'lunas' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                        {{ $t->status_pembayaran === 'lunas' ? 'Lunas' : 'DP' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-ink/80">{{ $rp($t->total) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-ink">{{ $rp($t->diterima) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-14 text-center text-sm font-medium text-muted">Tidak ada transaksi pada periode dan filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rincian->hasPages())
                <div class="no-print px-5 lg:px-6 py-4 border-t border-line">{{ $rincian->links() }}</div>
            @endif
        </div>

        {{-- ===================== CATATAN ===================== --}}
        <details class="no-print bg-panel rounded-2xl border border-line px-5 py-4 text-xs text-muted">
            <summary class="cursor-pointer font-bold text-ink select-none">Bagaimana angka di laporan ini dihitung?</summary>
            <ul class="mt-3 space-y-1.5 list-disc pl-5 leading-relaxed">
                <li>Periode mengikuti <strong>tanggal transaksi</strong>. Transaksi berstatus <em>pending</em> (belum dibayar) tidak dihitung.</li>
                <li><strong>Uang diterima</strong>: transaksi lunas dihitung penuh; transaksi DP dihitung sebesar pembayaran yang sudah masuk.</li>
                <li><strong>Pendapatan bersih</strong> = uang diterima + denda yang sudah dibayar − refund yang sudah diproses (refund pending/ditolak tidak mengurangi).</li>
                <li><strong>Sisa tagihan</strong> = nilai transaksi DP yang belum dilunasi. Uang jaminan/deposit sewa tidak dihitung sebagai pendapatan karena dikembalikan.</li>
                <li><strong>Omzet produk &amp; kategori</strong> adalah harga satuan × jumlah dari item transaksi (nilai transaksi, bukan uang yang sudah diterima).</li>
                <li>Persentase di kartu membandingkan dengan periode sebelumnya yang lamanya sama.</li>
            </ul>
        </details>
    </div>

    {{-- ===================== SCRIPT ===================== --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        function gantiPeriode(sel) {
            const kustom = sel.value === 'kustom';
            const box = document.getElementById('rentangKustom');
            box.classList.toggle('hidden', !kustom);
            box.classList.toggle('flex', kustom);
            if (!kustom) sel.form.submit();
        }

        (function () {
            const DATA = @json($grafik);
            const WARNA = { kasir: '#d97706', online: '#0ea5e9', garis: '#1f2937', donat: ['#d97706', '#0ea5e9', '#10b981', '#f43f5e', '#8b5cf6', '#64748b'] };

            const rp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
            const singkat = v => {
                const a = Math.abs(v);
                if (a >= 1e9) return (v / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M';
                if (a >= 1e6) return (v / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
                if (a >= 1e3) return (v / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb';
                return v;
            };
            const kosong = el => {
                el.parentElement.innerHTML = '<div class="flex h-full items-center justify-center text-sm font-medium text-muted">Belum ada data pada periode ini.</div>';
            };
            const jumlah = arr => arr.reduce((a, b) => a + b, 0);

            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#6b7280';

            // ---- Tren ----
            const tren = DATA.tren;
            const elTren = document.getElementById('chartTren');
            if (jumlah(tren.kasir) + jumlah(tren.online) === 0) {
                kosong(elTren);
            } else {
                new Chart(elTren, {
                    data: {
                        labels: tren.labels,
                        datasets: [
                            { type: 'bar', label: 'Kasir', data: tren.kasir, backgroundColor: WARNA.kasir, borderRadius: 4, stack: 'uang', yAxisID: 'y', order: 2 },
                            { type: 'bar', label: 'Online', data: tren.online, backgroundColor: WARNA.online, borderRadius: 4, stack: 'uang', yAxisID: 'y', order: 2 },
                            { type: 'line', label: 'Jumlah transaksi', data: tren.jumlah, borderColor: WARNA.garis, backgroundColor: WARNA.garis, borderWidth: 2, pointRadius: 2, tension: 0.3, yAxisID: 'y1', order: 1 },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                            tooltip: {
                                callbacks: {
                                    label: c => ' ' + c.dataset.label + ': ' + (c.dataset.yAxisID === 'y1' ? c.parsed.y + ' transaksi' : rp(c.parsed.y)),
                                },
                            },
                        },
                        scales: {
                            x: { stacked: true, grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 16 } },
                            y: { stacked: true, beginAtZero: true, ticks: { callback: singkat }, grid: { color: 'rgba(0,0,0,0.05)' } },
                            y1: { position: 'right', beginAtZero: true, ticks: { precision: 0 }, grid: { drawOnChartArea: false } },
                        },
                    },
                });
            }

            // ---- Donat ----
            function donat(id, labels, values, warna) {
                const el = document.getElementById(id);
                const total = jumlah(values);
                if (!total) return kosong(el);

                new Chart(el, {
                    type: 'doughnut',
                    data: { labels, datasets: [{ data: values, backgroundColor: warna, borderWidth: 0 }] },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                            tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + rp(c.parsed) + ' (' + Math.round(c.parsed / total * 100) + '%)' } },
                        },
                    },
                });
            }

            donat('chartKanal', DATA.kanal.labels, DATA.kanal.values, [WARNA.kasir, WARNA.online]);
            donat('chartJenis', DATA.jenis.labels, DATA.jenis.values, ['#10b981', '#8b5cf6']);

            // ---- Metode bayar ----
            const elMetode = document.getElementById('chartMetode');
            if (!jumlah(DATA.metode.values)) {
                kosong(elMetode);
            } else {
                new Chart(elMetode, {
                    type: 'bar',
                    data: {
                        labels: DATA.metode.labels,
                        datasets: [{ data: DATA.metode.values, backgroundColor: WARNA.donat, borderRadius: 6 }],
                    },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: c => ' ' + rp(c.parsed.x) } },
                        },
                        scales: {
                            x: { beginAtZero: true, ticks: { callback: singkat }, grid: { color: 'rgba(0,0,0,0.05)' } },
                            y: { grid: { display: false } },
                        },
                    },
                });
            }
        })();
    </script>
@endsection