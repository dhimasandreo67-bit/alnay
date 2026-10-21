@props(['name'])

@php
$icons = [
    'grid'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />',
    'box'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />',
    'hanger'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4a2 2 0 10-2 2c0 .5.2 1 .5 1.4L4 12l-1 4h18l-1-4-6.5-4.6c.3-.4.5-.9.5-1.4" /><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l-1 3h18l-1-3" />',
    'users'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-9.13a4 4 0 110 8 4 4 0 010-8zM16 3.13a4 4 0 010 7.75" />',
    'wallet'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6a2 2 0 012-2h12a2 2 0 012 2v2M3 6v12a2 2 0 002 2h14a2 2 0 002-2V8H17a2 2 0 100 4h4" />',
    'plus-circle' => '<circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-4h8" />',
    'calendar'    => '<rect x="3" y="4" width="18" height="18" rx="2" /><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18" />',
    'shield'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2l8 3v6c0 5-3.5 9-8 11-4.5-2-8-6-8-11V5l8-3z" />',
    'undo'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 010 10H9M3 10l5-5M3 10l5 5" />',
    'truck'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7h11v9H3V7zm11 3h4l3 3v3h-7v-6z" /><circle cx="7.5" cy="18" r="1.5" /><circle cx="17.5" cy="18" r="1.5" />',
    'clock'       => '<circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />',
    'star'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1L12 2z" />',
    'file-text'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 2h9l5 5v15H6V2z" /><path stroke-linecap="round" d="M9 13h6M9 17h6M9 9h2" />',
    'search'      => '<circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="M21 21l-4.3-4.3" />',
    'bell'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9" /><path stroke-linecap="round" d="M13.7 21a2 2 0 01-3.4 0" />',
    'settings'    => '<circle cx="12" cy="12" r="3" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1-1.6 1.7 1.7 0 00-1.9.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.6-1 1.7 1.7 0 00-.3-1.9l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.9.3h.1a1.7 1.7 0 001-1.6V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.9v.1a1.7 1.7 0 001.6 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.6 1z" />',
];
$path = $icons[$name] ?? $icons['grid'];
@endphp

<svg {{ $attributes->merge(['class' => 'inline-block']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
    {!! $path !!}
</svg>