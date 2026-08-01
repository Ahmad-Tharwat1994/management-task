<?php

namespace App\Repositories\Interfaces;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Project;

    public function findByIdAndUser(int $projectId, int $userId): ?Project;

    public function update(Project $project, array $data): bool;

    public function delete(Project $project): bool;
}