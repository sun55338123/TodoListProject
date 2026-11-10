<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeeklyGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyGoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_weekly_goals(): void
    {
        $this->getJson('/api/weekly-goals?date=2026-11-10')->assertUnauthorized();
    }

    public function test_index_returns_goals_of_week_with_progress(): void
    {
        $user = User::factory()->create();
        // 2026-11-09 是星期一
        WeeklyGoal::factory()->for($user)->create(['week_start' => '2026-11-09', 'is_completed' => true]);
        WeeklyGoal::factory()->for($user)->create(['week_start' => '2026-11-09', 'is_completed' => false]);
        WeeklyGoal::factory()->for($user)->create(['week_start' => '2026-11-09', 'is_completed' => false]);
        WeeklyGoal::factory()->for($user)->create(['week_start' => '2026-11-02']);
        WeeklyGoal::factory()->create(['week_start' => '2026-11-09']);

        // 星期日查詢，仍屬於 11-09 那一週
        $this->actingAs($user)
            ->getJson('/api/weekly-goals?date=2026-11-15')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.week_start', '2026-11-09')
            ->assertJsonPath('meta.week_end', '2026-11-15')
            ->assertJsonPath('meta.completed_count', 1)
            ->assertJsonPath('meta.total_count', 3)
            ->assertJsonPath('meta.progress_percentage', 33);
    }

    public function test_progress_is_zero_when_week_has_no_goals(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/weekly-goals?date=2026-11-10')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.progress_percentage', 0);
    }

    public function test_create_normalizes_week_start_to_monday(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/weekly-goals', ['title' => '運動 3 次', 'week_start' => '2026-11-12'])
            ->assertCreated()
            ->assertJsonPath('data.week_start', '2026-11-09')
            ->assertJsonPath('data.is_completed', false);
    }

    public function test_user_can_check_goal(): void
    {
        $user = User::factory()->create();
        $goal = WeeklyGoal::factory()->for($user)->create();

        $this->actingAs($user)
            ->patchJson("/api/weekly-goals/{$goal->id}", ['is_completed' => true])
            ->assertOk()
            ->assertJsonPath('data.is_completed', true);
    }

    public function test_user_can_delete_own_goal(): void
    {
        $user = User::factory()->create();
        $goal = WeeklyGoal::factory()->for($user)->create();

        $this->actingAs($user)->deleteJson("/api/weekly-goals/{$goal->id}")->assertNoContent();

        $this->assertModelMissing($goal);
    }

    public function test_user_cannot_modify_other_users_goal(): void
    {
        $user = User::factory()->create();
        $goal = WeeklyGoal::factory()->create();

        $this->actingAs($user)->patchJson("/api/weekly-goals/{$goal->id}", ['is_completed' => true])->assertForbidden();
        $this->actingAs($user)->deleteJson("/api/weekly-goals/{$goal->id}")->assertForbidden();

        $this->assertModelExists($goal);
    }
}
