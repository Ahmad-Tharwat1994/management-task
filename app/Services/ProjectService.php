<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Exceptions\ProjectNotFoundException;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $repository
    ) {
    }

    public function list(User $user)
    {
        return $this->repository->paginateByUser($user->id);
    }

    public function create(User $user, array $data): Project
    {
        $data['user_id'] = $user->id;

        return $this->repository->create($data);
    }

    public function show(User $user, int $projectId): Project
    {
        return $this->findProject($user, $projectId);
    }

    public function update(User $user, int $projectId, array $data): Project
    {
        $project = $this->findProject($user, $projectId);

        $this->repository->update($project, $data);

        return $project->refresh();
    }

    public function delete(User $user, int $projectId): void
    {
        $project = $this->findProject($user, $projectId);

        $this->repository->delete($project);
    }

    private function findProject(User $user, int $projectId): Project
    {
        $project = $this->repository->findByIdAndUser(
            $projectId,
            $user->id
        );

        if (! $project) {
            throw new ProjectNotFoundException('Project not found.');
        }

        return $project;
    }
}