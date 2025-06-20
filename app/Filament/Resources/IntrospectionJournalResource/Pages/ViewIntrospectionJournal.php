<?php

namespace App\Filament\Resources\IntrospectionJournalResource\Pages;

use App\Filament\Resources\IntrospectionJournalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Enums\FontWeight;

class ViewIntrospectionJournal extends ViewRecord
{
    protected static string $resource = IntrospectionJournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Entry Overview')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('title')
                                    ->weight(FontWeight::Bold)
                                    ->size('lg'),
                                TextEntry::make('type')
                                    ->badge()
                                    ->colors([
                                        'primary' => 'data_drop',
                                        'success' => 'learning',
                                        'warning' => 'rule',
                                        'info' => 'purpose',
                                    ])
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'data_drop' => 'Data Drop',
                                        'learning' => 'Learning',
                                        'rule' => 'Rule',
                                        'purpose' => 'Purpose',
                                        default => ucfirst($state),
                                    }),
                                TextEntry::make('entry_date')
                                    ->date(),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('mood')
                                    ->badge()
                                    ->colors([
                                        'danger' => ['very_low', 'low'],
                                        'gray' => 'neutral',
                                        'success' => ['good', 'very_good'],
                                    ])
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'very_low' => 'Very Low',
                                        'low' => 'Low',
                                        'neutral' => 'Neutral',
                                        'good' => 'Good',
                                        'very_good' => 'Very Good',
                                        default => 'Not Set',
                                    })
                                    ->placeholder('Not Set')
                                    ->visible(fn ($record) => $record->mood !== null),
                                
                                TextEntry::make('intensity_level')
                                    ->label('Intensity')
                                    ->badge()
                                    ->colors([
                                        'success' => fn ($state) => $state <= 3,
                                        'primary' => fn ($state) => $state > 3 && $state <= 6,
                                        'warning' => fn ($state) => $state > 6 && $state <= 8,
                                        'danger' => fn ($state) => $state > 8,
                                    ])
                                    ->visible(fn ($record) => $record->intensity_level !== null),
                                
                                IconEntry::make('is_important')
                                    ->label('Important')
                                    ->boolean()
                                    ->visible(fn ($record) => $record->is_important),
                                
                                IconEntry::make('needs_review')
                                    ->label('Needs Review')
                                    ->boolean()
                                    ->visible(fn ($record) => $record->needs_review),
                            ]),
                    ]),

                Section::make('Content')
                    ->schema([
                        TextEntry::make('content')
                            ->markdown()
                            ->hiddenLabel(),
                    ]),

                Section::make('Data Drop Details')
                    ->schema([
                        TextEntry::make('what_happened')
                            ->label('What Happened')
                            ->placeholder('Not provided')
                            ->visible(fn ($record) => $record->isDataDrop() && $record->what_happened),
                        
                        TextEntry::make('feelings')
                            ->label('Feelings')
                            ->placeholder('Not provided')
                            ->visible(fn ($record) => $record->isDataDrop() && $record->feelings),
                        
                        TextEntry::make('trigger_reason')
                            ->label('Trigger Reason')
                            ->placeholder('Not provided')
                            ->visible(fn ($record) => $record->isDataDrop() && $record->trigger_reason),
                        
                        TextEntry::make('trigger')
                            ->label('Trigger')
                            ->placeholder('Not provided')
                            ->visible(fn ($record) => $record->isDataDrop() && $record->trigger),
                    ])
                    ->visible(fn ($record) => $record->isDataDrop()),

                Section::make('Organization')
                    ->schema([
                        TextEntry::make('tags')
                            ->badge()
                            ->separator(',')
                            ->visible(fn ($record) => $record->tags && count($record->tags) > 0),
                    ])
                    ->visible(fn ($record) => $record->tags && count($record->tags) > 0),

                Section::make('Metadata')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->label('Created'),
                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->label('Last Updated'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
