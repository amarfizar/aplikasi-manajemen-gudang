<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::create(array_merge([
            'name' => 'Test '.$role,
            'email' => $role.'@test.local',
            'password' => Hash::make('password'),
            'status' => 'active',
        ], $attributes));
        $user->assignRole($role);

        return $user;
    }

    protected function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer '.$this->tokenFor($user)];
    }
}
