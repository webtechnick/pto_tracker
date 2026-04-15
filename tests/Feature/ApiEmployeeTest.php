<?php

namespace Tests\Feature;

use App\Employee;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiEmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_token' => 'test-token']);
        $this->headers = ['Authorization' => 'Bearer test-token'];
    }

    /** @test */
    public function it_returns_employees_with_expected_shape()
    {
        $this->create(Employee::class, ['name' => 'Jane Doe']);

        $response = $this->getJson('/api/employees', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [['id', 'name', 'active', 'is_contractor']],
                     'meta' => ['total', 'per_page', 'current_page'],
                 ])
                 ->assertJsonPath('data.0.name', 'Jane Doe')
                 ->assertJsonPath('data.0.active', true)
                 ->assertJsonPath('data.0.is_contractor', false);
    }

    /** @test */
    public function it_paginates_with_per_page_parameter()
    {
        $this->create(Employee::class, [], 3);

        $response = $this->getJson('/api/employees?per_page=2', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data')
                 ->assertJsonPath('meta.per_page', 2)
                 ->assertJsonPath('meta.total', 3);
    }

    /** @test */
    public function it_works_as_health_check_with_per_page_one()
    {
        $this->create(Employee::class);

        $response = $this->getJson('/api/employees?per_page=1', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('meta.per_page', 1);
    }

    /** @test */
    public function it_validates_per_page_is_positive_integer()
    {
        $response = $this->getJson('/api/employees?per_page=0', $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function it_validates_per_page_max()
    {
        $response = $this->getJson('/api/employees?per_page=501', $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function contractors_are_still_active_but_flagged()
    {
        $this->create(Employee::class, ['name' => 'Contractor Bob', 'is_contractor' => true]);

        $response = $this->getJson('/api/employees', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonPath('data.0.active', true)
                 ->assertJsonPath('data.0.is_contractor', true);
    }
}
