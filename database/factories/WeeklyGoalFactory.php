<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WeeklyGoal;
use App\Support\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeeklyGoal>
 */
class WeeklyGoalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'week_start' => Week::containing(now())->start->toDateString(),
            'title' => fake()->sentence(3),
            'is_completed' => false,
        ];
    }
}
