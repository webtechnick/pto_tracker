<?php

namespace Tests\Unit;

use App\Employee;
use App\Holiday;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class EmployeeUserSyncTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_clear_old_pto_but_keep_the_new()
    {
        $john = $this->create('App\Employee', ['name' => 'John Smith']);
        $jill = $this->create('App\Employee', ['name' => 'Jill Smith']);
        $jack = $this->create('App\Employee', ['name' => 'Jack Smith']);
        $user = $this->create('App\User', ['name' => 'John Smith']);
        $user2 = $this->create('App\User', ['name' => 'Jill Smith']);
        $user3 = $this->create('App\User', ['name' => 'Tim Smith']);

        $this->assertEquals(3, Employee::count());
        $this->assertEquals(3, User::count());
        $this->assertNull($user->employee);
        $this->assertNull($user2->employee);
        $this->assertNull($user3->employee);

        Artisan::call('employee:user-sync');

        $this->assertEquals(3, Employee::count());
        $this->assertEquals(3, User::count());
        $this->assertEquals($john->id, $user->fresh()->employee_id); // Assigned
        $this->assertEquals($jill->id, $user2->fresh()->employee_id); // Assigned
        $this->assertNull($user3->fresh()->employee); // Null
    }
}
