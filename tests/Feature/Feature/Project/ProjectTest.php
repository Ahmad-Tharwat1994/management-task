<?php

namespace Tests\Feature\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    private function validData(array $override = []): array
    {
        return array_merge([
            'name' => 'Task Management API',
            'description' => 'Project Description',
            'status' => 'active',
        ], $override);
    }

    public function test_user_can_create_project(): void
    {
        $user = $this->actingAsUser();

        $response = $this->postJson(
            '/api/projects',
            $this->validData()
        );

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Project created successfully.');

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'name' => 'Task Management API',
        ]);
    }

    public function test_user_can_list_his_projects(): void
    {
        $user = $this->actingAsUser();

        Project::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson('/api/projects');

        $response
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_can_view_his_project(): void
    {
        $user = $this->actingAsUser();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson(
            "/api/projects/{$project->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_can_update_his_project(): void
    {
        $user = $this->actingAsUser();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson(
            "/api/projects/{$project->id}",
            $this->validData([
                'name' => 'Updated Project',
            ])
        );

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Project updated successfully.');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project',
        ]);
    }

    public function test_user_can_delete_his_project(): void
    {
        $user = $this->actingAsUser();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson(
            "/api/projects/{$project->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Project deleted successfully.');

        $this->assertSoftDeleted($project);
    }
    public function test_user_cannot_view_another_users_project(): void
    {
        $this->actingAsUser();

        $project = Project::factory()->create();

        $response = $this->getJson(
            "/api/projects/{$project->id}"
        );

        $response->assertNotFound();
    }

    public function test_guest_cannot_access_projects(): void
    {
        $response = $this->getJson('/api/projects');

        $response->assertUnauthorized();
    }

    public function test_name_is_required(): void
    {
        $this->actingAsUser();

        $response = $this->postJson(
            '/api/projects',
            $this->validData([
                'name' => null,
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_status_must_be_valid(): void
    {
        $this->actingAsUser();

        $response = $this->postJson(
            '/api/projects',
            $this->validData([
                'status' => 'invalid-status',
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_description_must_be_string(): void
    {
        $this->actingAsUser();

        $response = $this->postJson(
            '/api/projects',
            $this->validData([
                'description' => ['array'],
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('description');
    }

    public function test_user_cannot_update_another_users_project(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $anotherUser = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $response = $this->putJson(
            "/api/projects/{$project->id}",
            $this->validData([
                'name' => 'Hacked Project',
            ])
        );

        $response->assertNotFound();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
            'name' => 'Hacked Project',
        ]);
    }

    public function test_user_cannot_delete_another_users_project(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $anotherUser = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $response = $this->deleteJson(
            "/api/projects/{$project->id}"
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'deleted_at' => null,
        ]);
    }

    public function test_update_requires_name(): void
    {
        $user = $this->actingAsUser();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson(
            "/api/projects/{$project->id}",
            $this->validData([
                'name' => null,
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }
    
    public function test_update_rejects_invalid_status(): void
    {
        $user = $this->actingAsUser();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson(
            "/api/projects/{$project->id}",
            $this->validData([
                'status' => 'invalid-status',
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }
}