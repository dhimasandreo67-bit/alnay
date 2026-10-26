@props(['name'])

{{--
    Minimal inline icon set so the views above don't depend on an external
    icon package. If you already use a package (e.g. blade-ui-kit/blade-icons),
    swap <x-admin::admin-icon> for your own <x-icon> component — the API is the same.
--}}

@php
    $paths = [
        'grid'        => 'M4 5h6v6H4V5Zm10 0h6v6h-6V5ZM4 15h6v6H4v-6Zm10 0h6v6h-6v-6Z',
        'box'         => 'M3.5 7.5 12 3l8.5 4.5M3.5 7.5 12 12m-8.5-4.5V16.5L12 21m0-9v9m0-9 8.5-4.5M12 21l8.5-4.5V7.5',
        'hanger'      => 'M12 3a2 2 0 1 1 1.5 3.3L12 7v2m0 0 8 5a2 2 0 0 1-1 3.7H5A2 2 0 0 1 4 14l8-5Z',
        'users'       => 'M16 14a4 4 0 1 0-4-4m4 4a4 4 0 1 1-4-4m4 4c3 0 6 1.3 6 4v2H6v-2c0-1.4 1-2.6 2.6-3.3M10 10a4 4 0 1 1 0-8',
        'wallet'      => 'M3 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2H3V7Zm0 2h16v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Zm12 4.5h2',
        'plus-circle' => 'M12 8v8m-4-4h8m6 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'calendar'    => 'M8 3v3m8-3v3M4 9h16M5 6h14a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z',
        'shield'      => 'M12 3 4 6v5c0 4.6 3.2 7.9 8 10 4.8-2.1 8-5.4 8-10V6l-8-3Z',
        'undo'        => 'M7 8H4V5m0 3a9 9 0 1 1-2 5.5',
        'truck'       => 'M3 7h11v9H3V7Zm11 3h4l3 3v3h-7v-6ZM6.5 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm11 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z',
        'clock'       => 'M12 7v5l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'star'        => 'm12 3 2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L12 17l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L12 3Z',
        'file-text'   => 'M8 3h6l4 4v14H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2Zm5 0v5h5M9 13h6m-6 4h4',
        'settings'    => 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8-3a7.9 7.9 0 0 0-.2-1.8l2-1.6-2-3.4-2.3.9a8 8 0 0 0-3-1.8L14 2h-4l-.5 2.3a8 8 0 0 0-3 1.8l-2.3-.9-2 3.4 2 1.6a8 8 0 0 0 0 3.6l-2 1.6 2 3.4 2.3-.9a8 8 0 0 0 3 1.8L10 22h4l.5-2.3a8 8 0 0 0 3-1.8l2.3.9 2-3.4-2-1.6c.1-.6.2-1.2.2-1.8Z',
        'search'      => 'm21 21-4.3-4.3M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z',
        'bell'        => 'M6 8a6 6 0 1 1 12 0c0 4 1.5 5.5 2 6H4c.5-.5 2-2 2-6Zm4.3 10a1.8 1.8 0 0 0 3.4 0',
        'dollar'      => 'M12 3v18m4-14.5c0-1.4-1.8-2.5-4-2.5s-4 1.1-4 2.5 1.8 2.3 4 2.8 4 1.4 4 2.8-1.8 2.4-4 2.4-4-1-4-2.4',
        'chevron-right' => 'm9 6 6 6-6 6',
        'wrench'      => 'M14.7 6.3a4 4 0 0 1-5.4 5.4L4 17v3h3l5.3-5.3a4 4 0 0 1 5.4-5.4L21 7l-3-3-3.3 2.3Z',
        'alert'       => 'M12 9v4m0 4h.01M10.3 3.9 2.5 18a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z',
    ];
    $d = $paths[$name] ?? $paths['grid'];
@endphp

<svg {{ $attributes->merge(['class' => 'h-4 w-4']) }} viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    <path d="{{ $d }}" />
</svg>
