<?php

namespace Tests\Feature;

use App\Employee;
use App\Events\EmployeeDeleting;
use App\Events\PaidTimeOffApproved;
use App\Events\PaidTimeOffDeleted;
use App\Events\PaidTimeOffRequested;
use App\Events\TagDeleting;
use App\Holiday;
use App\Mail\PaidTimeOffApproved as PaidTimeOffApprovedMail;
use App\Mail\PaidTimeOffDeleted as PaidTimeOffDeletedMail;
use App\Mail\PaidTimeOffRequested as PaidTimeOffRequestedMail;
use App\PaidTimeOff;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Behaviour that the Laravel 6 -> 13 upgrade could silently change.
 */
class UpgradeRegressionTest extends TestCase
{
    use DatabaseMigrations;

    public static function events(): array
    {
        return [
            [EmployeeDeleting::class],
            [TagDeleting::class],
            [PaidTimeOffRequested::class],
            [PaidTimeOffApproved::class],
            [PaidTimeOffDeleted::class],
        ];
    }

    #[Test]
    #[DataProvider('events')]
    public function each_event_has_exactly_one_listener(string $event)
    {
        $this->assertCount(1, Event::getListeners($event));
    }

    #[Test]
    public function requesting_pto_emails_the_manager_exactly_once()
    {
        Mail::fake();
        $manager = $this->create(User::class, ['role' => 'manager']);
        $employee = $this->create(Employee::class, ['manager_id' => $manager->id]);

        $this->post('/ptos/store', [
            'employee_id' => $employee->id,
            'start_time' => '2035-03-06', // Tuesday
            'end_time' => '2035-03-08',
        ])->assertRedirect(route('home'));

        Mail::assertSent(PaidTimeOffRequestedMail::class, 1);
        Mail::assertSent(PaidTimeOffRequestedMail::class, fn ($mail) => $mail->hasTo($manager->email));
    }

