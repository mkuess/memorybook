<?php

namespace App\Filament\Resources\MemoryPageResource\Pages;

use App\Filament\Resources\MemoryPageResource;
use App\Models\MemoryPage;
use Closure;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMemoryPages extends ListRecords
{
    protected static string $resource = MemoryPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn (MemoryPage $record): string =>
            MemoryPageResource::getUrl('view', ['record' => $record]);
    }
}
