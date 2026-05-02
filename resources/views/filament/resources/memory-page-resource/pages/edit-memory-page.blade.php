<x-filament-panels::page
    @class([
        'fi-resource-edit-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    {{-- Profile photo upload form rendered outside the Livewire form to avoid nested forms --}}
    <x-filament::section>
        <x-slot name="heading">Profilfoto</x-slot>

        @php
            $profilePhoto = $record->media()->where('collection', 'profile')->first();
        @endphp

        <form
            method="POST"
            action="{{ route('memory-pages.profile-photo.store', $record) }}"
            enctype="multipart/form-data"
            class="space-y-4"
        >
            @csrf
            <input type="hidden" name="from" value="admin">

            @if ($profilePhoto)
                <div>
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($profilePhoto->path) }}"
                        alt="Profilfoto"
                        style="width:80px;height:80px;object-fit:cover;border-radius:9999px;"
                    >
                </div>
            @else
                <p class="text-sm text-gray-500">Noch kein Profilfoto hochgeladen.</p>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <input
                    type="file"
                    name="photo"
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    class="text-sm text-gray-600"
                >
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 text-sm font-semibold text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    style="border-radius:4px"
                >
                    Profilfoto hochladen
                </button>
            </div>

            <p class="text-xs text-gray-400">Erlaubt: JPG, PNG oder WebP bis 5 MB.</p>

            @error('photo')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>
    </x-filament::section>

    {{-- Gallery management — outside the Livewire form to avoid nested forms --}}
    @php
        $galleryImages = $record->media()
            ->where('collection', 'gallery')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();
    @endphp
    <x-filament::section>
        <x-slot name="heading">Galerie</x-slot>

        @if (session('gallery_success'))
            <p class="text-sm text-green-700 mb-3">{{ session('gallery_success') }}</p>
        @endif

        @if ($galleryImages->isNotEmpty())
            <ul class="divide-y divide-gray-100 mb-4">
                @foreach ($galleryImages as $image)
                    @php $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($image->path); @endphp
                    <li class="flex items-center gap-3 py-2 min-w-0">
                        <a href="{{ $imageUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="shrink-0 block overflow-hidden rounded"
                           style="width:64px;height:64px;min-width:64px;">
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $image->original_filename }}"
                                style="width:64px;height:64px;max-width:64px;object-fit:cover;display:block;flex-shrink:0;"
                            >
                        </a>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $image->original_filename }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($image->size_bytes / 1024, 0) }} KB</p>
                        </div>
                        <form
                            method="POST"
                            action="{{ route('memory-pages.admin-gallery.destroy', [$record, $image]) }}"
                            onsubmit="return confirm('Bild wirklich löschen?')"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white text-xs font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition ease-in-out duration-150"
                                style="border-radius:4px"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                Löschen
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500 mb-3">Noch keine Galeriebilder vorhanden.</p>
        @endif

        @if ($galleryImages->count() >= 5)
            <p class="text-sm text-amber-700">Maximal 5 Galeriebilder möglich.</p>
        @else
            <form
                method="POST"
                action="{{ route('memory-pages.admin-gallery.store', $record) }}"
                enctype="multipart/form-data"
                class="space-y-3"
            >
                @csrf
                <div class="flex flex-wrap items-center gap-3">
                    <input
                        type="file"
                        name="image"
                        accept="image/jpeg,image/jpg,image/png,image/webp"
                        class="text-sm text-gray-600"
                    >
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 text-sm font-semibold text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        style="border-radius:4px"
                    >
                        Bild hochladen
                    </button>
                </div>
                <p class="text-xs text-gray-400">Erlaubt: JPG, PNG oder WebP bis 5 MB. Maximal 5 Bilder.</p>
                @error('image')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>
        @endif
    </x-filament::section>

    @capture($form)
        <x-filament-panels::form
            id="form"
            :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
            wire:submit="save"
        >
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    @endcapture

    @php
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
        {{ $form() }}
    @endif

    @if (count($relationManagers))
        <x-filament-panels::resources.relation-managers
            :active-locale="isset($activeLocale) ? $activeLocale : null"
            :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
            :content-tab-label="$this->getContentTabLabel()"
            :content-tab-icon="$this->getContentTabIcon()"
            :content-tab-position="$this->getContentTabPosition()"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        >
            @if ($hasCombinedRelationManagerTabsWithContent)
                <x-slot name="content">
                    {{ $form() }}
                </x-slot>
            @endif
        </x-filament-panels::resources.relation-managers>
    @endif

    <x-filament-panels::page.unsaved-data-changes-alert />
</x-filament-panels::page>
