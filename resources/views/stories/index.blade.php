<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Erinnerungen – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('memory-pages.edit', $memoryPage) }}"
                   class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Zurück zur Bearbeitung
                </a>
                <a href="{{ route('memory-pages.stories.create', $memoryPage) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Neue Erinnerung
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($stories->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-600 text-sm">
                        Es wurden noch keine Erinnerungen hinzugefügt.
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <ul class="divide-y divide-gray-100">
                        @foreach ($stories as $story)
                            <li class="p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $story->title }}</p>
                                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $story->content }}</p>
                                    </div>
                                    <span class="shrink-0 text-xs px-2 py-0.5 rounded-full {{ $story->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $story->is_published ? 'Veröffentlicht' : 'Entwurf' }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
