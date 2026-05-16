<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class TaskFactory extends Factory
{
    public function definition(): array
    {
       return [
            'created_by' => User::factory(),
            'assigned_to' => User::factory(),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['todo', 'in_progress', 'review', 'done']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'progress' => fake()->numberBetween(0, 100),
            'due_date' => fake()->dateTimeBetween('+1 days', '+30 days'),
            'completed_at' => null,
        ];
    }
}
