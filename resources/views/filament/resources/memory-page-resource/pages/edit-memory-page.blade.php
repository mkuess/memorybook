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

            @error('photo')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>
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
