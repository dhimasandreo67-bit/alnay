{{-- ============================================================
     IKON ALNAY.ID  —  <x-icon name="bag" :size="20" class="..." />
     Gaya   : garis halus (1.75) + isi duotone emas lembut pada bentuk tertutup
     Warna  : mengikuti `currentColor`
     Opsi   : :duotone="false" untuk mematikan isi transparan (garis saja)
     ============================================================ --}}
@props(['name' => 'search', 'size' => 20, 'duotone' => true])

@php
    // Isi transparan untuk bentuk tertutup (otomatis ikut warna teks)
    $f = $duotone ? 'fill="currentColor" fill-opacity=".16"' : '';

    $paths = [
        // ---------- Navigasi ----------
        'home'           => '<path d="M3 10.6 12 3.5l9 7.1V19.5a1.5 1.5 0 0 1-1.5 1.5H15v-6H9v6H4.5A1.5 1.5 0 0 1 3 19.5z" '.$f.'/>',
        'dress'          => '<path d="M9 3l3 4 3-4 1 5.5-1.5 2L19 21H5l4.5-10.5L8 8.5z" '.$f.'/><path d="M9.5 10.5h5"/>',
        'calendar-check' => '<rect x="3" y="5" width="18" height="16" rx="2.5" '.$f.'/><path d="M3 10h18M8 3v4M16 3v4M9 15.2l2 2 4-4.2"/>',
        'info-circle'    => '<circle cx="12" cy="12" r="9" '.$f.'/><path d="M12 11v5.2"/><path d="M12 7.8h.01" stroke-width="2.6"/>',
        'testimoni'      => '<path d="M20.5 11.5a8 8 0 0 1-8 8H4l1.7-3.4A8 8 0 1 1 20.5 11.5z" '.$f.'/><path d="M12 8l1.1 2.3 2.5.3-1.9 1.7.5 2.5-2.2-1.2-2.2 1.2.5-2.5-1.9-1.7 2.5-.3z" fill="currentColor" stroke-width="1.1"/>',

        // ---------- Aksi ----------
        'search'         => '<circle cx="11" cy="11" r="6.8" '.$f.'/><path d="M20.5 20.5l-4.7-4.7"/>',
        'bag'            => '<path d="M5.2 8.5h13.6l1 11.2a1.3 1.3 0 0 1-1.3 1.4H5.5a1.3 1.3 0 0 1-1.3-1.4z" '.$f.'/><path d="M8.8 8.5V7.2a3.2 3.2 0 0 1 6.4 0v1.3"/>',
        'cart'           => '<path d="M2.5 3.5h2l2.4 11.6a1.8 1.8 0 0 0 1.8 1.4h8.6a1.8 1.8 0 0 0 1.7-1.3L20.5 8H5.3" '.$f.'/><circle cx="9.5" cy="20" r="1.4"/><circle cx="17.5" cy="20" r="1.4"/>',
        'bell'           => '<path d="M6 16.5v-5.2a6 6 0 0 1 12 0v5.2l1.6 2H4.4z" '.$f.'/><path d="M10 21.2a2.2 2.2 0 0 0 4 0"/>',
        'user'           => '<circle cx="12" cy="8" r="4" '.$f.'/><path d="M4.5 20.5a7.5 7.5 0 0 1 15 0"/>',
        'logout'         => '<path d="M9.5 20.5H5.5A1.5 1.5 0 0 1 4 19V5a1.5 1.5 0 0 1 1.5-1.5h4"/><path d="M16 16.5l4.5-4.5L16 7.5M20.5 12H9.5"/>',

        // ---------- Kontrol & panah ----------
        'menu'           => '<path d="M4 7h16M4 12h11M4 17h16"/>',
        'close'          => '<path d="M6 6l12 12M18 6 6 18"/>',
        'chevron-down'   => '<path d="M6 9.5l6 6 6-6"/>',
        'chevron-right'  => '<path d="M9.5 6l6 6-6 6"/>',
        'arrow-right'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'check'          => '<path d="M5 12.5l4.5 4.5L19.5 7"/>',

        // ---------- Umum ----------
        'star'           => '<path d="M12 3l2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.8 1-6.1L3.2 9.4l6.1-.9L12 3z" fill="currentColor" stroke="none"/>',
        'star-outline'   => '<path d="M12 3l2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.8 1-6.1L3.2 9.4l6.1-.9L12 3z"/>',
        'heart'          => '<path d="M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.5-7 10-7 10z"/>',
        'clock'          => '<circle cx="12" cy="12" r="9" '.$f.'/><path d="M12 7v5l3 2"/>',
        'location'       => '<path d="M12 21s-7-6-7-11a7 7 0 0 1 14 0c0 5-7 11-7 11z" '.$f.'/><circle cx="12" cy="10" r="2.5"/>',
        'calendar'       => '<rect x="3" y="5" width="18" height="16" rx="2.5" '.$f.'/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'sparkle'        => '<path d="M12 3l1.9 5.6L19.5 10.5l-5.6 1.9L12 18l-1.9-5.6L4.5 10.5l5.6-1.9z" '.$f.'/><path d="M19 17v4M17 19h4"/>',
        'whatsapp'       => '<path d="M20.5 3.5A10 10 0 0 0 4 16l-1.5 5.5L8 20a10 10 0 1 0 12.5-16.5z" '.$f.'/><path d="M8.5 8.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 1c-1-.5-1.5-1-2-2l1-1-1-2-1.5 1z"/>',
    ];

    // Nama lain yang menunjuk ikon yang sama
    $alias = ['info' => 'info-circle'];
    $kunci = $alias[$name] ?? $name;

    $path = $paths[$kunci] ?? $paths['search'];
@endphp

<svg xmlns="http://www.w3.org/2000/svg"
     width="{{ $size }}"
     height="{{ $size }}"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="1.75"
     stroke-linecap="round"
     stroke-linejoin="round"
     aria-hidden="true"
     {{ $attributes->class(['al-ikon', 'al-ikon-'.$kunci]) }}>
    {!! $path !!}
</svg>