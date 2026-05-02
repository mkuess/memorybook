<?php

namespace App\Filament\Resources\PlaqueOrderResource\Pages;

use App\Filament\Resources\PlaqueOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditPlaqueOrder extends EditRecord
{
    protected static string $resource = PlaqueOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
