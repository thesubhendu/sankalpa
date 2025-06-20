<?php

namespace App\Filament\Widgets;

use App\Models\WeeklyGoal;
use App\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeeklyReviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.weekly-review-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = null;
    
    protected static bool $isDiscovered = false;

    public function getViewData(): array
    {
        $user = Auth::user();
        $pastWeeks = $this->getPastWeeksData();
        $monthlyStats = $this->getMonthlyStats();
        $trends = $this->getTrends();
        
        return [
            'user' => $user,
            'pastWeeks' => $pastWeeks,
            'monthlyStats' => $monthlyStats,
            'trends' => $trends,
        ];
    }

    private function getPastWeeksData(): Collection
    {
        $user = Auth::user();
        return WeeklyGoal::where('user_id', $user->id)
            ->where('week_end_date', '<', Carbon::now()->startOfWeek())
            ->with(['tasks', 'goal'])
            ->orderBy('week_start_date', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($goal) {
                $totalTasks = $goal->tasks->count();
                $completedTasks = $goal->tasks->where('status', 'done')->count();
                $totalPoints = $goal->total_points;
                $earnedPoints = $goal->tasks->where('status', 'done')->sum('points');
                
                return [
                    'goal' => $goal,
                    'total_tasks' => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0,
                    'total_points' => $totalPoints,
                    'earned_points' => $earnedPoints,
                    'point_efficiency' => $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0,
                    'week_label' => $goal->week_start_date->format('M j') . ' - ' . $goal->week_end_date->format('M j'),
                    'days_ago' => $goal->week_end_date->diffInDays(Carbon::now()),
                ];
            });
    }

    private function getMonthlyStats(): array
    {
        $user = Auth::user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $monthlyGoals = WeeklyGoal::where('user_id', $user->id)
            ->whereBetween('week_start_date', [$startOfMonth, $endOfMonth])
            ->with('tasks')
            ->get();

        $totalGoals = $monthlyGoals->count();
        $totalTasks = $monthlyGoals->sum(fn($g) => $g->tasks->count());
        $completedTasks = $monthlyGoals->sum(fn($g) => $g->tasks->where('status', 'done')->count());
        $totalPoints = $monthlyGoals->sum('total_points');
        $earnedPoints = $monthlyGoals->sum(fn($g) => $g->tasks->where('status', 'done')->sum('points'));

        return [
            'total_goals' => $totalGoals,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'point_efficiency' => $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0,
        ];
    }

    private function getTrends(): array
    {
        $user = Auth::user();
        $weeks = [];
        
        // Get last 8 weeks for trend analysis
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            
            $weeklyGoals = WeeklyGoal::where('user_id', $user->id)
                ->where('week_start_date', '<=', $weekStart)
                ->where('week_end_date', '>=', $weekEnd)
                ->with('tasks')
                ->get();

            $totalTasks = $weeklyGoals->sum(fn($g) => $g->tasks->count());
            $completedTasks = $weeklyGoals->sum(fn($g) => $g->tasks->where('status', 'done')->count());
            $earnedPoints = $weeklyGoals->sum(fn($g) => $g->tasks->where('status', 'done')->sum('points'));

            $weeks[] = [
                'week_label' => $weekStart->format('M j'),
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0,
                'earned_points' => $earnedPoints,
                'is_current' => $weekStart->isCurrentWeek(),
            ];
        }

        return [
            'weekly_data' => $weeks,
            'avg_completion_rate' => collect($weeks)->average('completion_rate'),
            'avg_points_per_week' => collect($weeks)->average('earned_points'),
            'trend_direction' => $this->calculateTrend($weeks),
        ];
    }

    private function calculateTrend(array $weeks): string
    {
        if (count($weeks) < 2) return 'stable';
        
        $recent = array_slice($weeks, -3); // Last 3 weeks
        $earlier = array_slice($weeks, 0, 3); // First 3 weeks
        
        $recentAvg = collect($recent)->average('completion_rate');
        $earlierAvg = collect($earlier)->average('completion_rate');
        
        $difference = $recentAvg - $earlierAvg;
        
        if ($difference > 10) return 'improving';
        if ($difference < -10) return 'declining';
        return 'stable';
    }

    public function getHeading(): string
    {
        return 'Weekly Review & Trends';
    }
} 