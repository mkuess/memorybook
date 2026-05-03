<x-guest-layout>

    <h1 class="text-xl font-semibold text-[#2F2E2A] mb-6">Konto erstellen</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Name" />
            <x-text-input id="name"
                          class="block mt-1 w-full"
                          type="text"
                          name="name"
                          :value="old('name')"
                          required
                          autofocus
                          autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- E-Mail-Adresse -->
        <div class="mt-4">
            <x-input-label for="email" value="E-Mail-Adresse" />
            <x-text-input id="email"
                          class="block mt-1 w-full"
                          type="email"
                          name="email"
                          :value="old('email')"
                          required
                          autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Passwort -->
        <div class="mt-4">
            <x-input-label for="password" value="Passwort" />
            <x-text-input id="password"
                          class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Passwort bestätigen -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Passwort bestätigen" />
            <x-text-input id="password_confirmation"
                          class="block mt-1 w-full"
                          type="password"
                          name="password_confirmation"
                          required
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                Registrieren
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 pt-5 border-t border-[#DDD6CA] text-center text-sm text-[#706B62]">
        Bereits ein Konto?
        <a href="{{ route('login') }}"
           class="font-semibold text-brand-600 hover:text-brand-700 underline underline-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 rounded">
            Anmelden
        </a>
    </div>

</x-guest-layout>
