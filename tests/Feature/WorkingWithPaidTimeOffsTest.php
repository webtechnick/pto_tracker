<?php

namespace Tests\Feature;

use App\Employee;
use App\Mail\PaidTimeOffApproved;
use App\Mail\PaidTimeOffDeleted;
use App\Mail\PaidTimeOffRequested;
use App\PaidTimeOff;
use App\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkingWithPaidTimeOffsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_be_able_to_add_pto()
    {
        $employee = $this->create('App\Employee');
        $data = [
            'employee_id' => $employee->id,
            'start_time' => '2022-03-01', // Tuesday
            'end_time' => '2022-03-04', // Friday
        ];
        $count = PaidTimeOff::count();

        $response = $this->post('/ptos/store', $data);

        $response->assertSessionMissing('end_time');
        $this->assertEquals($count + 1, PaidTimeOff::count()); // We added a PTO

    }

    /** @test */
    public function it_should_not_be_able_to_add_empty_pto()
    {
        $data = [];
        $count = PaidTimeOff::count();

        $response = $this->post('/ptos/store', $data);

        $response->assertSessionHasErrors(['start_time','end_time','employee_id']);
        $this->assertEquals($count, PaidTimeOff::count()); // We did not add a PTO
    }

    /** @test */
    public function it_should_not_be_able_to_end_time_earlier_than_start_time_pto()
    {
        $employee = $this->create('App\Employee');
        $data = [
            'employee_id' => $employee->id,
            'start_time' => '2022-03-04', // Friday
            'end_time' => '2022-03-01', // Tuesday
        ];
        $count = PaidTimeOff::count();

        $response = $this->post('/ptos/store', $data);

        $response->assertSessionHasErrors(['end_time']);
        $this->assertEquals($count, PaidTimeOff::count()); // We did not add a PTO
    }

    /** @test */
    public function it_should_not_be_able_to_add_pto_with_different_year()
    {
        $employee = $this->create('App\Employee');
        $data = [
            'employee_id' => $employee->id,
            'start_time' => '2022-12-29',
            'end_time' => '2023-01-03'
        ];
        $count = PaidTimeOff::count();

        $response = $this->post('/ptos/store', $data);

        $response->assertSessionHasErrors(['end_time']);
        $this->assertEquals($count, PaidTimeOff::count()); // We did not add a PTO
    }

    /** @test */
    public function it_should_mark_pto_as_sent_to_calendar()
    {
        $this->signInAdmin();

        $pto = factory(PaidTimeOff::class)->create();

        $this->assertFalse($pto->fresh()->is_sent_to_calendar);

        $response = $this->post('/ptos/sent_to_calendar/' . $pto->id);

        $this->assertTrue($pto->fresh()->is_sent_to_calendar);
    }

    /** @test */
    public function it_cannot_request_end_time_before_start_time()
    {
        $employee = $this->create('App\Employee');
        $data = [
            'employee_id' => $employee->id,
            'start_time' => '2022-03-04',
            'end_time' => '2022-03-01'
        ];
        $count = PaidTimeOff::count();

        $response = $this->post('/ptos/store', $data);

        $response->assertSessionHasErrors(['end_time']);
        $this->assertEquals($count, PaidTimeOff::count()); // We did not add a PTO
    }

    /** @test */
    public function it_should_email_manager_only_on_pto_save()
    {
        Mail::fake();

        $admin = $this->signInAdmin();
        $user = $this->signInAdmin();
        $employee = $this->create('App\Employee', ['manager_id' => $user->id]);

        $data = [
            'employee_id' => $employee->id,
            'start_time' => '2022-03-01',
            'end_time' => '2022-03-04',
        ];

        $response = $this->post('/ptos/store', $data);

        $this->assertEquals(1, PaidTimeOff::count());

        Mail::assertSent(PaidTimeOffRequested::class, function ($mail) use ($user, $admin) {
            return $mail->hasTo($user->email) && !$mail->hasTo($admin->email);
        });
    }

    /** @test */
    public function it_should_email_all_admin_on_pto_save()
    {
        Mail::fake();

        $admin = $this->signInAdmin();
        $user = $this->signInAdmin();
        $employee = $this->create('App\Employee', ['manager_id' => null]); // no manager

        $data = [
            'employee_id' => $employee->id,
            'start_time' => '2022-03-01',
            'end_time' => '2022-03-04',
        ];

        $response = $this->post('/ptos/store', $data);

        $this->assertEquals(1, PaidTimeOff::count());

        Mail::assertSent(PaidTimeOffRequested::class, function ($mail) use ($user, $admin) {
            return $mail->hasTo($user->email) && $mail->hasTo($admin->email);
        });
    }

    /** @test */
    public function it_cannot_request_pto_on_single_weekend()
    {
        Mail::fake();

        $admin = $this->signInAdmin();
        $employee = $this->create('App\Employee');

        $data = [
            'employee_id' => $employee->id,
            'start_time' => '2022-10-16', // Sunday
            'end_time' => '2022-10-16', // Sunday
        ];

        $response = $this->post('/ptos/store', $data);

        $response->assertSessionHasErrors('end_time');
        $this->assertEquals(0, PaidTimeOff::count());
        Mail::assertNotSent(PaidTimeOffRequested::class);
    }

    /** @test */
    public function it_should_email_employee_when_pto_approved()
    {
        Mail::fake();

        // Create user and employee link
        $employee = $this->create('App\Employee');
        $user = $this->signInEmployee($employee);

        $pto = $this->create('App\PaidTimeOff', [
            'start_time' => '2035-03-01',
            'end_time' => '2035-03-04',
        ]);

        $admin = $this->signInAdmin(); // Login as Admin

        // Assert Start State
        $this->assertFalse($pto->isApproved());
        $this->assertEquals(1, PaidTimeOff::count());

        $response = $this->post("/ptos/approve/$pto->id");

        // Assert End State
        $this->assertTrue($pto->fresh()->isApproved());
        $this->assertEquals(1, PaidTimeOff::count());

        // Assert Mail Sent
        Mail::assertSent(PaidTimeOffApproved::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /** @test */
    public function it_should_not_email_employee_if_pto_is_in_past()
    {
        Mail::fake();

        // Create user and employee link
        $employee = $this->create('App\Employee');
        $user = $this->signInEmployee($employee);

        // Past PTO
        $pto = $this->create('App\PaidTimeOff', [
            'start_time' => '2020-03-01',
            'end_time' => '2020-03-04',
        ]);

        $admin = $this->signInAdmin(); // Login as Admin

        // Assert Start State
        $this->assertFalse($pto->isApproved());
        $this->assertEquals(1, PaidTimeOff::count());

        $response = $this->post("/ptos/approve/$pto->id");

        // Assert End State
        $this->assertTrue($pto->fresh()->isApproved());
        $this->assertEquals(1, PaidTimeOff::count());

        // Assert Mail Sent
        Mail::assertNotSent(PaidTimeOffApproved::class);
    }

    /** @test */
    public function it_should_email_employee_when_pto_deleted()
    {
        Mail::fake();

        // Create user and employee link
        $employee = $this->create('App\Employee');
        $user = $this->signInEmployee($employee);

        $pto = $this->create('App\PaidTimeOff', [
            'start_time' => '2035-03-01', // Friday
            'end_time' => '2035-03-04', // Thursday
        ]);

        $admin = $this->signInAdmin(); // Login as Admin

        // Assert Start State
        $this->assertFalse($pto->isApproved());
        $this->assertEquals(1, PaidTimeOff::count());

        $response = $this->post("/ptos/destroy/$pto->id");

        // Assert End State
        $this->assertEquals(0, PaidTimeOff::count());

        // Assert Mail Sent
        Mail::assertSent(PaidTimeOffDeleted::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /** @test */
    public function it_should_not_email_employee_when_past_pto_is_deleted()
    {
        Mail::fake();

        // Create user and employee link
        $employee = $this->create('App\Employee');
        $user = $this->signInEmployee($employee);

        $pto = $this->create('App\PaidTimeOff', [
            'start_time' => '2022-03-01', // Friday
            'end_time' => '2022-03-04', // Thursday
        ]);

        $admin = $this->signInAdmin(); // Login as Admin

        // Assert Start State
        $this->assertFalse($pto->isApproved());
        $this->assertEquals(1, PaidTimeOff::count());

        $response = $this->post("/ptos/destroy/$pto->id");

        // Assert End State
        $this->assertEquals(0, PaidTimeOff::count());

        // Assert Mail Sent
        Mail::assertNotSent(PaidTimeOffDeleted::class);
    }

    /** @test */
    public function it_should_now_allow_same_day_pto_request()
    {
        Mail::fake();

        // Create user, employee, and pto
        $employee = $this->create('App\Employee');
        $user = $this->signInEmployee($employee);
        $pto = $this->create('App\PaidTimeOff', [
            'employee_id' => $employee->id,
            'start_time' => '2022-03-01', // Friday
            'end_time' => '2022-03-04', // Thursday
        ]);

        $this->assertEquals(1, PaidTimeOff::count());

        $data = [
            'employee_id' => $employee->id,
            'start_time' => '2022-03-01', // Friday
            'end_time' => '2022-03-01', // Friday
        ];

        $response = $this->post('/ptos/store', $data);

        $this->assertEquals(1, PaidTimeOff::count());
        Mail::assertNotSent(PaidTimeOffRequested::class);
    }
}
