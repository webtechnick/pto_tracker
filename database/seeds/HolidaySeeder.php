<?php

use App\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $holidays = [
            ['title' => 'New Year', 'date' => '2017-01-02'],
            ['title' => 'Good Friday', 'date' => '2017-04-14'],
            ['title' => 'Memorial Day', 'date' => '2017-05-29'],
            ['title' => 'Indepedence Day', 'date' => '2017-07-04'],
            ['title' => 'Indepedence Day', 'date' => '2017-07-03'],
            ['title' => 'Labor Day', 'date' => '2017-09-04'],
            ['title' => 'Thanksgiving', 'date' => '2017-11-23'],
            ['title' => 'Thanksgiving', 'date' => '2017-11-24'],
            ['title' => 'Christmas', 'date' => '2017-12-25'],
            ['title' => 'Christmas', 'date' => '2017-12-26'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }
    }
}
