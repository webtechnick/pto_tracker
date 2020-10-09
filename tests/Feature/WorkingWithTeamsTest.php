<?php

namespace Tests\Feature;

use App\Employee;
use App\Mail\OnCallDigest;
use App\PaidTimeOff;
use App\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkingWithTeamsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_remove_taggable_when_tag()
    {
        $employee = $this->create('App\Employee');
        $tag = $this->create('App\Tag');

        $employee->addTag($tag);

        $this->assertEquals(1, Employee::count());
        $this->assertEquals(1, Tag::count());
        $this->assertEquals(1, $employee->tags()->count());

        $tag->delete();

        $this->assertEquals(1, Employee::count());
        $this->assertEquals(0, Tag::count());
        $this->assertEquals(0, $employee->tags()->count());
    }
}
