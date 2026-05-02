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
            Action::make('toggle_lock')
                ->label(fn (): string => $this->record->is_locked ? 'Jetzt freigeben' : 'Jetzt blockieren')
                ->color(fn (): string => $this->record->is_locked ? 'success' : 'danger')
                ->icon(fn (): string => $this->record->is_locked
                    ? 'heroicon-o-lock-open'
                    : 'heroicon-o-lock-closed')
                ->action(function (): void {
                    $this->record->update(['is_locked' => ! $this->record->is_locked]);
                    $this->redirect(
                        $this->getResource()::getUrl('view', ['record' => $this->record])
                    );
                }),
        ];
    }
}
