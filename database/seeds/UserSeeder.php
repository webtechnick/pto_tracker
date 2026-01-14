<?php

use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            ['name' => 'Nick Baker', 'email' => 'nick.baker@continued.com', 'password' => bcrypt('secret'), 'role' => 'admin'],
            ['name' => 'Lyle Smart', 'email' => 'lyle.smart@continued.com', 'password' => bcrypt('secret'), 'role' => 'user'],
            ['name' => 'Planner', 'email' => 'planner@continued.com', 'password' => bcrypt('secret'), 'role' => 'planner'],
            ['name' => 'Manager', 'email' => 'manager@continued.com', 'password' => bcrypt('secret'), 'role' => 'manager'],
            ['name' => 'Employee', 'email' => 'employee@continued.com', 'password' => bcrypt('secret'), 'role' => 'user'],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
