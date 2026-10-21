<?php

namespace Tests\Feature;

use App\Employee;
use App\PaidTimeOff;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PtoRequestAndAuthorizationTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function ajax_pto_request_returns_the_created_pto_with_legacy_dates()
    {
        Mail::fake();
        $employee = $this->create(Employee::class);

        // The calendar's datepicker posts ISO strings; only the date part is kept.
        $this->postJson('/ptos/store', [
            'employee_id' => $employee->id,
            'start_time' => '2035-03-06T05:00:00.000Z',
            'end_time' => '2035-03-08T05:00:00.000Z',
        ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertCreated()
            ->assertJsonPath('start_time', '2035-03-06 00:00:00')
            ->assertJsonPath('end_time', '2035-03-08 00:00:00')
            ->assertJsonPath('days', 3);
    }

    #[Test]
    public function ajax_pto_request_validation_errors_are_json()
    {
        $this->postJson('/ptos/store', ['employee_id' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_time', 'end_time', 'employee_id']);
    }

    #[Test]
    public function only_managers_and_admins_can_approve()
    {
        $employee = $this->create(Employee::class);
        $pto = $this->create(PaidTimeOff::class, ['employee_id' => $employee->id]);

        $this->post("/ptos/approve/{$pto->id}")->assertRedirect('/login');

        $this->signInPlanner();
        $this->post("/ptos/approve/{$pto->id}")->assertRedirect('/login');

        $this->signInEmployee($employee);
        $this->post("/ptos/approve/{$pto->id}")->assertRedirect('/login');

        $this->assertFalse($pto->fresh()->is_approved);
    }

    #[Test]
    public function employee_can_cancel_only_their_future_pto()
    {
        Mail::fake();
        $employee = $this->create(Employee::class);
        $future = $this->create(PaidTimeOff::class, ['employee_id' => $employee->id, 'start_time' => '2035-03-06', 'end_time' => '2035-03-06']);
        $past = $this->create(PaidTimeOff::class, ['employee_id' => $employee->id, 'start_time' => '2020-03-06', 'end_time' => '2020-03-06']);
        $this->signInEmployee($employee);

        $this->post("/ptos/owner-destroy/{$past->id}")->assertForbidden();
        $this->post("/ptos/owner-destroy/{$future->id}")->assertOk();

        $this->assertNotNull($past->fresh());
        $this->assertNull($future->fresh());
    }
}
