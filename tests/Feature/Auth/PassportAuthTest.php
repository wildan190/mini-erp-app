<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PassportAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_guard_is_configured_for_passport(): void
    {
        $this->assertSame('passport', config('auth.guards.platform.driver'));
        $this->assertSame('users', config('auth.guards.platform.provider'));
    }

    public function test_platform_login_returns_access_token_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email' => 'platform@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/platform/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['message', 'token', 'user']);
        $this->assertNotEmpty($response->json('token'));
    }
}
