<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Bestellungen';

    protected static ?string $modelLabel = 'Bestellung';

    protected static ?string $pluralModelLabel = 'Bestellungen';

    protected static ?int $navigationSort = 5;

    private static function statusColor(string $state): string
    {
        return match ($state) {
            'requested'  => 'gray',
            'in_review'  => 'warning',
            'paid'       => 'success',
            'cancelled'  => 'danger',
            default      => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(Order::$statuses)
                    ->required(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('memoryPage.person_name')
                    ->label('Erinnerungsseite'),

                Infolists\Components\TextEntry::make('user.email')
                    ->label('Konto'),

                Infolists\Components\TextEntry::make('package')
                    ->label('Paket')
                    ->formatStateUsing(fn (string $state): string => Order::$packages[$state] ?? $state),

                Infolists\Components\TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => Order::$statuses[$state] ?? $state),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Bestellt am')
                    ->dateTime('d.m.Y H:i'),

                Infolists\Components\TextEntry::make('billing_name')
                    ->label('Name'),

                Infolists\Components\TextEntry::make('billing_email')
                    ->label('E-Mail'),

                Infolists\Components\TextEntry::make('billing_address')
                    ->label('Adresse'),

                Infolists\Components\TextEntry::make('billing_postal_code')
                    ->label('PLZ'),

                Infolists\Components\TextEntry::make('billing_city')
                    ->label('Ort'),

                Infolists\Components\TextEntry::make('billing_country')
                    ->label('Land'),

                Infolists\Components\TextEntry::make('consent_confirmed_at')
                    ->label('Zustimmung am')
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

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Konto')
                    ->searchable(),

                Tables\Columns\TextColumn::make('package')
                    ->label('Paket')
                    ->formatStateUsing(fn (string $state): string => Order::$packages[$state] ?? $state),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => Order::$statuses[$state] ?? $state),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bestellt')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Order::$statuses),

                Tables\Filters\SelectFilter::make('package')
                    ->label('Paket')
                    ->options(Order::$packages),
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
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
            'edit'  => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
