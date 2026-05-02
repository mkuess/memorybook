<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            QR-Code – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Person</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $memoryPage->person_name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">QR-Code</dt>
                        <dd class="mt-1 font-mono text-gray-900">{{ $qr->short_code }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Öffentliche URL</dt>
                        <dd class="mt-1">
                            <a href="{{ route('memory-pages.public', $qr->short_code) }}"
                               class="text-indigo-600 hover:text-indigo-800 break-all">
                                {{ route('memory-pages.public', $qr->short_code) }}
                            </a>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Aufrufe</dt>
                        <dd class="mt-1 text-gray-900">{{ $qr->scan_count }}</dd>
                    </div>

                    <div class="pt-4">
                        <a href="{{ route('memory-pages.edit', $memoryPage) }}"
                           class="text-sm text-gray-600 hover:text-gray-900">
                            &larr; Zurück zur Bearbeitung
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
