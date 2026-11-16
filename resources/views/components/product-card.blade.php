@props([
    'produk',
    'delay' => 0,
    'peringkat' => null,
])

@php
    $adalahModel = $produk instanceof \Illuminate\Database\Eloquent\Model;

    $ambil = function ($item, array $kunci, $default = null) {
        foreach ($kunci as $k) {
            $v = data_get($item, $k);
            if ($v !== null && $v !== '') {
                return $v;
            }
        }
        return $default;
    };

    $angka = fn ($v) => (int) round((float) ($v ?? 0));

    $nama = $ambil($produk, ['nama_produk', 'nama', 'name'], '-');

    // Foto
    $fotoRaw = $adalahModel
        ? $produk->sampulUrl()
        : $ambil($produk, ['sampul_url', 'foto', 'foto_utama', 'gambar', 'image', 'thumbnail']);
    $foto = is_string($fotoRaw) && $fotoRaw !== ''
        ? (\Illuminate\Support\Str::startsWith($fotoRaw, ['http://', 'https://']) ? $fotoRaw : asset($fotoRaw))
        : null;

    // Status
    $status = strtoupper((string) $ambil($produk, ['status', 'status_ketersediaan', 'status_produk'], 'TERSEDIA'));
    $tersedia = $status === 'TERSEDIA';

    // Harga dasar (sebelum diskon)
    $hargaSewa = $angka($ambil($produk, ['harga_sewa', 'harga_sewa_3hari', 'harga_rental'], 0));
    $hargaBeli = $angka($ambil($produk, ['harga_jual', 'harga_beli'], 0));

    // Aturan yang sama dengan halaman detail: flag dari database + harga harus > 0
    $bisaSewa = (bool) $ambil($produk, ['bisa_disewa'], false) && $hargaSewa > 0;
    $bisaBeli = (bool) $ambil($produk, ['dapat_dijual'], false) && $hargaBeli > 0;

    // Diskon (rumus sama dengan halaman detail & admin: harga * (1 - diskon/100))
    $klem = fn ($d) => max(0, min(100, (float) $d));
    $diskonSewa = $adalahModel ? $produk->diskonSewa() : $klem($ambil($produk, ['diskon_sewa_persen'], 0));
    $diskonBeli = $adalahModel ? $produk->diskonJual() : $klem($ambil($produk, ['diskon_jual_persen'], 0));
    $netSewa = $adalahModel ? $produk->hargaSewaNet() : (int) round($hargaSewa * (1 - $diskonSewa / 100));
    $netBeli = $adalahModel ? $produk->hargaJualNet() : (int) round($hargaBeli * (1 - $diskonBeli / 100));
    $fmtDiskon = fn ($d) => rtrim(rtrim(number_format($d, 2, '.', ''), '0'), '.');

    // Stok per jenis (sewa = kapasitas unit yang dimiliki; ketersediaan per tanggal dicek di halaman detail).
    // Data non-model (array) tidak membawa data stok lengkap, jadi dianggap tidak habis.
    $habisSewa = $adalahModel && $bisaSewa && $produk->stokUntuk('sewa') <= 0;
    $habisBeli = $adalahModel && $bisaBeli && $produk->stokUntuk('jual') <= 0;
    $habisSemua = ($bisaSewa || $bisaBeli)
        && (! $bisaSewa || $habisSewa)
        && (! $bisaBeli || $habisBeli);

    $labelTipe = match (true) {
        $bisaSewa && $bisaBeli => 'Sewa & Beli',
        $bisaSewa => 'Khusus Sewa',
        $bisaBeli => 'Khusus Jual',
        default => 'Belum Tersedia',
    };

    // Satu label saja di foto: status kalau tidak tersedia, selain itu jenis layanan
    $labelFoto = $tersedia ? $labelTipe : ucfirst(strtolower($status));

    // Rating
    $rating = (float) $ambil($produk, ['rating_rata_rata', 'rating'], 0);
    $ulasan = (int) $ambil($produk, ['jumlah_rating', 'jumlah_ulasan'], 0);

    // Link detail produk (route: produk/{sku})
    $sku = $ambil($produk, ['sku'], null);
    $urlDetail = ($sku && \Illuminate\Support\Facades\Route::has('produk.detail'))
        ? route('produk.detail', ['sku' => $sku])
        : $ambil($produk, ['url', 'link'], '#');

    // Baris harga (Sewa & Beli) dirender lewat satu loop
    $baris = [
        ['label' => 'Sewa', 'aktif' => $bisaSewa, 'asli' => $hargaSewa, 'net' => $netSewa, 'diskon' => $diskonSewa, 'habis' => $habisSewa, 'suffix' => '/3hr'],
        ['label' => 'Beli', 'aktif' => $bisaBeli, 'asli' => $hargaBeli, 'net' => $netBeli, 'diskon' => $diskonBeli, 'habis' => $habisBeli, 'suffix' => null],
    ];

    // Tombol
    $tbl = 'inline-flex h-8 items-center justify-center rounded-lg px-3 text-[11px] font-bold tracking-normal transition-all duration-300 ease-out active:scale-[.97]';
    $tblEmas = $tbl . ' border border-[#c99a32] bg-[#c99a32] text-[#15233d] hover:bg-[#b8860b] hover:border-[#b8860b] hover:text-white';
    $tblGaris = $tbl . ' border border-[#c99a32] bg-white text-[#15233d] hover:bg-[#fbf6e9]';
    $tblMati = $tbl . ' cursor-not-allowed border border-gray-200 bg-gray-100 text-gray-400';
