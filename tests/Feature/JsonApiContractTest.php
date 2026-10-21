<?php

namespace Tests\Feature;

use App\Database\LegacySqliteConnection;
use App\Employee;
use App\Holiday;
use App\PaidTimeOff;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * /api/time-off (dora-metrics) and /get/ptos (Vue calendar) must keep the
 * exact JSON master produces on PHP 7.2, where pdo_sqlite returned every
 * column as SQLite text. The expected bodies below are master's output.
 */
class JsonApiContractTest extends TestCase
{
    use DatabaseMigrations;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.api_token' => 'token']);
        $manager = User::forceCreate(['id' => 7, 'name' => 'Mgr', 'email' => 'mgr@continued.com', 'password' => 'x', 'role' => 'manager']);
        $this->employee = Employee::forceCreate([
            'id' => 15, 'name' => 'Fifteen', 'title' => 'Lead', 'color' => 'white', 'bgcolor' => 'red',
            'max_days_off' => 22.5, 'manager_id' => $manager->id,
        ]);
        Holiday::create(['title' => 'Summer Hours', 'date' => '2035-06-08', 'is_half_day' => true]);

        $four = PaidTimeOff::saveForm(['employee_id' => 15, 'start_time' => '2035-09-10', 'end_time' => '2035-09-13', 'description' => 'Trip']);
        $four->approve()->save();
        $half = PaidTimeOff::saveForm(['employee_id' => 15, 'start_time' => '2035-06-06', 'end_time' => '2035-06-08', 'description' => 'Short']);
        $half->approve()->save();
    }

    #[Test]
    public function time_off_api_matches_master_byte_for_byte()
    {
        $body = $this->getJson('/api/time-off?start_date=2035-01-01&end_date=2035-12-31', ['Authorization' => 'Bearer token'])
            ->assertOk()
            ->getContent();

        $this->assertSame(
            '{"data":[{"employee_id":15,"employee_name":"Fifteen","pto_days":6.5,"entries":['
            .'{"start_time":"2035-09-10","end_time":"2035-09-13","days":"4.0","is_half_day":false,"is_approved":true,"description":"Trip"},'
            .'{"start_time":"2035-06-06","end_time":"2035-06-08","days":"2.5","is_half_day":false,"is_approved":true,"description":"Short"}'
            .']}]}',
            $body
        );
    }

    #[Test]
    public function calendar_feed_returns_database_numbers_as_sqlite_text()
    {
        $ptos = json_decode($this->getJson('/get/ptos/2035')->assertOk()->getContent(), true);

        $this->assertCount(2, $ptos);
        foreach ($ptos as $pto) {
            $this->assertIsInt($pto['id']);
            $this->assertSame('15', $pto['employee_id']);
            $this->assertSame('7', $pto['employee']['manager_id']);
            $this->assertIsInt($pto['employee']['id']);
            $this->assertIsBool($pto['is_approved']);
            $this->assertIsBool($pto['is_half_day']);
            $this->assertIsBool($pto['is_sent_to_calendar']);
            $this->assertIsBool($pto['employee']['is_contractor']);
        }
        $this->assertSame(['4.0', '2.5'], array_column($ptos, 'days'));
        $this->assertStringContainsString('"employee_id":"15","is_approved":true,"is_half_day":false,"days":"4.0"', $this->getJson('/get/ptos/2035')->getContent());
    }

    #[Test]
    public function models_read_back_from_sqlite_hold_text_like_php_7()
    {
        $employee = Employee::find(15);

        $this->assertSame('22.5', $employee->max_days_off);
        $this->assertSame('7', $employee->manager_id);
        $this->assertSame(15, $employee->id);
        $this->assertSame(16.0, $employee->daysLeft(2035)); // "22.5" - ("4.0" + "2.5")
        $this->assertStringContainsString('4.0 day(s)', PaidTimeOff::where('days', 4)->first()->simpleString());
    }

    public static function reals(): array
    {
        return [[0.5], [1.0], [4.0], [2.5], [21.0], [0.1], [1234567.25], [123456789012345.0], [1e20], [-3.0], [1.5e-7]];
    }

    #[Test]
    #[DataProvider('reals')]
    public function real_formatting_matches_sqlite_text_conversion(float $value)
    {
        $sqlite = DB::connection()->getPdo()->query('SELECT CAST('.var_export($value, true).' AS TEXT)')->fetchColumn();

        $this->assertSame($sqlite, LegacySqliteConnection::sqliteText($value));
    }

    #[Test]
    public function integers_and_nulls_are_converted_like_php_7()
    {
        $row = DB::selectOne('select 15 as i, 4.0 as r, null as n, \'x\' as s');

        $this->assertSame(['i' => '15', 'r' => '4.0', 'n' => null, 's' => 'x'], (array) $row);
    }
}
