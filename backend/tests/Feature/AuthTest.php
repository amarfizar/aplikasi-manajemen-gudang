<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Support\Facades\Hash;

class AuthTest extends FeatureTestCase
{
    public function test_login_success_returns_token_and_user(): void
    {
        $user = $this->createUserWithRole('super-admin', ['email' => 'admin@test.local']);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.local',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['email', 'roles', 'permissions']]]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createUserWithRole('super-admin', ['email' => 'admin@test.local']);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.local',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = $this->createUserWithRole('viewer', ['email' => 'viewer@test.local']);

        $response = $this->getJson('/api/me', $this->authHeader($user));

        $response->assertOk()->assertJsonPath('data.user.email', 'viewer@test.local');
    }

    public function test_logout_revokes_token(): void
    {
        $user = $this->createUserWithRole('viewer');
        $token = $this->tokenFor($user);

        $this->postJson('/api/logout', [], ['Authorization' => 'Bearer '.$token])->assertOk();

        app(Factory::class)->forgetGuards();

        $this->getJson('/api/me', ['Authorization' => 'Bearer '.$token])->assertStatus(401);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'name' => 'Inactive',
            'email' => 'inactive@test.local',
            'password' => Hash::make('password'),
            'status' => 'inactive',
        ]);

        $this->postJson('/api/login', ['email' => 'inactive@test.local', 'password' => 'password'])
            ->assertStatus(403);
    }
}
