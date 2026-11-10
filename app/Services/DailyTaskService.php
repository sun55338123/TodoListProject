<?php

namespace App\Services;

use App\Contracts\Repositories\DailyTaskRepositoryInterface;
use App\Contracts\Repositories\WeeklyGoalRepositoryInterface;
use App\Contracts\Services\DailyTaskServiceInterface;
use App\Models\DailyTask;
use App\Models\User;
use App\Support\Week;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DailyTaskService implements DailyTaskServiceInterface
{
    public function __construct(
        private readonly DailyTaskRepositoryInterface $tasks,
        private readonly WeeklyGoalRepositoryInterface $goals,
    ) {}

    public function listForDate(User $user, string $date): Collection
    {
        return $this->tasks->forUserOnDate($user, $date);
    }

    public function create(User $user, array $data): DailyTask
    {
        $this->assertGoalInTaskWeek($user->id, $data['weekly_goal_id'] ?? null, $data['task_date']);

        return $this->tasks->createForUser($user, $data);
    }

    public function update(DailyTask $task, array $data): DailyTask
    {
        if (array_key_exists('task_date', $data) || array_key_exists('weekly_goal_id', $data)) {
            $this->assertGoalInTaskWeek(
                $task->user_id,
                array_key_exists('weekly_goal_id', $data) ? $data['weekly_goal_id'] : $task->weekly_goal_id,
                $data['task_date'] ?? $task->task_date->toDateString(),
            );
        }

        return $this->tasks->update($task, $this->resolveCompletedOn($task, $data));
    }

    public function delete(DailyTask $task): void
    {
        $this->tasks->delete($task);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveCompletedOn(DailyTask $task, array $data): array
    {
        if (! array_key_exists('is_completed', $data)) {
            unset($data['completed_on']);

            return $data;
        }

        $data['completed_on'] = $data['is_completed']
            ? ($data['completed_on'] ?? $data['task_date'] ?? $task->task_date->toDateString())
            : null;

        return $data;
    }

    /**
     * 掛勾的週目標必須屬於該使用者，且為任務日期所在的那一週。
     */
    private function assertGoalInTaskWeek(int $userId, ?int $goalId, string $taskDate): void
    {
        if ($goalId === null) {
            return;
        }

        $goal = $this->goals->findOwnedBy($userId, $goalId);

        if ($goal === null) {
            throw ValidationException::withMessages(['weekly_goal_id' => '找不到這個週目標。']);
        }

        if ($goal->week_start->toDateString() !== Week::containing($taskDate)->start->toDateString()) {
            throw ValidationException::withMessages(['weekly_goal_id' => '只能掛勾任務日期所在週的週目標。']);
        }
    }
}
