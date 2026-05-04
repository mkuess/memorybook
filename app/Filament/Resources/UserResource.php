<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Benutzer';

    protected static ?string $modelLabel = 'Benutzer';

    protected static ?string $pluralModelLabel = 'Benutzer';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('E-Mail')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->label('Passwort')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => bcrypt($state)),

                Forms\Components\Select::make('role')
                    ->label('Rolle')
                    ->options([
                        'user'  => 'Benutzer',
                        'admin' => 'Administrator',
                    ])
                    ->required(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->label('Name'),

                Infolists\Components\TextEntry::make('email')
                    ->label('E-Mail'),

                Infolists\Components\TextEntry::make('role')
                    ->label('Rolle')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        default => 'gray',
                    }),

                Infolists\Components\TextEntry::make('email_verified_at')
                    ->label('E-Mail bestätigt')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Nicht bestätigt'),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Rolle')
                    ->colors([
                        'danger' => 'admin',
                        'gray'   => 'user',
                    ]),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Zuletzt eingeloggt')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Noch nie')
                    ->sortable(),
            ])
            ->defaultSort('last_login_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
