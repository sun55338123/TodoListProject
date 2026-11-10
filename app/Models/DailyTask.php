<?php

namespace App\Models;

use Database\Factories\DailyTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTask extends Model
{
    /** @use HasFactory<DailyTaskFactory> */
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
        'weekly_goal_id',
        'task_date',
        'title',
        'is_completed',
        'completed_on',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'task_date' => 'date',
            'is_completed' => 'boolean',
            'completed_on' => 'date',
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
     * @return BelongsTo<WeeklyGoal, $this>
     */
    public function weeklyGoal(): BelongsTo
    {
        return $this->belongsTo(WeeklyGoal::class);
    }

    /**
     * 是否為週目標子任務（未完成時會延到之後的日期）。
     */
    public function isLinked(): bool
    {
        return $this->weekly_goal_id !== null;
    }
}
