<?php

namespace App\Filament\Resources\MemoryPageResource\Pages;

use App\Filament\Resources\MemoryPageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMemoryPage extends ViewRecord
{
    protected static string $resource = MemoryPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Beitrag bearbeiten')
                ->icon('heroicon-o-pencil-square')
                ->url(fn (): string => $this->getResource()::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
