<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserFactory extends Factory
{

     
    public function definition(): array
    {
       return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => fake()->randomElement(['admin', 'member']),
            'avatar' => null,
            'ball' => fake()->numberBetween(0, 500),
            'last_seen_at' => now()->subMinutes(rand(1, 500)),
            'online_minutes' => fake()->numberBetween(0, 2000),
            'remember_token' => Str::random(10),
        ];
    }

 
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
