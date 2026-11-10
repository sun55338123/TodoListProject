<?php

namespace App\Http\Resources;

use App\Models\WeeklyGoal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WeeklyGoal
 */
class WeeklyGoalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rate = $this->subtaskRate();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'week_start' => $this->week_start->toDateString(),
            // 有子任務時為自動計算的結果
            'is_completed' => $this->isAchieved(),
            'has_subtasks' => $rate->total > 0,
            'subtask_completed' => $rate->completed,
            'subtask_total' => $rate->total,
            'subtask_percentage' => $rate->percentage(),
            'subtasks' => $this->subtasks->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'task_date' => $task->task_date->toDateString(),
                'is_completed' => $task->is_completed,
                'completed_on' => $task->completed_on?->toDateString(),
            ])->values(),
        ];
    }
}
