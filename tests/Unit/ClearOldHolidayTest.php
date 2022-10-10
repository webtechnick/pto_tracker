<?php

namespace Tests\Unit;

use App\Holiday;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ClearOldHolidayTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_clear_old_pto_but_keep_the_new()
    {
        $holiday = $this->create('App\Holiday', [
            'title' => 'Keep Holiday',
            'date' => '2035-01-04'
        ]);
        $this->create('App\Holiday', [
            'title' => 'Remove',
            'date' => '2017-01-04'
        ]);
        $this->create('App\Holiday', [
            'title' => 'Old',
            'date' => '2016-01-04'
        ]);

        $this->assertEquals(3, Holiday::count());

        Artisan::call('holiday:clear-old');

        $this->assertEquals(1, Holiday::count());
        $this->assertEquals($holiday->id, Holiday::first()->id);
    }
}
