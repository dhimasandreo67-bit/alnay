<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Admin') — {{ config('alnay.brand.nama') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<style>
    /* Wajib ada supaya x-cloak benar-benar menyembunyikan elemen sebelum Alpine siap.
       Tanpa rule ini, elemen ber-atribut x-cloak (mis. modal zoom foto KTP yang
       fixed inset-0) akan sempat kelihatan sekilas (flash) saat halaman baru dimuat,
       sebelum Alpine.js selesai inisialisasi dan menyembunyikannya lewat x-show. */
    [x-cloak] {
        display: none !important;
    }

    /* Custom Scrollbar untuk Modal */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1; /* gray-300 */
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8; /* gray-400 */
}
</style>
<body style="font-family: 'Inter', sans-serif !important;" class="bg-cream text-ink font-sans antialiased min-h-screen flex">

    @include('partials.admin-sidebar')

    <div class="flex-1 flex flex-col min-w-0">
        @include('partials.admin-topbar', [
            'pageTitle' => $__env->yieldContent('pageTitle'),
            'pageSubtitle' => $__env->yieldContent('pageSubtitle'),
        ])

        <main class="flex-1 p-4 md:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    <x-toast />
    @stack('modals')


</body>
</html>