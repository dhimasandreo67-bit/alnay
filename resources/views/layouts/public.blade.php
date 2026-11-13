<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', config('alnay.brand.nama') . ' — ' . config('alnay.brand.tagline'))">
    
    {{-- META UNTUK TRANSISI HALUS --}}
    <meta name="view-transition" content="same-origin" />

    <title>@yield('title', config('alnay.brand.nama'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/home/alnaylogo.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #d1d5db; }
        [x-cloak] { display: none !important; }

        @view-transition {
            navigation: auto;
        }

        /* Efek memudar saat halaman lama hilang */
        ::view-transition-old(root) {
            animation: 300ms cubic-bezier(0.4, 0, 0.2, 1) both fade-out;
        }

        /* Efek memudar saat halaman baru muncul */
        ::view-transition-new(root) {
            animation: 300ms cubic-bezier(0.4, 0, 0.2, 1) both fade-in;
        }

        @keyframes fade-out {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.98); }
        }

        @keyframes fade-in {
            from { opacity: 0; transform: scale(1.02); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script>
        // Matikan fitur auto-scroll bawaan browser
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
    </script>
</head>

{{-- Class 'preload' DIHAPUS agar tidak bentrok dengan transisi --}}
<body class="bg-cream text-ink font-sans antialiased min-h-screen flex flex-col">

    @include('partials.header')

    {{-- overflow-x-clip (bukan hidden): hidden membuat <main> jadi kontainer scroll sendiri dengan scrollbar kedua dan tinggi terkunci --}}
    <main class="flex-1 overflow-x-clip">
        @yield('content')
    </main>

    @include('partials.footer')

    <x-toast />
    @stack('modals')

    @stack('scripts')

    <script>
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdownNotif');
            const btn = event.target.closest('button[onclick*="dropdownNotif"]');
            if (dropdown && !dropdown.contains(event.target) && !btn) {
                dropdown.classList.add('hidden');
            }
        });
    </script>

    <x-back-to-top />
</body>
</html>