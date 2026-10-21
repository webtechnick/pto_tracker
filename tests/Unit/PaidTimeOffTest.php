<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Holiday;
use App\PaidTimeOff;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PaidTimeOffTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_should_calculate_days_excluding_holidays()
    {
        Holiday::factory()->create(['date' => '2017-02-20']); // Monday
        $pto = PaidTimeOff::factory()->create([
            'start_time' => '2017-02-20', // Monday
            'end_time' => '2017-02-21', // Tuesday
        ]);

        $pto->save(); // Should have just Tuesday as PTO day.
        $this->assertEquals(1, $pto->days);
    }

    #[Test]
    public function it_should_calculate_days_excluding_holidays_and_weekends()
    {
        Holiday::factory()->create(['date' => '2017-02-20']); // Monday
        Holiday::factory()->create(['date' => '2017-02-21']); // Tuesday
        $pto = PaidTimeOff::factory()->create([
            'start_time' => '2017-02-17', // Friday
            'end_time' => '2017-02-23', // Thursday
        ]);

        $pto->save(); // Should have Friday, Wednesday and Thursday PTO. 3
        $this->assertEquals(3, $pto->days);
    }

    #[Test]
    public function it_should_mark_as_sent_to_calendar()
    {
        $pto = PaidTimeOff::factory()->create();
        $this->assertFalse($pto->is_sent_to_calendar);

        $pto->sentToCalendar()->save();

        $this->assertTrue($pto->fresh()->is_sent_to_calendar);
    }

    #[Test]
    public function it_should_calculate_days_excluding_half_day_holidays()
    {
        Holiday::factory()->create(['date' => '2017-02-20', 'is_half_day' => true]); // Monday Half Day
        Holiday::factory()->create(['date' => '2017-02-21']); // Tuesday
        $pto = PaidTimeOff::factory()->create([
            'start_time' => '2017-02-17', // Friday
            'end_time' => '2017-02-23', // Thursday
        ]);

        $pto->save(); // Should have Friday, Wednesday and Thursday PTO. 3
        $this->assertEquals(3.5, $pto->days);
    }

    #[Test]
    public function it_should_have_a_simple_subject()
    {
        $pto = PaidTimeOff::factory()->create([
            'start_time' => '2017-02-17', // Friday
            'end_time' => '2017-02-23', // Thursday
            'is_approved' => true
        ]);

        $this->assertEquals('Paid Time Off starting Feb 17, 2017 for 5 days(s) Approved', $pto->simpleSubject());
        $this->assertEquals('Paid Time Off starting Feb 17, 2017 for 5 days(s)', $pto->simpleSubject(false));
    }

    #[Test]
    public function it_should_know_if_in_past()
    {
        $pto = PaidTimeOff::factory()->create([
            'start_time' => '2017-02-17', // Friday
            'end_time' => '2017-02-23', // Thursday
            'is_approved' => true
        ]);

        $this->assertTrue($pto->isPast());
    }

    #[Test]
    public function it_should_know_if_in_future()
    {
        $pto = PaidTimeOff::factory()->create([
            'start_time' => '2035-02-17', // Friday
            'end_time' => '2035-02-23', // Thursday
            'is_approved' => true
        ]);

        $this->assertTrue($pto->isFuture());
    }

    #[Test]
    public function it_should_know_if_in_current()
    {
        $pto = PaidTimeOff::factory()->create([
            'start_time' => Carbon::now()->subDays(5)->toDateString(),
            'end_time' => Carbon::now()->addDays(5)->toDateString(),
            'is_approved' => true
        ]);

        $this->assertTrue($pto->isCurrent());
    }
}
