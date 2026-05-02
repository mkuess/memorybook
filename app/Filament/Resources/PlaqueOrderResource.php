<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaqueOrderResource\Pages;
use App\Models\PlaqueOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlaqueOrderResource extends Resource
{
    protected static ?string $model = PlaqueOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-2-stack';

    protected static ?string $navigationLabel = 'Plaketten';

    protected static ?string $modelLabel = 'Plakettenanfrage';

    protected static ?string $pluralModelLabel = 'Plaketten';

    protected static ?int $navigationSort = 4;

    private static array $statusOptions = [
        'requested'     => 'Angefragt',
        'in_review'     => 'In Prüfung',
        'in_production' => 'In Produktion',
        'shipped'       => 'Versendet',
        'done'          => 'Abgeschlossen',
        'cancelled'     => 'Storniert',
    ];

    private static function statusColor(string $state): string
    {
        return match ($state) {
            'requested'     => 'gray',
            'in_review'     => 'warning',
            'in_production' => 'info',
            'shipped'       => 'primary',
            'done'          => 'success',
            'cancelled'     => 'danger',
            default         => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(self::$statusOptions)
                    ->required(),

                Forms\Components\Textarea::make('admin_notes')
                    ->label('Admin-Notizen')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('memoryPage.person_name')
                    ->label('Erinnerungsseite'),

                Infolists\Components\TextEntry::make('user.email')
                    ->label('Besteller (Konto)'),

                Infolists\Components\TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => self::$statusOptions[$state] ?? $state),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Bestellt am')
                    ->dateTime('d.m.Y H:i'),

                Infolists\Components\TextEntry::make('contact_name')
                    ->label('Kontaktname'),

                Infolists\Components\TextEntry::make('contact_email')
                    ->label('Kontakt-E-Mail'),

                Infolists\Components\TextEntry::make('contact_phone')
                    ->label('Telefon')
                    ->placeholder('–'),

                Infolists\Components\TextEntry::make('shipping_address')
                    ->label('Lieferadresse')
                    ->columnSpanFull(),

                Infolists\Components\TextEntry::make('notes')
                    ->label('Kundennotizen')
                    ->placeholder('–')
                    ->columnSpanFull(),

                Infolists\Components\TextEntry::make('admin_notes')
                    ->label('Admin-Notizen')
                    ->placeholder('–')
                    ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Kontakt')
                    ->searchable(),

                Tables\Columns\TextColumn::make('contact_email')
                    ->label('E-Mail')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => self::$statusOptions[$state] ?? $state),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bestellt')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::$statusOptions),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaqueOrders::route('/'),
            'view'  => Pages\ViewPlaqueOrder::route('/{record}'),
            'edit'  => Pages\EditPlaqueOrder::route('/{record}/edit'),
        ];
    }
}
