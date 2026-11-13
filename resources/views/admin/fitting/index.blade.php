@extends('admin.layouts.admin')
@section('title', 'Reservasi Fitting')

@section('content')
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    $urlFoto = function ($produk) {
        $foto = $produk->foto_produk ?? null;

        if (is_string($foto)) {
            $dec = json_decode($foto, true);
            if (is_array($dec)) {
                $foto = $dec;
            }
        }
        if (is_array($foto)) {
            $foto = $foto[0] ?? null;
        }
        if (! $foto) {
            return null;
        }

        $foto = (string) $foto;
        if (Str::startsWith($foto, ['http://', 'https://', 'data:'])) {
            return $foto;
        }

        $foto = ltrim(preg_replace('#^(public/|storage/)#', '', ltrim($foto, '/')), '/');

        return asset('storage/' . $foto);
    };

    $status = $status ?? 'semua';
    $adaFilter = filled(request('search')) || filled(request('tanggal')) || $status !== 'semua';
@endphp

<div class="space-y-6 transition-all duration-500 animate-fade-in relative">

    {{-- CONTAINER TOAST NOTIFIKASI (Dari kode Anda) --}}
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>

    {{-- Header & Tombol Hapus Massal (Dari kode Anda) --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Reservasi Fitting</h1>
            <p class="text-xs text-gray-500 mt-1">Jadwal kedatangan pelanggan untuk fitting di toko ALNAY. Booking langsung terjadwal, tugas admin hanya menandai Hadir atau Batal.</p>
        </div>
        
        <button id="btnBulkDelete" class="hidden bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-rose-500/20 active:scale-95 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            Hapus Terpilih (<span id="countSelected">0</span>)
        </button>
    </div>

    {{-- Filter: otomatis, tanpa tombol Cari (Gabungan) --}}
    <form id="form-filter" method="GET" action="{{ route('admin.fitting.index') }}" class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <input type="hidden" id="input-status" name="status" value="{{ $status }}">
        <div class="relative w-full sm:w-96">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input id="input-cari" type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                   placeholder="Cari kode booking, nama, atau nomor HP..."
                   class="w-full pl-10 pr-10 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
            <span id="spinner" class="hidden absolute inset-y-0 right-0 items-center pr-3.5">
                <svg class="w-4 h-4 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            </span>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <input id="input-tanggal" type="date" name="tanggal" value="{{ request('tanggal') }}"
                   class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all cursor-pointer">
            <button type="button" id="btn-reset" class="text-xs text-gray-500 hover:text-amber-600 font-bold ml-2 transition-colors cursor-pointer">Reset</button>
        </div>
    </form>

    {{-- Atur ketersediaan: tutup tanggal / jam (Dari Teman Anda) --}}
    @if (\Illuminate\Support\Facades\Route::has('admin.fitting.slot.tutup'))
        @php $slotTutup = $slotTutup ?? collect(); $jamSlot = $jamSlot ?? []; @endphp
        <details class="bg-white rounded-3xl border border-gray-100 shadow-sm" {{ ($errors->any() || session('buka_panel')) ? 'open' : '' }}>
            <summary class="cursor-pointer list-none px-6 py-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-gray-900">Atur Ketersediaan Jadwal</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">Tutup tanggal atau jam tertentu jika staf berhalangan, toko libur, atau ada keperluan lain.</p>
                </div>
                <span class="shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $slotTutup->isNotEmpty() ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $slotTutup->flatten()->count() }} jam ditutup
                </span>
            </summary>

            <div class="px-6 pb-6 pt-5 border-t border-gray-100 grid lg:grid-cols-2 gap-8">
                <form id="form-tutup" method="POST" action="{{ route('admin.fitting.slot.tutup') }}" class="space-y-4">
                    @csrf
                    @if ($errors->any())
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-[11px] text-rose-700">
                            @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                        </div>
                    @endif

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal') }}" min="{{ today()->format('Y-m-d') }}" required
                               class="px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700 cursor-pointer">
                            <input type="checkbox" name="seharian" value="1" id="cek-seharian" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500" {{ old('seharian') ? 'checked' : '' }}>
                            Tutup seharian
                        </label>
                        <div id="grid-jam" class="grid grid-cols-4 gap-2 mt-3">
                            @foreach ($jamSlot as $j)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="jam[]" value="{{ $j }}" class="peer sr-only cek-jam" {{ in_array($j, old('jam', []), true) ? 'checked' : '' }}>
                                    <span class="block text-center py-1.5 rounded-lg border border-gray-200 text-[11px] font-bold text-gray-600 tabular-nums peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 peer-disabled:opacity-40 transition-all">{{ $j }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Alasan penutupan</label>
                        <input type="text" name="alasan" value="{{ old('alasan') }}" maxlength="200" required placeholder="Contoh: Staf berhalangan hadir"
                               class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
                    </div>

                    <label class="flex items-start gap-2 text-[11px] text-gray-600 cursor-pointer">
                        <input type="checkbox" name="batalkan_booking" value="1" class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <span><b class="text-gray-800">Batalkan booking yang sudah ada</b> di jam tersebut. Pelanggan akan melihat alasannya dan diminta memilih jadwal baru. Kalau tidak dicentang dan masih ada booking aktif, penutupan ditolak.</span>
                    </label>

                    <button type="submit" class="bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-700 hover:to-rose-600 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-rose-500/20 active:scale-95 transition-all cursor-pointer">Tutup Jadwal</button>
                </form>

                <div>
                    <p class="text-[11px] font-bold text-gray-600 mb-3">Sedang ditutup</p>
                    @forelse ($slotTutup as $tglKey => $slots)
                        <div class="mb-3 p-3 rounded-xl border border-gray-100 bg-gray-50/50">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($tglKey)->locale('id')->translatedFormat('l, d F Y') }}
                                    @if ($slots->count() >= count($jamSlot))
                                        <span class="ml-1 px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 text-[9px] font-black uppercase">Seharian</span>
                                    @endif
                                </p>
                                <form method="POST" action="{{ route('admin.fitting.slot.buka') }}">
                                    @csrf
                                    <input type="hidden" name="tanggal" value="{{ $tglKey }}">
                                    <button class="text-[10px] font-bold text-emerald-700 hover:underline cursor-pointer">Buka semua</button>
                                </form>
                            </div>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach ($slots as $sl)
                                    @php $jamTxt = \Carbon\Carbon::parse($sl->jam_mulai)->format('H:i'); @endphp
                                    <form method="POST" action="{{ route('admin.fitting.slot.buka') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="tanggal" value="{{ $tglKey }}">
                                        <input type="hidden" name="jam" value="{{ $jamTxt }}">
                                        <button title="Buka kembali jam ini" class="px-2 py-0.5 rounded-md bg-white border border-rose-200 text-[10px] font-bold text-rose-700 tabular-nums hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 transition-colors cursor-pointer">{{ $jamTxt }} ✕</button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">Belum ada jadwal yang ditutup.</p>
                    @endforelse
                </div>
            </div>
        </details>
    @endif

    {{-- Area yang diperbarui otomatis saat mengetik / ganti tanggal --}}
    <div id="area-tabel" class="transition-opacity duration-200">
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach ([['semua', 'Semua'], ['hari_ini', 'Hari ini'], ['terjadwal', 'Terjadwal'], ['hadir', 'Hadir'], ['batal', 'Batal']] as [$kunci, $label])
                <button type="button" data-status="{{ $kunci }}"
                        class="px-3.5 py-1.5 rounded-full border text-[11px] font-bold transition-all cursor-pointer {{ $status === $kunci ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-amber-300' }}">
                    {{ $label }} <span class="ml-1 opacity-60">{{ $hitung[$kunci] ?? 0 }}</span>
                </button>
            @endforeach
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between text-[11px] text-gray-500">
                <span>
                    @if (method_exists($reservasis, 'total'))
                        Menampilkan <b class="text-gray-700">{{ $reservasis->count() }}</b> dari <b class="text-gray-700">{{ $reservasis->total() }}</b> reservasi
                    @else
                        <b class="text-gray-700">{{ $reservasis->count() }}</b> reservasi
                    @endif
                    @if (filled(request('search')))
                        · kata kunci “<b class="text-gray-700">{{ request('search') }}</b>”
                    @endif
                    @if (filled(request('tanggal')))
                        · tanggal <b class="text-gray-700">{{ \Carbon\Carbon::parse(request('tanggal'))->format('d M Y') }}</b>
                    @endif
                </span>
                <span class="hidden sm:inline">Kuota: maks. 2 tamu per jam</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-left">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="pl-5 pr-0 py-3 w-9">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 text-amber-600 bg-gray-100 border-gray-300 rounded focus:ring-amber-500 cursor-pointer" aria-label="Pilih semua">
                            </th>
                            <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Pelanggan</th>
                            <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Jadwal</th>
                            <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap w-full">Busana yang Disewa</th>
                            <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Kode Booking</th>
                            <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Status</th>
                            <th class="px-3 pr-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/80 bg-white">
                        @forelse ($reservasis as $r)
                            @php
                                $namaPelanggan = $r->user->name ?? 'User Terhapus';
                                $inisial = strtoupper(mb_substr(trim($namaPelanggan) ?: '?', 0, 1));

                                $fotoUser = $r->user->foto_profil ?? null;
                                $fotoUserUrl = $fotoUser
                                    ? (Str::startsWith($fotoUser, ['http://', 'https://']) ? $fotoUser : asset('storage/' . $fotoUser))
                                    : null;

                                // Jadwal
                                $tgl = $r->slot ? \Carbon\Carbon::parse($r->slot->tanggal) : null;
                                $jam = $r->slot?->jam_mulai ? \Carbon\Carbon::parse($r->slot->jam_mulai)->format('H:i') : '-';

                                // Status
                                $st = strtolower($r->status);
                                $terjadwal = ! in_array($st, ['hadir', 'batal'], true);
                                $nilaiSelect = $terjadwal ? 'pending' : $st;
                                $hariIni = $tgl && $tgl->isToday();
                                $terlewat = $terjadwal && $tgl && $tgl->copy()->endOfDay()->isPast();

                                $badge = match (true) {
                                    $st === 'hadir' => ['bg-emerald-100 text-emerald-700 border-emerald-200', 'Hadir'],
                                    $st === 'batal' => ['bg-rose-100 text-rose-700 border-rose-200', 'Batal'],
                                    default => ['bg-sky-100 text-sky-700 border-sky-200', 'Terjadwal'],
                                };

                                // Busana
                                $trx = method_exists($r, 'transaksi') ? $r->transaksi : null;
                                $busana = [];
                                if ($trx && $trx->detail->isNotEmpty()) {
                                    foreach ($trx->detail as $d) {
                                        $busana[] = [
                                            'nama' => $d->produk->nama ?? 'Busana',
                                            'foto' => $d->produk ? $urlFoto($d->produk) : null,
                                            'ukuran' => $d->ukuran,
                                            'warna' => $d->warna,
                                        ];
                                    }
                                } elseif ($r->produk) {
                                    $busana[] = [
                                        'nama' => $r->produk->nama,
                                        'foto' => $urlFoto($r->produk),
                                        'ukuran' => null,
                                        'warna' => null,
                                    ];
                                }

                                $sewa = $trx?->sewa;
                                $catatan = collect(preg_split('/\R/', (string) $r->catatan))
                                    ->reject(fn ($l) => Str::startsWith(trim($l), 'Busana dalam pesanan:'))
                                    ->filter()->implode(' ');

                                // Link WhatsApp
                                $digits = preg_replace('/\D+/', '', (string) ($r->user->no_hp ?? ''));
                                $noWa = $digits === '' ? '' : (str_starts_with($digits, '0') ? '62' . substr($digits, 1) : (str_starts_with($digits, '8') ? '62' . $digits : $digits));
                                $tglTeks = $tgl ? $tgl->copy()->locale('id')->translatedFormat('l, d F Y') : '';
                                $pesanWa = $st === 'batal'
                                    ? "Halo {$namaPelanggan}, mohon maaf jadwal fitting Anda ({$r->kode_booking}, {$tglTeks} pukul {$jam} WIB) kami batalkan"
                                        . ($r->alasan_batal ? " karena {$r->alasan_batal}" : '')
                                        . '. Silakan pilih jadwal fitting baru di ' . url('/fitting') . '. Terima kasih.'
                                    : "Halo {$namaPelanggan}, pengingat jadwal fitting Anda di Toko ALNAY: {$tglTeks} pukul {$jam} WIB (kode {$r->kode_booking}). Sampai jumpa!";
                            @endphp
                            <tr id="baris-{{ $r->id }}" class="hover:bg-amber-50/30 transition-colors align-middle row-data" data-id="{{ $r->id }}">

                                {{-- Checkbox --}}
                                <td class="pl-5 pr-0 py-3">
                                    @if($r->status !== 'hadir')
                                        <input type="checkbox" value="{{ $r->id }}" class="row-checkbox w-4 h-4 text-amber-600 bg-gray-100 border-gray-300 rounded focus:ring-amber-500 cursor-pointer" aria-label="Pilih {{ $r->kode_booking }}">
                                    @else
                                        <input type="checkbox" disabled title="Data hadir tidak bisa dihapus" class="w-4 h-4 text-gray-300 bg-gray-100 border-gray-200 rounded cursor-not-allowed">
                                    @endif
                                </td>

                                {{-- Pelanggan --}}
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2.5">
                                        @if($fotoUserUrl)
                                            <img src="{{ $fotoUserUrl }}" alt="{{ $namaPelanggan }}" class="h-9 w-9 shrink-0 rounded-xl object-cover border border-gray-200">
                                        @else
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-black border bg-amber-50 text-amber-700 border-amber-100">{{ $inisial }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-bold text-gray-900 truncate max-w-[150px]" title="{{ $namaPelanggan }}">{{ $namaPelanggan }}</p>
                                            <p class="text-[11px] text-gray-500 whitespace-nowrap">{{ $r->user->no_hp ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Jadwal --}}
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="text-[13px] font-bold text-gray-800">{{ $tgl ? $tgl->format('d M Y') : '-' }}</div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] text-amber-600 font-bold">{{ $jam }} WIB</span>
                                        @if ($hariIni && $terjadwal)
                                            <span class="px-1.5 py-0.5 rounded bg-amber-500 text-white text-[9px] font-black uppercase tracking-wider">Hari ini</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Busana: ringkas, foto di kiri, info sejajar --}}
                                <td class="px-3 py-3">
                                    @if (count($busana))
                                        <div class="space-y-1.5">
                                            @foreach (array_slice($busana, 0, 3) as $b)
                                                <div class="flex items-center gap-2.5">
                                                    <div class="relative h-10 w-10 shrink-0 rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                                                        @if ($b['foto'])
                                                            <img src="{{ $b['foto'] }}" alt="{{ $b['nama'] }}" loading="lazy" class="relative z-10 h-full w-full object-cover bg-white" onerror="this.remove()">
                                                        @endif
                                                        <svg class="w-4 h-4 text-gray-300 absolute inset-0 m-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4-4a3 3 0 014 0l4 4m-2-2l1-1a3 3 0 014 0l2 2M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                                                            <span class="text-[13px] font-bold text-gray-800 truncate max-w-[200px]" title="{{ $b['nama'] }}">{{ $b['nama'] }}</span>
                                                            @if ($b['ukuran'])
                                                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-[10px] font-semibold text-gray-600 whitespace-nowrap">Ukuran {{ $b['ukuran'] }}</span>
                                                            @endif
                                                            @if ($b['warna'])
                                                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-[10px] font-semibold text-gray-600 whitespace-nowrap">{{ $b['warna'] }}</span>
                                                            @endif
                                                        </div>
                                                        {{-- Meta sewa menempel di item pertama agar baris tidak memanjang ke bawah --}}
                                                        @if ($loop->first && ($sewa || $trx))
                                                            <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0 text-[10px] text-gray-500">
                                                                @if ($sewa)
                                                                    <span class="whitespace-nowrap">Ambil <b class="text-gray-700">{{ \Carbon\Carbon::parse($sewa->tanggal_pinjam)->format('d M Y') }}</b></span>
                                                                    <span class="text-gray-300">→</span>
                                                                    <span class="whitespace-nowrap">Kembali <b class="text-gray-700">{{ \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->format('d M Y') }}</b></span>
                                                                @endif
                                                                @if ($trx)
                                                                    <span class="font-mono text-gray-400 whitespace-nowrap">{{ $trx->kode_transaksi }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if (count($busana) > 3)
                                                <p class="text-[10px] text-gray-400 pl-[50px]">+{{ count($busana) - 3 }} busana lainnya</p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Fitting umum (tanpa pesanan)</span>
                                        @if ($trx)
                                            <p class="text-[10px] font-mono text-gray-400 mt-0.5">{{ $trx->kode_transaksi }}</p>
                                        @endif
                                    @endif
                                    @if ($catatan)
                                        <p class="text-[10px] text-gray-500 italic mt-1 max-w-[320px] truncate" title="{{ $catatan }}">“{{ $catatan }}”</p>
                                    @endif
                                </td>

                                {{-- Kode booking: satu baris, tidak terpecah --}}
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black font-mono bg-gray-900 text-white tracking-wide shadow-sm select-all">{{ $r->kode_booking }}</span>
                                </td>

                                {{-- Status --}}
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider whitespace-nowrap status-badge {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                    @if ($terlewat)
                                        <p class="mt-1 text-[10px] font-semibold text-amber-600 max-w-[150px] leading-snug">Jadwal terlewat, tandai Hadir / Batal</p>
                                    @endif
                                    @if ($st === 'batal' && $r->alasan_batal)
                                        <p class="mt-1 text-[10px] text-rose-600 max-w-[170px] leading-snug">{{ $r->dibatalkan_oleh === 'pelanggan' ? 'Dibatalkan pelanggan' : 'Alasan' }}: {{ $r->alasan_batal }}</p>
                                    @elseif ($st === 'batal' && $r->dibatalkan_oleh === 'pelanggan')
                                        <p class="mt-1 text-[10px] text-rose-600">Dibatalkan pelanggan</p>
                                    @endif
                                </td>

                                {{-- Aksi: satu baris (status · WA · hapus) --}}
                                <td class="px-3 pr-5 py-3">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form id="form-status-{{ $r->id }}" action="{{ route('admin.fitting.update-status', $r->id) }}" method="POST" class="inline-block form-status">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" data-original="{{ $nilaiSelect }}" data-awal="{{ $nilaiSelect }}" data-nama="{{ $namaPelanggan }}" data-kode="{{ $r->kode_booking }}" onchange="pilihStatus(this)" aria-label="Ubah status reservasi"
                                                    class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-xl text-[11px] font-bold cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all hover:border-amber-300">
                                                <option value="pending" {{ $nilaiSelect === 'pending' ? 'selected' : '' }}>Terjadwal</option>
                                                <option value="hadir" {{ $nilaiSelect === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                <option value="batal" {{ $nilaiSelect === 'batal' ? 'selected' : '' }}>Batal</option>
                                            </select>
                                        </form>

                                        @if ($noWa)
                                            <a href="https://wa.me/{{ $noWa }}?text={{ rawurlencode($pesanWa) }}" target="_blank" rel="noopener"
                                               title="{{ $st === 'batal' ? 'Beritahu pelanggan lewat WhatsApp' : 'Ingatkan lewat WhatsApp' }}"
                                               aria-label="{{ $st === 'batal' ? 'Beritahu pelanggan lewat WhatsApp' : 'Ingatkan lewat WhatsApp' }}"
                                               class="p-1.5 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors border border-emerald-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            </a>
                                        @endif

                                        @if ($r->status !== 'hadir')
                                            <form action="{{ route('admin.fitting.destroy', $r->id) }}" method="POST" class="inline-block form-delete btn-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-rose-500 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors border border-rose-100" title="Hapus Reservasi" aria-label="Hapus Reservasi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
                                    @if ($adaFilter)
                                        Tidak ada reservasi yang cocok dengan pencarian Anda.
                                    @else
                                        Belum ada jadwal reservasi fitting.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="paginasi" class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $reservasis->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Dialog batalkan reservasi (alasan wajib) --}}
<dialog id="dlg-batal" class="m-auto rounded-2xl p-0 w-[92vw] max-w-md max-h-[90vh] overflow-y-auto bg-white text-gray-900 backdrop:bg-black/50 backdrop:backdrop-blur-sm shadow-2xl">
    <form id="form-batal" method="POST" class="p-6 space-y-4">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="batal">
        <div>
            <h3 class="text-base font-bold text-gray-900">Batalkan reservasi fitting?</h3>
            <p class="text-xs text-gray-500 mt-1" id="dlg-info"></p>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-gray-600 mb-1">Alasan (akan ditampilkan ke pelanggan)</label>
            <textarea name="alasan" id="dlg-alasan" rows="3" required minlength="3" maxlength="200" placeholder="Contoh: Staf berhalangan hadir"
                      class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs resize-none focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
            <p class="mt-1 text-right text-[10px] text-gray-400"><span id="dlg-hitung">0</span>/200</p>
            <div class="flex flex-wrap gap-1.5 mt-2">
                @foreach (['Staf berhalangan hadir', 'Toko tutup', 'Pelanggan meminta pembatalan', 'Pelanggan tidak hadir'] as $cepat)
                    <button type="button" data-isi="{{ $cepat }}" class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-amber-100 text-[10px] font-semibold text-gray-600 cursor-pointer transition-colors">{{ $cepat }}</button>
                @endforeach
            </div>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
            <button type="button" id="dlg-tutup" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer">Kembali</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold cursor-pointer">Batalkan Reservasi</button>
        </div>
    </form>
</dialog>

<script>
(function () {
    const form = document.getElementById('form-filter');
    const inputCari = document.getElementById('input-cari');
    const inputTanggal = document.getElementById('input-tanggal');
    const inputStatus = document.getElementById('input-status');
    const area = document.getElementById('area-tabel');
    const spinner = document.getElementById('spinner');
    let timer = null;
    let kontrol = null;

    function buatUrl(halaman) {
        const p = new URLSearchParams();
        const kata = inputCari.value.trim();
        if (kata) p.set('search', kata);
        if (inputTanggal.value) p.set('tanggal', inputTanggal.value);
        if (inputStatus.value && inputStatus.value !== 'semua') p.set('status', inputStatus.value);
        if (halaman && Number(halaman) > 1) p.set('page', halaman);
        const q = p.toString();
        return form.getAttribute('action') + (q ? '?' + q : '');
    }

    function tampilkanMuat(aktif) {
        area.classList.toggle('opacity-50', aktif);
        spinner.classList.toggle('hidden', !aktif);
        spinner.classList.toggle('flex', aktif);
    }

    async function muat(url) {
        if (kontrol) kontrol.abort();
        kontrol = new AbortController();
        tampilkanMuat(true);

        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                signal: kontrol.signal,
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);

            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const baru = doc.getElementById('area-tabel');
            if (!baru) throw new Error('Area tabel tidak ditemukan');

            area.innerHTML = baru.innerHTML;
            
            // Re-inisialisasi event listener untuk tabel yang baru dimuat (Penting untuk AJAX)
            if (typeof initTableEvents === 'function') initTableEvents();

            history.replaceState(null, '', url);
            tampilkanMuat(false);
        } catch (e) {
            if (e.name === 'AbortError') return;
            window.location.href = url;
        }
    }

    inputCari.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { muat(buatUrl()); }, 300);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(timer);
        muat(buatUrl());
    });

    inputTanggal.addEventListener('change', function () { muat(buatUrl()); });

    document.getElementById('btn-reset').addEventListener('click', function () {
        inputCari.value = '';
        inputTanggal.value = '';
        inputStatus.value = 'semua';
        muat(buatUrl());
        inputCari.focus();
    });

    area.addEventListener('click', function (e) {
        // Chip status
        const chip = e.target.closest('[data-status]');
        if (chip) {
            inputStatus.value = chip.dataset.status;
            muat(buatUrl());
            return;
        }

        const a = e.target.closest('a[href]');
        if (!a || !a.closest('#paginasi')) return;
        e.preventDefault();
        muat(buatUrl(new URL(a.href, location.origin).searchParams.get('page')));
    });
})();
</script>

