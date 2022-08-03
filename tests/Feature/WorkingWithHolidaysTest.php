<?php

namespace Tests\Feature;

use App\Holiday;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkingWithHolidaysTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_create_a_holiday_from_request()
    {
        $this->assertEquals(0, Holiday::count());

        $user = $this->signInAdmin();

        $data = [
            'title' => 'New Year',
            'date' => '2022-01-01'
        ];

        $response = $this->post('/admin/holidays/store', $data);

        $this->assertEquals(1, Holiday::count());
    }

    /** @test */
    public function it_should_update_a_holiday_from_request()
    {
        $holiday = $this->create('App\Holiday');
        $user = $this->signInAdmin();

        $this->assertEquals(1, Holiday::count());

        $data = [
            'title' => 'New Year',
            'date' => '2022-01-01'
        ];

        $response = $this->post(route('admin.holidays.update', $holiday, false), $data);
        $holiday->refresh();

        $this->assertEquals(1, Holiday::count());
        $this->assertEquals($data['title'], $holiday->title);
        $this->assertEquals($data['date'], $holiday->date);
        $this->assertEquals(false,$holiday->isHalfDay());
    }

    /** @test */
    public function it_should_delete_a_holiday()
    {
        $holiday = $this->create('App\Holiday');
        $user = $this->signInAdmin();

        $this->assertEquals(1, Holiday::count());

        $response = $this->get(route('admin.holidays.delete', $holiday, false));

        $this->assertEquals(0, Holiday::count());
    }

    /** @test */
    public function it_should_create_half_holiday_from_request()
    {
        $this->assertEquals(0, Holiday::count());

        $user = $this->signInAdmin();

        $data = [
            'title' => 'New Year',
            'date' => '2022-01-01',
            'is_half_day' => true,
        ];

        $response = $this->post('/admin/holidays/store', $data);

        $this->assertEquals(1, Holiday::count());

        $holiday = Holiday::first();

        $this->assertEquals($data['title'],$holiday->title);
        $this->assertEquals($data['date'],$holiday->date);
        $this->assertEquals(true,$holiday->isHalfDay());
    }
}
