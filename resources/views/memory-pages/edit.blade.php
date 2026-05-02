<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Erinnerungsseite bearbeiten
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
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
        </div>
    </div>
</x-app-layout>
