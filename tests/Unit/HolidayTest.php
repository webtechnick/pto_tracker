<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Holiday;
use App\PaidTimeOff;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class HolidayTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_be_half_day()
    {
        factory(Holiday::class)->create(['date' => '2017-02-20', 'is_half_day' => true]); // Monday Half Day

        $this->assertTrue(Holiday::isHalfDayHoliday('2017-02-20'));
        $this->assertTrue(Holiday::isHoliday('2017-02-20'));
    }
}
