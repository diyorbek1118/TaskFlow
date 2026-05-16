<?php

namespace Database\Seeders;

use App\Models\BallLog;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

   
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory(2)->create(['role' => 'admin']);
        User::factory(6)->create(['role' => 'member']);

        Task::factory(20)->create();

        Issue::factory(15)->create();

        Comment::factory(50)->create();

        BallLog::factory(80)->create();
    }
}
