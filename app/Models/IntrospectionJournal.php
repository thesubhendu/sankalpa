<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class IntrospectionJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'mood',
        'trigger',
        'what_happened',
        'feelings',
        'trigger_reason',
        'tags',
        'intensity_level',
        'is_important',
        'needs_review',
        'entry_date',
        'user_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'entry_date' => 'date',
        'is_important' => 'boolean',
        'needs_review' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeDataDrops(Builder $query): Builder
    {
        return $query->where('type', 'data_drop');
    }

    public function scopeLearnings(Builder $query): Builder
    {
        return $query->where('type', 'learning');
    }

    public function scopeRules(Builder $query): Builder
    {
        return $query->where('type', 'rule');
    }

    public function scopePurpose(Builder $query): Builder
    {
        return $query->where('type', 'purpose');
    }

    public function scopeImportant(Builder $query): Builder
    {
        return $query->where('is_important', true);
    }

    public function scopeNeedsReview(Builder $query): Builder
    {
        return $query->where('needs_review', true);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('entry_date', '>=', Carbon::now()->subDays($days));
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('entry_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('entry_date', Carbon::now()->month)
                    ->whereYear('entry_date', Carbon::now()->year);
    }

    // Accessors & Mutators
    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'data_drop' => 'Data Drop',
            'learning' => 'Learning',
            'rule' => 'Rule',
            'purpose' => 'Purpose',
            default => ucfirst($this->type)
        };
    }

    public function getMoodDisplayAttribute(): string
    {
        return match($this->mood) {
            'very_low' => 'Very Low',
            'low' => 'Low',
            'neutral' => 'Neutral',
            'good' => 'Good',
            'very_good' => 'Very Good',
            default => 'Not Set'
        };
    }

    // Helper Methods
    public function isDataDrop(): bool
    {
        return $this->type === 'data_drop';
    }

    public function isLearning(): bool
    {
        return $this->type === 'learning';
    }

    public function isRule(): bool
    {
        return $this->type === 'rule';
    }

    public function isPurpose(): bool
    {
        return $this->type === 'purpose';
    }

    public function getIntensityColor(): string
    {
        return match(true) {
            $this->intensity_level >= 8 => 'danger',
            $this->intensity_level >= 6 => 'warning',
            $this->intensity_level >= 4 => 'primary',
            default => 'success'
        };
    }

    public function getMoodColor(): string
    {
        return match($this->mood) {
            'very_low' => 'danger',
            'low' => 'warning',
            'neutral' => 'gray',
            'good' => 'success',
            'very_good' => 'success',
            default => 'gray'
        };
    }
}
