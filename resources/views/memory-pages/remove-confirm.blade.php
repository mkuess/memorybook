<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Profilseite löschen
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-[#DDD6CA] sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <h3 class="text-lg font-semibold text-[#2F2E2A] mb-3">
                        Profilseite wirklich entfernen?
                    </h3>

                    <p class="text-sm text-[#706B62] leading-relaxed mb-6">
                        Diese Profilseite wird aus deinem Kundenbereich ausgeblendet und ist öffentlich nicht mehr aufrufbar.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form method="POST" action="{{ route('memory-pages.remove', $memoryPage) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-5 py-2.5 bg-[#9A4F3F] border border-transparent rounded font-semibold text-sm text-white hover:bg-[#7E3F31] focus:outline-none focus:ring-2 focus:ring-[#9A4F3F] focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto">
                                Ja, Profilseite löschen
                            </button>
                        </form>
                        <a href="{{ route('memory-pages.edit', $memoryPage) }}"
                           class="inline-flex items-center justify-center px-5 py-2.5 bg-[#EFEAE1] border border-[#DDD6CA] rounded font-semibold text-sm text-[#2F2E2A] hover:bg-[#D8D2C8] focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto">
                            Abbrechen
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
