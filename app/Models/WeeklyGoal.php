<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class WeeklyGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'raw_input',
        'parsed_summary',
        'week_start_date',
        'week_end_date',
        'user_id',
        'goal_id',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function parseInput(string $rawInput): void
    {
        // Simple parsing logic - extract tasks from input
        $lines = explode("\n", trim($rawInput));
        $tasks = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Parse format: "Task: points, energy_level (duration), goal_id, priority, due_date"
            // Example: "Build Introspection Journal: 20, medium (2h), 1, high, 2025-06-20"
            if (preg_match('/^(.+?):\s*(\d+),\s*(\w+)\s*\(([^)]+)\),?\s*(.*)$/', $line, $matches)) {
                $taskTitle = trim($matches[1]);
                $points = intval($matches[2]);
                $energyLevel = strtolower(trim($matches[3]));
                $duration = $this->parseDuration(trim($matches[4]));
                $remaining = trim($matches[5]);
                
                // Parse remaining parts (goal_id, priority, due_date)
                $parts = array_map('trim', explode(',', $remaining));
                $goalId = isset($parts[0]) && is_numeric($parts[0]) ? intval($parts[0]) : null;
                $priority = $this->parsePriority(isset($parts[1]) ? $parts[1] : 'medium');
                $dueDate = isset($parts[2]) ? $parts[2] : null;
                
                $tasks[] = [
                    'title' => $taskTitle,
                    'points' => $points,
                    'energy_level' => in_array($energyLevel, ['low', 'medium', 'high']) ? $energyLevel : 'medium',
                    'estimated_duration' => $duration,
                    'goal_id' => $goalId,
                    'priority' => $priority,
                    'due_date' => $dueDate ? Carbon::parse($dueDate) : $this->week_end_date,
                ];
            }
        }

        // Create tasks
        foreach ($tasks as $taskData) {
            $this->tasks()->create(array_merge($taskData, [
                'user_id' => $this->user_id,
                'status' => 'pending',
                'weekly_goal_id' => $this->id,
            ]));
        }

        // Generate summary
        $this->generateSummary();
    }

    private function parseDuration(string $duration): ?int
    {
        // Convert "2h", "30m", "1.5h" to minutes
        if (preg_match('/(\d+(?:\.\d+)?)h/', $duration, $matches)) {
            return intval(floatval($matches[1]) * 60);
        } elseif (preg_match('/(\d+)m/', $duration, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }

    private function parsePriority(string $priority): int
    {
        return match(strtolower($priority)) {
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            default => 2,
        };
    }

    public function generateSummary(): string
    {
        $taskCount = $this->tasks()->count();
        $totalPoints = $this->tasks()->sum('points');
        $energyBreakdown = $this->tasks()
            ->selectRaw('energy_level, count(*) as count')
            ->groupBy('energy_level')
            ->pluck('count', 'energy_level')
            ->toArray();
        
        $summary = "Weekly Goal: {$this->title}\n\n";
        $summary .= "📋 Total Tasks: {$taskCount}\n";
        $summary .= "🎯 Total Points: {$totalPoints}\n\n";
        $summary .= "⚡ Energy Distribution:\n";
        
        foreach (['high' => '🔥', 'medium' => '⚡', 'low' => '🌱'] as $level => $emoji) {
            $count = $energyBreakdown[$level] ?? 0;
            $summary .= "  {$emoji} {$level}: {$count} tasks\n";
        }
        
        $this->update(['parsed_summary' => $summary]);
        return $summary;
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->tasks()->sum('points');
    }

    public function getCompletionPercentageAttribute(): float
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) return 0;
        
        $completedTasks = $this->tasks()->where('status', 'done')->count();
        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    public static function getCurrentWeek(int $userId): ?self
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        return static::where('user_id', $userId)
            ->where('week_start_date', '<=', $startOfWeek)
            ->where('week_end_date', '>=', $endOfWeek)
            ->first();
    }
}
