<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Support
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 p-4 bg-[#EAF0E8] border border-[#B5CDB0] rounded text-[#3D5C38] text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg p-6">
                <p class="text-sm text-[#706B62] mb-6">
                    Schreib uns, wenn du ein Problem hast, Hilfe brauchst oder einen Verbesserungsvorschlag senden möchtest.
                </p>

                <form method="POST" action="{{ route('support.store') }}" class="space-y-5">
                    @csrf

                    {{-- Anliegen --}}
                    <div>
                        <x-input-label for="category" value="Anliegen" />
                        <select id="category" name="category"
                            class="mt-1 block w-full border border-[#DDD6CA] rounded shadow-sm text-sm text-[#2F2E2A] focus:border-brand-600 focus:ring-brand-600 @error('category') border-red-400 @enderror">
                            <option value="">– bitte wählen –</option>
                            @foreach (['Problem', 'Frage', 'Verbesserungsvorschlag', 'Sonstiges'] as $opt)
                                <option value="{{ $opt }}" @selected(old('category') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category')" class="mt-1" />
                    </div>

                    {{-- Betreff --}}
                    <div>
                        <x-input-label for="subject" value="Betreff" />
                        <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full"
                            value="{{ old('subject') }}" maxlength="200" />
                        <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                    </div>

                    {{-- Nachricht --}}
                    <div>
                        <x-input-label for="description" value="Nachricht" />
                        <textarea id="description" name="description" rows="6" maxlength="5000"
                            class="mt-1 block w-full border border-[#DDD6CA] rounded shadow-sm text-sm text-[#2F2E2A] focus:border-brand-600 focus:ring-brand-600 @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>

                    {{-- Erinnerungsseite (optional) --}}
                    @if ($memoryPages->isNotEmpty())
                        <div>
                            <x-input-label for="memory_page_id" value="Erinnerungsseite (optional)" />
                            <select id="memory_page_id" name="memory_page_id"
                                class="mt-1 block w-full border border-[#DDD6CA] rounded shadow-sm text-sm text-[#2F2E2A] focus:border-brand-600 focus:ring-brand-600">
                                <option value="">– keine Auswahl –</option>
                                @foreach ($memoryPages as $page)
                                    <option value="{{ $page->id }}" @selected(old('memory_page_id') == $page->id)>
                                        {{ $page->person_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <x-primary-button>
                            Nachricht senden
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
