<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Task;
use App\Models\Goal;
use App\Services\GoalProgressService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskProgressWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $goalProgressService = new GoalProgressService();
        
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

        // Active goal progress
        $activeGoal = $user->activeGoal;
        if ($activeGoal) {
            $goalStats = $goalProgressService->getGoalStats($activeGoal);
            $goalProgressStat = Stat::make('Current Goal Progress', $goalStats['completion_percentage'] . '%')
                ->description($activeGoal->title)
                ->descriptionIcon('heroicon-m-flag')
                ->color($goalStats['completion_percentage'] > 50 ? 'success' : 'warning');
        } else {
            $goalProgressStat = Stat::make('Current Goal', 'No active goal')
                ->description('Create a goal to get started!')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('gray');
        }

        return [
            $todaysTaskStat,
            $weeklyPointsStat,
            $weeklyTasksStat,
            $goalProgressStat,
        ];
    }
}
