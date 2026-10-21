<?php

namespace Tests\Feature;

use App\Employee;
use App\Holiday;
use App\Tag;
use App\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Stored values must render escaped in forms and survive an edit/save cycle
 * unchanged (spatie/laravel-html writes textarea contents raw and does not
 * double-encode entities; App\Support\Html fixes both).
 */
class FormEscapingTest extends TestCase
{
    use DatabaseMigrations;

    private const XSS = '</textarea><script>alert("xss")</script>';
    private const ATTR_XSS = '"><script>alert(1)</script>';
    private const ENTITY = 'R&amp;D & Ops &lt;b&gt;';

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    private function field(string $html, string $name): string
    {
        $node = $this->xpath($html)->query("//*[@name='{$name}']")->item(0);
        $this->assertNotNull($node, "No form field named {$name}");

        return $node->nodeName === 'textarea' ? $node->textContent : $node->getAttribute('value');
    }

    private function assertNoInjectedScript(string $html): void
    {
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertSame(0, $this->xpath($html)->query("//script[contains(., 'alert')]")->length);
    }

    #[Test]
    public function team_textarea_escapes_markup_and_round_trips_entities()
    {
        $this->signInAdmin();
        $description = self::XSS . "\n" . self::ENTITY;
        $team = Tag::create(['name' => 'Backend', 'slug' => 'backend', 'description' => $description]);

        $html = $this->get(route('admin.teams.edit', $team))->assertOk()->getContent();

        $this->assertNoInjectedScript($html);
        $this->assertStringContainsString('&lt;/textarea&gt;&lt;script&gt;', $html);
        $this->assertStringContainsString('R&amp;amp;D &amp; Ops &amp;lt;b&amp;gt;', $html);
        $this->assertSame($description, $this->field($html, 'description'));

        // Submitting the form unchanged must not alter the stored value.
        $this->post(route('admin.teams.update', $team), [
            'name' => $this->field($html, 'name'),
            'slug' => $this->field($html, 'slug'),
            'description' => $this->field($html, 'description'),
        ])->assertRedirect(route('admin.teams'));

        $this->assertSame($description, $team->fresh()->description);
    }

    #[Test]
    public function text_inputs_escape_attribute_breakouts_and_round_trip_entities()
    {
        $this->signInAdmin();
        $team = Tag::create(['name' => self::ATTR_XSS . ' ' . self::ENTITY, 'slug' => 'payload']);
        $holiday = Holiday::create(['title' => self::ENTITY, 'date' => '2035-06-01']);

        $teamHtml = $this->get(route('admin.teams.edit', $team))->assertOk()->getContent();
        $this->assertNoInjectedScript($teamHtml);
        $this->assertSame($team->name, $this->field($teamHtml, 'name'));

        $holidayHtml = $this->get(route('admin.holidays.edit', $holiday))->assertOk()->getContent();
        $this->assertSame(self::ENTITY, $this->field($holidayHtml, 'title'));
    }

    #[Test]
    public function employee_form_round_trips_entities_in_inputs_and_select_labels()
    {
        $manager = $this->signInManager(['name' => 'Mia &amp; <i>Co</i>']);
        $employee = $this->create(Employee::class, ['name' => self::ENTITY, 'manager_id' => $manager->id]);

        $html = $this->get(route('manager.employees.edit', $employee))->assertOk()->getContent();

        $this->assertSame(self::ENTITY, $this->field($html, 'name'));
        $this->assertStringNotContainsString('<i>Co</i>', $html);
        $option = $this->xpath($html)->query("//select[@name='manager_id']/option[@value='{$manager->id}']")->item(0);
        $this->assertSame('Mia &amp; <i>Co</i>', $option->textContent);
        $this->assertTrue($option->hasAttribute('selected'));
    }

    #[Test]
    public function bulk_holiday_textarea_escapes_old_input()
    {
        $this->signInAdmin();
        $bulk = self::XSS . "\nHoliday " . self::ENTITY . ', 2035-01-02';

        $html = $this->withSession(['_old_input' => ['bulk' => $bulk]])
            ->get(route('admin.holidays.bulk'))
            ->assertOk()
            ->getContent();

        $this->assertNoInjectedScript($html);
        $this->assertSame($bulk, $this->field($html, 'bulk'));
    }

    #[Test]
    public function user_form_round_trips_entities()
    {
        $admin = $this->signInAdmin();
        $user = $this->create(User::class, ['name' => self::ATTR_XSS . self::ENTITY]);

        $html = $this->get(route('admin.users.edit', $user))->assertOk()->getContent();

        $this->assertNoInjectedScript($html);
        $this->assertSame($user->name, $this->field($html, 'name'));
    }
}
