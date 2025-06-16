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
        // Enhanced parsing logic - supports multiple simple formats
        $lines = explode("\n", trim($rawInput));
        $tasks = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) continue;
            
            $taskData = $this->parseTaskLine($line);
            if ($taskData) {
                $tasks[] = $taskData;
            }
        }

        // Create tasks
        foreach ($tasks as $taskData) {
            // Remove null goal_id to avoid constraint violation
            if (isset($taskData['goal_id']) && $taskData['goal_id'] === null) {
                unset($taskData['goal_id']);
            }
            
            $this->tasks()->create(array_merge($taskData, [
                'user_id' => $this->user_id,
                'status' => 'pending',
                'weekly_goal_id' => $this->id,
            ]));
        }

        // Generate summary
        $this->generateSummary();
    }

    private function parseTaskLine(string $line): ?array
    {
        // Format 1: Simple format - "Task Name (optional details)"
        // Examples: 
        // - "Build Introspection Journal"
        // - "Learn Java Spring (2h)"
        // - "Upload Videos (30m, high priority)"
        
        // Format 2: Points format - "Task Name - 20 points"
        // Format 3: Full format (legacy) - "Task: 20, medium (2h), 1, high, 2025-06-20"
        
        // Try Format 3 (Legacy) first for backward compatibility
        if (preg_match('/^(.+?):\s*(\d+),\s*(\w+)\s*\(([^)]+)\),?\s*(.*)$/', $line, $matches)) {
            return $this->parseLegacyFormat($matches);
        }
        
        // Try Format 2: "Task Name - 20 points"
        if (preg_match('/^(.+?)\s*-\s*(\d+)\s*points?\s*(.*)$/', $line, $matches)) {
            return $this->parsePointsFormat($matches);
        }
        
        // Format 1: Simple format with optional details in parentheses
        if (preg_match('/^(.+?)(?:\s*\(([^)]+)\))?$/', $line, $matches)) {
            return $this->parseSimpleFormat($matches);
        }
        
        return null;
    }

    private function parseSimpleFormat(array $matches): array
    {
        $taskTitle = trim($matches[1]);
        $details = isset($matches[2]) ? trim($matches[2]) : '';
        
        // Default values
        $points = 5; // Default points
        $energyLevel = 'medium';
        $priority = 2; // medium
        $duration = null;
        $goalId = null;
        $dueDate = null;
        
        // Parse details if provided
        if (!empty($details)) {
            // Look for duration (1h, 30m, 2.5h)
            if (preg_match('/(\d+(?:\.\d+)?[hm])/', $details, $durationMatch)) {
                $duration = $this->parseDuration($durationMatch[1]);
                // Auto-assign points based on duration
                if ($duration) {
                    $points = max(5, min(50, intval($duration / 15))); // 1 point per 15 minutes, min 5, max 50
                }
            }
            
            // Look for priority keywords
            if (stripos($details, 'high priority') !== false || stripos($details, 'important') !== false) {
                $priority = 3;
            } elseif (stripos($details, 'low priority') !== false || stripos($details, 'easy') !== false) {
                $priority = 1;
            }
            
            // Look for energy keywords
            if (stripos($details, 'high energy') !== false || stripos($details, 'focus') !== false) {
                $energyLevel = 'high';
            } elseif (stripos($details, 'low energy') !== false || stripos($details, 'quick') !== false) {
                $energyLevel = 'low';
            }
            
            // Look for specific date (Mon, Tue, Wed, etc. or MM-DD)
            if (preg_match('/\b(mon|tue|wed|thu|fri|sat|sun)\b/i', $details, $dayMatch)) {
                $dueDate = $this->getDateFromDayName($dayMatch[1]);
            } elseif (preg_match('/(\d{1,2}-\d{1,2})/', $details, $dateMatch)) {
                $dueDate = Carbon::parse('2025-' . $dateMatch[1]);
            }
        }
        
        $taskData = [
            'title' => $taskTitle,
            'points' => $points,
            'energy_level' => $energyLevel,
            'estimated_duration' => $duration,
            'priority' => $priority,
            'due_date' => $dueDate ?: $this->week_end_date,
        ];
        
        // Only add goal_id if it's not null
        if ($goalId !== null) {
            $taskData['goal_id'] = $goalId;
        }
        
        return $taskData;
    }

    private function parsePointsFormat(array $matches): array
    {
        $taskTitle = trim($matches[1]);
        $points = intval($matches[2]);
        $details = isset($matches[3]) ? trim($matches[3]) : '';
        
        // Use simple format parser for the rest
        $taskData = $this->parseSimpleFormat([$taskTitle . ' (' . $details . ')', $details]);
        $taskData['points'] = $points; // Override with specified points
        
        return $taskData;
    }

    private function parseLegacyFormat(array $matches): array
    {
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
        
        $taskData = [
            'title' => $taskTitle,
            'points' => $points,
            'energy_level' => in_array($energyLevel, ['low', 'medium', 'high']) ? $energyLevel : 'medium',
            'estimated_duration' => $duration,
            'priority' => $priority,
            'due_date' => $dueDate ? Carbon::parse($dueDate) : $this->week_end_date,
        ];
        
        // Only add goal_id if it's not null
        if ($goalId !== null) {
            $taskData['goal_id'] = $goalId;
        }
        
        return $taskData;
    }

    private function getDateFromDayName(string $dayName): Carbon
    {
        $dayName = strtolower($dayName);
        $daysMap = [
            'mon' => 'monday',
            'tue' => 'tuesday', 
            'wed' => 'wednesday',
            'thu' => 'thursday',
            'fri' => 'friday',
            'sat' => 'saturday',
            'sun' => 'sunday'
        ];
        
        $fullDayName = $daysMap[$dayName] ?? $dayName;
        
        // Get the date of that day in current week
        $date = Carbon::now()->startOfWeek();
        while ($date->format('l') !== ucfirst($fullDayName)) {
            $date->addDay();
            if ($date->dayOfWeek === 0 && $fullDayName !== 'sunday') {
                break; // Prevent infinite loop
            }
        }
        
        return $date;
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

    public function getTasksSummaryAttribute(): string
    {
        $done = $this->tasks->where('status', 'done')->count();
        $inProgress = $this->tasks->where('status', 'in_progress')->count();
        $pending = $this->tasks->where('status', 'pending')->count();
        return "✅ {$done} | 🔄 {$inProgress} | ⏳ {$pending}";
    }

    public function getPointsProgressAttribute(): string
    {
        $earned = $this->tasks->where('status', 'done')->sum('points');
        return "{$earned}/{$this->total_points}";
    }
}
