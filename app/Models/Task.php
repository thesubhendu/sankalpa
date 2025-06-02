<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'milestone_id',
        'goal_id',
        'title',
        'description',
        'due_date',
        'status',
        'points',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'status' => 'string',
        'points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function completion(): HasOne
    {
        return $this->hasOne(TaskCompletion::class);
    }

    public function markCompleted(?string $notes = null): void
    {
        $this->update(['status' => 'done']);
        
        TaskCompletion::create([
            'task_id' => $this->id,
            'completed_at' => now(),
            'notes' => $notes,
        ]);

        // Create point transaction
        PointTransaction::create([
            'user_id' => $this->user_id,
            'source' => 'task_completion',
            'amount' => $this->points,
            'description' => "Completed task: {$this->title}",
        ]);
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

    public function isOverdue(): bool
    {
        return $this->due_date < now() && $this->status !== 'done';
    }
} 