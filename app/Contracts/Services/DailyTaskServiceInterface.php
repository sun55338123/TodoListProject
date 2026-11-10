<?php

namespace App\Contracts\Services;

use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Support\Collection;

interface DailyTaskServiceInterface
{
    /**
     * 包含延到該日的週目標子任務。
     *
     * @return Collection<int, DailyTask>
     */
    public function listForDate(User $user, string $date): Collection;

    /**
     * weekly_goal_id 的週目標必須屬於 task_date 所在的那一週。
     *
     * @param  array{title: string, task_date: string, weekly_goal_id?: int|null}  $data
     */
    public function create(User $user, array $data): DailyTask;

    /**
     * is_completed 為 true 時，completed_on 為完成日期（未提供時使用任務日期）；為 false 時清除完成日期。
     *
     * @param  array{title?: string, task_date?: string, weekly_goal_id?: int|null, is_completed?: bool, completed_on?: string}  $data
     */
    public function update(DailyTask $task, array $data): DailyTask;

    public function delete(DailyTask $task): void;
}
