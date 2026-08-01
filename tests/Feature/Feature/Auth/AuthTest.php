<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user',
                ],
            ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Logged out successfully.',
            ]);
    }

    /** @test */
    public function guest_cannot_logout()
    {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized();
    }

    /** @test */
    public function register_requires_name()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'ahmed@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function test_register_name_must_be_string(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 123,
            'email' => 'ahmed@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    /** @test */
    public function test_register_name_must_not_exceed_max_length(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => str_repeat('A', 256),
            'email' => 'ahmed@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    /** @test */
    public function test_register_requires_email(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_register_requires_valid_email(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_register_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'ahmed@test.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }


    public function test_register_requires_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@test.com',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }


    public function test_register_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@test.com',
            'password' => 'password',
            'password_confirmation' => 'another-password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_register_password_must_have_minimum_length(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@test.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_login_requires_valid_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'ahmed@test.com',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ahmed@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'ahmed@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
    }

    public function test_login_fails_when_email_does_not_exist(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'unknown@test.com',
            'password' => 'password',
        ]);

        $response->assertUnprocessable();
    }
}