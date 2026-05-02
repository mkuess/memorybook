<?php

namespace App\Filament\Resources\MemoryPageResource\Pages;

use App\Filament\Resources\MemoryPageResource;
use App\Models\Story;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManageMemoryPageStories extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = MemoryPageResource::class;

    protected static string $view = 'filament.resources.memory-page-resource.pages.manage-memory-page-stories';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        static::authorizeResourceAccess();
    }

    public function getTitle(): string
    {
        return 'Stories verwalten';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Story::query()->where('memory_page_id', $this->record->id))
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable(),

                IconColumn::make('is_published')
                    ->label('Veröffentlicht')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Reihenfolge')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('content')
                            ->label('Inhalt')
                            ->rows(5),

                        Checkbox::make('is_published')
                            ->label('Veröffentlicht'),

                        TextInput::make('sort_order')
                            ->label('Reihenfolge')
                            ->numeric()
                            ->default(0),
                    ]),
            ])
            ->bulkActions([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Zurück')
                ->url(MemoryPageResource::getUrl('view', ['record' => $this->record]))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }
}
