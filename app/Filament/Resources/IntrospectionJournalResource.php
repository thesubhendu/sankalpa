<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntrospectionJournalResource\Pages;
use App\Filament\Resources\IntrospectionJournalResource\RelationManagers;
use App\Models\IntrospectionJournal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;

class IntrospectionJournalResource extends Resource
{
    protected static ?string $model = IntrospectionJournal::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Introspection Journal';

    protected static ?string $navigationGroup = 'Self Development';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Entry Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Brief title for this entry'),
                                
                                Select::make('type')
                                    ->options([
                                        'data_drop' => 'Data Drop',
                                        'learning' => 'Learning',
                                        'rule' => 'Rule',
                                        'purpose' => 'Purpose',
                                    ])
                                    ->required()
                                    ->default('data_drop')
                                    ->live()
                                    ->afterStateUpdated(fn ($state, $set) => 
                                        $state === 'data_drop' ? null : $set('needs_review', false)
                                    ),
                                
                                DatePicker::make('entry_date')
                                    ->required()
                                    ->default(now()),
                                
                                Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->default(auth()->id()),
                            ]),
                    ]),

                Section::make('Content')
                    ->schema([
                        MarkdownEditor::make('content')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'strike',
                                'heading',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'codeBlock',
                            ])
                            ->placeholder('Write your introspection entry here...'),
                    ]),

                Section::make('Data Drop Specifics')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('mood')
                                    ->options([
                                        'very_low' => 'Very Low',
                                        'low' => 'Low',
                                        'neutral' => 'Neutral',
                                        'good' => 'Good',
                                        'very_good' => 'Very Good',
                                    ])
                                    ->placeholder('Select your current mood'),
                                
                                TextInput::make('trigger')
                                    ->maxLength(255)
                                    ->placeholder('What triggered this feeling?'),
                            ]),
                        
                        Textarea::make('what_happened')
                            ->rows(3)
                            ->placeholder('Describe what happened in detail...'),
                        
                        Textarea::make('feelings')
                            ->rows(3)
                            ->placeholder('How are you feeling right now?'),
                        
                        Textarea::make('trigger_reason')
                            ->rows(3)
                            ->placeholder('What is the reason that triggered what you are feeling?'),
                        
                        Select::make('intensity_level')
                            ->label('Intensity Level (1-10)')
                            ->options([
                                1 => '1 - Very Low',
                                2 => '2 - Low',
                                3 => '3 - Below Average',
                                4 => '4 - Moderate',
                                5 => '5 - Average',
                                6 => '6 - Above Average',
                                7 => '7 - High',
                                8 => '8 - Very High',
                                9 => '9 - Intense',
                                10 => '10 - Extreme',
                            ])
                            ->default(5)
                            ->placeholder('Select intensity level'),
                    ])
                    ->visible(fn ($get) => $get('type') === 'data_drop'),

                Section::make('Organization')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TagsInput::make('tags')
                                    ->placeholder('Add tags to categorize this entry'),
                                
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('is_important')
                                            ->label('Mark as Important'),
                                        
                                        Toggle::make('needs_review')
                                            ->label('Needs Review')
                                            ->visible(fn ($get) => $get('type') === 'data_drop'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('type')
                    ->label('Type')
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
                
                TextColumn::make('mood')
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
                    ->placeholder('Not Set'),
                
                TextColumn::make('intensity_level')
                    ->label('Intensity')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state <= 3,
                        'primary' => fn ($state) => $state > 3 && $state <= 6,
                        'warning' => fn ($state) => $state > 6 && $state <= 8,
                        'danger' => fn ($state) => $state > 8,
                    ])
                    ->placeholder('Not Set'),
                
                IconColumn::make('is_important')
                    ->label('Important')
                    ->boolean()
                    ->toggleable(),
                
                IconColumn::make('needs_review')
                    ->label('Needs Review')
                    ->boolean()
                    ->toggleable(),
                
                TextColumn::make('entry_date')
                    ->date()
                    ->sortable(),
                
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('entry_date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'data_drop' => 'Data Drop',
                        'learning' => 'Learning',
                        'rule' => 'Rule',
                        'purpose' => 'Purpose',
                    ]),
                
                SelectFilter::make('mood')
                    ->options([
                        'very_low' => 'Very Low',
                        'low' => 'Low',
                        'neutral' => 'Neutral',
                        'good' => 'Good',
                        'very_good' => 'Very Good',
                    ]),
                
                Filter::make('important')
                    ->query(fn (Builder $query): Builder => $query->where('is_important', true))
                    ->label('Important Only'),
                
                Filter::make('needs_review')
                    ->query(fn (Builder $query): Builder => $query->where('needs_review', true))
                    ->label('Needs Review'),
                
                Filter::make('this_week')
                    ->query(fn (Builder $query): Builder => $query->thisWeek())
                    ->label('This Week'),
                
                Filter::make('this_month')
                    ->query(fn (Builder $query): Builder => $query->thisMonth())
                    ->label('This Month'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_important')
                    ->label('Toggle Important')
                    ->icon('heroicon-o-star')
                    ->action(fn (IntrospectionJournal $record) => $record->update(['is_important' => !$record->is_important]))
                    ->color('warning'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_important')
                        ->label('Mark as Important')
                        ->icon('heroicon-o-star')
                        ->action(fn ($records) => $records->each->update(['is_important' => true]))
                        ->color('warning'),
                    Tables\Actions\BulkAction::make('mark_reviewed')
                        ->label('Mark as Reviewed')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['needs_review' => false]))
                        ->color('success'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            IntrospectionJournalResource\Widgets\JournalInsightsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntrospectionJournals::route('/'),
            'create' => Pages\CreateIntrospectionJournal::route('/create'),
            'view' => Pages\ViewIntrospectionJournal::route('/{record}'),
            'edit' => Pages\EditIntrospectionJournal::route('/{record}/edit'),
        ];
    }
}