@endphp

<article
    class="group relative flex h-full flex-col overflow-hidden rounded-2xl border text-[#15233d] transition-all duration-500 hover:-translate-y-1 hover:shadow-lg {{ $habisSemua ? 'border-gray-200 bg-gray-50' : 'border-[#f0ece4] bg-white' }}"
    style="font-family: 'Inter', sans-serif;">

    {{-- FOTO + LABEL --}}
    <div class="relative aspect-[4/5] overflow-hidden bg-[#efe9dc] sm:aspect-[3/4]">
        <a href="{{ $urlDetail }}" class="block h-full w-full" aria-label="Lihat detail {{ $nama }}">
            @if ($foto)
                <img src="{{ $foto }}" alt="{{ $nama }}" width="600" height="800" loading="lazy" decoding="async"
                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105 {{ $habisSemua ? 'grayscale opacity-70' : '' }}">
            @endif
        </a>

        {{-- Stok habis (semua jenis): foto abu-abu + penanda di tengah --}}
        @if ($habisSemua)
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-white/35">
                <span class="rounded-full bg-[#15233d]/90 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-white shadow-md">
                    Stok Habis
                </span>
            </div>
        @endif

        {{-- Label Kiri Atas (Kategori/Status) --}}
        <span class="pointer-events-none absolute left-2 top-2 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide backdrop-blur-md {{ $tersedia ? 'bg-white/90 text-[#15233d]' : 'bg-[#15233d]/85 text-[#f5efe0]' }}">
            {{ $labelFoto }}
        </span>

        {{-- Label Kanan Atas (Peringkat) --}}
        @if ($peringkat !== null)
            <span class="pointer-events-none absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-[#c99a32] text-[10px] font-black text-white shadow-sm {{ $habisSemua ? 'grayscale' : '' }}">
                #{{ $peringkat }}
            </span>
        @endif
    </div>

    {{-- BODY --}}
    <div class="flex flex-1 flex-col px-3 pb-3 pt-3">

        {{-- NAMA --}}
        <h3 class="truncate text-[13px] font-bold leading-tight {{ $habisSemua ? 'text-gray-500' : '' }}" title="{{ $nama }}">
            <a href="{{ $urlDetail }}" class="transition-colors hover:text-[#8a6410]">{{ $nama }}</a>
        </h3>

        {{-- RATING --}}
        <div class="mt-1 flex h-4 items-center gap-1 text-[10px] text-[#6b7280]">
            @if ($ulasan > 0)
                <svg class="h-3 w-3 {{ $habisSemua ? 'text-gray-400' : 'text-[#c99a32]' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                </svg>
                <span class="font-bold {{ $habisSemua ? 'text-gray-500' : 'text-[#15233d]' }}">{{ number_format($rating, 1, ',', '.') }}</span>
                <span>({{ $ulasan }})</span>
            @else
                <span class="text-gray-400">Belum ada ulasan</span>
            @endif
        </div>

        {{-- HARGA (harga asli dicoret + persen diskon bila ada) --}}
        <div class="mt-3 flex flex-col gap-1.5 border-t pt-2.5 text-[11px] {{ $habisSemua ? 'border-gray-200' : 'border-[#f0ece4]' }}">
            @foreach ($baris as $b)
                <div class="flex items-start justify-between gap-2">
                    <span class="pt-0.5 font-medium text-[#6b7280]">{{ $b['label'] }}</span>

                    @if ($b['aktif'])
                        <div class="text-right">
                            @if ($b['diskon'] > 0)
                                <div class="flex items-center justify-end gap-1">
                                    <span class="text-[10px] text-[#9a9384] line-through tabular-nums">Rp {{ number_format($b['asli'], 0, ',', '.') }}</span>
                                    <span class="rounded px-1 py-px text-[9px] font-bold {{ $habisSemua ? 'bg-gray-200 text-gray-500' : 'bg-rose-100 text-rose-700' }}">-{{ $fmtDiskon($b['diskon']) }}%</span>
                                </div>
                            @endif

                            <span class="text-[12px] font-bold tabular-nums {{ $habisSemua || $b['habis'] ? 'text-gray-400' : 'text-[#15233d]' }}">
                                Rp {{ number_format($b['net'], 0, ',', '.') }}
                                @if ($b['suffix'])
                                    <span class="text-[9px] font-medium text-[#6b7280]">{{ $b['suffix'] }}</span>
                                @endif
                            </span>

                            {{-- Hanya satu jenis yang habis (jenis lain masih ada) --}}
                            @if ($b['habis'] && ! $habisSemua)
                                <span class="block text-[9px] font-semibold text-rose-500">Stok {{ strtolower($b['label']) }} habis</span>
                            @endif
                        </div>
                    @else
                        <span class="font-medium text-[#a39a85]">-</span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- TOMBOL --}}
        <div class="mt-auto grid gap-1.5 pt-3 {{ ! $habisSemua && $bisaSewa && $bisaBeli ? 'grid-cols-2' : 'grid-cols-1' }}">
            @if ($habisSemua)
                <span class="{{ $tblMati }}">Stok Habis</span>
            @elseif ($bisaSewa && $bisaBeli)
                @if ($habisSewa)
                    <span class="{{ $tblMati }}">Sewa habis</span>
                @else
                    <a href="{{ $urlDetail }}?mode=sewa" class="{{ $tblGaris }}">Sewa</a>
                @endif

                @if ($habisBeli)
                    <span class="{{ $tblMati }}">Beli habis</span>
                @else
                    <a href="{{ $urlDetail }}?mode=beli" class="{{ $tblEmas }}">Beli</a>
                @endif
            @elseif ($bisaSewa)
                <a href="{{ $urlDetail }}?mode=sewa" class="{{ $tblEmas }}">Sewa</a>
            @elseif ($bisaBeli)
                <a href="{{ $urlDetail }}?mode=beli" class="{{ $tblEmas }}">Beli</a>
            @else
                <span class="{{ $tblMati }}">Habis</span>
            @endif
        </div>
    </div>
</article>