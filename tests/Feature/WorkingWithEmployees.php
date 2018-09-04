<?php

namespace Tests\Feature;

use App\Employee;
use App\PaidTimeOff;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class WorkingWithEmployees extends TestCase
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
}
