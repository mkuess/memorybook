<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Person: <span class="font-medium text-gray-900 dark:text-white">{{ $this->record->person_name }}</span>
        </p>
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>
