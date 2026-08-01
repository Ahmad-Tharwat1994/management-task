<?php

namespace App\Repositories\Interfaces;

interface DashboardRepositoryInterface
{
    public function getProjectStatistics(int $userId): array;

    public function getTaskStatistics(int $userId): array;
}