<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Holiday;
use App\PaidTimeOff;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PaidTimeOffTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_calculate_days_excluding_holidays()
    {
        factory(Holiday::class)->create(['date' => '2017-02-20']); // Monday
        $pto = factory(PaidTimeOff::class)->create([
            'start_time' => '2017-02-20', // Monday
            'end_time' => '2017-02-21', // Tuesday
        ]);

        $pto->save(); // Should have just Tuesday as PTO day.
        $this->assertEquals(1, $pto->days);
    }

    /** @test */
    public function it_should_calculate_days_excluding_holidays_and_weekends()
    {
        factory(Holiday::class)->create(['date' => '2017-02-20']); // Monday
        factory(Holiday::class)->create(['date' => '2017-02-21']); // Tuesday
        $pto = factory(PaidTimeOff::class)->create([
            'start_time' => '2017-02-17', // Friday
            'end_time' => '2017-02-23', // Thursday
        ]);

        $pto->save(); // Should have Friday, Wednesday and Thursday PTO. 3
        $this->assertEquals(3, $pto->days);
    }

    /** @test */
    public function it_should_mark_as_sent_to_calendar()
    {
        $pto = factory(PaidTimeOff::class)->create();
        $this->assertFalse($pto->is_sent_to_calendar);

        $pto->sentToCalendar()->save();

        $this->assertTrue($pto->fresh()->is_sent_to_calendar);
    }
}
