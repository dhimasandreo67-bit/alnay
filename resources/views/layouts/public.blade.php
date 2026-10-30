<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', config('alnay.brand.nama') . ' — ' . config('alnay.brand.tagline'))">

    <title>@yield('title', config('alnay.brand.nama'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Tambahan CSS untuk Scrollbar Tipis pada Notifikasi --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #e5e7eb; /* gray-200 */
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #d1d5db; /* gray-300 */
        }

        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #d1d5db; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-cream text-ink font-sans antialiased min-h-screen flex flex-col">

    @include('partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')

    <x-toast />
    @stack('modals')

    {{-- layouts/public.blade.php --}}
    @stack('scripts')

    {{-- Script tutup dropdown saat klik di luar --}}
    <script>
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdownNotif');
            const btn = event.target.closest('button[onclick*="dropdownNotif"]');
            if (dropdown && !dropdown.contains(event.target) && !btn) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>