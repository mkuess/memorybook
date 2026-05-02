<?php

namespace App\Filament\Resources\MemoryPageResource\Pages;

use App\Filament\Resources\MemoryPageResource;
use Filament\Resources\Pages\EditRecord;

class EditMemoryPage extends EditRecord
{
    protected static string $resource = MemoryPageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
