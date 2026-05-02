<?php

namespace App\Filament\Resources\MemoryPageResource\Pages;

use App\Filament\Resources\MemoryPageResource;
use App\Models\MemoryPage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMemoryPage extends CreateRecord
{
    protected static string $resource = MemoryPageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        do {
            $slug = strtolower(Str::random(8));
        } while (MemoryPage::where('slug', $slug)->exists());

        $data['slug'] = $slug;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
