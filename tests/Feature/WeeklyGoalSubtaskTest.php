<?php

namespace Tests\Feature;

use App\Models\DailyTask;
use App\Models\User;
use App\Models\WeeklyGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 週目標與每日任務的掛勾。2026-11-09 是星期一。
 */
class WeeklyGoalSubtaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private WeeklyGoal $goal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->goal = WeeklyGoal::factory()->for($this->user)->create(['week_start' => '2026-11-09']);
    }

    private function subtask(array $attributes = []): DailyTask
    {
        return DailyTask::factory()->for($this->user)->create(array_merge([
            'weekly_goal_id' => $this->goal->id,
            'task_date' => '2026-11-10',
        ], $attributes));
    }

    private function idsOn(string $date): array
    {
        return collect($this->actingAs($this->user)->getJson("/api/daily-tasks?date={$date}")->assertOk()->json('data'))
            ->pluck('id')
            ->all();
    }

    public function test_user_can_create_linked_task(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/daily-tasks', ['title' => '背單字', 'task_date' => '2026-11-10', 'weekly_goal_id' => $this->goal->id])
            ->assertCreated()
            ->assertJsonPath('data.weekly_goal.id', $this->goal->id)
            ->assertJsonPath('data.weekly_goal.title', $this->goal->title);
    }

    public function test_cannot_link_goal_of_another_week(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/daily-tasks', ['title' => '背單字', 'task_date' => '2026-11-16', 'weekly_goal_id' => $this->goal->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('weekly_goal_id');
    }

    public function test_cannot_link_goal_of_another_user(): void
    {
        $otherGoal = WeeklyGoal::factory()->create(['week_start' => '2026-11-09']);

        $this->actingAs($this->user)
            ->postJson('/api/daily-tasks', ['title' => '背單字', 'task_date' => '2026-11-10', 'weekly_goal_id' => $otherGoal->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('weekly_goal_id');
    }

    public function test_incomplete_linked_task_carries_over_including_next_week(): void
    {
        $task = $this->subtask();

        $this->assertSame([], $this->idsOn('2026-11-09'));
        $this->assertSame([$task->id], $this->idsOn('2026-11-10'));
        $this->assertSame([$task->id], $this->idsOn('2026-11-11'));
        $this->assertSame([$task->id], $this->idsOn('2026-11-20'));
    }

    public function test_standalone_task_does_not_carry_over(): void
    {
        $task = DailyTask::factory()->for($this->user)->create(['task_date' => '2026-11-10']);

        $this->assertSame([$task->id], $this->idsOn('2026-11-10'));
        $this->assertSame([], $this->idsOn('2026-11-11'));
    }

    public function test_completed_linked_task_only_shows_on_completion_day(): void
    {
        $task = $this->subtask();

        $this->actingAs($this->user)
            ->patchJson("/api/daily-tasks/{$task->id}", ['is_completed' => true, 'completed_on' => '2026-11-12'])
            ->assertOk()
            ->assertJsonPath('data.completed_on', '2026-11-12');

        $this->assertSame([], $this->idsOn('2026-11-11'));
        $this->assertSame([$task->id], $this->idsOn('2026-11-12'));
        $this->assertSame([], $this->idsOn('2026-11-13'));
    }

    public function test_unchecking_clears_completion_date(): void
    {
        $task = $this->subtask(['is_completed' => true, 'completed_on' => '2026-11-12']);

        $this->actingAs($this->user)
            ->patchJson("/api/daily-tasks/{$task->id}", ['is_completed' => false])
            ->assertOk()
            ->assertJsonPath('data.completed_on', null);

        $this->assertSame([$task->id], $this->idsOn('2026-11-13'));
    }

    public function test_completion_date_defaults_to_task_date(): void
    {
        $task = DailyTask::factory()->for($this->user)->create(['task_date' => '2026-11-10']);

        $this->actingAs($this->user)
            ->patchJson("/api/daily-tasks/{$task->id}", ['is_completed' => true])
            ->assertOk()
            ->assertJsonPath('data.completed_on', '2026-11-10');
    }

    public function test_goal_progress_comes_from_subtasks_and_auto_completes(): void
    {
        $this->subtask(['is_completed' => true, 'completed_on' => '2026-11-10']);
        $pending = $this->subtask();

        $this->actingAs($this->user)
            ->getJson('/api/weekly-goals?date=2026-11-10')
            ->assertJsonPath('data.0.has_subtasks', true)
            ->assertJsonPath('data.0.subtask_completed', 1)
            ->assertJsonPath('data.0.subtask_total', 2)
            ->assertJsonPath('data.0.subtask_percentage', 50)
            ->assertJsonPath('data.0.is_completed', false)
            ->assertJsonCount(2, 'data.0.subtasks')
            ->assertJsonPath('meta.completed_count', 0);

        $this->actingAs($this->user)->patchJson("/api/daily-tasks/{$pending->id}", ['is_completed' => true]);

        $this->actingAs($this->user)
            ->getJson('/api/weekly-goals?date=2026-11-10')
            ->assertJsonPath('data.0.is_completed', true)
            ->assertJsonPath('data.0.subtask_percentage', 100)
            ->assertJsonPath('meta.completed_count', 1)
            ->assertJsonPath('meta.progress_percentage', 100);
    }

    public function test_adding_subtask_reopens_completed_goal(): void
    {
        $this->subtask(['is_completed' => true, 'completed_on' => '2026-11-10']);
        $this->subtask();

        $this->actingAs($this->user)
            ->getJson('/api/weekly-goals?date=2026-11-10')
            ->assertJsonPath('data.0.is_completed', false);
    }

    public function test_cannot_manually_check_goal_with_subtasks(): void
    {
        $this->subtask();

        $this->actingAs($this->user)
            ->patchJson("/api/weekly-goals/{$this->goal->id}", ['is_completed' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('is_completed');
    }

    public function test_cannot_move_goal_with_subtasks_to_another_week(): void
    {
        $this->subtask();

        $this->actingAs($this->user)
            ->patchJson("/api/weekly-goals/{$this->goal->id}", ['week_start' => '2026-11-16'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('week_start');
    }

    public function test_goal_without_subtasks_can_still_be_checked_manually(): void
    {
        $this->actingAs($this->user)
            ->patchJson("/api/weekly-goals/{$this->goal->id}", ['is_completed' => true])
            ->assertOk()
            ->assertJsonPath('data.is_completed', true)
            ->assertJsonPath('data.has_subtasks', false);
    }

    public function test_existing_task_can_be_relinked_to_another_goal_in_same_week(): void
    {
        $task = $this->subtask();
        $other = WeeklyGoal::factory()->for($this->user)->create(['week_start' => '2026-11-09']);

        $this->actingAs($this->user)
            ->patchJson("/api/daily-tasks/{$task->id}", ['weekly_goal_id' => $other->id])
            ->assertOk()
            ->assertJsonPath('data.weekly_goal.id', $other->id);
    }

    public function test_standalone_task_can_be_linked_later(): void
    {
        $task = DailyTask::factory()->for($this->user)->create(['task_date' => '2026-11-10']);

        $this->actingAs($this->user)
            ->patchJson("/api/daily-tasks/{$task->id}", ['weekly_goal_id' => $this->goal->id])
            ->assertOk()
            ->assertJsonPath('data.weekly_goal.id', $this->goal->id);

        $this->assertSame([$task->id], $this->idsOn('2026-11-11'));
    }

    public function test_unlinking_carried_task_with_new_date_keeps_it_on_that_date_only(): void
    {
        $task = $this->subtask();

        $this->actingAs($this->user)
            ->patchJson("/api/daily-tasks/{$task->id}", ['weekly_goal_id' => null, 'task_date' => '2026-11-12'])
            ->assertOk()
            ->assertJsonPath('data.weekly_goal', null)
            ->assertJsonPath('data.task_date', '2026-11-12');

        $this->assertSame([], $this->idsOn('2026-11-11'));
        $this->assertSame([$task->id], $this->idsOn('2026-11-12'));
        $this->assertSame([], $this->idsOn('2026-11-13'));
    }

    public function test_cannot_relink_to_goal_of_another_week(): void
    {
        $task = $this->subtask();
        $nextWeekGoal = WeeklyGoal::factory()->for($this->user)->create(['week_start' => '2026-11-16']);

        $this->actingAs($this->user)
            ->patchJson("/api/daily-tasks/{$task->id}", ['weekly_goal_id' => $nextWeekGoal->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('weekly_goal_id');
    }

    public function test_deleting_goal_deletes_its_subtasks(): void
    {
        $task = $this->subtask();
        $standalone = DailyTask::factory()->for($this->user)->create();

        $this->actingAs($this->user)->deleteJson("/api/weekly-goals/{$this->goal->id}")->assertNoContent();

        $this->assertModelMissing($task);
        $this->assertModelExists($standalone);
    }
}
