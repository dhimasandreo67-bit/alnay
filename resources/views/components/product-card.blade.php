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
    $badgeStatus = $status === 'TERSEDIA' ? 'bg-neutral-900' : 'bg-red-600';

    // Harga
    $hargaSewa = $angka($ambil($produk, ['harga_sewa', 'harga_sewa_3hari', 'harga_rental'], 0));
    $hargaBeli = $angka($ambil($produk, ['harga_jual', 'harga_beli'], 0));

    // Aturan yang sama dengan halaman detail: flag dari database + harga harus > 0
    $bisaSewa = (bool) $ambil($produk, ['bisa_disewa'], false) && $hargaSewa > 0;
    $bisaBeli = (bool) $ambil($produk, ['dapat_dijual'], false) && $hargaBeli > 0;

    $labelTipe = match (true) {
        $bisaSewa && $bisaBeli => 'Sewa & Beli',
        $bisaSewa => 'Khusus Sewa',
        $bisaBeli => 'Khusus Jual',
        default => 'Belum Tersedia',
    };

    // Rating
    $rating = (float) $ambil($produk, ['rating_rata_rata', 'rating'], 0);
    $ulasan = (int) $ambil($produk, ['jumlah_rating', 'jumlah_ulasan'], 0);

    // Link detail produk (route: produk/{sku})
    $sku = $ambil($produk, ['sku'], null);
    $urlDetail = ($sku && \Illuminate\Support\Facades\Route::has('produk.detail'))
        ? route('produk.detail', ['sku' => $sku])
        : $ambil($produk, ['url', 'link'], '#');

    // Warna: gabungan warna jual + warna sewa (JSON array atau string biasa)
    $pecahWarna = function ($mentah): array {
        if (is_array($mentah)) {
            return $mentah;
        }
        if (! is_string($mentah) || trim($mentah) === '') {
            return [];
        }
        $decoded = json_decode($mentah, true);
        return is_array($decoded) ? $decoded : [$mentah];
    };

    $namaWarna = collect(array_merge(
        $pecahWarna($ambil($produk, ['warna'])),
        $pecahWarna($ambil($produk, ['warna_sewa'])),
    ))
        ->filter(fn ($w) => is_string($w) && trim($w) !== '')
        ->map(fn ($w) => trim($w))
        ->unique()
        ->values();

    // Sama dengan logika warna di halaman Koleksi (sidebar)
    $tebakHex = function (string $w): string {
        $n = strtolower($w);
        return match (true) {
            str_contains($n, 'hitam') => '#1a1a1a',
            str_contains($n, 'maroon') || str_contains($n, 'marun') || str_contains($n, 'merah') => '#6b1c2a',
            str_contains($n, 'gold') || str_contains($n, 'emas') || str_contains($n, 'kuning') => '#c99a32',
            str_contains($n, 'cream') || str_contains($n, 'krem') || str_contains($n, 'putih') => '#f5e6c8',
            str_contains($n, 'hijau') || str_contains($n, 'green') => '#2a5c3f',
            str_contains($n, 'coklat') || str_contains($n, 'cokelat') || str_contains($n, 'brown') => '#5c3a21',
            str_contains($n, 'biru') || str_contains($n, 'blue') || str_contains($n, 'navy') => '#1e3a8a',
            str_contains($n, 'pink') || str_contains($n, 'rose') => '#f472b6',
            str_contains($n, 'ungu') || str_contains($n, 'purple') => '#7e22ce',
            str_contains($n, 'abu') || str_contains($n, 'grey') || str_contains($n, 'silver') => '#9ca3af',
            default => '#d1d5db',
        };
    };

    $palet = \App\Models\Produk::paletWarna();
    $warna = $namaWarna->map(fn ($w) => $palet[$w] ?? $tebakHex($w))->all();
@endphp

