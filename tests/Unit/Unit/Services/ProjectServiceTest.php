<?php

namespace Tests\Unit\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Services\ProjectService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    private ProjectRepositoryInterface $repository;

    private ProjectService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ProjectRepositoryInterface::class);

        $this->service = new ProjectService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }


    #[Test]
    public function it_creates_project(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project([
            'name' => 'API',
        ]);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($project);

        $result = $this->service->create($user, [
            'name' => 'API',
            'description' => null,
            'status' => 'active',
        ]);

        $this->assertSame($project, $result);
    }

    #[Test]
    public function it_lists_user_projects(): void
    {
        $user = new User();
        $user->id = 5;

        $paginator = Mockery::mock(
            \Illuminate\Contracts\Pagination\LengthAwarePaginator::class
        );

        $this->repository
            ->shouldReceive('paginateByUser')
            ->once()
            ->with(5)
            ->andReturn($paginator);

        $result = $this->service->list($user);

        $this->assertSame($paginator, $result);
    }
    #[Test]
    public function it_returns_project(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();

        $this->repository
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->with(10, 1)
            ->andReturn($project);

        $this->assertSame(
            $project,
            $this->service->show($user, 10)
        );
    }

    #[Test]
    public function it_updates_project(): void
    {
        $user = new User();
        $user->id = 1;

        $project = Mockery::mock(Project::class)->makePartial();

        $this->repository
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->andReturn($project);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($project, [
                'name' => 'New Name',
            ])
            ->andReturnTrue();

        $project
            ->shouldReceive('refresh')
            ->once()
            ->andReturnSelf();

        $result = $this->service->update(
            $user,
            1,
            [
                'name' => 'New Name',
            ]
        );

        $this->assertSame($project, $result);
    }

    #[Test]
    public function it_deletes_project(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();

        $this->repository
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->andReturn($project);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with($project)
            ->andReturnTrue();

        $this->service->delete($user, 1);

        $this->assertTrue(true);
    }
    #[Test]
    public function it_throws_exception_when_project_not_found(): void
    {
        $user = new User();
        $user->id = 1;

        $this->repository
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->andReturnNull();

        $this->expectException(ProjectNotFoundException::class);

        $this->service->show($user, 99);
    }
}