<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IssueResource;
use App\Services\IssueService;
use App\Models\Issue;
use App\Models\Task;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function __construct(
        private IssueService $issueService
    ) {}

    /**
     * GET /api/v1/tasks/{taskId}/issues
     * Get all issues for a task
     */
    public function index(Request $request, int $taskId)
    {
        $filters = [
            'status' => $request->query('status'),
            'severity' => $request->query('severity'),
            'assigned_to' => $request->query('assigned_to'),
            'per_page' => $request->query('per_page', 15),
        ];

        $issues = $this->issueService->getByTask(
            $taskId,
            $request->user()->team_id,
            $filters
        );

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => IssueResource::collection($issues),
            'pagination' => [
                'total' => $issues->total(),
                'per_page' => $issues->perPage(),
                'current_page' => $issues->currentPage(),
                'last_page' => $issues->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/tasks/{taskId}/issues
     * Create new issue
     */
    public function store(Request $request, int $taskId)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $issue = $this->issueService->create(
            $data,
            $taskId,
            $request->user()->id,
            $request->user()->team_id
        );

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Issue created successfully',
            'data' => new IssueResource($issue),
        ], 201);
    }

    /**
     * PUT /api/v1/issues/{issueId}
     * Update issue
     */
    public function update(Request $request, int $issueId)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'min:5', 'max:255'],
            'description' => ['sometimes', 'string', 'min:10', 'max:5000'],
            'severity' => ['sometimes', 'in:low,medium,high,critical'],
            'assigned_to' => ['sometimes', 'nullable', 'exists:users,id'],
        ]);

        $issue = $this->issueService->update(
            $data,
            $issueId,
            $request->user()->id,
            $request->user()->role,
            $request->user()->team_id
        );

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found or unauthorized',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Issue updated successfully',
            'data' => new IssueResource($issue),
        ]);
    }

    /**
     * PATCH /api/v1/issues/{issueId}/resolve
     * Resolve issue
     */
    public function resolve(Request $request, int $issueId)
    {
        $issue = $this->issueService->resolve(
            $issueId,
            $request->user()->id,
            $request->user()->role,
            $request->user()->team_id
        );

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found or unauthorized',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Issue resolved successfully',
            'data' => new IssueResource($issue),
        ]);
    }

    /**
     * PATCH /api/v1/issues/{issueId}/checked
     * Check and approve/reject resolved issue
     */
    public function checked(Request $request, int $issueId)
    {
        $data = $request->validate([
            'approve' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $issue = $this->issueService->checkedResolve(
            $issueId,
            $data['approve'],
            $request->user()->id,
            $request->user()->team_id,
            $data['notes'] ?? null
        );

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found or not in resolved status',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => $data['approve'] ? 'Issue checked and approved' : 'Issue rejected',
            'data' => new IssueResource($issue),
        ]);
    }

    /**
     * DELETE /api/v1/issues/{issueId}
     * Delete issue
     */
    public function destroy(Request $request, int $issueId)
    {
        $result = $this->issueService->delete($issueId, $request->user()->team_id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found or cannot be deleted',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Issue deleted successfully',
        ]);
    }
}
