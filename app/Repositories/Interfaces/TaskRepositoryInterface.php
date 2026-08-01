<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function paginateByProject(
        int $projectId,
        array $filters,
        int $perPage = 15
    ): LengthAwarePaginator;

    public function create(array $data): Task;

    public function findByIdAndProject(
        int $taskId,
        int $projectId
    ): ?Task;

    public function update(Task $task, array $data): bool;

    public function delete(Task $task): bool;
}