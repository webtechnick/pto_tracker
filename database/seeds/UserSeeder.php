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
            ['name' => 'Nick', 'email' => 'nick.baker@alliedhealthmedia.com', 'password' => bcrypt('sexybeard'), 'role' => 'admin']
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
