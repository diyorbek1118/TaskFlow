<?php

namespace App\Services;

use App\Models\Issue;
use App\Models\Task;
use App\Models\User;
use App\Models\BallLog;
use Illuminate\Pagination\Paginator;

class IssueService
{
    /**
     * Get all issues for a task with filters
     */
    public function getByTask(int $taskId, int $teamId, array $filters = []): Paginator
    {
        $task = Task::where('team_id', $teamId)
            ->where('id', $taskId)
            ->first();

        if (!$task) {
            return collect();
        }

        $query = Issue::where('task_id', $taskId);

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['severity']) && $filters['severity']) {
            $query->where('severity', $filters['severity']);
        }

        if (isset($filters['assigned_to']) && $filters['assigned_to']) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create new issue
     */
    public function create(array $data, int $taskId, int $userId, int $teamId): ?Issue
    {
        // Verify task exists and belongs to team
        $task = Task::where('team_id', $teamId)
            ->where('id', $taskId)
            ->first();

        if (!$task) {
            return null;
        }

        // Verify assigned_to belongs to same team if provided
        if (isset($data['assigned_to']) && $data['assigned_to']) {
            $assignee = User::where('team_id', $teamId)
                ->where('id', $data['assigned_to'])
                ->first();

            if (!$assignee) {
                return null;
            }
        }

        return Issue::create([
            'task_id' => $taskId,
            'created_by' => $userId,
            'assigned_to' => $data['assigned_to'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'],
            'severity' => $data['severity'] ?? 'medium',
            'status' => 'open',
        ]);
    }

    /**
     * Update issue
     */
    public function update(array $data, int $issueId, int $userId, string $userRole, int $teamId): ?Issue
    {
        $issue = Issue::with('task')
            ->whereHas('task', function ($query) use ($teamId) {
                $query->where('team_id', $teamId);
            })
            ->find($issueId);

        if (!$issue) {
            return null;
        }

        // Authorization: member can only update own issue
        if ($userRole === 'member' && $issue->created_by !== $userId) {
            return null;
        }

        // Can only update open or in_progress issues
        if (!in_array($issue->status, ['open', 'in_progress'])) {
            return null;
        }

        // Verify assigned_to belongs to same team if provided
        if (isset($data['assigned_to']) && $data['assigned_to']) {
            $assignee = User::where('team_id', $teamId)
                ->where('id', $data['assigned_to'])
                ->first();

            if (!$assignee) {
                return null;
            }
        }

        $issue->update([
            'title' => $data['title'] ?? $issue->title,
            'description' => $data['description'] ?? $issue->description,
            'severity' => $data['severity'] ?? $issue->severity,
            'assigned_to' => $data['assigned_to'] ?? $issue->assigned_to,
        ]);

        return $issue->fresh();
    }

    /**
     * Resolve issue
     */
    public function resolve(int $issueId, int $userId, string $userRole, int $teamId): ?Issue
    {
        $issue = Issue::with('task')
            ->whereHas('task', function ($query) use ($teamId) {
                $query->where('team_id', $teamId);
            })
            ->find($issueId);

        if (!$issue) {
            return null;
        }

        // Authorization: member can only resolve own issue
        if ($userRole === 'member' && $issue->created_by !== $userId) {
            return null;
        }

        // assigned_to is required
        if (!$issue->assigned_to) {
            return null;
        }

        // Update status
        $issue->status = 'resolved';
        $issue->resolved_at = now();
        $issue->save();

        // Award base ball (3 points)
        $user = User::find($issue->assigned_to);
        if ($user) {
            $user->ball += 3;
            $user->save();

            BallLog::create([
                'user_id' => $issue->assigned_to,
                'task_id' => $issue->task_id,
                'action' => 'issue_resolved',
                'ball' => 3,
            ]);
        }

        return $issue->fresh();
    }

    /**
     * Check and approve/reject resolved issue
     */
    public function checkedResolve(int $issueId, bool $approve, int $userId, int $teamId, ?string $notes = null): ?Issue
    {
        $issue = Issue::with('task')
            ->whereHas('task', function ($query) use ($teamId) {
                $query->where('team_id', $teamId);
            })
            ->find($issueId);

        if (!$issue) {
            return null;
        }

        if ($issue->status !== 'resolved') {
            return null;
        }

        if ($approve) {
            // Approve: calculate bonus based on severity
            $bonusPoints = match ($issue->severity) {
                'low' => 1,
                'medium' => 3,
                'high' => 5,
                'critical' => 10,
                default => 3,
            };

            $issue->is_checked = true;
            $issue->checked_by = $userId;
            $issue->checked_at = now();
            $issue->ball_awarded = true;
            $issue->save();

            // Award bonus ball
            $user = User::find($issue->assigned_to);
            if ($user) {
                $user->ball += $bonusPoints;
                $user->save();

                BallLog::create([
                    'user_id' => $issue->assigned_to,
                    'task_id' => $issue->task_id,
                    'action' => 'issue_resolved',
                    'ball' => $bonusPoints,
                ]);
            }
        } else {
            // Reject: revert to in_progress
            $issue->status = 'in_progress';
            $issue->save();
        }

        return $issue->fresh();
    }

    /**
     * Delete issue
     */
    public function delete(int $issueId, int $teamId): bool
    {
        $issue = Issue::with('task')
            ->whereHas('task', function ($query) use ($teamId) {
                $query->where('team_id', $teamId);
            })
            ->find($issueId);

        if (!$issue) {
            return false;
        }

        // Can only delete open status
        if ($issue->status !== 'open') {
            return false;
        }

        // Delete attachments
        $issue->attachments()->delete();

        $issue->delete();

        return true;
    }

    /**
     * Get issue statistics
     */
    public function getStatistics(int $teamId): array
    {
        $issues = Issue::whereHas('task', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })->get();

        return [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'checked' => $issues->where('is_checked', true)->count(),
            'by_severity' => [
                'low' => $issues->where('severity', 'low')->count(),
                'medium' => $issues->where('severity', 'medium')->count(),
                'high' => $issues->where('severity', 'high')->count(),
                'critical' => $issues->where('severity', 'critical')->count(),
            ],
        ];
    }
}
