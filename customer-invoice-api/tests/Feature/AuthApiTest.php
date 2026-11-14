<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @testdox TC_AUTH_USER_001 Unauthenticated GET /api/user returns 401 */
    public function test_auth_user_001_unauthenticated_user_endpoint_returns_401(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    /** @testdox TC_AUTH_USER_002 Authenticated Sanctum GET /api/user returns the user */
    public function test_auth_user_002_authenticated_user_endpoint_returns_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');

        $response->assertStatus(200)->assertJsonFragment(['email' => $user->email]);
    }
}
