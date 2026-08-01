<?php

namespace Tests\Unit\Services;

use App\Exceptions\TaskNotFoundException;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Services\ProjectService;
use App\Services\TaskService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    private TaskRepositoryInterface $repository;

    private ProjectService $projectService;

    private TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TaskRepositoryInterface::class);

        $this->projectService = Mockery::mock(ProjectService::class);

        $this->service = new TaskService(
            $this->repository,
            $this->projectService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    #[Test]
    public function it_creates_task(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->id = 5;

        $task = new Task([
            'title' => 'Learn Laravel',
        ]);

        $this->projectService
            ->shouldReceive('show')
            ->once()
            ->with($user, 5)
            ->andReturn($project);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($task);

        $result = $this->service->create(
            $user,
            5,
            [
                'title' => 'Learn Laravel',
                'priority' => 'high',
                'status' => 'todo',
            ]
        );

        $this->assertSame($task, $result);
    }

    #[Test]
    public function it_lists_project_tasks(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->id = 5;

        $filters = [
            'status' => 'todo',
        ];

        $paginator = Mockery::mock(
            \Illuminate\Contracts\Pagination\LengthAwarePaginator::class
        );

        $this->projectService
            ->shouldReceive('show')
            ->once()
            ->with($user, 5)
            ->andReturn($project);

        $this->repository
            ->shouldReceive('paginateByProject')
            ->once()
            ->with(5, $filters)
            ->andReturn($paginator);

        $result = $this->service->list(
            $user,
            5,
            $filters
        );

        $this->assertSame($paginator, $result);
    }

    #[Test]
    public function it_returns_task(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->id = 5;

        $task = new Task();

        $this->projectService
            ->shouldReceive('show')
            ->once()
            ->andReturn($project);

        $this->repository
            ->shouldReceive('findByIdAndProject')
            ->once()
            ->with(10, 5)
            ->andReturn($task);

        $this->assertSame(
            $task,
            $this->service->show($user, 5, 10)
        );
    }

    #[Test]
    public function it_updates_task(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->id = 5;

        $task = Mockery::mock(Task::class)->makePartial();

        $this->projectService
            ->shouldReceive('show')
            ->once()
            ->andReturn($project);

        $this->repository
            ->shouldReceive('findByIdAndProject')
            ->once()
            ->andReturn($task);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($task, [
                'title' => 'Updated',
            ])
            ->andReturnTrue();

        $task
            ->shouldReceive('refresh')
            ->once()
            ->andReturnSelf();

        $result = $this->service->update(
            $user,
            5,
            1,
            [
                'title' => 'Updated',
            ]
        );

        $this->assertSame($task, $result);
    }

    #[Test]
    public function it_deletes_task(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->id = 5;

        $task = new Task();

        $this->projectService
            ->shouldReceive('show')
            ->once()
            ->andReturn($project);

        $this->repository
            ->shouldReceive('findByIdAndProject')
            ->once()
            ->andReturn($task);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with($task)
            ->andReturnTrue();

        $this->service->delete($user, 5, 1);

        $this->assertTrue(true);
    }
    
    #[Test]
    public function it_throws_exception_when_task_not_found(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->id = 5;

        $this->projectService
            ->shouldReceive('show')
            ->once()
            ->andReturn($project);

        $this->repository
            ->shouldReceive('findByIdAndProject')
            ->once()
            ->andReturnNull();

        $this->expectException(TaskNotFoundException::class);

        $this->service->show($user, 5, 100);
    }
}