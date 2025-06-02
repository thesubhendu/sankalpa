<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Goal Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(3),
                Forms\Components\Select::make('goal_id')
                    ->relationship('goal', 'title')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('milestone_id')
                    ->relationship('milestone', 'title')
                    ->searchable()
                    ->nullable(),
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
                Forms\Components\TextInput::make('points')
                    ->numeric()
                    ->default(10)
                    ->required()
                    ->minValue(1),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('goal.title')
                    ->label('Goal')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('milestone.title')
                    ->label('Milestone')
                    ->sortable()
                    ->placeholder('No milestone'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned To')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in_progress',
                        'success' => 'done',
                    ]),
                Tables\Columns\TextColumn::make('points')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_overdue')
                    ->label('Overdue')
                    ->boolean()
                    ->getStateUsing(fn (Task $record): bool => $record->isOverdue())
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                    ]),
                Tables\Filters\SelectFilter::make('goal')
                    ->relationship('goal', 'title'),
                Tables\Filters\Filter::make('due_today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('due_date', today()))
                    ->label('Due Today'),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())->where('status', '!=', 'done'))
                    ->label('Overdue'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_completed')
                    ->label('Complete')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status !== 'done')
                    ->action(fn (Task $record) => $record->markCompleted()),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'asc');
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
}
