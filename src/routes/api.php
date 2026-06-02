<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\IssueController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::apiResource('members', \App\Http\Controllers\Api\MemberController::class);

        Route::post('/tasks', [TaskController::class, 'store']);
        Route::put('/tasks/{id}', [TaskController::class, 'update']);
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
        Route::patch('/tasks/{id}/assign', [TaskController::class, 'assign']);
        Route::patch('/tasks/{id}/complete', [TaskController::class, 'complete']);

        // Admin only issue routes
        Route::patch('/issues/{issueId}/checked', [IssueController::class, 'checked']);
        Route::delete('/issues/{issueId}', [IssueController::class, 'destroy']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::get('/tasks/{id}', [TaskController::class, 'show']);

        // Issue routes
        Route::get('/tasks/{taskId}/issues', [IssueController::class, 'index']);
        Route::post('/tasks/{taskId}/issues', [IssueController::class, 'store']);
        Route::put('/issues/{issueId}', [IssueController::class, 'update']);
        Route::patch('/issues/{issueId}/resolve', [IssueController::class, 'resolve']);

        Route::middleware('role:member')->group(function () {
            Route::patch('/tasks/{id}/progress', [TaskController::class, 'updateProgress']);
        });
    });
});
