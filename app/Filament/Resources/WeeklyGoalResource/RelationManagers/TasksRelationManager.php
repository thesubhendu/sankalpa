<?php

namespace App\Filament\Resources\WeeklyGoalResource\RelationManagers;

use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('points')
                            ->numeric()
                            ->default(5)
                            ->required(),

                        Forms\Components\Select::make('energy_level')
                            ->options([
                                'low' => 'Low Energy 🌱',
                                'medium' => 'Medium Energy ⚡',
                                'high' => 'High Energy 🔥',
                            ])
                            ->default('medium')
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->options([
                                1 => 'Low Priority',
                                2 => 'Medium Priority',
                                3 => 'High Priority',
                            ])
                            ->default(2)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'done' => 'Done',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\TextInput::make('estimated_duration')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->suffix('minutes'),

                        Forms\Components\DateTimePicker::make('due_date')
                            ->default(fn ($livewire) => $livewire->ownerRecord->week_end_date)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'done',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-play' => 'in_progress',
                        'heroicon-o-check-circle' => 'done',
                    ]),

                Tables\Columns\TextColumn::make('points')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('energy_level')
                    ->label('Energy')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => '🌱 Low',
                        'medium' => '⚡ Medium',
                        'high' => '🔥 High',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ]),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Low',
                        2 => 'Medium',
                        3 => 'High',
                        default => 'Medium',
                    })
                    ->colors([
                        'gray' => 1,
                        'warning' => 2,
                        'danger' => 3,
                    ]),

                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Duration')
                    ->placeholder('Not set'),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(fn (Task $record): string => $record->isOverdue() ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('goal.title')
                    ->label('Long-term Goal')
                    ->limit(20)
                    ->placeholder('Not linked')
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                    ]),

                Tables\Filters\SelectFilter::make('energy_level')
                    ->label('Energy Level')
                    ->options([
                        'low' => 'Low Energy',
                        'medium' => 'Medium Energy',
                        'high' => 'High Energy',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        1 => 'Low Priority',
                        2 => 'Medium Priority',
                        3 => 'High Priority',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Add Task'),
            ])
            ->actions([
                Action::make('start')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->action(function (Task $record) {
                        $record->start();
                        Notification::make()
                            ->title('Task started!')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Task $record): bool => $record->status === 'pending'),

                Action::make('complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Task $record) {
                        $record->complete();
                        Notification::make()
                            ->title('Task completed!')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Task $record): bool => in_array($record->status, ['pending', 'in_progress'])),

                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Action::make('bulk_complete')
                        ->label('Mark as Complete')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status !== 'done') {
                                    $record->complete();
                                }
                            }
                            Notification::make()
                                ->title('Tasks completed!')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('due_date', 'asc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }
}