{{-- SCRIPT 2: DIALOG BATAL + UBAH STATUS (AJAX) --}}
<script>
(function () {
    const dlg = document.getElementById('dlg-batal');
    const formBatal = document.getElementById('form-batal');
    const alasan = document.getElementById('dlg-alasan');
    const hitung = document.getElementById('dlg-hitung');
    let selAktif = null;
    let terkirim = false;

    const updateHitung = function () { hitung.textContent = alasan.value.length; };
    alasan.addEventListener('input', updateHitung);

    // Pilih status: Batal membuka dialog alasan, status lain langsung disimpan via AJAX
    window.pilihStatus = function (sel) {
        if (sel.value === 'batal') {
            selAktif = sel;
            terkirim = false;
            formBatal.action = sel.form.action;
            document.getElementById('dlg-info').textContent = sel.dataset.nama + ' · ' + sel.dataset.kode;
            alasan.value = '';
            updateHitung();
            document.body.style.overflow = 'hidden';
            dlg.showModal();
            alasan.focus();
            return;
        }
        
        // Jika bukan batal, jalankan AJAX (dari kode Anda)
        const form = sel.closest('form');
        const row = sel.closest('tr');
        const badge = row.querySelector('.status-badge');
        const newStatus = sel.value;
        const originalStatus = sel.dataset.original;

        // FormData harus dibuat SEBELUM select dinonaktifkan; field disabled tidak ikut terkirim
        const dataForm = new FormData(form);
        sel.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: dataForm,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan server');
            return data;
        })
        .then(data => {
            if (data.success) {
                badge.textContent = newStatus === 'pending' ? 'Terjadwal' : 'Hadir';
                badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider status-badge ';
                if (newStatus === 'pending') badge.classList.add('bg-sky-100', 'text-sky-700', 'border-sky-200');
                else if (newStatus === 'hadir') badge.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-200');
                
                sel.dataset.original = newStatus;
                
                const deleteBtn = row.querySelector('.btn-delete');
                if (deleteBtn) deleteBtn.style.display = newStatus === 'hadir' ? 'none' : 'inline-block';
                const checkbox = row.querySelector('.row-checkbox');
                if(checkbox) checkbox.parentElement.innerHTML = newStatus === 'hadir' ? '<input type="checkbox" disabled class="w-4 h-4 text-gray-300 bg-gray-100 border-gray-200 rounded cursor-not-allowed">' : '<input type="checkbox" value="'+row.dataset.id+'" class="row-checkbox w-4 h-4 text-amber-600 bg-gray-100 border-gray-300 rounded focus:ring-amber-500 cursor-pointer">';

                if(typeof showToast === 'function') showToast(data.message, 'success');
            }
        })
        .catch(err => {
            sel.value = originalStatus;
            if(typeof showToast === 'function') showToast(err.message || 'Gagal mengubah status', 'error');
        })
        .finally(() => {
            sel.disabled = false;
        });
    };

    document.getElementById('dlg-tutup').addEventListener('click', function () { dlg.close(); });

    // Ditutup tanpa mengirim: kembalikan pilihan dropdown seperti semula
    // (pakai data-original karena nilainya ikut diperbarui setelah status diubah via AJAX)
    dlg.addEventListener('close', function () {
        document.body.style.overflow = '';
        if (!terkirim && selAktif) selAktif.value = selAktif.dataset.original;
    });

    // Klik area gelap (backdrop) menutup dialog
    dlg.addEventListener('click', function (e) {
        const r = dlg.getBoundingClientRect();
        if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) dlg.close();
    });

    formBatal.addEventListener('submit', function (e) {
        e.preventDefault();
        terkirim = true;
        
        fetch(formBatal.action, {
            method: 'POST',
            body: new FormData(formBatal),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan');
            return data;
        })
        .then(data => {
            if(data.success) {
                dlg.close();
                // Reload tabel agar alasan batal dan tombol WA muncul (karena HTML butuh render ulang)
                window.location.reload(); 
            }
        })
        .catch(err => {
            terkirim = false;
            if (typeof showToast === 'function') showToast(err.message || 'Gagal membatalkan reservasi', 'error');
        });
    });

    dlg.querySelectorAll('[data-isi]').forEach(function (b) {
        b.addEventListener('click', function () { alasan.value = b.dataset.isi; updateHitung(); alasan.focus(); });
    });

    // Form tutup jadwal: "seharian" menonaktifkan pilihan jam
    const cekSeharian = document.getElementById('cek-seharian');
    if (cekSeharian) {
        const atur = function () {
            document.querySelectorAll('.cek-jam').forEach(function (c) {
                c.disabled = cekSeharian.checked;
                if (cekSeharian.checked) c.checked = false;
            });
        };
        cekSeharian.addEventListener('change', atur);
        atur();
    }
})();
</script>

