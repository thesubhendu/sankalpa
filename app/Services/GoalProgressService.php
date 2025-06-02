<?php

namespace App\Services;

use App\Models\Goal;

class GoalProgressService
{
    public function getCompletionPercentage(Goal $goal): float
    {
        $totalTasks = $goal->tasks()->count();
        
        if ($totalTasks === 0) {
            return 0;
        }
        
        $completedTasks = $goal->tasks()->where('status', 'done')->count();
        
        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    public function getTotalPoints(Goal $goal): int
    {
        return $goal->tasks()->where('status', 'done')->sum('points');
    }

    public function getRemainingTasks(Goal $goal): int
    {
        return $goal->tasks()->whereIn('status', ['pending', 'in_progress'])->count();
    }

    public function getGoalStats(Goal $goal): array
    {
        return [
            'completion_percentage' => $this->getCompletionPercentage($goal),
            'total_points' => $this->getTotalPoints($goal),
            'remaining_tasks' => $this->getRemainingTasks($goal),
            'total_tasks' => $goal->tasks()->count(),
            'completed_tasks' => $goal->tasks()->where('status', 'done')->count(),
        ];
    }
} 