<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Adminbereich' }} – memorybook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-56 flex-shrink-0 bg-gray-900 text-gray-100 flex flex-col">

        <div class="px-6 py-5 text-lg font-semibold tracking-wide border-b border-gray-700">
            memorybook
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 font-medium' : '' }}">
                Übersicht
            </a>
            <a href="#"
               class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-700">
                Benutzer
            </a>
            <a href="#"
               class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-700">
                Erinnerungsseiten
            </a>
            <a href="#"
               class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-700">
                Meldungen
            </a>
            <a href="#"
               class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-700">
                Plaketten
            </a>
        </nav>

        <div class="px-4 py-4 border-t border-gray-700 text-xs text-gray-400">
            {{ Auth::user()->name }}
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="hover:text-white">Abmelden</button>
            </form>
        </div>

    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col">

        <header class="bg-white shadow-sm px-8 py-4">
            <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Adminbereich' }}</h1>
        </header>

        <main class="flex-1 px-8 py-6">
            {{ $slot }}
        </main>

    </div>

</div>

</body>
</html>
