<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Erinnerungsseite bearbeiten
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            {{-- Profile photo --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Profilfoto</h3>

                    @if ($profilePhoto)
                        <div class="mb-4">
                            <img src="{{ Storage::disk('public')->url($profilePhoto->path) }}"
                                 alt="Profilfoto"
                                 class="w-32 h-32 object-cover rounded-full shadow">
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mb-4">Noch kein Profilfoto hochgeladen.</p>
                    @endif

                    @if (session('success'))
                        <p class="text-sm text-green-700 mb-3">{{ session('success') }}</p>
                    @endif

                    <form method="POST"
                          action="{{ route('memory-pages.profile-photo.store', $memoryPage) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="flex flex-wrap items-center gap-3">
                            <input type="file"
                                   name="photo"
                                   accept="image/jpeg,image/jpg,image/png,image/webp"
                                   class="text-sm text-gray-600">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Foto hochladen
                            </button>
                        </div>
                        @error('photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </form>
                </div>
            </div>

            {{-- Main form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <form method="POST" action="{{ route('memory-pages.update', $memoryPage) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-5">
                            <label for="person_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Name der Person <span class="text-red-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="person_name"
                                name="person_name"
                                value="{{ old('person_name', $memoryPage->person_name) }}"
                                required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('person_name') border-red-500 @enderror"
                            >
                            @error('person_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Geburtsdatum
                            </label>
                            <input
                                type="date"
                                id="birth_date"
                                name="birth_date"
                                value="{{ old('birth_date', $memoryPage->birth_date?->toDateString()) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('birth_date') border-red-500 @enderror"
                            >
                            @error('birth_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="death_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Sterbedatum
                            </label>
                            <input
                                type="date"
                                id="death_date"
                                name="death_date"
                                value="{{ old('death_date', $memoryPage->death_date?->toDateString()) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('death_date') border-red-500 @enderror"
                            >
                            @error('death_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="short_bio" class="block text-sm font-medium text-gray-700 mb-1">
                                Kurze Biografie
                            </label>
                            <textarea
                                id="short_bio"
                                name="short_bio"
                                rows="4"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('short_bio') border-red-500 @enderror"
                            >{{ old('short_bio', $memoryPage->short_bio) }}</textarea>
                            @error('short_bio')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Sichtbarkeit
                            </label>
                            <div class="space-y-2">
                                @foreach (['private' => 'Privat', 'link' => 'Nur per Link', 'public' => 'Öffentlich'] as $value => $label)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="radio"
                                            name="visibility"
                                            value="{{ $value }}"
                                            {{ old('visibility', $memoryPage->visibility) === $value ? 'checked' : '' }}
                                            class="text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        >
                                        <span class="text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('visibility')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Änderungen speichern
                            </button>
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                Abbrechen
                            </a>
                        </div>

                    </form>

                </div>
            </div>

            {{-- Gallery --}}
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Galerie</h3>

                    @if ($galleryImages->isNotEmpty())
                        <ul class="divide-y divide-gray-100 mb-4">
                            @foreach ($galleryImages as $image)
                                @php $imageUrl = Storage::disk('public')->url($image->path); @endphp
                                <li class="flex items-center gap-3 py-2 min-w-0">
                                    <a href="{{ $imageUrl }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="shrink-0 block overflow-hidden rounded"
                                       style="width:64px;height:64px;min-width:64px;">
                                        <img src="{{ $imageUrl }}"
                                             alt="{{ $image->original_filename }}"
                                             style="width:64px;height:64px;max-width:64px;object-fit:cover;display:block;flex-shrink:0;">
                                    </a>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $image->original_filename }}</p>
                                        <p class="text-xs text-gray-500">{{ number_format($image->size_bytes / 1024, 0) }} KB</p>
                                    </div>
                                    <form method="POST"
                                          action="{{ route('memory-pages.gallery.destroy', [$memoryPage, $image]) }}"
                                          onsubmit="return confirm('Bild wirklich löschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 border border-transparent rounded text-xs font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            Löschen
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 mb-4">Noch keine Galeriebilder hochgeladen.</p>
                    @endif

                    @if (session('gallery_success'))
                        <p class="text-sm text-green-700 mb-3">{{ session('gallery_success') }}</p>
                    @endif

                    @if ($galleryImages->count() >= 5)
                        <p class="text-sm text-amber-700">Maximal 5 Galeriebilder möglich.</p>
                    @else
                        <form method="POST"
                              action="{{ route('memory-pages.gallery.store', $memoryPage) }}"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="flex flex-wrap items-center gap-3">
                                <input type="file"
                                       name="image"
                                       accept="image/jpeg,image/jpg,image/png,image/webp"
                                       class="text-sm text-gray-600">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Bild hochladen
                                </button>
                            </div>
                            @error('image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </form>
                    @endif
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Erinnerungen</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $memoryPage->stories()->count() }} Erinnerung(en) vorhanden
                        </p>
                    </div>
                    <a href="{{ route('memory-pages.stories.index', $memoryPage) }}"
                       class="text-sm text-indigo-600 hover:text-indigo-800">
                        Verwalten &rarr;
                    </a>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Veröffentlichung</h3>

                    @if ($memoryPage->is_published)
                        <p class="text-sm text-green-700 mb-4">
                            Diese Seite ist veröffentlicht.
                            @if ($memoryPage->published_at)
                                <span class="text-gray-500">({{ $memoryPage->published_at->format('d.m.Y H:i') }})</span>
                            @endif
                        </p>
                        <form method="POST" action="{{ route('memory-pages.unpublish', $memoryPage) }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Veröffentlichung zurückziehen
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('memory-pages.publish', $memoryPage) }}">
                            @csrf
                            <div class="mb-4">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="consent"
                                        value="1"
                                        {{ old('consent') ? 'checked' : '' }}
                                        class="mt-0.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 @error('consent') border-red-500 @enderror"
                                    >
                                    <span class="text-sm text-gray-700">
                                        Ich bestätige, dass ich berechtigt bin, diese Erinnerungsseite zu veröffentlichen.
                                    </span>
                                </label>
                                @error('consent')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Seite veröffentlichen
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
