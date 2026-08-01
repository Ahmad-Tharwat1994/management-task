<?php

namespace App\Services;

use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\TaskRepositoryInterface;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectService $projectService,
    ) {
    }

    public function list(User $user, int $projectId, array $filters)
    {
        $project = $this->projectService->show($user, $projectId);

        return $this->taskRepository->paginateByProject(
            $project->id,
            $filters
        );
    }

    public function create(User $user, int $projectId, array $data): Task
    {
        $project = $this->projectService->show($user, $projectId);

        $data['project_id'] = $project->id;

        return $this->taskRepository->create($data);
    }

    public function show(User $user, int $projectId, int $taskId): Task
    {
        $project = $this->projectService->show($user, $projectId);

        return $this->findTask($project->id, $taskId);
    }

    public function update(
        User $user,
        int $projectId,
        int $taskId,
        array $data
    ): Task {
        $project = $this->projectService->show($user, $projectId);

        $task = $this->findTask($project->id, $taskId);

        $this->taskRepository->update($task, $data);

        return $task->refresh();
    }

    public function delete(
        User $user,
        int $projectId,
        int $taskId
    ): void {
        $project = $this->projectService->show($user, $projectId);

        $task = $this->findTask($project->id, $taskId);

        $this->taskRepository->delete($task);
    }

    private function findTask(
        int $projectId,
        int $taskId
    ): Task {
        $task = $this->taskRepository->findByIdAndProject(
            $taskId,
            $projectId
        );

        if (! $task) {
            throw new TaskNotFoundException('Task not found.');
        }

        return $task;
    }
}