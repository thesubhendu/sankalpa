<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserPoints extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_points',
        'weekly_points',
        'last_reset_date',
    ];

    protected $casts = [
        'last_reset_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getCurrentPoints(int $userId): int
    {
        $userPoints = static::firstOrCreate(
            ['user_id' => $userId],
            ['total_points' => 0, 'weekly_points' => 0]
        );
        
        // Check if we need to reset weekly points
        $userPoints->checkWeeklyReset();
        
        return $userPoints->total_points;
    }

    public static function getWeeklyPoints(int $userId): int
    {
        $userPoints = static::firstOrCreate(
            ['user_id' => $userId],
            ['total_points' => 0, 'weekly_points' => 0]
        );
        
        // Check if we need to reset weekly points
        $userPoints->checkWeeklyReset();
        
        return $userPoints->weekly_points;
    }

    public function addPoints(int $points, string $source = 'task_completion'): void
    {
        $this->checkWeeklyReset();
        
        $this->increment('total_points', $points);
        $this->increment('weekly_points', $points);
    }

    public function checkWeeklyReset(): void
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        
        if (!$this->last_reset_date || $this->last_reset_date->lt($startOfWeek)) {
            $this->resetWeeklyPoints();
        }
    }

    public function resetWeeklyPoints(): void
    {
        $this->update([
            'weekly_points' => 0,
            'last_reset_date' => Carbon::now()->startOfWeek(),
        ]);
    }

    public static function getLeaderboard(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('user')
            ->orderBy('total_points', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getWeeklyLeaderboard(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        // First ensure all users have reset their weekly points if needed
        static::chunk(100, function ($userPoints) {
            foreach ($userPoints as $userPoint) {
                $userPoint->checkWeeklyReset();
            }
        });
        
        return static::with('user')
            ->orderBy('weekly_points', 'desc')
            ->limit($limit)
            ->get();
    }
}
