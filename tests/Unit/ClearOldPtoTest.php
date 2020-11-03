<?php

namespace Tests\Unit;

use App\PaidTimeOff;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ClearOldPtoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_clear_old_pto_but_keep_the_new()
    {
        $pto = $this->create('App\PaidTimeOff', [
            'start_time' => '2025-01-01',
            'end_time' => '2025-01-04'
        ]);
        $this->create('App\PaidTimeOff', [
            'start_time' => '2017-01-01',
            'end_time' => '2017-01-04'
        ]);
        $this->create('App\PaidTimeOff', [
            'start_time' => '2016-01-01',
            'end_time' => '2016-01-04'
        ]);

        $this->assertEquals(3, PaidTimeOff::count());

        Artisan::call('employee:clear-old-pto');

        $this->assertEquals(1, PaidTimeOff::count());
        $this->assertEquals($pto->id, PaidTimeOff::first()->id);
    }
}
