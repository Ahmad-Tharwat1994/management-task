<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Validation\ValidationException;

class AuthServiceTest extends TestCase
{
    #[Test]
    public function it_registers_a_new_user()
    {
        $repository = Mockery::mock(AuthRepositoryInterface::class);

        $service = new AuthService($repository);

        $user = Mockery::mock(User::class)->makePartial();

        $user->name = 'Ahmed';
        $user->email = 'ahmed@test.com';

        $repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($user);

        $user
            ->shouldReceive('createToken')
            ->once()
            ->andReturn(
                new class {
                    public string $plainTextToken = 'fake-token';
                }
            );

        $result = $service->register([
            'name' => 'Ahmed',
            'email' => 'ahmed@test.com',
            'password' => 'password',
        ]);

        $this->assertSame($user, $result['user']);
        $this->assertEquals('fake-token', $result['token']);
    }

    #[Test]
    public function it_logs_in_an_existing_user()
    {
        $repository = Mockery::mock(AuthRepositoryInterface::class);

        $service = new AuthService($repository);

        $user = Mockery::mock(User::class)->makePartial();

        $user->password = Hash::make('password');

        $repository
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturn($user);

        $user
            ->shouldReceive('createToken')
            ->once()
            ->andReturn(
                new class {
                    public string $plainTextToken = 'fake-token';
                }
            );

        $result = $service->login([
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        $this->assertEquals('fake-token', $result['token']);
    }

    #[Test]
    public function it_throws_exception_when_credentials_are_invalid()
    {
        $repository = Mockery::mock(AuthRepositoryInterface::class);

        $service = new AuthService($repository);

        $repository
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturnNull();

        $this->expectException(ValidationException::class);

        $service->login([
            'email' => 'wrong@test.com',
            'password' => '123456',
        ]);
    }
}