{{-- SCRIPT 3: AJAX HAPUS, UBAH STATUS (NON-BATAL), & CHECKBOX DARI ANDA --}}
<script>
    // Bungkus ke dalam fungsi agar bisa dipanggil ulang saat tabel di-reload via AJAX
    function initTableEvents() {
        // --- FUNGSI TOAST NOTIFIKASI ---
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-rose-500';
            const icon = type === 'success' 
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
            
            toast.className = `${bgColor} text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-full opacity-0`;
            toast.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">${icon}</svg>
                <span class="text-sm font-bold">${message}</span>
            `;
            
            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-x-full', 'opacity-0'), 10);
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };

        // --- LOGIKA HAPUS SATUAN (AJAX) ---
        document.querySelectorAll('.form-delete').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!confirm('Yakin ingin menghapus reservasi ini?')) return;

                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.closest('tr').remove();
                        showToast(data.message || 'Data berhasil dihapus', 'success');
                    } else {
                        showToast(data.message || 'Gagal menghapus', 'error');
                    }
                });
            });
        });

        // --- LOGIKA CHECKBOX & BULK DELETE ---
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const countSelected = document.getElementById('countSelected');

        function updateBulkBtn() {
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            if(countSelected) countSelected.textContent = checked;
            if (checked > 0) {
                btnBulkDelete.classList.remove('hidden');
            } else {
                btnBulkDelete.classList.add('hidden');
            }
        }

        if (selectAll) {
            selectAll.replaceWith(selectAll.cloneNode(true));
            const newSelectAll = document.getElementById('selectAll');
            
            newSelectAll.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
                updateBulkBtn();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.replaceWith(cb.cloneNode(true));
        });
        
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const total = document.querySelectorAll('.row-checkbox').length;
                const checked = document.querySelectorAll('.row-checkbox:checked').length;
                if(document.getElementById('selectAll')) document.getElementById('selectAll').checked = total === checked;
                updateBulkBtn();
            });
        });

        if (btnBulkDelete) {
            btnBulkDelete.replaceWith(btnBulkDelete.cloneNode(true));
            document.getElementById('btnBulkDelete').addEventListener('click', function() {
                const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
                if (ids.length === 0) return;
                
                if (!confirm(`Yakin ingin menghapus ${ids.length} reservasi terpilih?`)) return;

                fetch("{{ route('admin.fitting.bulk-destroy') }}", {
                    method: 'POST',
                    body: JSON.stringify({ ids: ids, _token: "{{ csrf_token() }}", _method: "DELETE" }),
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        ids.forEach(id => {
                            const row = document.querySelector(`.row-data[data-id="${id}"]`);
                            if (row) row.remove();
                        });
                        if(document.getElementById('selectAll')) document.getElementById('selectAll').checked = false;
                        updateBulkBtn();
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message, 'error');
                    }
                });
            });
        }
    }

    // Jalankan saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', initTableEvents);
</script>
@endsection