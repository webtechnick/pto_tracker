<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Holiday;
use App\PaidTimeOff;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class HolidayTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_should_be_half_day()
    {
        Holiday::factory()->create(['date' => '2017-02-20', 'is_half_day' => true]); // Monday Half Day

        $this->assertTrue(Holiday::isHalfDayHoliday('2017-02-20'));
        $this->assertTrue(Holiday::isHoliday('2017-02-20'));
    }
}
