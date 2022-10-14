<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Employee;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EmployeeTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function employee_can_have_different_max_days_off_allowed()
    {
        $employee = $this->create('App\Employee');

        $this->assertEquals(config('app.max_days_off'), $employee->daysLeft());

        $employee = $this->create('App\Employee', ['max_days_off' => 6]);

        $this->assertEquals(6, $employee->daysLeft());
    }

    /** @test */
    public function employee_can_be_on_call()
    {
        $employee = factory(Employee::class)->create();

        $this->assertFalse($employee->isOnCall());

        $employee->setOnCall()->save();

        $this->assertTrue($employee->isOnCall());
    }

    /** @test */
    public function we_can_clear_on_call_employees()
    {
        $employees = factory(Employee::class, 3)->create(['is_on_call' => true]);

        foreach ($employees as $employee) {
            $this->assertTrue($employee->isOnCall());
        }

        Employee::clearOnCall();

        foreach ($employees as $employee) {
            $this->assertFalse($employee->fresh()->isOnCall());
        }
    }

    /** @test */
    public function can_retrive_all_on_call_employees()
    {
        $employeesOnCall = factory(Employee::class, 2)->create(['is_on_call' => true]);
        $employeesNotOnCall = factory(Employee::class, 3)->create(['is_on_call' => false]);

        $employees = Employee::onCall()->get();

        $this->assertCount(2, $employees);
    }

    /** @test */
    public function it_can_know_if_user_can_view_pto_because_admin()
    {
        $user = $this->signInAdmin();
        $employee = $this->create('App\Employee');

        $this->assertTrue($employee->canViewPto());
    }

    /** @test */
    public function it_can_see_pto_because_google_user_is_employee()
    {
        $employee = $this->create('App\Employee', ['name' => 'Nick Baker']);
        $google = new \stdClass;
        $google->name = 'Nick Baker';

        session(['GoogleUser' => $google]);

        $this->assertTrue($employee->canViewPto());
    }

    /** @test */
    public function it_can_see_pto_because_planner_in_team()
    {
        $user_employee = $this->create('App\Employee');
        $other_employee = $this->create('App\Employee');

        $user = $this->signInPlanner(['employee_id' => $user_employee->id]);

        $user_employee->teams = 'Team A, Team C';
        $other_employee->teams = 'Team B, Team A';

        $this->assertTrue($user_employee->hasTag('Team A')); // Same Team
        $this->assertTrue($other_employee->hasTag('Team A')); // Same Team
        $this->assertTrue($other_employee->canViewPto());
    }

    /** @test */
    public function it_can_not_see_pto_because_planner_not_in_team()
    {
        $user_employee = $this->create('App\Employee');
        $other_employee = $this->create('App\Employee');

        $user = $this->signInPlanner(['employee_id' => $user_employee->id]);

        $user_employee->teams = 'Team A, Team B';
        $other_employee->teams = 'Team C, Team D';

        $this->assertTrue($user_employee->hasTag('Team A'));
        $this->assertFalse($other_employee->hasTag('Team A')); // Nope
        $this->assertFalse($other_employee->canViewPto());
    }

    /** @test */
    public function it_can_see_own_pto_because_they_are_the_employee()
    {
        $employee = $this->create('App\Employee');
        $user = $this->signInEmployee($employee);

        $employee2 = $this->create('App\Employee');

        $this->assertTrue($employee->canViewPto()); // Their PTO
        $this->assertFalse($employee2->canViewPto()); // Not Their PTO
    }

    /** @test */
    public function it_cannot_see_pto_because_not_planner_or_admin_or_google_user()
    {
        $user = $this->signIn();
        $employee = $this->create('App\Employee', ['name' => 'No Match']);

        $this->assertFalse($employee->canViewPto());
    }

    /** @test */
    public function it_has_a_manager()
    {
        $user = $this->signInAdmin();
        $employee = $this->create('App\Employee', ['name' => 'Nick Baker', 'manager_id' => $user->id]);

        $manager = $employee->manager()->first();

        $this->assertEquals($user->id, $manager->id);
    }

    /** @test */
    public function it_can_see_pto_because_user_is_manager()
    {
        $user = $this->signInManager();
        $employee = $this->create('App\Employee', ['manager_id' => $user->id]);

        $this->assertTrue($employee->canViewPto());
    }

    /** @test */
    public function it_cannot_see_pto_because_user_is_manager_but_not_employee_manager()
    {
        $user = $this->signInManager();
        $employee = $this->create('App\Employee');

        $this->assertFalse($employee->canViewPto());
    }

    /** @test */
    public function it_can_be_managed_by_manager()
    {
        $user = $this->signInManager();
        $employee = $this->create('App\Employee', ['manager_id' => $user->id]);

        $this->assertTrue($employee->can_manage);
    }

    /** @test */
    public function it_can_be_managed_by_admin_or_manager()
    {
        $user = $this->signInAdmin();
        $employee = $this->create('App\Employee');

        $this->assertTrue($employee->can_manage);
    }

    /** @test */
    public function it_cannot_be_managed_by_user_or_planner()
    {
        $user = $this->signInPlanner();
        $employee = $this->create('App\Employee');

        $this->assertFalse($employee->can_manage);
    }
}
