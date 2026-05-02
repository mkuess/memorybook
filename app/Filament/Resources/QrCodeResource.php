<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QrCodeResource\Pages;
use App\Models\QrCode;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QrCodeResource extends Resource
{
    protected static ?string $model = QrCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'QR-Codes';

    protected static ?string $modelLabel = 'QR-Code';

    protected static ?string $pluralModelLabel = 'QR-Codes';

    protected static ?int $navigationSort = 2;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('memoryPage.person_name')
                    ->label('Erinnerungsseite'),

                Infolists\Components\TextEntry::make('short_code')
                    ->label('Kurzcode')
                    ->fontFamily('mono'),

                Infolists\Components\TextEntry::make('public_url')
                    ->label('Öffentliche URL')
                    ->state(fn (QrCode $record): string => route('memory-pages.public', $record->short_code))
                    ->url(fn (QrCode $record): string => route('memory-pages.public', $record->short_code))
                    ->openUrlInNewTab(),

                Infolists\Components\TextEntry::make('scan_count')
                    ->label('Aufrufe'),

                Infolists\Components\TextEntry::make('png_path')
                    ->label('PNG-Pfad')
                    ->placeholder('–'),

                Infolists\Components\TextEntry::make('svg_path')
                    ->label('SVG-Pfad')
                    ->placeholder('–'),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('memoryPage.person_name')
                    ->label('Erinnerungsseite')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('short_code')
                    ->label('Kurzcode')
                    ->fontFamily('mono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('scan_count')
                    ->label('Aufrufe')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQrCodes::route('/'),
            'view'  => Pages\ViewQrCode::route('/{record}'),
        ];
    }
}
