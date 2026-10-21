<?php

namespace Tests\Feature;

use App\Employee;
use App\Holiday;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewFollowUpTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function bulk_holiday_csv_parses_quoted_fields_without_php_deprecations()
    {
        $this->withoutDeprecationHandling();
        $this->signInAdmin();

        $this->post(route('admin.holidays.bulkstore'), [
            'bulk' => "\"Holiday, Observed\",2035-01-02\nSummer Hours,2035-07-06,1",
        ])->assertRedirect(route('admin.holidays'));

        $this->assertTrue(Holiday::where('title', 'Holiday, Observed')->where('date', '2035-01-02')->exists());
        $this->assertTrue(Holiday::where('title', 'Summer Hours')->first()->isHalfDay());
    }

    #[Test]
    public function api_does_not_answer_cross_origin_requests()
    {
        config(['app.api_token' => 'token']);

        $preflight = $this->call('OPTIONS', '/api/employees', [], [], [], [
            'HTTP_ORIGIN' => 'https://evil.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ]);
        $this->assertFalse($preflight->headers->has('Access-Control-Allow-Origin'));

        $response = $this->getJson('/api/employees', [
            'Origin' => 'https://evil.example',
            'Authorization' => 'Bearer token',
        ]);
        $response->assertOk();
        $this->assertFalse($response->headers->has('Access-Control-Allow-Origin'));
    }

    #[Test]
    public function manager_cannot_blank_an_employee_name()
    {
        $this->signInManager();
        $employee = $this->create(Employee::class, ['name' => 'Keep Me']);

        $this->from(route('manager.employees.edit', $employee))
            ->post(route('manager.employees.update', $employee), ['name' => '', 'color' => 'white', 'bgcolor' => 'green'])
            ->assertRedirect(route('manager.employees.edit', $employee))
            ->assertSessionHasErrors('name');

        $this->assertSame('Keep Me', $employee->fresh()->name);
    }

    #[Test]
    public function holiday_update_requires_title_and_date()
    {
        $this->signInAdmin();
        $holiday = Holiday::create(['title' => 'Christmas', 'date' => '2035-12-25']);

        $this->post(route('admin.holidays.update', $holiday), ['title' => '', 'date' => ''])
            ->assertSessionHasErrors(['title', 'date']);

        $this->assertSame('2035-12-25', $holiday->fresh()->date);
    }
}
