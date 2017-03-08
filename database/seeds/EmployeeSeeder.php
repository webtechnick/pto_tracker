<?php

use App\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $employees = [
            ['name' => 'Aaron Hall', 'bgcolor' => 'green'],
            ['name' => 'Ben Stout', 'bgcolor' => 'darkslategrey', 'color' => 'white'],
            ['name' => 'Craig Knowles', 'bgcolor' => 'brown'],
            ['name' => 'Dillon Formo', 'bgcolor' => 'olive'],
            ['name' => 'Jeff Kolesnikowicz', 'bgcolor' => 'red'],
            ['name' => 'Laura Kimpel-Matthews', 'bgcolor' => 'gold', 'color' => 'black'],
            ['name' => 'Lyle Smart', 'bgcolor' => 'blueviolet', 'color' => 'white'],
            ['name' => 'Mark Vince', 'bgcolor' => 'maroon'],
            ['name' => 'Nick Baker', 'bgcolor' => 'navy'],
            ['name' => 'Rachelle Zani', 'bgcolor' => 'pink', 'color' => 'black'],
            ['name' => 'Travis Reddell', 'bgcolor' => 'orangered'],
            ['name' => 'Tyler Shaw', 'bgcolor' => 'purple'],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
