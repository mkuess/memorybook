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
            <div class="grid grid-cols-3 gap-3 mb-4">
                @foreach ($galleryImages as $image)
                    <div class="relative group">
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                            alt="{{ $image->original_filename }}"
                            class="w-full aspect-square object-cover rounded-md shadow-sm"
                        >
                        <form
                            method="POST"
                            action="{{ route('memory-pages.admin-gallery.destroy', [$record, $image]) }}"
                            class="absolute top-1 right-1"
                            onsubmit="return confirm('Bild wirklich löschen?')"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white text-xs px-2 py-1 rounded shadow"
                                title="Bild entfernen"
                            >
                                &times;
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
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
