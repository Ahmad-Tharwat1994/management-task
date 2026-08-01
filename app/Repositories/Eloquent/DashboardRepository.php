<?php

namespace App\Repositories\Eloquent;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\Interfaces\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getProjectStatistics(int $userId): array
    {
        $statistics = Project::query()
            ->where('user_id', $userId)
            ->selectRaw(
                '
                COUNT(*) AS total_projects,
                SUM(status = ?) AS active_projects
                ',
                [
                    ProjectStatus::Active->value,
                ]
            )
            ->first();

        return [
            'total_projects' => (int) $statistics->total_projects,
            'active_projects' => (int) $statistics->active_projects,
        ];
    }

    public function getTaskStatistics(int $userId): array
    {
        $statistics = Task::query()
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->where('projects.user_id', $userId)
            ->selectRaw(
                '
                COUNT(*) AS total_tasks,

                SUM(tasks.status = ?) AS completed_tasks,

                SUM(tasks.status <> ?) AS pending_tasks,

                SUM(
                    tasks.status <> ?
                    AND tasks.due_date < CURDATE()
                ) AS overdue_tasks
                ',
                [
                    TaskStatus::Done->value,
                    TaskStatus::Done->value,
                    TaskStatus::Done->value,
                ]
            )
            ->first();

        return [
            'total_tasks' => (int) $statistics->total_tasks,
            'completed_tasks' => (int) $statistics->completed_tasks,
            'pending_tasks' => (int) $statistics->pending_tasks,
            'overdue_tasks' => (int) $statistics->overdue_tasks,
        ];
    }
}