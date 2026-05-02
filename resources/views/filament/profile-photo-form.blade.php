@php
    use Illuminate\Support\Facades\Storage;
    $photoUrl = ($profilePhoto && Storage::disk('public')->exists($profilePhoto->path))
        ? Storage::disk('public')->url($profilePhoto->path)
        : null;
@endphp

<div class="col-span-full">
    <div class="text-sm font-medium leading-6 text-gray-950 dark:text-white mb-2">
        Profilfoto
    </div>

    @if ($photoUrl)
        <div class="mb-3">
            <img src="{{ $photoUrl }}"
                 alt="Profilfoto"
                 class="w-24 h-24 object-cover rounded-full shadow">
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
            Noch kein Profilfoto hochgeladen.
        </p>
    @endif

    @if ($memoryPage)
        <form method="POST"
              action="{{ route('memory-pages.profile-photo.store', $memoryPage) }}"
              enctype="multipart/form-data"
              class="flex flex-wrap items-center gap-3">
            @csrf
            <input type="file"
                   name="photo"
                   accept="image/jpeg,image/jpg,image/png,image/webp"
                   class="text-sm text-gray-700 dark:text-gray-300">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                Foto hochladen
            </button>
        </form>
    @endif
</div>
