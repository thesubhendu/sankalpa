<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_energy_level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'current_energy_level' => 'string',
        ];
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function weeklyGoals(): HasMany
    {
        return $this->hasMany(WeeklyGoal::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function userPoints(): HasOne
    {
        return $this->hasOne(UserPoints::class);
    }

    public function introspectionJournals(): HasMany
    {
        return $this->hasMany(IntrospectionJournal::class);
    }

    // Energy Level Methods
    public function updateEnergyLevel(string $energyLevel): void
    {
        if (in_array($energyLevel, ['low', 'medium', 'high'])) {
            $this->update(['current_energy_level' => $energyLevel]);
        }
    }

    public function getTasksByCurrentEnergy()
    {
        if (!$this->current_energy_level) {
            return $this->tasks()->active()->orderBy('priority', 'desc')->get();
        }

        return $this->tasks()
            ->active()
            ->where('energy_level', $this->current_energy_level)
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    // Point-related methods (updated to use UserPoints)
    public function getTotalPointsAttribute(): int
    {
        return UserPoints::getCurrentPoints($this->id);
    }

    public function getWeeklyPointsAttribute(): int
    {
        return UserPoints::getWeeklyPoints($this->id);
    }

    public function getCurrentWeeklyGoal(): ?WeeklyGoal
    {
        return WeeklyGoal::getCurrentWeek($this->id);
    }

    public function getActiveGoalAttribute(): ?Goal
    {
        return $this->goals()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    public function getTodaysPendingTaskAttribute(): ?Task
    {
        return $this->tasks()
            ->pending()
            ->dueToday()
            ->first();
    }

    public function getTodaysTasksByEnergy(string $energyLevel = null)
    {
        $query = $this->tasks()
            ->active()
            ->whereDate('due_date', today());

        if ($energyLevel) {
            $query->where('energy_level', $energyLevel);
        } elseif ($this->current_energy_level) {
            $query->where('energy_level', $this->current_energy_level);
        }

        return $query->orderBy('priority', 'desc')->get();
    }

    public function getActiveTasksAttribute()
    {
        return $this->tasks()
            ->active()
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getWeeklyTaskStatsAttribute(): array
    {
        $thisWeek = $this->tasks()
            ->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);

        return [
            'completed' => $thisWeek->clone()->done()->count(),
            'total' => $thisWeek->count(),
            'points_earned' => $thisWeek->clone()->done()->sum('points'),
        ];
    }

    public function getEnergyTaskBreakdown(): array
    {
        return [
            'high' => $this->tasks()->active()->where('energy_level', 'high')->count(),
            'medium' => $this->tasks()->active()->where('energy_level', 'medium')->count(),
            'low' => $this->tasks()->active()->where('energy_level', 'low')->count(),
        ];
    }
}
