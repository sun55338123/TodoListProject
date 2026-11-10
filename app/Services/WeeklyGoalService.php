<?php

namespace App\Services;

use App\Contracts\Repositories\WeeklyGoalRepositoryInterface;
use App\Contracts\Services\WeeklyGoalServiceInterface;
use App\Models\User;
use App\Models\WeeklyGoal;
use App\Support\Week;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WeeklyGoalService implements WeeklyGoalServiceInterface
{
    public function __construct(
        private readonly WeeklyGoalRepositoryInterface $goals,
    ) {}

    public function listForWeek(User $user, Week $week): Collection
    {
        return $this->goals->forUserInWeek($user, $week->start->toDateString());
    }

    public function create(User $user, array $data): WeeklyGoal
    {
        return $this->goals->createForUser($user, $this->normalizeWeekStart($data));
    }

    public function update(WeeklyGoal $goal, array $data): WeeklyGoal
    {
        $this->assertEditableWithSubtasks($goal, $data);

        return $this->goals->update($goal, $this->normalizeWeekStart($data));
    }

    /**
     * 有子任務的目標：完成狀態由子任務決定，不可手動打勾；也不可移到其他週，避免子任務與目標不在同一週。
     *
     * @param  array<string, mixed>  $data
     */
    private function assertEditableWithSubtasks(WeeklyGoal $goal, array $data): void
    {
        $changesCompletion = array_key_exists('is_completed', $data);
        $changesWeek = array_key_exists('week_start', $data);

        if (! $changesCompletion && ! $changesWeek) {
            return;
        }

        if (! $this->goals->hasSubtasks($goal)) {
            return;
        }

        if ($changesCompletion) {
            throw ValidationException::withMessages(['is_completed' => '此目標有子任務，完成狀態由子任務自動計算。']);
        }

        throw ValidationException::withMessages(['week_start' => '此目標有子任務，不能移到其他週。']);
    }

    public function delete(WeeklyGoal $goal): void
    {
        $this->goals->delete($goal);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeWeekStart(array $data): array
    {
        if (isset($data['week_start'])) {
            $data['week_start'] = Week::containing($data['week_start'])->start->toDateString();
        }

        return $data;
    }
}
