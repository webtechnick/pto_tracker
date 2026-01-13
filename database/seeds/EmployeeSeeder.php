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
        // Employees grouped by team
        // Teams are stored as Tags via the Taggable trait
        $employees = [
            // Engineering Team
            ['name' => 'Ben Stout', 'bgcolor' => 'darkslategrey', 'color' => 'white', 'team' => 'Engineering'],
            ['name' => 'Dillon Formo', 'bgcolor' => 'olive', 'team' => 'Engineering'],
            ['name' => 'Frank Torres', 'bgcolor' => 'steelblue', 'color' => 'white', 'team' => 'Engineering'],
            ['name' => 'Henry Patel', 'bgcolor' => 'slategray', 'color' => 'white', 'team' => 'Engineering'],
            ['name' => 'Lyle Smart', 'bgcolor' => 'blueviolet', 'color' => 'white', 'team' => 'Engineering'],
            ['name' => 'Nick Baker', 'bgcolor' => 'navy', 'color' => 'white', 'team' => 'Engineering'],
            ['name' => 'Sam Johnson', 'bgcolor' => 'teal', 'color' => 'white', 'team' => 'Engineering'],

            // Design Team
            ['name' => 'Emily Chen', 'bgcolor' => 'coral', 'team' => 'Design'],
            ['name' => 'Grace Kim', 'bgcolor' => 'mediumseagreen', 'team' => 'Design'],
            ['name' => 'Isabella Martinez', 'bgcolor' => 'hotpink', 'team' => 'Design'],
            ['name' => 'Jake Williams', 'bgcolor' => 'darkorange', 'team' => 'Design'],
            ['name' => 'Karen Liu', 'bgcolor' => 'darkturquoise', 'team' => 'Design'],
            ['name' => 'Nina Rodriguez', 'bgcolor' => 'mediumpurple', 'team' => 'Design'],
            ['name' => 'Rachelle Zani', 'bgcolor' => 'pink', 'color' => 'black', 'team' => 'Design'],

            // Product Team
            ['name' => 'Elanor Riley', 'bgcolor' => 'green', 'team' => 'Product'],
            ['name' => 'Drew Glaser', 'bgcolor' => 'brown', 'color' => 'white', 'team' => 'Product'],
            ['name' => 'Leo Thompson', 'bgcolor' => 'indianred', 'color' => 'white', 'team' => 'Product'],
            ['name' => 'Mark Vince', 'bgcolor' => 'maroon', 'color' => 'white', 'team' => 'Product'],
            ['name' => 'Oscar Davis', 'bgcolor' => 'chocolate', 'color' => 'white', 'team' => 'Product'],
            ['name' => 'Valerie England', 'bgcolor' => 'orangered', 'team' => 'Product'],
            ['name' => 'Tyler Shaw', 'bgcolor' => 'purple', 'color' => 'white', 'team' => 'Product'],
        ];

        foreach ($employees as $data) {
            // Extract team before creating employee
            $team = $data['team'] ?? null;
            unset($data['team']);

            // Create the employee
            $employee = Employee::create($data);

            // Assign team tag if specified
            if ($team) {
                $employee->syncTagString($team);
            }
        }
    }
}
