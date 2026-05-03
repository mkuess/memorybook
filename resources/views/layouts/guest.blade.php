<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'memorybook') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#2F2E2A] antialiased bg-[#F8F5ED]">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">

            <div class="mb-6">
                <a href="/" class="text-2xl font-bold tracking-tight text-[#2F2E2A] hover:text-brand-700 transition">
                    memorybook
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-8 bg-white border border-[#DDD6CA] overflow-hidden sm:rounded-lg shadow-sm">
                {{ $slot }}
            </div>

        </div>
    </body>
</html>
