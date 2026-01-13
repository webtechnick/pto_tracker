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
            ['name' => 'Nick Baker', 'email' => 'nick.baker@continued.com', 'password' => bcrypt('secret'), 'role' => 'admin']
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
