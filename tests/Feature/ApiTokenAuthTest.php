<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTokenAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_token' => 'test-token-abc123']);
    }

    /** @test */
    public function it_rejects_requests_without_a_bearer_token()
    {
        $response = $this->getJson('/api/employees');

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Unauthorized']);
    }

    /** @test */
    public function it_rejects_requests_with_an_invalid_token()
    {
        $response = $this->getJson('/api/employees', [
            'Authorization' => 'Bearer wrong-token',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Unauthorized']);
    }

    /** @test */
    public function it_allows_requests_with_a_valid_token()
    {
        $response = $this->getJson('/api/employees', [
            'Authorization' => 'Bearer test-token-abc123',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_rejects_requests_when_no_token_is_configured()
    {
        config(['app.api_token' => null]);

        $response = $this->getJson('/api/employees', [
            'Authorization' => 'Bearer anything',
        ]);

        $response->assertStatus(401);
    }
}
