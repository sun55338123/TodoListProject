<?php

namespace App\Repositories;

use App\Contracts\Repositories\DailyTaskRepositoryInterface;
use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentDailyTaskRepository implements DailyTaskRepositoryInterface
{
    public function forUserOnDate(User $user, string $date): Collection
    {
        return $user->dailyTasks()
            ->where(function (Builder $query) use ($date) {
                $query
                    ->where(function (Builder $standalone) use ($date) {
                        $standalone->whereNull('weekly_goal_id')->whereDate('task_date', $date);
                    })
                    ->orWhere(function (Builder $linked) use ($date) {
                        $linked->whereNotNull('weekly_goal_id')
                            ->whereDate('task_date', '<=', $date)
                            ->where(function (Builder $status) use ($date) {
                                $status->where('is_completed', false)->orWhereDate('completed_on', $date);
                            });
                    });
            })
            ->with('weeklyGoal:id,title')
            ->orderBy('task_date')
            ->orderBy('id')
            ->get();
    }

    public function createForUser(User $user, array $attributes): DailyTask
    {
        return $user->dailyTasks()->create($attributes)->load('weeklyGoal:id,title');
    }

    public function update(DailyTask $task, array $attributes): DailyTask
    {
        $task->update($attributes);

        return $task->load('weeklyGoal:id,title');
    }

    public function delete(DailyTask $task): void
    {
        $task->delete();
    }
}
