<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeeklyGoalResource\Pages;
use App\Filament\Resources\WeeklyGoalResource\RelationManagers;
use App\Models\WeeklyGoal;
use App\Models\Goal;
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
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class WeeklyGoalResource extends Resource
{
    protected static ?string $model = WeeklyGoal::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Weekly Planning';

    // protected static ?string $navigationGroup = 'Planning';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Weekly Goal Details')
                    ->description('Create your weekly goal and tasks')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Build Introspection Journal and Sankalpa app'),

                                Forms\Components\Select::make('goal_id')
                                    ->label('Long-term Goal (Optional)')
                                    ->options(Goal::all()->pluck('title', 'id'))
                                    ->searchable()
                                    ->placeholder('Link to a long-term goal'),
                            ]),

                        Forms\Components\Toggle::make('use_current_week')
                            ->label('📅 Use Current Week')
                            ->helperText('Automatically set dates to current week (Monday to Sunday)')
                            ->default(true)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('week_start_date', Carbon::now()->startOfWeek());
                                    $set('week_end_date', Carbon::now()->endOfWeek());
                                }
                            }),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('week_start_date')
                                    ->label('Week Start')
                                    ->default(Carbon::now()->startOfWeek())
                                    ->required()
                                    ->readOnly(fn (Forms\Get $get) => $get('use_current_week')),

                                Forms\Components\DatePicker::make('week_end_date')
                                    ->label('Week End')
                                    ->default(Carbon::now()->endOfWeek())
                                    ->required()
                                    ->readOnly(fn (Forms\Get $get) => $get('use_current_week')),
                            ]),
                    ]),

                Section::make('Task Input')
                    ->description('Enter your tasks using any of these simple formats')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Textarea::make('raw_input')
                                    ->label('Tasks')
                                    ->required()
                                    ->rows(15)
                                    ->placeholder("# Simple format (recommended)\nBuild Introspection Journal\nLearn Java Spring (2h)\nUpload Videos (30m, high priority)\nReview Code (1h, focus, Tue)\n\n# With points\nBig Project - 25 points\nQuick Task - 5 points\n\n# Legacy format still works\nOld Task: 20, medium (2h), 1, high, 2025-06-20")
                                    ->helperText('Multiple formats supported - choose what feels natural to you!'),

                                Forms\Components\Placeholder::make('parsing_info')
                                    ->label('✨ Smart Parsing Guide')
                                    ->content(view('filament.components.task-parsing-help')),
                            ]),
                    ]),

                Section::make('Summary')
                    ->schema([
                        Forms\Components\Textarea::make('parsed_summary')
                            ->label('Generated Summary')
                            ->disabled()
                            ->rows(8)
                            ->placeholder('Summary will appear here after parsing'),
                    ])
                    ->visible(fn ($record) => $record && $record->parsed_summary),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('week_start_date')
                    ->label('Week')
                    ->formatStateUsing(fn ($record) => $record->week_start_date->format('M j') . ' - ' . $record->week_end_date->format('M j, Y'))
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->week_start_date->isCurrentWeek() ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('goal.title')
                    ->label('Long-term Goal')
                    ->placeholder('No goal linked')
                    ->limit(30)
                    ->color('info'),

                Tables\Columns\TextColumn::make('tasks_summary')
                    ->label('Tasks Status')
                    ->formatStateUsing(function ($record) {
                        $done = $record->tasks->where('status', 'done')->count();
                        $inProgress = $record->tasks->where('status', 'in_progress')->count();
                        $pending = $record->tasks->where('status', 'pending')->count();
                        return "✅ {$done} | 🔄 {$inProgress} | ⏳ {$pending}";
                    })
                    ->tooltip(fn ($record) => $record->tasks->count() . ' total tasks'),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($record) => $record->completion_percentage . '%')
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->completion_percentage >= 80 => 'success',
                        $record->completion_percentage >= 50 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('points_progress')
                    ->label('Points')
                    ->formatStateUsing(function ($record) {
                        $earned = $record->tasks->where('status', 'done')->sum('points');
                        return "{$earned}/{$record->total_points}";
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('goal_id')
                    ->label('Long-term Goal')
                    ->options(Goal::all()->pluck('title', 'id'))
                    ->placeholder('All goals'),
                
                Tables\Filters\SelectFilter::make('week_filter')
                    ->label('Time Period')
                    ->options([
                        'current_week' => 'Current Week',
                        'last_week' => 'Last Week',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'current_week' => $query->whereBetween('week_start_date', [
                                Carbon::now()->startOfWeek(),
                                Carbon::now()->endOfWeek()
                            ]),
                            'last_week' => $query->whereBetween('week_start_date', [
                                Carbon::now()->subWeek()->startOfWeek(),
                                Carbon::now()->subWeek()->endOfWeek()
                            ]),
                            'this_month' => $query->whereBetween('week_start_date', [
                                Carbon::now()->startOfMonth(),
                                Carbon::now()->endOfMonth()
                            ]),
                            'last_month' => $query->whereBetween('week_start_date', [
                                Carbon::now()->subMonth()->startOfMonth(),
                                Carbon::now()->subMonth()->endOfMonth()
                            ]),
                            default => $query,
                        };
                    }),

                Tables\Filters\SelectFilter::make('completion_status')
                    ->label('Completion Status')
                    ->options([
                        'excellent' => 'Excellent (80%+)',
                        'good' => 'Good (60-79%)',
                        'needs_improvement' => 'Needs Improvement (<60%)',
                        'complete' => 'Fully Complete (100%)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['value'])) {
                            return $query->whereHas('tasks', function ($subQuery) use ($data) {
                                $subQuery->select('weekly_goal_id')
                                    ->selectRaw('COUNT(*) as total_tasks')
                                    ->selectRaw('SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed_tasks')
                                    ->groupBy('weekly_goal_id')
                                    ->havingRaw(match ($data['value']) {
                                        'excellent' => '(completed_tasks / total_tasks * 100) >= 80',
                                        'good' => '(completed_tasks / total_tasks * 100) BETWEEN 60 AND 79',
                                        'needs_improvement' => '(completed_tasks / total_tasks * 100) < 60',
                                        'complete' => '(completed_tasks / total_tasks * 100) = 100',
                                        default => '1=1'
                                    });
                            });
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Action::make('quick_view')
                    ->label('Quick Overview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(fn (WeeklyGoal $record): View => view(
                        'filament.modals.weekly-goal-overview',
                        ['record' => $record]
                    ))
                    ->modalHeading(fn (WeeklyGoal $record): string => $record->title . ' - Overview')
                    ->modalWidth('5xl'),

                Action::make('clone_for_next_week')
                    ->label('Clone for Next Week')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('success')
                    ->action(function (WeeklyGoal $record) {
                        $nextWeekStart = Carbon::now()->addWeek()->startOfWeek();
                        $nextWeekEnd = Carbon::now()->addWeek()->endOfWeek();
                        
                        $newGoal = WeeklyGoal::create([
                            'title' => $record->title . ' (Next Week)',
                            'raw_input' => $record->raw_input,
                            'week_start_date' => $nextWeekStart,
                            'week_end_date' => $nextWeekEnd,
                            'user_id' => $record->user_id,
                            'goal_id' => $record->goal_id,
                        ]);
                        
                        $newGoal->parseInput($newGoal->raw_input);
                        
                        Notification::make()
                            ->title('Weekly goal cloned for next week')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),

                Action::make('parseInput')
                    ->label('Re-parse Tasks')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (WeeklyGoal $record) {
                        // Delete existing tasks
                        $record->tasks()->delete();
                        
                        // Re-parse
                        $record->parseInput($record->raw_input);
                        
                        Notification::make()
                            ->title('Tasks re-parsed successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalDescription('This will delete all existing tasks and create new ones based on the raw input.'),

                Action::make('mark_week_complete')
                    ->label('Complete Week')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (WeeklyGoal $record) {
                        $record->tasks()->where('status', 'pending')->update(['status' => 'done']);
                        
                        Notification::make()
                            ->title('All pending tasks marked as complete')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (WeeklyGoal $record) => $record->tasks()->where('status', 'pending')->count() > 0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('week_start_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWeeklyGoals::route('/'),
            'create' => Pages\CreateWeeklyGoal::route('/create'),
            'view' => Pages\ViewWeeklyGoal::route('/{record}'),
            'edit' => Pages\EditWeeklyGoal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['goal', 'tasks']);
    }
}
