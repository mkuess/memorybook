<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->person_name }} – Erinnerungsseite</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

    <div class="min-h-screen flex items-start justify-center py-16 px-4">
        <div class="w-full max-w-2xl">

            <div class="bg-white rounded-lg shadow-sm p-8">

                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    {{ $page->person_name }}
                </h1>

                @if ($page->birth_date || $page->death_date)
                    <p class="text-gray-500 text-sm mb-6">
                        @if ($page->birth_date)
                            {{ $page->birth_date->format('d.m.Y') }}
                        @endif
                        @if ($page->birth_date && $page->death_date)
                            –
                        @endif
                        @if ($page->death_date)
                            {{ $page->death_date->format('d.m.Y') }}
                        @endif
                    </p>
                @endif

                @if ($page->short_bio)
                    <div class="prose prose-gray max-w-none mt-4">
                        <p>{{ $page->short_bio }}</p>
                    </div>
                @endif

            </div>

        </div>
    </div>

</body>
</html>
