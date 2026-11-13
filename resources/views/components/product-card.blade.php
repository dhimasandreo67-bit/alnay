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

    // Tombol
    $tbl = 'inline-flex h-8 items-center justify-center rounded-lg px-3 text-[11px] font-bold tracking-normal transition-all duration-300 ease-out active:scale-[.97]';
    $tblEmas = $tbl . ' border border-[#c99a32] bg-[#c99a32] text-[#15233d] hover:bg-[#b8860b] hover:border-[#b8860b] hover:text-white';
    $tblGaris = $tbl . ' border border-[#c99a32] bg-white text-[#15233d] hover:bg-[#fbf6e9]';
    $tblMati = $tbl . ' cursor-not-allowed border border-gray-200 bg-gray-100 text-gray-400';
@endphp

<article
    class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-[#f0ece4] bg-white text-[#15233d] transition-all duration-500 hover:-translate-y-1 hover:shadow-lg"
    style="font-family: 'Inter', sans-serif;">

    {{-- FOTO + LABEL --}}
    <div class="relative aspect-[4/5] overflow-hidden bg-[#efe9dc] sm:aspect-[3/4]">
        <a href="{{ $urlDetail }}" class="block h-full w-full" aria-label="Lihat detail {{ $nama }}">
            @if ($foto)
                <img src="{{ $foto }}" alt="{{ $nama }}" width="600" height="800" loading="lazy" decoding="async"
                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
            @endif
        </a>

        {{-- Label Kiri Atas (Kategori/Status) --}}
        <span class="pointer-events-none absolute left-2 top-2 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide backdrop-blur-md {{ $tersedia ? 'bg-white/90 text-[#15233d]' : 'bg-[#15233d]/85 text-[#f5efe0]' }}">
            {{ $labelFoto }}
        </span>

        {{-- Label Kanan Atas (Peringkat) --}}
        @if ($peringkat !== null)
            <span class="pointer-events-none absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-[#c99a32] text-[10px] font-black text-white shadow-sm">
                #{{ $peringkat }}
            </span>
        @endif
    </div>

    {{-- BODY --}}
    <div class="flex flex-1 flex-col px-3 pb-3 pt-3">
        
        {{-- NAMA --}}
        <h3 class="truncate text-[13px] font-bold leading-tight" title="{{ $nama }}">
            <a href="{{ $urlDetail }}" class="transition-colors hover:text-[#8a6410]">{{ $nama }}</a>
        </h3>

        {{-- RATING --}}
        <div class="mt-1 flex h-4 items-center gap-1 text-[10px] text-[#6b7280]">
            @if ($ulasan > 0)
                <svg class="h-3 w-3 text-[#c99a32]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                </svg>
                <span class="font-bold text-[#15233d]">{{ number_format($rating, 1, ',', '.') }}</span>
                <span>({{ $ulasan }})</span>
            @else
                <span class="text-gray-400">Belum ada ulasan</span>
            @endif
        </div>

        {{-- HARGA --}}
        <div class="mt-3 flex flex-col justify-end gap-1 border-t border-[#f0ece4] pt-2.5 text-[11px]">
            <div class="flex items-center justify-between">
                <span class="text-[#6b7280] font-medium">Sewa</span>
                @if ($bisaSewa)
                    <span class="text-[12px] font-bold text-[#15233d]">
                        Rp {{ number_format($hargaSewa, 0, ',', '.') }}
                        <span class="text-[9px] font-medium text-[#6b7280]">/3hr</span>
                    </span>
                @else
                    <span class="font-medium text-[#a39a85]">-</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#6b7280] font-medium">Beli</span>
                @if ($bisaBeli)
                    <span class="text-[12px] font-bold text-[#15233d]">
                        Rp {{ number_format($hargaBeli, 0, ',', '.') }}
                    </span>
                @else
                    <span class="font-medium text-[#a39a85]">-</span>
                @endif
            </div>
        </div>

        {{-- TOMBOL --}}
        <div class="mt-3 grid gap-1.5 {{ $bisaSewa && $bisaBeli ? 'grid-cols-2' : 'grid-cols-1' }}">
            @if ($bisaSewa && $bisaBeli)
                <a href="{{ $urlDetail }}?mode=sewa" class="{{ $tblGaris }}">Sewa</a>
                <a href="{{ $urlDetail }}?mode=beli" class="{{ $tblEmas }}">Beli</a>
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