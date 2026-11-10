<?php

namespace App\Models;

use App\Support\CompletionRate;
use Database\Factories\WeeklyGoalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyGoal extends Model
{
    /** @use HasFactory<WeeklyGoalFactory> */
    use HasFactory;

    /**
     * 與資料表預設值一致，讓新建立的模型在回應中也有正確的值。
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_completed' => false,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'week_start',
        'title',
        'is_completed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'is_completed' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 掛勾此目標的每日任務。
     *
     * @return HasMany<DailyTask, $this>
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(DailyTask::class);
    }

    /**
     * 子任務完成率。
     */
    public function subtaskRate(): CompletionRate
    {
        return CompletionRate::fromItems($this->subtasks);
    }

    /**
     * 有子任務時，是否完成由子任務決定（全部完成才算完成）；沒有子任務時使用手動打勾的值。
     */
    public function isAchieved(): bool
    {
        $rate = $this->subtaskRate();

        if ($rate->total === 0) {
            return $this->is_completed;
        }

        return $rate->completed === $rate->total;
    }
}
