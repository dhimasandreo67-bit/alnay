<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', config('alnay.brand.nama') . ' — ' . config('alnay.brand.tagline'))">

    <title>@yield('title', config('alnay.brand.nama'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
</body>
</html>