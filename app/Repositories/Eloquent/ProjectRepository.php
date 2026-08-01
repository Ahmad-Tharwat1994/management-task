<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Project::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function findByIdAndUser(int $projectId, int $userId): ?Project
    {
        return Project::query()
            ->whereKey($projectId)
            ->where('user_id', $userId)
            ->first();
    }

    public function update(Project $project, array $data): bool
    {
        return $project->update($data);
    }

    public function delete(Project $project): bool
    {
        return (bool) $project->delete();
    }
}