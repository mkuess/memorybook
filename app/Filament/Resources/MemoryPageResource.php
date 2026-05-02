<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemoryPageResource\Pages;
use App\Models\MemoryPage;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class MemoryPageResource extends Resource
{
    protected static ?string $model = MemoryPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Erinnerungsseiten';

    protected static ?string $modelLabel = 'Erinnerungsseite';

    protected static ?string $pluralModelLabel = 'Erinnerungsseiten';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profilfoto')
                    ->schema([
                        Forms\Components\Placeholder::make('profile_photo_preview')
                            ->label('')
                            ->content(function (?MemoryPage $record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('<p class="text-sm text-gray-500 dark:text-gray-400">Noch kein Profilfoto hochgeladen.</p>');
                                }
                                $photo = $record->media()->where('collection', 'profile')->first();
                                if (! $photo) {
                                    return new HtmlString('<p class="text-sm text-gray-500 dark:text-gray-400">Noch kein Profilfoto hochgeladen.</p>');
                                }
                                $url = e(Storage::disk('public')->url($photo->path));
                                return new HtmlString("<img src=\"{$url}\" alt=\"Profilfoto\" style=\"width:80px;height:80px;object-fit:cover;border-radius:9999px;\">");
                            }),

                        Forms\Components\Placeholder::make('profile_photo_upload_button')
                            ->label('')
                            ->content(function (?MemoryPage $record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('');
                                }

                                $url  = e(route('memory-pages.profile-photo.create', $record) . '?from=admin');
                                $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>';

                                return new HtmlString(
                                    '<a href="' . $url . '" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">'
                                    . $icon
                                    . '<span>Profilfoto hochladen</span>'
                                    . '</a>'
                                );
                            }),
                    ])
                    ->hiddenOn('create')
                    ->columnSpanFull(),

                Forms\Components\Select::make('user_id')
                    ->label('Inhaber')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        return User::where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orderBy('name')
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn (User $u): array => [$u->id => "{$u->name} ({$u->email})"])
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $user = User::find($value);
                        return $user ? "{$user->name} ({$user->email})" : null;
                    })
                    ->required()
                    ->visibleOn('create'),

                Forms\Components\TextInput::make('person_name')
                    ->label('Person')
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('birth_date')
                    ->label('Geburtsdatum')
                    ->displayFormat('d.m.Y'),

                Forms\Components\DatePicker::make('death_date')
                    ->label('Sterbedatum')
                    ->displayFormat('d.m.Y'),

                Forms\Components\Textarea::make('short_bio')
                    ->label('Kurzbiografie')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\Select::make('visibility')
                    ->label('Sichtbarkeit')
                    ->options([
                        'private' => 'Privat',
                        'link'    => 'Nur per Link',
                        'public'  => 'Öffentlich',
                    ])
                    ->required(),

                Forms\Components\Toggle::make('is_published')
                    ->label('Freigegeben'),

                Forms\Components\Toggle::make('is_locked')
                    ->label('Gesperrt'),

            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\ImageEntry::make('profile_photo')
                    ->label('Profilfoto')
                    ->state(fn (MemoryPage $record): ?string =>
                        $record->media()->where('collection', 'profile')->first()?->path
                    )
                    ->disk('public')
                    ->height(80)
                    ->width(80),

                Infolists\Components\TextEntry::make('person_name')
                    ->label('Person'),

                Infolists\Components\TextEntry::make('user.email')
                    ->label('Inhaber'),

                Infolists\Components\TextEntry::make('slug')
                    ->label('Slug'),

                Infolists\Components\TextEntry::make('public_url')
                    ->label('Öffentliche URL')
                    ->state(fn (MemoryPage $record): string =>
                        $record->qrCode
                            ? '/m/' . $record->qrCode->short_code
                            : 'Noch kein QR-Code vorhanden'
                    )
                    ->url(fn (MemoryPage $record): ?string =>
                        $record->qrCode
                            ? url('/m/' . $record->qrCode->short_code)
                            : null
                    )
                    ->openUrlInNewTab(),

                Infolists\Components\TextEntry::make('visibility')
                    ->label('Sichtbarkeit')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public'  => 'success',
                        'link'    => 'info',
                        default   => 'gray',
                    }),

                Infolists\Components\IconEntry::make('is_published')
                    ->label('Freigegeben')
                    ->boolean()
                    ->helperText('Freigegeben bedeutet: Die Seite darf öffentlich angezeigt werden, wenn die Sichtbarkeit dies erlaubt.'),

                Infolists\Components\Group::make([
                    Infolists\Components\TextEntry::make('is_locked')
                        ->label('Gesperrt')
                        ->state(fn (MemoryPage $record): string => $record->is_locked ? 'Gesperrt' : 'Nicht gesperrt')
                        ->badge()
                        ->color(fn (MemoryPage $record): string => $record->is_locked ? 'danger' : 'success')
                        ->helperText('Gesperrt bedeutet: Die Seite ist durch die Verwaltung blockiert und öffentlich nicht sichtbar.'),

                    Infolists\Components\Actions::make([
                        Infolists\Components\Actions\Action::make('toggle_lock')
                            ->label(fn (MemoryPage $record): string => $record->is_locked ? 'Jetzt freigeben' : 'Jetzt blockieren')
                            ->color(fn (MemoryPage $record): string => $record->is_locked ? 'success' : 'danger')
                            ->icon(fn (MemoryPage $record): string => $record->is_locked
                                ? 'heroicon-o-lock-open'
                                : 'heroicon-o-lock-closed')
                            ->action(function (MemoryPage $record): void {
                                $record->update(['is_locked' => ! $record->is_locked]);
                            }),
                    ]),
                ]),

                Infolists\Components\TextEntry::make('birth_date')
                    ->label('Geburtsdatum')
                    ->date('d.m.Y')
                    ->placeholder('–'),

                Infolists\Components\TextEntry::make('death_date')
                    ->label('Sterbedatum')
                    ->date('d.m.Y')
                    ->placeholder('–'),

                Infolists\Components\TextEntry::make('short_bio')
                    ->label('Kurzbiografie')
                    ->placeholder('–')
                    ->columnSpanFull(),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('person_name')
                    ->label('Person')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Inhaber')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('visibility')
                    ->label('Sichtbarkeit')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public'  => 'success',
                        'link'    => 'info',
                        default   => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Freigegeben')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_locked')
                    ->label('Gesperrt')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Sichtbarkeit')
                    ->options([
                        'private' => 'Privat',
                        'link'    => 'Nur per Link',
                        'public'  => 'Öffentlich',
                    ]),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Freigegeben'),

                Tables\Filters\TernaryFilter::make('is_locked')
                    ->label('Gesperrt'),
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
            'index'  => Pages\ListMemoryPages::route('/'),
            'create' => Pages\CreateMemoryPage::route('/create'),
            'view'   => Pages\ViewMemoryPage::route('/{record}'),
            'edit'   => Pages\EditMemoryPage::route('/{record}/edit'),
        ];
    }
}
