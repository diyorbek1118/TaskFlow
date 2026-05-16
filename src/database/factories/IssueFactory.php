<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'created_by' => User::factory(),
            'assigned_to' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'severity' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => fake()->randomElement(['open', 'in_progress', 'resolved']),
            'resolved_at' => null,
        ];
    }
}