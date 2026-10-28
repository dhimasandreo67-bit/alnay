<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Owner') — {{ config('alnay.brand.nama') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-ink font-sans antialiased min-h-screen flex">

    @include('partials.owner-sidebar')

    <div class="flex-1 flex flex-col min-w-0">
        @include('partials.owner-topbar', [
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