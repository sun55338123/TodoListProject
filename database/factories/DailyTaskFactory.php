<?php

namespace Database\Factories;

use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyTask>
 */
class DailyTaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'task_date' => now()->toDateString(),
            'title' => fake()->sentence(3),
            'is_completed' => false,
        ];
    }
}
