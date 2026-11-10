<?php

namespace App\Contracts\Services;

use App\Models\User;
use App\Models\WeeklyGoal;
use App\Support\Week;
use Illuminate\Support\Collection;

interface WeeklyGoalServiceInterface
{
    /**
     * @return Collection<int, WeeklyGoal>
     */
    public function listForWeek(User $user, Week $week): Collection;

    /**
     * week_start 可以是該週任一天，儲存時會轉換為該週星期一。
     *
     * @param  array{title: string, week_start: string}  $data
     */
    public function create(User $user, array $data): WeeklyGoal;

    /**
     * @param  array{title?: string, week_start?: string, is_completed?: bool}  $data
     */
    public function update(WeeklyGoal $goal, array $data): WeeklyGoal;

    public function delete(WeeklyGoal $goal): void;
}
