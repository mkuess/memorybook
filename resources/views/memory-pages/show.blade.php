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

                @if ($profilePhoto)
                    <div class="flex justify-center mb-6">
                        <img src="{{ Storage::disk('public')->url($profilePhoto->path) }}"
                             alt="{{ $page->person_name }}"
                             class="w-32 h-32 object-cover rounded-full shadow">
                    </div>
                @endif

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

            @if ($galleryImages->isNotEmpty())
                <div class="mt-8">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach ($galleryImages as $image)
                            <div class="aspect-square overflow-hidden rounded-lg shadow-sm">
                                <img src="{{ Storage::disk('public')->url($image->path) }}"
                                     alt="{{ $image->original_filename }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($stories->isNotEmpty())
                <div class="mt-8 space-y-6">
                    @foreach ($stories as $story)
                        <div class="bg-white rounded-lg shadow-sm p-8">
                            <h2 class="text-xl font-semibold text-gray-900 mb-3">
                                {{ $story->title }}
                            </h2>
                            <div class="prose prose-gray max-w-none">
                                <p>{{ $story->content }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

</body>
</html>
