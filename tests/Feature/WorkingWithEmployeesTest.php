<?php

namespace Tests\Feature;

use App\Employee;
use App\Mail\OnCallDigest;
use App\PaidTimeOff;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkingWithEmployeesTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_remove_pto_when_deleting_employee()
    {
        $employee = $this->create('App\Employee');
        $pto = $this->create('App\PaidTimeOff', ['employee_id' => $employee->id]);

        $this->assertEquals(1, Employee::count());
        $this->assertEquals(1, PaidTimeOff::count());

        $employee->delete();

        $this->assertEquals(0, Employee::count());
        $this->assertEquals(0, PaidTimeOff::count());
    }

    /** @test */
    public function it_should_know_oncall_digest()
    {
        Mail::fake();

        $employee1 = $this->create('App\Employee', ['is_on_call' => true]);
        $employee2 = $this->create('App\Employee', ['is_on_call' => true]);
        $employee3 = $this->create('App\Employee', ['is_on_call' => false]);

        Mail::to('oncall@lacallegroup.com')->send(new OnCallDigest());

        Mail::assertSent(OnCallDigest::class, function($mail) use ($employee1, $employee2, $employee3) {
            $mail->build();

            $html = view($mail->view, $mail->viewData)->render();

            $this->assertContains($employee1->name, $html);
            $this->assertContains($employee2->name, $html);
            $this->assertNotContains($employee3->name, $html);

            return true;
        });
    }

    /** @test */
    public function managers_can_and_manage_all_employees()
    {
        $user = $this->signInManager();
        $employee1 = $this->create('App\Employee');

        $data = [
            'name' => 'New Name',
            'max_days_off' => '30'
        ];

        $response = $this->post('/manager/employees/1/update', $data);
        $response->assertRedirect('/manager');

        $employee = Employee::first();
        $this->assertEquals(1, Employee::count());
        $this->assertEquals($data['name'], $employee->name);
        $this->assertEquals((float) $data['max_days_off'], (float) $employee->max_days_off);
    }

    /** @test */
    public function it_should_not_be_able_to_manage_employees()
    {
        $user = $this->signIn();
        $employee1 = $this->create('App\Employee');

        $data = [
            'name' => 'New Name',
            'max_days_off' => '30'
        ];

        $response = $this->post('/manager/employees/1/update', $data);
        $response->assertRedirect('/login'); // not allowed

        $employee = Employee::first();
        $this->assertEquals(1, Employee::count());
        $this->assertNotEquals($data['name'], $employee->name);
        $this->assertNotEquals($data['max_days_off'], $employee->max_days_off);
    }

    /** @test */
    public function it_should_automatically_sync_to_employee_account_if_unclaimed_employee_with_same_name_exists()
    {
        $employee = $this->create('App\Employee', ['name' => 'John Smith']);

        $data = [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'password' => 'changeme',
            'password_confirmation' => 'changeme',
        ];

        $this->assertEquals(0, User::count());

        $response = $this->post('/register', $data);

        $user = User::first();

        $this->assertEquals(1, User::count());
        $this->assertEquals($user->employee_id, $employee->id);
    }

    /** @test */
    public function it_should_not_overwrite_already_claimed_employee_with_same_name()
    {
        $employee = $this->create('App\Employee', ['name' => 'John Smith']);
        $user = $this->create('App\User', ['employee_id' => $employee->id]);

        $data = [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'password' => 'changeme',
            'password_confirmation' => 'changeme',
        ];

        $this->assertEquals(1, User::count());
        $this->assertEquals($user->employee_id, $employee->id);

        $response = $this->post('/register', $data);

        $new_user = User::orderBy('id','DESC')->first();

        $this->assertEquals(2, User::count());
        $this->assertEquals($user->employee_id, $employee->id);
        $this->assertNotEquals($new_user->employee_id, $employee->id);
    }

    /** @test */
    public function employee_can_remove_their_own_future_pto()
    {
        $employee = $this->create('App\Employee');
        $user = $this->signInEmployee($employee);

        // Create a future PTO
        $futurePto = $this->create('App\PaidTimeOff', [
            'employee_id' => $employee->id,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(10),
        ]);

        $this->assertEquals(1, PaidTimeOff::count());

        // Employee can remove their own future PTO
        $this->assertTrue($employee->canRemovePto($futurePto));
        $this->assertTrue($futurePto->canRemove());

        // Make the delete request
        $response = $this->post('/ptos/owner-destroy/' . $futurePto->id);
        $response->assertStatus(200);

        $this->assertEquals(0, PaidTimeOff::count());
    }

    /** @test */
    public function employee_cannot_remove_their_own_past_pto()
    {
        $employee = $this->create('App\Employee');
        $user = $this->signInEmployee($employee);

        // Create a past PTO
        $pastPto = $this->create('App\PaidTimeOff', [
            'employee_id' => $employee->id,
            'start_time' => now()->subDays(10),
            'end_time' => now()->subDays(7),
        ]);

        $this->assertEquals(1, PaidTimeOff::count());

        // Employee cannot remove their own past PTO
        $this->assertFalse($employee->canRemovePto($pastPto));
        $this->assertFalse($pastPto->canRemove());

        // Make the delete request - should be forbidden
        $response = $this->post('/ptos/owner-destroy/' . $pastPto->id);
        $response->assertStatus(403);

        $this->assertEquals(1, PaidTimeOff::count());
    }

    /** @test */
    public function employee_cannot_remove_someone_elses_pto()
    {
        $employee1 = $this->create('App\Employee');
        $employee2 = $this->create('App\Employee');
        $user = $this->signInEmployee($employee1);

        // Create a future PTO for employee2
        $otherPto = $this->create('App\PaidTimeOff', [
            'employee_id' => $employee2->id,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(10),
        ]);

        $this->assertEquals(1, PaidTimeOff::count());

        // Employee1 cannot remove employee2's PTO
        $this->assertFalse($employee1->canRemovePto($otherPto));
        $this->assertFalse($otherPto->canRemove());

        // Make the delete request - should be forbidden
        $response = $this->post('/ptos/owner-destroy/' . $otherPto->id);
        $response->assertStatus(403);

        $this->assertEquals(1, PaidTimeOff::count());
    }

    /** @test */
    public function employee_cannot_remove_current_pto_that_has_started()
    {
        $employee = $this->create('App\Employee');
        $user = $this->signInEmployee($employee);

        // Create a current PTO (started but not ended)
        $currentPto = $this->create('App\PaidTimeOff', [
            'employee_id' => $employee->id,
            'start_time' => now()->subDays(1),
            'end_time' => now()->addDays(3),
        ]);

        $this->assertEquals(1, PaidTimeOff::count());

        // Employee cannot remove PTO that has already started
        $this->assertFalse($employee->canRemovePto($currentPto));
        $this->assertFalse($currentPto->canRemove());

        // Make the delete request - should be forbidden
        $response = $this->post('/ptos/owner-destroy/' . $currentPto->id);
        $response->assertStatus(403);

        $this->assertEquals(1, PaidTimeOff::count());
    }

    /** @test */
    public function admin_can_still_remove_any_pto_via_manager_route()
    {
        $employee = $this->create('App\Employee');
        $this->signInAdmin();

        // Create a past PTO (admin should still be able to delete it)
        $pastPto = $this->create('App\PaidTimeOff', [
            'employee_id' => $employee->id,
            'start_time' => now()->subDays(10),
            'end_time' => now()->subDays(7),
        ]);

        $this->assertEquals(1, PaidTimeOff::count());

        // Admin can remove any PTO via the manager route
        $response = $this->post('/ptos/destroy/' . $pastPto->id);
        $response->assertStatus(200);

        $this->assertEquals(0, PaidTimeOff::count());
    }

    /** @test */
    public function unauthenticated_user_cannot_remove_pto()
    {
        $employee = $this->create('App\Employee');

        $futurePto = $this->create('App\PaidTimeOff', [
            'employee_id' => $employee->id,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(10),
        ]);

        $this->assertEquals(1, PaidTimeOff::count());

        // Unauthenticated user should be redirected to login
        $response = $this->post('/ptos/owner-destroy/' . $futurePto->id);
        $response->assertRedirect('/login');

        $this->assertEquals(1, PaidTimeOff::count());
    }
}
