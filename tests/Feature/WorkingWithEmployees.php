<?php

namespace Tests\Feature;

use App\Employee;
use App\Mail\OnCallDigest;
use App\PaidTimeOff;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;
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
}
