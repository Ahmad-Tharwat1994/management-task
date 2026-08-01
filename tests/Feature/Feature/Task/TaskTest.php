<?php

namespace Tests\Feature\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    private function createProject(User $user): Project
    {
        return Project::factory()->create([
            'user_id' => $user->id,
        ]);
    }

    private function createTask(Project $project, array $attributes = []): Task
    {
        return Task::factory()->create(array_merge([
            'project_id' => $project->id,
        ], $attributes));
    }

    private function validData(array $override = []): array
    {
        return array_merge([
            'title' => 'Build REST API',
            'description' => 'Implement Tasks Module',
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => now()->addWeek()->toDateString(),
        ], $override);
    }

    public function test_user_can_create_task(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/tasks",
            $this->validData()
        );

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Task created successfully.');

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Build REST API',
        ]);
    }

    public function test_user_can_list_project_tasks(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $this->createTask($project);
        $this->createTask($project);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks"
        );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_view_task(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $task = $this->createTask($project);

        $this->getJson("/api/projects/{$project->id}/tasks/{$task->id}")
            ->assertOk();
    }

    public function test_user_can_update_task(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $task = $this->createTask($project);

        $response = $this->putJson(
            "/api/projects/{$project->id}/tasks/{$task->id}",
            $this->validData([
                'title' => 'Updated Task',
            ])
        );

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Task updated successfully.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
        ]);
    }

    public function test_user_can_delete_task(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $task = $this->createTask($project);

        $this->deleteJson(
            "/api/projects/{$project->id}/tasks/{$task->id}"
        )
            ->assertOk();

        $this->assertSoftDeleted($task);
    }

    public function test_can_filter_tasks_by_status(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $this->createTask($project, ['status' => 'done']);
        $this->createTask($project, ['status' => 'todo']);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks?status=done"
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_tasks_by_priority(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $this->createTask($project, ['priority' => 'high']);
        $this->createTask($project, ['priority' => 'low']);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks?priority=high"
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
    public function test_can_search_tasks_by_title(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $this->createTask($project, [
            'title' => 'Laravel API',
        ]);

        $this->createTask($project, [
            'title' => 'Vue Dashboard',
        ]);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks?search=Laravel"
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_access_another_users_project_tasks(): void
    {
        $this->actingAsUser();

        $anotherUser = User::factory()->create();

        $project = $this->createProject($anotherUser);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks"
        );

        $response->assertNotFound();
    }
    public function test_guest_cannot_access_tasks(): void
    {
        $project = Project::factory()->create();

        $this->getJson("/api/projects/{$project->id}/tasks")
            ->assertUnauthorized();
    }

    public function test_title_is_required(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/tasks",
            $this->validData([
                'title' => null,
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_title_must_be_string(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/tasks",
            $this->validData([
                'title' => 12345,
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_title_must_not_exceed_max_length(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/tasks",
            $this->validData([
                'title' => str_repeat('A', 256),
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }
    public function test_priority_must_be_valid(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/tasks",
            $this->validData([
                'priority' => 'urgent',
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('priority');
    }

    public function test_status_must_be_valid(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/tasks",
            $this->validData([
                'status' => 'finished',
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_due_date_must_be_valid_date(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/tasks",
            $this->validData([
                'due_date' => 'invalid-date',
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('due_date');
    }

    public function test_due_date_cannot_be_in_the_past(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/tasks",
            $this->validData([
                'due_date' => now()->subDay()->toDateString(),
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('due_date');
    }

    public function test_update_requires_title(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $task = $this->createTask($project);

        $response = $this->putJson(
            "/api/projects/{$project->id}/tasks/{$task->id}",
            $this->validData([
                'title' => null,
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_update_rejects_invalid_priority(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $task = $this->createTask($project);

        $response = $this->putJson(
            "/api/projects/{$project->id}/tasks/{$task->id}",
            $this->validData([
                'priority' => 'urgent',
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('priority');
    }

    public function test_update_rejects_invalid_status(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $task = $this->createTask($project);

        $response = $this->putJson(
            "/api/projects/{$project->id}/tasks/{$task->id}",
            $this->validData([
                'status' => 'finished',
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_update_rejects_past_due_date(): void
    {
        $user = $this->actingAsUser();

        $project = $this->createProject($user);

        $task = $this->createTask($project);

        $response = $this->putJson(
            "/api/projects/{$project->id}/tasks/{$task->id}",
            $this->validData([
                'due_date' => now()->subDay()->toDateString(),
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('due_date');
    }

    public function test_user_cannot_update_task_in_another_users_project(): void
    {
        $user = $this->actingAsUser();

        $anotherUser = User::factory()->create();

        $project = $this->createProject($anotherUser);

        $task = $this->createTask($project);

        $response = $this->putJson(
            "/api/projects/{$project->id}/tasks/{$task->id}",
            $this->validData([
                'title' => 'Hacked Task',
            ])
        );

        $response->assertNotFound();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
            'title' => 'Hacked Task',
        ]);
    }
    
    public function test_user_cannot_delete_task_in_another_users_project(): void
    {
        $user = $this->actingAsUser();

        $anotherUser = User::factory()->create();

        $project = $this->createProject($anotherUser);

        $task = $this->createTask($project);

        $response = $this->deleteJson(
            "/api/projects/{$project->id}/tasks/{$task->id}"
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }
}