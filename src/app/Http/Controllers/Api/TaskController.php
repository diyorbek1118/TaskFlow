<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {
    }

    public function index(Request $request)
    {
        $tasks = $this->taskService->index(
            $request->user()->team_id,
            $request->user()->id,
            $request->user()->role
        );

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task = $this->taskService->create(
            $data,
            $request->user()->team_id,
            $request->user()->id
        );

        return response()->json($task, 201);
    }

    public function show(Request $request, int $id)
    {
        $task = $this->taskService->show($id, $request->user()->team_id);

        if (!$task) {
            return response()->json(['message' => 'Task topilmadi.'], 404);
        }

        return response()->json($task);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
            'due_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'in:todo,in_progress,review,done'],
        ]);

        $task = $this->taskService->update($data, $id, $request->user()->team_id);

        if (!$task) {
            return response()->json(['message' => 'Task topilmadi.'], 404);
        }

        return response()->json($task);
    }

    public function destroy(Request $request, int $id)
    {
        $result = $this->taskService->destroy($id, $request->user()->team_id);

        if (!$result) {
            return response()->json(['message' => 'Task topilmadi.'], 404);
        }

        return response()->json(['message' => 'Task o\'chirildi.']);
    }

    public function assign(Request $request, int $id)
    {
        $data = $request->validate([
            'member_id' => ['required', 'integer'],
        ]);

        $task = $this->taskService->assign($id, $data['member_id'], $request->user()->team_id);

        if (!$task) {
            return response()->json(['message' => 'Task yoki member topilmadi.'], 404);
        }

        return response()->json($task);
    }

    public function updateProgress(Request $request, int $id)
    {
        $data = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $task = $this->taskService->updateProgress(
            $id,
            $data['progress'],
            $request->user()->id,
            $request->user()->team_id
        );

        if (!$task) {
            return response()->json(['message' => 'Task topilmadi yoki sizga biriktirilmagan.'], 404);
        }

        return response()->json($task);
    }

    public function complete(Request $request, int $id)
    {
        $task = $this->taskService->complete($id, $request->user()->team_id);

        if (!$task) {
            return response()->json(['message' => 'Task topilmadi.'], 404);
        }

        return response()->json($task);
    }
}
