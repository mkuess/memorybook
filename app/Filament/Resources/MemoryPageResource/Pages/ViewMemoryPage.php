<?php

namespace App\Filament\Resources\MemoryPageResource\Pages;

use App\Filament\Resources\MemoryPageResource;
use App\Filament\Resources\QrCodeResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMemoryPage extends ViewRecord
{
    protected static string $resource = MemoryPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_qr_code')
                ->label('QR-Code anzeigen')
                ->icon('heroicon-o-qr-code')
                ->url(fn (): ?string => $this->record->qrCode
                    ? QrCodeResource::getUrl('view', ['record' => $this->record->qrCode])
                    : null
                )
                ->visible(fn (): bool => (bool) $this->record->qrCode),

            Action::make('edit')
                ->label('Beitrag bearbeiten')
                ->icon('heroicon-o-pencil-square')
                ->url(fn (): string => $this->getResource()::getUrl('edit', ['record' => $this->record])),

            Action::make('manage_stories')
                ->label('Stories verwalten')
                ->icon('heroicon-o-book-open')
                ->url(fn (): string => $this->getResource()::getUrl('stories', ['record' => $this->record])),
        ];
    }
}
