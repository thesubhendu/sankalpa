<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\Goal;
use App\Models\WeeklyGoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    // protected static ?string $navigationGroup = 'Planning';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Task Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('description')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('goal_id')
                                    ->label('Long-term Goal')
                                    ->options(Goal::all()->pluck('title', 'id'))
                                    ->searchable()
                                    ->placeholder('Select a goal'),

                                Forms\Components\Select::make('weekly_goal_id')
                                    ->label('Weekly Goal')
                                    ->options(WeeklyGoal::all()->pluck('title', 'id'))
                                    ->searchable()
                                    ->placeholder('Select a weekly goal'),
                            ]),
                    ]),

                Section::make('Task Properties')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('energy_level')
                                    ->label('Energy Level')
                                    ->options([
                                        'low' => 'Low 🌱',
                                        'medium' => 'Medium ⚡',
                                        'high' => 'High 🔥',
                                    ])
                                    ->default('medium')
                                    ->required(),

                                Forms\Components\TextInput::make('estimated_duration')
                                    ->label('Duration (minutes)')
                                    ->numeric()
                                    ->placeholder('e.g., 60')
                                    ->helperText('Estimated time in minutes'),

                                Forms\Components\Select::make('priority')
                                    ->options([
                                        1 => 'Low',
                                        2 => 'Medium',
                                        3 => 'High',
                                    ])
                                    ->default(2)
                                    ->required(),
                            ]),

                        Forms\Components\TextInput::make('points')
                            ->numeric()
                            ->default(10)
                            ->required()
                            ->minValue(1),

                        Forms\Components\Textarea::make('motivation_note')
                            ->label('Why is this important?')
                            ->placeholder('Write a motivational note to remind yourself why this task matters')
                            ->rows(2),
                    ]),

                Section::make('Scheduling')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('due_date')
                                    ->required()
                                    ->default(now()->addDay()),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'in_progress' => 'In Progress',
                                        'done' => 'Done',
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Session Tracking')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('started_at')
                                    ->label('Started At')
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('completed_at')
                                    ->label('Completed At')
                                    ->disabled(),
                            ]),
                    ])
                    ->visible(fn ($record) => $record && ($record->started_at || $record->completed_at)),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('goal.title')
                    ->label('Goal')
                    ->limit(20)
                    ->placeholder('No goal'),

                Tables\Columns\TextColumn::make('weeklyGoal.title')
                    ->label('Weekly Goal')
                    ->limit(20)
                    ->placeholder('No weekly goal'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'done',
                    ]),

                Tables\Columns\BadgeColumn::make('energy_level')
                    ->label('Energy')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => '🌱 Low',
                        'medium' => '⚡ Medium',
                        'high' => '🔥 High',
                    }),

                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Duration')
                    ->placeholder('Not set'),

                Tables\Columns\BadgeColumn::make('priority')
                    ->colors([
                        'gray' => 1,
                        'warning' => 2,
                        'danger' => 3,
                    ])
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Low',
                        2 => 'Medium',
                        3 => 'High',
                    }),

                Tables\Columns\TextColumn::make('points')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime('M j, H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_overdue')
                    ->label('⚠️')
                    ->boolean()
                    ->getStateUsing(fn (Task $record): bool => $record->isOverdue())
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        'low' => '🌱 Low',
                        'medium' => '⚡ Medium',
                        'high' => '🔥 High',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        1 => 'Low Priority',
                        2 => 'Medium Priority',
                        3 => 'High Priority',
                    ]),

                Tables\Filters\SelectFilter::make('goal')
                    ->relationship('goal', 'title'),

                Tables\Filters\SelectFilter::make('weeklyGoal')
                    ->relationship('weeklyGoal', 'title')
                    ->label('Weekly Goal'),

                Tables\Filters\Filter::make('due_today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('due_date', today()))
                    ->label('Due Today'),

                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())->where('status', '!=', 'done'))
                    ->label('Overdue'),

                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->active())
                    ->label('Active Tasks'),
            ])
            ->actions([
                Action::make('start')
                    ->label('Start')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(fn (Task $record): bool => $record->canStart())
                    ->action(function (Task $record) {
                        // Stop any other active tasks for this user
                        Task::where('user_id', $record->user_id)
                            ->where('status', 'in_progress')
                            ->update(['status' => 'pending', 'started_at' => null]);
                        
                        $record->start();
                        
                        Notification::make()
                            ->title('Task started successfully')
                            ->success()
                            ->send();
                    }),

                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->canComplete())
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Completion Notes (Optional)')
                            ->placeholder('Any notes about completing this task...')
                            ->rows(3),
                    ])
                    ->action(function (Task $record, array $data) {
                        $record->complete($data['notes'] ?? null);
                        
                        Notification::make()
                            ->title("Task completed! +{$record->points} points")
                            ->success()
                            ->send();
                    }),

                Action::make('pause')
                    ->label('Pause')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (Task $record): bool => $record->isActive())
                    ->action(function (Task $record) {
                        $record->update(['status' => 'pending', 'started_at' => null]);
                        
                        Notification::make()
                            ->title('Task paused')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['goal', 'weeklyGoal', 'user']);
    }
}
