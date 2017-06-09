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
}
