<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            QR-Code – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6 space-y-4">

                    <div>
                        <dt class="text-sm font-medium text-[#706B62]">Person</dt>
                        <dd class="mt-1 text-lg text-[#2F2E2A]">{{ $memoryPage->person_name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-[#706B62]">QR-Code</dt>
                        <dd class="mt-1 font-mono text-[#2F2E2A]">{{ $qr->short_code }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-[#706B62]">Öffentliche URL</dt>
                        <dd class="mt-1">
                            <a href="{{ route('memory-pages.public', $qr->short_code) }}"
                               class="text-brand-600 hover:text-brand-700 break-all">
                                {{ route('memory-pages.public', $qr->short_code) }}
                            </a>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-[#706B62]">Aufrufe</dt>
                        <dd class="mt-1 text-[#2F2E2A]">{{ $qr->scan_count }}</dd>
                    </div>

                    <div class="pt-4">
                        <a href="{{ route('memory-pages.edit', $memoryPage) }}"
                           class="text-sm text-[#706B62] hover:text-[#2F2E2A]">
                            &larr; Zurück zur Bearbeitung
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
