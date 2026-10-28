<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name' => 'search', 'size' => 20]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['name' => 'search', 'size' => 20]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $paths = [
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    'cart'      => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h2l2.5 12.5a2 2 0 0 0 2 1.5h9a2 2 0 0 0 2-1.6L21 8H5"/>',
    'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    
    // TAMBAHKAN ICON BARU DI BAWAH INI:
    'home'      => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
    'dress'     => '<path d="M6 3h12l3 6-3 12H6L3 9z"/><path d="M9 3v18"/><path d="M15 3v18"/><path d="M6 9h12"/>',
    'info-circle' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
    'bell'      => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
    
    'menu'      => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'cart'      => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h2l2.5 12.5a2 2 0 0 0 2 1.5h9a2 2 0 0 0 2-1.6L21 8H5"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'menu'      => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'star'      => '<path d="M12 3l2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.8 1-6.1L3.2 9.4l6.1-.9L12 3z" fill="currentColor" stroke="none"/>',
        'star-outline' => '<path d="M12 3l2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.8 1-6.1L3.2 9.4l6.1-.9L12 3z"/>',
        'whatsapp'  => '<path d="M20.5 3.5A10 10 0 0 0 4 16l-1.5 5.5L8 20a10 10 0 1 0 12.5-16.5z"/><path d="M8.5 8.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 1c-1-.5-1.5-1-2-2l1-1-1-2-1.5 1z"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'location'  => '<path d="M12 21s-7-6-7-11a7 7 0 0 1 14 0c0 5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/>',
        'heart'     => '<path d="M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.5-7 10-7 10z"/>',
        'check'     => '<path d="m5 12 5 5L20 7"/>',
        'sparkle'   => '<path d="M12 3v6M12 15v6M3 12h6M15 12h6"/>',
    ];
    $path = $paths[$name] ?? $paths['search'];
?>

<svg xmlns="http://www.w3.org/2000/svg"
     width="<?php echo e($size); ?>"
     height="<?php echo e($size); ?>"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="1.75"
     stroke-linecap="round"
     stroke-linejoin="round"
     aria-hidden="true"
     <?php echo e($attributes); ?>>
    <?php echo $path; ?>

</svg><?php /**PATH D:\alnay\resources\views/components/icon.blade.php ENDPATH**/ ?>