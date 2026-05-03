<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->person_name }} – Erinnerungsseite</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8F5ED] text-gray-900 antialiased">

    {{-- Preview notice banner --}}
    @if (($previewMode ?? null) === 'owner')
        <div class="bg-amber-50 border-b border-amber-200 px-4 py-3 text-sm text-amber-800 text-center">
            Vorschau: Diese Seite ist derzeit nicht öffentlich sichtbar.
        </div>
    @elseif (($previewMode ?? null) === 'admin')
        <div class="bg-blue-50 border-b border-blue-200 px-4 py-3 text-sm text-blue-800 text-center">
            Admin-Vorschau: Diese Seite ist öffentlich möglicherweise nicht sichtbar.
        </div>
    @endif

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

                <h1 class="text-2xl sm:text-3xl font-bold text-brand-900 mb-1 text-center">
                    {{ $page->person_name }}
                </h1>

                @if ($page->birth_date || $page->death_date)
                    <p class="text-gray-500 text-sm text-center mb-0">
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

                {{-- Share button — directly below date section --}}
                <div class="mt-5 flex flex-col items-center gap-2">
                    <button onclick="shareProfile()"
                            class="inline-flex items-center px-5 py-2 bg-brand-700 text-white text-sm font-medium rounded hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                        Profil teilen
                    </button>
                    <p id="share-msg" class="text-xs text-gray-500 min-h-[1rem]"></p>
                </div>

                @if ($page->short_bio)
                    <div class="mt-6 pt-5 border-t border-gray-100 text-gray-700 leading-relaxed text-sm sm:text-base text-left">
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
                            <h2 class="text-lg sm:text-xl font-semibold text-brand-900 mb-3">
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

    <script>
    function shareProfile() {
        var url  = window.location.href;
        var title = '{{ addslashes($page->person_name) }} – Erinnerungsseite';
        var msg  = document.getElementById('share-msg');
        if (navigator.share) {
            navigator.share({ title: title, url: url }).catch(function () {});
        } else if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                msg.textContent = 'In die Zwischenablage kopiert.';
            }).catch(function () {
                msg.textContent = 'Link konnte nicht automatisch kopiert werden.';
            });
        } else {
            msg.textContent = 'Link konnte nicht automatisch kopiert werden.';
        }
    }
    </script>

</body>
</html>
