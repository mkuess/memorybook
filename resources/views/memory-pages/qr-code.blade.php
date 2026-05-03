<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            QR-Code – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-sm mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-8 flex flex-col items-center gap-6">

                    {{-- QR code image --}}
                    @if ($qr->png_path && Storage::disk('public')->exists($qr->png_path))
                        <img
                            src="{{ Storage::disk('public')->url($qr->png_path) }}"
                            alt="QR-Code für {{ $memoryPage->person_name }}"
                            class="w-56 h-56"
                        >
                    @endif

                    {{-- Label below QR --}}
                    <div class="text-center leading-tight">
                        <p class="text-sm font-medium text-[#706B62]">memorybook.at/</p>
                        <p class="text-lg font-bold tracking-widest text-[#2F2E2A]">{{ $qr->short_code }}</p>
                    </div>

                    {{-- Meta info --}}
                    <dl class="w-full divide-y divide-[#DDD6CA] text-sm">
                        <div class="py-3">
                            <dt class="text-[#706B62]">Öffentliche URL</dt>
                            <dd class="mt-0.5">
                                <a href="{{ route('memory-pages.public', $qr->short_code) }}"
                                   class="text-brand-600 hover:text-brand-700 break-all">
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
