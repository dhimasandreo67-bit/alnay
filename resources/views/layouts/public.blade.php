<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', config('alnay.brand.nama') . ' — ' . config('alnay.brand.tagline'))">

    <title>@yield('title', config('alnay.brand.nama'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #d1d5db; }
        [x-cloak] { display: none !important; }

        /* ANTI-KEDIP: Sembunyikan body saat pertama kali dimuat */
        body.preload {
            visibility: hidden !important;
            opacity: 0 !important;
        }
    </style>

    <script>
        // Matikan fitur auto-scroll bawaan browser
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
    </script>
</head>

<!-- TAMBAHKAN CLASS 'preload' DI SINI -->
<body class="bg-cream text-ink font-sans antialiased min-h-screen flex flex-col preload">

    @include('partials.header')

    <main class="flex-1">
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

    <script>
        // 1. Simpan posisi scroll TEPAT SEBELUM halaman di-refresh
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('scrollPosition', window.scrollY);
        });

        // 2. Kembalikan posisi dan TAMPILKAN halaman
        window.addEventListener('DOMContentLoaded', function() {
            const scrollPos = sessionStorage.getItem('scrollPosition');
            
            // Matikan animasi smooth sementara
            document.documentElement.style.scrollBehavior = 'auto';
            
            if (scrollPos) {
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem('scrollPosition');
            }
            
            // MUNCULKAN HALAMAN SETELAH POSISI DIATUR (Hilangkan class preload)
            document.body.classList.remove('preload');
            
            setTimeout(() => {
                document.documentElement.style.scrollBehavior = 'smooth';
            }, 10);
        });
    </script>
</body>
</html>