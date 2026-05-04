<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Meldungen';

    protected static ?string $modelLabel = 'Meldung';

    protected static ?string $pluralModelLabel = 'Meldungen';

    protected static ?int $navigationSort = 3;

    private static array $statusOptions = [
        'open'       => 'Offen',
        'in_review'  => 'In Prüfung',
        'resolved'   => 'Erledigt',
        'dismissed'  => 'Abgewiesen',
    ];

    private static array $categoryLabels = [
        'profile_report'       => 'Profilmeldung',
        'Problem'              => 'Problem',
        'Frage'                => 'Frage',
        'Verbesserungsvorschlag' => 'Verbesserungsvorschlag',
        'Sonstiges'            => 'Sonstiges',
    ];

    private static function statusColor(string $state): string
    {
        return match ($state) {
            'open'      => 'danger',
            'in_review' => 'warning',
            'resolved'  => 'success',
            'dismissed' => 'gray',
            default     => 'gray',
        };
    }

    private static function categoryColor(string $state): string
    {
        return match ($state) {
            'profile_report' => 'danger',
            'Problem'        => 'warning',
            'Frage'          => 'info',
            default          => 'gray',
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
                Infolists\Components\TextEntry::make('category')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => self::categoryColor($state))
                    ->formatStateUsing(fn (string $state): string => self::$categoryLabels[$state] ?? $state),

                Infolists\Components\TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => self::$statusOptions[$state] ?? $state),

                Infolists\Components\TextEntry::make('reporter_name')
                    ->label('Name')
                    ->placeholder('–'),

                Infolists\Components\TextEntry::make('reporter_email')
                    ->label('E-Mail')
                    ->placeholder('–'),

                Infolists\Components\TextEntry::make('subject')
                    ->label('Betreff')
                    ->placeholder('–')
                    ->columnSpanFull(),

                Infolists\Components\TextEntry::make('memoryPage.person_name')
                    ->label('Erinnerungsseite')
                    ->placeholder('–'),

                Infolists\Components\TextEntry::make('reason')
                    ->label('Grund (Profilmeldung)')
                    ->placeholder('–'),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Eingegangen am')
                    ->dateTime('d.m.Y H:i'),

                Infolists\Components\TextEntry::make('description')
                    ->label('Nachricht')
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
                Tables\Columns\TextColumn::make('category')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => self::categoryColor($state))
                    ->formatStateUsing(fn (string $state): string => self::$categoryLabels[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('reporter_email')
                    ->label('E-Mail')
                    ->searchable()
                    ->placeholder('–'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Betreff')
                    ->limit(40)
                    ->placeholder('–'),

                Tables\Columns\TextColumn::make('memoryPage.person_name')
                    ->label('Erinnerungsseite')
                    ->placeholder('–')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => self::$statusOptions[$state] ?? $state),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Eingegangen')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::$statusOptions),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Typ')
                    ->options(self::$categoryLabels),
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
            'index' => Pages\ListReports::route('/'),
            'view'  => Pages\ViewReport::route('/{record}'),
            'edit'  => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
