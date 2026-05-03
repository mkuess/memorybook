<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Link abgelaufen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8F5ED] text-gray-900 antialiased">

    <div class="min-h-screen flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">

                <div class="w-14 h-14 rounded-full bg-[#F5EEEC] flex items-center justify-center mx-auto mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-[#9A4F3F]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                </div>

                <h1 class="text-xl font-bold text-[#2F2E2A] mb-2">Link ungültig oder abgelaufen</h1>
                <p class="text-sm text-[#706B62] leading-relaxed">
                    Dieser Bestätigungslink ist nicht mehr gültig.<br>
                    Bestätigungslinks sind 7 Tage gültig.
                </p>

            </div>
        </div>
    </div>

</body>
</html>
