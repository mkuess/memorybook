<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Profilfoto hochladen
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <h3 class="text-base font-semibold text-gray-800 mb-4">
                        {{ $memoryPage->person_name }}
                    </h3>

                    @if ($profilePhoto)
                        <div class="mb-4">
                            <img src="{{ Storage::disk('public')->url($profilePhoto->path) }}"
                                 alt="Profilfoto"
                                 class="w-32 h-32 object-cover rounded-full shadow">
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mb-4">Noch kein Profilfoto hochgeladen.</p>
                    @endif

                    @if (session('success'))
                        <p class="text-sm text-green-700 mb-3">{{ session('success') }}</p>
                    @endif

                    <form method="POST"
                          action="{{ route('memory-pages.profile-photo.store', $memoryPage) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="from" value="{{ $from }}">

                        <div class="flex flex-wrap items-center gap-3">
                            <input type="file"
                                   name="photo"
                                   accept="image/jpeg,image/jpg,image/png,image/webp"
                                   class="text-sm text-gray-600">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Foto hochladen
                            </button>
                        </div>

                        @error('photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </form>

                    <div class="mt-5">
                        @if ($from === 'admin')
                            <a href="/admin/memory-pages/{{ $memoryPage->id }}/edit"
                               class="text-sm text-gray-600 hover:text-gray-900">
                                &larr; Zurück zur Verwaltung
                            </a>
                        @else
                            <a href="{{ route('memory-pages.edit', $memoryPage) }}"
                               class="text-sm text-gray-600 hover:text-gray-900">
                                &larr; Zurück
                            </a>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
