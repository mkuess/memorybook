<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Erinnerungsseite bearbeiten
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Profilfoto --}}
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-[#2F2E2A] mb-4">Profilfoto</h3>

                    @if ($profilePhoto)
                        <div class="mb-4">
                            <img src="{{ Storage::disk('public')->url($profilePhoto->path) }}"
                                 alt="Profilfoto"
                                 class="w-32 h-32 object-cover rounded-full shadow">
                        </div>
                    @else
                        <p class="text-sm text-[#706B62] mb-4">Noch kein Profilfoto hochgeladen.</p>
                    @endif

                    @if (session('success'))
                        <p class="text-sm text-[#6F7F68] mb-3">{{ session('success') }}</p>
                    @endif

                    <form method="POST"
                          action="{{ route('memory-pages.profile-photo.store', $memoryPage) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="flex flex-wrap items-center gap-3">
                            <input type="file"
                                   name="photo"
                                   accept="image/jpeg,image/jpg,image/png,image/webp"
                                   class="text-sm text-[#706B62]">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                                Foto hochladen
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-[#706B62]">Erlaubt: JPG, PNG oder WebP bis 5 MB.</p>
                        @error('photo')
                            <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                        @enderror
                    </form>
                </div>
            </div>

            {{-- 2. Basisdaten --}}
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-[#2F2E2A] mb-4">Basisdaten</h3>

                    @if (session('basisdaten_success'))
                        <p class="text-sm text-[#6F7F68] mb-4">{{ session('basisdaten_success') }}</p>
                    @endif

                    <form method="POST" action="{{ route('memory-pages.update', $memoryPage) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-5">
                            <label for="person_name" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                                Name der Person <span class="text-[#9A4F3F]">*</span>
                            </label>
                            <input
                                type="text"
                                id="person_name"
                                name="person_name"
                                value="{{ old('person_name', $memoryPage->person_name) }}"
                                required
                                class="w-full border-[#DDD6CA] rounded-md shadow-sm focus:ring-brand-600 focus:border-brand-600 @error('person_name') border-[#9A4F3F] @enderror"
                            >
                            @error('person_name')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="birth_date" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                                Geburtsdatum
                            </label>
                            <input
                                type="date"
                                id="birth_date"
                                name="birth_date"
                                value="{{ old('birth_date', $memoryPage->birth_date?->toDateString()) }}"
                                class="w-full border-[#DDD6CA] rounded-md shadow-sm focus:ring-brand-600 focus:border-brand-600 @error('birth_date') border-[#9A4F3F] @enderror"
                            >
                            @error('birth_date')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="death_date" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                                Sterbedatum
                            </label>
                            <input
                                type="date"
                                id="death_date"
                                name="death_date"
                                value="{{ old('death_date', $memoryPage->death_date?->toDateString()) }}"
                                class="w-full border-[#DDD6CA] rounded-md shadow-sm focus:ring-brand-600 focus:border-brand-600 @error('death_date') border-[#9A4F3F] @enderror"
                            >
                            @error('death_date')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="short_bio" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                                Kurze Biografie
                            </label>
                            <textarea
                                id="short_bio"
                                name="short_bio"
                                rows="4"
                                class="w-full border-[#DDD6CA] rounded-md shadow-sm focus:ring-brand-600 focus:border-brand-600 @error('short_bio') border-[#9A4F3F] @enderror"
                            >{{ old('short_bio', $memoryPage->short_bio) }}</textarea>
                            @error('short_bio')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:bg-brand-700 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                            Änderungen speichern
                        </button>

                    </form>
                </div>
            </div>

            {{-- 3. Galerie --}}
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-[#2F2E2A] mb-4">Galerie</h3>

                    @if ($galleryImages->isNotEmpty())
                        <ul class="divide-y divide-[#DDD6CA] mb-4">
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
                                        <p class="text-sm font-medium text-[#2F2E2A] truncate">{{ $image->original_filename }}</p>
                                        <p class="text-xs text-[#706B62]">{{ number_format($image->size_bytes / 1024, 0) }} KB</p>
                                    </div>
                                    <form method="POST"
                                          action="{{ route('memory-pages.gallery.destroy', [$memoryPage, $image]) }}"
                                          onsubmit="return confirm('Bild wirklich löschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#9A4F3F] border border-transparent rounded text-xs font-semibold text-white hover:bg-[#7E3F31] focus:outline-none focus:ring-2 focus:ring-[#9A4F3F] focus:ring-offset-2 transition ease-in-out duration-150">
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
                        <p class="text-sm text-[#706B62] mb-4">Noch keine Galeriebilder hochgeladen.</p>
                    @endif

                    @if (session('gallery_success'))
                        <p class="text-sm text-[#6F7F68] mb-3">{{ session('gallery_success') }}</p>
                    @endif

                    @if ($galleryImages->count() >= 5)
                        <p class="text-sm text-[#B08A4A]">Maximal 5 Galeriebilder möglich.</p>
                    @else
                        <form method="POST"
                              action="{{ route('memory-pages.gallery.store', $memoryPage) }}"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="flex flex-wrap items-center gap-3">
                                <input type="file"
                                       name="image"
                                       accept="image/jpeg,image/jpg,image/png,image/webp"
                                       class="text-sm text-[#706B62]">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Bild hochladen
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-[#706B62]">Erlaubt: JPG, PNG oder WebP bis 5 MB. Maximal 5 Bilder.</p>
                            @error('image')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </form>
                    @endif
                </div>
            </div>

            {{-- 4. Sichtbarkeit und Veröffentlichung --}}
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-[#2F2E2A] mb-4">Sichtbarkeit und Veröffentlichung</h3>

                    {{-- Visibility --}}
                    @if (session('visibility_success'))
                        <p class="text-sm text-[#6F7F68] mb-4">{{ session('visibility_success') }}</p>
                    @endif

                    <form method="POST"
                          action="{{ route('memory-pages.update-visibility', $memoryPage) }}"
                          class="mb-6">
                        @csrf
                        @method('PUT')

                        <fieldset class="mb-4">
                            <legend class="text-sm font-medium text-[#2F2E2A] mb-2">Sichtbarkeit</legend>
                            <div class="space-y-2">
                                @foreach (['private' => 'Privat', 'link' => 'Nur per Link', 'public' => 'Öffentlich'] as $value => $label)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="radio"
                                            name="visibility"
                                            value="{{ $value }}"
                                            {{ old('visibility', $memoryPage->visibility) === $value ? 'checked' : '' }}
                                            class="text-brand-600 border-[#DDD6CA] focus:ring-brand-600"
                                        >
                                        <span class="text-sm text-[#2F2E2A]">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('visibility')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                            Sichtbarkeit speichern
                        </button>
                    </form>

                    <hr class="border-[#DDD6CA] mb-6">

                    {{-- Publish / Unpublish --}}
                    @if ($memoryPage->is_published)
                        <p class="text-sm text-[#6F7F68] mb-4">
                            Diese Seite ist veröffentlicht.
                            @if ($memoryPage->published_at)
                                <span class="text-[#706B62]">({{ $memoryPage->published_at->format('d.m.Y H:i') }})</span>
                            @endif
                        </p>
                        <form method="POST" action="{{ route('memory-pages.unpublish', $memoryPage) }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-[#9A4F3F] border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#7E3F31] focus:outline-none focus:ring-2 focus:ring-[#9A4F3F] focus:ring-offset-2 transition ease-in-out duration-150">
                                Nicht mehr veröffentlichen
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
                                        class="mt-0.5 text-brand-600 border-[#DDD6CA] rounded focus:ring-brand-600 @error('consent') border-[#9A4F3F] @enderror"
                                    >
                                    <span class="text-sm text-[#2F2E2A]">
                                        Ich bestätige, dass ich berechtigt bin, diese Erinnerungsseite zu veröffentlichen.
                                    </span>
                                </label>
                                @error('consent')
                                    <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-[#6F7F68] border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#5A6A54] focus:outline-none focus:ring-2 focus:ring-[#6F7F68] focus:ring-offset-2 transition ease-in-out duration-150">
                                Veröffentlichen
                            </button>
                        </form>
                    @endif

                </div>
            </div>

            {{-- 5. Links / nächste Schritte --}}
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-[#2F2E2A] mb-4">Weitere Optionen</h3>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('memory-pages.stories.index', $memoryPage) }}"
                           class="inline-flex items-center px-4 py-2 bg-[#EFEAE1] border border-[#DDD6CA] rounded font-semibold text-xs text-[#2F2E2A] uppercase tracking-widest hover:bg-[#D8D2C8] focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                            Stories verwalten
                        </a>
                        <a href="{{ route('memory-pages.qr-code', $memoryPage) }}"
                           class="inline-flex items-center px-4 py-2 bg-[#EFEAE1] border border-[#DDD6CA] rounded font-semibold text-xs text-[#2F2E2A] uppercase tracking-widest hover:bg-[#D8D2C8] focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                            QR-Code anzeigen
                        </a>
                        @if ($memoryPage->qrCode)
                            <a href="/m/{{ $memoryPage->qrCode->short_code }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#EFEAE1] border border-[#DDD6CA] rounded font-semibold text-xs text-[#2F2E2A] uppercase tracking-widest hover:bg-[#D8D2C8] focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                Profilseite aufrufen
                            </a>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
