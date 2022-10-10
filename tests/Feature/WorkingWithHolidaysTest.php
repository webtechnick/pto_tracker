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

    /** @test */
    public function it_should_be_able_to_create_holidays_in_bulk()
    {
        $this->assertEquals(0, Holiday::count());

        $user = $this->signInAdmin();

        $bulk =
"Holiday 1, 2022-01-02
Holiday 2, 2022-07-04
Holiday Half, 2022-08-14, 1
Holiday 4, 2022-12-25
Holiday 5, 2022-12-26
";
        $data = ['bulk' => $bulk];

        $response = $this->post('/admin/holidays/bulk', $data);

        $this->assertEquals(5, Holiday::count());
        $this->assertEquals(1, Holiday::halfDay()->count());
        $this->assertEquals(4, Holiday::fullDay()->count());
    }
}
