<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TaskService $taskService
    ) {
    }

    public function index(Request $request, int $project): JsonResponse
    {
        $tasks = $this->taskService->list(
            $request->user(),
            $project,
            $request->only([
                'status',
                'priority',
                'search',
            ])
        );

        return $this->success(
            data: TaskResource::collection($tasks),
            message: 'Tasks retrieved successfully.'
        );
    }

    public function store(
        StoreTaskRequest $request,
        int $project
    ): JsonResponse {
        $task = $this->taskService->create(
            $request->user(),
            $project,
            $request->validated()
        );

        return $this->success(
            data: new TaskResource($task),
            message: 'Task created successfully.',
            status: 201
        );
    }

    public function show(
        Request $request,
        int $project,
        int $task
    ): JsonResponse {
        $task = $this->taskService->show(
            $request->user(),
            $project,
            $task
        );

        return $this->success(
            data: new TaskResource($task),
            message: 'Task retrieved successfully.'
        );
    }

    public function update(
        UpdateTaskRequest $request,
        int $project,
        int $task
    ): JsonResponse {
        $task = $this->taskService->update(
            $request->user(),
            $project,
            $task,
            $request->validated()
        );

        return $this->success(
            data: new TaskResource($task),
            message: 'Task updated successfully.'
        );
    }

    public function destroy(
        Request $request,
        int $project,
        int $task
    ): JsonResponse {
        $this->taskService->delete(
            $request->user(),
            $project,
            $task
        );

        return $this->success(
            message: 'Task deleted successfully.'
        );
    }
}