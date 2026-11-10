<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use App\Models\WeeklyGoal;
use Illuminate\Support\Collection;

/**
 * 回傳的 WeeklyGoal 一律已載入 subtasks 關聯。
 */
interface WeeklyGoalRepositoryInterface
{
    /**
     * @param  string  $weekStart  該週星期一（Y-m-d）
     * @return Collection<int, WeeklyGoal>
     */
    public function forUserInWeek(User $user, string $weekStart): Collection;

    /**
     * 取得屬於指定使用者的目標，不存在或不屬於該使用者時回傳 null。
     */
    public function findOwnedBy(int $userId, int $goalId): ?WeeklyGoal;

    public function hasSubtasks(WeeklyGoal $goal): bool;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForUser(User $user, array $attributes): WeeklyGoal;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(WeeklyGoal $goal, array $attributes): WeeklyGoal;

    public function delete(WeeklyGoal $goal): void;
}
