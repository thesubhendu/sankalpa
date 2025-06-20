<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ProblemSolvingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'problem_description',
        'what_doing_wrong',
        'trigger',
        'is_daily_pattern',
        'what_to_change_trigger',
        'what_do_when_doing_wrong',
        'long_term_impact',
        'what_should_do_instead',
        'how_would_benefit',
        'problem_nature',
        'emotional_impact_percentage',
        'emotional_strategy',
        'have_power_to_solve',
        'what_have_power_to_change',
        'how_get_out_long_term',
        'status',
        'tags',
        'needs_follow_up',
        'session_date',
        'user_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'session_date' => 'date',
        'is_daily_pattern' => 'boolean',
        'have_power_to_solve' => 'boolean',
        'needs_follow_up' => 'boolean',
        'emotional_impact_percentage' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeNeedsFollowUp(Builder $query): Builder
    {
        return $query->where('needs_follow_up', true);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('session_date', '>=', Carbon::now()->subDays($days));
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('session_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('session_date', Carbon::now()->month)
                    ->whereYear('session_date', Carbon::now()->year);
    }

    public function scopeByProblemNature(Builder $query, string $nature): Builder
    {
        return $query->where('problem_nature', $nature);
    }

    // Accessors & Mutators
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            default => ucfirst($this->status)
        };
    }

    public function getProblemNatureDisplayAttribute(): string
    {
        return match($this->problem_nature) {
            'house_abuse' => 'House Abuse',
            'financial' => 'Financial',
            'emotional' => 'Emotional',
            'spouse' => 'Spouse',
            'work' => 'Work',
            'health' => 'Health',
            'relationships' => 'Relationships',
            'other' => 'Other',
            default => 'Not Set'
        };
    }

    // Helper Methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getEmotionalImpactColor(): string
    {
        if (!$this->emotional_impact_percentage) {
            return 'gray';
        }

        return match(true) {
            $this->emotional_impact_percentage >= 80 => 'danger',
            $this->emotional_impact_percentage >= 60 => 'warning',
            $this->emotional_impact_percentage >= 40 => 'primary',
            default => 'success'
        };
    }

    public function getCompletionPercentage(): int
    {
        $totalFields = [
            'what_doing_wrong',
            'trigger',
            'is_daily_pattern',
            'what_should_do_instead',
            'how_would_benefit',
            'problem_nature',
            'emotional_impact_percentage',
            'have_power_to_solve',
            'what_have_power_to_change',
        ];

        $filledFields = 0;
        foreach ($totalFields as $field) {
            if (!empty($this->$field)) {
                $filledFields++;
            }
        }

        return (int) (($filledFields / count($totalFields)) * 100);
    }
}
