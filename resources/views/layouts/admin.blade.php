<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Admin') — {{ config('alnay.brand.nama') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
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

</body>
</html>