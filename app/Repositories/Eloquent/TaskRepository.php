<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    public function paginateByProject(
        int $projectId,
        array $filters,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Task::query()
            ->where('project_id', $projectId)
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['priority'] ?? null,
                fn ($query, $priority) => $query->where('priority', $priority)
            )
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) => $query->where(
                    'title',
                    'like',
                    "%{$search}%"
                )
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function findByIdAndProject(
        int $taskId,
        int $projectId
    ): ?Task {
        return Task::query()
            ->whereKey($taskId)
            ->where('project_id', $projectId)
            ->first();
    }

    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    public function delete(Task $task): bool
    {
        return (bool) $task->delete();
    }
}