<?php

namespace Tests\Feature;

use App\Employee;
use App\PaidTimeOff;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTimeOffTest extends TestCase
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
        $response = $this->getJson('/api/time-off', $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    /** @test */
    public function it_validates_end_date_is_after_or_equal_to_start_date()
    {
        $response = $this->getJson('/api/time-off?' . http_build_query([
            'start_date' => '2026-04-15',
            'end_date'   => '2026-04-10',
        ]), $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_returns_approved_pto_in_date_range()
    {
        $employee = $this->create(Employee::class);

        // Approved PTO in range
        $this->create(PaidTimeOff::class, [
            'employee_id' => $employee->id,
            'start_time'  => '2026-04-07',
            'end_time'    => '2026-04-09',
            'is_approved' => true,
            'is_half_day' => false,
            'days'        => 3,
            'description' => 'Vacation',
        ]);

        // Pending PTO in range (should be excluded)
        $this->create(PaidTimeOff::class, [
            'employee_id' => $employee->id,
            'start_time'  => '2026-04-10',
            'end_time'    => '2026-04-10',
            'is_approved' => false,
            'is_half_day' => false,
            'days'        => 1,
            'description' => 'Pending day',
        ]);

        $response = $this->getJson('/api/time-off?' . http_build_query([
            'start_date' => '2026-04-01',
            'end_date'   => '2026-04-30',
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.employee_id', $employee->id)
                 ->assertJsonPath('data.0.pto_days', 3.0)
                 ->assertJsonCount(1, 'data.0.entries');
    }

    /** @test */
    public function it_excludes_pto_outside_date_range()
    {
        $employee = $this->create(Employee::class);

        $this->create(PaidTimeOff::class, [
            'employee_id' => $employee->id,
            'start_time'  => '2026-03-01',
            'end_time'    => '2026-03-03',
            'is_approved' => true,
            'days'        => 3,
        ]);

        $response = $this->getJson('/api/time-off?' . http_build_query([
            'start_date' => '2026-04-01',
            'end_date'   => '2026-04-30',
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function it_filters_by_employee_ids()
    {
        $employee1 = $this->create(Employee::class);
        $employee2 = $this->create(Employee::class);

        $this->create(PaidTimeOff::class, [
            'employee_id' => $employee1->id,
            'start_time'  => '2026-04-07',
            'end_time'    => '2026-04-07',
            'is_approved' => true,
            'days'        => 1,
        ]);

        $this->create(PaidTimeOff::class, [
            'employee_id' => $employee2->id,
            'start_time'  => '2026-04-08',
            'end_time'    => '2026-04-08',
            'is_approved' => true,
            'days'        => 1,
        ]);

        $response = $this->getJson('/api/time-off?' . http_build_query([
            'start_date'    => '2026-04-01',
            'end_date'      => '2026-04-30',
            'employee_ids'  => [$employee1->id],
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.employee_id', $employee1->id);
    }

    /** @test */
    public function it_filters_by_employee_names_case_insensitively()
    {
        $employee = $this->create(Employee::class, ['name' => 'Jane Doe']);

        $this->create(PaidTimeOff::class, [
            'employee_id' => $employee->id,
            'start_time'  => '2026-04-07',
            'end_time'    => '2026-04-07',
            'is_approved' => true,
            'days'        => 1,
        ]);

        $response = $this->getJson('/api/time-off?' . http_build_query([
            'start_date'     => '2026-04-01',
            'end_date'       => '2026-04-30',
            'employee_names' => ['jane doe'],
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.employee_name', 'Jane Doe');
    }

    /** @test */
    public function it_sums_pto_days_including_half_days()
    {
        $employee = $this->create(Employee::class);

        $this->create(PaidTimeOff::class, [
            'employee_id' => $employee->id,
            'start_time'  => '2026-04-07',
            'end_time'    => '2026-04-08',
            'is_approved' => true,
            'is_half_day' => false,
            'days'        => 2,
        ]);

        $this->create(PaidTimeOff::class, [
            'employee_id' => $employee->id,
            'start_time'  => '2026-04-10',
            'end_time'    => '2026-04-10',
            'is_approved' => true,
            'is_half_day' => true,
            'days'        => 0.5,
        ]);

        $response = $this->getJson('/api/time-off?' . http_build_query([
            'start_date' => '2026-04-01',
            'end_date'   => '2026-04-30',
        ]), $this->headers);

        $response->assertStatus(200)
                 ->assertJsonPath('data.0.pto_days', 2.5)
                 ->assertJsonCount(2, 'data.0.entries');
    }
}
