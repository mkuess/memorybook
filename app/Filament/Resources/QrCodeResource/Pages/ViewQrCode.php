<?php

namespace App\Filament\Resources\QrCodeResource\Pages;

use App\Filament\Resources\QrCodeResource;
use App\Services\QrCodeImageService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewQrCode extends ViewRecord
{
    protected static string $resource = QrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('regenerate')
                ->label('QR-Code neu generieren')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $record = $this->getRecord();
                    $url    = route('memory-pages.public', $record->short_code);

                    app(QrCodeImageService::class)->generateAndStore($record, $url);

                    Notification::make()
                        ->title('QR-Code wurde neu generiert.')
                        ->success()
                        ->send();

                    redirect($this->getResource()::getUrl('view', ['record' => $record]));
                }),
        ];
    }
}
