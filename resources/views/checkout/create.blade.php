<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2F2E2A] leading-tight">
            Paket auswählen – {{ $memoryPage->person_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('memory-pages.checkout.store', $memoryPage) }}">
                @csrf

                {{-- 1. Paket auswählen --}}
                <div class="bg-white border border-[#DDD6CA] sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-[#2F2E2A] mb-4">1. Paket auswählen</h3>

                        <fieldset class="space-y-3">
                            <label class="flex items-start gap-4 p-4 border border-[#DDD6CA] rounded cursor-pointer hover:border-brand-600 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                <input type="radio" name="package" value="basic"
                                       {{ old('package', 'basic') === 'basic' ? 'checked' : '' }}
                                       class="mt-0.5 text-brand-600 border-[#DDD6CA] focus:ring-brand-600">
                                <div>
                                    <p class="font-semibold text-[#2F2E2A] text-sm">Erinnerungsseite</p>
                                    <p class="text-xs text-[#706B62] mt-0.5">Digitale Erinnerungsseite mit Fotos und Storys</p>
                                    <p class="text-sm font-bold text-brand-700 mt-1">€ 29,–</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-4 p-4 border border-[#DDD6CA] rounded cursor-pointer hover:border-brand-600 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                <input type="radio" name="package" value="plaque"
                                       {{ old('package') === 'plaque' ? 'checked' : '' }}
                                       class="mt-0.5 text-brand-600 border-[#DDD6CA] focus:ring-brand-600">
                                <div>
                                    <p class="font-semibold text-[#2F2E2A] text-sm">Erinnerungsseite + QR-Plakette</p>
                                    <p class="text-xs text-[#706B62] mt-0.5">Digitale Seite inkl. gravierter QR-Plakette für den Grabstein</p>
                                    <p class="text-sm font-bold text-brand-700 mt-1">€ 79,–</p>
                                </div>
                            </label>
                        </fieldset>

                        @error('package')
                            <p class="mt-2 text-sm text-[#9A4F3F]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 2. Rechnungsdaten --}}
                <div class="bg-white border border-[#DDD6CA] sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-[#2F2E2A] mb-4">2. Rechnungsdaten</h3>

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="billing_name" value="Name *" />
                                <x-text-input id="billing_name" name="billing_name" type="text"
                                              class="block mt-1 w-full"
                                              :value="old('billing_name')" required />
                                <x-input-error :messages="$errors->get('billing_name')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="billing_email" value="E-Mail-Adresse *" />
                                <x-text-input id="billing_email" name="billing_email" type="email"
                                              class="block mt-1 w-full"
                                              :value="old('billing_email')" required />
                                <x-input-error :messages="$errors->get('billing_email')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="billing_address" value="Adresse *" />
                                <x-text-input id="billing_address" name="billing_address" type="text"
                                              class="block mt-1 w-full"
                                              :value="old('billing_address')" required />
                                <x-input-error :messages="$errors->get('billing_address')" class="mt-1" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="billing_postal_code" value="PLZ *" />
                                    <x-text-input id="billing_postal_code" name="billing_postal_code" type="text"
                                                  class="block mt-1 w-full"
                                                  :value="old('billing_postal_code')" required />
                                    <x-input-error :messages="$errors->get('billing_postal_code')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="billing_city" value="Ort *" />
                                    <x-text-input id="billing_city" name="billing_city" type="text"
                                                  class="block mt-1 w-full"
                                                  :value="old('billing_city')" required />
                                    <x-input-error :messages="$errors->get('billing_city')" class="mt-1" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="billing_country" value="Land *" />
                                <x-text-input id="billing_country" name="billing_country" type="text"
                                              class="block mt-1 w-full"
                                              :value="old('billing_country', 'Österreich')" required />
                                <x-input-error :messages="$errors->get('billing_country')" class="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Rechtlicher Hinweis --}}
                <div class="bg-white border border-[#DDD6CA] sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-[#2F2E2A] mb-4">3. Bestellbestätigung</h3>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="consent" value="1"
                                   {{ old('consent') ? 'checked' : '' }}
                                   class="mt-0.5 rounded border-[#DDD6CA] text-brand-600 focus:ring-brand-600 @error('consent') border-[#9A4F3F] @enderror">
                            <span class="text-sm text-[#2F2E2A]">
                                Ich bestelle zahlungspflichtig.
                            </span>
                        </label>

                        @error('consent')
                            <p class="mt-2 text-sm text-[#9A4F3F]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="{{ route('memory-pages.edit', $memoryPage) }}"
                       class="text-sm text-[#706B62] hover:text-[#2F2E2A]">
                        &larr; Zurück zur Bearbeitung
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-brand-600 border border-transparent rounded font-semibold text-sm text-white uppercase tracking-widest hover:bg-brand-700 focus:bg-brand-700 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                        Zahlungspflichtig bestellen
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
