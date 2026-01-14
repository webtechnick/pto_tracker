<?php

use App\Employee;
use App\User;
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

            // Test Users - Manager and Employee for testing role-based features
            ['name' => 'Manager', 'bgcolor' => 'darkgoldenrod', 'color' => 'white', 'team' => 'Engineering', 'link_user' => 'manager@continued.com'],
            ['name' => 'Employee', 'bgcolor' => 'cadetblue', 'color' => 'white', 'team' => 'Engineering', 'link_user' => 'employee@continued.com'],
        ];

        foreach ($employees as $data) {
            // Extract team and user link before creating employee
            $team = $data['team'] ?? null;
            $linkUser = $data['link_user'] ?? null;
            unset($data['team'], $data['link_user']);

            // Create the employee
            $employee = Employee::create($data);

            // Assign team tag if specified
            if ($team) {
                $employee->syncTagString($team);
            }

            // Link to user account if specified
            if ($linkUser) {
                $user = User::where('email', $linkUser)->first();
                if ($user) {
                    $user->update(['employee_id' => $employee->id]);
                }
            }
        }

        // Also link existing employees to their user accounts by name
        $this->linkExistingUsersToEmployees();
    }

    /**
     * Link existing users to employees with matching names
     */
    private function linkExistingUsersToEmployees()
    {
        $usersToLink = [
            'nick.baker@continued.com' => 'Nick Baker',
            'lyle.smart@continued.com' => 'Lyle Smart',
        ];

        foreach ($usersToLink as $email => $employeeName) {
            $user = User::where('email', $email)->first();
            $employee = Employee::where('name', $employeeName)->first();

            if ($user && $employee && !$user->employee_id) {
                $user->update(['employee_id' => $employee->id]);
            }
        }
    }
}
