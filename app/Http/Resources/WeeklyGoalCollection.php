<?php

namespace App\Http\Resources;

use App\Models\WeeklyGoal;
use App\Support\CompletionRate;
use App\Support\Week;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

/**
 * 一週的目標清單，meta 附上週範圍與完成進度。
 */
class WeeklyGoalCollection extends ResourceCollection
{
    public $collects = WeeklyGoalResource::class;

    /**
     * @param  Collection<int, WeeklyGoal>  $goals
     */
    public function __construct(
        Collection $goals,
        private readonly Week $week,
    ) {
        parent::__construct($goals);
    }

    /**
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        $rate = CompletionRate::fromFlags(
            $this->collection->map(fn (WeeklyGoalResource $goal) => $goal->resource->isAchieved()),
        );

        return [
            'meta' => [
                'week_start' => $this->week->start->toDateString(),
                'week_end' => $this->week->end->toDateString(),
                'completed_count' => $rate->completed,
                'total_count' => $rate->total,
                'progress_percentage' => $rate->percentage(),
            ],
        ];
    }
}
