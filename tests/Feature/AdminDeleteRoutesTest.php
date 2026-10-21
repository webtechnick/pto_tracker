<?php

namespace Tests\Feature;

use App\Employee;
use App\PaidTimeOff;
use App\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The delete routes run the model events that clean up related rows.
 */
class AdminDeleteRoutesTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function deleting_a_team_detaches_it_from_employees()
    {
        $this->signInAdmin();
        $tag = $this->create(Tag::class, ['name' => 'Backend Probe', 'slug' => 'backend-probe']);
        $employee = $this->create(Employee::class);
        $employee->syncTagString($tag->name);
        $this->assertSame(1, DB::table('taggables')->count());

        $this->get(route('admin.teams.delete', $tag))->assertRedirect(route('admin.teams'));

        $this->assertNull($tag->fresh());
        $this->assertSame(0, DB::table('taggables')->count());
        $this->assertNotNull($employee->fresh());
    }

    #[Test]
    public function deleting_an_employee_removes_their_pto()
    {
        Mail::fake();
        $this->signInAdmin();
        $employee = $this->create(Employee::class);
        $pto = $this->create(PaidTimeOff::class, ['employee_id' => $employee->id]);

        $this->get(route('manager.employees.delete', $employee))->assertRedirect(route('manager.employees'));

        $this->assertNull($employee->fresh());
        $this->assertNull($pto->fresh());
    }
}
