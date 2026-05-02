<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Erinnerung bearbeiten – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <form method="POST" action="{{ route('memory-pages.stories.update', [$memoryPage, $story]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-5">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Titel <span class="text-red-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title', $story->title) }}"
                                required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                            >
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                                Inhalt <span class="text-red-600">*</span>
                            </label>
                            <textarea
                                id="content"
                                name="content"
                                rows="8"
                                required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('content') border-red-500 @enderror"
                            >{{ old('content', $story->content) }}</textarea>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="is_published"
                                    value="1"
                                    {{ old('is_published', $story->is_published) ? 'checked' : '' }}
                                    class="text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                >
                                <span class="text-sm text-gray-700">Veröffentlicht</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Änderungen speichern
                            </button>
                            <a href="{{ route('memory-pages.stories.index', $memoryPage) }}"
                               class="text-sm text-gray-600 hover:text-gray-900">
                                Abbrechen
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
