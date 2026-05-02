<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->person_name }} – Erinnerungsseite</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

    <div class="min-h-screen py-10 px-4 sm:py-16">
        <div class="w-full max-w-xl mx-auto">

            {{-- Profile header --}}
            <div class="bg-white rounded-xl shadow-sm p-6 sm:p-10">

                @if ($profilePhoto)
                    <div class="flex justify-center mb-5">
                        <img src="{{ Storage::disk('public')->url($profilePhoto->path) }}"
                             alt="{{ $page->person_name }}"
                             class="w-28 h-28 sm:w-36 sm:h-36 object-cover rounded-full shadow">
                    </div>
                @endif

                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1 text-center">
                    {{ $page->person_name }}
                </h1>

                @if ($page->birth_date || $page->death_date)
                    <p class="text-gray-500 text-sm text-center">
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
                    <div class="mt-6 text-gray-700 leading-relaxed text-sm sm:text-base text-left">
                        {{ $page->short_bio }}
                    </div>
                @endif

            </div>

            {{-- Gallery: no heading, just the images --}}
            @if ($galleryImages->isNotEmpty())
                <div class="mt-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($galleryImages as $image)
                            <div class="aspect-square overflow-hidden rounded-lg">
                                <img src="{{ Storage::disk('public')->url($image->path) }}"
                                     alt="{{ $image->original_filename }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Stories --}}
            @if ($stories->isNotEmpty())
                <div class="mt-5 space-y-4">
                    @foreach ($stories as $story)
                        <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
                            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3">
                                {{ $story->title }}
                            </h2>
                            <div class="text-gray-700 leading-relaxed text-sm sm:text-base">
                                {{ $story->content }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

</body>
</html>
