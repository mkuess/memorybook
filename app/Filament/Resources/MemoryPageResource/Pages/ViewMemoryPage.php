<?php

namespace App\Filament\Resources\MemoryPageResource\Pages;

use App\Filament\Resources\MemoryPageResource;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewMemoryPage extends ViewRecord
{
    protected static string $resource = MemoryPageResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->record;

        return [
            Action::make('upload_profile_photo')
                ->label('Profilfoto hochladen')
                ->icon('heroicon-o-camera')
                ->modalHeading('Profilfoto hochladen')
                ->modalSubmitActionLabel('Hochladen')
                ->form([
                    Forms\Components\FileUpload::make('photo')
                        ->label('Foto auswählen')
                        ->disk('public')
                        ->directory("memory-pages/{$record->id}/profile")
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = is_array($data['photo']) ? ($data['photo'][0] ?? '') : $data['photo'];

                    $existing = $this->record->media()->where('collection', 'profile')->first();
                    if ($existing) {
                        Storage::disk('public')->delete($existing->path);
                        $existing->delete();
                    }

                    $fullPath = Storage::disk('public')->path($path);
                    [$width, $height] = @getimagesize($fullPath) ?: [null, null];

                    $this->record->media()->create([
                        'collection'        => 'profile',
                        'filename'          => basename($path),
                        'original_filename' => basename($path),
                        'path'              => $path,
                        'mime_type'         => Storage::disk('public')->mimeType($path) ?: 'image/jpeg',
                        'size_bytes'        => Storage::disk('public')->size($path) ?: 0,
                        'width'             => $width ?: null,
                        'height'            => $height ?: null,
                        'sort_order'        => 0,
                    ]);
                }),

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
