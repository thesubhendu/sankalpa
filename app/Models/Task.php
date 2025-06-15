<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'weekly_goal_id',
        'title',
        'description',
        'due_date',
        'status',
        'points',
        'energy_level',
        'estimated_duration',
        'motivation_note',
        'priority',
        'started_at',
        'completed_at',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'status' => 'string',
        'points' => 'integer',
        'energy_level' => 'string',
        'estimated_duration' => 'integer',
        'priority' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function weeklyGoal(): BelongsTo
    {
        return $this->belongsTo(WeeklyGoal::class);
    }

    public function completion(): HasOne
    {
        return $this->hasOne(TaskCompletion::class);
    }

    public function start(): void
    {
        $this->update([
            'started_at' => now(),
            'status' => 'in_progress'
        ]);
    }

    public function complete(?string $notes = null): void
    {
        $this->update([
            'completed_at' => now(),
            'status' => 'done'
        ]);
        
        // Award points using UserPoints
        $userPoints = UserPoints::firstOrCreate(
            ['user_id' => $this->user_id],
            ['total_points' => 0, 'weekly_points' => 0]
        );
        $userPoints->addPoints($this->points, 'task_completion');
        
        // Create completion record if needed
        if ($notes) {
            TaskCompletion::updateOrCreate(
                ['task_id' => $this->id],
                [
                    'completed_at' => $this->completed_at,
                    'notes' => $notes,
                ]
            );
        }
    }

    public function markCompleted(?string $notes = null): void
    {
        $this->complete($notes);
    }

    public function markReopened(): void
    {
        // Remove points when reopening
        if ($this->status === 'done') {
            $userPoints = UserPoints::where('user_id', $this->user_id)->first();
            if ($userPoints) {
                $userPoints->total_points = max(0, $userPoints->total_points - $this->points);
                $userPoints->weekly_points = max(0, $userPoints->weekly_points - $this->points);
                $userPoints->save();
            }
        }
        
        $this->update([
            'status' => 'pending',
            'started_at' => null,
            'completed_at' => null
        ]);
        
        // Remove the completion record
        $this->completion()?->delete();
    }

    public function getActualDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return null;
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->estimated_duration) return '';
        
        $hours = intval($this->estimated_duration / 60);
        $minutes = $this->estimated_duration % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    public function getFormattedActualDurationAttribute(): string
    {
        $duration = $this->actual_duration;
        if (!$duration) return '';
        
        $hours = intval($duration / 60);
        $minutes = $duration % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    public function getPriorityTextAttribute(): string
    {
        return match($this->priority) {
            1 => 'Low',
            2 => 'Medium',
            3 => 'High',
            default => 'Medium',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'gray',
            'in_progress' => 'warning',
            'done' => 'success',
            default => 'gray',
        };
    }

    public function getEnergyLevelColorAttribute(): string
    {
        return match($this->energy_level) {
            'low' => 'success',
            'medium' => 'warning', 
            'high' => 'danger',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeByEnergyLevel(Builder $query, string $energyLevel): Builder
    {
        return $query->where('energy_level', $energyLevel);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeByPriority(Builder $query, int $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', 3);
    }

    public function isOverdue(): bool
    {
        return $this->due_date < now() && $this->status !== 'done';
    }

    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    public function canStart(): bool
    {
        return $this->status === 'pending';
    }

    public function canComplete(): bool
    {
        return in_array($this->status, ['pending', 'in_progress']);
    }
} 