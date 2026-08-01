<?php

namespace Tests\Feature\Dashboard;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    #[Test]
    public function guest_cannot_access_dashboard(): void
    {
        $this->getJson('/api/dashboard')
            ->assertUnauthorized();
    }

    #[Test]
    public function dashboard_returns_correct_statistics(): void
    {
        $user = $this->actingAsUser();

        $activeProject = Project::factory()->create([
            'user_id' => $user->id,
            'status' => ProjectStatus::Active,
        ]);

        $completedProject = Project::factory()->create([
            'user_id' => $user->id,
            'status' => ProjectStatus::Completed,
        ]);

        Task::factory()->create([
            'project_id' => $activeProject->id,
            'status' => TaskStatus::Done,
            'due_date' => now()->subDay(),
        ]);

        Task::factory()->create([
            'project_id' => $activeProject->id,
            'status' => TaskStatus::Todo,
            'due_date' => now()->addDays(2),
        ]);

        Task::factory()->create([
            'project_id' => $completedProject->id,
            'status' => TaskStatus::InProgress,
            'due_date' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/dashboard');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Dashboard statistics retrieved successfully.',
                'data' => [
                    'total_projects' => 2,
                    'active_projects' => 1,
                    'total_tasks' => 3,
                    'completed_tasks' => 1,
                    'pending_tasks' => 2,
                    'overdue_tasks' => 1,
                ],
            ]);
    }

    #[Test]
    public function dashboard_does_not_include_other_users_statistics(): void
    {
        $user = $this->actingAsUser();

        $anotherUser = User::factory()->create();

        Project::factory()->create([
            'user_id' => $anotherUser->id,
            'status' => ProjectStatus::Active,
        ]);

        $response = $this->getJson('/api/dashboard');

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'total_projects' => 0,
                    'active_projects' => 0,
                    'total_tasks' => 0,
                    'completed_tasks' => 0,
                    'pending_tasks' => 0,
                    'overdue_tasks' => 0,
                ],
            ]);
    }

}   