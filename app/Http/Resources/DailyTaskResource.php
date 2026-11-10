<?php

namespace App\Http\Resources;

use App\Models\DailyTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DailyTask
 */
class DailyTaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'task_date' => $this->task_date->toDateString(),
            'is_completed' => $this->is_completed,
            'completed_on' => $this->completed_on?->toDateString(),
            'weekly_goal' => $this->whenLoaded('weeklyGoal', fn () => $this->weeklyGoal === null ? null : [
                'id' => $this->weeklyGoal->id,
                'title' => $this->weeklyGoal->title,
            ]),
        ];
    }
}
