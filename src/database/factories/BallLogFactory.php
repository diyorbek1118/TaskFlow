<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BallLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'action' => fake()->randomElement([
                'task_completed',
                'early_bonus',
                'issue_resolved'
            ]),
            'ball' => fake()->numberBetween(5, 100),
            'created_at' => now()->subDays(rand(0, 30)),
        ];
    }
}