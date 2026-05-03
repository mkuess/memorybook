<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Meine Erinnerungsseiten
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <a href="{{ route('memory-pages.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:bg-brand-700 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                    Neue Erinnerungsseite anlegen
                </a>
            </div>

            @if ($memoryPages->isEmpty())
                <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                    <div class="p-6 text-[#706B62] text-sm">
                        Sie haben noch keine Erinnerungsseite angelegt.
                    </div>
                </div>
            @else
                <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                    {{-- Desktop table --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#DDD6CA]">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-[#706B62] uppercase tracking-wider">
                                    <th class="px-6 py-3">Name</th>
                                    <th class="px-6 py-3">Storys</th>
                                    <th class="px-6 py-3">Freigegeben</th>
                                    <th class="px-6 py-3 text-right">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#DDD6CA]">
                                @foreach ($memoryPages as $page)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('memory-pages.edit', $page) }}"
                                               class="text-[#2F2E2A] hover:text-brand-700 font-medium">
                                                {{ $page->person_name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('memory-pages.stories.index', $page) }}"
                                               class="text-brand-600 hover:text-brand-700 font-medium tabular-nums">
                                                {{ $page->stories_count }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($page->is_published)
                                                <span class="text-[#6F7F68] font-medium">Ja</span>
                                            @else
                                                <span class="text-[#706B62]">Nein</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-end gap-4">
                                                @if ($page->qrCode)
                                                    {{-- QR-Code --}}
                                                    <a href="{{ route('memory-pages.qr-code', $page) }}"
                                                       class="inline-flex items-center gap-1.5 text-sm text-[#706B62] hover:text-brand-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5ZM6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                                        </svg>
                                                        QR-Code
                                                    </a>

                                                    {{-- Profil aufrufen --}}
                                                    <a href="/m/{{ $page->qrCode->short_code }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="inline-flex items-center gap-1.5 text-sm text-[#706B62] hover:text-brand-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                        </svg>
                                                        Profil aufrufen
                                                    </a>
                                                @else
                                                    <span class="text-sm text-[#706B62] opacity-50">Kein QR-Code</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile card list --}}
                    <ul class="sm:hidden divide-y divide-[#DDD6CA]">
                        @foreach ($memoryPages as $page)
                            <li class="p-4 space-y-2">
                                <a href="{{ route('memory-pages.edit', $page) }}"
                                   class="block text-[#2F2E2A] hover:text-brand-700 font-medium">
                                    {{ $page->person_name }}
                                </a>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-[#706B62]">
                                    <span>
                                        Storys:
                                        <a href="{{ route('memory-pages.stories.index', $page) }}"
                                           class="text-brand-600 hover:text-brand-700 font-medium">
                                            {{ $page->stories_count }}
                                        </a>
                                    </span>
                                    <span>
                                        Freigegeben:
                                        @if ($page->is_published)
                                            <span class="text-[#6F7F68] font-medium">Ja</span>
                                        @else
                                            <span class="text-[#706B62]">Nein</span>
                                        @endif
                                    </span>
                                    @if ($page->qrCode)
                                        <a href="{{ route('memory-pages.qr-code', $page) }}"
                                           class="inline-flex items-center gap-1 text-[#706B62] hover:text-brand-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5ZM6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                            </svg>
                                            QR-Code
                                        </a>
                                        <a href="/m/{{ $page->qrCode->short_code }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 text-[#706B62] hover:text-brand-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                            Profil aufrufen
                                        </a>
                                    @else
                                        <span class="opacity-50">Kein QR-Code</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
