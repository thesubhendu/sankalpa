<?php

namespace App\Filament\Widgets;

use App\Models\WeeklyGoal;
use App\Models\Task;
use App\Models\Goal;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeeklyPlanningWidget extends Widget
{
    protected static string $view = 'filament.widgets.weekly-planning-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getViewData(): array
    {
        $user = Auth::user();
        $currentWeek = $this->getCurrentWeekData();
        $weeklyStats = $this->getWeeklyStats();
        $upcomingTasks = $this->getUpcomingTasks();
        $todayTasks = $this->getTodayTasks();
        
        return [
            'user' => $user,
            'currentWeek' => $currentWeek,
            'weeklyStats' => $weeklyStats,
            'upcomingTasks' => $upcomingTasks,
            'todayTasks' => $todayTasks,
            'weekProgress' => $this->getWeekProgress(),
        ];
    }

    private function getCurrentWeekData(): array
    {
        $user = Auth::user();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        $weeklyGoals = WeeklyGoal::where('user_id', $user->id)
            ->where('week_start_date', '<=', $startOfWeek)
            ->where('week_end_date', '>=', $endOfWeek)
            ->with(['tasks', 'goal'])
            ->get();

        return [
            'goals' => $weeklyGoals,
            'week_start' => $startOfWeek,
            'week_end' => $endOfWeek,
            'total_goals' => $weeklyGoals->count(),
            'total_tasks' => $weeklyGoals->sum(fn($g) => $g->tasks->count()),
            'completed_tasks' => $weeklyGoals->sum(fn($g) => $g->tasks->where('status', 'done')->count()),
            'total_points' => $weeklyGoals->sum('total_points'),
            'earned_points' => $weeklyGoals->sum(fn($g) => $g->tasks->where('status', 'done')->sum('points')),
        ];
    }

    private function getWeeklyStats(): array
    {
        $user = Auth::user();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        $tasks = Task::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        $tasksByStatus = $tasks->groupBy('status');
        $tasksByEnergy = $tasks->groupBy('energy_level');
        $tasksByPriority = $tasks->groupBy('priority');

        return [
            'by_status' => [
                'pending' => $tasksByStatus->get('pending', collect())->count(),
                'in_progress' => $tasksByStatus->get('in_progress', collect())->count(),
                'done' => $tasksByStatus->get('done', collect())->count(),
            ],
            'by_energy' => [
                'low' => $tasksByEnergy->get('low', collect())->count(),
                'medium' => $tasksByEnergy->get('medium', collect())->count(),
                'high' => $tasksByEnergy->get('high', collect())->count(),
            ],
            'by_priority' => [
                'low' => $tasksByPriority->get('1', collect())->count(),
                'medium' => $tasksByPriority->get('2', collect())->count(),
                'high' => $tasksByPriority->get('3', collect())->count(),
            ],
        ];
    }

    private function getUpcomingTasks(): Collection
    {
        $user = Auth::user();
        return Task::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('due_date', '>', Carbon::now())
            ->orderBy('due_date')
            ->orderBy('priority', 'desc')
            ->limit(5)
            ->get();
    }

    private function getTodayTasks(): Collection
    {
        $user = Auth::user();
        return Task::where('user_id', $user->id)
            ->whereDate('due_date', Carbon::today())
            ->orderBy('priority', 'desc')
            ->get();
    }

    private function getWeekProgress(): array
    {
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $progress = [];
        
        foreach ($daysOfWeek as $index => $day) {
            $date = Carbon::now()->startOfWeek()->addDays($index);
            $tasks = Task::where('user_id', Auth::id())
                ->whereDate('due_date', $date)
                ->get();
            
            $progress[] = [
                'day' => $day,
                'date' => $date,
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'done')->count(),
                'is_today' => $date->isToday(),
                'is_past' => $date->isPast() && !$date->isToday(),
            ];
        }
        
        return $progress;
    }

    public function createQuickWeeklyGoal(): void
    {
        $user = Auth::user();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Check if there's already a weekly goal for this week
        $existingGoal = WeeklyGoal::where('user_id', $user->id)
            ->where('week_start_date', '<=', $startOfWeek)
            ->where('week_end_date', '>=', $endOfWeek)
            ->first();
        
        if (!$existingGoal) {
            WeeklyGoal::create([
                'title' => 'Week of ' . $startOfWeek->format('M j, Y'),
                'raw_input' => '',
                'week_start_date' => $startOfWeek,
                'week_end_date' => $endOfWeek,
                'user_id' => $user->id,
            ]);
        }
        
        $this->dispatch('weeklyGoalCreated');
    }
} 