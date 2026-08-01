<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ProjectService $projectService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectService->list($request->user());

        return $this->success(
            data: ProjectResource::collection($projects),
            message: 'Projects retrieved successfully.'
        );
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create(
            $request->user(),
            $request->validated()
        );

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project created successfully.',
            status: 201
        );
    }

    public function show(Request $request, int $project): JsonResponse
    {
        $project = $this->projectService->show(
            $request->user(),
            $project
        );

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project retrieved successfully.'
        );
    }

    public function update(
        UpdateProjectRequest $request,
        int $project
    ): JsonResponse {
        $project = $this->projectService->update(
            $request->user(),
            $project,
            $request->validated()
        );

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project updated successfully.'
        );
    }

    public function destroy(Request $request, int $project): JsonResponse
    {
        $this->projectService->delete(
            $request->user(),
            $project
        );

        return $this->success(
            message: 'Project deleted successfully.'
        );
    }
}