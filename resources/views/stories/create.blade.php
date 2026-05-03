<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Neue Erinnerung – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6">

                    <form method="POST" action="{{ route('memory-pages.stories.store', $memoryPage) }}">
                        @csrf

                        <div class="mb-5">
                            <label for="title" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                                Titel <span class="text-[#9A4F3F]">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                required
                                class="w-full border-[#DDD6CA] rounded-md shadow-sm focus:ring-brand-600 focus:border-brand-600 @error('title') border-[#9A4F3F] @enderror"
                            >
                            @error('title')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="content" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                                Inhalt <span class="text-[#9A4F3F]">*</span>
                            </label>
                            <textarea
                                id="content"
                                name="content"
                                rows="8"
                                required
                                class="w-full border-[#DDD6CA] rounded-md shadow-sm focus:ring-brand-600 focus:border-brand-600 @error('content') border-[#9A4F3F] @enderror"
                            >{{ old('content') }}</textarea>
                            @error('content')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="is_published"
                                    value="1"
                                    {{ old('is_published') ? 'checked' : '' }}
                                    class="text-brand-600 border-[#DDD6CA] rounded focus:ring-brand-600"
                                >
                                <span class="text-sm text-[#2F2E2A]">Sofort veröffentlichen</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:bg-brand-700 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                                Erinnerung speichern
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
