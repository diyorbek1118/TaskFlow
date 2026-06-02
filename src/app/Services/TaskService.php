<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\BallLog;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function create(array $data, int $teamId, int $adminId): Task
    {
        return Task::create([
            'team_id' => $teamId,
            'created_by' => $adminId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'todo',
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
        ]);
    }

    public function assign(int $taskId, int $memberId, int $teamId): ?Task
    {
        $task = Task::where('team_id', $teamId)
            ->where('id', $taskId)
            ->first();

        if (!$task) {
            return null;
        }

        $member = User::where('team_id', $teamId)
            ->where('id', $memberId)
            ->where('role', 'member')
            ->first();

        if (!$member) {
            return null;
        }

        $task->assigned_to = $memberId;
        $task->status = 'in_progress';
        $task->save();

        return $task->fresh();
    }

    public function updateProgress(int $taskId, int $progress, int $userId, int $teamId): ?Task
    {
        $task = Task::where('team_id', $teamId)
            ->where('id', $taskId)
            ->where('assigned_to', $userId)
            ->first();

        if (!$task) {
            return null;
        }

        $task->progress = min(100, max(0, $progress));

        if ($task->progress == 100) {
            $task->status = 'review';
        } elseif ($task->progress > 0 && $task->status == 'todo') {
            $task->status = 'in_progress';
        }

        $task->save();

        return $task->fresh();
    }

    public function complete(int $taskId, int $teamId): ?Task
    {
        return DB::transaction(function () use ($taskId, $teamId) {
            $task = Task::where('team_id', $teamId)
                ->where('id', $taskId)
                ->first();

            if (!$task || !$task->assigned_to) {
                return null;
            }

            $task->status = 'done';
            $task->progress = 100;
            $task->completed_at = now();
            $task->save();

            $ball = $this->calculateBall($task->priority);

            $user = User::find($task->assigned_to);
            if ($user) {
                $user->ball += $ball;
                $user->save();
            }

            BallLog::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'action' => 'task_completed',
                'ball' => $ball,
                'created_at' => now(),
            ]);

            return $task->fresh();
        });
    }

    private function calculateBall(string $priority): int
    {
        return match ($priority) {
            'low' => 10,
            'medium' => 20,
            'high' => 30,
            'urgent' => 50,
            default => 20,
        };
    }

    public function index(int $teamId, ?int $userId = null, ?string $role = null)
    {
        $query = Task::where('team_id', $teamId);

        if ($role === 'member' && $userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->with(['creator', 'assignee'])->get();
    }

    public function show(int $taskId, int $teamId): ?Task
    {
        return Task::where('team_id', $teamId)
            ->where('id', $taskId)
            ->with(['creator', 'assignee', 'issues', 'comments', 'attachments'])
            ->first();
    }

    public function update(array $data, int $taskId, int $teamId): ?Task
    {
        $task = Task::where('team_id', $teamId)
            ->where('id', $taskId)
            ->first();

        if (!$task) {
            return null;
        }

        if (isset($data['title'])) {
            $task->title = $data['title'];
        }
        if (isset($data['description'])) {
            $task->description = $data['description'];
        }
        if (isset($data['priority'])) {
            $task->priority = $data['priority'];
        }
        if (isset($data['due_date'])) {
            $task->due_date = $data['due_date'];
        }
        if (isset($data['status'])) {
            $task->status = $data['status'];
        }

        $task->save();

        return $task->fresh();
    }

    public function destroy(int $taskId, int $teamId): bool
    {
        $task = Task::where('team_id', $teamId)
            ->where('id', $taskId)
            ->first();

        if (!$task) {
            return false;
        }

        return $task->delete();
    }
}
