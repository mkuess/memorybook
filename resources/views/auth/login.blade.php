<x-guest-layout>

    <h1 class="text-xl font-semibold text-[#2F2E2A] mb-6">Anmelden</h1>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- E-Mail-Adresse -->
        <div>
            <x-input-label for="email" value="E-Mail-Adresse" />
            <x-text-input id="email"
                          class="block mt-1 w-full"
                          type="email"
                          name="email"
                          :value="old('email')"
                          required
                          autofocus
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
                          autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Angemeldet bleiben -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me"
                       type="checkbox"
                       name="remember"
                       class="rounded border-[#DDD6CA] text-brand-600 shadow-sm focus:ring-brand-600">
                <span class="text-sm text-[#706B62]">Angemeldet bleiben</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm text-[#706B62] hover:text-[#2F2E2A] underline underline-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 rounded">
                    Passwort vergessen?
                </a>
            @endif

            <x-primary-button>
                Anmelden
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 pt-5 border-t border-[#DDD6CA] text-center text-sm text-[#706B62]">
        Noch kein Konto?
        <a href="{{ route('register') }}"
           class="font-semibold text-brand-600 hover:text-brand-700 underline underline-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 rounded">
            Konto erstellen
        </a>
    </div>

</x-guest-layout>
