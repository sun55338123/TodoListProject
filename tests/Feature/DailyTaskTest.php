<?php

namespace Tests\Feature;

use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_daily_tasks(): void
    {
        $this->getJson('/api/daily-tasks?date=2026-11-10')->assertUnauthorized();
    }

    public function test_index_returns_only_own_tasks_for_given_date(): void
    {
        $user = User::factory()->create();
        $own = DailyTask::factory()->for($user)->create(['task_date' => '2026-11-10']);
        DailyTask::factory()->for($user)->create(['task_date' => '2026-11-11']);
        DailyTask::factory()->create(['task_date' => '2026-11-10']);

        $this->actingAs($user)
            ->getJson('/api/daily-tasks?date=2026-11-10')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id)
            ->assertJsonPath('data.0.task_date', '2026-11-10');
    }

    public function test_index_requires_valid_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/daily-tasks')->assertUnprocessable()->assertJsonValidationErrors('date');
        $this->actingAs($user)->getJson('/api/daily-tasks?date=2026/11/10')->assertUnprocessable();
    }

    public function test_user_can_create_task(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/daily-tasks', ['title' => '寫報告', 'task_date' => '2026-11-10'])
            ->assertCreated()
            ->assertJsonPath('data.title', '寫報告')
            ->assertJsonPath('data.is_completed', false);

        $this->assertDatabaseHas('daily_tasks', ['user_id' => $user->id, 'title' => '寫報告']);
    }

    public function test_user_can_toggle_completion_and_edit_past_task(): void
    {
        $user = User::factory()->create();
        $task = DailyTask::factory()->for($user)->create(['task_date' => '2020-01-01']);

        $this->actingAs($user)
            ->patchJson("/api/daily-tasks/{$task->id}", ['is_completed' => true, 'title' => '已修改'])
            ->assertOk()
            ->assertJsonPath('data.is_completed', true)
            ->assertJsonPath('data.title', '已修改');
    }

    public function test_user_can_delete_own_task(): void
    {
        $user = User::factory()->create();
        $task = DailyTask::factory()->for($user)->create();

        $this->actingAs($user)->deleteJson("/api/daily-tasks/{$task->id}")->assertNoContent();

        $this->assertModelMissing($task);
    }

    public function test_user_cannot_modify_other_users_task(): void
    {
        $user = User::factory()->create();
        $task = DailyTask::factory()->create();

        $this->actingAs($user)->patchJson("/api/daily-tasks/{$task->id}", ['is_completed' => true])->assertForbidden();
        $this->actingAs($user)->deleteJson("/api/daily-tasks/{$task->id}")->assertForbidden();

        $this->assertModelExists($task);
    }
}
