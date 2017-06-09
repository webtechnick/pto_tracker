<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\PaidTimeOff;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PaidTimeOffControllerTest extends TestCase
{
    use DatabaseMigrations;


    /** @test */
    public function it_should_be_able_to_add_pto()
    {
        //$user = factory(User::class)->create();
        //$this->withoutMiddleware();
        //$this->actingAs($user);

        $data = [
            'employee_id' => 1,
            'start_time' => '2017-01-03',
            'end_time' => '2017-01-06'
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

        $response->assertSessionHasErrors(['start_time','end_time']);
        $this->assertEquals($count, PaidTimeOff::count()); // We did not add a PTO
    }

    /** @test */
    public function it_should_not_be_able_to_end_time_earlier_than_start_time_pto()
    {
        $data = [
            'employee_id' => 1,
            'start_time' => '2017-01-03',
            'end_time' => '2017-01-01'
        ];
        $count = PaidTimeOff::count();

        $response = $this->post('/ptos/store', $data);

        $response->assertSessionHasErrors(['end_time']);
        $this->assertEquals($count, PaidTimeOff::count()); // We did not add a PTO
    }

    /** @test */
    public function it_should_not_be_able_to_add_pto_with_different_year()
    {
        $data = [
            'employee_id' => 1,
            'start_time' => '2017-12-30',
            'end_time' => '2018-01-03'
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
}
