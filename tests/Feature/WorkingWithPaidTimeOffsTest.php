<?php

namespace Tests\Feature;

use App\Employee;
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
    public function it_should_email_manager_only_on_pto_save()
    {
        Mail::fake();

        $admin = $this->signInAdmin();
        $user = $this->signInAdmin();
        $employee = $this->create('App\Employee', ['manager_id' => $user->id]);

        $data = [
            'employee_id' => $employee->id,
            'start_time' => '2022-03-01',
            'end_time' => '2022-03-05',
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
            'end_time' => '2022-03-05',
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

        $this->assertEquals(0, PaidTimeOff::count());

        Mail::assertNotSent(PaidTimeOffRequested::class);
    }
}
