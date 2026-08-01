<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repository
    ) {
    }

    public function statistics(User $user): array
    {
        return array_merge(
            $this->repository->getProjectStatistics($user->id),
            $this->repository->getTaskStatistics($user->id),
        );
    }
}