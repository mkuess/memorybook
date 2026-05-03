<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Bestellung eingegangen
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-8 flex flex-col items-center gap-6 text-center">

                    <div class="w-14 h-14 rounded-full bg-[#EBF0EB] flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-[#6F7F68]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-[#2F2E2A] mb-2">Bestellung eingegangen.</h3>
                        <p class="text-sm text-[#706B62] max-w-sm">
                            Wir prüfen deine Angaben und melden uns.
                        </p>
                    </div>

                    <a href="{{ route('memory-pages.edit', $memoryPage) }}"
                       class="inline-flex items-center px-5 py-2.5 bg-brand-600 border border-transparent rounded font-semibold text-sm text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                        Zur Erinnerungsseite
                    </a>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
