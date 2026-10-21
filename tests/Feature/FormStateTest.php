<?php

namespace Tests\Feature;

use App\Employee;
use App\Holiday;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Model binding and old-input behaviour of the spatie/laravel-html forms.
 */
class FormStateTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function failed_validation_redisplays_old_input_over_model_values()
    {
        $this->signInAdmin();
        $manager = $this->create(User::class, ['role' => 'manager', 'name' => 'Mgr One']);
        $employee = $this->create(Employee::class, ['name' => 'Orig', 'is_contractor' => true, 'manager_id' => $manager->id]);

        $this->from(route('manager.employees.edit', $employee))
            ->post(route('manager.employees.update', $employee), [
                'name' => '', 'title' => 'New "Title"', 'is_contractor' => '0', 'manager_id' => (string) $manager->id,
            ])
            ->assertRedirect(route('manager.employees.edit', $employee))
            ->assertSessionHasErrors('name');

        $html = $this->get(route('manager.employees.edit', $employee))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<input[^>]*name="title"[^>]*value="New &quot;Title&quot;"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="name"[^>]*>/', $html);
        $this->assertStringNotContainsString('value="Orig"', $html);
        // The model says contractor, the submitted (old) value says not.
        $this->assertMatchesRegularExpression('/<input type="checkbox" name="is_contractor"[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input type="checkbox" name="is_contractor"[^>]*checked/', $html);
        $this->assertStringContainsString('<option value="' . $manager->id . '" selected="selected">Mgr One</option>', $html);
    }

    #[Test]
    public function user_edit_preselects_role_and_employee()
    {
        $this->signInAdmin();
        $employee = $this->create(Employee::class, ['name' => 'Emp A']);
        $user = $this->create(User::class, ['role' => 'planner', 'employee_id' => $employee->id]);

        $html = $this->get(route('admin.users.edit', $user))->assertOk()->getContent();

        preg_match_all('/<option[^>]*selected[^>]*>[^<]*<\/option>/', $html, $selected);
        $this->assertSame([
            '<option value="planner" selected="selected">Planner</option>',
            '<option value="' . $employee->id . '" selected="selected">Emp A</option>',
        ], $selected[0]);
    }

    #[Test]
    public function half_day_holiday_checkbox_can_be_unchecked()
    {
        $this->signInAdmin();
        $holiday = $this->create(Holiday::class, ['title' => 'Christmas Eve', 'date' => '2035-12-24', 'is_half_day' => true]);

        $html = $this->get(route('admin.holidays.edit', $holiday))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<input type="checkbox" name="is_half_day"[^>]*checked/', $html);

        // Unchecked box: only the hidden "0" field is submitted.
        $this->post(route('admin.holidays.update', $holiday), ['title' => 'Christmas Eve', 'date' => '2035-12-24', 'is_half_day' => '0'])
            ->assertRedirect(route('admin.holidays'));

        $this->assertFalse($holiday->fresh()->is_half_day);
    }
}
