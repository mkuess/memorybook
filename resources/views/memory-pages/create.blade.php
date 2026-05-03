<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Neue Erinnerungsseite anlegen
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6">

                    <form method="POST" action="{{ route('memory-pages.store') }}">
                        @csrf

                        <div class="mb-5">
                            <label for="person_name" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                                Name der Person <span class="text-[#9A4F3F]">*</span>
                            </label>
                            <input
                                type="text"
                                id="person_name"
                                name="person_name"
                                value="{{ old('person_name') }}"
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
                                value="{{ old('birth_date') }}"
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
                                value="{{ old('death_date') }}"
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
                            >{{ old('short_bio') }}</textarea>
                            @error('short_bio')
                                <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:bg-brand-700 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                                Erinnerungsseite erstellen
                            </button>
                            <a href="{{ route('dashboard') }}" class="text-sm text-[#706B62] hover:text-[#2F2E2A]">
                                Abbrechen
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
