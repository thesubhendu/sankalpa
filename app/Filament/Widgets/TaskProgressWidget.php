<?php

namespace App\Filament\Widgets;

use App\Services\GoalProgressService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskProgressWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        
        // Today's pending task
        $todaysTask = $user->todaysPendingTask;
        $todaysTaskStat = Stat::make('Today\'s Task', $todaysTask ? $todaysTask->title : 'No tasks due today')
            ->description($todaysTask ? 'Due: ' . $todaysTask->due_date->format('H:i') : 'All caught up!')
            ->descriptionIcon($todaysTask ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
            ->color($todaysTask ? 'warning' : 'success');

        // Weekly points
        $weeklyPoints = $user->weeklyPoints;
        $weeklyPointsStat = Stat::make('Weekly Points', $weeklyPoints)
            ->description('Points earned this week')
            ->descriptionIcon('heroicon-m-star')
            ->color('primary');

        // Weekly task stats
        $weeklyStats = $user->weeklyTaskStats;
        $weeklyTasksStat = Stat::make('This Week', $weeklyStats['completed'] . ' / ' . $weeklyStats['total'] . ' Tasks')
            ->description('Tasks completed this week')
            ->descriptionIcon('heroicon-m-check-badge')
            ->color($weeklyStats['completed'] > 0 ? 'success' : 'gray');


        return [
            $todaysTaskStat,
            $weeklyPointsStat,
            $weeklyTasksStat,
        ];
    }
}
