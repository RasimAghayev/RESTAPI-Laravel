<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @testdox TC_HEALTH_LIVE_001 GET /api/health/live returns 200 with status ok */
    public function test_health_live_001_liveness_returns_ok(): void
    {
        $response = $this->getJson('/api/health/live');

        $response->assertStatus(200)->assertJson(['status' => 'ok']);
    }

    /** @testdox TC_HEALTH_READY_001 GET /api/health/ready returns ok when the database is available */
    public function test_health_ready_001_readiness_returns_ok_when_db_available(): void
    {
        $response = $this->getJson('/api/health/ready');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database', 'ok');
    }
}
