<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Employee;
use App\Holiday;
use App\PaidTimeOff;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ContractorTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function employee_can_be_a_contractor()
    {
        $employee = Employee::factory()->create(['is_contractor' => true]);

        $this->assertTrue($employee->isContractor());
    }

    #[Test]
    public function employee_is_not_a_contractor_by_default()
    {
        $employee = Employee::factory()->create();

        $this->assertFalse($employee->isContractor());
    }

    #[Test]
    public function scope_contractor_returns_only_contractors()
    {
        Employee::factory()->count(3)->create(['is_contractor' => false]);
        Employee::factory()->count(2)->create(['is_contractor' => true]);

        $contractors = Employee::contractor()->get();

        $this->assertCount(2, $contractors);
        foreach ($contractors as $contractor) {
            $this->assertTrue($contractor->isContractor());
        }
    }

    #[Test]
    public function contractor_is_charged_full_day_on_full_day_holiday()
    {
        $contractor = Employee::factory()->create(['is_contractor' => true]);
        Holiday::factory()->create(['date' => '2017-02-20']); // Monday

        $pto = PaidTimeOff::factory()->create([
            'employee_id' => $contractor->id,
            'start_time' => '2017-02-20', // Monday (holiday)
            'end_time' => '2017-02-21',   // Tuesday
        ]);

        $this->assertEquals(2, $pto->days);
    }

    #[Test]
    public function regular_employee_is_not_charged_on_full_day_holiday()
    {
        $employee = Employee::factory()->create(['is_contractor' => false]);
        Holiday::factory()->create(['date' => '2017-02-20']); // Monday

        $pto = PaidTimeOff::factory()->create([
            'employee_id' => $employee->id,
            'start_time' => '2017-02-20', // Monday (holiday)
            'end_time' => '2017-02-21',   // Tuesday
        ]);

        $this->assertEquals(1, $pto->days);
    }

    #[Test]
    public function contractor_is_charged_full_day_on_half_day_holiday()
    {
        $contractor = Employee::factory()->create(['is_contractor' => true]);
        Holiday::factory()->create(['date' => '2017-02-20', 'is_half_day' => true]); // Monday half day

        $pto = PaidTimeOff::factory()->create([
            'employee_id' => $contractor->id,
            'start_time' => '2017-02-20', // Monday (half-day holiday)
            'end_time' => '2017-02-21',   // Tuesday
        ]);

        $this->assertEquals(2, $pto->days);
    }

    #[Test]
    public function regular_employee_is_charged_half_day_on_half_day_holiday()
    {
        $employee = Employee::factory()->create(['is_contractor' => false]);
        Holiday::factory()->create(['date' => '2017-02-20', 'is_half_day' => true]); // Monday half day

        $pto = PaidTimeOff::factory()->create([
            'employee_id' => $employee->id,
            'start_time' => '2017-02-20', // Monday (half-day holiday)
            'end_time' => '2017-02-21',   // Tuesday
        ]);

        $this->assertEquals(1.5, $pto->days);
    }

    #[Test]
    public function contractor_still_skips_weekends()
    {
        $contractor = Employee::factory()->create(['is_contractor' => true]);

        $pto = PaidTimeOff::factory()->create([
            'employee_id' => $contractor->id,
            'start_time' => '2017-02-17', // Friday
            'end_time' => '2017-02-21',   // Tuesday
        ]);

        // Friday + Monday + Tuesday = 3 (Saturday and Sunday skipped)
        $this->assertEquals(3, $pto->days);
    }
}
