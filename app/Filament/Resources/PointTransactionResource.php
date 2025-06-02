<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointTransactionResource\Pages;
use App\Filament\Resources\PointTransactionResource\RelationManagers;
use App\Models\PointTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PointTransactionResource extends Resource
{
    protected static ?string $model = PointTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Point Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->default(auth()->id()),
                Forms\Components\Select::make('source')
                    ->options([
                        'task_completion' => 'Task Completion',
                        'bonus' => 'Bonus',
                        'streak' => 'Streak',
                        'penalty' => 'Penalty',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('source')
                    ->colors([
                        'success' => 'task_completion',
                        'primary' => 'bonus',
                        'warning' => 'streak',
                        'danger' => 'penalty',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->sortable()
                    ->color(fn (PointTransaction $record): string => $record->amount > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'task_completion' => 'Task Completion',
                        'bonus' => 'Bonus',
                        'streak' => 'Streak',
                        'penalty' => 'Penalty',
                    ]),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
                Tables\Filters\Filter::make('this_week')
                    ->query(fn (Builder $query): Builder => $query->thisWeek())
                    ->label('This Week'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPointTransactions::route('/'),
            'create' => Pages\CreatePointTransaction::route('/create'),
            'edit' => Pages\EditPointTransaction::route('/{record}/edit'),
        ];
    }
}
