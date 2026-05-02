<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Meine Erinnerungsseiten
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <a href="{{ route('memory-pages.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Neue Erinnerungsseite anlegen
                </a>
            </div>

            @if ($memoryPages->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-600">
                        Sie haben noch keine Erinnerungsseite angelegt.
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    {{-- Desktop table --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-6 py-3">Name</th>
                                    <th class="px-6 py-3">Storys</th>
                                    <th class="px-6 py-3">Freigegeben</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($memoryPages as $page)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('memory-pages.edit', $page) }}"
                                               class="text-gray-900 hover:text-indigo-600 font-medium">
                                                {{ $page->person_name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('memory-pages.stories.index', $page) }}"
                                               class="text-indigo-600 hover:text-indigo-800 font-medium tabular-nums">
                                                {{ $page->stories_count }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($page->is_published)
                                                <span class="text-green-700 font-medium">Ja</span>
                                            @else
                                                <span class="text-gray-400">Nein</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('memory-pages.qr-code', $page) }}"
                                               class="text-sm text-gray-500 hover:text-indigo-600">
                                                QR-Code
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile card list --}}
                    <ul class="sm:hidden divide-y divide-gray-100">
                        @foreach ($memoryPages as $page)
                            <li class="p-4 space-y-2">
                                <a href="{{ route('memory-pages.edit', $page) }}"
                                   class="block text-gray-900 hover:text-indigo-600 font-medium">
                                    {{ $page->person_name }}
                                </a>
                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span>
                                        Storys:
                                        <a href="{{ route('memory-pages.stories.index', $page) }}"
                                           class="text-indigo-600 hover:text-indigo-800 font-medium">
                                            {{ $page->stories_count }}
                                        </a>
                                    </span>
                                    <span>
                                        Freigegeben:
                                        @if ($page->is_published)
                                            <span class="text-green-700 font-medium">Ja</span>
                                        @else
                                            <span class="text-gray-400">Nein</span>
                                        @endif
                                    </span>
                                    <a href="{{ route('memory-pages.qr-code', $page) }}"
                                       class="text-gray-500 hover:text-indigo-600">
                                        QR-Code
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
