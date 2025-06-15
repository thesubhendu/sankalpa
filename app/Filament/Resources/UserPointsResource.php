<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserPointsResource\Pages;
use App\Filament\Resources\UserPointsResource\RelationManagers;
use App\Models\UserPoints;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserPointsResource extends Resource
{
    protected static ?string $model = UserPoints::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Points';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('total_points')
                    ->required()
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('weekly_points')
                    ->required()
                    ->numeric()
                    ->default(0),

                Forms\Components\DatePicker::make('last_reset_date')
                    ->label('Last Weekly Reset')
                    ->helperText('Date when weekly points were last reset'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_points')
                    ->label('Total Points')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('weekly_points')
                    ->label('Weekly Points')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_reset_date')
                    ->label('Last Reset')
                    ->date()
                    ->sortable()
                    ->placeholder('Never'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('high_performers')
                    ->query(fn (Builder $query): Builder => $query->where('total_points', '>', 100))
                    ->label('High Performers (>100 points)'),

                Tables\Filters\Filter::make('this_week_active')
                    ->query(fn (Builder $query): Builder => $query->where('weekly_points', '>', 0))
                    ->label('Active This Week'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('resetWeekly')
                    ->label('Reset Weekly')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (UserPoints $record) {
                        $record->resetWeeklyPoints();
                        
                        Notification::make()
                            ->title('Weekly points reset successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalDescription('This will reset the weekly points to 0 for this user.'),

                Action::make('addPoints')
                    ->label('Add Points')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('points')
                            ->label('Points to Add')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\TextInput::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (UserPoints $record, array $data) {
                        $record->addPoints($data['points'], $data['reason']);
                        
                        Notification::make()
                            ->title("{$data['points']} points added successfully")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('total_points', 'desc');
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
            'index' => Pages\ListUserPoints::route('/'),
            'create' => Pages\CreateUserPoints::route('/create'),
            'edit' => Pages\EditUserPoints::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }
}
