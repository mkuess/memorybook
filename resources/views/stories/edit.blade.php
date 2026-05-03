<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Erinnerung bearbeiten – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6">

                    <form method="POST"
                          action="{{ route('memory-pages.stories.update', [$memoryPage, $story]) }}"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-5">
                            <label for="content" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                                Erinnerung <span class="text-[#9A4F3F]">*</span>
                            </label>
                            <textarea
                                id="content"
                                name="content"
                                rows="8"
                                required
                                class="w-full border-[#DDD6CA] rounded-md shadow-sm focus:ring-brand-600 focus:border-brand-600 @error('content') border-[#9A4F3F] @enderror"
                            >{{ old('content', $story->content) }}</textarea>
                            @error('content')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="image" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                                Bild (optional)
                            </label>
                            @if ($story->image_path)
                                <div class="mb-2">
                                    <img src="{{ Storage::disk('public')->url($story->image_path) }}"
                                         alt="Aktuelles Bild"
                                         class="h-28 object-cover rounded border border-[#DDD6CA]">
                                    <p class="mt-1 text-xs text-[#706B62]">Aktuelles Bild. Ein neues Bild ersetzte es.</p>
                                </div>
                            @endif
                            <input
                                type="file"
                                id="image"
                                name="image"
                                accept="image/jpg,image/jpeg,image/png,image/webp"
                                class="block w-full text-sm text-[#706B62] file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-[#EFEAE1] file:text-[#2F2E2A] hover:file:bg-[#D8D2C8] @error('image') border border-[#9A4F3F] rounded @enderror"
                            >
                            <p class="mt-1 text-xs text-[#706B62]">JPG, PNG oder WebP, max. 5 MB.</p>
                            @error('image')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="is_published"
                                    value="1"
                                    {{ old('is_published', $story->is_published) ? 'checked' : '' }}
                                    class="text-brand-600 border-[#DDD6CA] rounded focus:ring-brand-600"
                                >
                                <span class="text-sm text-[#2F2E2A]">Veröffentlicht</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:bg-brand-700 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                                Änderungen speichern
                            </button>
                            <a href="{{ route('memory-pages.stories.index', $memoryPage) }}"
                               class="text-sm text-[#706B62] hover:text-[#2F2E2A]">
                                Abbrechen
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
