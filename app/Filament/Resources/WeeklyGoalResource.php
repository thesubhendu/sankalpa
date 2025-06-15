<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeeklyGoalResource\Pages;
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
use Carbon\Carbon;

class WeeklyGoalResource extends Resource
{
    protected static ?string $model = WeeklyGoal::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Weekly Planning';

    protected static ?string $navigationGroup = 'Planning';

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

                        Forms\Components\DatePicker::make('week_start_date')
                            ->label('Week Start')
                            ->default(Carbon::now()->startOfWeek())
                            ->required(),

                        Forms\Components\DatePicker::make('week_end_date')
                            ->label('Week End')
                            ->default(Carbon::now()->endOfWeek())
                            ->required(),
                    ]),

                Section::make('Task Input')
                    ->description('Enter your tasks in the format: "Task: points, energy_level (duration), goal_id, priority, due_date"')
                    ->schema([
                        Forms\Components\Textarea::make('raw_input')
                            ->label('Tasks')
                            ->required()
                            ->rows(10)
                            ->placeholder("Build Introspection Journal: 20, medium (2h), 1, high, 2025-06-20\nLearn Java Spring: 15, high (1h), 2, medium\nUpload Videos: 10, low (30m), 3, high")
                            ->helperText('Format: Task: points, energy_level (duration), goal_id, priority, due_date\nEnergy levels: low, medium, high\nPriorities: low, medium, high'),

                        Forms\Components\Placeholder::make('parsing_info')
                            ->label('How it works')
                            ->content('After saving, tasks will be automatically parsed and created. You can then view the summary and individual tasks.'),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('week_start_date')
                    ->label('Week')
                    ->formatStateUsing(fn ($record) => $record->week_start_date->format('M j') . ' - ' . $record->week_end_date->format('M j, Y'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('goal.title')
                    ->label('Long-term Goal')
                    ->placeholder('No goal linked')
                    ->limit(30),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->label('Tasks')
                    ->counts('tasks')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($record) => $record->completion_percentage . '%')
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->completion_percentage >= 80 => 'success',
                        $record->completion_percentage >= 50 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_points')
                    ->label('Total Points')
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            //
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