    #[Test]
    public function approving_pto_returns_json_with_legacy_date_format_and_emails_once()
    {
        Mail::fake();
        $employee = $this->create(Employee::class);
        $this->signInEmployee($employee);
        $pto = $this->create(PaidTimeOff::class, [
            'employee_id' => $employee->id,
            'start_time' => '2035-03-05',
            'end_time' => '2035-03-07',
        ]);
        $this->signInAdmin();

        $response = $this->postJson("/ptos/approve/{$pto->id}");

        $response->assertOk()
            ->assertJsonPath('id', $pto->id)
            ->assertJsonPath('is_approved', true)
            ->assertJsonPath('start_time', '2035-03-05 00:00:00')
            ->assertJsonPath('end_time', '2035-03-07 00:00:00');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $response->json('created_at'));
        Mail::assertSent(PaidTimeOffApprovedMail::class, 1);
    }

    #[Test]
    public function deleting_pto_emails_the_employee_once_and_removes_it()
    {
        Mail::fake();
        $employee = $this->create(Employee::class);
        $user = $this->signInEmployee($employee);
        $pto = $this->create(PaidTimeOff::class, [
            'employee_id' => $employee->id,
            'start_time' => '2035-03-05',
            'end_time' => '2035-03-07',
        ]);
        $this->signInManager();

        $this->post("/ptos/destroy/{$pto->id}")->assertOk()->assertSee('1');

        $this->assertNull($pto->fresh());
        Mail::assertSent(PaidTimeOffDeletedMail::class, 1);
        Mail::assertSent(PaidTimeOffDeletedMail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    #[Test]
    public function calendar_feed_keeps_its_json_shape()
    {
        $employee = $this->create(Employee::class, ['name' => 'Alice']);
        Holiday::create(['title' => 'Memorial Day', 'date' => '2035-05-28', 'is_half_day' => false]);
        $pto = PaidTimeOff::saveForm([
            'employee_id' => $employee->id,
            'start_time' => '2035-05-25',
            'end_time' => '2035-05-29',
        ]);

        $this->getJson('/get/ptos/2035')
            ->assertOk()
            ->assertJsonPath('0.id', $pto->id)
            ->assertJsonPath('0.start_time', '2035-05-25 00:00:00')
            ->assertJsonPath('0.end_time', '2035-05-29 00:00:00')
            ->assertJsonPath('0.days', 2) // Fri + Tue; Memorial Day and the weekend are free
            ->assertJsonPath('0.is_approved', false)
            ->assertJsonPath('0.employee.name', 'Alice');

        $this->getJson('/get/holidays/2035')
            ->assertOk()
            ->assertJsonPath('0.date', '2035-05-28')
            ->assertJsonPath('0.is_half_day', false);
    }

    #[Test]
    public function employee_form_is_prefilled_from_the_model()
    {
        $manager = $this->signInManager(['name' => 'Mia Manager']);
        $employee = $this->create(Employee::class, [
            'name' => 'Carl Contractor',
            'phone' => '555-0100',
            'manager_id' => $manager->id,
            'is_contractor' => true,
        ]);

        $html = $this->get(route('manager.employees.edit', $employee))->assertOk()->getContent();

        $this->assertStringContainsString('action="' . route('manager.employees.update', $employee) . '"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="name"[^>]*value="Carl Contractor"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="phone"[^>]*value="555-0100"/', $html);
        $this->assertMatchesRegularExpression('/<option value="' . $manager->id . '" selected="selected">Mia Manager<\/option>/', $html);
        $this->assertMatchesRegularExpression('/<input type="checkbox" name="is_contractor"[^>]*value="1"[^>]*checked/', $html);
        $this->assertStringContainsString('<input type="hidden" name="is_contractor" value="0">', $html);
    }

    #[Test]
    public function employee_create_form_starts_unchecked_and_empty()
    {
        $this->signInManager();

        $html = $this->get(route('manager.employees.create'))->assertOk()->getContent();

        $this->assertStringContainsString('action="' . route('manager.employees.store') . '"', $html);
        $this->assertDoesNotMatchRegularExpression('/name="is_contractor"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/<option value(="")? selected="selected">Select One..<\/option>/', $html);
    }

    #[Test]
    public function employee_can_be_saved_with_blank_optional_fields()
    {
        $this->signInManager();
        $employee = $this->create(Employee::class, ['phone' => '555-0100', 'max_days_off' => 25]);

        $this->post(route('manager.employees.update', $employee), [
            'name' => 'Renamed',
            'title' => '',
            'phone' => '',
            'color' => 'white',
            'bgcolor' => 'green',
            'max_days_off' => '',
            'tag_string' => '',
            'manager_id' => '',
            'is_contractor' => '0',
        ])->assertRedirect('/manager');

        $employee->refresh();
        $this->assertSame('Renamed', $employee->name);
        $this->assertEmpty($employee->phone);
        $this->assertEquals(config('app.max_days_off'), $employee->daysLeft());
    }

    #[Test]
    public function holiday_forms_render_with_model_binding()
    {
        $this->signInAdmin();
        $holiday = Holiday::create(['title' => 'Summer Hours', 'date' => '2035-06-01', 'is_half_day' => true]);

        $edit = $this->get(route('admin.holidays.edit', $holiday))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<input[^>]*name="title"[^>]*value="Summer Hours"/', $edit);
        $this->assertMatchesRegularExpression('/<input[^>]*name="date"[^>]*value="2035-06-01"/', $edit);
        $this->assertMatchesRegularExpression('/<input type="checkbox" name="is_half_day"[^>]*checked/', $edit);

        $this->get(route('admin.holidays.create'))->assertOk()->assertSee('name="_token"', false);
        $this->get(route('admin.holidays.bulk'))->assertOk()
            ->assertSee('action="' . route('admin.holidays.bulkstore') . '"', false)
            ->assertSee('action="' . route('admin.holidays.halfstore') . '"', false);
    }

    #[Test]
    public function admin_user_and_team_forms_render()
    {
        $admin = $this->signInAdmin();
        $team = $this->create('App\Tag', ['name' => 'Backend', 'slug' => 'backend']);

        $this->get(route('admin.users.create'))->assertOk()
            ->assertSee('type="password" name="password"', false)
            ->assertSee('type="password" name="password_confirmation"', false);
        $this->get(route('admin.users.edit', $admin))->assertOk()
            ->assertSee('value="' . e($admin->email) . '"', false)
            ->assertSee('<option value="admin" selected="selected">Admin</option>', false);
        $this->get(route('admin.teams.edit', $team))->assertOk()
            ->assertSee('value="backend"', false);
    }

    #[Test]
    public function pto_mails_render_and_send_through_symfony_mailer()
    {
        config(['mail.default' => 'array']);
        $employee = $this->create(Employee::class, ['name' => 'Alice']);
        $this->create(User::class, ['employee_id' => $employee->id, 'email' => 'alice@continued.com']);
        $pto = PaidTimeOff::saveForm([
            'employee_id' => $employee->id,
            'start_time' => '2035-03-05',
            'end_time' => '2035-03-07',
        ]);

        foreach ([new PaidTimeOffRequestedMail($pto), new PaidTimeOffApprovedMail($pto), new PaidTimeOffDeletedMail($pto)] as $mailable) {
            $this->assertStringContainsString('Mon, Mar 5, 2035', $mailable->render());
            Mail::to('alice@continued.com')->send($mailable);
        }

        $messages = app('mail.manager')->mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(3, $messages);
        $subjects = $messages->map(fn ($sent) => $sent->getOriginalMessage()->getSubject())->all();
        $this->assertSame([
            'Paid Time Off Request from Alice',
            'Paid Time Off starting Mar 5, 2035 for 3 days(s) Pending',
            'Paid Time Off starting Mar 5, 2035 for 3 days(s) removed.',
        ], $subjects);
    }

    #[Test]
    public function on_call_page_reads_contact_from_config()
    {
        config([
            'app.always_on_call_name' => 'Help Desk',
            'app.always_on_call_number' => '(555) 010-0000',
        ]);

        $this->withSession(['GoogleToken' => 't', 'GoogleUser' => (object) ['name' => 'Someone']])
            ->get('/oncall')
            ->assertOk()
            ->assertSee('Help Desk')
            ->assertSee('(555) 010-0000');
    }

    #[Test]
    public function scheduled_commands_are_registered()
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('employee:user-sync')
            ->expectsOutputToContain('employee:clear-pto-limit')
            ->expectsOutputToContain('employee:clear-old-pto')
            ->expectsOutputToContain('holiday:clear-old')
            ->assertSuccessful();
    }

    #[Test]
    public function app_commands_are_discovered()
    {
        $commands = array_keys(\Illuminate\Support\Facades\Artisan::all());

        foreach (['oncall:shuffle', 'oncall:display', 'oncall:digest', 'employee:user-sync', 'employee:clear-pto-limit', 'employee:clear-old-pto', 'holiday:clear-old', 'api:generate-token'] as $command) {
            $this->assertContains($command, $commands);
        }
    }
}
