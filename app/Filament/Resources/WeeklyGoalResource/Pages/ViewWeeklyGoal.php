<?php

namespace App\Filament\Resources\WeeklyGoalResource\Pages;

use App\Filament\Resources\WeeklyGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;

class ViewWeeklyGoal extends ViewRecord
{
    protected static string $resource = WeeklyGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Weekly Goal Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->size('lg')
                                    ->weight('bold'),
                                
                                TextEntry::make('goal.title')
                                    ->label('Long-term Goal')
                                    ->placeholder('No goal linked'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('week_start_date')
                                    ->label('Week Start')
                                    ->date(),
                                
                                TextEntry::make('week_end_date')
                                    ->label('Week End')
                                    ->date(),
                                
                                TextEntry::make('total_points')
                                    ->label('Total Points')
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ]),

                Section::make('Progress Overview')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('tasks_count')
                                    ->label('Total Tasks')
                                    ->getStateUsing(fn ($record) => $record->tasks()->count())
                                    ->badge()
                                    ->color('primary'),
                                
                                TextEntry::make('completed_tasks')
                                    ->label('Completed')
                                    ->getStateUsing(fn ($record) => $record->tasks()->where('status', 'done')->count())
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('in_progress_tasks')
                                    ->label('In Progress')
                                    ->getStateUsing(fn ($record) => $record->tasks()->where('status', 'in_progress')->count())
                                    ->badge()
                                    ->color('warning'),
                                
                                TextEntry::make('completion_percentage')
                                    ->label('Progress')
                                    ->formatStateUsing(fn ($record) => $record->completion_percentage . '%')
                                    ->badge()
                                    ->color(fn ($record) => match (true) {
                                        $record->completion_percentage >= 80 => 'success',
                                        $record->completion_percentage >= 50 => 'warning',
                                        default => 'gray',
                                    }),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('points_earned')
                                    ->label('Points Earned')
                                    ->getStateUsing(fn ($record) => $record->tasks()->where('status', 'done')->sum('points'))
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('total_points')
                                    ->label('Total Points')
                                    ->badge()
                                    ->color('info'),
                                
                                TextEntry::make('point_efficiency')
                                    ->label('Point Efficiency')
                                    ->getStateUsing(function ($record) {
                                        $total = $record->total_points;
                                        $earned = $record->tasks()->where('status', 'done')->sum('points');
                                        return $total > 0 ? round(($earned / $total) * 100) . '%' : '0%';
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $total = $record->total_points;
                                        $earned = $record->tasks()->where('status', 'done')->sum('points');
                                        $efficiency = $total > 0 ? ($earned / $total) * 100 : 0;
                                        return match (true) {
                                            $efficiency >= 80 => 'success',
                                            $efficiency >= 60 => 'warning',
                                            default => 'gray',
                                        };
                                    }),
                            ]),
                    ]),

                Section::make('Task Breakdown')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('energy_breakdown')
                                    ->label('Energy Levels')
                                    ->getStateUsing(function ($record) {
                                        $high = $record->tasks()->where('energy_level', 'high')->count();
                                        $medium = $record->tasks()->where('energy_level', 'medium')->count();
                                        $low = $record->tasks()->where('energy_level', 'low')->count();
                                        return "🔥 {$high} • ⚡ {$medium} • 🌱 {$low}";
                                    }),
                                
                                TextEntry::make('priority_breakdown')
                                    ->label('Priorities')
                                    ->getStateUsing(function ($record) {
                                        $high = $record->tasks()->where('priority', 3)->count();
                                        $medium = $record->tasks()->where('priority', 2)->count();
                                        $low = $record->tasks()->where('priority', 1)->count();
                                        return "🔴 {$high} • 🟡 {$medium} • 🟢 {$low}";
                                    }),
                                
                                TextEntry::make('due_today')
                                    ->label('Due Today')
                                    ->getStateUsing(fn ($record) => $record->tasks()->whereDate('due_date', today())->count())
                                    ->badge()
                                    ->color(function ($record) {
                                        $count = $record->tasks()->whereDate('due_date', today())->count();
                                        return $count > 0 ? 'warning' : 'gray';
                                    }),
                            ]),
                    ]),

                Section::make('Summary')
                    ->schema([
                        TextEntry::make('parsed_summary')
                            ->label('')
                            ->formatStateUsing(fn ($state) => nl2br(e($state)))
                            ->html()
                            ->placeholder('No summary generated'),
                    ])
                    ->visible(fn ($record) => $record->parsed_summary),

                Section::make('Raw Input')
                    ->schema([
                        TextEntry::make('raw_input')
                            ->label('')
                            ->formatStateUsing(fn ($state) => nl2br(e($state)))
                            ->html(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
} 