<div class="group flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white transition-all duration-500 hover:-translate-y-1 hover:shadow-lg">

    {{-- FOTO + BADGE --}}
    <div class="relative aspect-[3/4] overflow-hidden bg-[#efe9dc]">
        <a href="{{ $urlDetail }}" class="block h-full w-full" aria-label="Lihat detail {{ $nama }}">
            @if ($foto)
                <img src="{{ $foto }}" alt="{{ $nama }}" loading="lazy"
                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
            @endif
        </a>

        <span class="absolute left-2.5 top-2.5 px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-white {{ $badgeStatus }}">
            {{ $status }}
        </span>

        @if ($peringkat !== null)
            <span class="absolute right-2.5 top-2.5 px-2 py-1 text-[10px] font-bold text-[#17140f]"
                style="background: #c99a32;">
                #{{ $peringkat }}
            </span>
        @endif

        <span class="absolute bottom-2.5 left-2.5 rounded bg-black/55 px-2 py-1 text-[10px] text-white backdrop-blur-sm">
            {{ $labelTipe }}
        </span>
    </div>

    <div class="flex flex-1 flex-col p-3 sm:p-4">

        {{-- NAMA --}}
        <h3 class="truncate text-sm font-bold sm:text-base"
            style="color: #15233d; font-family: 'Playfair Display', serif;">
            {{ $nama }}
        </h3>

        {{-- RATING --}}
        <div class="mt-1.5 flex items-center gap-1.5 text-[11px]">
            <div class="flex items-center gap-0.5" role="img"
                aria-label="Rating {{ number_format($rating, 1, ',', '.') }} dari 5">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="h-3 w-3 {{ $i <= round($rating) ? 'text-[#c99a32]' : 'text-[#e5dfd0]' }}"
                        viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                    </svg>
                @endfor
            </div>
            <span class="font-medium text-gray-700">{{ number_format($rating, 1, ',', '.') }}</span>
            <span class="text-gray-400">({{ $ulasan }})</span>
        </div>

        <div class="my-3 border-t" style="border-color: #f0ece4;"></div>

        {{-- HARGA --}}
        <div class="space-y-1.5 text-[11px] sm:text-xs">
            @if ($bisaSewa)
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500">Sewa:</span>
                    <span class="whitespace-nowrap" style="color: #15233d;">
                        <span class="text-sm font-bold sm:text-base"
                            style="font-family: 'Playfair Display', serif;">Rp {{ number_format($hargaSewa, 0, ',', '.') }}</span>
                        <span class="text-[10px] text-gray-400">/3hr</span>
                    </span>
                </div>
            @else
                <div class="flex items-center justify-between gap-2">
                    <span class="font-medium text-red-700">Status Sewa:</span>
                    <span class="text-right text-red-700">Tidak Disewakan</span>
                </div>
            @endif

            @if ($bisaBeli)
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500">Beli Baru:</span>
                    <span class="whitespace-nowrap font-semibold" style="color: #15233d;">
                        Rp {{ number_format($hargaBeli, 0, ',', '.') }}
                    </span>
                </div>
            @else
                <div class="flex items-center justify-between gap-2">
                    <span class="font-medium text-red-700">Status Jual:</span>
                    <span class="text-right text-red-700">Tidak Dijual</span>
                </div>
            @endif
        </div>

        {{-- PILIHAN WARNA --}}
        @if (count($warna))
            <div class="mt-3 flex flex-wrap items-center gap-1.5">
                @foreach ($warna as $hex)
                    <span class="h-3.5 w-3.5 rounded-full border border-black/10" style="background-color: {{ $hex }};"></span>
                @endforeach
            </div>
        @endif

        {{-- TOMBOL --}}
        <div class="mt-auto pt-4">
            @if ($bisaSewa && $bisaBeli)
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ $urlDetail }}?mode=sewa"
                        class="block border py-2.5 text-center text-[11px] font-bold uppercase tracking-wider transition-colors hover:bg-gray-50"
                        style="border-color: #c99a32; color: #15233d;">
                        Sewa
                    </a>
                    <a href="{{ $urlDetail }}?mode=beli"
                        class="block py-2.5 text-center text-[11px] font-bold uppercase tracking-wider transition duration-300 hover:-translate-y-0.5"
                        style="background: #c99a32; color: #17140f;">
                        Beli Baru
                    </a>
                </div>
            @elseif ($bisaSewa)
                <a href="{{ $urlDetail }}?mode=sewa"
                    class="block py-2.5 text-center text-[11px] font-bold uppercase tracking-wider transition duration-300 hover:-translate-y-0.5"
                    style="background: #c99a32; color: #17140f;">
                    Sewa Sekarang
                </a>
            @elseif ($bisaBeli)
                <a href="{{ $urlDetail }}?mode=beli"
                    class="block py-2.5 text-center text-[11px] font-bold uppercase tracking-wider transition duration-300 hover:-translate-y-0.5"
                    style="background: #c99a32; color: #17140f;">
                    Beli Sekarang
                </a>
            @else
                <span class="block bg-gray-200 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    Belum Tersedia
                </span>
            @endif
        </div>
    </div>
</div>