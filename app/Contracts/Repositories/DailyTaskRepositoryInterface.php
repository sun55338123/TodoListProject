<?php

namespace App\Contracts\Repositories;

use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Support\Collection;

interface DailyTaskRepositoryInterface
{
    /**
     * 指定日期要顯示的任務：
     * 1. 單日任務：任務日期為該日。
     * 2. 週目標子任務：任務日期不晚於該日，且尚未完成或在該日完成（未完成會延到之後的日期）。
     *
     * @return Collection<int, DailyTask>
     */
    public function forUserOnDate(User $user, string $date): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForUser(User $user, array $attributes): DailyTask;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(DailyTask $task, array $attributes): DailyTask;

    public function delete(DailyTask $task): void;
}
