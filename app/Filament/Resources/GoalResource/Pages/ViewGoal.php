<?php

namespace App\Filament\Resources\GoalResource\Pages;

use App\Filament\Resources\GoalResource;
use App\Models\Goal;
use App\Models\Task;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;

class ViewGoal extends ViewRecord
{
    protected static string $resource = GoalResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->title;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Goal Overview')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('title')
                                        ->weight(FontWeight::Bold)
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    Infolists\Components\TextEntry::make('user.name')
                                        ->label('Assigned to'),
                                    Infolists\Components\TextEntry::make('difficulty_level')
                                        ->badge()
                                        ->colors([
                                            'success' => 'easy',
                                            'warning' => 'medium',
                                            'danger' => 'hard',
                                        ]),
                                    Infolists\Components\TextEntry::make('total_points')
                                        ->label('Points Earned')
                                        ->suffix(' pts')
                                        ->color('success'),
                                ]),
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('start_date')
                                        ->date(),
                                    Infolists\Components\TextEntry::make('end_date')
                                        ->date(),
                                    Infolists\Components\TextEntry::make('completion_percentage')
                                        ->label('Progress')
                                        ->suffix('%')
                                        ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
                                    Infolists\Components\TextEntry::make('remaining_tasks')
                                        ->label('Remaining Tasks')
                                        ->suffix(' tasks'),
                                ]),
                        ]),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->placeholder('No description provided'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Progress Overview')
                    ->schema([
                        Infolists\Components\ViewEntry::make('progress_chart')
                            ->view('filament.infolists.components.goal-progress-chart'),
                    ]),

                Infolists\Components\Section::make('Tasks')
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('add_task')
                            ->label('Add Task')
                            ->icon('heroicon-o-plus-circle')
                            ->color('success')
                            ->form([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->rows(3),
                                Forms\Components\DateTimePicker::make('due_date')
                                    ->required()
                                    ->after('now'),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'in_progress' => 'In Progress',
                                        'done' => 'Done',
                                    ])
                                    ->default('pending')
                                    ->required(),
                                Forms\Components\TextInput::make('points')
                                    ->numeric()
                                    ->default(10)
                                    ->required()
                                    ->minValue(1),
                            ])
                            ->action(function (array $data) {
                                Task::create([
                                    'goal_id' => $this->getRecord()->id,
                                    'title' => $data['title'],
                                    'description' => $data['description'],
                                    'due_date' => $data['due_date'],
                                    'status' => $data['status'],
                                    'points' => $data['points'],
                                    'user_id' => $this->getRecord()->user_id,
                                ]);
                                
                                Notification::make()
                                    ->title('Task created successfully')
                                    ->success()
                                    ->send();
                                    
                                $this->refreshRecordData(['tasks']);
                            }),
                    ])
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('tasks')
                            ->schema([
                                Infolists\Components\Split::make([
                                    Infolists\Components\Grid::make(2)
                                        ->schema([
                                            Infolists\Components\TextEntry::make('title')
                                                ->weight(FontWeight::Bold)
                                                ->color('primary'),
                                            Infolists\Components\TextEntry::make('due_date')
                                                ->dateTime()
                                                ->color(fn ($record) => $record->isOverdue() ? 'danger' : 'success'),
                                        ]),
                                    Infolists\Components\Grid::make(1)
                                        ->schema([
                                            Infolists\Components\TextEntry::make('status')
                                                ->badge()
                                                ->colors([
                                                    'warning' => 'pending',
                                                    'primary' => 'in_progress',
                                                    'success' => 'done',
                                                ]),
                                        ]),
                                ]),
                                Infolists\Components\TextEntry::make('description')
                                    ->columnSpanFull()
                                    ->placeholder('No description provided'),
                                Infolists\Components\Actions::make([
                                    Infolists\Components\Actions\Action::make('edit_task')
                                        ->label('Edit Task')
                                        ->icon('heroicon-o-pencil')
                                        ->color('warning')
                                        ->form([
                                            Forms\Components\TextInput::make('title')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\Textarea::make('description')
                                                ->rows(3),
                                            Forms\Components\DateTimePicker::make('due_date')
                                                ->required(),
                                            Forms\Components\Select::make('status')
                                                ->options([
                                                    'pending' => 'Pending',
                                                    'in_progress' => 'In Progress',
                                                    'done' => 'Done',
                                                ])
                                                ->required(),
                                            Forms\Components\TextInput::make('points')
                                                ->numeric()
                                                ->required()
                                                ->minValue(1),
                                        ])
                                        ->fillForm(fn ($record) => [
                                            'title' => $record->title,
                                            'description' => $record->description,
                                            'due_date' => $record->due_date,
                                            'status' => $record->status,
                                            'points' => $record->points,
                                        ])
                                        ->action(function (array $data, $record) {
                                            $record->update($data);
                                            
                                            Notification::make()
                                                ->title('Task updated successfully')
                                                ->success()
                                                ->send();
                                                
                                            $this->refreshRecordData(['tasks']);
                                        }),
                                    Infolists\Components\Actions\Action::make('delete_task')
                                        ->label('Delete')
                                        ->icon('heroicon-o-trash')
                                        ->color('danger')
                                        ->requiresConfirmation()
                                        ->modalDescription('Are you sure you want to delete this task?')
                                        ->action(function ($record) {
                                            $record->delete();
                                            
                                            Notification::make()
                                                ->title('Task deleted successfully')
                                                ->success()
                                                ->send();
                                                
                                            $this->refreshRecordData(['tasks']);
                                        }),
                                ])
                                ->alignment('start'),
                            ]),
                    ]),


            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }

    protected function refreshRecordData(array $relationships = []): void
    {
        if (empty($relationships)) {
            $this->record = $this->record->fresh();
        } else {
            $this->record->load($relationships);
        }
    }
} 