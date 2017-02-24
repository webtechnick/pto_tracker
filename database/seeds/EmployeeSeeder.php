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
            ['name' => 'Ben Stout', 'bgcolor' => 'moccasin', 'color' => 'black'],
            ['name' => 'Craig Knowles', 'bgcolor' => 'brown'],
            ['name' => 'Dillon Formo', 'bgcolor' => 'salmon'],
            ['name' => 'Jeff Kolesnikowicz', 'bgcolor' => 'red'],
            ['name' => 'Laura Kimpel-Matthews', 'bgcolor' => 'gold', 'color' => 'black'],
            ['name' => 'Lyle Smart', 'bgcolor' => 'lavender', 'color' => 'black'],
            ['name' => 'Mark Vince', 'bgcolor' => 'magenta'],
            ['name' => 'Nick Baker', 'bgcolor' => 'blue'],
            ['name' => 'Rachelle Zani', 'bgcolor' => 'pink', 'color' => 'black'],
            ['name' => 'Travis Reddell', 'bgcolor' => 'orange'],
            ['name' => 'Tyler Shaw', 'bgcolor' => 'purple'],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
