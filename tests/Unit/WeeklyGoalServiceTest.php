<?php

namespace Tests\Unit;

use App\Contracts\Repositories\WeeklyGoalRepositoryInterface;
use App\Models\User;
use App\Models\WeeklyGoal;
use App\Services\WeeklyGoalService;
use App\Support\Week;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

/**
 * 以假的 Repository 測試 Service 邏輯，不需要資料庫。
 */
class WeeklyGoalServiceTest extends TestCase
{
    public function test_create_normalizes_week_start_to_monday(): void
    {
        $repository = $this->createMock(WeeklyGoalRepositoryInterface::class);
        $user = new User;

        $repository->expects($this->once())
            ->method('createForUser')
            ->with($user, ['title' => '運動', 'week_start' => '2026-11-09'])
            ->willReturn(new WeeklyGoal);

        (new WeeklyGoalService($repository))->create($user, ['title' => '運動', 'week_start' => '2026-11-14']);
    }

    public function test_update_without_week_start_keeps_data_unchanged(): void
    {
        $repository = $this->createMock(WeeklyGoalRepositoryInterface::class);
        $goal = new WeeklyGoal;

        $repository->expects($this->once())
            ->method('update')
            ->with($goal, ['is_completed' => true])
            ->willReturn($goal);

        (new WeeklyGoalService($repository))->update($goal, ['is_completed' => true]);
    }

    public function test_list_queries_by_monday_of_week(): void
    {
        $repository = $this->createMock(WeeklyGoalRepositoryInterface::class);
        $user = new User;

        $repository->expects($this->once())
            ->method('forUserInWeek')
            ->with($user, '2026-11-09')
            ->willReturn(new Collection);

        (new WeeklyGoalService($repository))->listForWeek($user, Week::containing('2026-11-12'));
    }
}
