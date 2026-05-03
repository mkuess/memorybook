<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erinnerung hinterlassen – {{ $page->person_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8F5ED] text-gray-900 antialiased">

    <div class="min-h-screen py-10 px-4 sm:py-16">
        <div class="w-full max-w-lg mx-auto">

            <div class="mb-6">
                <a href="/m/{{ $code }}"
                   class="text-sm text-[#706B62] hover:text-[#2F2E2A]">
                    &larr; Zurück zur Profilseite
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">

                <h1 class="text-xl font-bold text-[#2F2E2A] mb-1">Erinnerung hinterlassen</h1>
                <p class="text-sm text-[#706B62] mb-6">Für {{ $page->person_name }}</p>

                @if ($errors->any())
                    <div class="mb-5 p-4 bg-[#FDF2F0] border border-[#9A4F3F] rounded text-sm text-[#9A4F3F]">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('visitor-memory.store', $code) }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-5">
                        <label for="content" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                            Deine Erinnerung <span class="text-[#9A4F3F]">*</span>
                        </label>
                        <textarea
                            id="content"
                            name="content"
                            rows="6"
                            required
                            placeholder="Schreibe hier deine Erinnerung..."
                            class="w-full border-[#DDD6CA] rounded-lg shadow-sm focus:ring-brand-600 focus:border-brand-600 text-sm @error('content') border-[#9A4F3F] @enderror"
                        >{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="image" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                            Bild (optional)
                        </label>
                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept="image/jpg,image/jpeg,image/png,image/webp"
                            class="block w-full text-sm text-[#706B62] file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-[#EFEAE1] file:text-[#2F2E2A] hover:file:bg-[#D8D2C8] @error('image') border border-[#9A4F3F] rounded @enderror"
                        >
                        <p class="mt-1 text-xs text-[#706B62]">JPG, PNG oder WebP, max. 5 MB.</p>
                        @error('image')
                            <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium text-[#2F2E2A] mb-1">
                            E-Mail-Adresse <span class="text-[#9A4F3F]">*</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full border-[#DDD6CA] rounded-lg shadow-sm focus:ring-brand-600 focus:border-brand-600 text-sm @error('email') border-[#9A4F3F] @enderror"
                        >
                        <p class="mt-1 text-xs text-[#706B62]">
                            Deine E-Mail-Adresse wird nicht öffentlich angezeigt. Sie wird nur zur Bestätigung deiner Erinnerung verwendet.
                        </p>
                        @error('email')
                            <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TODO: Legal wording must be reviewed by a legal professional before production use. --}}
                    <div class="mb-6">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                name="consent"
                                value="1"
                                {{ old('consent') ? 'checked' : '' }}
                                class="mt-0.5 text-brand-600 border-[#DDD6CA] rounded focus:ring-brand-600"
                            >
                            <span class="text-sm text-[#2F2E2A] leading-snug">
                                Ich akzeptiere die Datenschutzbestimmungen und bestätige, dass ich diese Erinnerung veröffentlichen darf.
                            </span>
                        </label>
                        @error('consent')
                            <p class="mt-1 text-sm text-[#9A4F3F]">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full inline-flex justify-center items-center px-5 py-3 bg-brand-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 transition ease-in-out duration-150">
                        Erinnerung absenden
                    </button>

                </form>

            </div>
        </div>
    </div>

</body>
</html>
