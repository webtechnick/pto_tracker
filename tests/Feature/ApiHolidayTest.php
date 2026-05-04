<?php

namespace Tests\Feature;

use App\Holiday;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiHolidayTest extends TestCase
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
    public function it_requires_start_date_and_end_date()
    {
        $response = $this->getJson('/api/holidays', $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    /** @test */
    public function it_validates_end_date_is_after_or_equal_to_start_date()
    {
        $response = $this->getJson('/api/holidays?' . http_build_query([
            'start_date' => '2026-04-15',
            'end_date'   => '2026-04-10',
        ]), $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_returns_holidays_in_date_range()
    {
        Holiday::create(['title' => 'Independence Day', 'date' => '2026-07-03', 'is_half_day' => false]);
        Holiday::create(['title' => 'Christmas', 'date' => '2026-12-25', 'is_half_day' => false]);

        $response = $this->getJson('/api/holidays?' . http_build_query([
            'start_date' => '2026-07-01',
            'end_date'   => '2026-07-31',
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'Independence Day')
                 ->assertJsonPath('meta.total_holidays', 1)
                 ->assertJsonPath('meta.total_days', 1.0);
    }

    /** @test */
    public function it_counts_half_day_holidays_as_half()
    {
        Holiday::create(['title' => 'Summer Hours', 'date' => '2026-06-05', 'is_half_day' => true]);
        Holiday::create(['title' => 'Summer Hours', 'date' => '2026-06-12', 'is_half_day' => true]);
        Holiday::create(['title' => 'Juneteenth', 'date' => '2026-06-19', 'is_half_day' => false]);

        $response = $this->getJson('/api/holidays?' . http_build_query([
            'start_date' => '2026-06-01',
            'end_date'   => '2026-06-30',
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data')
                 ->assertJsonPath('meta.total_holidays', 3)
                 ->assertJsonPath('meta.total_days', 2.0);
    }

    /** @test */
    public function it_returns_holidays_ordered_by_date()
    {
        Holiday::create(['title' => 'Second', 'date' => '2026-07-10', 'is_half_day' => false]);
        Holiday::create(['title' => 'First', 'date' => '2026-07-03', 'is_half_day' => false]);

        $response = $this->getJson('/api/holidays?' . http_build_query([
            'start_date' => '2026-07-01',
            'end_date'   => '2026-07-31',
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonPath('data.0.title', 'First')
                 ->assertJsonPath('data.1.title', 'Second');
    }

    /** @test */
    public function it_returns_empty_when_no_holidays_in_range()
    {
        Holiday::create(['title' => 'Christmas', 'date' => '2026-12-25', 'is_half_day' => false]);

        $response = $this->getJson('/api/holidays?' . http_build_query([
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-31',
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonCount(0, 'data')
                 ->assertJsonPath('meta.total_holidays', 0)
                 ->assertJsonPath('meta.total_days', 0.0);
    }

    /** @test */
    public function it_returns_expected_response_shape()
    {
        Holiday::create(['title' => 'Test Holiday', 'date' => '2026-05-01', 'is_half_day' => false]);

        $response = $this->getJson('/api/holidays?' . http_build_query([
            'start_date' => '2026-05-01',
            'end_date'   => '2026-05-31',
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [['id', 'title', 'date', 'is_half_day', 'days']],
                     'meta' => ['total_days', 'total_holidays'],
                 ]);
    }

    /** @test */
    public function it_requires_auth()
    {
        $response = $this->getJson('/api/holidays?' . http_build_query([
            'start_date' => '2026-05-01',
            'end_date'   => '2026-05-31',
        ]));

        $response->assertStatus(401);
    }
}
