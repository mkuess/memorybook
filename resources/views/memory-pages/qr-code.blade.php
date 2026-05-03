<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            QR-Code – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-8 flex flex-col items-center gap-6">

                    {{-- Privacy warning --}}
                    @if ($memoryPage->visibility === 'private')
                        <div class="w-full rounded-md border border-[#E8C87A] bg-[#FEF9EE] px-4 py-3 text-sm text-[#7A5C1E]">
                            <p class="font-semibold">Profilseite ist privat</p>
                            <p class="mt-1">Diese Profilseite ist derzeit nicht öffentlich aufrufbar, weil die Sichtbarkeit auf Privat gestellt ist.</p>
                            <p class="mt-1">Ändere die Sichtbarkeit auf „Nur per Link" oder „Öffentlich", damit der QR-Code für Besucher funktioniert.</p>
                        </div>
                    @endif

                    {{-- QR code image --}}
                    @if ($qr->png_path && Storage::disk('public')->exists($qr->png_path))
                        <img
                            src="{{ Storage::disk('public')->url($qr->png_path) }}"
                            alt="QR-Code für {{ $memoryPage->person_name }}"
                            class="w-64 h-64"
                        >
                    @endif

                    {{-- Label below QR --}}
                    <div class="text-center leading-snug">
                        <p class="text-sm font-medium text-[#706B62]">memorybook.com</p>
                        <p class="text-lg font-bold tracking-widest text-[#2F2E2A]">{{ strtoupper($qr->short_code) }}</p>
                    </div>

                    {{-- Download buttons --}}
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full">
                        <a href="{{ route('memory-pages.qr-code.download-png', $memoryPage) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            PNG herunterladen
                        </a>
                        <a href="{{ route('memory-pages.qr-code.download-pdf', $memoryPage) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-[#DDD6CA] text-[#2F2E2A] text-sm font-semibold rounded hover:bg-[#F8F5ED] focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            PDF herunterladen
                        </a>
                    </div>

                    {{-- Meta info --}}
                    <dl class="w-full divide-y divide-[#DDD6CA] text-sm">
                        <div class="py-3">
                            <dt class="text-[#706B62]">Öffentliche URL</dt>
                            <dd class="mt-0.5 break-all">
                                <a href="{{ route('memory-pages.public', $qr->short_code) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="text-brand-600 hover:text-brand-700">
                                    {{ route('memory-pages.public', $qr->short_code) }}
                                </a>
                            </dd>
                        </div>
                        <div class="py-3">
                            <dt class="text-[#706B62]">Aufrufe</dt>
                            <dd class="mt-0.5 text-[#2F2E2A] font-medium">{{ $qr->scan_count }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('memory-pages.edit', $memoryPage) }}"
                       class="text-sm text-[#706B62] hover:text-[#2F2E2A]">
                        &larr; Zurück zur Bearbeitung
                    </a>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
