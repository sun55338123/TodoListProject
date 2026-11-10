<?php

namespace App\Repositories;

use App\Contracts\Repositories\WeeklyGoalRepositoryInterface;
use App\Models\User;
use App\Models\WeeklyGoal;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class EloquentWeeklyGoalRepository implements WeeklyGoalRepositoryInterface
{
    public function forUserInWeek(User $user, string $weekStart): Collection
    {
        return $user->weeklyGoals()
            ->whereDate('week_start', $weekStart)
            ->with($this->subtasksRelation())
            ->orderBy('id')
            ->get();
    }

    public function findOwnedBy(int $userId, int $goalId): ?WeeklyGoal
    {
        return WeeklyGoal::query()->where('user_id', $userId)->find($goalId);
    }

    public function hasSubtasks(WeeklyGoal $goal): bool
    {
        return $goal->subtasks()->exists();
    }

    public function createForUser(User $user, array $attributes): WeeklyGoal
    {
        return $user->weeklyGoals()->create($attributes)->load($this->subtasksRelation());
    }

    public function update(WeeklyGoal $goal, array $attributes): WeeklyGoal
    {
        $goal->update($attributes);

        return $goal->load($this->subtasksRelation());
    }

    public function delete(WeeklyGoal $goal): void
    {
        // 子任務由外鍵 cascadeOnDelete 一併刪除。
        $goal->delete();
    }

    /**
     * @return array<string, \Closure>
     */
    private function subtasksRelation(): array
    {
        return [
            'subtasks' => fn (HasMany $query) => $query->orderBy('task_date')->orderBy('id'),
        ];
    }
}
