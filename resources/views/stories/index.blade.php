<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Erinnerungen – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('memory-pages.edit', $memoryPage) }}"
                   class="text-sm text-[#706B62] hover:text-[#2F2E2A]">
                    &larr; Zurück zur Bearbeitung
                </a>
                <a href="{{ route('memory-pages.stories.create', $memoryPage) }}"
                   class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                    Erinnerung hinzufügen
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-[#EEF1ED] border border-[#6F7F68] rounded text-[#6F7F68] text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($stories->isEmpty())
                <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                    <div class="p-6 text-[#706B62] text-sm">
                        Es wurden noch keine Erinnerungen hinzugefügt.
                    </div>
                </div>
            @else
                <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                    <ul class="divide-y divide-[#DDD6CA]">
                        @foreach ($stories as $story)
                            <li class="p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        @if ($story->image_path)
                                            <div class="mb-2">
                                                <img src="{{ Storage::disk('public')->url($story->image_path) }}"
                                                     alt=""
                                                     class="h-14 object-cover rounded border border-[#DDD6CA]">
                                            </div>
                                        @endif
                                        <p class="mt-1 text-sm text-[#706B62] line-clamp-2">{{ $story->content }}</p>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-3">
                                        @if ($story->is_visitor_submission && ! $story->is_published)
                                            <span class="text-xs px-2 py-0.5 rounded bg-[#FDF7E3] text-[#9A7A2F]">
                                                Wartet auf E-Mail-Bestätigung
                                            </span>
                                        @elseif ($story->is_published)
                                            <span class="text-xs px-2 py-0.5 rounded bg-[#E8EDE7] text-[#6F7F68]">
                                                Veröffentlicht
                                            </span>
                                        @else
                                            <span class="text-xs px-2 py-0.5 rounded bg-[#EFEAE1] text-[#706B62]">
                                                Entwurf
                                            </span>
                                        @endif
                                        @if (! $story->is_visitor_submission)
                                            <a href="{{ route('memory-pages.stories.edit', [$memoryPage, $story]) }}"
                                               class="text-sm text-brand-600 hover:text-brand-700">
                                                Bearbeiten
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
