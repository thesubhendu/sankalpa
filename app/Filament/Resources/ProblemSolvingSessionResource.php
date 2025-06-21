<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProblemSolvingSessionResource\Pages;
use App\Models\ProblemSolvingSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class ProblemSolvingSessionResource extends Resource
{
    protected static ?string $model = ProblemSolvingSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $navigationLabel = 'Problem Solving';

    protected static ?string $navigationGroup = 'Self Development';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Problem Overview')
                    ->description('Start by describing your problem clearly')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Brief title for this problem-solving session'),
                                
                                DatePicker::make('session_date')
                                    ->required()
                                    ->default(now())
                                    ->label('Session Date'),
                            ]),
                        
                        Textarea::make('problem_description')
                            ->required()
                            ->rows(4)
                            ->placeholder('Describe the problem you want to solve in detail...'),
                        

                    ]),

                Section::make('Problem Analysis - SHERLOCK HOLMES Method')
                    ->description('Let\'s analyze what\'s going wrong and why')
                    ->schema([
                        Textarea::make('what_doing_wrong')
                            ->label('What am I doing wrong?')
                            ->placeholder('Identify the specific behavior or action that is problematic...')
                            ->rows(3),
                        
                        Textarea::make('trigger')
                            ->label('What is the trigger that makes me do this?')
                            ->placeholder('What situation, emotion, or circumstance triggers this behavior?...')
                            ->rows(3),
                        
                        Toggle::make('is_daily_pattern')
                            ->label('Is this a pattern that I do daily?')
                            ->helperText('Mark this if this problem happens regularly'),
                        
                        Textarea::make('what_to_change_trigger')
                            ->label('What do I need to change to avoid the trigger?')
                            ->placeholder('How can you modify your environment or approach to prevent the trigger?...')
                            ->rows(3),
                        
                        Textarea::make('what_do_when_doing_wrong')
                            ->label('What do I do once I start doing the wrong thing?')
                            ->placeholder('Describe your typical response or behavior chain...')
                            ->rows(3),
                        
                        Textarea::make('long_term_impact')
                            ->label('How does that mess me up in the long term?')
                            ->placeholder('Explain the long-term consequences of this pattern...')
                            ->rows(3),
                    ]),

                Section::make('Solution Design')
                    ->description('Now let\'s figure out what to do instead')
                    ->schema([
                        Textarea::make('what_should_do_instead')
                            ->label('What should I be doing instead?')
                            ->placeholder('Describe the ideal behavior or approach...')
                            ->rows(4),
                        
                        Textarea::make('how_would_benefit')
                            ->label('How would that benefit me?')
                            ->placeholder('Explain the positive outcomes and incentives...')
                            ->rows(4),
                    ]),

                Section::make('Problem Nature & Emotional Impact')
                    ->description('Understanding the emotional dimension')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('problem_nature')
                                    ->label('What is the nature of the problem?')
                                    ->options([
                                        'emotional' => 'Emotional',
                                        'financial' => 'Financial', 
                                        'house_abuse' => 'House Abuse',
                                        'spouse' => 'Spouse',
                                        'work' => 'Work',
                                        'health' => 'Health',
                                        'relationships' => 'Relationships',
                                        'other' => 'Other',
                                    ])
                                    ->placeholder('Select the primary nature of this problem'),
                                
                                Select::make('emotional_impact_percentage')
                                    ->label('What is the emotional impact of the problem in %?')
                                    ->options(array_combine(
                                        range(0, 100, 10),
                                        array_map(fn($i) => $i . '%', range(0, 100, 10))
                                    ))
                                    ->placeholder('How much does this emotionally affect you?'),
                            ]),
                        
                        Textarea::make('emotional_strategy')
                            ->label('Do I have a strategy to deal with the emotional disturbances it causes?')
                            ->placeholder('Describe your coping mechanisms (meditation, grieving, problem solving, cognitive exercises, action lists, etc.)...')
                            ->rows(4),
                    ]),

                Section::make('Power & Control Analysis')
                    ->description('What can you actually control and change?')
                    ->schema([
                        Toggle::make('have_power_to_solve')
                            ->label('Do I have the power to solve this problem?')
                            ->helperText('Yes = it depends on me, No = external source of authority'),
                        
                        Textarea::make('what_have_power_to_change')
                            ->label('What do I have the power to do and change?')
                            ->placeholder('List the specific things within your control...')
                            ->rows(4),
                        
                        Textarea::make('how_get_out_long_term')
                            ->label('How will that get me out of this in the long term?')
                            ->placeholder('Explain your long-term strategy and expected outcomes...')
                            ->rows(4),
                    ]),

                Section::make('Session Management')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'in_progress' => 'In Progress',
                                        'completed' => 'Completed',
                                    ])
                                    ->default('draft')
                                    ->required(),
                                
                                Toggle::make('needs_follow_up')
                                    ->label('Needs Follow-up'),
                                
                                TagsInput::make('tags')
                                    ->placeholder('Add tags to categorize this session'),
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
                    ->limit(40),
                
                BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'in_progress', 
                        'success' => 'completed',
                    ]),
                
                BadgeColumn::make('problem_nature')
                    ->label('Nature')
                    ->colors([
                        'danger' => ['emotional', 'house_abuse'],
                        'warning' => ['financial', 'spouse'],
                        'primary' => ['work', 'health'],
                        'success' => ['relationships'],
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state)) : 'Not Set'),
                
                TextColumn::make('emotional_impact_percentage')
                    ->label('Emotional Impact')
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : 'Not Set')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state && $state < 40,
                        'primary' => fn ($state) => $state && $state >= 40 && $state < 60,
                        'warning' => fn ($state) => $state && $state >= 60 && $state < 80,
                        'danger' => fn ($state) => $state && $state >= 80,
                    ]),
                
                IconColumn::make('is_daily_pattern')
                    ->label('Daily Pattern')
                    ->boolean(),
                
                IconColumn::make('have_power_to_solve')
                    ->label('Have Power')
                    ->boolean(),
                
                IconColumn::make('needs_follow_up')
                    ->label('Follow-up')
                    ->boolean(),
                
                TextColumn::make('session_date')
                    ->date()
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                    ]),
                
                SelectFilter::make('problem_nature')
                    ->options([
                        'emotional' => 'Emotional',
                        'financial' => 'Financial',
                        'house_abuse' => 'House Abuse',
                        'spouse' => 'Spouse',
                        'work' => 'Work',
                        'health' => 'Health',
                        'relationships' => 'Relationships',
                        'other' => 'Other',
                    ]),
                
                SelectFilter::make('needs_follow_up')
                    ->options([
                        1 => 'Needs Follow-up',
                        0 => 'No Follow-up Needed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('session_date', 'desc');
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
            'index' => Pages\ListProblemSolvingSessions::route('/'),
            'create' => Pages\CreateProblemSolvingSession::route('/create'),
            'edit' => Pages\EditProblemSolvingSession::route('/{record}/edit'),
        ];
    }
}
