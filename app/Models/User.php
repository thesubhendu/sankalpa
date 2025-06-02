<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        ];
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->pointTransactions()->sum('amount');
    }

    public function getWeeklyPointsAttribute(): int
    {
        return $this->pointTransactions()
            ->thisWeek()
            ->sum('amount');
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
        ];
    }
}
