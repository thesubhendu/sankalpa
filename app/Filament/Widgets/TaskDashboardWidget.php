<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class TaskDashboardWidget extends Widget
{
    protected static string $view = 'filament.widgets.task-dashboard-widget';

    protected int | string | array $columnSpan = 'full';



    public function getViewData(): array
    {
        $user = Auth::user();
        $currentTask = Task::where('user_id', $user->id)
            ->whereNotNull('started_at')
            ->whereNull('completed_at')
            ->first();

        $userPoints = $user->userPoints;
        $totalPoints = $userPoints ? $userPoints->total_points : 0;
        $weeklyPoints = $userPoints ? $userPoints->weekly_points : 0;

        // Get recommended task based on user's current energy level
        $recommendedTask = null;
        if ($user->current_energy_level) {
            $recommendedTask = Task::where('user_id', $user->id)
                ->where('energy_level', $user->current_energy_level)
                ->whereNull('completed_at')
                ->whereNull('started_at')
                ->orderBy('priority', 'desc')
                ->orderBy('due_date', 'asc')
                ->first();
        }

        return [
            'user' => $user,
            'currentTask' => $currentTask,
            'totalPoints' => $totalPoints,
            'weeklyPoints' => $weeklyPoints,
            'recommendedTask' => $recommendedTask,
            'currentEnergyLevel' => $user->current_energy_level,
        ];
    }

    public function setEnergyLevel($level)
    {
        $user = Auth::user();
        $user->current_energy_level = $level;
        $user->save();
        
        $this->dispatch('energyLevelUpdated');
    }

    public function startTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->started_at = now();
        $task->save();
        
        $this->dispatch('taskStarted');
    }

    public function completeTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->completed_at = now();
        $task->save();
        
        // Add points
        $user = Auth::user();
        $userPoints = $user->userPoints ?? $user->userPoints()->create(['total_points' => 0, 'weekly_points' => 0]);
        $userPoints->total_points += $task->points;
        $userPoints->weekly_points += $task->points;
        $userPoints->save();
        
        $this->dispatch('taskCompleted');
    }
} 